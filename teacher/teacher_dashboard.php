<?php
// --- 1. START SESSION ---
session_start();

// --- 2. SECURITY CHECK ---
if (empty($_SESSION['teacher_id'])) {
    header("Location: ../Verification/teacher_login.php"); 
    exit;
}

// --- 3. GET TEACHER INFO & DATABASE ---
require_once '../conn.php'; // Include database connection

$teacher_id = $_SESSION['teacher_id'];
$teacher_username = $_SESSION['teacher_username'] ?? 'Teacher';

// --- 4. RUN YOUR DASHBOARD QUERY ---
$sql = "
SELECT 
    u.user_id,
    u.username,
    COUNT(us.quiz_number) AS total_quizzes_completed,
    COALESCE(MAX(us.stars_earned), 0) AS highest_score,
    COALESCE(ROUND(AVG(us.stars_earned), 2), 0) AS average_score
FROM users u
LEFT JOIN user_scores us ON u.user_id = us.user_id
WHERE u.role = 'student'
GROUP BY u.user_id, u.username
ORDER BY average_score DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Error running query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #a3d5f7, #e3f2fd);
            margin: 0;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* --- Sidebar & Header Styles (from main.php) --- */
        .header-section {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: 0.3s;
        }

        .logo-toggle:hover {
            transform: scale(1.05);
        }

        .logo-toggle img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .header-title {
            color: #ec5757;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .header-title.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
            z-index: 999;
            padding-top: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .sidebar-header h2 {
            color: #ec5757;
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .sidebar-header p {
            color: #666;
            margin: 0;
            word-wrap: break-word;
        }

        .nav-links {
            padding: 20px 0;
            flex-grow: 1;
        }

        .nav-links a {
            display: block;
            padding: 15px 25px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-links a:hover,
        .nav-links a.active-link {
            background: #fdf0f0;
            border-left-color: #ec5757;
            color: #ec5757;
        }

        .logout-section {
            margin-top: auto;
            border-top: 1px solid #eee;
            padding: 15px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* --- Main Content Styles --- */
        .main-content {
            margin-left: 0; /* Changed for mobile-first */
            padding: 20px;
            min-height: 100vh;
            padding-top: 90px; /* Space for header */
            transition: margin-left 0.3s ease;
        }
        
        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ec5757;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* --- Styled Buttons --- */
        .action-btn {
            /* width: 100%; */ /* Removed width: 100% to allow auto sizing */
            padding: 10px 20px; /* Adjusted padding for smaller button */
            border: none;
            border-radius: 12px;
            font-size: 0.9rem; /* Adjusted font size */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex; /* Changed to inline-flex */
            align-items: center;
            justify-content: center;
            gap: 8px; /* Slightly reduced gap */
            text-decoration: none;
            color: inherit;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #ec5757, #ff8787);
            color: white;
        }

        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 87, 87, 0.4);
        }

        .action-btn.secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e9ecef;
        }

        .action-btn.secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        /* --- Table Card Styles --- */
        .table-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            overflow: hidden; /* Important for border-radius on table */
        }
        
        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 800px; /* Force horizontal scroll if needed */
            border-collapse: collapse;
        }

        thead tr {
            background-color: #fdf6f6;
        }

        th, td {
            padding: 15px 25px;
            text-align: left;
            white-space: nowrap;
        }

        th {
            font-size: 0.85rem;
            font-weight: 600;
            color: #7c4646;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:last-child {
            border-bottom: none;
        }
        
        tbody tr:hover {
            background-color: #fcfcfc;
        }
        
        td {
            color: #555;
            font-size: 0.95rem;
        }
        
        td.username {
            font-weight: 600;
            color: #333;
        }

        .score-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .score-badge.high {
            background-color: #d4edda;
            color: #155724;
        }
        
        .score-badge.avg {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .no-data-cell {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 1rem;
        }
        
        .footer {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 30px;
            padding-bottom: 20px;
        }

        /* --- Responsive Design --- */
        
        /* Desktop */
        @media (min-width: 1024px) {
            .main-content {
                margin-left: 300px;
            }
        }
        
        /* Tablet & Mobile */
        @media (max-width: 1023px) {
            .header-section {
                left: 15px;
                top: 15px;
            }
            .header-title { 
                font-size: 1.5rem; 
            }
            .logo-toggle img { 
                width: 40px; 
                height: 40px; 
            }
            .sidebar { 
                width: 280px; 
                left: -280px; 
            }
            .main-content {
                padding: 15px;
                padding-top: 80px;
            }
        }
        
        /* Mobile */
        @media (max-width: 767px) {
            .dashboard-header {
                align-items: stretch;
            }
            .dashboard-header h1 {
                font-size: 1.8rem;
                text-align: center;
            }
            .action-btn {
                font-size: 0.9rem; /* Adjusted base font-size */
                padding: 10px 15px; /* Adjusted base padding */
            }
            
            .table-header {
                padding: 15px 20px;
            }
            .table-header h2 {
                font-size: 1.25rem;
            }
            
            th, td {
                padding: 12px 20px;
            }
            
            td {
                font-size: 0.9rem;
            }
            
            /* Keep primary button consistent */
            /* Removed specific .action-btn.primary style override for mobile */
        }

    </style>
</head>
<body>

    <!-- --- Header from main.php --- -->
    <div class="header-section">
        <button class="logo-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
            <!-- Assuming logo path is relative to this file's location -->
            <img src="../ui/Illustration17.png" alt="PeakBasa Logo">
        </button>
        <h1 class="header-title">PeakBasa</h1>
    </div>

    <!-- --- Sidebar from main.php --- -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Navigation</h2>
            <p>Welcome, <b><?php echo htmlspecialchars($teacher_username); ?></b>!</p>
        </div>

        <div class="nav-links">
            <a href="teacher_dashboard.php" class="active-link">📊 Teacher Dashboard</a>
            <a href="../Main/main.php">🏆 Progress Map (Play)</a>
            <a href="../Main/profilemain.php">👤 Profile</a>
            <!-- You might want to add other teacher-specific links here -->
        </div>
        
        <div class="logout-section">
            <a href="../Verification/logout.php" class="logout-btn">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- --- Overlay from main.php --- -->
    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <!-- --- Main Content Wrapper --- -->
    <div class="main-content">
        <div class="dashboard-container">
            
            <!-- Dashboard Header -->
            <header class="dashboard-header">
                <h1>🧑‍🏫 Teacher Dashboard</h1>
                <!-- You could add a "Generate Full Class Report" button here -->
            </header>

            <!-- Main Content: Student Progress Table -->
            <main>
                <div class="table-card">
                    <div class="table-header">
                        <h2>Student Progress</h2>
                    </div>
                    
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Total Quizzes</th>
                                    <th>Highest Score</th>
                                    <th>Average Score</th>
                                    <th style="text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="username"><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= $row['total_quizzes_completed'] ?></td>
                                            <td>
                                                <span class="score-badge high">
                                                    <?= $row['highest_score'] ?> ⭐
                                                </span>
                                            </td>
                                            <td>
                                                <span class="score-badge avg">
                                                    <?= $row['average_score'] ?> ⭐
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <form action="generate_student_report.php" method="POST" target="_blank" style="display: inline-block;">
                                                    <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                                                    <!-- Removed inline styles, added icon -->
                                                    <button type="submit" class="action-btn primary">
                                                        📄 Generate PDF 
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-data-cell">
                                            No student data found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php $conn->close(); ?>
                            </tbody>
                        </table>
                    </div> <!-- end table-wrapper -->
                </div> <!-- end table-card -->
            </main>
            
            <footer class="footer">
                © <?php echo date("Y"); ?> PeakBasa. All rights reserved.
            </footer>

        </div> <!-- end dashboard-container -->
    </div> <!-- end main-content -->

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const headerTitle = document.querySelector('.header-title');

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            headerTitle.classList.toggle('hidden');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const headerTitle = document.querySelector('.header-title');

            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            headerTitle.classList.remove('hidden');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const headerSection = document.querySelector('.header-section');

            if (sidebar.classList.contains('active') &&
                !sidebar.contains(event.target) &&
                !headerSection.contains(event.target)) {
                 closeSidebar();
            }
        });

        document.addEventListener('keydown', function(e) {
            const sidebar = document.getElementById('sidebar');
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>

