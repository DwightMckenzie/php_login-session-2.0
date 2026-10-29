-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 29, 2026 at 12:47 AM
-- Server version: 8.0.46-0ubuntu0.24.04.4
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `session_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `register_date`) VALUES
(1, 'Dwight1980', '$2y$10$k2z0q4FmiHApZdxTQdQlyOK.ZuFJQPRcOx/v6FgTsRHt48hgpRcUa', 'dmckenzie2010@example.com', '2026-01-01 18:46:06'),
(2, 'johnnydoe', '$2y$10$9npB.2sAU1ljHn9O2.FNK.ignBTHoApdhUaKwz.0p1J2VM7FCW44q', 'johndoe@email.com', '2026-01-02 18:47:24'),
(3, 'dannydude2', 'b3cd2265d992e6db22d95fff840571d2', 'dans@example.com', '2026-01-01 20:06:43'),
(4, 'denny123', '$2y$10$dxqTVYAG4GfF7vNI0QAT6u/xDPrVycNag.M5BTDCR3E6RnWJwIoJS', 'denny@example.com', '2026-03-01 20:06:55'),
(5, 'dannyDude', '$2y$10$0hiz7Ux/ps0jzgdpF1/3v.90NMyGYsBYyK4BHGwYYTfaBg219JGwS', 'danny2013@example.com', '2026-10-24 22:53:42'),
(6, 'Abby1970', '$2y$10$UVXoUeQf4GBrYD1.Ga2vHeMoC/J8OdlWFbqmqT1c8DADcDLvRK7D2', 'abb1979@example.com', '2026-10-25 00:31:39'),
(7, 'JennyG', '$2y$10$Hmz3HcyK5x5d72Dqe3J7eOm6h7qXt.xiIbOnKvP9rpFhmyYmVDiiS', 'jgump@example.com', '2026-10-25 00:36:20'),
(9, 'SomeBody', '$2y$10$we1bus3/XRZTB5nhMFlFNOTxA4rndkLsQLH8idVzQphB/TUFflO3q', 'somebdy@example.com', '2026-10-28 01:04:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
