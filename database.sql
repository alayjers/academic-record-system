-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2026 at 07:07 PM
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
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('written','performance','exam') NOT NULL,
  `max_score` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `name`, `category`, `max_score`, `weight`, `semester`, `subject_id`) VALUES
(78, 'WW1', 'written', 30, 4.00, 1, 3),
(79, 'WW2', 'written', 15, 4.00, 1, 3),
(80, 'WW3', 'written', 15, 4.00, 1, 3),
(81, 'WW4', 'written', 10, 4.00, 1, 3),
(82, 'WW5', 'written', 15, 4.00, 1, 3),
(83, 'PT1', 'performance', 100, 16.67, 1, 3),
(84, 'PT2', 'performance', 100, 16.67, 1, 3),
(85, 'PT3', 'performance', 100, 16.66, 1, 3),
(86, 'ST1', 'exam', 50, 10.00, 1, 3),
(87, 'ST2', 'exam', 50, 10.00, 1, 3),
(88, 'TE', 'exam', 50, 10.00, 1, 3),
(89, 'WW1', 'written', 10, 4.00, 2, 3),
(90, 'WW2', 'written', 15, 4.00, 2, 3),
(91, 'WW3', 'written', 15, 4.00, 2, 3),
(92, 'WW4', 'written', 10, 4.00, 2, 3),
(93, 'WW5', 'written', 15, 4.00, 2, 3),
(94, 'PT1', 'performance', 100, 16.67, 2, 3),
(95, 'PT2', 'performance', 100, 16.67, 2, 3),
(96, 'PT3', 'performance', 100, 16.66, 2, 3),
(97, 'ST1', 'exam', 50, 10.00, 2, 3),
(98, 'ST2', 'exam', 50, 10.00, 2, 3),
(99, 'TE', 'exam', 50, 10.00, 2, 3),
(100, 'WW1', 'written', 10, 4.00, 3, 3),
(101, 'WW2', 'written', 15, 4.00, 3, 3),
(102, 'WW3', 'written', 15, 4.00, 3, 3),
(103, 'WW4', 'written', 10, 4.00, 3, 3),
(104, 'WW5', 'written', 15, 4.00, 3, 3),
(105, 'PT1', 'performance', 100, 16.67, 3, 3),
(106, 'PT2', 'performance', 100, 16.67, 3, 3),
(107, 'PT3', 'performance', 100, 16.66, 3, 3),
(108, 'ST1', 'exam', 50, 10.00, 3, 3),
(109, 'ST2', 'exam', 50, 10.00, 3, 3),
(110, 'TE', 'exam', 50, 10.00, 3, 3),
(137, 'WW1', 'written', 30, 4.00, 1, 2),
(138, 'WW2', 'written', 15, 4.00, 1, 2),
(139, 'WW3', 'written', 15, 4.00, 1, 2),
(140, 'WW4', 'written', 10, 4.00, 1, 2),
(141, 'WW5', 'written', 15, 4.00, 1, 2),
(142, 'PT1', 'performance', 100, 16.67, 1, 2),
(143, 'PT2', 'performance', 100, 16.67, 1, 2),
(144, 'PT3', 'performance', 100, 16.66, 1, 2),
(145, 'ST1', 'exam', 50, 10.00, 1, 2),
(146, 'ST2', 'exam', 50, 10.00, 1, 2),
(147, 'TE', 'exam', 50, 10.00, 1, 2),
(148, 'WW1', 'written', 30, 4.00, 2, 2),
(149, 'WW2', 'written', 15, 4.00, 2, 2),
(150, 'WW3', 'written', 15, 4.00, 2, 2),
(151, 'WW4', 'written', 10, 4.00, 2, 2),
(152, 'WW5', 'written', 15, 4.00, 2, 2),
(153, 'PT1', 'performance', 100, 16.67, 2, 2),
(154, 'PT2', 'performance', 100, 16.67, 2, 2),
(155, 'PT3', 'performance', 100, 16.66, 2, 2),
(156, 'ST1', 'exam', 50, 10.00, 2, 2),
(157, 'ST2', 'exam', 50, 10.00, 2, 2),
(158, 'TE', 'exam', 50, 10.00, 2, 2),
(159, 'WW1', 'written', 30, 4.00, 3, 2),
(160, 'WW2', 'written', 15, 4.00, 3, 2),
(161, 'WW3', 'written', 15, 4.00, 3, 2),
(162, 'WW4', 'written', 10, 4.00, 3, 2),
(163, 'WW5', 'written', 15, 4.00, 3, 2),
(164, 'PT1', 'performance', 100, 16.67, 3, 2),
(165, 'PT2', 'performance', 100, 16.67, 3, 2),
(166, 'PT3', 'performance', 100, 16.66, 3, 2),
(167, 'ST1', 'exam', 50, 10.00, 3, 2),
(168, 'ST2', 'exam', 50, 10.00, 3, 2),
(169, 'TE', 'exam', 50, 10.00, 3, 2),
(170, 'WW1', 'written', 30, 4.00, 1, 1),
(171, 'WW2', 'written', 15, 4.00, 1, 1),
(172, 'WW3', 'written', 15, 4.00, 1, 1),
(173, 'WW4', 'written', 10, 4.00, 1, 1),
(174, 'WW5', 'written', 15, 4.00, 1, 1),
(175, 'PT1', 'performance', 100, 16.67, 1, 1),
(176, 'PT2', 'performance', 100, 16.67, 1, 1),
(177, 'PT3', 'performance', 100, 16.66, 1, 1),
(178, 'ST1', 'exam', 50, 10.00, 1, 1),
(179, 'ST2', 'exam', 50, 10.00, 1, 1),
(180, 'TE', 'exam', 50, 10.00, 1, 1),
(181, 'WW1', 'written', 30, 4.00, 2, 1),
(182, 'WW2', 'written', 15, 4.00, 2, 1),
(183, 'WW3', 'written', 15, 4.00, 2, 1),
(184, 'WW4', 'written', 10, 4.00, 2, 1),
(185, 'WW5', 'written', 15, 4.00, 2, 1),
(186, 'PT1', 'performance', 100, 16.67, 2, 1),
(187, 'PT2', 'performance', 100, 16.67, 2, 1),
(188, 'PT3', 'performance', 100, 16.66, 2, 1),
(189, 'ST1', 'exam', 50, 10.00, 2, 1),
(190, 'ST2', 'exam', 50, 10.00, 2, 1),
(191, 'TE', 'exam', 50, 10.00, 2, 1),
(192, 'WW1', 'written', 30, 4.00, 3, 1),
(193, 'WW2', 'written', 15, 4.00, 3, 1),
(194, 'WW3', 'written', 15, 4.00, 3, 1),
(195, 'WW4', 'written', 10, 4.00, 3, 1),
(196, 'WW5', 'written', 15, 4.00, 3, 1),
(197, 'PT1', 'performance', 100, 16.67, 3, 1),
(198, 'PT2', 'performance', 100, 16.67, 3, 1),
(199, 'PT3', 'performance', 100, 16.66, 3, 1),
(200, 'ST1', 'exam', 50, 10.00, 3, 1),
(201, 'ST2', 'exam', 50, 10.00, 3, 1),
(202, 'TE', 'exam', 50, 10.00, 3, 1),
(203, 'WW1', 'written', 30, 4.00, 1, 4),
(204, 'WW2', 'written', 15, 4.00, 1, 4),
(205, 'WW3', 'written', 15, 4.00, 1, 4),
(206, 'WW4', 'written', 10, 4.00, 1, 4),
(207, 'WW5', 'written', 15, 4.00, 1, 4),
(208, 'PT1', 'performance', 100, 16.67, 1, 4),
(209, 'PT2', 'performance', 100, 16.67, 1, 4),
(210, 'PT3', 'performance', 100, 16.66, 1, 4),
(211, 'ST1', 'exam', 50, 10.00, 1, 4),
(212, 'ST2', 'exam', 50, 10.00, 1, 4),
(213, 'TE', 'exam', 50, 10.00, 1, 4),
(214, 'WW1', 'written', 30, 4.00, 2, 4),
(215, 'WW2', 'written', 15, 4.00, 2, 4),
(216, 'WW3', 'written', 15, 4.00, 2, 4),
(217, 'WW4', 'written', 10, 4.00, 2, 4),
(218, 'WW5', 'written', 15, 4.00, 2, 4),
(219, 'PT1', 'performance', 100, 16.67, 2, 4),
(220, 'PT2', 'performance', 100, 16.67, 2, 4),
(221, 'PT3', 'performance', 100, 16.66, 2, 4),
(222, 'ST1', 'exam', 50, 10.00, 2, 4),
(223, 'ST2', 'exam', 50, 10.00, 2, 4),
(224, 'TE', 'exam', 50, 10.00, 2, 4),
(225, 'WW1', 'written', 30, 4.00, 3, 4),
(226, 'WW2', 'written', 15, 4.00, 3, 4),
(227, 'WW3', 'written', 15, 4.00, 3, 4),
(228, 'WW4', 'written', 10, 4.00, 3, 4),
(229, 'WW5', 'written', 15, 4.00, 3, 4),
(230, 'PT1', 'performance', 100, 16.67, 3, 4),
(231, 'PT2', 'performance', 100, 16.67, 3, 4),
(232, 'PT3', 'performance', 100, 16.66, 3, 4),
(233, 'ST1', 'exam', 50, 10.00, 3, 4),
(234, 'ST2', 'exam', 50, 10.00, 3, 4),
(235, 'TE', 'exam', 50, 10.00, 3, 4),
(236, 'WW1', 'written', 30, 4.00, 1, 5),
(237, 'WW2', 'written', 15, 4.00, 1, 5),
(238, 'WW3', 'written', 15, 4.00, 1, 5),
(239, 'WW4', 'written', 10, 4.00, 1, 5),
(240, 'WW5', 'written', 15, 4.00, 1, 5),
(241, 'PT1', 'performance', 100, 16.67, 1, 5),
(242, 'PT2', 'performance', 100, 16.67, 1, 5),
(243, 'PT3', 'performance', 100, 16.66, 1, 5),
(244, 'ST1', 'exam', 50, 10.00, 1, 5),
(245, 'ST2', 'exam', 50, 10.00, 1, 5),
(246, 'TE', 'exam', 50, 10.00, 1, 5),
(247, 'WW1', 'written', 30, 4.00, 2, 5),
(248, 'WW2', 'written', 15, 4.00, 2, 5),
(249, 'WW3', 'written', 15, 4.00, 2, 5),
(250, 'WW4', 'written', 10, 4.00, 2, 5),
(251, 'WW5', 'written', 15, 4.00, 2, 5),
(252, 'PT1', 'performance', 100, 16.67, 2, 5),
(253, 'PT2', 'performance', 100, 16.67, 2, 5),
(254, 'PT3', 'performance', 100, 16.66, 2, 5),
(255, 'ST1', 'exam', 50, 10.00, 2, 5),
(256, 'ST2', 'exam', 50, 10.00, 2, 5),
(257, 'TE', 'exam', 50, 10.00, 2, 5),
(258, 'WW1', 'written', 30, 4.00, 3, 5),
(259, 'WW2', 'written', 15, 4.00, 3, 5),
(260, 'WW3', 'written', 15, 4.00, 3, 5),
(261, 'WW4', 'written', 10, 4.00, 3, 5),
(262, 'WW5', 'written', 15, 4.00, 3, 5),
(263, 'PT1', 'performance', 100, 16.67, 3, 5),
(264, 'PT2', 'performance', 100, 16.67, 3, 5),
(265, 'PT3', 'performance', 100, 16.66, 3, 5),
(266, 'ST1', 'exam', 50, 10.00, 3, 5),
(267, 'ST2', 'exam', 50, 10.00, 3, 5),
(268, 'TE', 'exam', 50, 10.00, 3, 5),
(269, 'WW1', 'written', 30, 4.00, 1, 6),
(270, 'WW2', 'written', 15, 4.00, 1, 6),
(271, 'WW3', 'written', 15, 4.00, 1, 6),
(272, 'WW4', 'written', 10, 4.00, 1, 6),
(273, 'WW5', 'written', 15, 4.00, 1, 6),
(274, 'PT1', 'performance', 100, 16.67, 1, 6),
(275, 'PT2', 'performance', 100, 16.67, 1, 6),
(276, 'PT3', 'performance', 100, 16.66, 1, 6),
(277, 'ST1', 'exam', 50, 10.00, 1, 6),
(278, 'ST2', 'exam', 50, 10.00, 1, 6),
(279, 'TE', 'exam', 50, 10.00, 1, 6),
(280, 'WW1', 'written', 30, 4.00, 2, 6),
(281, 'WW2', 'written', 15, 4.00, 2, 6),
(282, 'WW3', 'written', 15, 4.00, 2, 6),
(283, 'WW4', 'written', 10, 4.00, 2, 6),
(284, 'WW5', 'written', 15, 4.00, 2, 6),
(285, 'PT1', 'performance', 100, 16.67, 2, 6),
(286, 'PT2', 'performance', 100, 16.67, 2, 6),
(287, 'PT3', 'performance', 100, 16.66, 2, 6),
(288, 'ST1', 'exam', 50, 10.00, 2, 6),
(289, 'ST2', 'exam', 50, 10.00, 2, 6),
(290, 'TE', 'exam', 50, 10.00, 2, 6),
(291, 'WW1', 'written', 30, 4.00, 3, 6),
(292, 'WW2', 'written', 15, 4.00, 3, 6),
(293, 'WW3', 'written', 15, 4.00, 3, 6),
(294, 'WW4', 'written', 10, 4.00, 3, 6),
(295, 'WW5', 'written', 15, 4.00, 3, 6),
(296, 'PT1', 'performance', 100, 16.67, 3, 6),
(297, 'PT2', 'performance', 100, 16.67, 3, 6),
(298, 'PT3', 'performance', 100, 16.66, 3, 6),
(299, 'ST1', 'exam', 50, 10.00, 3, 6),
(300, 'ST2', 'exam', 50, 10.00, 3, 6),
(301, 'TE', 'exam', 50, 10.00, 3, 6),
(302, 'WW1', 'written', 30, 4.00, 1, 7),
(303, 'WW2', 'written', 15, 4.00, 1, 7),
(304, 'WW3', 'written', 15, 4.00, 1, 7),
(305, 'WW4', 'written', 10, 4.00, 1, 7),
(306, 'WW5', 'written', 15, 4.00, 1, 7),
(307, 'PT1', 'performance', 100, 16.67, 1, 7),
(308, 'PT2', 'performance', 100, 16.67, 1, 7),
(309, 'PT3', 'performance', 100, 16.66, 1, 7),
(310, 'ST1', 'exam', 50, 10.00, 1, 7),
(311, 'ST2', 'exam', 50, 10.00, 1, 7),
(312, 'TE', 'exam', 50, 10.00, 1, 7),
(313, 'WW1', 'written', 30, 4.00, 2, 7),
(314, 'WW2', 'written', 15, 4.00, 2, 7),
(315, 'WW3', 'written', 15, 4.00, 2, 7),
(316, 'WW4', 'written', 10, 4.00, 2, 7),
(317, 'WW5', 'written', 15, 4.00, 2, 7),
(318, 'PT1', 'performance', 100, 16.67, 2, 7),
(319, 'PT2', 'performance', 100, 16.67, 2, 7),
(320, 'PT3', 'performance', 100, 16.66, 2, 7),
(321, 'ST1', 'exam', 50, 10.00, 2, 7),
(322, 'ST2', 'exam', 50, 10.00, 2, 7),
(323, 'TE', 'exam', 50, 10.00, 2, 7),
(324, 'WW1', 'written', 30, 4.00, 3, 7),
(325, 'WW2', 'written', 15, 4.00, 3, 7),
(326, 'WW3', 'written', 15, 4.00, 3, 7),
(327, 'WW4', 'written', 10, 4.00, 3, 7),
(328, 'WW5', 'written', 15, 4.00, 3, 7),
(329, 'PT1', 'performance', 100, 16.67, 3, 7),
(330, 'PT2', 'performance', 100, 16.67, 3, 7),
(331, 'PT3', 'performance', 100, 16.66, 3, 7),
(332, 'ST1', 'exam', 50, 10.00, 3, 7),
(333, 'ST2', 'exam', 50, 10.00, 3, 7),
(334, 'TE', 'exam', 50, 10.00, 3, 7),
(335, 'WW1', 'written', 30, 4.00, 1, 8),
(336, 'WW2', 'written', 15, 4.00, 1, 8),
(337, 'WW3', 'written', 15, 4.00, 1, 8),
(338, 'WW4', 'written', 10, 4.00, 1, 8),
(339, 'WW5', 'written', 15, 4.00, 1, 8),
(340, 'PT1', 'performance', 100, 16.67, 1, 8),
(341, 'PT2', 'performance', 100, 16.67, 1, 8),
(342, 'PT3', 'performance', 100, 16.66, 1, 8),
(343, 'ST1', 'exam', 50, 10.00, 1, 8),
(344, 'ST2', 'exam', 50, 10.00, 1, 8),
(345, 'TE', 'exam', 50, 10.00, 1, 8),
(346, 'WW1', 'written', 30, 4.00, 2, 8),
(347, 'WW2', 'written', 15, 4.00, 2, 8),
(348, 'WW3', 'written', 15, 4.00, 2, 8),
(349, 'WW4', 'written', 10, 4.00, 2, 8),
(350, 'WW5', 'written', 15, 4.00, 2, 8),
(351, 'PT1', 'performance', 100, 16.67, 2, 8),
(352, 'PT2', 'performance', 100, 16.67, 2, 8),
(353, 'PT3', 'performance', 100, 16.66, 2, 8),
(354, 'ST1', 'exam', 50, 10.00, 2, 8),
(355, 'ST2', 'exam', 50, 10.00, 2, 8),
(356, 'TE', 'exam', 50, 10.00, 2, 8),
(357, 'WW1', 'written', 30, 4.00, 3, 8),
(358, 'WW2', 'written', 15, 4.00, 3, 8),
(359, 'WW3', 'written', 15, 4.00, 3, 8),
(360, 'WW4', 'written', 10, 4.00, 3, 8),
(361, 'WW5', 'written', 15, 4.00, 3, 8),
(362, 'PT1', 'performance', 100, 16.67, 3, 8),
(363, 'PT2', 'performance', 100, 16.67, 3, 8),
(364, 'PT3', 'performance', 100, 16.66, 3, 8),
(365, 'ST1', 'exam', 50, 10.00, 3, 8),
(366, 'ST2', 'exam', 50, 10.00, 3, 8),
(367, 'TE', 'exam', 50, 10.00, 3, 8);

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
(40, 3, 'UPDATE_MAX_SCORE', 'Assignment ID 78: max_score changed from 25 to 30', '2026-06-03 10:26:12'),
(41, 16, 'UPDATE_SCORE', 'Student 20, Assignment 78, Semester 1: score changed from NULL to 30', '2026-06-10 06:13:16'),
(42, 16, 'UPDATE_SCORE', 'Student 20, Assignment 79, Semester 1: score changed from NULL to 14', '2026-06-10 06:13:16'),
(43, 16, 'UPDATE_SCORE', 'Student 20, Assignment 80, Semester 1: score changed from NULL to 14', '2026-06-10 06:13:16'),
(44, 16, 'UPDATE_SCORE', 'Student 20, Assignment 81, Semester 1: score changed from NULL to 9', '2026-06-10 06:13:16'),
(45, 16, 'UPDATE_SCORE', 'Student 20, Assignment 82, Semester 1: score changed from NULL to 14', '2026-06-10 06:13:16'),
(46, 16, 'UPDATE_SCORE', 'Student 20, Assignment 83, Semester 1: score changed from NULL to 90', '2026-06-10 06:13:16'),
(47, 16, 'UPDATE_SCORE', 'Student 20, Assignment 84, Semester 1: score changed from NULL to 90', '2026-06-10 06:13:16'),
(48, 16, 'UPDATE_SCORE', 'Student 20, Assignment 85, Semester 1: score changed from NULL to 90', '2026-06-10 06:13:16'),
(49, 16, 'UPDATE_SCORE', 'Student 20, Assignment 86, Semester 1: score changed from NULL to 40', '2026-06-10 06:13:16'),
(50, 16, 'UPDATE_SCORE', 'Student 20, Assignment 87, Semester 1: score changed from NULL to 40', '2026-06-10 06:13:16'),
(51, 16, 'UPDATE_SCORE', 'Student 20, Assignment 88, Semester 1: score changed from NULL to 40', '2026-06-10 06:13:16');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Enrolled',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `section_id`, `status`, `enrolled_at`) VALUES
(1, 16, 1, 'Enrolled', '2026-06-11 13:37:00'),
(2, 17, 1, 'Enrolled', '2026-06-11 13:37:00'),
(3, 18, 2, 'Enrolled', '2026-06-11 13:37:00'),
(4, 19, 2, 'Enrolled', '2026-06-11 13:37:00'),
(5, 20, 3, 'Enrolled', '2026-06-11 13:37:00'),
(6, 21, 3, 'Enrolled', '2026-06-11 13:37:00');

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
(12, 16, 78, 10.00, 1, '2025-2026'),
(13, 16, 79, 14.00, 1, '2025-2026'),
(14, 16, 80, 14.00, 1, '2025-2026'),
(15, 16, 81, 10.00, 1, '2025-2026'),
(16, 16, 82, 14.00, 1, '2025-2026'),
(17, 16, 83, 90.00, 1, '2025-2026'),
(18, 16, 84, 90.00, 1, '2025-2026'),
(19, 16, 85, 90.00, 1, '2025-2026'),
(20, 16, 86, 40.00, 1, '2025-2026'),
(21, 16, 87, 40.00, 1, '2025-2026'),
(22, 16, 88, 40.00, 1, '2025-2026'),
(23, 17, 78, 12.00, 1, '2025-2026'),
(36, 20, 78, 30.00, 1, '2025-2026'),
(37, 20, 79, 14.00, 1, '2025-2026'),
(38, 20, 80, 14.00, 1, '2025-2026'),
(39, 20, 81, 9.00, 1, '2025-2026'),
(40, 20, 82, 14.00, 1, '2025-2026'),
(41, 20, 83, 90.00, 1, '2025-2026'),
(42, 20, 84, 90.00, 1, '2025-2026'),
(43, 20, 85, 90.00, 1, '2025-2026'),
(44, 20, 86, 40.00, 1, '2025-2026'),
(45, 20, 87, 40.00, 1, '2025-2026'),
(46, 20, 88, 40.00, 1, '2025-2026'),
(84, 18, 78, 12.00, 1, '2025-2026'),
(85, 18, 79, 15.00, 1, '2025-2026'),
(86, 18, 80, 15.00, 1, '2025-2026'),
(87, 18, 81, 10.00, 1, '2025-2026'),
(88, 18, 82, 15.00, 1, '2025-2026'),
(89, 18, 83, 90.00, 1, '2025-2026'),
(90, 18, 84, 90.00, 1, '2025-2026'),
(91, 18, 85, 90.00, 1, '2025-2026'),
(92, 18, 86, 30.00, 1, '2025-2026'),
(93, 18, 87, 30.00, 1, '2025-2026'),
(94, 18, 88, 0.00, 1, '2025-2026'),
(95, 19, 78, 12.50, 1, '2025-2026'),
(156, 16, 137, 29.00, 1, '2025-2026'),
(157, 16, 138, 14.00, 1, '2025-2026'),
(158, 16, 139, 14.00, 1, '2025-2026'),
(159, 16, 140, 9.00, 1, '2025-2026'),
(160, 16, 141, 14.00, 1, '2025-2026'),
(161, 16, 142, 90.00, 1, '2025-2026'),
(162, 16, 143, 90.00, 1, '2025-2026'),
(163, 16, 144, 90.00, 1, '2025-2026'),
(164, 16, 145, 40.00, 1, '2025-2026'),
(165, 16, 146, 40.00, 1, '2025-2026'),
(166, 16, 147, 40.00, 1, '2025-2026'),
(167, 16, 203, 29.00, 1, '2025-2026'),
(168, 16, 204, 14.00, 1, '2025-2026'),
(169, 16, 205, 14.00, 1, '2025-2026'),
(170, 16, 206, 9.00, 1, '2025-2026'),
(171, 16, 207, 14.00, 1, '2025-2026'),
(172, 16, 208, 90.00, 1, '2025-2026'),
(173, 16, 209, 90.00, 1, '2025-2026'),
(174, 16, 210, 90.00, 1, '2025-2026'),
(175, 16, 211, 40.00, 1, '2025-2026'),
(176, 16, 212, 40.00, 1, '2025-2026'),
(177, 16, 213, 40.00, 1, '2025-2026'),
(178, 16, 236, 29.00, 1, '2025-2026'),
(179, 16, 237, 14.00, 1, '2025-2026'),
(180, 16, 238, 14.00, 1, '2025-2026'),
(181, 16, 239, 9.00, 1, '2025-2026'),
(182, 16, 240, 14.00, 1, '2025-2026'),
(183, 16, 241, 90.00, 1, '2025-2026'),
(184, 16, 242, 90.00, 1, '2025-2026'),
(185, 16, 243, 90.00, 1, '2025-2026'),
(186, 16, 244, 40.00, 1, '2025-2026'),
(187, 16, 245, 40.00, 1, '2025-2026'),
(188, 16, 246, 40.00, 1, '2025-2026'),
(189, 16, 170, 29.00, 1, '2025-2026'),
(190, 16, 171, 14.00, 1, '2025-2026'),
(191, 16, 172, 14.00, 1, '2025-2026'),
(192, 16, 173, 9.00, 1, '2025-2026'),
(193, 16, 174, 14.00, 1, '2025-2026'),
(194, 16, 175, 90.00, 1, '2025-2026'),
(195, 16, 176, 90.00, 1, '2025-2026'),
(196, 16, 177, 90.00, 1, '2025-2026'),
(197, 16, 178, 40.00, 1, '2025-2026'),
(198, 16, 179, 40.00, 1, '2025-2026'),
(199, 16, 180, 40.00, 1, '2025-2026'),
(200, 16, 181, 29.00, 2, '2025-2026'),
(201, 16, 182, 14.00, 2, '2025-2026'),
(202, 16, 183, 14.00, 2, '2025-2026'),
(203, 16, 184, 9.00, 2, '2025-2026'),
(204, 16, 185, 14.00, 2, '2025-2026'),
(205, 16, 186, 90.00, 2, '2025-2026'),
(206, 16, 187, 90.00, 2, '2025-2026'),
(207, 16, 188, 90.00, 2, '2025-2026'),
(208, 16, 189, 40.00, 2, '2025-2026'),
(209, 16, 190, 40.00, 2, '2025-2026'),
(210, 16, 191, 40.00, 2, '2025-2026');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade_level` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`, `grade_level`, `school_year`, `created_at`) VALUES
(1, '1-A', 7, '2025-2026', '2026-06-11 13:37:00'),
(2, '1-B', 7, '2025-2026', '2026-06-11 13:37:00'),
(3, '1-C', 7, '2025-2026', '2026-06-11 13:37:00');

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
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_section`
--

INSERT INTO `teacher_subject_section` (`id`, `teacher_id`, `subject_id`, `section_id`) VALUES
(1, 16, 2, 1),
(2, 15, 1, 1),
(3, 8, 3, 1),
(4, 12, 4, 1),
(5, 14, 8, 1),
(6, 13, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `term_grades`
--

CREATE TABLE `term_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `term` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `final_grade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `term_grades`
--

INSERT INTO `term_grades` (`id`, `student_id`, `subject_id`, `term`, `school_year`, `final_grade`) VALUES
(1, 16, 2, 1, '2025-2026', 92),
(2, 17, 2, 1, '2025-2026', 0),
(3, 16, 4, 1, '2025-2026', 92),
(4, 17, 4, 1, '2025-2026', 0),
(5, 16, 5, 1, '2025-2026', 92),
(6, 17, 5, 1, '2025-2026', 0),
(7, 16, 1, 1, '2025-2026', 92),
(8, 17, 1, 1, '2025-2026', 0),
(9, 16, 1, 2, '2025-2026', 92),
(10, 17, 1, 2, '2025-2026', 0);

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
  ADD KEY `teacher_id` (`teacher_id`);

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
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_year` (`student_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_assignment` (`student_id`,`assignment_id`,`semester`,`school_year`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `term_grades`
--
ALTER TABLE `term_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject_term_year` (`student_id`,`subject_id`,`term`,`school_year`),
  ADD KEY `subject_id` (`subject_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `term_grades`
--
ALTER TABLE `term_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `teacher_subject_section_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `term_grades`
--
ALTER TABLE `term_grades`
  ADD CONSTRAINT `term_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `term_grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `students`
  DROP COLUMN name;