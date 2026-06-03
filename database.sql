-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 12:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `academic_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisory_section`
--

CREATE TABLE `advisory_section` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisory_section`
--

INSERT INTO `advisory_section` (`id`, `teacher_id`, `section`) VALUES
(4, 8, '1-A'),
(6, 12, '1-B'),
(5, 16, '1-C');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('quiz','activity','written','exam') NOT NULL,
  `category` enum('written','performance','exam') NOT NULL,
  `max_score` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `name`, `type`, `category`, `max_score`, `weight`, `semester`, `subject_id`) VALUES
(78, 'WW1', 'quiz', 'written', 30, 4.00, 1, 3),
(79, 'WW2', 'quiz', 'written', 15, 4.00, 1, 3),
(80, 'WW3', 'quiz', 'written', 15, 4.00, 1, 3),
(81, 'WW4', 'quiz', 'written', 10, 4.00, 1, 3),
(82, 'WW5', 'quiz', 'written', 15, 4.00, 1, 3),
(83, 'PT1', 'quiz', 'performance', 100, 16.67, 1, 3),
(84, 'PT2', 'quiz', 'performance', 100, 16.67, 1, 3),
(85, 'PT3', 'quiz', 'performance', 100, 16.66, 1, 3),
(86, 'ST1', 'quiz', 'exam', 50, 10.00, 1, 3),
(87, 'ST2', 'quiz', 'exam', 50, 10.00, 1, 3),
(88, 'TE', 'quiz', 'exam', 50, 10.00, 1, 3),
(89, 'WW1', 'quiz', 'written', 10, 4.00, 2, 3),
(90, 'WW2', 'quiz', 'written', 15, 4.00, 2, 3),
(91, 'WW3', 'quiz', 'written', 15, 4.00, 2, 3),
(92, 'WW4', 'quiz', 'written', 10, 4.00, 2, 3),
(93, 'WW5', 'quiz', 'written', 15, 4.00, 2, 3),
(94, 'PT1', 'quiz', 'performance', 100, 16.67, 2, 3),
(95, 'PT2', 'quiz', 'performance', 100, 16.67, 2, 3),
(96, 'PT3', 'quiz', 'performance', 100, 16.66, 2, 3),
(97, 'ST1', 'quiz', 'exam', 50, 10.00, 2, 3),
(98, 'ST2', 'quiz', 'exam', 50, 10.00, 2, 3),
(99, 'TE', 'quiz', 'exam', 50, 10.00, 2, 3),
(100, 'WW1', 'quiz', 'written', 10, 4.00, 3, 3),
(101, 'WW2', 'quiz', 'written', 15, 4.00, 3, 3),
(102, 'WW3', 'quiz', 'written', 15, 4.00, 3, 3),
(103, 'WW4', 'quiz', 'written', 10, 4.00, 3, 3),
(104, 'WW5', 'quiz', 'written', 15, 4.00, 3, 3),
(105, 'PT1', 'quiz', 'performance', 100, 16.67, 3, 3),
(106, 'PT2', 'quiz', 'performance', 100, 16.67, 3, 3),
(107, 'PT3', 'quiz', 'performance', 100, 16.66, 3, 3),
(108, 'ST1', 'quiz', 'exam', 50, 10.00, 3, 3),
(109, 'ST2', 'quiz', 'exam', 50, 10.00, 3, 3),
(110, 'TE', 'quiz', 'exam', 50, 10.00, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 3, 'UPDATE_SCORE', 'Student 16, Assignment 78, Semester 1: score changed from NULL to 1', '2026-06-03 09:30:48'),
(2, 3, 'UPDATE_SCORE', 'Student 16, Assignment 78, Semester 1: score changed from 1.00 to 10', '2026-06-03 09:30:59'),
(3, 3, 'UPDATE_SCORE', 'Student 16, Assignment 79, Semester 1: score changed from NULL to 14', '2026-06-03 09:30:59'),
(4, 3, 'UPDATE_SCORE', 'Student 16, Assignment 80, Semester 1: score changed from NULL to 14', '2026-06-03 09:30:59'),
(5, 3, 'UPDATE_SCORE', 'Student 16, Assignment 81, Semester 1: score changed from NULL to 10', '2026-06-03 09:30:59'),
(6, 3, 'UPDATE_SCORE', 'Student 16, Assignment 82, Semester 1: score changed from NULL to 14', '2026-06-03 09:30:59'),
(7, 3, 'UPDATE_SCORE', 'Student 16, Assignment 83, Semester 1: score changed from NULL to 90', '2026-06-03 09:30:59'),
(8, 3, 'UPDATE_SCORE', 'Student 16, Assignment 84, Semester 1: score changed from NULL to 90', '2026-06-03 09:30:59'),
(9, 3, 'UPDATE_SCORE', 'Student 16, Assignment 85, Semester 1: score changed from NULL to 90', '2026-06-03 09:30:59'),
(10, 3, 'UPDATE_SCORE', 'Student 16, Assignment 86, Semester 1: score changed from NULL to 40', '2026-06-03 09:30:59'),
(11, 3, 'UPDATE_SCORE', 'Student 16, Assignment 87, Semester 1: score changed from NULL to 40', '2026-06-03 09:30:59'),
(12, 3, 'UPDATE_SCORE', 'Student 16, Assignment 88, Semester 1: score changed from NULL to 40', '2026-06-03 09:30:59'),
(13, 3, 'UPDATE_SCORE', 'Student 17, Assignment 78, Semester 1: score changed from NULL to -10', '2026-06-03 09:46:55'),
(14, 3, 'UPDATE_MAX_SCORE', 'Assignment ID 78: max_score changed from 10 to 13', '2026-06-03 10:03:47'),
(15, 3, 'UPDATE_SCORE', 'Student 17, Assignment 78, Semester 1: score changed from -10.00 to 12', '2026-06-03 10:03:49'),
(16, 3, 'UPDATE_SCORE', 'Student 18, Assignment 78, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(17, 3, 'UPDATE_SCORE', 'Student 18, Assignment 79, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(18, 3, 'UPDATE_SCORE', 'Student 18, Assignment 80, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(19, 3, 'UPDATE_SCORE', 'Student 18, Assignment 81, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(20, 3, 'UPDATE_SCORE', 'Student 18, Assignment 82, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(21, 3, 'UPDATE_SCORE', 'Student 18, Assignment 83, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(22, 3, 'UPDATE_SCORE', 'Student 18, Assignment 84, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(23, 3, 'UPDATE_SCORE', 'Student 18, Assignment 85, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(24, 3, 'UPDATE_SCORE', 'Student 18, Assignment 86, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(25, 3, 'UPDATE_SCORE', 'Student 18, Assignment 87, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(26, 3, 'UPDATE_SCORE', 'Student 18, Assignment 88, Semester 1: score changed from NULL to 0', '2026-06-03 10:09:08'),
(27, 3, 'UPDATE_SCORE', 'Student 18, Assignment 78, Semester 1: score changed from 0.00 to 12', '2026-06-03 10:09:27'),
(28, 3, 'UPDATE_SCORE', 'Student 18, Assignment 79, Semester 1: score changed from 0.00 to 15', '2026-06-03 10:09:27'),
(29, 3, 'UPDATE_SCORE', 'Student 18, Assignment 80, Semester 1: score changed from 0.00 to 15', '2026-06-03 10:09:27'),
(30, 3, 'UPDATE_SCORE', 'Student 18, Assignment 81, Semester 1: score changed from 0.00 to 10', '2026-06-03 10:09:27'),
(31, 3, 'UPDATE_SCORE', 'Student 18, Assignment 82, Semester 1: score changed from 0.00 to 15', '2026-06-03 10:09:27'),
(32, 3, 'UPDATE_SCORE', 'Student 18, Assignment 83, Semester 1: score changed from 0.00 to 90', '2026-06-03 10:09:27'),
(33, 3, 'UPDATE_SCORE', 'Student 18, Assignment 84, Semester 1: score changed from 0.00 to 90', '2026-06-03 10:09:27'),
(34, 3, 'UPDATE_SCORE', 'Student 18, Assignment 85, Semester 1: score changed from 0.00 to 90', '2026-06-03 10:09:27'),
(35, 3, 'UPDATE_SCORE', 'Student 18, Assignment 86, Semester 1: score changed from 0.00 to 30', '2026-06-03 10:09:35'),
(36, 3, 'UPDATE_SCORE', 'Student 18, Assignment 87, Semester 1: score changed from 0.00 to 30', '2026-06-03 10:09:35'),
(37, 3, 'UPDATE_SCORE', 'Student 19, Assignment 78, Semester 1: score changed from NULL to 12.5', '2026-06-03 10:24:41'),
(38, 3, 'UPDATE_MAX_SCORE', 'Assignment ID 78: max_score changed from 13 to 16', '2026-06-03 10:25:09'),
(39, 3, 'UPDATE_MAX_SCORE', 'Assignment ID 78: max_score changed from 16 to 25', '2026-06-03 10:25:13'),
(40, 3, 'UPDATE_MAX_SCORE', 'Assignment ID 78: max_score changed from 25 to 30', '2026-06-03 10:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `student_id`, `assignment_id`, `score`, `semester`, `school_year`) VALUES
(12, 16, 78, 10.00, 1, NULL),
(13, 16, 79, 14.00, 1, NULL),
(14, 16, 80, 14.00, 1, NULL),
(15, 16, 81, 10.00, 1, NULL),
(16, 16, 82, 14.00, 1, NULL),
(17, 16, 83, 90.00, 1, NULL),
(18, 16, 84, 90.00, 1, NULL),
(19, 16, 85, 90.00, 1, NULL),
(20, 16, 86, 40.00, 1, NULL),
(21, 16, 87, 40.00, 1, NULL),
(22, 16, 88, 40.00, 1, NULL),
(23, 17, 78, 12.00, 1, NULL),
(24, 18, 78, 12.00, 1, NULL),
(25, 18, 79, 15.00, 1, NULL),
(26, 18, 80, 15.00, 1, NULL),
(27, 18, 81, 10.00, 1, NULL),
(28, 18, 82, 15.00, 1, NULL),
(29, 18, 83, 90.00, 1, NULL),
(30, 18, 84, 90.00, 1, NULL),
(31, 18, 85, 90.00, 1, NULL),
(32, 18, 86, 30.00, 1, NULL),
(33, 18, 87, 30.00, 1, NULL),
(34, 18, 88, 0.00, 1, NULL),
(35, 19, 78, 12.50, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `school_id_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `grade_level` int(11) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `school_id_number`, `name`, `first_name`, `last_name`, `lrn`, `birth_date`, `gender`, `grade_level`, `section`, `created_at`) VALUES
(16, '20261-00001', 'Habibi, Junior', 'Junior', 'Habibi', '293493929494', '2009-06-26', 'Male', 7, '1-A', '2026-06-02 07:41:19'),
(17, '20261-00002', 'Habibi, Senior', 'Senior', 'Habibi', '128323737333', '2010-04-10', 'Female', 7, '1-A', '2026-06-02 07:41:58'),
(18, '20261-00003', 'Doe, John', 'John', 'Doe', '721471273717', '2008-11-29', 'Male', 7, '1-B', '2026-06-03 06:43:23'),
(19, '20261-00004', 'Dayo, Kimi', 'Kimi', 'Dayo', '123127377333', '2008-09-28', 'Female', 7, '1-B', '2026-06-03 06:44:48'),
(20, '20261-00005', 'Core, Sound', 'Sound', 'Core', '112277373222', '2010-12-27', 'Male', 7, '1-C', '2026-06-03 06:45:25'),
(21, '20261-00006', 'Os, Mac', 'Mac', 'Os', '322355533222', '2010-09-11', 'Female', 7, '1-C', '2026-06-03 06:46:41');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `code`, `sort_order`) VALUES
(1, 'Filipino', 'FIL', 1),
(2, 'English', 'ENG', 2),
(3, 'Mathematics', 'MATH', 3),
(4, 'Science', 'SCI', 4),
(5, 'Araling Panlipunan', 'AP', 5),
(6, 'Edukasyon sa Pagpapakatao', 'ESP', 6),
(7, 'Technology and Livelihood Education', 'TLE', 7),
(8, 'MAPEH', 'MAPEH', 8);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_section`
--

CREATE TABLE `teacher_subject_section` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','subject_teacher','advisory_teacher') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(3, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', '2026-05-17 17:44:12'),
(8, 'math_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Math Teacher', 'subject_teacher', '2026-05-29 19:03:51'),
(9, 'advisory_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Advisory Teacher', 'advisory_teacher', '2026-05-29 19:03:51'),
(12, 'science_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Science Teacher', 'subject_teacher', '2026-06-03 06:04:23'),
(13, 'ap_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. AP Teacher', 'subject_teacher', '2026-06-03 06:27:05'),
(14, 'mapeh_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mrs. Mapeh Teacher', 'subject_teacher', '2026-06-03 06:39:22'),
(15, 'filipino_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Filipino Teacher', 'subject_teacher', '2026-06-03 06:41:59'),
(16, 'english_teacher', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. English Teacher', 'subject_teacher', '2026-06-03 06:42:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisory_section`
--
ALTER TABLE `advisory_section`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_advisory` (`teacher_id`,`section`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_assignment` (`student_id`,`assignment_id`,`semester`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`school_id_number`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_subject_section`
--
ALTER TABLE `teacher_subject_section`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`section`,`subject_id`),
  ADD KEY `tss_subject_fk` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisory_section`
--
ALTER TABLE `advisory_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher_subject_section`
--
ALTER TABLE `teacher_subject_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisory_section`
--
ALTER TABLE `advisory_section`
  ADD CONSTRAINT `advisory_section_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_assignments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject_section`
--
ALTER TABLE `teacher_subject_section`
  ADD CONSTRAINT `teacher_subject_section_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tss_subject_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
