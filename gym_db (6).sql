-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 11:33 AM
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
-- Database: `gym_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `checkin_time` datetime DEFAULT current_timestamp(),
  `checkout_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `checkin_time`, `checkout_time`, `notes`, `created_by`) VALUES
(70, 190, '2026-01-21 10:06:50', '2026-01-21 10:07:04', NULL, NULL),
(71, 191, '2026-01-21 10:07:00', '2026-01-21 10:07:05', NULL, NULL),
(73, 193, '2026-01-23 09:09:17', '2026-01-23 09:09:24', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gym_settings`
--

CREATE TABLE `gym_settings` (
  `id` int(11) NOT NULL,
  `gym_name` varchar(255) NOT NULL DEFAULT 'Gym Management System',
  `logo_path` varchar(255) NOT NULL DEFAULT 'gym logo.jpg',
  `background_path` varchar(255) NOT NULL DEFAULT 'gym background.jpg',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sidebar_theme` varchar(50) NOT NULL DEFAULT 'primary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gym_settings`
--

INSERT INTO `gym_settings` (`id`, `gym_name`, `logo_path`, `background_path`, `updated_at`, `sidebar_theme`) VALUES
(1, 'Olympic Fitness Gym', 'gym_logo_1769151822.jpg', 'gym_background_1769151846.jpg', '2026-01-23 10:21:28', 'primary');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `member_code` varchar(50) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `plan` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('ACTIVE','EXPIRED','SUSPENDED') DEFAULT 'ACTIVE',
  `is_student` tinyint(1) DEFAULT 0,
  `student_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `member_code`, `fullname`, `email`, `phone`, `address`, `plan`, `start_date`, `end_date`, `status`, `is_student`, `student_id`, `created_at`, `created_by`, `qr_code`, `qr_token`) VALUES
(190, 'MEM2026C0C7EB', 'Test ', NULL, '', NULL, 'Per Session', '2026-01-21', '2026-01-21', 'EXPIRED', 1, '12', '2026-01-21 02:05:42', 2, 'qr_codes/6c48fe80b1becaf2fe74ff3c2049e07a7d790ac0d2c60c543c6353d5f4dbff6c.png', '6c48fe80b1becaf2fe74ff3c2049e07a7d790ac0d2c60c543c6353d5f4dbff6c'),
(191, 'MEM202668833B', 'Tyron Del Valle', '', '', '', '1 Month', '2026-01-21', '2026-02-21', 'ACTIVE', 0, NULL, '2026-01-21 02:06:17', 2, 'qr_codes/ac5e9f16cb7001828c57a5b027ac30bb04499b65951f78036eb7c3a3a29b3c12.png', 'ac5e9f16cb7001828c57a5b027ac30bb04499b65951f78036eb7c3a3a29b3c12'),
(193, 'MEM2026572480', 'cj', NULL, '', NULL, 'Per Session', '2026-01-23', '2026-01-23', 'ACTIVE', 0, '', '2026-01-23 01:08:59', 2, 'qr_codes/76a1f9d4dd44042e4e18b76239451e4999d2be2f9e41d52526bca15c209f5260.png', '76a1f9d4dd44042e4e18b76239451e4999d2be2f9e41d52526bca15c209f5260');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `is_student_discount` tinyint(1) DEFAULT 0,
  `student_id` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `reference_no` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `amount`, `receipt_no`, `payment_method`, `payment_date`, `notes`, `is_student_discount`, `student_id`, `discount_amount`, `reference_no`, `created_by`) VALUES
(154, 190, 50.00, 'R20260121C90599', NULL, '2026-01-21 00:00:00', NULL, 1, '12', 10.00, NULL, NULL),
(155, 191, 500.00, 'R20260121BE149E', 'GCash', '2026-01-21 00:00:00', NULL, 0, NULL, 0.00, '212', NULL),
(156, 193, 50.00, 'R20260123AA7882', NULL, '2026-01-23 00:00:00', NULL, 0, '', 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `on_duty` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `created_at`, `on_duty`) VALUES
(1, 'Gym Administrator', 'admin', '$2y$10$z0JNe43ik6B65LxzXVZTveM5vQJDEVrmvrf1CzAiXcJR6k3yK9hK6', 'admin', '2026-01-14 05:43:14', 0),
(2, 'Gym Cashier', 'cashier', '$2y$10$mNWq/LEmbhESmkANUdX4juSPg49P3/c3IWhdxlL0SVjcadI80tBOG', 'cashier', '2026-01-14 05:43:14', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `fk_attendance_created_by` (`created_by`);

--
-- Indexes for table `gym_settings`
--
ALTER TABLE `gym_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_code` (`member_code`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `fk_members_created_by` (`created_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `fk_payments_created_by` (`created_by`);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `gym_settings`
--
ALTER TABLE `gym_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_members_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
