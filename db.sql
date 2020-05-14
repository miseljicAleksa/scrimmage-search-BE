-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 13, 2020 at 01:30 PM
-- Server version: 5.7.26
-- PHP Version: 7.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `scrimmag_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(10) NOT NULL,
  `created_by` int(10) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `city` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `date_and_time` datetime DEFAULT NULL,
  `description` text,
  `sport_id` int(10) DEFAULT NULL,
  `rate` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `created_by`, `created_on`, `city`, `address`, `state`, `zip_code`, `lat`, `lon`, `date_and_time`, `description`, `sport_id`, `rate`) VALUES
(1, 80, '2019-12-13 12:37:22', 'Las Vegas', NULL, NULL, NULL, 321312312, 1231231231231, '2019-12-13 11:00:00', 'The soccer fixture for tourists', 1, NULL),
(3, 33, '2020-03-16 19:09:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) NOT NULL,
  `user_to` int(10) NOT NULL,
  `user_from` int(10) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text NOT NULL,
  `viewed` smallint(1) NOT NULL DEFAULT '0',
  `request_id` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_to`, `user_from`, `created_on`, `message`, `viewed`, `request_id`) VALUES
(1, 60, 1, '2020-04-12 07:40:18', 'njanjanjanjanjanjanja', 0, NULL),
(3, 60, 1, '2020-04-12 07:40:30', 'njanjanjanjanjanjanja', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notfications`
--

CREATE TABLE `notfications` (
  `id` int(10) NOT NULL,
  `user_to` int(10) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text NOT NULL,
  `viewed` smallint(1) NOT NULL DEFAULT '0',
  `message_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` int(10) NOT NULL,
  `years_of_experience` varchar(20) DEFAULT NULL COMMENT 'years',
  `officiating_fee` varchar(100) DEFAULT NULL,
  `sertificate` varchar(255) DEFAULT NULL,
  `user_id` int(10) NOT NULL,
  `official_description` text,
  `lon` double NOT NULL,
  `lat` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `officials`
--

INSERT INTO `officials` (`id`, `years_of_experience`, `officiating_fee`, `sertificate`, `user_id`, `official_description`, `lon`, `lat`) VALUES
(1, '12.1', '1222.00', '', 80, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(10) NOT NULL,
  `years_of_experience` varchar(100) DEFAULT NULL,
  `interested_in` varchar(255) DEFAULT NULL COMMENT 'which kind of games is player interested',
  `player_image` varchar(255) DEFAULT NULL,
  `user_id` int(10) NOT NULL,
  `team_name` varchar(255) DEFAULT NULL,
  `player_description` varchar(255) DEFAULT NULL,
  `lon` double NOT NULL,
  `lat` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `years_of_experience`, `interested_in`, `player_image`, `user_id`, `team_name`, `player_description`, `lon`, `lat`) VALUES
(1, '1.0', 'Games', '', 80, NULL, NULL, 0, 0),
(2, '3.2', 'Tournament', '', 2, NULL, NULL, 0, 0),
(3, '3.0', 'Season', '', 4, NULL, NULL, 0, 0),
(4, NULL, NULL, 'player_image!', 123, NULL, NULL, 0, 0),
(5, NULL, NULL, 'player_image!', 126, NULL, NULL, 0, 0),
(6, '10', 'Seazons', 'player_image!', 128, 'GREEN DEVILS', 'Type a short description...', 0, 0),
(7, '10', 'Seazons', 'player_image!', 130, 'GREEN DEVILS', 'Type a short description...', 0, 0),
(8, '10', 'Seazons', NULL, 159, 'GREEN DEVILS', 'Type a short description...', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` int(10) NOT NULL,
  `team_id` int(10) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(10) NOT NULL,
  `rate` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(10) NOT NULL,
  `team_id` int(10) DEFAULT NULL,
  `player_id` int(10) DEFAULT NULL,
  `official_id` int(10) DEFAULT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(10) NOT NULL,
  `accepted` smallint(1) NOT NULL DEFAULT '0',
  `accepted_on` date DEFAULT NULL,
  `game_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `team_id`, `player_id`, `official_id`, `created_on`, `created_by`, `accepted`, `accepted_on`, `game_id`) VALUES
(1, 65, NULL, NULL, '2019-12-13 01:00:00', 80, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `id` int(10) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`id`, `name`) VALUES
(1, '\0S\0o\0c\0c\0e\0r'),
(2, '\0T\0e\0n\0n\0i\0s'),
(3, '\0B\0a\0s\0k\0e\0t\0b\0a\0l\0l');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(10) NOT NULL,
  `team_name` varchar(200) NOT NULL,
  `team_gender` varchar(5) NOT NULL DEFAULT 'M' COMMENT 'C - mixed gender',
  `age_group` year(4) NOT NULL,
  `user_id` int(10) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `team_colors` varchar(100) DEFAULT NULL,
  `team_description` text,
  `lon` double NOT NULL,
  `lat` double NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `has_home_field` smallint(1) NOT NULL,
  `sport_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='teams tabel used by entity coach';

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team_name`, `team_gender`, `age_group`, `user_id`, `created_on`, `team_colors`, `team_description`, `lon`, `lat`, `zip_code`, `has_home_field`, `sport_id`) VALUES
(1, 'Wolfs', 'C', 2000, 80, '2019-11-14 13:19:20', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', NULL, 0, 0, '', 0, 1),
(65, '\0C\0h\0e\0e\0r\0l\0e\0a\0d\0e\0r\0s', '\0F', 2002, 80, '2019-11-20 18:07:48', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', NULL, 0, 0, '', 0, 1),
(76, '\0H\0O\0O\0L\0I\0G\0A\0N\0S', '\0M', 2003, 80, '2019-11-20 18:17:46', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', NULL, 0, 0, '', 0, 1),
(105, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 153, '2020-02-03 19:20:35', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0T\0y\0p\0e\0 \0a\0 \0s\0h\0o\0r\0t\0 \0d\0e\0s\0c\0r\0i\0p\0t\0i\0o\0n\0.\0.\0.', 0, 0, '', 0, 1),
(106, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 154, '2020-02-03 19:33:12', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0v\0e\0r\0y\0 \0g\0o\0o\0d\0 \0t\0r\0a\0i\0n\0e\0r\0 \0a\0n\0d\0 \0p\0o\0s\0i\0t\0i\0v\0e\0 \0p\0e\0r\0s\0o\0n\0.\0 \0r\0e\0a\0d\0 \0A\0n\0a\0 \0B\0u\0c\0e\0v\0i\0c\0 \0a\0n\0d\0 \0d\0o\0 \0j\0o\0g\0s\0 \0f\0o\0r\0 \0f\0u\0n', 0, 0, '', 0, 1),
(107, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 156, '2020-02-04 01:07:24', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0T\0y\0p\0e\0 \0a\0 \0s\0h\0o\0r\0t\0 \0d\0e\0s\0c\0r\0i\0p\0t\0i\0o\0n\0.\0.\0.', 0, 0, '', 0, 1),
(108, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 157, '2020-02-04 01:29:27', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0a\0g\0a\0e\0f\0a\0f\0a\0f', 0, 0, '', 0, 1),
(109, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 158, '2020-02-04 01:44:22', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0T\0y\0p\0e\0 \0a\0 \0s\0h\0o\0r\0t\0 \0d\0e\0s\0c\0r\0i\0p\0t\0i\0o\0n\0.\0.\0.', 0, 0, '', 0, 1),
(110, '\0G\0r\0e\0e\0n\0 \0D\0e\0v\0i\0l\0s', '\0M', 2000, 159, '2020-02-04 01:49:21', '\0[\0\"\0#\03\04\0C\07\05\09\0\"\0,\0\"\0#\0A\02\08\04\05\0E\0\"\0]', '\0T\0y\0p\0e\0 \0a\0 \0s\0h\0o\0r\0t\0 \0d\0e\0s\0c\0r\0i\0p\0t\0i\0o\0n\0.\0.\0.', 0, 0, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(64) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bio` text,
  `fbId` varchar(30) DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` set('ACTIVE','DEACTIVATED') NOT NULL DEFAULT 'ACTIVE',
  `properties` text,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `image_file_name` varchar(255) DEFAULT NULL,
  `role` set('MEMBER','MANAGER') NOT NULL DEFAULT 'MEMBER',
  `verification_code` int(4) DEFAULT NULL,
  `mobile_number` varchar(13) DEFAULT NULL,
  `zip_code` varchar(8) DEFAULT NULL,
  `year_born` varchar(4) DEFAULT NULL,
  `profile_type` varchar(20) DEFAULT NULL,
  `sport` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `location`, `bio`, `fbId`, `last_login`, `status`, `properties`, `created_on`, `image_file_name`, `role`, `verification_code`, `mobile_number`, `zip_code`, `year_born`, `profile_type`, `sport`) VALUES
(33, 'aleksa01miseljic@gmail.com', 'dada', 'aleksa', 'miseljic', 'smederevo', 'njanjanjanja', NULL, '2020-01-31 00:00:00', 'ACTIVE', NULL, '2020-01-24 00:00:00', '', 'MEMBER', NULL, NULL, NULL, NULL, NULL, NULL),
(80, 'arsen.leontijevic@gmail.com', '$2y$10$PxcvEtPFWZxkAc0sm6VLn.0fs02/Vsi5LrxtwjqalRT4DOLzmHpC6', 'Nikola', 'Tesla', NULL, 'My short bio... but maybe it\'s not important for the first version of Workmark', '1158161255', '2018-04-17 11:34:17', 'ACTIVE', '{\"nots\":{\"w\":1, \"e\":1}}', '2020-01-24 00:00:00', '', 'MANAGER', NULL, NULL, NULL, NULL, NULL, NULL),
(81, 'dzkvjzd@gafae.com', 'dsdasd', 'Nikola', 'Tesla', NULL, NULL, NULL, '2018-04-17 11:34:17', 'ACTIVE', NULL, '2018-08-23 00:00:00', '', 'MEMBER', NULL, NULL, NULL, NULL, NULL, NULL),
(82, 'john.smith@io.com', '$2y$10$4DmfAQXOOJfE2VsnyvpDOeEovZJEkswVdhq4ooz5mBh845W6hf8im', NULL, NULL, NULL, NULL, NULL, '2020-01-29 12:46:29', 'ACTIVE', NULL, '2020-01-29 12:46:29', NULL, 'MEMBER', 2213, NULL, NULL, NULL, NULL, NULL),
(83, 'john.smith@iod.com', '$2y$10$p7EYneEJs6LZi4tp9NJCaOMvr7BV3FrO3.MkkJ4Ql4J5NJARpSLaO', NULL, NULL, NULL, NULL, NULL, '2020-01-29 12:53:21', 'ACTIVE', NULL, '2020-01-29 12:53:21', NULL, 'MEMBER', 2888, NULL, NULL, NULL, NULL, NULL),
(84, 'john.smith11@iod.com', '$2y$10$XAd9ht3NUmT0iYuGHjF5iej.KY8uJKyA5c1ITmzyT4kZU5dGtccUq', NULL, NULL, NULL, NULL, NULL, '2020-01-29 12:54:51', 'ACTIVE', NULL, '2020-01-29 12:54:51', NULL, 'MEMBER', 4130, NULL, NULL, NULL, NULL, NULL),
(85, 'john.smith1122@iod.com', 'password', NULL, NULL, NULL, NULL, NULL, '2020-01-29 12:59:08', 'ACTIVE', NULL, '2020-01-29 12:59:08', NULL, 'MEMBER', 5019, NULL, NULL, NULL, NULL, NULL),
(86, 'john.smith001122@iod.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 13:03:59', 'ACTIVE', NULL, '2020-01-29 13:03:59', NULL, 'MEMBER', 3781, NULL, NULL, NULL, NULL, NULL),
(87, 'john.smith@io999999.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 15:00:20', 'ACTIVE', NULL, '2020-01-29 15:00:20', NULL, 'MEMBER', 3114, NULL, NULL, NULL, NULL, NULL),
(88, 'john.smith@io000999999.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 15:00:46', 'ACTIVE', NULL, '2020-01-29 15:00:46', NULL, 'MEMBER', 7527, NULL, NULL, NULL, NULL, NULL),
(89, 'john.smith@io1111111.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 15:05:59', 'ACTIVE', NULL, '2020-01-29 15:05:59', NULL, 'MEMBER', 365, NULL, NULL, NULL, NULL, NULL),
(90, 'mojsexyemail@gmail.com', 'mnogoneprobojanpwd', 'milena', 'smites', NULL, NULL, NULL, '2020-01-29 15:08:41', 'ACTIVE', NULL, '2020-01-29 15:08:41', NULL, 'MEMBER', 6911, NULL, NULL, NULL, NULL, NULL),
(91, 'john.smith@321312312io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 15:23:33', 'ACTIVE', NULL, '2020-01-29 15:23:33', NULL, 'MEMBER', 1650, NULL, NULL, NULL, NULL, NULL),
(92, 'john.smithhhhfhfhghfhf@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 16:00:07', 'ACTIVE', NULL, '2020-01-29 16:00:07', NULL, 'MEMBER', 9477, NULL, NULL, NULL, NULL, NULL),
(93, 'john.smith@2222222io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-29 16:21:42', 'ACTIVE', NULL, '2020-01-29 16:21:42', NULL, 'MEMBER', 4133, NULL, NULL, NULL, NULL, NULL),
(94, 'john.smith@222io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 14:27:28', 'ACTIVE', NULL, '2020-01-30 14:27:28', NULL, 'MEMBER', 1465, NULL, NULL, NULL, NULL, NULL),
(95, 'john.smith@kkkkkio.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 14:36:49', 'ACTIVE', NULL, '2020-01-30 14:36:49', NULL, 'MEMBER', 5552, NULL, NULL, NULL, NULL, NULL),
(96, 'john.smithfffff@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 14:44:39', 'ACTIVE', NULL, '2020-01-30 14:44:39', NULL, 'MEMBER', 5348, NULL, NULL, NULL, NULL, NULL),
(97, 'john.smith@io2211111.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 14:59:17', 'ACTIVE', NULL, '2020-01-30 14:59:17', NULL, 'MEMBER', 40, NULL, NULL, NULL, NULL, NULL),
(98, 'john.smith333333@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 15:05:28', 'ACTIVE', NULL, '2020-01-30 15:05:28', NULL, 'MEMBER', 6347, NULL, NULL, NULL, NULL, NULL),
(99, 'john.smith3333@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 15:15:53', 'ACTIVE', NULL, '2020-01-30 15:15:53', NULL, 'MEMBER', 4075, NULL, NULL, NULL, NULL, NULL),
(100, 'john.smith222222@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 15:17:44', 'ACTIVE', NULL, '2020-01-30 15:17:44', NULL, 'MEMBER', 3065, NULL, NULL, NULL, NULL, NULL),
(101, 'john.smithjjjj@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 15:22:17', 'ACTIVE', NULL, '2020-01-30 15:22:17', NULL, 'MEMBER', 3838, NULL, NULL, NULL, NULL, NULL),
(102, 'john.smithaaaa@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:25:39', 'ACTIVE', NULL, '2020-01-30 16:25:39', NULL, 'MEMBER', 6505, NULL, NULL, NULL, NULL, NULL),
(103, 'j22222ohn.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:27:52', 'ACTIVE', NULL, '2020-01-30 16:27:52', NULL, 'MEMBER', 6097, NULL, NULL, NULL, NULL, NULL),
(104, 'john.smith22222@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:33:20', 'ACTIVE', NULL, '2020-01-30 16:33:20', NULL, 'MEMBER', 7787, NULL, NULL, NULL, NULL, NULL),
(105, 'john.hhhhsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:39:40', 'ACTIVE', NULL, '2020-01-30 16:39:40', NULL, 'MEMBER', 5529, NULL, NULL, NULL, NULL, NULL),
(106, 'john.333333smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:46:19', 'ACTIVE', NULL, '2020-01-30 16:46:19', NULL, 'MEMBER', 3715, NULL, NULL, NULL, NULL, NULL),
(107, 'john.smith22222222@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:51:37', 'ACTIVE', NULL, '2020-01-30 16:51:37', NULL, 'MEMBER', 6996, NULL, NULL, NULL, NULL, NULL),
(108, 'john.ffffsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:53:58', 'ACTIVE', NULL, '2020-01-30 16:53:58', NULL, 'MEMBER', 5173, NULL, NULL, NULL, NULL, NULL),
(109, 'john.smith2222211@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 16:59:26', 'ACTIVE', NULL, '2020-01-30 16:59:26', NULL, 'MEMBER', 9959, NULL, NULL, NULL, NULL, NULL),
(110, 'john.smith3332222@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 17:46:16', 'ACTIVE', NULL, '2020-01-30 17:46:16', NULL, 'MEMBER', 5758, NULL, NULL, NULL, NULL, NULL),
(111, 'njanjanjanja@gmail.com', 'password', 'pets', 'detlic', NULL, NULL, NULL, '2020-01-30 17:56:09', 'ACTIVE', NULL, '2020-01-30 17:56:09', NULL, 'MEMBER', 472, NULL, NULL, NULL, NULL, NULL),
(112, 'john.sm@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 18:06:39', 'ACTIVE', NULL, '2020-01-30 18:06:39', NULL, 'MEMBER', 7782, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(113, 'aleksei@gmail.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-30 18:36:48', 'ACTIVE', NULL, '2020-01-30 18:36:48', NULL, 'MEMBER', 918, '0606060', '11300', '2001', 'Coach', 'Soccer'),
(114, 'john.smith2222@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 12:34:42', 'ACTIVE', NULL, '2020-01-31 12:34:42', NULL, 'MEMBER', 9357, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(115, 'john.smith1d11d1d1@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 12:38:03', 'ACTIVE', NULL, '2020-01-31 12:38:03', NULL, 'MEMBER', 8421, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(116, 'john.smithaaaaaf@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 12:41:02', 'ACTIVE', NULL, '2020-01-31 12:41:02', NULL, 'MEMBER', 4557, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(117, 'john.smith3333222111@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 13:11:05', 'ACTIVE', NULL, '2020-01-31 13:11:05', NULL, 'MEMBER', 7973, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(118, 'john.cccvvvsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 13:59:20', 'ACTIVE', NULL, '2020-01-31 13:59:20', NULL, 'MEMBER', 1137, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(119, 'john.vvvveeeeeesmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:00:05', 'ACTIVE', NULL, '2020-01-31 14:00:05', NULL, 'MEMBER', 3217, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(120, 'johnddffff.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:01:36', 'ACTIVE', NULL, '2020-01-31 14:01:36', NULL, 'MEMBER', 7968, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(121, 'johnqqqqq.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:02:53', 'ACTIVE', NULL, '2020-01-31 14:02:53', NULL, 'MEMBER', 3749, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(122, 'john.kkkksmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:10:26', 'ACTIVE', NULL, '2020-01-31 14:10:26', NULL, 'MEMBER', 1466, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(123, 'ppp.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:11:15', 'ACTIVE', NULL, '2020-01-31 14:11:15', NULL, 'MEMBER', 1799, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(124, 'johnnnnbbb.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:29:22', 'ACTIVE', NULL, '2020-01-31 14:29:22', NULL, 'MEMBER', 7253, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(125, 'john.smithrrrrrr@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:29:47', 'ACTIVE', NULL, '2020-01-31 14:29:47', NULL, 'MEMBER', 9744, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(126, 'john22222.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:35:03', 'ACTIVE', NULL, '2020-01-31 14:35:03', NULL, 'MEMBER', 2085, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(127, 'johnfffff.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:43:19', 'ACTIVE', NULL, '2020-01-31 14:43:19', NULL, 'MEMBER', 4634, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(128, 'john22221.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 14:45:03', 'ACTIVE', NULL, '2020-01-31 14:45:03', NULL, 'MEMBER', 2448, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(129, 'john.smithddd@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 15:01:09', 'ACTIVE', NULL, '2020-01-31 15:01:09', NULL, 'MEMBER', 1012, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(130, 'johneeee.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-01-31 15:01:36', 'ACTIVE', NULL, '2020-01-31 15:01:36', NULL, 'MEMBER', 9429, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(131, 'john.smithmmmmmm@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 16:47:17', 'ACTIVE', NULL, '2020-02-01 16:47:17', NULL, 'MEMBER', 3209, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(132, 'john.222222smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 16:51:54', 'ACTIVE', NULL, '2020-02-01 16:51:54', NULL, 'MEMBER', 7327, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(133, 'john.smith2222111@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 17:01:07', 'ACTIVE', NULL, '2020-02-01 17:01:07', NULL, 'MEMBER', 2235, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(134, 'john.smithfef@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 17:05:03', 'ACTIVE', NULL, '2020-02-01 17:05:03', NULL, 'MEMBER', 5394, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(135, 'john.smithmmmm@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 17:18:15', 'ACTIVE', NULL, '2020-02-01 17:18:15', NULL, 'MEMBER', 6621, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(136, 'john.smithkkkk@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-01 17:19:35', 'ACTIVE', NULL, '2020-02-01 17:19:35', NULL, 'MEMBER', 5427, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(137, 'john.smytttttith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 18:56:40', 'ACTIVE', NULL, '2020-02-02 18:56:40', NULL, 'MEMBER', 854, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(138, 'john.yyyyyyysmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 18:57:23', 'ACTIVE', NULL, '2020-02-02 18:57:23', NULL, 'MEMBER', 6325, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(139, 'joh21affafaen.smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 19:07:13', 'ACTIVE', NULL, '2020-02-02 19:07:13', NULL, 'MEMBER', 371, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(140, 'john.sm32f2fdith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 19:36:37', 'ACTIVE', NULL, '2020-02-02 19:36:37', NULL, 'MEMBER', 2365, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(141, 'john.2222fffsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 19:46:22', 'ACTIVE', NULL, '2020-02-02 19:46:22', NULL, 'MEMBER', 9704, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(142, 'john.smithvvvvv@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-02 19:52:17', 'ACTIVE', NULL, '2020-02-02 19:52:17', NULL, 'MEMBER', 1038, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(143, 'john.dfsfassmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 12:20:49', 'ACTIVE', NULL, '2020-02-03 12:20:49', NULL, 'MEMBER', 4932, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(144, 'john.65r46tysmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 12:50:22', 'ACTIVE', NULL, '2020-02-03 12:50:22', NULL, 'MEMBER', 5619, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(145, 'john.dasdsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 12:56:06', 'ACTIVE', NULL, '2020-02-03 12:56:06', NULL, 'MEMBER', 7193, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(146, 'john.wertwetwerrwassmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 13:04:01', 'ACTIVE', NULL, '2020-02-03 13:04:01', NULL, 'MEMBER', 139, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(147, 'john.smithfffft@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 13:11:30', 'ACTIVE', NULL, '2020-02-03 13:11:30', NULL, 'MEMBER', 9934, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(148, 'john.ewrewrsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 13:16:47', 'ACTIVE', NULL, '2020-02-03 13:16:47', NULL, 'MEMBER', 73, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(149, 'john.rewrqwerqwsmith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 13:25:54', 'ACTIVE', NULL, '2020-02-03 13:25:54', NULL, 'MEMBER', 77, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(150, 'john.smith222rff@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 18:36:42', 'ACTIVE', NULL, '2020-02-03 18:36:42', NULL, 'MEMBER', 5528, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(151, 'john.33333smith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 18:39:43', 'ACTIVE', NULL, '2020-02-03 18:39:43', NULL, 'MEMBER', 4697, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(152, 'john.smithssss@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 19:19:26', 'ACTIVE', NULL, '2020-02-03 19:19:26', NULL, 'MEMBER', 6901, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(153, 'john.smithsssqq@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 19:20:00', 'ACTIVE', NULL, '2020-02-03 19:20:00', NULL, 'MEMBER', 3788, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(154, 'john.smithllopp@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 19:32:28', 'ACTIVE', NULL, '2020-02-03 19:32:28', NULL, 'MEMBER', 9183, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(155, 'john.smithddddd@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-03 23:57:54', 'ACTIVE', NULL, '2020-02-03 23:57:54', NULL, 'MEMBER', 6965, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(156, 'john.smithmmmmlll@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-04 01:07:04', 'ACTIVE', NULL, '2020-02-04 01:07:04', NULL, 'MEMBER', 7964, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(157, 'john.smithssssfff@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-04 01:29:11', 'ACTIVE', NULL, '2020-02-04 01:29:11', NULL, 'MEMBER', 3407, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(158, 'john.smithssccff@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-04 01:44:04', 'ACTIVE', NULL, '2020-02-04 01:44:04', NULL, 'MEMBER', 1735, '0606060', '11300', '2000', 'Coach', 'Soccer'),
(159, 'john.smkkkith@io.com', 'password', 'John', 'Smith', NULL, NULL, NULL, '2020-02-04 01:49:04', 'ACTIVE', NULL, '2020-02-04 01:49:04', NULL, 'MEMBER', 4303, '0606060', '11300', '2000', 'Coach', 'Soccer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `lat` (`lat`),
  ADD KEY `lon` (`lon`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_from` (`user_from`),
  ADD KEY `user_to` (`user_to`);

--
-- Indexes for table `notfications`
--
ALTER TABLE `notfications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_to` (`user_to`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_id_2` (`team_id`,`created_by`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `official_id` (`official_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `scrimmage_id` (`game_id`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `age` (`age_group`),
  ADD KEY `created_by` (`user_id`),
  ADD KEY `created_on` (`created_on`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notfications`
--
ALTER TABLE `notfications`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notfications`
--
ALTER TABLE `notfications`
  ADD CONSTRAINT `notfications_ibfk_2` FOREIGN KEY (`user_to`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notfications_ibfk_3` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `officials`
--
ALTER TABLE `officials`
  ADD CONSTRAINT `officials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rates`
--
ALTER TABLE `rates`
  ADD CONSTRAINT `rates_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rates_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`official_id`) REFERENCES `officials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `requests_ibfk_5` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teams_ibfk_3` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
