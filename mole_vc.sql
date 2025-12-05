-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 05, 2025 at 03:36 AM
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
-- Database: `mole_vc`
--

-- --------------------------------------------------------

--
-- Table structure for table `help_tickets`
--

CREATE TABLE `help_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `issue_type` enum('vc_issue','forget_password','other') NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `help_tickets`
--

INSERT INTO `help_tickets` (`id`, `user_id`, `issue_type`, `message`, `status`, `created_at`) VALUES
(1, 2, 'vc_issue', 'HELP', 'closed', '2025-12-02 18:49:52'),
(2, 2, 'forget_password', 'LL', 'closed', '2025-12-03 14:00:10'),
(3, 2, 'vc_issue', 'KKK', 'closed', '2025-12-03 14:20:13'),
(4, 2, 'forget_password', 'OKK', 'closed', '2025-12-03 14:45:51'),
(5, 2, 'forget_password', 'ok', 'closed', '2025-12-03 19:04:36'),
(6, 2, 'forget_password', 'help', 'closed', '2025-12-04 17:18:25');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic` text NOT NULL,
  `hall` enum('new_sabhaghar','main_community_hall','chamber','online') NOT NULL,
  `platform` enum('webex','bharatvc','zoom','other') NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('current','previous','cancelled') DEFAULT 'current',
  `meeting_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `chaired_by` varchar(50) DEFAULT NULL,
  `chair_person_name` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `user_id`, `topic`, `hall`, `platform`, `start_time`, `end_time`, `status`, `meeting_link`, `created_at`, `chaired_by`, `chair_person_name`) VALUES
(2, 2, 'MAIN MEETING', 'online', 'other', '2025-12-18 02:30:00', '2025-12-18 06:30:00', 'previous', 'WWW.GOOGLE.VC.COM', '2025-12-03 02:49:29', NULL, NULL),
(3, 2, 'Cipl', 'main_community_hall', 'webex', '2025-12-05 15:00:00', '2025-12-05 16:00:00', 'current', NULL, '2025-12-04 23:15:13', NULL, NULL),
(4, 2, 'help', 'new_sabhaghar', 'webex', '2025-12-18 02:00:00', '2025-12-18 05:00:00', 'previous', '', '2025-12-05 01:23:33', 'AS', 'help');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','disabled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `email`, `password`, `role`, `status`) VALUES
(1, 'admin', 'admin@molevc.local', '0192023a7bbd73250516f069df18b500', 'admin', 'active'),
(2, 'user1', 'user1@molevc.local', '6ad14ba9986e3615423dfca256d04e3f', 'user', 'active'),
(3, 'admin24', 'kamitabh244@gmail.com', '0e7517141fb53f21ee439b355b5a1d0a', 'admin', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `help_tickets`
--
ALTER TABLE `help_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_meet_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `help_tickets`
--
ALTER TABLE `help_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `fk_meet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
