-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 11:13 PM
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
(1, 'Quiz 1', 'quiz', 'written', 20, 4.00, 1, 1),
(2, 'Quiz 2', 'quiz', 'written', 20, 4.00, 1, 1),
(3, 'Quiz 3', 'quiz', 'written', 20, 4.00, 1, 1),
(4, 'Quiz 4', 'quiz', 'written', 20, 4.00, 1, 1),
(5, 'Activity 1', 'activity', 'performance', 20, 4.00, 1, 1),
(6, 'Activity 2', 'activity', 'performance', 20, 4.00, 1, 1),
(7, 'Activity 3', 'activity', 'performance', 20, 4.00, 1, 1),
(8, 'Activity 4', 'activity', 'performance', 20, 4.00, 1, 1),
(9, 'Written Work 1', 'written', 'written', 30, 4.00, 1, 1),
(10, 'Written Work 2', 'written', 'written', 30, 4.00, 1, 1),
(11, 'Quarterly Exam', 'exam', 'exam', 50, 20.00, 1, 1),
(12, 'Written Work 1', 'quiz', 'written', 20, 4.00, 1, 1),
(13, 'Written Work 2', 'quiz', 'written', 20, 4.00, 1, 1),
(14, 'Written Work 3', 'quiz', 'written', 20, 4.00, 1, 1),
(15, 'Written Work 4', 'quiz', 'written', 20, 4.00, 1, 1),
(16, 'Written Work 5', 'quiz', 'written', 20, 4.00, 1, 1),
(17, 'Written Work 6', 'quiz', 'written', 20, 4.00, 1, 1),
(18, 'Written Work 7', 'quiz', 'written', 20, 4.00, 1, 1),
(19, 'Written Work 8', 'quiz', 'written', 20, 4.00, 1, 1),
(20, 'Written Work 9', 'quiz', 'written', 20, 4.00, 1, 1),
(21, 'Written Work 10', 'quiz', 'written', 20, 4.00, 1, 1),
(22, 'Performance Task 1', 'quiz', 'performance', 20, 4.00, 1, 1),
(23, 'Performance Task 2', 'quiz', 'performance', 20, 4.00, 1, 1),
(24, 'Performance Task 3', 'quiz', 'performance', 20, 4.00, 1, 1),
(25, 'Performance Task 4', 'quiz', 'performance', 20, 4.00, 1, 1),
(26, 'Performance Task 5', 'quiz', 'performance', 20, 4.00, 1, 1),
(27, 'Performance Task 6', 'quiz', 'performance', 20, 4.00, 1, 1),
(28, 'Performance Task 7', 'quiz', 'performance', 20, 4.00, 1, 1),
(29, 'Performance Task 8', 'quiz', 'performance', 20, 4.00, 1, 1),
(30, 'Performance Task 9', 'quiz', 'performance', 20, 4.00, 1, 1),
(31, 'Performance Task 10', 'quiz', 'performance', 20, 4.00, 1, 1),
(32, 'Quarterly Assessment 1', 'quiz', 'exam', 50, 10.00, 1, 1),
(33, 'Quarterly Assessment 2', 'quiz', 'exam', 50, 10.00, 1, 1),
(34, 'Written Work 1', 'quiz', 'written', 20, 4.00, 2, 1),
(35, 'Written Work 2', 'quiz', 'written', 20, 4.00, 2, 1),
(36, 'Written Work 3', 'quiz', 'written', 20, 4.00, 2, 1),
(37, 'Written Work 4', 'quiz', 'written', 20, 4.00, 2, 1),
(38, 'Written Work 5', 'quiz', 'written', 20, 4.00, 2, 1),
(39, 'Written Work 6', 'quiz', 'written', 20, 4.00, 2, 1),
(40, 'Written Work 7', 'quiz', 'written', 20, 4.00, 2, 1),
(41, 'Written Work 8', 'quiz', 'written', 20, 4.00, 2, 1),
(42, 'Written Work 9', 'quiz', 'written', 20, 4.00, 2, 1),
(43, 'Written Work 10', 'quiz', 'written', 20, 4.00, 2, 1),
(44, 'Performance Task 1', 'quiz', 'performance', 20, 4.00, 2, 1),
(45, 'Performance Task 2', 'quiz', 'performance', 20, 4.00, 2, 1),
(46, 'Performance Task 3', 'quiz', 'performance', 20, 4.00, 2, 1),
(47, 'Performance Task 4', 'quiz', 'performance', 20, 4.00, 2, 1),
(48, 'Performance Task 5', 'quiz', 'performance', 20, 4.00, 2, 1),
(49, 'Performance Task 6', 'quiz', 'performance', 20, 4.00, 2, 1),
(50, 'Performance Task 7', 'quiz', 'performance', 20, 4.00, 2, 1),
(51, 'Performance Task 8', 'quiz', 'performance', 20, 4.00, 2, 1),
(52, 'Performance Task 9', 'quiz', 'performance', 20, 4.00, 2, 1),
(53, 'Performance Task 10', 'quiz', 'performance', 20, 4.00, 2, 1),
(54, 'Quarterly Assessment 1', 'quiz', 'exam', 100, 10.00, 2, 1),
(55, 'Quarterly Assessment 2', 'quiz', 'exam', 100, 10.00, 2, 1),
(56, 'Written Work 1', 'quiz', 'written', 20, 4.00, 3, 1),
(57, 'Written Work 2', 'quiz', 'written', 20, 4.00, 3, 1),
(58, 'Written Work 3', 'quiz', 'written', 20, 4.00, 3, 1),
(59, 'Written Work 4', 'quiz', 'written', 20, 4.00, 3, 1),
(60, 'Written Work 5', 'quiz', 'written', 20, 4.00, 3, 1),
(61, 'Written Work 6', 'quiz', 'written', 20, 4.00, 3, 1),
(62, 'Written Work 7', 'quiz', 'written', 20, 4.00, 3, 1),
(63, 'Written Work 8', 'quiz', 'written', 20, 4.00, 3, 1),
(64, 'Written Work 9', 'quiz', 'written', 20, 4.00, 3, 1),
(65, 'Written Work 10', 'quiz', 'written', 20, 4.00, 3, 1),
(66, 'Performance Task 1', 'quiz', 'performance', 20, 4.00, 3, 1),
(67, 'Performance Task 2', 'quiz', 'performance', 20, 4.00, 3, 1),
(68, 'Performance Task 3', 'quiz', 'performance', 20, 4.00, 3, 1),
(69, 'Performance Task 4', 'quiz', 'performance', 20, 4.00, 3, 1),
(70, 'Performance Task 5', 'quiz', 'performance', 20, 4.00, 3, 1),
(71, 'Performance Task 6', 'quiz', 'performance', 20, 4.00, 3, 1),
(72, 'Performance Task 7', 'quiz', 'performance', 20, 4.00, 3, 1),
(73, 'Performance Task 8', 'quiz', 'performance', 20, 4.00, 3, 1),
(74, 'Performance Task 9', 'quiz', 'performance', 20, 4.00, 3, 1),
(75, 'Performance Task 10', 'quiz', 'performance', 20, 4.00, 3, 1),
(76, 'Quarterly Assessment 1', 'quiz', 'exam', 100, 10.00, 3, 1),
(77, 'Quarterly Assessment 2', 'quiz', 'exam', 100, 10.00, 3, 1);

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
(1, 6, 1, 20.00, 1, NULL),
(4, 6, 2, 20.00, 1, NULL),
(5, 6, 3, 20.00, 1, NULL),
(6, 6, 4, 20.00, 1, NULL),
(7, 6, 9, 20.00, 1, NULL),
(8, 6, 10, 20.00, 1, NULL),
(9, 6, 12, 20.00, 1, NULL),
(10, 6, 13, 20.00, 1, NULL),
(11, 6, 14, 20.00, 1, NULL),
(12, 6, 15, 20.00, 1, NULL),
(13, 6, 16, 20.00, 1, NULL),
(14, 6, 17, 20.00, 1, NULL),
(15, 6, 18, 20.00, 1, NULL),
(16, 6, 19, 20.00, 1, NULL),
(17, 6, 20, 20.00, 1, NULL),
(18, 6, 21, 20.00, 1, NULL),
(19, 6, 5, 20.00, 1, NULL),
(20, 6, 6, 20.00, 1, NULL),
(21, 6, 7, 20.00, 1, NULL),
(22, 6, 8, 20.00, 1, NULL),
(23, 6, 22, 20.00, 1, NULL),
(24, 6, 23, 20.00, 1, NULL),
(25, 6, 24, 20.00, 1, NULL),
(26, 6, 25, 20.00, 1, NULL),
(27, 6, 26, 20.00, 1, NULL),
(28, 6, 27, 20.00, 1, NULL),
(29, 6, 28, 20.00, 1, NULL),
(30, 6, 29, 20.00, 1, NULL),
(31, 6, 30, 20.00, 1, NULL),
(32, 6, 31, 20.00, 1, NULL),
(33, 6, 11, 50.00, 1, NULL),
(34, 6, 32, 50.00, 1, NULL),
(35, 6, 33, 50.00, 1, NULL),
(36, 7, 1, 20.00, 1, NULL),
(37, 7, 2, 20.00, 1, NULL),
(38, 7, 3, 20.00, 1, NULL),
(39, 7, 4, 20.00, 1, NULL),
(40, 7, 9, 20.00, 1, NULL),
(41, 7, 10, 20.00, 1, NULL),
(42, 7, 12, 20.00, 1, NULL),
(43, 7, 13, 20.00, 1, NULL),
(44, 7, 14, 20.00, 1, NULL),
(45, 7, 15, 20.00, 1, NULL),
(46, 7, 16, 20.00, 1, NULL),
(47, 7, 17, 20.00, 1, NULL),
(48, 7, 18, 20.00, 1, NULL),
(49, 7, 19, 20.00, 1, NULL),
(50, 7, 20, 20.00, 1, NULL),
(51, 7, 21, 20.00, 1, NULL),
(52, 7, 5, 20.00, 1, NULL),
(53, 7, 6, 20.00, 1, NULL),
(54, 7, 7, 20.00, 1, NULL),
(55, 7, 8, 20.00, 1, NULL),
(56, 7, 22, 20.00, 1, NULL),
(57, 7, 23, 20.00, 1, NULL),
(58, 7, 24, 20.00, 1, NULL),
(59, 7, 25, 20.00, 1, NULL),
(60, 7, 26, 20.00, 1, NULL),
(61, 7, 27, 20.00, 1, NULL),
(62, 7, 28, 20.00, 1, NULL),
(63, 7, 29, 20.00, 1, NULL),
(64, 7, 30, 20.00, 1, NULL),
(65, 7, 31, 20.00, 1, NULL),
(66, 7, 11, 50.00, 1, NULL),
(67, 7, 32, 50.00, 1, NULL),
(68, 7, 33, 50.00, 1, NULL),
(69, 5, 1, 20.00, 1, NULL),
(70, 5, 2, 20.00, 1, NULL),
(71, 5, 3, 20.00, 1, NULL),
(72, 5, 4, 20.00, 1, NULL),
(73, 5, 9, 20.00, 1, NULL),
(74, 5, 10, 20.00, 1, NULL),
(75, 5, 12, 20.00, 1, NULL),
(76, 5, 13, 20.00, 1, NULL),
(77, 5, 14, 20.00, 1, NULL),
(78, 5, 15, 20.00, 1, NULL),
(79, 5, 16, 20.00, 1, NULL),
(80, 5, 17, 20.00, 1, NULL),
(81, 5, 18, 20.00, 1, NULL),
(82, 5, 19, 20.00, 1, NULL),
(83, 5, 20, 20.00, 1, NULL),
(84, 5, 21, 20.00, 1, NULL),
(85, 5, 5, 20.00, 1, NULL),
(86, 5, 6, 20.00, 1, NULL),
(87, 5, 7, 20.00, 1, NULL),
(88, 5, 8, 20.00, 1, NULL),
(89, 5, 22, 20.00, 1, NULL),
(90, 5, 23, 20.00, 1, NULL),
(91, 5, 24, 20.00, 1, NULL),
(92, 5, 25, 20.00, 1, NULL),
(93, 5, 26, 20.00, 1, NULL),
(94, 5, 27, 20.00, 1, NULL),
(95, 5, 28, 20.00, 1, NULL),
(96, 5, 29, 20.00, 1, NULL),
(97, 5, 30, 20.00, 1, NULL),
(98, 5, 31, 20.00, 1, NULL),
(99, 5, 11, 50.00, 1, NULL),
(100, 5, 32, 50.00, 1, NULL),
(101, 5, 33, 50.00, 1, NULL),
(102, 3, 1, 20.00, 1, NULL),
(103, 3, 2, 20.00, 1, NULL),
(104, 3, 3, 20.00, 1, NULL),
(105, 3, 4, 20.00, 1, NULL),
(106, 3, 9, 20.00, 1, NULL),
(107, 3, 10, 20.00, 1, NULL),
(108, 3, 12, 20.00, 1, NULL),
(109, 3, 13, 20.00, 1, NULL),
(110, 3, 14, 20.00, 1, NULL),
(111, 3, 15, 20.00, 1, NULL),
(112, 3, 16, 20.00, 1, NULL),
(113, 3, 17, 20.00, 1, NULL),
(114, 3, 18, 20.00, 1, NULL),
(115, 3, 19, 20.00, 1, NULL),
(116, 3, 20, 20.00, 1, NULL),
(117, 3, 21, 20.00, 1, NULL),
(118, 3, 5, 20.00, 1, NULL),
(119, 3, 6, 20.00, 1, NULL),
(120, 3, 7, 20.00, 1, NULL),
(121, 3, 8, 20.00, 1, NULL),
(122, 3, 22, 20.00, 1, NULL),
(123, 3, 23, 20.00, 1, NULL),
(124, 3, 24, 20.00, 1, NULL),
(125, 3, 25, 20.00, 1, NULL),
(126, 3, 26, 20.00, 1, NULL),
(127, 3, 27, 20.00, 1, NULL),
(128, 3, 28, 20.00, 1, NULL),
(129, 3, 29, 20.00, 1, NULL),
(130, 3, 30, 20.00, 1, NULL),
(131, 3, 31, 20.00, 1, NULL),
(132, 3, 11, 50.00, 1, NULL),
(133, 3, 32, 50.00, 1, NULL),
(134, 3, 33, 50.00, 1, NULL),
(135, 4, 1, 20.00, 1, NULL),
(136, 4, 2, 20.00, 1, NULL),
(137, 4, 3, 20.00, 1, NULL),
(138, 4, 4, 20.00, 1, NULL),
(139, 4, 9, 20.00, 1, NULL),
(140, 4, 10, 20.00, 1, NULL),
(141, 4, 12, 20.00, 1, NULL),
(142, 4, 13, 20.00, 1, NULL),
(143, 4, 14, 20.00, 1, NULL),
(144, 4, 15, 20.00, 1, NULL),
(145, 4, 16, 20.00, 1, NULL),
(146, 4, 17, 20.00, 1, NULL),
(147, 4, 18, 20.00, 1, NULL),
(148, 4, 19, 20.00, 1, NULL),
(149, 4, 20, 20.00, 1, NULL),
(150, 4, 21, 20.00, 1, NULL),
(151, 4, 5, 20.00, 1, NULL),
(152, 4, 6, 20.00, 1, NULL),
(153, 4, 7, 20.00, 1, NULL),
(154, 4, 8, 20.00, 1, NULL),
(155, 4, 22, 20.00, 1, NULL),
(156, 4, 23, 20.00, 1, NULL),
(157, 4, 24, 20.00, 1, NULL),
(158, 4, 25, 20.00, 1, NULL),
(159, 4, 26, 20.00, 1, NULL),
(160, 4, 27, 20.00, 1, NULL),
(161, 4, 28, 20.00, 1, NULL),
(162, 4, 29, 20.00, 1, NULL),
(163, 4, 30, 20.00, 1, NULL),
(164, 4, 31, 20.00, 1, NULL),
(165, 4, 11, 50.00, 1, NULL),
(166, 4, 32, 50.00, 1, NULL),
(167, 4, 33, 50.00, 1, NULL);

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
(2, 8, '10-D', 'Math');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_weights`
--
ALTER TABLE `grading_weights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
