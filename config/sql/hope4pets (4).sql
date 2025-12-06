-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 06:46 PM
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

--
-- Dumping data for table `adoptions`
--

INSERT INTO `adoptions` (`id`, `pet_id`, `applicant_id`, `shelter_id`, `status`, `created_at`, `reviewed_by`, `reviewed_at`) VALUES
(3, 12, 11, 5, 'applied', '2025-10-20 14:27:50', NULL, NULL),
(4, 12, 14, 5, 'applied', '2025-10-20 14:37:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `adoption_applicants`
--

CREATE TABLE `adoption_applicants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `adoption_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_applicants`
--

INSERT INTO `adoption_applicants` (`id`, `adoption_id`, `name`, `phone`, `address`, `message`, `created_at`) VALUES
(3, 3, 'Othelo Adiong', '09652113834', 'Purok Mangga 1 Lantian, Labangan, Zamboanga Del Sur', 'hahahaha', '2025-10-20 14:27:50'),
(4, 4, 'Ashlie Roncales', '09652113834', 'Purok Mangga 1 Lantian, Labangan, Zamboanga Del Sur', 'dadada', '2025-10-20 14:37:42');

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
(70, 11, 6, 'hello', 1, '2025-10-15 14:56:41'),
(71, 12, 6, 'hello', 1, '2025-10-15 15:59:56'),
(72, 6, 12, 'hello', 0, '2025-10-15 16:07:42'),
(73, 6, 12, 'hi', 0, '2025-10-15 16:17:51'),
(74, 12, 6, 'hello', 1, '2025-10-15 16:17:59'),
(75, 6, 12, 'hello', 0, '2025-10-15 16:25:57'),
(76, 6, 12, 'hello', 0, '2025-10-15 16:31:09'),
(77, 12, 6, 'oiy kinsa ka', 1, '2025-10-15 16:31:40'),
(78, 6, 12, 'hahahaha', 0, '2025-10-15 16:39:15'),
(79, 6, 12, 'hahah', 0, '2025-10-15 16:42:16'),
(80, 6, 12, 'hahahaha', 0, '2025-10-15 16:42:20'),
(81, 6, 12, 'hahahahaha', 0, '2025-10-15 16:42:23'),
(82, 6, 12, 'hahahahahah', 0, '2025-10-15 16:42:26'),
(83, 6, 12, 'hahahaha', 0, '2025-10-15 16:42:29'),
(84, 6, 12, 'hello', 0, '2025-10-15 16:50:05'),
(85, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(86, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(87, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(88, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(89, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(90, 6, 12, 'hello', 0, '2025-10-15 16:50:06'),
(91, 6, 12, 'hello', 0, '2025-10-15 16:50:07'),
(92, 6, 12, 'hahahahah', 0, '2025-10-15 16:50:24'),
(93, 6, 12, 'hello', 0, '2025-10-15 16:57:55'),
(94, 12, 6, 'hello', 1, '2025-10-15 16:58:07'),
(95, 6, 12, 'hi', 0, '2025-10-15 17:04:04'),
(96, 12, 6, 'hello', 1, '2025-10-15 17:06:05'),
(97, 6, 12, 'hello', 0, '2025-10-15 17:07:14'),
(98, 6, 12, 'hello', 0, '2025-10-15 17:08:09'),
(99, 12, 6, 'hello', 1, '2025-10-15 17:08:15'),
(100, 6, 12, 'hello', 0, '2025-10-15 17:09:23'),
(101, 12, 6, 'hahhaha', 1, '2025-10-15 17:09:28'),
(102, 12, 6, 'okay na hahahhaa', 1, '2025-10-15 17:09:40'),
(103, 12, 6, 'okay', 1, '2025-10-15 17:11:17'),
(104, 6, 12, 'done ni gana na', 0, '2025-10-15 17:12:50'),
(105, 12, 6, 'uhuhuhuh', 1, '2025-10-15 17:12:56'),
(106, 12, 6, 'okay hahahaha', 1, '2025-10-15 17:13:05'),
(107, 6, 12, 'huhuhu', 0, '2025-10-15 17:15:06'),
(108, 12, 6, 'hello', 1, '2025-10-15 17:18:49'),
(109, 6, 12, 'huhuh', 0, '2025-10-15 17:21:56'),
(110, 12, 6, 'huhuhuh', 1, '2025-10-15 17:22:06'),
(111, 12, 6, 'huhuhu', 1, '2025-10-15 17:22:31'),
(112, 6, 12, 'ahahahahaha', 0, '2025-10-15 17:22:38'),
(113, 12, 6, 'huhuhuhuhuh', 1, '2025-10-15 17:22:57'),
(114, 12, 6, 'hello', 1, '2025-10-15 17:23:06'),
(115, 6, 12, 'hahahahha', 0, '2025-10-15 17:23:11'),
(116, 12, 6, 'huhuhuh', 1, '2025-10-15 17:23:26'),
(117, 6, 12, 'huhuhuhuh', 0, '2025-10-15 17:23:30'),
(118, 6, 12, 'huhuhuhuuh', 0, '2025-10-15 17:25:02'),
(119, 12, 6, 'hello', 1, '2025-10-15 17:25:06'),
(120, 12, 6, 'hahahhaha', 1, '2025-10-15 17:25:11'),
(121, 6, 12, 'huy', 0, '2025-10-15 17:46:36'),
(122, 12, 6, 'oiy', 1, '2025-10-15 17:46:45'),
(123, 13, 12, 'hello', 0, '2025-10-16 13:46:28'),
(124, 13, 12, 'ssncksnc', 0, '2025-10-16 13:46:37'),
(125, 13, 12, 'xcxc', 0, '2025-10-16 13:46:42'),
(126, 13, 6, 'sasasa', 1, '2025-10-16 13:47:15'),
(127, 13, 6, 'erefvfd', 1, '2025-10-16 13:47:19'),
(128, 6, 11, 'hello po', 1, '2025-10-20 14:26:41'),
(129, 11, 6, 'oiy bakit', 1, '2025-10-20 14:26:50'),
(130, 6, 11, 'wala lang', 1, '2025-10-20 14:26:56'),
(131, 11, 6, 'awh hahahah', 1, '2025-10-20 14:27:00'),
(132, 6, 11, 'okay', 1, '2025-10-20 14:27:03'),
(133, 11, 6, 'po', 1, '2025-10-20 14:27:06'),
(134, 11, 6, 'bakit di ako maka request huhuhuhuh', 1, '2025-10-20 14:27:21'),
(135, 6, 11, 'iwan ko', 1, '2025-10-20 14:27:29'),
(136, 6, 14, 'hello', 0, '2025-10-21 14:32:09'),
(137, 11, 6, 'okay naba to siya?', 1, '2025-10-22 16:09:54'),
(138, 6, 11, 'okay hahahahha', 0, '2025-10-22 16:10:03'),
(139, 11, 6, 'Magkano?', 1, '2025-10-22 16:10:10'),
(140, 6, 11, 'mga 4k lang bos', 0, '2025-10-22 16:10:25'),
(141, 11, 6, 'okay boss copy send lang nako gcash', 1, '2025-10-22 16:10:38'),
(142, 6, 11, 'sege sge boss', 0, '2025-10-22 16:10:45');

--
-- Triggers `messages`
--
DELIMITER $$
CREATE TRIGGER `trg_after_message_insert_update_recipient` AFTER INSERT ON `messages` FOR EACH ROW BEGIN
    -- I-update ang 'updated_at' timestamp ng user na nakatanggap ng mensahe (recipient)
    -- Ito ay magsisilbing indicator ng "new activity" sa back-end
    UPDATE `users`
    SET `updated_at` = NEW.created_at
    WHERE `id` = NEW.recipient_id;

    -- Opsyonal: I-update din ang 'updated_at' ng sender para sa consistency
    UPDATE `users`
    SET `updated_at` = NEW.created_at
    WHERE `id` = NEW.sender_id;
END
$$
DELIMITER ;

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
(12, 6, 5, 'ChuChai', 'cat', 'Mixed', '23', 'male', 'medium', '/storage/uploads/images/6/1760723006_d4621c30dbf9.jpg', 'Partially vaccinated', 'Healthy', 'Iligan City', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', 'available', '2025-10-17 17:43:26');

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
(64, 6, NULL, 'huhuhuhuh', '2025-10-17 16:53:05'),
(65, 6, NULL, 'Lorem ipsum dolor sit amet consectetur adipiscing elit. Pretium tellus duis convallis tempus leo eu aenean. Iaculis massa nisl malesuada lacinia integer nunc posuere. Conubia nostra inceptos himenaeos orci varius natoque penatibus. Nulla molestie mattis scelerisque maximus eget fermentum odio. Blandit quis suspendisse aliquet nisi sodales consequat magna. Ligula congue sollicitudin erat viverra ac tincidunt nam. Velit aliquam imperdiet mollis nullam volutpat porttitor ullamcorper. Dui felis venenatis ultrices proin libero feugiat tristique. Cubilia curae hac habitasse platea dictumst lorem ipsum. Sem placerat in id cursus mi pretium tellus. Fringilla lacus nec metus bibendum egestas iaculis massa. Taciti sociosqu ad litora torquent per conubia nostra. Ridiculus mus donec rhoncus eros lobortis nulla molestie. Mauris pharetra vestibulum fusce dictum risus blandit quis. Finibus facilisis dapibus etiam interdum tortor ligula congue. Justo lectus commodo augue arcu dignissim velit aliquam. Primis vulputate ornare sagittis vehicula praesent dui felis. Senectus netus suscipit auctor curabitur facilisi cubilia curae. Quisque faucibus ex sapien vitae pellentesque sem placerat.', '2025-10-17 18:03:31'),
(66, 6, NULL, 'Lorem ipsum dolor sit amet consectetur adipiscing elit. Semper vel class aptent taciti sociosqu ad litora. Blandit quis suspendisse aliquet nisi sodales consequat magna. Cras eleifend turpis fames primis vulputate ornare sagittis. Sem placerat in id cursus mi pretium tellus. Orci varius natoque penatibus et magnis dis parturient. Finibus facilisis dapibus etiam interdum tortor ligula congue. Proin libero feugiat tristique accumsan maecenas potenti ultricies. Sed diam urna tempor pulvinar vivamus fringilla lacus. Eros lobortis nulla molestie mattis scelerisque maximus eget. Porta elementum a enim euismod quam justo lectus. Curabitur facilisi cubilia curae hac habitasse platea dictumst. Nisl malesuada lacinia integer nunc posuere ut hendrerit. Efficitur laoreet mauris pharetra vestibulum fusce dictum risus. Imperdiet mollis nullam volutpat porttitor ullamcorper rutrum gravida. Adipiscing elit quisque faucibus ex sapien vitae pellentesque. Ad litora torquent per conubia nostra inceptos himenaeos. Consequat magna ante condimentum neque at luctus nibh. Ornare sagittis vehicula praesent dui felis venenatis ultrices. Pretium tellus duis convallis tempus leo eu aenean. Dis parturient montes nascetur ridiculus mus donec rhoncus. Ligula congue sollicitudin erat viverra ac tincidunt nam. Potenti ultricies habitant morbi senectus netus suscipit auctor. Fringilla lacus nec metus bibendum egestas iaculis massa. Maximus eget fermentum odio phasellus non purus est. Justo lectus commodo augue arcu dignissim velit aliquam. Platea dictumst lorem ipsum dolor sit amet consectetur. Ut hendrerit semper vel class aptent taciti sociosqu. Dictum risus blandit quis suspendisse aliquet nisi sodales. Rutrum gravida cras eleifend turpis fames primis vulputate. Vitae pellentesque sem placerat in id cursus mi. Inceptos himenaeos orci varius natoque penatibus et magnis. Luctus nibh finibus facilisis dapibus etiam interdum tortor. Venenatis ultrices proin libero feugiat tristique accumsan maecenas. Eu aenean sed diam urna tempor pulvinar vivamus. Donec rhoncus eros lobortis nulla molestie mattis scelerisque. Tincidunt nam porta elementum a enim euismod quam. Suscipit auctor curabitur facilisi cubilia curae hac habitasse. Iaculis massa nisl malesuada lacinia integer nunc posuere. Purus est efficitur laoreet mauris pharetra vestibulum fusce. Velit aliquam imperdiet mollis nullam volutpat porttitor ullamcorper. Amet consectetur adipiscing elit quisque faucibus ex sapien. Taciti sociosqu ad litora torquent per conubia nostra. Nisi sodales consequat magna ante condimentum neque at. Primis vulputate ornare sagittis vehicula praesent dui felis. Cursus mi pretium tellus duis convallis tempus leo. Et magnis dis parturient montes nascetur ridiculus mus. Interdum tortor ligula congue sollicitudin erat viverra ac. Accumsan maecenas potenti ultricies habitant morbi senectus netus. Pulvinar vivamus fringilla lacus nec metus bibendum egestas.', '2025-10-17 18:08:55'),
(67, 11, NULL, 'hello hahahaha', '2025-10-20 14:26:11');

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

-- --------------------------------------------------------

--
-- Table structure for table `post_photos`
--

CREATE TABLE `post_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `photo_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_photos`
--

INSERT INTO `post_photos` (`id`, `post_id`, `photo_path`) VALUES
(37, 64, 'storage/uploads/posts/photos/6/post_68f274711bc30_1760719985.jpg'),
(38, 65, 'storage/uploads/posts/photos/6/post_68f284f3a009b_1760724211.jpg'),
(39, 65, 'storage/uploads/posts/photos/6/post_68f284f3a021f_1760724211.jpg'),
(40, 65, 'storage/uploads/posts/photos/6/post_68f284f3a036f_1760724211.jpg'),
(41, 65, 'storage/uploads/posts/photos/6/post_68f284f3a04c0_1760724211.webp'),
(42, 65, 'storage/uploads/posts/photos/6/post_68f284f3a066e_1760724211.jpg'),
(43, 65, 'storage/uploads/posts/photos/6/post_68f284f3a07c6_1760724211.jpg'),
(44, 66, 'storage/uploads/posts/photos/6/post_68f2863742a18_1760724535.jpg'),
(45, 66, 'storage/uploads/posts/photos/6/post_68f2863742bb6_1760724535.jpg'),
(46, 66, 'storage/uploads/posts/photos/6/post_68f2863742d12_1760724535.webp'),
(47, 66, 'storage/uploads/posts/photos/6/post_68f2863742e53_1760724535.jpg'),
(48, 66, 'storage/uploads/posts/photos/6/post_68f2863742f9b_1760724535.jpg'),
(49, 66, 'storage/uploads/posts/photos/6/post_68f2863743227_1760724535.jpg'),
(50, 67, 'storage/uploads/posts/photos/11/post_68f64683eb416_1760970371.jpg'),
(51, 67, 'storage/uploads/posts/photos/11/post_68f64683eb65d_1760970371.jpg');

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

--
-- Dumping data for table `post_reports`
--

INSERT INTO `post_reports` (`id`, `reporter_id`, `post_id`, `reason`, `status`, `created_at`, `handled_by`, `handled_at`) VALUES
(1, 11, 64, 'This is spam.', 'open', '2025-10-22 16:10:45', NULL, NULL),
(2, 6, 67, 'Inappropriate content.', 'open', '2025-10-22 16:10:45', NULL, NULL);

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
  `shelter_photo` varchar(255) NOT NULL,
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

INSERT INTO `shelters` (`id`, `user_id`, `shelter_name`, `shelter_photo`, `address`, `contact_number`, `verified_badge`, `created_at`, `is_verified`, `verified_at`, `verified_by`) VALUES
(4, 7, 'Pagdian_Shelter branch4', '', 'Pagadian City', '09705433760', 0, '2025-10-12 12:32:58', 0, NULL, NULL),
(5, 6, 'PagadianShelter-2', '', 'Mea Building, Second Floor, Saray, Iligan City, Lanao Del Norte, 9200', '09705433760', 0, '2025-10-13 08:41:14', 0, NULL, NULL),
(7, 9, 'PagadianShelter-branch3', '', 'Roosevelt, Benito Labao St, Brgy, Iligan City, 9200 Lanao del Norte', '09705433760', 0, '2025-10-13 12:30:35', 0, NULL, NULL),
(8, 12, 'Happy Paw', '', 'Palao Street Second Building, Aguinaldo, Ubaldo, Iligan City, Lanao del Norte, 9200', '+639652113834', 0, '2025-10-15 11:36:49', 0, NULL, NULL);

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
(6, 'Jeff John Petiluna', '2006-11-12', 'male', 'dankendonat17@gmail.com', '$2y$10$VCzlsYY8kgrT5Ntmj51Wde2yIHDwlUfiWUHWtm9TlRQdiITp3gDFu', 'storage/uploads/profile_picture/6/profile_6_686ee65b386f.jpg', 'Mea Building, Iligan City, Second Floor, 9200, Second Floor, Saray', '09705433760', 1, '2025-10-06 18:08:17', '2025-10-22 16:10:45'),
(7, 'Ashlie Roncales', NULL, 'unspecified', 'ashlieroncales16@gmail.com', '$2y$10$zRtM8dAtcmpFm8fPJxPCFenwqgjdLZfh.lxZ9LdAblb/jrNs6.R2u', NULL, NULL, '', 0, '2025-10-07 05:19:29', '2025-10-07 05:19:29'),
(9, 'Jeff John Petiluna', NULL, 'unspecified', 'petilunajeffjohn18@gmail.com', '$2y$10$2ix03vM0dlR/sYG7r7HV1.HiiIelHDfrmGoNPPFxe8iVi9pnoEOGq', 'https://lh3.googleusercontent.com/a/ACg8ocLl18hZ3w4q_tzAARCfiBh851M6i9cw8fndp1Hu9ujKUEXK3g=s96-c', NULL, NULL, 1, '2025-10-13 18:29:21', '2025-10-13 18:29:21'),
(10, 'Information Technology 2 _ PETILUNA', NULL, 'unspecified', 'petiluna.jeffjohn@ici.edu.ph', '$2y$10$xYt0ySwvbgMKSuQRTzOaSugvk3d83NIOwvYBlnmoDJAffdmKpfFIa', 'https://lh3.googleusercontent.com/a/ACg8ocIlNCEKD0bjQZCzrXdsQ99LgNGyfiuh4YYe_Ktlxr1bmKn9joA=s96-c', NULL, NULL, 1, '2025-10-14 17:08:34', '2025-10-14 17:08:34'),
(11, 'Othelo Adiong', NULL, 'male', 'adiongothelo33@gmail.com', '$2y$10$7lJXKOJEvrwpcEvYPy8hweQ9/JYrEZSMqTMJrHOuaOrz7gqy6FiwG', 'storage/uploads/profile_picture/11/profile_11_8b4f532ac987.jpg', NULL, NULL, 1, '2025-10-15 09:35:04', '2025-10-22 16:10:45'),
(12, 'Saira', '2006-12-31', 'female', 'triangle.outsourcing.corp.toc@gmail.com', '$2y$10$P0YEgpW03efY5O8WycPUDufAN7iWh1mDDYV1jGCMGn67avs4i3W/S', 'storage/uploads/profile_picture/12/profile_12_03808ec3e1f1.jpg', 'huhuhuhuh, 7017, huhuhuh, adadada, Labangan, Zamboanga Del Sur', '09652113834', 1, '2025-10-15 13:56:32', '2025-10-16 13:46:42'),
(13, 'Kaia Dacillo', NULL, 'unspecified', 'sai@gmail.com', '$2y$10$aAeIatsF5eV.YDM0ZY4F5u.L70jv/pPdXS8pCg9B3V763P4KexeVW', NULL, NULL, '', 0, '2025-10-16 13:45:10', '2025-10-16 13:47:19'),
(14, 'Ashlie Roncales', NULL, 'unspecified', 'ashlieroncales17@gmail.com', '$2y$10$DZDMI8e0O5vdGa2OUrLLWuhWrjlzOimSevoqxBUvL7MsPcu8ZGlzu', NULL, NULL, '', 0, '2025-10-20 14:37:10', '2025-10-21 14:32:09');

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
-- Indexes for table `adoption_applicants`
--
ALTER TABLE `adoption_applicants`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `uq_reporter_post` (`reporter_id`,`post_id`),
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `adoption_applicants`
--
ALTER TABLE `adoption_applicants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `post_photos`
--
ALTER TABLE `post_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `post_reports`
--
ALTER TABLE `post_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_videos`
--
ALTER TABLE `post_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shelters`
--
ALTER TABLE `shelters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shelter_documents`
--
ALTER TABLE `shelter_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
