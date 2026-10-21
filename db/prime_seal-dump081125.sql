-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 03:24 PM
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
-- Database: `prime_seal`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `license` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `number_of_employee` int(11) DEFAULT NULL,
  `company_desc` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `license`, `name`, `address`, `contact`, `email`, `number_of_employee`, `company_desc`, `logo`, `website`, `date_created`) VALUES
(1, NULL, 'test1', 'testtest1', '09876541', 'maurineaustin@gmail.com', NULL, NULL, NULL, NULL, '2025-05-06 20:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `table_name` varchar(55) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `email_logs` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resent_emails`
--

CREATE TABLE `resent_emails` (
  `id` int(11) NOT NULL,
  `resent_by` int(11) DEFAULT NULL,
  `sent_template_id` int(11) DEFAULT NULL,
  `type` enum('signing','completion') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sent_templates`
--

CREATE TABLE `sent_templates` (
  `id` int(11) NOT NULL,
  `signing_id` varchar(55) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `email_order` int(11) DEFAULT NULL,
  `terms_agree` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_email_sent` tinyint(1) DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `email_sent_date` datetime DEFAULT NULL,
  `email_subject` varchar(110) DEFAULT NULL,
  `email_message` varchar(1010) DEFAULT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  `signing_link` longtext DEFAULT NULL,
  `completion_link` longtext DEFAULT NULL,
  `signature_token` text DEFAULT NULL,
  `primary_signature` text DEFAULT NULL,
  `initial_signature` text DEFAULT NULL,
  `signed_data_id` int(11) DEFAULT NULL,
  `signed_finished_date` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signed_data`
--

CREATE TABLE `signed_data` (
  `id` int(11) NOT NULL,
  `signing_id` varchar(55) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `signatures_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `doc_id` varchar(55) NOT NULL,
  `name` text NOT NULL,
  `creation_type` enum('manual','upload') NOT NULL,
  `contract_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contract_body`)),
  `email_subject` text DEFAULT NULL,
  `email_message` text DEFAULT NULL,
  `signature_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `text_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`text_config`)),
  `created_by` int(11) DEFAULT NULL,
  `document_status` enum('draft','published','archived') NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `template_files`
--

CREATE TABLE `template_files` (
  `id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `file_order` int(11) DEFAULT NULL,
  `file_name` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin') NOT NULL,
  `avatar` text DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `contact`, `address`, `email`, `password`, `role`, `avatar`, `company_id`, `position`, `status`, `date_created`) VALUES
(1, 'John', 'Montemar', NULL, NULL, 'montemarjohn66@gmail.com', '$2y$10$uzdycxVlCh3pG.bjElgIvOK3nYe1hK4gWJsyMlRULNPhpuiaXFlzK', 'admin', NULL, NULL, NULL, 1, '2025-04-23 20:05:39'),
(2, 'Mark1', 'Cooper1', '09876541', 'testtest1', 'maurineaustin@gmail.com1', NULL, 'admin', NULL, NULL, NULL, 1, '2025-05-06 21:42:23'),
(3, 'Mark', 'Cooper', '0987654', 'testtest', 'maurineaustin1@gmail.com', NULL, 'super_admin', NULL, 1, '1', 1, '2025-05-06 21:43:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resent_emails`
--
ALTER TABLE `resent_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resent_emails_ibfk_1` (`sent_template_id`),
  ADD KEY `resent_by` (`resent_by`);

--
-- Indexes for table `sent_templates`
--
ALTER TABLE `sent_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sent_templates_ibfk_1` (`signed_data_id`),
  ADD KEY `sent_templates_ibfk_2` (`sender_id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `signed_data`
--
ALTER TABLE `signed_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `template_files`
--
ALTER TABLE `template_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_files_ibfk_1` (`template_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resent_emails`
--
ALTER TABLE `resent_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sent_templates`
--
ALTER TABLE `sent_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signed_data`
--
ALTER TABLE `signed_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `template_files`
--
ALTER TABLE `template_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `resent_emails`
--
ALTER TABLE `resent_emails`
  ADD CONSTRAINT `resent_emails_ibfk_1` FOREIGN KEY (`sent_template_id`) REFERENCES `sent_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resent_emails_ibfk_2` FOREIGN KEY (`resent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sent_templates`
--
ALTER TABLE `sent_templates`
  ADD CONSTRAINT `sent_templates_ibfk_1` FOREIGN KEY (`signed_data_id`) REFERENCES `signed_data` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sent_templates_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sent_templates_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `signed_data`
--
ALTER TABLE `signed_data`
  ADD CONSTRAINT `signed_data_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `templates`
--
ALTER TABLE `templates`
  ADD CONSTRAINT `templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `template_files`
--
ALTER TABLE `template_files`
  ADD CONSTRAINT `template_files_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
