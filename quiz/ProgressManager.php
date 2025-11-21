<?php
class ProgressManager {
    private $conn;

    public function __construct($host, $user, $pass, $db_name) {
        $dbHost = defined('DB_HOST') ? DB_HOST : $host;
        $dbUser = defined('DB_USER') ? DB_USER : $user;
        $dbPass = defined('DB_PASS') ? DB_PASS : $pass;
        $dbName = defined('DB_NAME') ? DB_NAME : $db_name;

        mysqli_report(MYSQLI_REPORT_OFF);
        $this->conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
    }

    public function getUserProgress($user_id) {
        if (!$this->conn || $this->conn->connect_error) {
            return ['current_level' => 1, 'total_stars' => 0];
        }

        $stmt = $this->conn->prepare("SELECT current_level, total_score FROM users WHERE user_id = ?");
        if (!$stmt) return ['current_level' => 1, 'total_stars' => 0];

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $progress = $result->fetch_assoc();
        $stmt->close();

        return [
            'current_level' => (int)($progress['current_level'] ?? 1),
            'total_stars' => (int)($progress['total_score'] ?? 0)
        ];
    }

    private function getQuizMaxStars($user_id, $level, $quiz_number) {
        if (!$this->conn || $this->conn->connect_error) return 0;

        $stmt = $this->conn->prepare("SELECT stars_earned FROM user_scores WHERE user_id = ? AND level = ? AND quiz_number = ?");
        if (!$stmt) return 0;

        $stmt->bind_param("iii", $user_id, $level, $quiz_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return (int)($data['stars_earned'] ?? 0);
    }

    public function hasCompletedLevel($user_id, $level, $quiz_data) {
        if (!isset($quiz_data[$level])) return true;

        $quizzesInLevel = count(array_filter(array_keys($quiz_data[$level]), 'is_numeric'));
        if ($quizzesInLevel === 0) return true;

        if (!$this->conn || $this->conn->connect_error) return false;

        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT quiz_number) AS count FROM user_scores WHERE user_id = ? AND level = ? AND stars_earned > 0");
        if (!$stmt) return false;

        $stmt->bind_param("ii", $user_id, $level);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return ((int)($data['count'] ?? 0)) >= $quizzesInLevel;
    }

    public function updateProgress($user_id, $stars_earned, $submitted_level, $submitted_quiz, $is_passed, $quiz_data) {
        if (!$is_passed) return ['status' => 'failed', 'message' => 'Quiz failed, no progress update.'];
        if ($user_id <= 0) return ['status' => 'skipped', 'message' => 'Invalid user ID.'];
        if (!$this->conn || $this->conn->connect_error) return ['status' => 'error', 'message' => 'Database connection error.'];

        $this->conn->begin_transaction();
        $message = "";

        try {
            $current_max_stars = $this->getQuizMaxStars($user_id, $submitted_level, $submitted_quiz);
            $scoreUpdated = false;

            if ($stars_earned > $current_max_stars) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO user_scores (user_id, level, quiz_number, stars_earned) 
                     VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stars_earned = VALUES(stars_earned)"
                );
                if (!$stmt) throw new Exception($this->conn->error);

                $stmt->bind_param("iiii", $user_id, $submitted_level, $submitted_quiz, $stars_earned);
                $stmt->execute();
                $stmt->close();
                $scoreUpdated = true;
                $message .= " New high score saved!";

                // Recalculate total stars
                $stmt_total = $this->conn->prepare("SELECT SUM(stars_earned) AS total FROM user_scores WHERE user_id = ?");
                $stmt_total->bind_param("i", $user_id);
                $stmt_total->execute();
                $result_total = $stmt_total->get_result();
                $new_total_score = (int)($result_total->fetch_assoc()['total'] ?? 0);
                $stmt_total->close();

                $stmt_update = $this->conn->prepare("UPDATE users SET total_score = ? WHERE user_id = ?");
                $stmt_update->bind_param("ii", $new_total_score, $user_id);
                $stmt_update->execute();
                $stmt_update->close();
                $message .= " Total stars: $new_total_score.";
            } else {
                // Ensure at least 1 star if passed but not improved
                $placeholder_stars = $current_max_stars > 0 ? $current_max_stars : 1;
                $stmt = $this->conn->prepare(
                    "INSERT IGNORE INTO user_scores (user_id, level, quiz_number, stars_earned) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("iiii", $user_id, $submitted_level, $submitted_quiz, $placeholder_stars);
                $stmt->execute();
                $stmt->close();
                $message = "Quiz completed, but score ($stars_earned) not improved over current ($current_max_stars).";
            }

            // Level advancement
            $stmt = $this->conn->prepare("SELECT current_level FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_level_in_db = (int)($result->fetch_assoc()['current_level'] ?? 1);
            $stmt->close();

            if ($submitted_level == $current_level_in_db && $this->hasCompletedLevel($user_id, $current_level_in_db, $quiz_data)) {
                $numeric_keys = array_filter(array_keys($quiz_data), 'is_int');
                $total_levels_defined = !empty($numeric_keys) ? max($numeric_keys) : 0;
                $next_level = $current_level_in_db + 1;

                if ($next_level <= $total_levels_defined && isset($quiz_data[$next_level])) {
                    $stmt = $this->conn->prepare("UPDATE users SET current_level = ? WHERE user_id = ? AND current_level < ?");
                    $stmt->bind_param("iii", $next_level, $user_id, $next_level);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) $_SESSION['current_level'] = $next_level;
                    $stmt->close();
                    $message = "Level $current_level_in_db completed! Unlocked Level $next_level. " . $message;
                } else {
                    $final_level_state = $total_levels_defined + 1;
                    $stmt = $this->conn->prepare("UPDATE users SET current_level = ? WHERE user_id = ? AND current_level <= ?");
                    $stmt->bind_param("iii", $final_level_state, $user_id, $total_levels_defined);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) $_SESSION['current_level'] = $final_level_state;
                    $stmt->close();
                    $message = "Congratulations! Final level completed! " . $message;
                }
            }

            $this->conn->commit();
            return ['status' => 'success', 'message' => $message];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Progress update failed for user $user_id: " . $e->getMessage());
            return ['status' => 'error', 'message' => "Database error during progress update."];
        }
    }

    public function resetProgress($user_id) {
        if ($user_id <= 0) return ['status' => 'error', 'message' => 'Invalid user ID.'];
        if (!$this->conn || $this->conn->connect_error) return ['status' => 'error', 'message' => 'DB connection error.'];

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("DELETE FROM user_scores WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("UPDATE users SET current_level = 1, total_score = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['current_level'] = 1;
            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Progress reset successfully.'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Progress reset failed for user $user_id: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error during reset.'];
        }
    }

    public function __destruct() {
        if ($this->conn && !$this->conn->connect_error) $this->conn->close();
    }
}
?>
