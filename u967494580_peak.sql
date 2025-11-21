-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 21, 2025 at 04:59 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u967494580_peak`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `achievement_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `requirement` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(1, 'angelo gabriel tisoy', 'tisoyangelo31@gmail.com', 'helooooo', '2025-10-24 23:19:47'),
(2, 'gabriel', 'tisoyangelo31@gmail.com', 'helo again', '2025-10-25 09:37:02'),
(3, 'angelo gabriel tisoy', 'tisoyangelo31@gmail.com', 'hi again', '2025-10-27 11:32:58'),
(4, 'Bien Miguel M. Lamano', 'lamanomiguel@gmail.com', 'brah brah', '2025-10-29 03:11:54'),
(5, 'Bien Miguel M. Lamano', 'lamanomiguel@gmail.com', '1321312', '2025-10-29 03:23:34'),
(6, 'Bien Miguel M. Lamano', 'lamanobienmiguel@gmail.com', 'dwadwdwfasd', '2025-10-29 03:29:54'),
(7, 'angelo tisoy', 'lamanomiguel@gmail.com', 'hello, i need help', '2025-10-29 10:45:00'),
(8, 'angelo tisoy', 'lamanomiguel@gmail.com', 'hello, i need help', '2025-10-29 10:45:04'),
(9, 'Pogi', 'your@gmail.com', 'Djdkfjkdkddjdj', '2025-11-21 04:14:30');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `difficulty_level` enum('easy','medium','hard') DEFAULT 'easy',
  `language` enum('Filipino','English') DEFAULT 'Filipino',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lesson_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('not started','in progress','completed') DEFAULT 'not started',
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `question_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `quiz_number` int(11) NOT NULL,
  `quiz_type` enum('mcq','truefalse','fillblank') NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(10) NOT NULL,
  `blank_prompt` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `verification_code` varchar(8) DEFAULT NULL,
  `login_code` varchar(6) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `login_code_timestamp` datetime DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'teacher'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `username`, `email`, `photo`, `password_hash`, `reset_token`, `token_expiry`, `is_verified`, `is_banned`, `verification_code`, `login_code`, `full_name`, `created_at`, `login_code_timestamp`, `role`) VALUES
(21, 'BienTest', 'dieamond.art@gmail.com', '1761707772_VALOMATCHMVPyep1.png', '$2y$10$2mvWgrcc9gOL0pL56tFnu.Cb9zNnwy0.tt2RRHpEvvuuOSG4IFHUK', NULL, NULL, 1, 0, NULL, NULL, 'Bien Miguel M. Lamano', '2025-10-29 03:12:08', '2025-10-29 04:12:38', 'teacher'),
(22, 'TISOY', 'tisoyangelo31@gmail.com', '1763651020_3.png', '$2y$10$g3BT9pWdMZ90n/KlhR58N.eS6pMOwv4nJgAu5zAvXLMJEAk9LqGF2', NULL, NULL, 1, 0, NULL, NULL, 'Angelo Gabriel Tisoy', '2025-11-20 14:00:23', '2025-11-20 14:49:07', 'teacher');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `login_code` varchar(6) DEFAULT NULL,
  `current_level` int(11) NOT NULL DEFAULT 1,
  `quizzes_completed` int(11) NOT NULL DEFAULT 0,
  `total_score` int(11) NOT NULL DEFAULT 0,
  `photo` varchar(255) DEFAULT 'default.png',
  `is_banned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password_hash`, `role`, `created_at`, `reset_token`, `token_expiry`, `verification_code`, `is_verified`, `login_code`, `current_level`, `quizzes_completed`, `total_score`, `photo`, `is_banned`) VALUES
(90, 'Bien Miguel M. Lamano', 'Bien', 'lamanomiguel@gmail.com', '$2y$10$Py4tcXHa2T9OzM1oGTfijeWgxRTnqNJtR0gUihQ/GoxDD4QqlI2.y', 'admin', '2025-11-20 06:40:51', NULL, NULL, NULL, 1, NULL, 2, 0, 22, '1763698166_2nd.png', 0),
(91, 'Angelo Medina', 'GElO', 'tisoyangelo31@gmail.com', '$2y$10$8dAxuft6gyEjbZzH6MfF9.pWAZcD4u013MVEnoxdjnlRdDyYLLssK', 'student', '2025-11-20 15:12:12', NULL, NULL, NULL, 1, NULL, 2, 0, 25, '1.png', 0),
(92, 'Millado, Marich Lew Adrian B.', 'Mawich', 'marichmillado@gmail.com', '$2y$10$cHCf7cEPNSPBUY8/yqTbnuRZ6YPPKBgggNw8HoqMwJHpeJ0fuspf2', 'student', '2025-11-20 16:38:50', NULL, NULL, NULL, 1, NULL, 2, 0, 25, '2.png', 0),
(93, 'Pamela Felize Ramirez Padlan', 'hamuko27', 'mpfpadlan@tip.edu.ph', '$2y$10$9.TthYmbNC/SczWffyzkROvOk1yR9l..pAudv3BI.E7nszoeJOGom', 'student', '2025-11-20 20:17:44', NULL, NULL, NULL, 1, NULL, 5, 0, 248, '2.png', 0),
(94, 'Miguel Bien', 'Bean', 'lamanobienmiguel@gmail.com', '$2y$10$aieoazGWx/dVSl/1vJ5BiuPraZ6lwaMnj7V/fPe2J686Hy42qdPL2', 'student', '2025-11-20 20:20:07', NULL, NULL, NULL, 1, NULL, 5, 0, 243, 'default.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_achievement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_scores`
--

CREATE TABLE `user_scores` (
  `user_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `quiz_number` int(11) NOT NULL,
  `stars_earned` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_scores`
--

INSERT INTO `user_scores` (`user_id`, `level`, `quiz_number`, `stars_earned`) VALUES
(90, 1, 1, 5),
(90, 1, 2, 7),
(90, 1, 3, 10),
(91, 1, 1, 6),
(91, 1, 2, 9),
(91, 1, 3, 10),
(92, 1, 1, 6),
(92, 1, 2, 9),
(92, 1, 3, 10),
(93, 1, 1, 6),
(93, 1, 2, 9),
(93, 1, 3, 10),
(93, 2, 1, 17),
(93, 2, 2, 19),
(93, 2, 3, 14),
(93, 3, 1, 26),
(93, 3, 2, 27),
(93, 3, 3, 24),
(93, 4, 1, 30),
(93, 4, 2, 28),
(93, 4, 3, 38),
(94, 1, 1, 5),
(94, 1, 2, 9),
(94, 1, 3, 10),
(94, 2, 1, 17),
(94, 2, 2, 15),
(94, 2, 3, 18),
(94, 3, 1, 21),
(94, 3, 2, 27),
(94, 3, 3, 24),
(94, 4, 1, 38),
(94, 4, 2, 36),
(94, 4, 3, 23);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`achievement_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`question_id`),
  ADD UNIQUE KEY `level` (`level`,`quiz_number`,`question_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_scores`
--
ALTER TABLE `user_scores`
  ADD PRIMARY KEY (`user_id`,`level`,`quiz_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `achievement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `user_achievement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`);

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`achievement_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_scores`
--
ALTER TABLE `user_scores`
  ADD CONSTRAINT `user_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
