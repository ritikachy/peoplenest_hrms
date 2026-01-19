-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2026 at 01:15 PM
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
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `status`, `check_in_time`, `latitude`, `longitude`, `check_out_time`, `notes`, `created_by`, `created_at`) VALUES
(6, 12, '2026-01-16', 'present', '13:53:54', NULL, NULL, '13:58:34', NULL, NULL, '2026-01-16 12:53:54'),
(7, 12, '2026-01-17', 'present', '04:44:42', NULL, NULL, '05:51:04', '', NULL, '2026-01-17 03:44:42'),
(9, 15, '2026-01-17', 'present', '11:30:19', 26.49113917, 87.29222967, '20:20:29', ' i need to live early so', NULL, '2026-01-17 05:45:19'),
(10, 10, '2026-01-17', 'present', '20:25:49', 26.49121433, 87.29216933, '20:26:21', 'wwergtbf', NULL, '2026-01-17 14:40:49'),
(11, 12, '2026-01-18', 'present', '16:46:11', 26.49136346, 87.29262550, '17:29:00', '', NULL, '2026-01-18 11:01:11'),
(21, 12, '2026-01-19', 'present', '14:52:37', 0.00000000, 0.00000000, NULL, NULL, NULL, '2026-01-19 09:07:37'),
(22, 16, '2026-01-19', 'present', '15:16:54', 0.00000000, 0.00000000, NULL, NULL, NULL, '2026-01-19 09:31:54');

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
(4, 'samira magar', 'samira506@peoplenest.com', '9856990566', 'accountant', 3, 'assets/uploads/resumes/1768123288_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', '2026-01-13 15:06:00', NULL, NULL, '2026-01-11 09:21:28', '2026-01-13 07:22:46'),
(5, 'Mansi Thapa', 'mansi034@peoplenest.com', '9801124367', 'marketing', 3, 'assets/uploads/resumes/1768287339_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', NULL, NULL, NULL, '2026-01-13 06:55:39', '2026-01-13 07:22:46'),
(6, 'Ram Kumar', 'Ram342@peoplenest.com', '982146718', 'operation', 3, 'assets/uploads/resumes/1768303600_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', '2026-01-22 17:21:00', NULL, NULL, '2026-01-13 11:26:40', '2026-01-13 12:52:28'),
(7, 'Nayan Kumar', 'Nayan034@peoplenest.com', '9824671842', 'marketing', 4, 'assets/uploads/resumes/1768316991_UNIT_3_Intellectual_Property.pdf', 'interview_scheduled', '2026-01-13 20:56:00', NULL, NULL, '2026-01-13 15:09:51', '2026-01-16 08:13:20'),
(8, 'Sudip Thapa', 'sudip567@peoplenest.com', '9856712467', 'manager', 3, 'assets/uploads/resumes/1768317475_UNIT_5_FUNDAMENTALS_OF_CYBERSECURITY.pdf', 'selected', NULL, NULL, NULL, '2026-01-13 15:17:55', '2026-01-14 05:46:55'),
(9, 'Hari Kumar', 'hari354@peoplenest.com', '9846732460', 'junior accountant', 2, 'assets/uploads/resumes/1768547527_Unit_2___Ethics_for_IT_Workers_and_IT_Users.pdf', 'pending', NULL, NULL, NULL, '2026-01-16 07:12:07', '2026-01-16 07:12:07');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_employee`
--

CREATE TABLE `candidate_employee` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `hired_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_employee`
--

INSERT INTO `candidate_employee` (`id`, `candidate_id`, `employee_id`, `hired_at`) VALUES
(1, 5, 15, '2026-01-13 06:59:30'),
(2, 4, 14, '2026-01-13 07:21:34'),
(4, 1, 13, '2026-01-13 07:22:13'),
(5, 6, 16, '2026-01-13 12:28:21');

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
  `leave_balance` int(11) DEFAULT 20,
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

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name`, `last_name`, `leave_balance`, `email`, `phone`, `department`, `designation`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(7, 4, 'Emp004', 'Aayush', 'Sharma', 20, 'aayush.sharma@peoplenest.com', '', 'IT', 'Developer', '2026-01-06', 0.00, 'active', '2026-01-06 14:18:42', '2026-01-06 14:18:42'),
(10, 8, 'Emp105', 'Ritika', 'Chaudhary', 20, 'ritikachy002@peoplenest.com', '9827737372', 'Finance', 'finance manager', '2026-01-10', 60000.00, 'active', '2026-01-10 07:37:21', '2026-01-10 07:37:21'),
(12, 10, 'Emp107', 'Kim', 'Jung', 14, 'kimjung012@peoplenest.com', '9827272735', 'Marketing', 'senior marketing manager', '2026-01-02', 60000.00, 'active', '2026-01-11 07:01:52', '2026-01-19 09:02:30'),
(13, 11, 'Emp108', 'sita', 'chaudhary', 20, 'rit@gmail.com', '9875431456', 'IT', 'developer', '2026-01-11', 50000.00, 'active', '2026-01-11 08:10:48', '2026-01-12 02:32:59'),
(14, 13, 'Emp109', 'samira', 'magar', 20, 'samira506@peoplenest.com', '9856990566', 'IT', 'accountant', '2026-01-12', NULL, 'active', '2026-01-12 10:13:46', '2026-01-12 10:13:46'),
(15, 14, 'Emp110', 'Mansi', 'Thapa', 20, 'mansi034@peoplenest.com', '9801124367', 'IT', 'marketing', '2026-01-13', NULL, 'active', '2026-01-13 06:59:30', '2026-01-13 06:59:30'),
(16, 15, 'Emp111', 'Ram', 'Kumar', 17, 'Ram342@peoplenest.com', '982146718', 'IT', 'operation', '2026-01-13', NULL, 'active', '2026-01-13 12:28:21', '2026-01-19 05:16:09');

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
(4, 7, 'casual', '2026-01-20', '2026-01-21', 2, 'bnmhnbjkn', 'rejected', 1, '2026-01-12 05:44:51', 'i=njkg', '2026-01-08 07:32:55', '2026-01-12 05:44:51'),
(5, 12, 'annual', '2026-01-20', '2026-01-23', 4, 'i have some important work', 'approved', 1, '2026-01-19 04:12:15', NULL, '2026-01-19 03:10:52', '2026-01-19 04:12:15'),
(6, 15, 'maternity', '2026-01-19', '2026-01-27', 9, 'i want maternity leave', 'approved', 1, '2026-01-19 04:56:28', '', '2026-01-19 04:14:25', '2026-01-19 04:56:28'),
(7, 16, 'emergency', '2026-01-19', '2026-01-21', 3, 'i need emergency leave', 'approved', 1, '2026-01-19 05:16:09', NULL, '2026-01-19 05:02:36', '2026-01-19 05:16:09'),
(8, 10, 'sick', '2026-01-19', '2026-01-21', 3, 'i am sick', 'pending', NULL, NULL, NULL, '2026-01-19 05:25:29', '2026-01-19 05:25:29'),
(9, 12, 'casual', '2026-01-23', '2026-01-24', 2, 'i need holiday', 'pending', NULL, NULL, NULL, '2026-01-19 08:36:58', '2026-01-19 08:36:58'),
(10, 12, 'annual', '2026-01-22', '2026-01-23', 2, 'i need leave', 'approved', 1, '2026-01-19 09:02:30', NULL, '2026-01-19 09:01:42', '2026-01-19 09:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 10, 'Your leave request (annual) from 2026-01-20 has been APPROVED. ????', 1, '2026-01-19 04:12:15'),
(2, 14, 'Your leave request has been APPROVED.', 1, '2026-01-19 04:56:28'),
(3, 15, 'Your leave request (emergency) from 2026-01-19 has been APPROVED. ????', 1, '2026-01-19 05:16:09'),
(4, 1, 'New Annual leave request from Kim Jung (2 days).', 1, '2026-01-19 09:01:42'),
(5, 10, 'Your leave request (annual) from 2026-01-22 has been APPROVED. ????', 1, '2026-01-19 09:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('recruitment_status', 'open');

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
(13, 'Emp109', 'samiramagar', 'samira506@peoplenest.com', '$2y$10$zIdy6KKLMFzQucSl86XxquWNZwxI4Pv.KDfGwP3PYkbU2f5mEgO4O', 'employee', '2026-01-12 10:13:46', '2026-01-12 10:13:46'),
(14, 'Emp110', 'mansithapa', 'mansi034@peoplenest.com', '$2y$10$9YmWRIPj3WSc.Yde0cQs.OCQjS5hgXJptyYqhTo9p//kRkigbsuWy', 'employee', '2026-01-13 06:59:30', '2026-01-13 06:59:30'),
(15, 'Emp111', 'ramkumar', 'Ram342@peoplenest.com', '$2y$10$f2p.uwexWSJUie8O.zwoPe4xK1PJnxTPPOIdBJdWaV0ytCU5Az5Y.', 'employee', '2026-01-13 12:28:21', '2026-01-13 12:28:21');

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
  ADD UNIQUE KEY `unique_application` (`email`,`position`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hire` (`candidate_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Constraints for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  ADD CONSTRAINT `candidate_employee_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_employee_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

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
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2026 at 01:15 PM
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
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `status`, `check_in_time`, `latitude`, `longitude`, `check_out_time`, `notes`, `created_by`, `created_at`) VALUES
(6, 12, '2026-01-16', 'present', '13:53:54', NULL, NULL, '13:58:34', NULL, NULL, '2026-01-16 12:53:54'),
(7, 12, '2026-01-17', 'present', '04:44:42', NULL, NULL, '05:51:04', '', NULL, '2026-01-17 03:44:42'),
(9, 15, '2026-01-17', 'present', '11:30:19', 26.49113917, 87.29222967, '20:20:29', ' i need to live early so', NULL, '2026-01-17 05:45:19'),
(10, 10, '2026-01-17', 'present', '20:25:49', 26.49121433, 87.29216933, '20:26:21', 'wwergtbf', NULL, '2026-01-17 14:40:49'),
(11, 12, '2026-01-18', 'present', '16:46:11', 26.49136346, 87.29262550, '17:29:00', '', NULL, '2026-01-18 11:01:11'),
(21, 12, '2026-01-19', 'present', '14:52:37', 0.00000000, 0.00000000, NULL, NULL, NULL, '2026-01-19 09:07:37'),
(22, 16, '2026-01-19', 'present', '15:16:54', 0.00000000, 0.00000000, NULL, NULL, NULL, '2026-01-19 09:31:54');

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
(4, 'samira magar', 'samira506@peoplenest.com', '9856990566', 'accountant', 3, 'assets/uploads/resumes/1768123288_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', '2026-01-13 15:06:00', NULL, NULL, '2026-01-11 09:21:28', '2026-01-13 07:22:46'),
(5, 'Mansi Thapa', 'mansi034@peoplenest.com', '9801124367', 'marketing', 3, 'assets/uploads/resumes/1768287339_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', NULL, NULL, NULL, '2026-01-13 06:55:39', '2026-01-13 07:22:46'),
(6, 'Ram Kumar', 'Ram342@peoplenest.com', '982146718', 'operation', 3, 'assets/uploads/resumes/1768303600_BIM-6th-Semester-Syllabus-2024.pdf', 'selected', '2026-01-22 17:21:00', NULL, NULL, '2026-01-13 11:26:40', '2026-01-13 12:52:28'),
(7, 'Nayan Kumar', 'Nayan034@peoplenest.com', '9824671842', 'marketing', 4, 'assets/uploads/resumes/1768316991_UNIT_3_Intellectual_Property.pdf', 'interview_scheduled', '2026-01-13 20:56:00', NULL, NULL, '2026-01-13 15:09:51', '2026-01-16 08:13:20'),
(8, 'Sudip Thapa', 'sudip567@peoplenest.com', '9856712467', 'manager', 3, 'assets/uploads/resumes/1768317475_UNIT_5_FUNDAMENTALS_OF_CYBERSECURITY.pdf', 'selected', NULL, NULL, NULL, '2026-01-13 15:17:55', '2026-01-14 05:46:55'),
(9, 'Hari Kumar', 'hari354@peoplenest.com', '9846732460', 'junior accountant', 2, 'assets/uploads/resumes/1768547527_Unit_2___Ethics_for_IT_Workers_and_IT_Users.pdf', 'pending', NULL, NULL, NULL, '2026-01-16 07:12:07', '2026-01-16 07:12:07');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_employee`
--

CREATE TABLE `candidate_employee` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `hired_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_employee`
--

INSERT INTO `candidate_employee` (`id`, `candidate_id`, `employee_id`, `hired_at`) VALUES
(1, 5, 15, '2026-01-13 06:59:30'),
(2, 4, 14, '2026-01-13 07:21:34'),
(4, 1, 13, '2026-01-13 07:22:13'),
(5, 6, 16, '2026-01-13 12:28:21');

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
  `leave_balance` int(11) DEFAULT 20,
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

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `first_name`, `last_name`, `leave_balance`, `email`, `phone`, `department`, `designation`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(7, 4, 'Emp004', 'Aayush', 'Sharma', 20, 'aayush.sharma@peoplenest.com', '', 'IT', 'Developer', '2026-01-06', 0.00, 'active', '2026-01-06 14:18:42', '2026-01-06 14:18:42'),
(10, 8, 'Emp105', 'Ritika', 'Chaudhary', 20, 'ritikachy002@peoplenest.com', '9827737372', 'Finance', 'finance manager', '2026-01-10', 60000.00, 'active', '2026-01-10 07:37:21', '2026-01-10 07:37:21'),
(12, 10, 'Emp107', 'Kim', 'Jung', 14, 'kimjung012@peoplenest.com', '9827272735', 'Marketing', 'senior marketing manager', '2026-01-02', 60000.00, 'active', '2026-01-11 07:01:52', '2026-01-19 09:02:30'),
(13, 11, 'Emp108', 'sita', 'chaudhary', 20, 'rit@gmail.com', '9875431456', 'IT', 'developer', '2026-01-11', 50000.00, 'active', '2026-01-11 08:10:48', '2026-01-12 02:32:59'),
(14, 13, 'Emp109', 'samira', 'magar', 20, 'samira506@peoplenest.com', '9856990566', 'IT', 'accountant', '2026-01-12', NULL, 'active', '2026-01-12 10:13:46', '2026-01-12 10:13:46'),
(15, 14, 'Emp110', 'Mansi', 'Thapa', 20, 'mansi034@peoplenest.com', '9801124367', 'IT', 'marketing', '2026-01-13', NULL, 'active', '2026-01-13 06:59:30', '2026-01-13 06:59:30'),
(16, 15, 'Emp111', 'Ram', 'Kumar', 17, 'Ram342@peoplenest.com', '982146718', 'IT', 'operation', '2026-01-13', NULL, 'active', '2026-01-13 12:28:21', '2026-01-19 05:16:09');

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
(4, 7, 'casual', '2026-01-20', '2026-01-21', 2, 'bnmhnbjkn', 'rejected', 1, '2026-01-12 05:44:51', 'i=njkg', '2026-01-08 07:32:55', '2026-01-12 05:44:51'),
(5, 12, 'annual', '2026-01-20', '2026-01-23', 4, 'i have some important work', 'approved', 1, '2026-01-19 04:12:15', NULL, '2026-01-19 03:10:52', '2026-01-19 04:12:15'),
(6, 15, 'maternity', '2026-01-19', '2026-01-27', 9, 'i want maternity leave', 'approved', 1, '2026-01-19 04:56:28', '', '2026-01-19 04:14:25', '2026-01-19 04:56:28'),
(7, 16, 'emergency', '2026-01-19', '2026-01-21', 3, 'i need emergency leave', 'approved', 1, '2026-01-19 05:16:09', NULL, '2026-01-19 05:02:36', '2026-01-19 05:16:09'),
(8, 10, 'sick', '2026-01-19', '2026-01-21', 3, 'i am sick', 'pending', NULL, NULL, NULL, '2026-01-19 05:25:29', '2026-01-19 05:25:29'),
(9, 12, 'casual', '2026-01-23', '2026-01-24', 2, 'i need holiday', 'pending', NULL, NULL, NULL, '2026-01-19 08:36:58', '2026-01-19 08:36:58'),
(10, 12, 'annual', '2026-01-22', '2026-01-23', 2, 'i need leave', 'approved', 1, '2026-01-19 09:02:30', NULL, '2026-01-19 09:01:42', '2026-01-19 09:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 10, 'Your leave request (annual) from 2026-01-20 has been APPROVED. ????', 1, '2026-01-19 04:12:15'),
(2, 14, 'Your leave request has been APPROVED.', 1, '2026-01-19 04:56:28'),
(3, 15, 'Your leave request (emergency) from 2026-01-19 has been APPROVED. ????', 1, '2026-01-19 05:16:09'),
(4, 1, 'New Annual leave request from Kim Jung (2 days).', 1, '2026-01-19 09:01:42'),
(5, 10, 'Your leave request (annual) from 2026-01-22 has been APPROVED. ????', 1, '2026-01-19 09:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('recruitment_status', 'open');

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
(13, 'Emp109', 'samiramagar', 'samira506@peoplenest.com', '$2y$10$zIdy6KKLMFzQucSl86XxquWNZwxI4Pv.KDfGwP3PYkbU2f5mEgO4O', 'employee', '2026-01-12 10:13:46', '2026-01-12 10:13:46'),
(14, 'Emp110', 'mansithapa', 'mansi034@peoplenest.com', '$2y$10$9YmWRIPj3WSc.Yde0cQs.OCQjS5hgXJptyYqhTo9p//kRkigbsuWy', 'employee', '2026-01-13 06:59:30', '2026-01-13 06:59:30'),
(15, 'Emp111', 'ramkumar', 'Ram342@peoplenest.com', '$2y$10$f2p.uwexWSJUie8O.zwoPe4xK1PJnxTPPOIdBJdWaV0ytCU5Az5Y.', 'employee', '2026-01-13 12:28:21', '2026-01-13 12:28:21');

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
  ADD UNIQUE KEY `unique_application` (`email`,`position`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hire` (`candidate_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Constraints for table `candidate_employee`
--
ALTER TABLE `candidate_employee`
  ADD CONSTRAINT `candidate_employee_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_employee_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

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
