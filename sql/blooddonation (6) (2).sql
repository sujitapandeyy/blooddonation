-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2024 at 03:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blooddonation`
--

-- --------------------------------------------------------

--
-- Table structure for table `bloodbank`
--

CREATE TABLE `bloodbank` (
  `id` int(11) UNSIGNED NOT NULL,
  `service_type` enum('24-hour','Custom') NOT NULL,
  `service_start_time` time DEFAULT NULL,
  `service_end_time` time DEFAULT NULL,
  `image` varchar(255) DEFAULT 'img/slide1.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bloodbank`
--

INSERT INTO `bloodbank` (`id`, `service_type`, `service_start_time`, `service_end_time`, `image`) VALUES
(21, '24-hour', '00:00:00', '23:59:59', '../upload/abc.png'),
(22, 'Custom', '09:00:00', '22:00:00', '../upload/slide4.png'),
(23, '24-hour', '00:00:00', '23:59:59', '../upload/slide3.png'),
(26, 'Custom', '05:00:00', '17:00:00', '../upload/main1.jpg'),
(27, '24-hour', '00:00:00', '23:59:59', '../upload/land4.png'),
(33, '24-hour', '00:00:00', '23:59:59', '../upload/land2.png'),
(36, '24-hour', '00:00:00', '23:59:59', 'img/slide1.png'),
(37, '24-hour', '00:00:00', '23:59:59', 'img/slide1.png'),
(39, '24-hour', '00:00:00', '23:59:59', 'img/slide1.png');

-- --------------------------------------------------------

--
-- Table structure for table `blood_bank_ratings`
--

CREATE TABLE `blood_bank_ratings` (
  `id` int(11) NOT NULL,
  `blood_bank_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_bank_ratings`
--

INSERT INTO `blood_bank_ratings` (`id`, `blood_bank_id`, `user_id`, `rating`, `created_at`) VALUES
(2, 22, 1, 4, '2024-09-22 11:35:39'),
(3, 23, 1, 4, '2024-09-22 11:36:22'),
(4, 22, 9, 1, '2024-09-22 11:37:21'),
(5, 21, 9, 5, '2024-09-22 13:03:43'),
(6, 26, 1, 2, '2024-09-24 11:23:45'),
(7, 21, 1, 1, '2024-09-24 16:05:29'),
(8, 23, 12, 1, '2024-09-26 16:19:51'),
(10, 22, 12, 4, '2024-09-27 12:24:08'),
(11, 33, 34, 4, '2024-09-27 12:25:23'),
(12, 33, 32, 4, '2024-09-27 12:33:11'),
(17, 33, 31, 2, '2024-09-28 19:53:32'),
(19, 22, 31, 4, '2024-09-29 10:38:15'),
(20, 26, 34, 5, '2024-09-29 11:11:17'),
(21, 27, 1, 3, '2024-09-30 21:23:25'),
(22, 37, 35, 5, '2024-09-30 22:54:30'),
(23, 36, 35, 4, '2024-09-30 22:57:18');

-- --------------------------------------------------------

--
-- Table structure for table `blood_details`
--

CREATE TABLE `blood_details` (
  `id` int(11) UNSIGNED NOT NULL,
  `donor_email` varchar(225) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` char(1) NOT NULL,
  `dob` date NOT NULL,
  `weight` float NOT NULL,
  `bloodgroup` varchar(3) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(15) NOT NULL,
  `bloodqty` float NOT NULL,
  `collection` date NOT NULL,
  `bloodbank_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire` timestamp NULL DEFAULT NULL,
  `donor_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_details`
--

INSERT INTO `blood_details` (`id`, `donor_email`, `name`, `gender`, `dob`, `weight`, `bloodgroup`, `address`, `contact`, `bloodqty`, `collection`, `bloodbank_id`, `created_at`, `expire`, `donor_id`) VALUES
(15, 'lab@gmail.com', 'lab', 'M', '2001-09-17', 67, 'A+', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', '9876543212', 20, '2024-09-24', 22, '2024-05-24 12:31:22', '2024-07-05 12:31:22', 12),
(19, 'hari@gmail.com', 'hari thapa', 'M', '2001-09-10', 66, 'A+', 'Bishnumati, Dallu, Chhauni, Kathmandu-15, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44600, Nepal', '9876544567', 30, '2024-09-25', 23, '2024-09-25 01:32:40', '2024-11-06 01:32:40', 25),
(20, 'lab@gmail.com', 'lab', 'M', '2001-09-17', 67, 'A+', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', '9876543212', 0, '2024-09-25', 22, '2024-09-25 04:09:21', '2024-11-06 04:09:21', 12),
(21, 'kush@gmail.com', 'prince kush', 'M', '2001-09-17', 67, 'A+', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', '9876543212', 21, '2024-09-25', 26, '2024-09-25 04:11:54', '2024-11-06 04:11:54', 13),
(23, 'lab@gmail.com', 'lab', 'M', '2001-09-17', 48, 'A+', 'Lainchaur, Kathmandu-26, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 25515, Nepal', '9876543212', 1, '2024-09-26', 22, '2024-09-26 15:30:18', '2024-11-07 15:30:18', 12),
(24, 'varun@gmail.com', 'varun Dhawan', 'M', '2088-12-31', 45, 'O+', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', '9878765643', 200, '2024-09-27', 33, '2024-09-27 06:03:07', '2024-11-08 06:03:07', 31),
(25, 'siddharth@gmail.com', 'siddharth Malhotra', 'M', '1987-02-03', 77, 'O+', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', '987645321', 200, '2024-09-27', 33, '2024-09-27 06:05:32', '2024-11-08 06:05:32', 32),
(26, 'varun@gmail.com', 'varun Dhawan', 'M', '2088-12-31', 45, 'O+', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', '9878765643', 200, '2024-09-27', 22, '2024-09-27 06:07:56', '2024-11-08 06:07:56', 31),
(27, 'alia@gmail.com', 'Alia Bhatt', 'F', '1988-01-11', 60, 'O+', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', '9876453212', 100, '2024-09-28', 33, '2024-09-28 14:24:04', '2024-11-09 14:24:04', 34),
(28, 'alia@gmail.com', 'Alia Bhatt', 'M', '1988-01-11', 60, 'O+', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', '9876453212', 200, '2024-09-28', 26, '2024-09-28 15:08:19', '2024-11-09 15:08:19', 34),
(29, 'ram@gmail.com', 'ram babu', 'M', '2001-09-25', 60, 'A+', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', '9803293516', 200, '2024-09-30', 37, '2024-09-30 17:13:48', '2024-11-11 17:13:48', 35),
(30, 'ram@gmail.com', 'ram babu', 'M', '2001-09-25', 60, 'A+', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', '9803293516', 200, '2024-09-30', 36, '2024-09-30 17:15:19', '2024-11-11 17:15:19', 35),
(32, 'hari@gmail.com', 'hari thapa', 'M', '2001-09-10', 66, 'A+', 'Bishnumati, Dallu, Chhauni, Kathmandu-15, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44600, Nepal', '9876544567', 200, '2024-10-01', 37, '2024-10-01 01:12:30', '2024-11-12 01:12:30', 25);

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `bloodgroup` varchar(200) NOT NULL,
  `requester_name` varchar(255) NOT NULL,
  `requester_email` varchar(255) NOT NULL,
  `requester_phone` varchar(50) NOT NULL,
  `donation_address` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `bloodbank_id` int(11) UNSIGNED NOT NULL,
  `delivery_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `bloodgroup`, `requester_name`, `requester_email`, `requester_phone`, `donation_address`, `quantity`, `message`, `request_date`, `status`, `bloodbank_id`, `delivery_time`) VALUES
(25, 'A+', 'sujit', 'suj@gmail.com', '987654321', 'battar', 20, 'jsdbc wcjjhdc jssdbcjhdc', '2024-08-15 12:54:50', 'Pending', 21, NULL),
(26, 'A+', 'sujit', 'suj@gmail.com', '987654321', 'bir hospital', 20, 'hellojsdncjd', '2024-08-15 12:55:18', 'Approved', 27, '00:15:00'),
(31, 'A+', 'sujit', 'suj@gmail.com', '987654321', 'bir hospital', 20, 'hello i need it urgently', '2024-08-16 01:31:31', 'Pending', 21, NULL),
(32, 'A+', 'sujit', 'suj@gmail.com', '987654321', 'bir hospital', 10, 'i need it urgently', '2024-08-16 01:33:04', 'Approved', 27, '00:30:00'),
(34, 'A-', 'sujita', 'sujita@gmail.com', '9876543212', 'Maitighar, Thapathali, Kathmandu-11, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44617, Nepal', 20, 'contact me in this number', '2024-09-17 10:51:15', 'Approved', 21, '01:05:00'),
(35, 'A-', 'sujita', 'sujita@gmail.com', '9876543212', 'Maitighar, Thapathali, Kathmandu-11, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44617, Nepal', 20, 'contact me in this number', '2024-09-17 10:51:47', 'Pending', 21, NULL),
(36, 'A-', 'suji', 'sujita@gmail.com', '987654321', 'Maitighar, Thapathali, Kathmandu-11, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44617, Nepal', 30, 'contact me in this number', '2024-09-17 11:00:04', 'Pending', 21, NULL),
(37, 'A-', 'sam', 'sam@gmail.com', '9876543212', 'abc', 10, 'please contact in mobile', '2024-09-17 16:18:57', 'Pending', 21, NULL),
(38, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 20, 'its urgent contact on my mobile number', '2024-09-17 17:26:19', 'Pending', 21, NULL),
(39, 'AB+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 10, 'hii', '2024-09-20 11:03:16', 'Pending', 21, NULL),
(46, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 22, 'hii', '2024-09-21 20:18:01', 'Approved', 22, '01:10:00'),
(48, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 22, 'hi', '2024-09-29 09:09:46', 'Approved', 22, '01:10:00'),
(49, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 10, 'i need', '2024-09-30 14:30:52', 'Pending', 22, NULL),
(50, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 20, 'hhhh', '2024-09-30 14:31:20', 'Pending', 22, NULL),
(51, 'A+', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 6, 'it is very urgent', '2024-09-30 17:27:52', 'Approved', 22, '00:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `campaign_date` date NOT NULL,
  `description` text NOT NULL,
  `bloodbank_id` int(10) UNSIGNED NOT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `campaign_name`, `contact_number`, `campaign_date`, `description`, `bloodbank_id`, `location`, `latitude`, `longitude`) VALUES
(1, 'SaveLife Campaign', '9805432176', '2024-08-07', 'skjdfhdfkjghdfbvxc igdughkjsd ksjhfkjsdvbv kgkfhkwhsdfsd', 27, '', NULL, NULL),
(2, 'SaveLife Campaign', '9805432176', '2024-08-07', 'skjdfhdfkjghdfbvxc igdughkjsd ksjhfkjsdvbv kgkfhkwhsdfsd', 27, '', NULL, NULL),
(3, 'SaveLife Campaign', '9805432176', '2024-08-16', 'sefjhdsdhvgsdvhbjdhbvgfweyfuwef weyriwefweuif', 27, '', NULL, NULL),
(4, 'SaveLife Campaign', '9805432176', '2024-08-07', 'jhsdfhdsfhsdhvbd sdhgfhsdgfhdshfksf wejgfhgfr', 27, '', NULL, NULL),
(5, 'ham sath sath he', '9847847545', '2024-08-07', 'sdfsdhfhdvhjfdgdfgjvhcbvhcxbvhdg', 27, 'Paknajol, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71554820, 85.30845770),
(6, 'life saving campain', '987654321', '2024-09-27', 'its for saving life of people ', 27, 'Kathmandu', 27.71819000, 85.30966660),
(7, 'life saving campain', '987654321', '2024-08-19', 'its for saving life of people ', 27, 'Kathmandu', 0.00000000, 0.00000000),
(8, 'abc', '123456789', '2024-10-04', 'save life', 21, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 30.71819000, 80.30966660),
(9, 'macd', '123456789', '2024-10-06', 'save life,donate blood', 21, 'Machhapokhari, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 00971, Nepal', 27.73528730, 85.30582420),
(10, 'bacd', '123456789', '2024-10-01', 'save life,donate blood', 21, 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 27.89825350, 85.14634770),
(11, 'pokhara camp', '123456789', '2024-10-01', 'save life', 21, 'Pokhara, Banfikot-08, Banfikot, Western Rukum District, Karnali Province, Nepal', 28.70814250, 82.42726420),
(13, 'sorakhutte camp', '9876543213', '2024-10-02', 'hello hello', 22, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71819000, 85.30966660),
(14, 'machha camp', '986745321', '2024-10-11', 'its an emergency campaign', 26, 'Machhapokhari, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 00971, Nepal', 27.73528730, 85.30582420),
(16, 'machhaabc camp', '986745321', '2024-09-24', 'hehehe', 26, 'Machhapokhari, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 00971, Nepal', 27.73528730, 85.30582420),
(19, 'sora camp', '9876543223', '2024-10-04', 'its mmmmmmm', 22, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71819000, 85.30966660),
(20, 'sora camp', '9876543223', '2024-10-04', 'its mmmmmmm', 22, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71819000, 85.30966660),
(21, 'sora camp', '9876543223', '2024-10-04', 'its mmmmmmm', 22, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71819000, 85.30966660),
(22, 'sora camp', '9876543223', '2024-10-04', 'its mmmmmmm', 22, 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 27.71819000, 85.30966660),
(23, 'birgunj camp', '9876543212', '2024-10-04', 'from 10 am to 5 pm', 33, 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', 27.01351960, 84.87638040);

-- --------------------------------------------------------

--
-- Table structure for table `donation_requests`
--

CREATE TABLE `donation_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `blood_bank_id` int(11) UNSIGNED NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `quantity` int(100) NOT NULL,
  `donor_email` varchar(255) NOT NULL,
  `message` varchar(225) DEFAULT NULL,
  `appointment_time` timestamp(5) NULL DEFAULT NULL,
  `responsetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_requests`
--

INSERT INTO `donation_requests` (`id`, `blood_bank_id`, `request_date`, `status`, `quantity`, `donor_email`, `message`, `appointment_time`, `responsetime`) VALUES
(8, 21, '2024-09-19 04:15:44', 'Pending', 21, 'lab@gmail.com', 'i am kasjdjd', NULL, '2024-09-19 04:15:44'),
(9, 21, '2024-09-24 11:14:02', 'Pending', 10, 'lab@gmail.com', 'i am free tomorrow', NULL, '2024-09-24 11:14:02'),
(10, 21, '2024-09-24 11:18:35', 'Pending', 20, 'lab@gmail.com', 'im free', NULL, '2024-09-24 11:18:35'),
(20, 23, '2024-09-26 10:22:12', 'Pending', 1, 'lab@gmail.com', 'hi', NULL, '2024-09-26 10:22:12'),
(26, 22, '2024-09-29 09:38:23', 'Pending', 200, 'kush@gmail.com', 'hiiii', NULL, '2024-09-29 09:38:23'),
(29, 22, '2024-09-30 19:40:26', 'Pending', 200, 'lab@gmail.com', 'i am free tomorrow', NULL, '2024-09-30 19:40:26');

-- --------------------------------------------------------

--
-- Table structure for table `donor`
--

CREATE TABLE `donor` (
  `id` int(11) UNSIGNED NOT NULL,
  `donor_blood_type` varchar(3) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `last_donation_date` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT '../upload/defaultimage.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donor`
--

INSERT INTO `donor` (`id`, `donor_blood_type`, `dob`, `weight`, `gender`, `last_donation_date`, `profile_image`) VALUES
(12, 'A+', '2001-09-17', 48, 'Male', '2024-01-01', '../upload/heroo.png'),
(13, 'A+', '2001-09-17', 67, 'Male', '2024-05-17', '../upload/abcd.png'),
(15, 'O-', '2001-09-18', 76, 'Male', '2024-05-10', 'upload/defaultimage.png'),
(25, 'A+', '2001-09-10', 66, 'Male', '2024-01-01', '../upload/defaultimage.png'),
(31, 'O+', '1988-12-31', 45, 'Male', '2024-09-27', '../upload/defaultimage.png'),
(32, 'O+', '1987-02-03', 77, 'Male', '2024-06-27', '../upload/defaultimage.png'),
(34, 'O+', '1988-12-11', 60, 'Female', '2024-09-28', '../upload/defaultimage.png'),
(35, 'A+', '2001-09-25', 60, 'Male', '2024-09-30', '../upload/defaultimage.png'),
(38, 'A-', '2001-01-11', 70, 'Male', '2024-01-01', '../upload/defaultimage.png');

-- --------------------------------------------------------

--
-- Table structure for table `donorblood_request`
--

CREATE TABLE `donorblood_request` (
  `id` int(11) UNSIGNED NOT NULL,
  `donor_id` int(11) UNSIGNED NOT NULL,
  `donor_email` varchar(255) NOT NULL,
  `requester_name` varchar(255) NOT NULL,
  `requester_email` varchar(255) NOT NULL,
  `requester_phone` varchar(50) NOT NULL,
  `donation_address` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `bloodgroup` varchar(3) NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `responsetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donorblood_request`
--

INSERT INTO `donorblood_request` (`id`, `donor_id`, `donor_email`, `requester_name`, `requester_email`, `requester_phone`, `donation_address`, `quantity`, `message`, `request_date`, `status`, `bloodgroup`, `delivery_time`, `responsetime`) VALUES
(1, 12, 'lab@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 20, 'its urgent contact on my mobile number', '2024-09-17 17:35:46', 'Completed', 'A+', '01:10:00', '2024-09-19 10:03:54'),
(2, 12, 'lab@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 20, 'i need it urgently', '2024-09-25 04:05:25', 'Pending', 'A+', '01:21:00', '2024-09-26 10:54:56'),
(3, 13, 'kush@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 200, 'i need it urgently ', '2024-09-29 13:25:46', 'Pending', 'A+', NULL, NULL),
(4, 12, 'lab@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 200, 'i need it urgently', '2024-09-30 16:32:16', 'Pending', 'A+', NULL, NULL),
(5, 12, 'lab@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 200, 'i need it urgently', '2024-09-30 16:32:38', 'Pending', 'A+', NULL, NULL),
(7, 12, 'lab@gmail.com', 'Sujita', 'sujita@gmail.com', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 200, 'its urgent ', '2024-09-30 17:27:13', 'Completed', 'A+', '00:10:00', '2024-09-30 19:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_type` enum('User','Donor','Admin','BloodBank') NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `address`, `user_type`, `latitude`, `longitude`) VALUES
(1, 'Sujita', 'sujita@gmail.com', '$2y$10$fRhgrhvKCJGQRsJKmjDRHOGpk1Wb93Eqj.7uUj/h.NRt.jyY9FSXq', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 'User', 27.718190, 85.309666),
(9, 'sam', 'sam@gmail.com', '$2y$10$HVnC9prNM0HVjM7Ws2UPM.eR1e5LVuxxwhit6Oh50fvkwCTpIQdmu', '987654321', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 'User', 27.718190, 85.309667),
(12, 'lab', 'lab@gmail.com', '$2y$10$NWr/w80L/cjZcorwpoE4veHJFRCiv5RgPzdu9E.lPLB/1t8V7j7xu', '9876543212', 'Lainchaur, Kathmandu-26, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 25515, Nepal', 'Donor', 27.719410, 85.315162),
(13, 'prince kush', 'kush@gmail.com', '$2y$10$Aa3lqBRCLVWzb9Ht/u6BdusBMS3D6JAr2X89IAqPEQKwADEkiFS.C', '9876543212', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'Donor', 27.898254, 85.146348),
(15, 'nik', 'nik@gmail.com', '$2y$10$IOceNIvwdIp4iutvwVKL5eC9Fwybi.Upms640KQtoskLobsDbNvl2', '82365833453465', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 'Donor', 27.718190, 85.309667),
(16, 'admin', 'admin@gmail.com', '$2y$10$G8keCLbl4LFdKeRBOvjrT.orq90/H.wNoGe9vePSrxQakS64zs4c.', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 'Admin', 27.718190, 85.309667),
(21, 'hamro bb', 'hamro@gmail.com', '$2y$10$ICl/m9vAFU7EovOPWW/Kburki22bUqvYtMa0dklv0xmciVve6J0ji', '9876510982', 's, Bansagadhi-02, Bansagadhi, Bardiya, Nepal', 'BloodBank', 28.253416, 81.582472),
(22, 'sorakhutte bb', 'sorakhutte@gmail.com', '$2y$10$L3c7GqByla/UNI8qZFKd0.YsZe.hKuhlEl6IVMTplhMoY9UBOyM4S', '9876543212', 'Sorakhutte, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 46001, Nepal', 'BloodBank', 27.718190, 85.309667),
(23, 'Nuwakot bb', 'nuwakot@gmail.com', '$2y$10$Roakh4VEbvLgR41RCNumK.2zA04Ykl5nNpyZs3zANpaHoVDzU9HO6', '9876543212', 'Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'BloodBank', 27.898254, 85.146348),
(25, 'hari thapa', 'hari@gmail.com', '$2y$10$DAiVoQXXu4vk9XIrMfV6QOcoCFKrDFA6NJYh9ePtrtQfTkW68MVda', '9876544567', 'Bishnumati, Dallu, Chhauni, Kathmandu-15, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 44600, Nepal', 'Donor', 27.708591, 85.302684),
(26, 'machhapokhari bb', 'machhapokhari@gmail.com', '$2y$10$5RR92Z5BpGJzrxy0xjOaDOqfXewayv6I/YiSoRFzkwDim/a2.w9Eu', '9867452312', 'Machhapokhari, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 00971, Nepal', 'BloodBank', 27.735287, 85.305824),
(27, 'pokhara bb', 'pokhara@gmail.com', '$2y$10$3CqpcQQO8Ax8H5DkcG12Qe7q5dyKNKgZe6nkOxuUVmmaBXasRLQ3q', '986745321', 'Pokhara, Sunchhahari-01, Sunchhahari, Rolpa, Nepal', 'BloodBank', 28.415938, 82.858218),
(31, 'varun Dhawan', 'varun@gmail.com', '$2y$10$Q/GBz4ANd87KmAL/wIDm/.KiIYU6kYOCB3kSjuQ0Yk86LK1LSgsxm', '9878765643', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', 'Donor', 27.013520, 84.876380),
(32, 'siddharth Malhotra', 'siddharth@gmail.com', '$2y$10$mRk571sIMIAnTnrbbXyhRe7FTivNv3k6HtuIP.JjVBomtwB9veTRS', '987645321', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', 'Donor', 27.013520, 84.876380),
(33, 'Birgunj bb', 'birgunj@gmail.com', '$2y$10$1TU9ngDa4hLK7BNFGFE1w.SvP8NdPA6iMSGQ0VyY5fO3kBQA9NvX2', '98765432234', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', 'BloodBank', 27.013520, 84.876380),
(34, 'Alia Bhatt', 'alia@gmail.com', '$2y$10$xKpSATt4HPLinCwTCs7Gs.7uac78BA1tdAdOyBbVAgrcdgDMO3F9W', '9876453212', 'Birgunj, Parsa, Madhesh Province, 44300, Nepal', 'Donor', 27.013520, 84.876380),
(35, 'ram babu', 'ram@gmail.com', '$2y$10$pqZx7ic1u4uMWlFqit5W6uYW/rb7Ib3TxEGQubqgU04Z1ISHeJkXu', '9803293516', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'Donor', 27.898254, 85.146348),
(36, 'bidur bb', 'bidur@gmail.com', '$2y$10$DT5wLpSnoWjKkh5LzjiRxO51ngZY/Q47.aYBIITqW8izvu5sf3hsu', '9876543212', 'Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'BloodBank', 27.895260, 85.146446),
(37, 'Battar bb', 'battar@gmail.com', '$2y$10$xiIXQ/MEa.0GgjBb3RvNRublwm5/EqDANan4i59gCDFmxqedogHSW', '9876543212', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'BloodBank', 27.898254, 85.146348),
(38, 'harii adhakari', 'harii@gmail.com', '$2y$10$jcjmMFK31RQ9jh6xiQSkuu828ca7UBzECuUX87fw6jGaZTqWOU8Oi', '9876543212', 'Batār Bāzār, Bidur-04, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'Donor', 27.898254, 85.146348),
(39, 'trishuli bb', 'trishuli@gmail.com', '$2y$10$H0Ea646NpmT/bAxRIw3hIu2BM3zABPaYhPBm4A6I7YXO8xvZ8sllm', '9876542345', 'Trishuli, 28A012R, Bidur-09, Bidur, Nuwakot, Bagmati Province, 44900, Nepal', 'BloodBank', 27.921479, 85.145994);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bloodbank`
--
ALTER TABLE `bloodbank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blood_bank_ratings`
--
ALTER TABLE `blood_bank_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blood_bank_id` (`blood_bank_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blood_details`
--
ALTER TABLE `blood_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bloodbank_id` (`bloodbank_id`),
  ADD KEY `fk_donor` (`donor_id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bloodbank_id` (`bloodbank_id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bloodbank_id` (`bloodbank_id`);

--
-- Indexes for table `donation_requests`
--
ALTER TABLE `donation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_donor_email` (`donor_email`),
  ADD KEY `fk_blood_bank_id` (`blood_bank_id`);

--
-- Indexes for table `donor`
--
ALTER TABLE `donor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donorblood_request`
--
ALTER TABLE `donorblood_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_id` (`donor_id`);

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
-- AUTO_INCREMENT for table `blood_bank_ratings`
--
ALTER TABLE `blood_bank_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `blood_details`
--
ALTER TABLE `blood_details`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `donation_requests`
--
ALTER TABLE `donation_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `donorblood_request`
--
ALTER TABLE `donorblood_request`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bloodbank`
--
ALTER TABLE `bloodbank`
  ADD CONSTRAINT `bloodbank_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_bank_ratings`
--
ALTER TABLE `blood_bank_ratings`
  ADD CONSTRAINT `blood_bank_ratings_ibfk_1` FOREIGN KEY (`blood_bank_id`) REFERENCES `bloodbank` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blood_bank_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_details`
--
ALTER TABLE `blood_details`
  ADD CONSTRAINT `blood_details_ibfk_1` FOREIGN KEY (`bloodbank_id`) REFERENCES `bloodbank` (`id`),
  ADD CONSTRAINT `fk_donor` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`id`);

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `fk_bloodbank_id` FOREIGN KEY (`bloodbank_id`) REFERENCES `bloodbank` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`bloodbank_id`) REFERENCES `bloodbank` (`id`);

--
-- Constraints for table `donation_requests`
--
ALTER TABLE `donation_requests`
  ADD CONSTRAINT `donation_requests_ibfk_1` FOREIGN KEY (`donor_email`) REFERENCES `users` (`email`),
  ADD CONSTRAINT `fk_blood_bank_id` FOREIGN KEY (`blood_bank_id`) REFERENCES `bloodbank` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donor`
--
ALTER TABLE `donor`
  ADD CONSTRAINT `donor_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donorblood_request`
--
ALTER TABLE `donorblood_request`
  ADD CONSTRAINT `donorblood_request_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
