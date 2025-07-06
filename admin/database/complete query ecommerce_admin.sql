-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2025 at 05:13 PM
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
-- Database: `ecommerce_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password`, `name`, `created_at`) VALUES
(1, 'admin@ecommerce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '2025-07-06 06:46:59');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Flour', 'Flour products', 'active', '2025-07-06 06:47:22'),
(2, 'Rice', 'Rice products', 'active', '2025-07-06 06:47:22'),
(3, 'General Grocery', 'General Grocery products', 'active', '2025-07-06 06:47:22'),
(4, 'Oil', 'Oil products', 'active', '2025-07-06 06:47:22'),
(5, 'Tea', 'Tea products', 'active', '2025-07-06 06:47:22'),
(6, 'Meat', 'Fresh Meat', 'active', '2025-07-06 14:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `name`, `type`, `value`, `product_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(45, 'Sher Atta Desi Style 20 lb Discount', 'fixed', 4.00, 1, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(46, 'Ocean Pearl Rice 10 lb Discount', 'fixed', 3.00, 2, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(47, 'Dewan Basmati Rice 10 lb Discount', 'fixed', 3.00, 3, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(48, 'Minar Classic Rice 10 lb Discount', 'fixed', 3.00, 4, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(49, 'Guard Basmati Rice 10 lb Discount', 'fixed', 3.00, 5, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(50, 'Handi Extreme Rice Discount', 'fixed', 2.00, 6, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(51, 'Shan Paste 700 gm Discount', 'fixed', 0.50, 7, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(52, 'Handi Paste 750 gm Discount', 'fixed', 2.00, 8, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(53, 'Handi Fried Onion 400 gm Discount', 'fixed', 1.00, 9, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(54, 'PK Sunflower Oil 3 Litre Discount', 'fixed', 2.00, 10, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(55, 'Patanjali Mustard Oil 1 Litre Discount', 'fixed', 3.00, 11, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(56, 'Tez Mustard Oil 4.75 Litre Discount', 'fixed', 5.00, 12, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(57, 'Patanjali Mustard Oil 5 Litre Discount', 'fixed', 5.00, 13, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(58, 'Nanak Desi Ghee 1.6 kg Discount', 'fixed', 4.00, 14, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(59, 'Sher Besan 4 lb Discount', 'fixed', 2.00, 15, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(60, 'Nestle Everyday Powder 850 gm Discount', 'fixed', 3.00, 16, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(61, 'Tapal Danedar Tea Pouch 900 gm Discount', 'fixed', 4.00, 17, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(62, 'Tetley Tea 300 gm Discount', 'fixed', 3.00, 18, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(63, 'Tapal Danedar Tea 220 Bags Discount', 'fixed', 3.00, 19, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(64, 'Tapal Green Tea 30 Bags Discount', 'fixed', 1.50, 20, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(65, 'Red Label Tea 900 gm Discount', 'fixed', 3.00, 21, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(66, 'Nestle Dairy Cream 200 ml Discount', 'fixed', 1.00, 22, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(67, 'Lipton Danedar Tea Jar 475 gm Discount', 'fixed', 2.00, 23, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(68, 'Barbican Drink 6 Pack Discount', 'fixed', 2.50, 24, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(69, 'Shezan Juice 6 x 250 ml Discount', 'fixed', 3.00, 25, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(70, 'Shezan Juice 36 x 250 ml Discount', 'fixed', 8.00, 26, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(71, 'National Pickle 1 kg Discount', 'fixed', 1.50, 27, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(72, 'Shezan Cordia Mix 1 kg Discount', 'fixed', 2.00, 28, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(73, 'Shezan Lime Chilli Pickle 1 kg Discount', 'fixed', 2.00, 29, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(74, 'GV Khudri Dates 1 kg Discount', 'fixed', 2.00, 30, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(75, 'GV Medjoul Dates 1 kg Discount', 'fixed', 3.00, 31, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(76, 'Dawn Paratha Family Pack 30 Pcs Discount', 'fixed', 2.00, 32, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(77, 'Brar’s Desi Ghee 1.6 kg Discount', 'fixed', 4.00, 33, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(78, 'Shezan Sarson Ka Saag 840 gm Discount', 'fixed', 2.00, 34, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(79, 'Mitchell’s Sarson Ka Saag 800 gm Discount', 'fixed', 1.00, 35, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(80, 'Fresh Chicken Leg Clean Discount', 'fixed', 1.00, 36, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(81, 'Fresh Chicken Thigh Clean Discount', 'fixed', 1.00, 37, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(82, 'Fresh Goat Leg Discount', 'fixed', 3.00, 38, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(83, 'Fresh Goat Shoulder Discount', 'fixed', 3.00, 39, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(84, 'Fresh Beef Eye of Round Discount', 'fixed', 2.00, 40, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(85, 'Fresh Beef Veal with Bone Discount', 'fixed', 1.00, 41, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(86, 'Marhaba Ispaghol Husk 95 gm Discount', 'fixed', 4.00, 42, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(87, 'Potato White 10 lb Discount', 'fixed', 1.00, 43, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24'),
(88, 'Onion Yellow 10 lb Discount', 'fixed', 1.00, 44, '2025-07-06', '2025-08-05', 'active', '2025-07-06 06:50:24');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `image`, `status`, `created_at`, `discount_id`) VALUES
(1, 'Sher Atta Desi Style 20 lb', 'Premium desi-style wheat flour for soft rotis and parathas.', 17.99, 1, 'Sher Atta Desi Style 20 lb.jpg', 'active', '2025-07-06 06:50:11', 45),
(2, 'Ocean Pearl Rice 10 lb', 'High-quality long grain rice, perfect for daily cooking.', 13.99, 2, 'Ocean Pearl Rice 10 lb.jpg', 'active', '2025-07-06 06:50:11', 46),
(3, 'Dewan Basmati Rice 10 lb', 'Aromatic basmati rice ideal for biryani and pulao.', 15.99, 2, 'Dewan Basmati Rice 10 lb.jpg', 'active', '2025-07-06 06:50:11', 47),
(4, 'Minar Classic Rice 10 lb', 'Classic rice with excellent texture and aroma.', 14.99, 2, 'Minar Classic Rice 10 lb.jpg', 'active', '2025-07-06 06:50:11', 48),
(5, 'Guard Basmati Rice 10 lb', 'Authentic basmati rice with long grains.', 14.99, 2, 'Guard Basmati Rice 10 lb.jpg', 'active', '2025-07-06 06:50:11', 49),
(6, 'Handi Extreme Rice', 'Premium quality rice with enhanced flavor.', 11.99, 2, 'Handi Extreme Rice.jpg', 'active', '2025-07-06 06:50:11', 50),
(7, 'Shan Paste 700 gm', 'Ready-to-use cooking pastes for desi dishes.', 5.49, 3, 'Shan Paste 700 gm.jpg', 'active', '2025-07-06 06:50:11', 51),
(8, 'Handi Paste 750 gm', 'Cooking pastes packed with traditional spices.', 4.99, 3, 'Handi Paste 750 gm.jpg', 'active', '2025-07-06 06:50:11', 52),
(9, 'Handi Fried Onion 400 gm', 'Crispy fried onions for garnishing biryani and curries.', 2.99, 3, 'Handi Fried Onion 400 gm.jpg', 'active', '2025-07-06 06:50:11', 53),
(10, 'PK Sunflower Oil 3 Litre', 'Healthy sunflower oil for everyday use.', 8.99, 4, 'PK Sunflower Oil 3 Litre.jpg', 'active', '2025-07-06 06:50:11', 54),
(11, 'Patanjali Mustard Oil 1 Litre', 'Strong, aromatic mustard oil.', 6.99, 4, 'Patanjali Mustard Oil 1 Litre.jpg', 'active', '2025-07-06 06:50:11', 55),
(12, 'Tez Mustard Oil 4.75 Litre', 'Large pack of mustard oil for bulk use.', 27.99, 4, 'Tez Mustard Oil 4.75 Litre.jpg', 'active', '2025-07-06 06:50:11', 56),
(13, 'Patanjali Mustard Oil 5 Litre', 'Natural mustard oil in a 5-litre pack.', 24.99, 4, 'Patanjali Mustard Oil 5 Litre.jpg', 'active', '2025-07-06 06:50:11', 57),
(14, 'Nanak Desi Ghee 1.6 kg', 'Traditional clarified butter for cooking.', 28.99, 3, 'Nanak Desi Ghee 1.6 kg.jpg', 'active', '2025-07-06 06:50:11', 58),
(15, 'Sher Besan 4 lb', 'Gram flour used in a variety of desi recipes.', 6.99, 3, 'Sher Besan 4 lb.jpg', 'active', '2025-07-06 06:50:11', 59),
(16, 'Nestle Everyday Powder 850 gm', 'Creamer powder for tea and coffee.', 16.99, 3, 'Nestle Everyday Powder 850 gm.jpg', 'active', '2025-07-06 06:50:11', 60),
(17, 'Tapal Danedar Tea Pouch 900 gm', 'Strong, flavorful tea blend.', 12.99, 5, 'Tapal Danedar Tea Pouch 900 gm.jpg', 'active', '2025-07-06 06:50:11', 61),
(18, 'Tetley Tea 300 gm', 'Popular black tea for daily use.', 16.99, 5, 'Tetley Tea 300 gm.jpg', 'active', '2025-07-06 06:50:11', 62),
(19, 'Tapal Danedar Tea 220 Bags', 'Convenient tea bags with rich taste.', 10.99, 5, 'Tapal Danedar Tea 220 Bags.jpg', 'active', '2025-07-06 06:50:11', 63),
(20, 'Tapal Green Tea 30 Bags', 'Green tea bags for a healthy lifestyle.', 4.99, 5, 'Tapal Green Tea 30 Bags.jpg', 'active', '2025-07-06 06:50:11', 64),
(21, 'Red Label Tea 900 gm', 'Full-bodied Indian tea.', 12.99, 5, 'Red Label Tea 900 gm.jpg', 'active', '2025-07-06 06:50:11', 65),
(22, 'Nestle Dairy Cream 200 ml', 'Rich dairy cream for desserts and tea.', 4.99, 3, 'Nestle Dairy Cream 200 ml.jpg', 'active', '2025-07-06 06:50:11', 66),
(23, 'Lipton Danedar Tea Jar 475 gm', 'Danedar blend for strong flavor.', 8.99, 5, 'Lipton Danedar Tea Jar 475 gm.jpg', 'active', '2025-07-06 06:50:11', 67),
(24, 'Barbican Drink 6 Pack', 'Non-alcoholic malt drink.', 10.99, 3, 'Barbican Drink 6 Pack.jpg', 'active', '2025-07-06 06:50:11', 68),
(25, 'Shezan Juice 6 x 250 ml', 'Assorted fruit juices in glass bottles.', 12.99, 3, 'Shezan Juice 6 x 250 ml.jpg', 'active', '2025-07-06 06:50:11', 69),
(26, 'Shezan Juice 36 x 250 ml', 'Bulk fruit juice pack.', 29.99, 3, 'Shezan Juice 36 x 250 ml.jpg', 'active', '2025-07-06 06:50:11', 70),
(27, 'National Pickle 1 kg', 'Mixed vegetable pickle with spices.', 6.99, 3, 'National Pickle 1 kg.jpg', 'active', '2025-07-06 06:50:11', 71),
(28, 'Shezan Cordia Mix 1 kg', 'Traditional tangy Cordia fruit pickle.', 4.99, 3, 'Shezan Cordia Mix 1 kg.jpg', 'active', '2025-07-06 06:50:11', 72),
(29, 'Shezan Lime Chilli Pickle 1 kg', 'Spicy lime and chilli pickle.', 4.99, 3, 'Shezan Lime Chilli Pickle 1 kg.jpg', 'active', '2025-07-06 06:50:11', 73),
(30, 'GV Khudri Dates 1 kg', 'Sweet, chewy Khudri dates.', 9.99, 3, 'GV Khudri Dates 1 kg.jpg', 'active', '2025-07-06 06:50:11', 74),
(31, 'GV Medjoul Dates 1 kg', 'Premium Medjoul dates.', 17.99, 3, 'GV Medjoul Dates 1 kg.jpg', 'active', '2025-07-06 06:50:11', 75),
(32, 'Dawn Paratha Family Pack 30 Pcs', 'Ready-to-cook layered parathas.', 9.99, 3, 'Dawn Paratha Family Pack 30 Pcs.jpg', 'active', '2025-07-06 06:50:11', 76),
(33, 'Brar’s Desi Ghee 1.6 kg', 'High quality desi ghee.', 29.99, 3, 'Brar’s Desi Ghee 1.6 kg.jpg', 'active', '2025-07-06 06:50:11', 77),
(34, 'Shezan Sarson Ka Saag 840 gm', 'Ready-to-eat traditional mustard greens.', 4.99, 3, 'Shezan Sarson Ka Saag 840 gm.jpg', 'active', '2025-07-06 06:50:11', 78),
(35, 'Mitchell’s Sarson Ka Saag 800 gm', 'Authentic saag with home-style flavor.', 4.99, 3, 'Mitchell’s Sarson Ka Saag 800 gm.jpg', 'active', '2025-07-06 06:50:11', 79),
(36, 'Fresh Chicken Leg Clean', 'Clean chicken legs for cooking.', 3.99, 6, 'Fresh Chicken Leg Clean.jpg', 'active', '2025-07-06 06:50:11', 80),
(37, 'Fresh Chicken Thigh Clean', 'Cleaned chicken thighs.', 5.99, 6, 'Fresh Chicken Thigh Clean.jpg', 'active', '2025-07-06 06:50:11', 81),
(38, 'Fresh Goat Leg', 'Halal goat leg, fresh cut.', 13.99, 6, 'Fresh Goat Leg.jpg', 'active', '2025-07-06 06:50:11', 82),
(39, 'Fresh Goat Shoulder', 'Halal goat shoulder meat.', 11.99, 6, 'Fresh Goat Shoulder.jpg', 'active', '2025-07-06 06:50:11', 83),
(40, 'Fresh Beef Eye of Round', 'Lean cut of beef.', 9.99, 6, 'Fresh Beef Eye of Round.jpg', 'active', '2025-07-06 06:50:11', 84),
(41, 'Fresh Beef Veal with Bone', 'Veal with bone, perfect for stews.', 7.99, 6, 'Fresh Beef Veal with Bone.jpg', 'active', '2025-07-06 06:50:11', 85),
(42, 'Marhaba Ispaghol Husk 95 gm', 'Fiber-rich psyllium husk.', 7.99, 3, 'Marhaba Ispaghol Husk 95 gm.jpg', 'active', '2025-07-06 06:50:11', 86),
(43, 'Potato White 10 lb', 'White potatoes for everyday cooking.', 4.99, 3, 'Potato White 10 lb.jpg', 'active', '2025-07-06 06:50:11', 87),
(44, 'Onion Yellow 10 lb', 'Yellow onions for all kinds of dishes.', 4.99, 3, 'Onion Yellow 10 lb.jpg', 'active', '2025-07-06 06:50:11', 88);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'E-commerce Admin', '2025-07-06 06:46:59'),
(2, 'site_email', 'admin@ecommerce.com', '2025-07-06 06:46:59'),
(3, 'timezone', 'UTC', '2025-07-06 06:46:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `status`, `created_at`) VALUES
(1, 'John Doe', 'john@example.com', '+1234567890', '123 Main St, City, State', 'active', '2025-07-06 06:46:59'),
(2, 'Jane Smith', 'jane@example.com', '+0987654321', '456 Oak Ave, City, State', 'active', '2025-07-06 06:46:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

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
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
