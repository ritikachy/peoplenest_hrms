-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 06:56 AM
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
-- Database: `peoplenest_hrms`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','leave') NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `status`, `check_in_time`, `check_out_time`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, '2026-01-08', 'present', NULL, NULL, NULL, 1, '2026-01-08 04:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `position` varchar(50) NOT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','interview_scheduled','selected','rejected') DEFAULT 'pending',
  `interview_date` datetime DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `email`, `phone`, `position`, `experience_years`, `resume_path`, `status`, `interview_date`, `interview_notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'sita', 'rit@gmail.com', '9875431456', 'developer', 2, NULL, 'pending', NULL, NULL, 1, '2025-12-23 07:04:36', '2025-12-23 07:04:36');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `department` varchar(50) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `hire_date` date NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `designation`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'EMP001', 'John', 'Doe', 'john.doe@peoplenest.com', '+1234567890', 'IT', 'Software Developer', '2024-01-15', 75000.00, 'active', '2025-07-26 16:19:52', '2025-07-26 16:19:52'),
(2, NULL, 'EMP002', 'Ritu', 'Km', 'ritukim021@gmail.com', '9827345670', 'Finance', 'accounting ', '2001-04-06', 45.00, 'inactive', '2025-08-02 14:17:01', '2026-01-06 13:36:19'),
(3, NULL, '09', 'ram', 'cgy', 'rit@gmail.com', '', 'Finance', 'financer', '2025-12-19', 900.00, 'inactive', '2025-12-23 07:01:48', '2026-01-06 13:36:27'),
(4, NULL, '002', 'smriti ', 'khan', 'smirti123@gmail.com', '9845362784', 'Marketing', 'i am marketing employe', '2025-12-28', 30000.00, 'inactive', '2026-01-01 12:17:36', '2026-01-06 13:36:39'),
(5, NULL, '003', 'ritika', 'chaudhary', 'ritikachy059@gmail.com', '9845362784', 'Operations', 'i am manager', '2026-01-17', 800000.00, 'active', '2026-01-05 14:34:39', '2026-01-05 14:34:39'),
(7, 4, 'Emp004', 'Aayush', 'Sharma', 'aayush.sharma@peoplenest.com', '', 'IT', 'Developer', '2026-01-06', 0.00, 'active', '2026-01-06 14:18:42', '2026-01-06 14:18:42'),
(8, 6, 'Emp102', 'Riya', 'Singh', 'riya102@peoplenest.com', '9802345678', 'Finance', 'finance manager', '2024-06-06', 50000.00, 'active', '2026-01-06 14:49:54', '2026-01-06 14:49:54'),
(9, 7, 'Emp104', 'Meera', 'Joshi', 'meera104@peoplenest.com', '9804567890', 'Marketing', ' marketing employee', '2025-01-06', 43000.00, 'active', '2026-01-06 15:06:46', '2026-01-06 15:06:46');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','casual','annual','maternity','emergency') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `days_requested`, `reason`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 'sick', '2025-08-04', '2025-08-06', 3, 'I am extremely sick please i grant leave for few days accept my leave.', 'pending', NULL, NULL, NULL, '2025-08-02 14:22:00', '2025-08-02 14:22:00'),
(2, 1, 'sick', '2025-12-22', '2025-12-24', 3, 'i am sick and suffering from fever ', 'pending', NULL, NULL, NULL, '2025-12-22 04:21:30', '2025-12-22 04:21:30'),
(3, 1, 'emergency', '2025-12-25', '2025-12-26', 2, 'fghdrt', 'approved', 1, '2026-01-01 12:17:55', NULL, '2025-12-23 07:07:11', '2026-01-01 12:17:55'),
(4, 7, 'casual', '2026-01-20', '2026-01-21', 2, 'bnmhnbjkn', 'pending', NULL, NULL, NULL, '2026-01-08 07:32:55', '2026-01-08 07:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@peoplenest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-07-26 16:19:51', '2025-07-26 16:19:51'),
(2, 'john.doe', 'john.doe@peoplenest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', '2025-07-26 16:19:52', '2025-07-26 16:19:52'),
(4, 'aayush.sharma@peoplenest.com', 'aayush.sharma@peoplenest.com', '$2y$10$JxXWkT5ck7JX7wLQ/rCPv.DAsGfPDXy77AqAsoJtGiAWFOKLhbZP2', 'employee', '2026-01-06 13:43:27', '2026-01-06 13:43:27'),
(6, 'riya102@peoplenest.com', 'riya102@peoplenest.com', '$2y$10$D58ito8gqOMN7pfUt3SyW.zShYmjjy1AAhA63FPB/LTNsCXAz8.uW', 'employee', '2026-01-06 14:49:54', '2026-01-06 14:49:54'),
(7, 'meera104@peoplenest.com', 'meera104@peoplenest.com', '$2y$10$6VbbopcFG6.DrX8iwsgqseUeqLJwwoETV0bRh2hB/1SoPLSvQDG9.', 'employee', '2026-01-06 15:06:46', '2026-01-06 15:06:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_date` (`employee_id`,`date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
