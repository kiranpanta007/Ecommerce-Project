-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 03:43 AM
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
-- Database: `project_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$RwIAWLnng.xB6KOnWNQruOI7sGCzv/rqxv5uJ1N5ygpNta1EXMyz.', '2025-03-14 23:34:02');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Electronics', 'Latest gadgets and devices', 'laptop-1.webp', '2025-03-26 10:25:55'),
(2, 'Clothing', 'Fashionable clothes for all seasons', 'mixer-1.webp', '2025-03-26 10:25:55'),
(3, 'Home & Furniture', 'Furniture and home essentials', 'washing machine-1.webp', '2025-03-26 10:25:55'),
(4, 'Books', 'Books across various genres', 'tv-01.webp', '2025-03-26 10:25:55');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(1, 'kiran', 'kiranpanta9846@gamil.com', 'hii', '2025-03-17 16:56:46'),
(2, 'kiran', 'kiranpanta@gmail.com', 'hiiii', '2025-03-20 02:41:13'),
(3, 'kiran panta', 'kiran90@gmail.com', 'This Is the Best Ecommerce Website from Where You Can Buy Products easily without any Problems Thanks for the developer for this Amazing Website ..Thanks To the Mero Shopping Teams..', '2025-04-17 01:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `expiration_date` datetime DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 11:30:52'),
(2, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:02:39'),
(3, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:21:38'),
(4, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:21:47'),
(5, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:23:46'),
(6, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:23:59'),
(7, 'kiran', 'kiran@gmail.com', 'hiiii', '2025-03-11 12:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('Pending','Shipped','Delivered') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(100) DEFAULT NULL,
  `transaction_uuid` varchar(255) NOT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `shipping_status` enum('pending','shipped','in_transit','delivered') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `payment_method`, `status`, `order_date`, `customer_name`, `transaction_uuid`, `tracking_number`, `shipping_status`) VALUES
(144, 23, 150500.99, 'EPAYTEST', 'Pending', '2025-04-17 02:50:22', NULL, 'txn_68006c6e75a80', NULL, 'pending'),
(145, 23, 40.00, 'EPAYTEST', '', '2025-04-17 02:51:23', NULL, 'txn_68006cab37bf4', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `transaction_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 'txn_67e0dac07fd1e', 2, 1, 399.00, 399.99),
(2, 'txn_67e0db2205cf3', 2, 1, 399.00, 399.99),
(3, 'txn_67e0dc3cb3927', 2, 1, 399.00, 399.99),
(4, 'txn_67e0dd7565087', 1, 1, 500.00, 500.99),
(5, 'txn_67e0de07afd5f', 4, 1, 500.00, 500.99),
(6, 'txn_67e14b834e1f7', 2, 1, 399.00, 399.99),
(7, 'txn_67e14bb2c628b', 1, 1, 500.00, 500.99),
(8, 'txn_67e14d9da47df', 2, 1, 399.00, 399.99),
(9, 'txn_67e14f4ae737f', 4, 1, 500.00, 500.99),
(10, 'txn_67e150b6b41df', 2, 1, 399.00, 399.99),
(11, 'txn_67e15197e13ca', 2, 1, 399.00, 399.99),
(12, 'txn_67e15235cbedd', 2, 1, 399.00, 399.99),
(13, 'txn_67e152e0e9da8', 2, 1, 399.00, 399.99),
(14, 'txn_67e153bcd66c0', 2, 1, 399.00, 399.99),
(15, 'txn_67e15452ed346', 2, 1, 399.00, 399.99),
(16, 'txn_67e178cfada87', 2, 1, 399.00, 399.99),
(17, 'txn_67e17a2b1ee98', 5, 1, 40.00, 40.00),
(18, 'txn_67e17b48e8a2a', 6, 1, 50.00, 50.00),
(19, 'txn_67e17cb008830', 3, 1, 299.00, 299.99),
(20, 'txn_67e17d3909287', 3, 1, 299.00, 299.99),
(21, 'txn_67e17dbdb8d43', 2, 1, 399.00, 399.99),
(22, 'txn_67e17e63578c97.98008793', 6, 1, 50.00, 50.00),
(23, 'txn_67e17f0fa162c2.95834767', 2, 1, 399.00, 399.99),
(24, 'txn_67e17f9222925', 2, 1, 399.00, 399.99),
(25, 'txn_67e180cdc38cb', 1, 1, 500.00, 500.99),
(26, 'txn_67e18149e3d40', 3, 1, 299.00, 299.99),
(27, 'txn_67e182b90f453', 1, 1, 500.00, 500.99),
(28, 'txn_67e18346e08c7', 3, 1, 299.00, 299.99),
(29, 'txn_67e184074e8d0', 2, 1, 399.00, 399.99),
(30, 'txn_67e18521533f1', 2, 1, 399.00, 399.99),
(31, 'txn_67e1863422046', 2, 1, 399.00, 399.99),
(32, 'txn_67e186c43bb66', 2, 1, 399.00, 399.99),
(33, 'txn_67e18b836fe13', 2, 1, 399.00, 399.99),
(34, 'txn_67e18ba29e2f1', 2, 1, 399.00, 399.99),
(35, 'txn_67e18bad64e33', 2, 1, 399.00, 399.99),
(36, 'txn_67e18be0e9d6f', 3, 1, 299.00, 299.99),
(37, 'txn_67e18cfeef2a3', 2, 1, 399.00, 399.99),
(38, 'txn_67e18ed86593a', 2, 1, 399.00, 399.99),
(39, 'txn_67e1905ec1882', 2, 1, 399.00, 399.99),
(40, 'txn_67e196265a2cf', 3, 1, 299.00, 299.99),
(41, 'txn_67e1968217d15', 3, 1, 299.00, 299.99),
(42, 'txn_67e196927dab0', 3, 1, 299.00, 299.99),
(43, 'txn_67e1987246f8e', 4, 1, 500.00, 500.99),
(44, 'txn_67e1fb5328d8d', 1, 1, 500.00, 500.99),
(45, 'txn_67e1fbf28af6a', 6, 1, 50.00, 50.00),
(46, 'txn_67e1fee724f10', 2, 1, 399.00, 399.99),
(47, 'txn_67e2004bcc172', 2, 1, 399.00, 399.99),
(48, 'txn_67e2011e31e50', 2, 2, 399.00, 799.98),
(49, 'txn_67e2016f970ca', 3, 1, 299.00, 299.99),
(50, 'txn_67e203ee3286e', 1, 1, 500.00, 500.99),
(51, 'txn_67e204b3519e3', 4, 1, 500.00, 500.99),
(52, 'txn_67e20672d3bf6', 3, 1, 299.00, 299.99),
(53, 'txn_67e267e186a54', 2, 1, 399.00, 399.99),
(54, 'txn_67e26b8ae16e1', 6, 1, 50.00, 50.00),
(55, 'txn_67e26c7fd2c16', 1, 1, 500.00, 500.99),
(56, 'txn_67e26eaf3038b', 2, 1, 399.00, 399.99),
(57, 'txn_67e26fab0260f', 5, 1, 40.00, 40.00),
(58, 'txn_67e27038e241e', 1, 1, 500.00, 500.99),
(59, 'txn_67e2707592404', 2, 1, 399.00, 399.99),
(60, 'txn_67e270ccea3e5', 2, 1, 399.00, 399.99),
(61, 'txn_67e271c773caf', 4, 1, 500.00, 500.99),
(62, 'txn_67e2727fd5816', 2, 2, 399.00, 799.98),
(63, 'txn_67e2768a98509', 5, 1, 40.00, 40.00),
(64, 'txn_67e277b7deb28', 5, 1, 40.00, 40.00),
(65, 'txn_67e2783376abc', 1, 1, 500.00, 500.99),
(66, 'txn_67e2c648974e7', 2, 1, 399.00, 399.99),
(67, 'txn_67e3635d953de', 3, 3, 299.00, 899.97),
(68, 'txn_67e3635d953de', 2, 2, 399.00, 799.98),
(69, 'txn_67e36457c81de', 5, 1, 40.00, 40.00),
(70, 'txn_67e36992b05c1', 5, 2, 40.00, 80.00),
(71, 'txn_67e36a25d38f1', 5, 1, 40.00, 40.00),
(72, 'txn_67e36b90c1c29', 2, 1, 399.00, 399.99),
(73, 'txn_67e36c63ca763', 2, 1, 399.00, 399.99),
(74, 'txn_67e36e68db8f2', 1, 1, 500.00, 500.99),
(75, 'txn_67e36f7c2de51', 1, 1, 500.00, 500.99),
(76, 'txn_67e3761fd5da2', 1, 1, 500.00, 500.99),
(77, 'txn_67e376ee9c9fb', 1, 1, 500.00, 500.99),
(78, 'txn_67e3785118a12', 1, 1, 500.00, 500.99),
(79, 'txn_67ef41fabfbfd', 8, 1, 350.00, 350.00),
(80, 'txn_67ef4aa90073c', 12, 1, -122.00, -122.00),
(81, 'txn_67ef4abe4d65e', 3, 1, 299.00, 299.99),
(82, 'txn_67ffa4888562b', 3, 2, 299.00, 599.98),
(83, 'txn_67ffa4888562b', 8, 1, 350.00, 350.00),
(84, 'txn_67ffa4a6c866c', 2, 2, 399.00, 799.98),
(85, 'txn_67ffa4e947624', 3, 1, 299.00, 299.99),
(86, 'txn_67ffa5a225137', 3, 1, 299.00, 299.99),
(87, 'txn_67ffa5c51605b', 8, 2, 350.00, 700.00),
(88, 'txn_67ffa62a4b9b1', 8, 1, 350.00, 350.00),
(89, 'txn_68006c6e75a80', 1, 1, 500.00, 500.99),
(90, 'txn_68006c6e75a80', 14, 1, 150000.00, 150000.00),
(91, 'txn_68006cab37bf4', 5, 1, 40.00, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `brand_id`, `stock`) VALUES
(1, 'Camera CTI', 'Description for Product 1', 500.99, 'camera-1.webp', 0, NULL, 9),
(2, 'camera CTZ', 'Description for Product 2', 399.99, 'camera-2.webp', 0, NULL, 0),
(3, 'camera CTM', 'Description for Product 3', 299.99, 'camera-3.webp', 0, NULL, 0),
(5, 'Mixer', NULL, 40.00, 'mixer-1.webp', 0, NULL, 0),
(6, 'Watch', NULL, 50.00, '2dcea7df1155925b9b20446a68c486f9.jpg', 0, NULL, 0),
(7, 'fridge', NULL, 10000.00, '22ec9525c47509e61f7abde5435daae8.jpg', 0, NULL, 0),
(8, 'Smartphone', 'Latest smartphone with advanced features', 350.00, 'smartphone-1.webp', 1, NULL, 0),
(9, 'Laptop', 'High-end gaming laptop', 999.99, 'laptop-1.webp', 1, NULL, 0),
(10, 'Fridge', 'Energy-efficient fridge', 500.00, 'fridge-1.webp', 1, NULL, 0),
(11, 'watch', NULL, 900.00, '2dcea7df1155925b9b20446a68c486f9.jpg', 0, NULL, 0),
(12, 'watch', NULL, 15000.00, 'mi-smart-band-5-1-nepal_2.jpg', 0, NULL, 0),
(14, 'GoPro', NULL, 150000.00, '30ef10d6b5bc1957e7f0953b38fda06f.jpg', 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_code` varchar(255) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'eSewa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `transaction_id`, `transaction_code`, `product_code`, `signature`, `email`, `status`, `payment_method`, `created_at`, `updated_at`, `order_id`) VALUES
(1, 16, 399.99, 'txn_67e18521533f1', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:15:29', '2025-03-24 16:15:54', 0),
(2, 16, 399.99, 'txn_67e1863422046', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:20:04', '2025-03-24 16:20:19', 0),
(3, 16, 399.99, 'txn_67e186c43bb66', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:22:28', '2025-03-24 16:22:44', 0),
(4, 16, 399.99, 'txn_67e18ba29e2f1', '', '', '', 'kiran00@gmail.com', 'pending', 'eSewa', '2025-03-24 16:43:14', NULL, 0),
(5, 16, 399.99, 'txn_67e18bad64e33', '', '', '', 'kiran00@gmail.com', 'pending', 'eSewa', '2025-03-24 16:43:25', NULL, 0),
(6, 16, 299.99, 'txn_67e18be0e9d6f', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:44:16', '2025-03-24 16:44:30', 0),
(7, 16, 399.99, 'txn_67e18cfeef2a3', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:49:02', '2025-03-24 16:49:18', 0),
(8, 16, 399.99, 'txn_67e18ed86593a', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 16:56:56', '2025-03-24 16:57:28', 0),
(9, 16, 399.99, 'txn_67e1905ec1882', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 17:03:26', '2025-03-24 17:03:47', 0),
(10, 16, 299.99, 'txn_67e196265a2cf', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 17:28:06', '2025-03-24 17:28:29', 0),
(11, 11, 299.99, 'txn_67e1968217d15', '', '', '', 'kiran@gmail.com', 'pending', 'eSewa', '2025-03-24 17:29:38', NULL, 0),
(12, 11, 299.99, 'txn_67e196927dab0', '', '', '', 'kiran@gmail.com', '', 'eSewa', '2025-03-24 17:29:54', '2025-03-24 17:30:09', 0),
(13, 16, 500.99, 'txn_67e1987246f8e', '', '', '', 'kiran00@gmail.com', '', 'eSewa', '2025-03-24 17:37:54', '2025-03-24 17:38:16', 0),
(14, 17, 500.99, 'txn_67e1fb5328d8d', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 00:39:47', '2025-03-25 00:40:06', 0),
(15, 17, 50.00, 'txn_67e1fbf28af6a', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 00:42:26', '2025-03-25 00:42:59', 0),
(16, 17, 399.99, 'txn_67e1fee724f10', '', '', '', 'hacker@01gmail.com', 'pending', 'eSewa', '2025-03-25 00:55:03', NULL, 0),
(17, 17, 399.99, 'txn_67e2004bcc172', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 01:00:59', '2025-03-25 01:02:18', 0),
(18, 17, 799.98, 'txn_67e2011e31e50', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 01:04:30', '2025-03-25 01:04:50', 0),
(19, 17, 500.99, 'txn_67e203ee3286e', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 01:16:30', '2025-03-25 01:17:03', 0),
(20, 17, 500.99, 'txn_67e204b3519e3', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 01:19:47', '2025-03-25 01:20:06', 0),
(21, 17, 299.99, 'txn_67e20672d3bf6', '', '', '', 'hacker@01gmail.com', '', 'eSewa', '2025-03-25 01:27:14', '2025-03-25 01:27:32', 0),
(22, 18, 399.99, 'txn_67e267e186a54', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 08:22:57', '2025-03-25 08:23:28', 0),
(23, 18, 50.00, 'txn_67e26b8ae16e1', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 08:38:34', '2025-03-25 08:39:13', 0),
(24, 18, 500.99, 'txn_67e26c7fd2c16', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 08:42:39', '2025-03-25 08:43:09', 0),
(25, 18, 399.99, 'txn_67e26eaf3038b', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 08:51:59', '2025-03-25 08:52:36', 0),
(26, 18, 40.00, 'txn_67e26fab0260f', '', '', '', 'kiran01@gmail.com', 'pending', 'eSewa', '2025-03-25 08:56:11', NULL, 0),
(27, 18, 500.99, 'txn_67e27038e241e', '', '', '', 'kiran01@gmail.com', 'pending', 'eSewa', '2025-03-25 08:58:32', NULL, 0),
(28, 18, 399.99, 'txn_67e2707592404', '', '', '', 'kiran01@gmail.com', 'pending', 'eSewa', '2025-03-25 08:59:33', NULL, 0),
(29, 18, 399.99, 'txn_67e270ccea3e5', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:01:00', '2025-03-25 09:01:20', 0),
(30, 18, 500.99, 'txn_67e271c773caf', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:05:11', '2025-03-25 09:05:46', 0),
(31, 18, 799.98, 'txn_67e2727fd5816', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:08:15', '2025-03-25 09:08:49', 0),
(32, 18, 40.00, 'txn_67e2768a98509', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:25:30', '2025-03-25 09:26:07', 0),
(33, 18, 40.00, 'txn_67e277b7deb28', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:30:31', '2025-03-25 09:30:55', 0),
(34, 18, 500.99, 'txn_67e2783376abc', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 09:32:35', '2025-03-25 09:33:09', 0),
(35, 18, 399.99, 'txn_67e2c648974e7', '', '', '', 'kiran01@gmail.com', '', 'eSewa', '2025-03-25 15:05:44', '2025-03-25 15:06:25', 0),
(36, 19, 1699.95, 'txn_67e3635d953de', '', '', '', 'project98@gmail.com', 'pending', 'eSewa', '2025-03-26 02:15:57', NULL, 0),
(37, 19, 40.00, 'txn_67e36457c81de', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 02:20:07', '2025-03-26 02:21:03', 0),
(38, 19, 80.00, 'txn_67e36992b05c1', '', '', '', 'project98@gmail.com', 'pending', 'eSewa', '2025-03-26 02:42:26', NULL, 0),
(39, 19, 40.00, 'txn_67e36a25d38f1', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 02:44:53', '2025-03-26 02:45:28', 0),
(40, 19, 399.99, 'txn_67e36b90c1c29', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 02:50:56', '2025-03-26 02:52:59', 0),
(41, 19, 399.99, 'txn_67e36c63ca763', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 02:54:27', '2025-03-26 02:56:19', 0),
(42, 19, 500.99, 'txn_67e36e68db8f2', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 03:03:04', '2025-03-26 03:04:47', 0),
(43, 19, 500.99, 'txn_67e36f7c2de51', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 03:07:40', '2025-03-26 03:08:52', 0),
(44, 19, 500.99, 'txn_67e3761fd5da2', '', '', '', 'project98@gmail.com', 'pending', 'eSewa', '2025-03-26 03:35:59', NULL, 0),
(45, 19, 500.99, 'txn_67e376ee9c9fb', '', '', '', 'project98@gmail.com', 'pending', 'eSewa', '2025-03-26 03:39:26', NULL, 0),
(46, 19, 500.99, 'txn_67e3785118a12', '', '', '', 'project98@gmail.com', '', 'eSewa', '2025-03-26 03:45:21', '2025-03-26 03:46:19', 0),
(47, 20, 350.00, 'txn_67ef41fabfbfd', '', '', '', 'kiranpanta98@gmail.com', '', 'eSewa', '2025-04-04 02:20:42', '2025-04-04 02:21:06', 0),
(48, 21, -122.00, 'txn_67ef4aa90073c', '', '', '', 'kiranpanta9846@gmail.com', 'pending', 'eSewa', '2025-04-04 02:57:45', NULL, 0),
(49, 21, 299.99, 'txn_67ef4abe4d65e', '', '', '', 'kiranpanta9846@gmail.com', '', 'eSewa', '2025-04-04 02:58:06', '2025-04-04 02:59:07', 0),
(50, 22, 949.98, 'txn_67ffa4888562b', '', '', '', 'kiran90@gmail.com', 'pending', 'eSewa', '2025-04-16 12:37:28', NULL, 0),
(51, 22, 799.98, 'txn_67ffa4a6c866c', '', '', '', 'kiran90@gmail.com', 'pending', 'eSewa', '2025-04-16 12:37:58', NULL, 0),
(52, 22, 299.99, 'txn_67ffa4e947624', '', '', '', 'kiran90@gmail.com', 'pending', 'eSewa', '2025-04-16 12:39:05', NULL, 0),
(53, 22, 299.99, 'txn_67ffa5a225137', '', '', '', 'kiran90@gmail.com', 'pending', 'eSewa', '2025-04-16 12:42:10', NULL, 0),
(54, 22, 700.00, 'txn_67ffa5c51605b', '', '', '', 'kiran90@gmail.com', '', 'eSewa', '2025-04-16 12:42:45', '2025-04-16 12:43:15', 0),
(55, 22, 350.00, 'txn_67ffa62a4b9b1', '', '', '', 'kiran90@gmail.com', 'pending', 'eSewa', '2025-04-16 12:44:26', NULL, 0),
(56, 23, 150500.99, 'txn_68006c6e75a80', '', '', '', 'kiranpanta90@gmail.com', 'pending', 'eSewa', '2025-04-17 02:50:22', NULL, 0),
(57, 23, 40.00, 'txn_68006cab37bf4', '', '', '', 'kiranpanta90@gmail.com', '', 'eSewa', '2025-04-17 02:51:23', '2025-04-17 02:52:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(100) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `name`, `reset_token`, `reset_expiry`) VALUES
(23, '', 'kiranpanta90@gmail.com', '$2y$10$kcdnkgUf6kRXKHE.qEpBuuw9/p.A3oc/2hthevFgKNIsWIWjwXSXK', '2025-04-17 02:49:17', 'kiranpanta', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `products` ADD FULLTEXT KEY `name` (`name`,`description`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product` (`product_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
