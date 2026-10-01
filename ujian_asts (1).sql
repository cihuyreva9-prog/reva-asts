-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 07:52 AM
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
-- Database: `ujian_asts`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `ttl` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `nisn`, `ttl`, `gender`, `email`, `address`, `foto`, `no_hp`) VALUES
(1, 'Leanne Graham', '123131444', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Sincere@april.biz', 'Kulas Light, Apt. 556, Gwenborough, 92998-3874', NULL, NULL),
(2, 'Ervin Howell', '262363463', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Shanna@melissa.tv', 'Victor Plains, Suite 879, Wisokyburgh, 90566-7771', NULL, NULL),
(3, 'Clementine Bauch', '632641362', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Nathan@yesenia.net', 'Douglas Extension, Suite 847, McKenziehaven, 59590-4157', NULL, NULL),
(4, 'Patricia Lebsack', '745821963', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Julianne.OConner@kory.org', 'Hoeger Mall, Apt. 692, South Elvis, 53919-4257', NULL, NULL),
(5, 'Chelsey Dietrich', '856932174', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Lucio_Hettinger@annie.ca', 'Skiles Walks, Suite 351, Roscoeview, 33263', NULL, NULL),
(6, 'Mrs. Dennis Schulist', '967143285', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Karley_Dach@jasper.info', 'Norberto Crossing, Apt. 950, South Christy, 23505-1337', NULL, NULL),
(7, 'Kurtis Weissnat', '178254396', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Telly.Hoeger@billy.biz', 'Rex Trail, Suite 280, Howemouth, 58804-1099', NULL, NULL),
(8, 'Nicholas Runolfsdottir V', '289365417', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Sherwood@rosamond.me', 'Ellsworth Summit, Suite 729, Aliyaview, 45169', NULL, NULL),
(9, 'Glenna Reichert', '390476528', 'PONOROGO, 12 Agustus 2010', 'MALE', 'Chaim_McDermott@dana.io', 'Dayna Park, Suite 449, Bartholomebury, 76495-3109', NULL, NULL),
(10, 'Clementina DuBuque', '401587639', 'PONOROGO, 12 Agustus 2010', 'FEMALE', 'Rey.Padberg@karina.biz', 'Kattie Turnpike, Suite 198, Lebsackbury, 31428-2261', NULL, NULL),
(11, 'RASYA ANANDITYO KEYSHA', '2828282828', 'MADIUN, 28 AGUSTUS 2008', 'MALE', 'rasya@gmail.com', 'MLILIR, DOLOPO, MADIUN RT 03 RW 01', NULL, NULL),
(13, 'REVA ALVAY', '2222222222', 'PULUNG 1 JANUARI 1999', 'MALE', 'reva@gmail.com', 'eukiefieg', NULL, NULL),
(14, 'SAKTI HARLAN', '2132132132', 'PONOROGO 2 DESEMBER 1888', 'MALE', 'sakti@gmail.com', 'hikiibgiigifFEWF', NULL, NULL),
(16, 'ZWEI MAUN', '3244567433', 'MAGETAN 12 AGUSTUS 2000', 'FEMALE', 'zwei@gmail.com', 'ngunut', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_login`
--

CREATE TABLE `users_login` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_login`
--

INSERT INTO `users_login` (`id`, `nama`, `username`, `email`, `no_hp`, `password`, `created_at`) VALUES
(2, 'Administrator', 'admin', 'admin@gmail.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCq5n5jG6jR6H4Q7R7m', '2026-10-01 01:13:57'),
(4, 'Reva Alvay', 'reva', 'reva@gmail.com', NULL, '$2y$12$i.y1.WoIlfh2I4EN7LRNW.kZp4Yg.AI3wR3pDBiXv8woXunnLeGRy', '2026-10-01 01:22:38'),
(6, 'rasya', 'rva', 'rsa@gmail.com', '6285177863330', '$2y$10$C4txclJ1.M0O1BRdgONr2.ur2DuARDoQXrQMSkMsaseQRU/14m1IS', '2026-10-01 04:34:49'),
(7, 'REVA', 'revaa', 'rva@gmail.com', '6285235477023', '$2y$10$tpr5fJ2TaCKYuY8eQZBkiOupw8IYCEF5Cw3ala.typ2seUyPFxYQe', '2026-10-01 05:28:22'),
(8, 'febyy', 'feb11', 'febb@gmail.com', '6285643898497', '$2y$10$nGgChZ.eizHeE.ktEm8Hke1HO/dKaeJo2cD7tCJz0AHwtG.rT.b5.', '2026-10-01 05:33:23'),
(9, 'Silvi Saranya Bagus Aira', 'silpiiiii', 'silpi@gmail.com', '6281249375911', '$2y$10$x/namLMR/ePeOJLwgI6DwueE7Wjvmh5vpV37nwTTz/R.zLS3USQo2', '2026-10-01 05:35:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_login`
--
ALTER TABLE `users_login`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users_login`
--
ALTER TABLE `users_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
