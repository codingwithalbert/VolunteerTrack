-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 11:14 AM
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
-- Database: `volunteertrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `activity_name`, `description`, `location`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Cleanup Drive', 'help cleans the community sector', 'Park', 7, 'active', '2025-10-28 06:28:21', '2025-10-28 06:28:49'),
(5, 'Office Duty', 'Office Desk', 'CITE', 8, 'active', '2025-10-29 05:37:03', '2025-10-29 05:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `hours`
--

CREATE TABLE `hours` (
  `hour_id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `hours_worked` decimal(5,2) NOT NULL,
  `work_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hours`
--

INSERT INTO `hours` (`hour_id`, `volunteer_id`, `activity_id`, `hours_worked`, `work_date`, `description`, `status`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 9, 4, 12.50, '2025-10-29', '', 'verified', 8, '2025-10-29 05:36:27', '2025-10-29 05:32:47', '2025-10-29 05:36:27'),
(2, 10, 4, 2.50, '2025-10-29', '', 'verified', 8, '2025-10-29 05:36:25', '2025-10-29 05:35:00', '2025-10-29 05:36:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('volunteer','coordinator','admin') NOT NULL DEFAULT 'volunteer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `role`, `phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(7, 'admin', 'admin@volunteertrack.com', '$2y$10$CkDCR5mfGjYUmJqolVNMu.M5aiu7/5HbrBtUX3ds.uJiNT3Phqbb6', 'System Administrator', 'admin', NULL, NULL, 'active', '2025-10-28 06:09:40', '2025-10-28 06:09:40'),
(8, 'coordinator1', 'coordinator@volunteertrack.com', '$2y$10$U3D12vpLl.I7kgGBq5RYTel2klCcxfg8IUB4NpUUj9Q3b5CsGilwq', 'John Coordinator', 'coordinator', NULL, NULL, 'active', '2025-10-28 06:09:40', '2025-10-28 06:09:40'),
(9, 'volunteer1', 'volunteer@volunteertrack.com', '$2y$10$tUOzHivRyxJ8gY/e0lgHxOm1ClsPM/TsHO55hkRTRkskHvOQA6KOK', 'Jane Volunteer', 'volunteer', NULL, NULL, 'active', '2025-10-28 06:09:40', '2025-10-28 06:09:40'),
(10, 'albert', 'icojonrodalbert@gmail.com', '$2y$10$j3yYi6lV.1OVZtVNud4Zm.0Z7DCHTMM37TcgWTsvzlDqQUP51FkvK', 'jon rod ico', 'volunteer', '123123123123111', '265 Sitio Garcia Ambuetel Calasiao', 'active', '2025-10-29 05:33:58', '2025-10-29 05:33:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `hours`
--
ALTER TABLE `hours`
  ADD PRIMARY KEY (`hour_id`),
  ADD KEY `volunteer_id` (`volunteer_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hours`
--
ALTER TABLE `hours`
  MODIFY `hour_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `hours`
--
ALTER TABLE `hours`
  ADD CONSTRAINT `hours_ibfk_1` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hours_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hours_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
