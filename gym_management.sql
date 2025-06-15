-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 07, 2025 at 04:27 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(50) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'Admin', '$2y$10$Sr9UppXD49RNzGmR2eR2auYhdrqfEFKR2MdjlvNsaLzXf757MDYiS');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `status`) VALUES
(1, 1, '2024-12-30', 'Absent'),
(2, 2, '2024-12-30', 'Present'),
(3, 3, '2024-12-30', 'Absent'),
(4, 4, '2024-12-30', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `months` varchar(250) NOT NULL,
  `year` varchar(250) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` int(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `months`, `year`, `payment_date`, `amount`) VALUES
(1, 'January,February', '2024', '2024-12-30', 6000),
(1, 'March,April', '2024', '2024-12-30', 6000),
(1, 'May,June,July', '2024', '2024-12-30', 9000),
(1, 'August,September', '2024', '2024-12-30', 6000),
(1, 'October', '2024', '2024-12-30', 3000),
(1, 'November,December', '2024', '2024-12-30', 6000),
(2, 'January,February,March', '2024', '2024-12-30', 6000),
(2, 'April,May,June,July', '2024', '2024-12-30', 8000),
(2, 'August,September,October', '2024', '2024-12-30', 6000),
(2, 'November', '2024', '2024-12-30', 2000),
(3, 'January,February,March', '2024', '2024-12-30', 9000),
(3, 'April,May,June,July', '2024', '2024-12-30', 12000),
(3, 'August,September,October', '2024', '2024-12-30', 9000),
(3, 'November,December', '2024', '2024-12-30', 6000),
(4, 'January,February,March', '2024', '2024-12-30', 6000),
(4, 'April,May,June', '2024', '2024-12-30', 6000),
(4, 'October,November', '2024', '2024-12-30', 4000),
(2, 'January', '2025', '2025-01-04', 2000),
(2, 'February', '2025', '2025-02-06', 2000),
(2, 'March', '2025', '2025-02-06', 2000),
(2, 'April', '2025', '2025-02-06', 2000),
(2, 'May', '2025', '2025-02-06', 2000),
(1, 'January', '2025', '2025-02-06', 3000),
(1, 'February', '2025', '2025-02-06', 3000),
(3, 'January', '2025', '2025-02-06', 3000),
(1, 'March', '2025', '2025-02-06', 3000),
(1, 'April', '2025', '2025-02-06', 3000),
(1, 'May,June', '2025', '2025-02-06', 6000),
(4, 'January', '2025', '2025-02-06', 2000),
(4, 'February,March', '2025', '2025-02-06', 4000),
(3, 'February', '2025', '2025-02-07', 3000),
(3, 'March', '2025', '2025-02-07', 3000),
(3, 'April', '2025', '2025-02-07', 3000),
(3, 'May', '2025', '2025-02-07', 3000),
(2, 'June', '2025', '2025-02-07', 1500);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` varchar(250) NOT NULL,
  `contact` varchar(250) NOT NULL,
  `schedule_type` varchar(250) NOT NULL,
  `weigth` int(250) NOT NULL,
  `payment_day` varchar(250) NOT NULL,
  `schedule_day` varchar(250) NOT NULL,
  `birthday` date NOT NULL,
  `registered_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `gender`, `contact`, `schedule_type`, `weigth`, `payment_day`, `schedule_day`, `birthday`, `registered_date`) VALUES
('0001', 'Binidu Ranasinghe', 'Male', '0783833814', 'Cardio', 90, '25', '10', '2004-12-09', '2024-12-30 11:09:09'),
('0002', 'Dulaj', 'Male', '0783833814', 'Fitness', 50, '2', '20', '2014-10-15', '2024-12-30 11:09:36'),
('0003', 'Sanidi Dilara', 'Female', '0783833814', 'Cardio', 100, '25', '10', '2003-06-11', '2024-12-30 11:10:50'),
('0004', 'Onadi Ranasinghe', 'Female', '0783833814', 'Fitness', 40, '3', '20', '2005-12-24', '2025-01-04 08:13:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
