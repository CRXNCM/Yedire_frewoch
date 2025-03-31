-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 02:59 PM
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
-- Database: `yedire_frewoch`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$uKrjAsCWYPV.S6XZnLbz0.HKUVm4KlbV9olM1VZ7Yk1W0IWwTMEHO', '2025-03-28 20:45:55');

-- --------------------------------------------------------

--
-- Table structure for table `bank_info`
--

CREATE TABLE `bank_info` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `routing_number` varchar(50) DEFAULT NULL,
  `swift_code` varchar(50) DEFAULT NULL,
  `bank_address` text DEFAULT NULL,
  `bank_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_info`
--

INSERT INTO `bank_info` (`id`, `bank_name`, `account_name`, `account_number`, `routing_number`, `swift_code`, `bank_address`, `bank_image`, `is_active`, `last_updated`, `payment_link`) VALUES
(1, 'cbe', 'weseb', '10000343243', '234', 'dfndnk', 'dire', 'uploads/bank_images/1743204164_cbe-logo.png', 1, '2025-03-29 02:22:44', NULL),
(2, 'nib', 'wesen', '2930239', '392030', 'dkfj', 'dire', 'uploads/bank_images/1743204694_nib-logo.png', 1, '2025-03-29 02:31:34', NULL),
(4, 'awash', 'wesen', '1000349483', '934893', '', 'dire dawa', 'uploads/bank_images/1743239763_awash-logo.png', 1, '2025-03-29 12:16:03', '');

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`id`, `name`, `region`, `description`, `created_at`) VALUES
(1, 'Government', 'dire dawa', 'helping', '2025-03-28 19:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donation_id` varchar(50) NOT NULL,
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(100) NOT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `donor_address` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_date` datetime NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `donor_message` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `footer_links`
--

CREATE TABLE `footer_links` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `footer_links`
--

INSERT INTO `footer_links` (`id`, `title`, `url`, `display_order`, `is_active`, `date_added`, `last_updated`) VALUES
(1, 'about', 'http://localhost/yedire_frewoch/manage_images.php', 1, 1, '2025-03-29 00:15:24', '2025-03-29 00:15:24'),
(2, 'about', 'http://localhost/yedire_frewoch/manage_images.php', 1, 1, '2025-03-29 00:27:36', '2025-03-29 00:27:36'),
(3, 'about', 'http://localhost/yedire_frewoch/manage_images.php', 1, 1, '2025-03-29 00:28:42', '2025-03-29 00:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `children_served` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `name`, `description`, `region`, `children_served`, `created_at`) VALUES
('aftesa', 'aftesa', 'small village school', '', 100, '2025-03-28 22:07:37'),
('dechatu_hedase', 'Dechatu hedase', 'dechatu', '', 200, '2025-03-28 22:09:14'),
('Dire shcool', 'ewfbjhjkbfekw', 'wekfjbefkwbmf', 'Region 2', 87, '2025-03-28 20:50:05'),
('hhhhhhh', 'hhhhhhh', 'ewugisfdhjwnjd', 'Region 3', 122, '2025-03-28 21:02:12'),
('sabiyan_no_1', 'sabiyan no 1', 'small villaeg school', '', 120, '2025-03-29 09:04:34');

-- --------------------------------------------------------

--
-- Table structure for table `school_images`
--

CREATE TABLE `school_images` (
  `id` int(11) NOT NULL,
  `school_id` varchar(20) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_images`
--

INSERT INTO `school_images` (`id`, `school_id`, `image_name`, `title`, `description`, `is_featured`, `upload_date`) VALUES
(1, 'Dire shcool', 'img_67e71482479b2.jpg', '', '', 0, '2025-03-28 21:28:34'),
(2, 'Dire shcool', 'img_67e719331e26c.png', 'schol', 'sdnjsd', 0, '2025-03-28 21:48:35'),
(3, 'dechatu_hedase', 'img_67e71e4e2c60f.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(4, 'dechatu_hedase', 'img_67e71e4e2cbbe.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(5, 'dechatu_hedase', 'img_67e71e4e2d274.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(6, 'dechatu_hedase', 'img_67e71e4e2d64e.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(7, 'dechatu_hedase', 'img_67e71e4e2db34.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(8, 'dechatu_hedase', 'img_67e71e4e2df1e.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(9, 'dechatu_hedase', 'img_67e71e4e2e385.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(10, 'dechatu_hedase', 'img_67e71e4e2e751.jpg', 'morning meal', 'dsdnmdn', 0, '2025-03-28 22:10:22'),
(11, 'sabiyan_no_1', 'img_67e7b7eab786d.jpg', 'kurse', 'bread', 0, '2025-03-29 09:05:46'),
(12, 'sabiyan_no_1', 'img_67e7b7eab86c5.jpg', 'kurse', 'bread', 0, '2025-03-29 09:05:46'),
(13, 'sabiyan_no_1', 'img_67e7b7eab8f98.jpg', 'kurse', 'bread', 0, '2025-03-29 09:05:46'),
(14, 'sabiyan_no_1', 'img_67e7b7eab9a87.jpg', 'kurse', 'bread', 0, '2025-03-29 09:05:46'),
(15, 'sabiyan_no_1', 'img_67e7b7eabaa3b.jpg', 'kurse', 'bread', 0, '2025-03-29 09:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `social_links`
--

CREATE TABLE `social_links` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon_class` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_links`
--

INSERT INTO `social_links` (`id`, `platform`, `url`, `icon_class`, `display_order`, `is_active`, `date_added`, `last_updated`) VALUES
(3, 'instagram', 'https://www.instagram.com/moss7_777', 'fab fa-instagram', 2, 1, '2025-03-29 00:29:35', '2025-03-29 00:29:35'),
(5, 'addisu', 'https://github.com/CRXNCM/Yedire_frewoch', 'fab fa-facebook', 2, 1, '2025-03-29 08:42:49', '2025-03-29 08:42:49'),
(6, 'facebook', 'http://localhost/yedire_frewoch/index.php', 'fab fa-facebook', 1, 1, '2025-03-29 09:11:21', '2025-03-29 09:11:21');

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) NOT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sponsors`
--

INSERT INTO `sponsors` (`id`, `name`, `description`, `logo_path`, `website_url`, `is_active`, `created_at`) VALUES
(1, 'cbe', 'best bank', 'images/sponsors/sponsor_67e79f006ac39.png', '', 1, '2025-03-29 07:19:28'),
(2, 'nib bank', '', 'images/sponsors/sponsor_67e79f3880249.png', '', 1, '2025-03-29 07:20:24'),
(3, 'awash bank', '', 'images/sponsors/sponsor_67e79f4b13ff9.png', '', 1, '2025-03-29 07:20:43'),
(4, 'dashin bank', '', 'images/sponsors/sponsor_67e79f6b64401.png', '', 1, '2025-03-29 07:21:15'),
(5, 'berhan bank', '', 'images/sponsors/sponsor_67e79f89ebefd.png', '', 1, '2025-03-29 07:21:45'),
(6, 'just', 'we support', 'images/sponsors/sponsor_67e7b8ad56b56.png', '', 1, '2025-03-29 09:09:01');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `rating` int(1) DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `role`, `message`, `image_path`, `rating`, `is_active`, `created_at`) VALUES
(1, 'Kedir', 'Major', 'serywhb ywrewgtye55eyey y re', 'images/testimonials/1743203485_log-modified.png', 5, 1, '2025-03-28 20:11:25'),
(2, 'Kedir', 'Major', 'serywhb ywrewgtye55eyey y re', 'images/testimonials/1743204952_log-modified.png', 5, 1, '2025-03-28 20:35:52'),
(0, 'yoni', 'mgmt', 'hellow world', 'images/testimonials/1743210126_img_67e719331e26c.png', 5, 1, '2025-03-29 01:02:06'),
(0, 'dr abey', 'prime ministor', 'very good', 'images/testimonials/1743239258_Wesen.png', 5, 1, '2025-03-29 09:07:38');

-- --------------------------------------------------------

--
-- Table structure for table `urgent_messages`
--

CREATE TABLE `urgent_messages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `urgency_level` enum('Urgent','Important','Normal') NOT NULL DEFAULT 'Normal',
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `action_link` varchar(255) DEFAULT NULL,
  `action_text` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `urgent_messages`
--

INSERT INTO `urgent_messages` (`id`, `title`, `message`, `image_path`, `urgency_level`, `status`, `action_link`, `action_text`, `created_at`, `updated_at`) VALUES
(1, 'we need help', 'please', 'images/urgent/1743209230_bg-bunner-2.png', 'Urgent', 'inactive', 'https://www.instagram.com/yon_ii_/', 'Help Now', '2025-03-29 00:47:10', '2025-03-29 00:47:10'),
(2, 'need', 'we need help on supplies', 'images/urgent/1743239568_img_67e719331e26c.png', 'Urgent', 'inactive', '', 'Help Now', '2025-03-29 09:12:48', '2025-03-29 09:12:48');

-- --------------------------------------------------------

--
-- Table structure for table `volunteers`
--

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `join_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteers`
--

INSERT INTO `volunteers` (`id`, `name`, `email`, `phone`, `join_date`) VALUES
(1, 'M&M', 'mosisaboneya4@gmail.com', '0928984993', '2025-03-28 18:57:31'),
(0, 'nahom', 'comradencm@gmail.com', '0925254765', '2025-03-28 22:23:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bank_info`
--
ALTER TABLE `bank_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `donation_id` (`donation_id`);

--
-- Indexes for table `footer_links`
--
ALTER TABLE `footer_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`);

--
-- Indexes for table `school_images`
--
ALTER TABLE `school_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `social_links`
--
ALTER TABLE `social_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `urgent_messages`
--
ALTER TABLE `urgent_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bank_info`
--
ALTER TABLE `bank_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `footer_links`
--
ALTER TABLE `footer_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_images`
--
ALTER TABLE `school_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `social_links`
--
ALTER TABLE `social_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `urgent_messages`
--
ALTER TABLE `urgent_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `school_images`
--
ALTER TABLE `school_images`
  ADD CONSTRAINT `school_images_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
