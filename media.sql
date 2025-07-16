-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 09:34 AM
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
-- Database: `media`
--

-- --------------------------------------------------------

--
-- Table structure for table `actors`
--

CREATE TABLE `actors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `birth_year` int(11) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collections`
--

INSERT INTO `collections` (`id`, `name`, `description`, `created_at`, `image_url`) VALUES
(1, 'אוסף ניסיון', 'אוסף כללי', '2025-07-14 23:42:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `collection_items`
--

CREATE TABLE `collection_items` (
  `id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `added_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_reports`
--

CREATE TABLE `contact_reports` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_requests`
--

INSERT INTO `contact_requests` (`id`, `email`, `message`, `created_at`) VALUES
(3, 'Thisel.db1@gmail.com', 'בדיקה', '2025-07-15 07:24:41'),
(4, 'Thisel.db1@gmail.com', 'פנה אליי\r\nואטפל מיד בבעיה', '2025-07-15 07:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `posters`
--

CREATE TABLE `posters` (
  `id` int(11) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_he` varchar(255) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `imdb_rating` varchar(10) DEFAULT NULL,
  `imdb_link` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `plot` text DEFAULT NULL,
  `type` enum('movie','series') DEFAULT NULL,
  `lang_code` varchar(10) DEFAULT NULL,
  `is_dubbed` tinyint(1) DEFAULT 0,
  `has_subtitles` tinyint(1) DEFAULT 0,
  `tvdb_id` varchar(100) DEFAULT NULL,
  `youtube_trailer` varchar(255) DEFAULT NULL,
  `genre` varchar(255) DEFAULT NULL,
  `actors` text DEFAULT NULL,
  `metacritic_score` varchar(50) DEFAULT NULL,
  `rt_score` varchar(50) DEFAULT NULL,
  `metacritic_link` varchar(255) DEFAULT NULL,
  `rt_link` varchar(255) DEFAULT NULL,
  `imdb_id` varchar(15) DEFAULT NULL,
  `pending` tinyint(4) DEFAULT 0,
  `collection_name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posters`
--

INSERT INTO `posters` (`id`, `title_en`, `title_he`, `year`, `imdb_rating`, `imdb_link`, `image_url`, `plot`, `type`, `lang_code`, `is_dubbed`, `has_subtitles`, `tvdb_id`, `youtube_trailer`, `genre`, `actors`, `metacritic_score`, `rt_score`, `metacritic_link`, `rt_link`, `imdb_id`, `pending`, `collection_name`, `created_at`) VALUES
(2, 'Avatar', '', '2009', '7.9', 'https://www.imdb.com/title/tt0499549/', 'https://m.media-amazon.com/images/M/MV5BMDEzMmQwZjctZWU2My00MWNlLWE0NjItMDJlYTRlNGJiZjcyXkEyXkFqcGc@._V1_SX300.jpg', 'A paraplegic Marine dispatched to the moon Pandora on a unique mission becomes torn between following his orders and protecting the world he feels is his home.', 'movie', 'en', 0, 0, '', '', 'Action, Adventure, Fantasy', 'Sam Worthington, Zoe Saldaña, Sigourney Weaver', '', '', '', '', 'tt0499549', 0, NULL, '2025-07-15 00:11:58'),
(3, 'Covert Affairs', '', '2010–2014', '7.3', 'https://www.imdb.com/title/tt1495708/', 'https://m.media-amazon.com/images/M/MV5BMTM3Nzk5Njc3M15BMl5BanBnXkFtZTcwMTUxNzc4Nw@@._V1_SX300.jpg', 'Fresh out of the Farm, Annie Walker must adapt to the challenging life of a CIA operative under the guidance of her handler, Auggie. But soon she realizes her recruitment might have to do with her last boyfriend rather than her ta...', 'series', 'en', 0, 0, '', '', 'Action, Comedy, Crime', 'Piper Perabo, Christopher Gorham, Kari Matchett', 'https://www.metacritic.com/movie/tale-of-tales?fta', 'https://www.rottentomatoes.com/m/tale_of_tales', '', '', 'tt1495708', 0, NULL, '2025-07-15 02:34:00'),
(4, 'Game of Thrones', '', '2011–2019', '9.2', 'https://www.imdb.com/title/tt0944947/?ref_=nv_sr_srsg_0_tt_6_nm_2_in_0_q_GAM', 'https://m.media-amazon.com/images/M/MV5BMTNhMDJmNmYtNDQ5OS00ODdlLWE0ZDAtZTgyYTIwNDY3OTU3XkEyXkFqcGc@._V1_SX300.jpg', 'Nine noble families fight for control over the lands of Westeros, while an ancient enemy returns after being dormant for millennia.', 'series', 'en', 0, 1, 'https://www.thetvdb.com/series/game-of-thrones/', 'https://www.youtube.com/watch?v=KPLWWIOCOOQ&t=2s&ab_channel=GameofThrones', 'Action, Adventure, Drama', 'Emilia Clarke, Peter Dinklage, Kit Harington', '', '', '', '', 'tt0944947', 0, NULL, '2025-07-15 10:22:51'),
(5, 'House of the Dragon', '', '2022–', '8.3', 'https://www.imdb.com/title/tt11198330/?ref_=nv_sr_srsg_0_tt_8_nm_0_in_0_q_house%2520of', 'https://m.media-amazon.com/images/M/MV5BM2QzMGVkNjUtN2Y4Yi00ODMwLTg3YzktYzUxYjJlNjFjNDY1XkEyXkFqcGc@._V1_SX300.jpg', 'An internal succession war within House Targaryen at the height of its power, 172 years before the birth of Daenerys Targaryen.', 'series', 'en', 0, 0, '', '', 'Action, Adventure, Drama', 'Matt Smith, Emma D\'Arcy, Olivia Cooke', '', '', '', '', 'tt11198330', 0, NULL, '2025-07-15 10:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `poster_categories`
--

CREATE TABLE `poster_categories` (
  `poster_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poster_genres_user`
--

CREATE TABLE `poster_genres_user` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `genre` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poster_languages`
--

CREATE TABLE `poster_languages` (
  `poster_id` int(11) NOT NULL,
  `lang_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poster_likes`
--

CREATE TABLE `poster_likes` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poster_reports`
--

CREATE TABLE `poster_reports` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `report_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `handled_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poster_reports`
--

INSERT INTO `poster_reports` (`id`, `poster_id`, `report_reason`, `created_at`, `handled_at`) VALUES
(1, 2, 'תקלה!', '2025-07-14 21:13:20', NULL),
(3, 2, 'תקלה', '2025-07-14 23:41:13', '2025-07-15 02:42:28'),
(4, 3, 'תקלה', '2025-07-14 23:41:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `poster_similar`
--

CREATE TABLE `poster_similar` (
  `poster_id` int(11) NOT NULL,
  `similar_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poster_similar`
--

INSERT INTO `poster_similar` (`poster_id`, `similar_id`) VALUES
(4, 5),
(5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `poster_votes`
--

CREATE TABLE `poster_votes` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) DEFAULT NULL,
  `visitor_token` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `vote_type` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poster_votes`
--

INSERT INTO `poster_votes` (`id`, `poster_id`, `visitor_token`, `ip_address`, `vote_type`, `created_at`) VALUES
(1, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, '', '2025-07-14 21:08:54'),
(2, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, '', '2025-07-14 21:08:57'),
(3, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, '', '2025-07-14 21:10:22'),
(4, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, '', '2025-07-14 21:10:25'),
(5, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, '', '2025-07-14 21:10:40'),
(6, 1, 'mikjfqmmv7s6eckind0jvcle7i', NULL, 'like', '2025-07-14 21:11:31'),
(7, 2, 'mikjfqmmv7s6eckind0jvcle7i', NULL, 'dislike', '2025-07-14 21:12:32'),
(8, 3, 'mikjfqmmv7s6eckind0jvcle7i', NULL, 'like', '2025-07-14 23:34:03'),
(9, 5, 'mikjfqmmv7s6eckind0jvcle7i', NULL, 'like', '2025-07-15 07:31:23');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_tags`
--

CREATE TABLE `user_tags` (
  `id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `genre` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actors`
--
ALTER TABLE `actors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collection_items`
--
ALTER TABLE `collection_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collection_id` (`collection_id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- Indexes for table `contact_reports`
--
ALTER TABLE `contact_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posters`
--
ALTER TABLE `posters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poster_categories`
--
ALTER TABLE `poster_categories`
  ADD PRIMARY KEY (`poster_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `poster_genres_user`
--
ALTER TABLE `poster_genres_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- Indexes for table `poster_languages`
--
ALTER TABLE `poster_languages`
  ADD PRIMARY KEY (`poster_id`,`lang_code`);

--
-- Indexes for table `poster_likes`
--
ALTER TABLE `poster_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poster_reports`
--
ALTER TABLE `poster_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- Indexes for table `poster_similar`
--
ALTER TABLE `poster_similar`
  ADD PRIMARY KEY (`poster_id`,`similar_id`),
  ADD KEY `similar_id` (`similar_id`);

--
-- Indexes for table `poster_votes`
--
ALTER TABLE `poster_votes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- Indexes for table `user_tags`
--
ALTER TABLE `user_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poster_id` (`poster_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actors`
--
ALTER TABLE `actors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `collection_items`
--
ALTER TABLE `collection_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_reports`
--
ALTER TABLE `contact_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `posters`
--
ALTER TABLE `posters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `poster_genres_user`
--
ALTER TABLE `poster_genres_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poster_likes`
--
ALTER TABLE `poster_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poster_reports`
--
ALTER TABLE `poster_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `poster_votes`
--
ALTER TABLE `poster_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_tags`
--
ALTER TABLE `user_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collection_items`
--
ALTER TABLE `collection_items`
  ADD CONSTRAINT `collection_items_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_items_ibfk_2` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poster_categories`
--
ALTER TABLE `poster_categories`
  ADD CONSTRAINT `poster_categories_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poster_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poster_genres_user`
--
ALTER TABLE `poster_genres_user`
  ADD CONSTRAINT `poster_genres_user_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poster_languages`
--
ALTER TABLE `poster_languages`
  ADD CONSTRAINT `poster_languages_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poster_reports`
--
ALTER TABLE `poster_reports`
  ADD CONSTRAINT `poster_reports_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poster_similar`
--
ALTER TABLE `poster_similar`
  ADD CONSTRAINT `poster_similar_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poster_similar_ibfk_2` FOREIGN KEY (`similar_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tags`
--
ALTER TABLE `user_tags`
  ADD CONSTRAINT `user_tags_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `posters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
