-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 08:15 PM
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
-- Database: `hope4pets`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `created_at`) VALUES
(1, 'Dankendonat', '$2y$10$kIHlfQQgtpGQZ12ZUBS4DuwHRxmA2YT7vLx20PiIgttnwesiSaEWm', 'dankendonat17@gmail.com', '2025-10-10 12:29:53'),
(2, 'demo', '$2y$10$s283dgVnscqFrnuWDNUNY.PWO9KnUAKkWvzzzmafUvIoKXJOBET.2', 'demo@gmail.com', '2025-10-10 15:56:21'),
(3, 'Jeff', '$2y$10$a7HfF/h1RzAijZrxg8/yNuxk9ynrYp/C1NpFixjZ0VuroFzAyVLba', 'jeff@gmail.com', '2025-10-10 16:04:36'),
(4, 'Saira', '$2y$10$S1swUFsE9GkwmASB/V568uAkRSbNrqiB9clFuLltfeolL2HUoYX/y', 'saira@gmail.com', '2025-10-10 16:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adoptions`
--

CREATE TABLE `adoptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `applicant_id` bigint(20) UNSIGNED NOT NULL,
  `shelter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('applied','approved','denied','completed','cancelled') DEFAULT 'applied',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `donor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shelter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `donor_name` varchar(150) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','gcash','paymaya','bank_transfer','other') NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recipient_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `body`, `is_read`, `created_at`) VALUES
(31, 1, 5, 'huhuhuh', 1, '2025-10-06 17:28:07'),
(32, 1, 5, 'hello', 1, '2025-10-06 17:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `shelter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `species` enum('dog','cat','bird','rabbit','other') DEFAULT 'other',
  `breed` varchar(200) DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','unknown') DEFAULT 'unknown',
  `size` enum('small','medium','large','extra-large') DEFAULT 'medium',
  `pet_photos` varchar(255) NOT NULL,
  `vaccine_status` varchar(255) DEFAULT NULL,
  `health_status` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','pending','adopted','removed') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `owner_id`, `shelter_id`, `name`, `species`, `breed`, `age`, `gender`, `size`, `pet_photos`, `vaccine_status`, `health_status`, `location`, `description`, `status`, `created_at`) VALUES
(3, 7, 4, 'What a Sigma', 'cat', 'Persian', '23', 'male', 'small', '/storage/uploads/images/7/1760378948_d61ae4a1c652.webp', 'Anti Rabbis', 'Good', 'Tiolanos Dapit', 'hahahaha', 'adopted', '2025-10-13 16:41:39'),
(6, 7, 4, 'The  Super Sigma', 'cat', 'Persian', '23', 'male', 'extra-large', '/storage/uploads/images/7/1760378787_7ec23add4bb8.jpg', 'Anti Rabbis', 'Good', 'Plaza', 'aasd afasefafafafasfas', 'adopted', '2025-10-13 18:06:27'),
(7, 7, 4, 'Sigma 1', 'cat', 'Persian', '23', 'male', 'small', '/storage/uploads/images/7/1760378916_f6edf35dddbe.jpg', 'Anti Rabbis', 'Good', 'Tiolanos Dapit', 'ahaahahhahahahahha', 'available', '2025-10-13 18:08:36'),
(8, 7, 4, 'Sigma 2', 'cat', 'Persian', '23', 'female', 'extra-large', '/storage/uploads/images/7/1760379005_947a9d499cfc.jpg', 'Anti Rabbis', 'Good', 'Tiolanos Dapit', 'wdadadada', 'available', '2025-10-13 18:10:05');

-- --------------------------------------------------------

--
-- Table structure for table `pet_comments`
--

CREATE TABLE `pet_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_reactions`
--

CREATE TABLE `pet_reactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reaction_type` varchar(50) DEFAULT 'like',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pet_reports`
--

CREATE TABLE `pet_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pet_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('open','resolved','dismissed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `handled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `pet_id`, `content`, `created_at`) VALUES
(9, 3, NULL, 'adadada', '2025-10-06 13:18:31'),
(10, 3, NULL, 'dadadada', '2025-10-06 13:24:08'),
(11, 4, NULL, 'hahahahahahehahdadadada', '2025-10-06 13:45:56'),
(12, 1, NULL, 'Mao ni akong iribng', '2025-10-06 16:36:37'),
(13, 6, NULL, 'Ming MIng who willing to Adopt PM me', '2025-10-06 18:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 11, 3, 'adadadad', '2025-10-06 14:10:26'),
(2, 11, 3, 'hahaha ako nana', '2025-10-06 14:11:24'),
(3, 11, 4, 'hahahahahhahah nigana na', '2025-10-06 14:14:38'),
(4, 11, 4, 'hahahaha', '2025-10-06 14:16:23'),
(5, 11, 4, 'hahahha', '2025-10-06 14:43:28'),
(6, 11, 2, 'hahahaha', '2025-10-06 15:46:05'),
(7, 9, 5, 'wawawaw', '2025-10-06 16:10:51'),
(8, 11, 5, 'okissss', '2025-10-06 16:22:50'),
(9, 12, 5, 'Megatron', '2025-10-06 16:40:31'),
(10, 12, 5, 'Robot reads wattpad HAHAHA', '2025-10-06 16:40:57'),
(11, 13, 6, 'hello', '2025-10-11 17:05:24');

-- --------------------------------------------------------

--
-- Table structure for table `post_photos`
--

CREATE TABLE `post_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `photo_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_reactions`
--

CREATE TABLE `post_reactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reaction_type` varchar(50) DEFAULT 'like',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_reactions`
--

INSERT INTO `post_reactions` (`id`, `post_id`, `user_id`, `reaction_type`, `created_at`) VALUES
(9, 11, 3, 'like', '2025-10-06 14:13:25'),
(10, 11, 4, 'like', '2025-10-06 14:14:25'),
(11, 9, 5, 'like', '2025-10-06 16:10:40'),
(12, 11, 5, 'like', '2025-10-06 16:22:26'),
(13, 12, 5, 'like', '2025-10-06 16:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `post_reports`
--

CREATE TABLE `post_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('open','resolved','dismissed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `handled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_videos`
--

CREATE TABLE `post_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `video_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shelters`
--

CREATE TABLE `shelters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `shelter_name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `verified_badge` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shelters`
--

INSERT INTO `shelters` (`id`, `user_id`, `shelter_name`, `address`, `contact_number`, `verified_badge`, `created_at`, `is_verified`, `verified_at`, `verified_by`) VALUES
(4, 7, 'Pagdian_Shelter', 'Pagadian City', '09705433760', 0, '2025-10-12 12:32:58', 0, NULL, NULL),
(5, 6, 'PagadianShelter-2', 'Roosevelt, Benito Labao St, Brgy, Iligan City, 9200 Lanao del Norte', '09705433760', 0, '2025-10-13 08:41:14', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shelter_documents`
--

CREATE TABLE `shelter_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shelter_id` bigint(20) UNSIGNED NOT NULL,
  `doc_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shelter_documents`
--

INSERT INTO `shelter_documents` (`id`, `shelter_id`, `doc_type`, `file_path`, `status`, `uploaded_at`, `reviewed_by`, `reviewed_at`) VALUES
(101, 4, 'mayors_permit', '/storage/documents/PagadianShelter/1760358511_0c514bb6c141_Sample-BIR-Certificate-of-Registration-2.png', 'pending', '2025-10-13 12:28:31', NULL, NULL),
(102, 4, 'bir_registration', '/storage/documents/PagadianShelter/1760358511_dbb6f1001949_Sample-Barangay-Business-Clearance.png', 'pending', '2025-10-13 12:28:31', NULL, NULL),
(103, 4, 'barangay_clearance', '/storage/documents/PagadianShelter/1760358511_784f1306a567_DTI-Certificate-x1.png', 'pending', '2025-10-13 12:28:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` enum('male','female','other','unspecified') DEFAULT 'unspecified',
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `birthday`, `gender`, `email`, `password_hash`, `profile_photo`, `location`, `contact_number`, `is_verified`, `created_at`, `updated_at`) VALUES
(6, 'Jeff John Petiluna', NULL, 'unspecified', 'dankendonat17@gmail.com', '$2y$10$VCzlsYY8kgrT5Ntmj51Wde2yIHDwlUfiWUHWtm9TlRQdiITp3gDFu', 'https://lh3.googleusercontent.com/a/ACg8ocLJJRwNJZEoQsAhIcpterkiFfQew9RqQuLdkVhBy2KmSR_UOQ=s96-c', NULL, NULL, 1, '2025-10-06 18:08:17', '2025-10-06 18:08:17'),
(7, 'Ashlie Roncales', NULL, 'unspecified', 'ashlieroncales16@gmail.com', '$2y$10$zRtM8dAtcmpFm8fPJxPCFenwqgjdLZfh.lxZ9LdAblb/jrNs6.R2u', NULL, NULL, '', 0, '2025-10-07 05:19:29', '2025-10-07 05:19:29');

-- --------------------------------------------------------

--
-- Table structure for table `user_documents`
--

CREATE TABLE `user_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `doc_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reported_user_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('open','resolved','dismissed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `handled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_username` (`username`),
  ADD UNIQUE KEY `uq_admins_email` (`email`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_adminlogs_admin` (`admin_id`);

--
-- Indexes for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_adoptions_pet` (`pet_id`),
  ADD KEY `fk_adoptions_applicant` (`applicant_id`),
  ADD KEY `fk_adoptions_shelter` (`shelter_id`),
  ADD KEY `fk_adoptions_admin` (`reviewed_by`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_transaction_id` (`transaction_id`),
  ADD KEY `fk_donations_donor` (`donor_id`),
  ADD KEY `fk_donations_shelter` (`shelter_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_sender` (`sender_id`),
  ADD KEY `fk_messages_recipient` (`recipient_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pets_owner` (`owner_id`),
  ADD KEY `fk_pets_shelter` (`shelter_id`);

--
-- Indexes for table `pet_comments`
--
ALTER TABLE `pet_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petcomments_pet` (`pet_id`),
  ADD KEY `fk_petcomments_user` (`user_id`);

--
-- Indexes for table `pet_reactions`
--
ALTER TABLE `pet_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pet_react` (`pet_id`,`user_id`),
  ADD KEY `fk_petreactions_user` (`user_id`);

--
-- Indexes for table `pet_reports`
--
ALTER TABLE `pet_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_petreports_reporter` (`reporter_id`),
  ADD KEY `fk_petreports_pet` (`pet_id`),
  ADD KEY `fk_petreports_admin` (`handled_by`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_posts_user` (`user_id`),
  ADD KEY `fk_posts_pet` (`pet_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_postcomments_post` (`post_id`),
  ADD KEY `fk_postcomments_user` (`user_id`);

--
-- Indexes for table `post_photos`
--
ALTER TABLE `post_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_postphotos_post` (`post_id`);

--
-- Indexes for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_post_react` (`post_id`,`user_id`),
  ADD KEY `fk_postreactions_user` (`user_id`);

--
-- Indexes for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_postreports_reporter` (`reporter_id`),
  ADD KEY `fk_postreports_post` (`post_id`),
  ADD KEY `fk_postreports_admin` (`handled_by`);

--
-- Indexes for table `post_videos`
--
ALTER TABLE `post_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_postvideos_post` (`post_id`);

--
-- Indexes for table `shelters`
--
ALTER TABLE `shelters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_shelters_user` (`user_id`);

--
-- Indexes for table `shelter_documents`
--
ALTER TABLE `shelter_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_shelterdocs_shelter` (`shelter_id`),
  ADD KEY `fk_shelterdocs_admin` (`reviewed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userdocs_user` (`user_id`),
  ADD KEY `fk_userdocs_admin` (`reviewed_by`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_userreports_reporter` (`reporter_id`),
  ADD KEY `fk_userreports_reported` (`reported_user_id`),
  ADD KEY `fk_userreports_admin` (`handled_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adoptions`
--
ALTER TABLE `adoptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pet_comments`
--
ALTER TABLE `pet_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_reactions`
--
ALTER TABLE `pet_reactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pet_reports`
--
ALTER TABLE `pet_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `post_photos`
--
ALTER TABLE `post_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_videos`
--
ALTER TABLE `post_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shelters`
--
ALTER TABLE `shelters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `shelter_documents`
--
ALTER TABLE `shelter_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_documents`
--
ALTER TABLE `user_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_adminlogs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD CONSTRAINT `fk_adoptions_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_adoptions_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_adoptions_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_adoptions_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_donor` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_donations_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `fk_pets_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pets_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pet_comments`
--
ALTER TABLE `pet_comments`
  ADD CONSTRAINT `fk_petcomments_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_petcomments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_reactions`
--
ALTER TABLE `pet_reactions`
  ADD CONSTRAINT `fk_petreactions_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_petreactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pet_reports`
--
ALTER TABLE `pet_reports`
  ADD CONSTRAINT `fk_petreports_admin` FOREIGN KEY (`handled_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_petreports_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_petreports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `fk_postcomments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_postcomments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_photos`
--
ALTER TABLE `post_photos`
  ADD CONSTRAINT `fk_postphotos_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD CONSTRAINT `fk_postreactions_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_postreactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reports`
--
ALTER TABLE `post_reports`
  ADD CONSTRAINT `fk_postreports_admin` FOREIGN KEY (`handled_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_postreports_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_postreports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_videos`
--
ALTER TABLE `post_videos`
  ADD CONSTRAINT `fk_postvideos_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shelters`
--
ALTER TABLE `shelters`
  ADD CONSTRAINT `fk_shelters_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shelter_documents`
--
ALTER TABLE `shelter_documents`
  ADD CONSTRAINT `fk_shelterdocs_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_shelterdocs_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD CONSTRAINT `fk_userdocs_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_userdocs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `fk_userreports_admin` FOREIGN KEY (`handled_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_userreports_reported` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_userreports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
