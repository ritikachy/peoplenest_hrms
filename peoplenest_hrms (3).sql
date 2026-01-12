-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 03:18 PM
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
(2, 7, '2026-01-13', 'present', NULL, NULL, NULL, 1, '2026-01-12 05:44:32'),
(3, 12, '2026-01-13', 'absent', NULL, NULL, NULL, 1, '2026-01-12 05:44:32'),
(4, 10, '2026-01-13', 'present', NULL, NULL, NULL, 1, '2026-01-12 05:44:32'),
(5, 13, '2026-01-13', 'absent', NULL, NULL, NULL, 1, '2026-01-12 05:44:32');

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
(1, 'sita', 'rit@gmail.com', '9875431456', 'developer', 2, NULL, 'selected', '2026-01-28 13:13:00', NULL, 1, '2025-12-23 07:04:36', '2026-01-11 07:34:47'),
(3, 'Nira Thapa', 'nira123@peoplenest.com', '9800234567', 'junior developer', 4, 'assets/uploads/resumes/1768123100_BIM-6th-Semester-Syllabus-2024.pdf', 'rejected', '2026-01-11 15:04:00', NULL, NULL, '2026-01-11 09:18:20', '2026-01-11 09:19:17'),
(4, 'samira magar', 'samira506@peoplenest.com', '9856990566', 'accountant', 3, 'assets/uploads/resumes/1768123288_BIM-6th-Semester-Syllabus-2024.pdf', '', '2026-01-13 15:06:00', NULL, NULL, '2026-01-11 09:21:28', '2026-01-12 10:13:46');

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
(7, 4, 'Emp004', 'Aayush', 'Sharma', 'aayush.sharma@peoplenest.com', '', 'IT', 'Developer', '2026-01-06', 0.00, 'active', '2026-01-06 14:18:42', '2026-01-06 14:18:42'),
(10, 8, 'Emp105', 'Ritika', 'Chaudhary', 'ritikachy002@peoplenest.com', '9827737372', 'Finance', 'finance manager', '2026-01-10', 60000.00, 'active', '2026-01-10 07:37:21', '2026-01-10 07:37:21'),
(12, 10, 'Emp107', 'Kim', 'Jung', 'kimjung012@peoplenest.com', '9827272735', 'Marketing', 'senior marketing manager', '2026-01-02', 60000.00, 'active', '2026-01-11 07:01:52', '2026-01-11 07:01:52'),
(13, 11, 'Emp108', 'sita', 'chaudhary', 'rit@gmail.com', '9875431456', 'IT', 'developer', '2026-01-11', 50000.00, 'active', '2026-01-11 08:10:48', '2026-01-12 02:32:59'),
(14, 13, 'Emp109', 'samira', 'magar', 'samira506@peoplenest.com', '9856990566', 'IT', 'accountant', '2026-01-12', NULL, 'active', '2026-01-12 10:13:46', '2026-01-12 10:13:46');

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
(4, 7, 'casual', '2026-01-20', '2026-01-21', 2, 'bnmhnbjkn', 'rejected', 1, '2026-01-12 05:44:51', 'i=njkg', '2026-01-08 07:32:55', '2026-01-12 05:44:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(20) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `emp_id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'EMP001', 'admin', 'admin@peoplenest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-07-26 16:19:51', '2026-01-09 06:40:25'),
(4, 'EMP003', 'aayush.sharma@peoplenest.com', 'aayush.sharma@peoplenest.com', '$2y$10$JxXWkT5ck7JX7wLQ/rCPv.DAsGfPDXy77AqAsoJtGiAWFOKLhbZP2', 'employee', '2026-01-06 13:43:27', '2026-01-09 06:40:25'),
(8, 'Emp105', 'ritikachy002@peoplenest.com', 'ritikachy002@peoplenest.com', '$2y$10$oFHDhv0lykOyU184at53Q.iUhdKMwoIO5eS1c2RIcfOhlJGzerrmG', 'employee', '2026-01-10 07:37:21', '2026-01-10 07:37:21'),
(10, 'Emp107', 'kimjung', 'kimjung012@peoplenest.com', '$2y$10$E85AGPowhT1ioFW6.OKHpeqxa0JBc7mj05QFPhE2daQc6F2QqzYmG', 'employee', '2026-01-11 07:01:52', '2026-01-11 07:01:52'),
(11, 'Emp108', 'sitachaudhary', 'rit@gmail.com', '$2y$10$2Yi3DRQS.aNGs5c9XxioBuFUBCwjctXjOYfeTO45w9tKoRC3hu4Ga', 'employee', '2026-01-11 08:10:48', '2026-01-11 08:10:48'),
(13, 'Emp109', 'samiramagar', 'samira506@peoplenest.com', '$2y$10$zIdy6KKLMFzQucSl86XxquWNZwxI4Pv.KDfGwP3PYkbU2f5mEgO4O', 'employee', '2026-01-12 10:13:46', '2026-01-12 10:13:46');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
