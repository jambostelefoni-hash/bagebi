-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 10, 2026 at 04:04 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u814212997_mtskhet4`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_statuses`
--

CREATE TABLE `active_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `created_at` varchar(45) DEFAULT NULL,
  `updated_at` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `active_statuses`
--

INSERT INTO `active_statuses` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'მომლოდინე', NULL, NULL),
(2, 'აქტიური', NULL, NULL),
(3, 'დამთავრებული', NULL, NULL),
(4, 'გასული', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_age_ranges`
--

CREATE TABLE `group_age_ranges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `range` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `group_age_ranges`
--

INSERT INTO `group_age_ranges` (`id`, `range`) VALUES
(1, '2-3'),
(2, '3-4'),
(3, '4-5'),
(4, '5-6');

-- --------------------------------------------------------

--
-- Table structure for table `international_ratings_tmp`
--

CREATE TABLE `international_ratings_tmp` (
  `Country_ID` int(11) DEFAULT NULL,
  `Column_1_DB_SCORE` decimal(3,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `international_ratings_tmp`
--

INSERT INTO `international_ratings_tmp` (`Country_ID`, `Column_1_DB_SCORE`) VALUES
(4, 44.1),
(8, 67.7),
(12, 48.6),
(24, 41.3),
(28, 60.3),
(31, 76.7),
(32, 59.0),
(36, 81.2),
(40, 78.7),
(44, 59.9),
(48, 76.0),
(50, 45.0),
(51, 74.5),
(52, 57.9),
(56, 75.0),
(64, 66.0),
(68, 51.7),
(70, 65.4),
(72, 66.2),
(76, 59.1),
(84, 55.5),
(90, 55.3),
(96, 70.1),
(100, 72.0),
(104, 46.8),
(108, 46.8),
(112, 74.3),
(116, 53.8),
(120, 46.1),
(124, 79.6),
(132, 55.0),
(140, 35.6),
(144, 61.8),
(148, 36.9),
(152, 72.6),
(156, 77.9),
(158, 80.9),
(170, 70.1),
(174, 47.9),
(178, 39.5),
(180, 36.2),
(188, 69.2),
(191, 73.6),
(196, 73.4),
(203, 76.3),
(204, 52.4),
(208, 85.3),
(212, 60.5),
(214, 60.0),
(218, 57.7),
(222, 65.3),
(226, 41.1),
(231, 48.0),
(232, 21.6),
(233, 80.6),
(242, 61.5),
(246, 80.2),
(250, 76.8),
(262, 60.5),
(266, 45.0),
(268, 83.7),
(270, 50.3),
(276, 79.7),
(288, 60.0),
(296, 46.9),
(300, 68.4),
(308, 53.4),
(320, 62.6),
(324, 49.4),
(328, 55.5),
(332, 40.7),
(340, 56.3),
(344, 85.3),
(348, 73.4),
(352, 79.0),
(356, 71.0),
(360, 69.6),
(364, 58.5),
(368, 44.7),
(372, 79.6),
(376, 76.7),
(380, 72.9),
(384, 60.7),
(388, 69.7),
(392, 78.0),
(398, 79.6),
(400, 69.0),
(404, 73.2),
(410, 84.0),
(414, 67.4),
(417, 67.8),
(418, 50.8),
(422, 54.3),
(426, 59.4),
(428, 80.3),
(430, 43.2),
(434, 32.7),
(440, 81.6),
(442, 69.6),
(450, 47.7),
(454, 60.9),
(458, 81.5),
(462, 53.3),
(466, 52.9),
(470, 66.1),
(478, 51.1),
(480, 81.5),
(484, 72.4),
(496, 67.8),
(498, 74.4),
(499, 73.8),
(504, 73.4),
(508, 55.0),
(512, 70.0),
(516, 61.4),
(524, 63.2),
(528, 76.1),
(548, 61.1),
(554, 86.8),
(558, 54.4),
(562, 56.8),
(566, 56.9),
(578, 82.6),
(583, 48.1),
(584, 50.9),
(585, 53.7),
(586, 61.0),
(591, 66.6),
(598, 59.8),
(600, 59.1),
(604, 68.7),
(608, 62.8),
(616, 76.4),
(620, 76.5),
(624, 43.2),
(626, 39.4),
(630, 70.1),
(634, 68.7),
(642, 73.3),
(643, 78.2),
(646, 76.5),
(659, 54.6),
(662, 63.7),
(670, 57.1),
(674, 64.2),
(678, 45.0),
(682, 71.6),
(686, 59.3),
(688, 75.7),
(690, 61.7),
(694, 47.5),
(702, 86.2),
(703, 75.6),
(704, 69.8),
(705, 76.5),
(706, 20.0),
(710, 67.0),
(716, 54.5),
(724, 77.9),
(728, 34.6),
(729, 44.8),
(740, 47.5),
(748, 59.5),
(752, 82.0),
(756, 76.6),
(760, 42.0),
(762, 61.3),
(764, 80.1),
(768, 62.3),
(776, 61.4),
(780, 61.3),
(784, 80.9),
(788, 68.7),
(792, 76.8),
(800, 60.0),
(804, 70.2),
(807, 80.7),
(818, 60.1),
(826, 83.5),
(834, 54.5),
(840, 84.0),
(854, 51.4),
(858, 61.5),
(860, 69.9),
(862, 30.2),
(882, 62.1),
(887, 31.8),
(894, 66.9);

-- --------------------------------------------------------

--
-- Table structure for table `kindergarteners`
--

CREATE TABLE `kindergarteners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `municipality_id` bigint(20) DEFAULT NULL,
  `kindergarten_id` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `active_status_id` bigint(20) DEFAULT 1,
  `graduate` tinyint(4) DEFAULT 0,
  `kids_personal_number` varchar(45) DEFAULT NULL,
  `kids_first_name` varchar(45) DEFAULT NULL,
  `kids_last_name` varchar(45) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `mother_personal_number` varchar(45) DEFAULT NULL,
  `mother_first_name` varchar(45) DEFAULT NULL,
  `mother_last_name` varchar(45) DEFAULT NULL,
  `father_personal_number` varchar(45) DEFAULT NULL,
  `father_first_name` varchar(45) DEFAULT NULL,
  `father_last_name` varchar(45) DEFAULT NULL,
  `mobile_number` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kindergartens`
--

CREATE TABLE `kindergartens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `municipality_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(60) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kindergarten_group_age_range`
--

CREATE TABLE `kindergarten_group_age_range` (
  `kindergarten_id` bigint(20) UNSIGNED NOT NULL,
  `group_age_range` bigint(20) UNSIGNED NOT NULL,
  `space_length` varchar(45) DEFAULT '0',
  `space_filled` varchar(45) DEFAULT '0',
  `space_free` varchar(45) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kindergartner_priorities`
--

CREATE TABLE `kindergartner_priorities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kindergartner_id` bigint(20) DEFAULT NULL,
  `priority_id` bigint(20) DEFAULT NULL,
  `has_permission` tinyint(4) DEFAULT 0,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kindergartner_priorities`
--

INSERT INTO `kindergartner_priorities` (`id`, `kindergartner_id`, `priority_id`, `has_permission`, `created_at`, `updated_at`) VALUES
(1, 10, 1, 1, NULL, '2021-09-15'),
(2, 51, 1, 1, '2021-09-15', '2021-09-15'),
(3, 52, 2, 1, '2021-09-15', '2021-09-15'),
(4, 53, 2, 1, '2021-09-15', '2021-09-15'),
(5, 56, 1, 0, '2021-09-20', '2021-09-20'),
(6, 57, 1, 1, '2022-07-03', '2022-07-03'),
(7, 58, 1, 0, '2023-07-12', '2023-07-12'),
(8, 59, 3, 1, '2023-07-23', '2023-07-23'),
(9, 61, 4, 0, '2023-07-23', '2023-07-23'),
(10, 64, 3, 0, '2023-11-22', '2023-11-22'),
(11, 65, 3, 0, '2024-01-24', '2024-01-24'),
(12, 71, 3, 0, '2025-09-27', '2025-10-01'),
(13, 72, 4, 0, '2025-10-01', '2025-10-01'),
(14, 78, 3, 1, '2025-10-01', '2025-10-01'),
(15, 75, 5, 0, '2025-10-01', '2025-10-01'),
(16, 74, 14, 0, '2025-10-01', '2025-10-01');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2025_10_01_144040_add_birth_date_to_kindergarteners_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE `municipalities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

CREATE TABLE `priorities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(145) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`id`, `name`, `created_at`, `updated_at`) VALUES
(3, 'სოციალურად დაუცველი ბავშვები', '2023-07-23', '2025-09-29'),
(4, 'სსსმ ბავშვები', '2023-07-23', '2023-11-22'),
(5, 'იმ ოჯახის ბავშვები, რომელთა შვილი (შვილები) სარგებლობენ იმავე სკოლამდელი დაწესებულების მომსახურებით.', '2023-11-20', '2025-09-20'),
(11, 'მარტოხელა და მრავალშვილიანი, მშობლის სტატუსის მქონე შვილები.', '2023-11-22', '2023-11-22'),
(12, 'ა(ა)იპ სკოლამდელი აღზრდის დაწესებულებათა გაერთიანების ადმინისტრაციის თანამშრომელთა და საბავშვო ბაგა-ბაღში დასაქმებულ პერსონალთა შვილები.', '2023-11-22', '2025-09-20'),
(13, 'სკოლამდელი აღზრდის დაწესებულების მიმდებარე ტერიტორიაზე მცხოვრები ბავშვები.', '2025-09-20', '2025-09-20'),
(14, 'ბავშვები, რომელთა მშობლები მუშაობენ სკოლამდელი დაწესებულების მიმდებარე ტერიტორიაზე და სხვა.', '2025-09-20', '2025-09-20');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(45) DEFAULT NULL,
  `object` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`object`)),
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `slug`, `object`, `created_at`, `updated_at`) VALUES
(22, 'date', '{\"start\":\"09\\/15\\/2025\",\"end\":\"07\\/31\\/2026\"}', '2021-09-11', '2025-10-01'),
(23, 'basic', '{\"canPorting\":false,\"nottification\":null,\"isLearningStart\":false,\"isPrioritetiesStart\":false,\"isRegistrationStart\":\"true\"}', '2021-09-11', '2025-10-01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_administrator` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `is_administrator`, `created_at`, `updated_at`) VALUES
(9, 'ადმინისტრაცია', 'vasokima@gmail.com', NULL, '$2y$10$7o52BFt7LzoYpdQ4YI8jVeEzkdVEW9.LNDc7s/lwxala98dWb1LOa', NULL, 0, '2023-11-22 06:53:07', '2026-02-03 19:06:17'),
(11, 'გვანცა', 'sabavshvobagebi2025@gmail.com', NULL, '$2y$10$hXm5aRBSeaechsa4kMMv4.kX0RGW/syiN5TSbjkH1/m1py8ZZzy.a', NULL, 0, '2025-10-01 02:54:48', '2025-10-01 05:03:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_statuses`
--
ALTER TABLE `active_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_age_ranges`
--
ALTER TABLE `group_age_ranges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `kindergarteners`
--
ALTER TABLE `kindergarteners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `kindergartens`
--
ALTER TABLE `kindergartens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kindergartens_municipalities_municipality_id_idx` (`municipality_id`);

--
-- Indexes for table `kindergarten_group_age_range`
--
ALTER TABLE `kindergarten_group_age_range`
  ADD PRIMARY KEY (`kindergarten_id`,`group_age_range`),
  ADD KEY `fk_group_age_range_group_age_range_idx` (`group_age_range`);

--
-- Indexes for table `kindergartner_priorities`
--
ALTER TABLE `kindergartner_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `fk_regions_municipalities_region_id_idx` (`region_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `priorities`
--
ALTER TABLE `priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_statuses`
--
ALTER TABLE `active_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_age_ranges`
--
ALTER TABLE `group_age_ranges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kindergarteners`
--
ALTER TABLE `kindergarteners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `kindergartens`
--
ALTER TABLE `kindergartens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `kindergartner_priorities`
--
ALTER TABLE `kindergartner_priorities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `priorities`
--
ALTER TABLE `priorities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kindergartens`
--
ALTER TABLE `kindergartens`
  ADD CONSTRAINT `fk_kindergartens_municipalities_municipality_id` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kindergarten_group_age_range`
--
ALTER TABLE `kindergarten_group_age_range`
  ADD CONSTRAINT `fk_group_age_range_group_age_range` FOREIGN KEY (`group_age_range`) REFERENCES `group_age_ranges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kindergartens_kindergarten_id` FOREIGN KEY (`kindergarten_id`) REFERENCES `kindergartens` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD CONSTRAINT `fk_municipalities_regions_region_id` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
