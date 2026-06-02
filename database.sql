-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2026 at 05:33 PM
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
(2, 9, '10-D'),
(3, 9, '7-A');

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
(78, 'WW1', 'quiz', 'written', 10, 4.00, 1, 1),
(79, 'WW2', 'quiz', 'written', 15, 4.00, 1, 1),
(80, 'WW3', 'quiz', 'written', 15, 4.00, 1, 1),
(81, 'WW4', 'quiz', 'written', 10, 4.00, 1, 1),
(82, 'WW5', 'quiz', 'written', 15, 4.00, 1, 1),
(83, 'PT1', 'quiz', 'performance', 100, 16.67, 1, 1),
(84, 'PT2', 'quiz', 'performance', 100, 16.67, 1, 1),
(85, 'PT3', 'quiz', 'performance', 100, 16.66, 1, 1),
(86, 'ST1', 'quiz', 'exam', 50, 10.00, 1, 1),
(87, 'ST2', 'quiz', 'exam', 50, 10.00, 1, 1),
(88, 'TE', 'quiz', 'exam', 50, 10.00, 1, 1),
(89, 'WW1', 'quiz', 'written', 10, 4.00, 2, 1),
(90, 'WW2', 'quiz', 'written', 15, 4.00, 2, 1),
(91, 'WW3', 'quiz', 'written', 15, 4.00, 2, 1),
(92, 'WW4', 'quiz', 'written', 10, 4.00, 2, 1),
(93, 'WW5', 'quiz', 'written', 15, 4.00, 2, 1),
(94, 'PT1', 'quiz', 'performance', 100, 16.67, 2, 1),
(95, 'PT2', 'quiz', 'performance', 100, 16.67, 2, 1),
(96, 'PT3', 'quiz', 'performance', 100, 16.66, 2, 1),
(97, 'ST1', 'quiz', 'exam', 50, 10.00, 2, 1),
(98, 'ST2', 'quiz', 'exam', 50, 10.00, 2, 1),
(99, 'TE', 'quiz', 'exam', 50, 10.00, 2, 1),
(100, 'WW1', 'quiz', 'written', 10, 4.00, 3, 1),
(101, 'WW2', 'quiz', 'written', 15, 4.00, 3, 1),
(102, 'WW3', 'quiz', 'written', 15, 4.00, 3, 1),
(103, 'WW4', 'quiz', 'written', 10, 4.00, 3, 1),
(104, 'WW5', 'quiz', 'written', 15, 4.00, 3, 1),
(105, 'PT1', 'quiz', 'performance', 100, 16.67, 3, 1),
(106, 'PT2', 'quiz', 'performance', 100, 16.67, 3, 1),
(107, 'PT3', 'quiz', 'performance', 100, 16.66, 3, 1),
(108, 'ST1', 'quiz', 'exam', 50, 10.00, 3, 1),
(109, 'ST2', 'quiz', 'exam', 50, 10.00, 3, 1),
(110, 'TE', 'quiz', 'exam', 50, 10.00, 3, 1);

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
(1, 8, 'UPDATE_SCORE', 'Student 7, Assignment 78, Semester 1: score changed from NULL to 10', '2026-06-01 05:40:01'),
(2, 8, 'UPDATE_SCORE', 'Student 7, Assignment 79, Semester 1: score changed from NULL to 10', '2026-06-01 05:40:01'),
(3, 8, 'UPDATE_SCORE', 'Student 7, Assignment 80, Semester 1: score changed from NULL to 10', '2026-06-01 05:40:01'),
(4, 8, 'UPDATE_SCORE', 'Student 7, Assignment 81, Semester 1: score changed from NULL to 10', '2026-06-01 05:40:01'),
(5, 8, 'UPDATE_SCORE', 'Student 7, Assignment 82, Semester 1: score changed from NULL to 10', '2026-06-01 05:40:01'),
(6, 8, 'UPDATE_SCORE', 'Student 7, Assignment 83, Semester 1: score changed from NULL to 90', '2026-06-01 05:40:01'),
(7, 8, 'UPDATE_SCORE', 'Student 7, Assignment 84, Semester 1: score changed from NULL to 90', '2026-06-01 05:40:01'),
(8, 8, 'UPDATE_SCORE', 'Student 7, Assignment 85, Semester 1: score changed from NULL to 90', '2026-06-01 05:40:01'),
(9, 8, 'UPDATE_SCORE', 'Student 7, Assignment 86, Semester 1: score changed from NULL to 40', '2026-06-01 05:40:01'),
(10, 8, 'UPDATE_SCORE', 'Student 7, Assignment 87, Semester 1: score changed from NULL to 40', '2026-06-01 05:40:01'),
(11, 8, 'UPDATE_SCORE', 'Student 7, Assignment 88, Semester 1: score changed from NULL to 40', '2026-06-01 05:40:01');

-- --------------------------------------------------------

--
-- Table structure for table `grading_weights`
--

CREATE TABLE `grading_weights` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_weight_written` decimal(5,2) DEFAULT 40.00,
  `category_weight_pt` decimal(5,2) DEFAULT 40.00,
  `category_weight_exam` decimal(5,2) DEFAULT 20.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grading_weights`
--

INSERT INTO `grading_weights` (`id`, `name`, `category_weight_written`, `category_weight_pt`, `category_weight_exam`) VALUES
(1, 'General', 40.00, 40.00, 20.00),
(2, 'General', 40.00, 40.00, 20.00),
(3, 'General', 40.00, 40.00, 20.00);

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
(168, 7, 78, 10.00, 1, NULL),
(169, 7, 79, 10.00, 1, NULL),
(170, 7, 80, 10.00, 1, NULL),
(171, 7, 81, 10.00, 1, NULL),
(172, 7, 82, 10.00, 1, NULL),
(173, 7, 83, 90.00, 1, NULL),
(174, 7, 84, 90.00, 1, NULL),
(175, 7, 85, 90.00, 1, NULL),
(176, 7, 86, 40.00, 1, NULL),
(177, 7, 87, 40.00, 1, NULL),
(178, 7, 88, 40.00, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `grade_level` int(11) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `first_name`, `last_name`, `lrn`, `birth_date`, `gender`, `grade_level`, `section`, `created_at`) VALUES
(3, '2024-001', 'Juan Dela Cruz', 'Juan', 'Cruz', NULL, NULL, NULL, 7, '7-A', '2026-05-17 18:12:20'),
(4, '2024-002', 'Maria Santos', 'Maria', 'Santos', NULL, NULL, NULL, 7, '7-A', '2026-05-17 18:12:20'),
(5, '2024-003', 'Jose Rizal', 'Jose', 'Rizal', NULL, NULL, NULL, 8, '8-B', '2026-05-17 18:12:20'),
(6, '2024-004', 'Andres Bonifacio', 'Andres', 'Bonifacio', NULL, NULL, NULL, 9, '9-C', '2026-05-17 18:12:20'),
(7, '2024-005', 'Gabriela Silang', 'Gabriela', 'Silang', NULL, NULL, NULL, 10, '10-D', '2026-05-17 18:12:20'),
(8, '5', 'Mike Enriquez', 'Mike', 'Enriquez', NULL, NULL, NULL, 7, '8-C', '2026-05-28 13:56:58');

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
  `subject` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_section`
--

INSERT INTO `teacher_subject_section` (`id`, `teacher_id`, `section`, `subject`) VALUES
(2, 8, '10-D', 'Math'),
(5, 8, '7-A', 'Math'),
(4, 8, '8-B', 'Math');

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
(3, 'admin', '$2y$10$6axogDDGjYEK7Yyf12YSU.G.NrZC6HwIexABNnWxanOBZBxHTwAxq', 'System Administrator', 'admin', '2026-05-17 17:44:12'),
(8, 'math_teacher', '$2y$10$CRa1B5/JeED3n53JNsnxiuAXE2O2o5FVMsfyJbmJ53oucpKY.Ao9O', 'Mr. Math Teacher', 'subject_teacher', '2026-05-29 19:03:51'),
(9, 'advisory_teacher', '$2y$10$CRa1B5/JeED3n53JNsnxiuAXE2O2o5FVMsfyJbmJ53oucpKY.Ao9O', 'Ms. Advisory Teacher', 'advisory_teacher', '2026-05-29 19:03:51');

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
-- Indexes for table `grading_weights`
--
ALTER TABLE `grading_weights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

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
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`section`,`subject`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `grading_weights`
--
ALTER TABLE `grading_weights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teacher_subject_section`
--
ALTER TABLE `teacher_subject_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `grading_weights` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `teacher_subject_section_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
