-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2025 at 03:01 PM
-- Server version: 9.3.0
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ukm`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `payment_method` enum('DANA','GOPAY','OVO','LINKAJA','SHOPEEPAY','QRIS','BCA','MANDIRI','BRI','BNI','DANAMON','PERMATA','CIMB') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` enum('Pending','Process','Delivery','Completed','Canceled') NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `username`, `address`, `city`, `postal_code`, `email`, `phone`, `note`, `payment_method`, `status`, `create_at`, `update_at`) VALUES
(3, 'Mojangzzz', 'Perumahan Pesona Vista', 'Bogor ', 16820, 'rektword@gmail.com', '085123658885', 'KIRIM SECEPATNYA ', 'DANA', 'Completed', '2025-06-15 09:18:37', '2025-06-15 09:57:05'),
(4, 'Mojangzzz', 'Dffd', 'Cfddc', 827372, 'rektword@gmail.com', '085123658885', '', 'BCA', 'Completed', '2025-06-15 10:10:20', '2025-06-15 13:40:55'),
(5, 'adminJF1', 'Perum Surya Kencana', 'Jakarta', 18401, 'admin@jadoel.com', '085941395388', 'asasas', 'OVO', 'Completed', '2025-06-15 11:21:37', '2025-06-15 13:52:33'),
(6, 'adminJF1', 'Perumahan Surya Kencana', 'Jarkata', 12345, 'admin@jadoel.com', '085941395388', '', 'SHOPEEPAY', 'Delivery', '2025-06-15 11:29:41', '2025-06-15 13:52:23'),
(7, 'adminJF1', 'dasdqsdasd1234213', 'asasc', 123, 'admin@jadoel.com', '085941395388', '', 'OVO', 'Completed', '2025-06-15 11:53:02', '2025-06-15 13:52:14'),
(8, 'adminJF1', 'Perumahan Pesona Kahuripan 5', 'Jakarta', 16850, 'admin@jadoel.com', '085941395388', '', 'GOPAY', 'Process', '2025-06-15 12:00:44', '2025-06-15 13:52:06'),
(9, 'adminJF1', 'Perumahan Pesona Kahuripan 6', 'Esperanza', 12345, 'admin@jadoel.com', '085941395388', 'Tes', 'LINKAJA', 'Delivery', '2025-06-15 12:03:45', '2025-06-15 13:51:58'),
(10, 'adminJF1', 'Perumahan pesona kahuripan 7', 'Jakarta', 16710, 'admin@jadoel.com', '085941395388', '', 'BRI', 'Process', '2025-06-15 12:05:26', '2025-06-15 13:51:50'),
(11, 'Mojangzzz', 'Perumahan Pesona Kahuripan 9', 'Jakarta', 17834, 'rektword@gmail.com', '085123658885', '', 'SHOPEEPAY', 'Completed', '2025-06-15 12:13:03', '2025-06-15 13:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(7, 3, 6, 2, 10000.00),
(8, 4, 6, 10, 10000.00),
(9, 5, 5, 1, 15000.00),
(10, 6, 2, 1, 25000.00),
(11, 7, 2, 1, 25000.00),
(12, 8, 4, 1, 30000.00),
(13, 9, 6, 1, 10000.00),
(14, 10, 6, 1, 10000.00),
(15, 11, 3, 1, 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `stock` int NOT NULL,
  `price` varchar(100) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_available` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `product_image`, `stock`, `price`, `create_at`, `is_available`, `updated_at`) VALUES
(2, 'Bakpia Jogja', '684dd123c3acc_1749930275.png', 100, '25000', '2025-06-14 19:44:35', 1, '2025-06-15 13:41:49'),
(3, 'Lapis Legit', '684e625e0775e_1749967454.jpg', 100, '25000', '2025-06-15 06:04:14', 1, '2025-06-15 13:41:59'),
(4, 'Bolu Lapis Talas', '684e627be400b_1749967483.jpg', 100, '30000', '2025-06-15 06:04:43', 1, '2025-06-15 13:42:11'),
(5, 'Kuping Gajah', '684e629418a6a_1749967508.jpeg', 100, '15000', '2025-06-15 06:05:08', 1, '2025-06-15 13:42:37'),
(6, 'Dodol Garut', '684e62a634bfb_1749967526.jpg', 100, '10000', '2025-06-15 06:05:26', 1, '2025-06-15 13:42:53');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `update_product_availability` BEFORE UPDATE ON `products` FOR EACH ROW BEGIN
    IF NEW.stock <= 0 THEN
        SET NEW.is_available = 0;
    ELSE
        SET NEW.is_available = 1;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `profil_image` varchar(255) NOT NULL,
  `role` enum('Admin','Customer') NOT NULL DEFAULT 'Customer',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `pass`, `email`, `phone`, `profil_image`, `role`, `create_at`, `updated_at`) VALUES
(10, 'Muhammad Fauzan', 'adminJF1', '$2y$10$UR4Vuhsz4kLc0goturmnqOth.ToInS.lGapS5yjvCNJrECwOCl1kW', 'admin@jadoel.com', '085941395388', 'profile_10_1749979587.jpg', 'Admin', '2025-06-12 15:21:00', '2025-06-15 09:26:27'),
(11, 'Rozan Fathin Yafi', 'Mojangzzz', '$2y$10$tQcX8VBPpZLajaGvDit9bO83tHcLoQ2w3LkYFhkRuTzugGES5l5wm', 'rektword@gmail.com', '085123658885', 'profile_11_1749979015.jpg', 'Customer', '2025-06-12 15:53:52', '2025-06-15 11:00:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
