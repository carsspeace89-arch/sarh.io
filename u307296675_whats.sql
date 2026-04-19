-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- مضيف: 127.0.0.1:3306
-- وقت الجيل: 01 أبريل 2026 الساعة 07:31
-- إصدار الخادم: 11.8.6-MariaDB-log
-- نسخة PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- قاعدة بيانات: `u307296675_whats`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$12$TRPJG/pqpqPRxUSx4w1XnePhqFHYtBkHpBhJXvIY5.ckGJLP7DI7i', 'مدير النظام', '2026-04-01 10:30:18', '2026-03-15 17:52:11');

-- --------------------------------------------------------

--
-- بنية الجدول `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `type` enum('in','out','overtime') NOT NULL,
  `timestamp` datetime NOT NULL,
  `attendance_date` date NOT NULL,
  `late_minutes` int(11) DEFAULT 0,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `location_accuracy` decimal(5,2) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `attendances`
--

INSERT INTO `attendances` (`id`, `employee_id`, `type`, `timestamp`, `attendance_date`, `late_minutes`, `latitude`, `longitude`, `location_accuracy`, `ip_address`, `user_agent`, `notes`, `created_at`) VALUES
(20, 52, 'in', '2026-03-17 21:58:47', '2026-03-17', 0, 24.57260380, 46.60303050, 42.51, '37.217.240.218', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-17 18:58:47'),
(21, 55, 'in', '2026-03-17 22:54:32', '2026-03-17', 55, 24.57234070, 46.60265700, 13.34, '37.217.240.218', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-17 19:54:32'),
(22, 67, 'in', '2026-03-17 22:59:47', '2026-03-17', 60, 24.57233650, 46.60265160, 14.27, '2a02:9b0:26:1087:7771:c5b7:995e:9469', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-17 19:59:47'),
(23, 26, 'in', '2026-03-17 23:01:45', '2026-03-17', 62, 24.57234553, 46.60256893, 17.90, '2a02:9b0:26:1087:c30:41c0:c648:ee28', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Mobile/15E148 Safari/604.1', NULL, '2026-03-17 20:01:45'),
(24, 1, 'in', '2026-03-17 23:03:31', '2026-03-17', 64, 24.57236390, 46.60266770, 14.98, '2a02:9b0:26:1087:31bb:3ac9:973d:5b7d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-17 20:03:31'),
(25, 7, 'in', '2026-03-17 23:20:57', '2026-03-17', 81, 24.57246300, 46.60274620, 15.99, '2001:16a4:75:c642:189c:67d3:bdb8:df00', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-17 20:20:57'),
(26, 8, 'in', '2026-03-17 23:26:23', '2026-03-17', 86, 24.57236262, 46.60276708, 16.99, '37.217.240.218', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-17 20:26:23'),
(27, 46, 'in', '2026-03-18 12:08:36', '2026-03-18', 9, 24.56964590, 46.61425830, 3.79, '2001:16a2:c054:2797:2183:8106:994:cf74', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 09:08:36'),
(28, 55, 'in', '2026-03-18 12:33:02', '2026-03-18', 33, 24.57237900, 46.60269830, 21.09, '2a02:9b0:3c:931b:406b:4b27:d05e:4df6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 09:33:02'),
(29, 46, 'out', '2026-03-18 20:08:33', '2026-03-18', 0, 24.56962600, 46.61427090, 6.80, '2001:16a2:c054:2797:59c9:fe2:3c4a:ea5b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 17:08:33'),
(30, 46, '', '2026-03-18 20:08:39', '2026-03-18', 0, 24.56964960, 46.61428910, 10.17, '2001:16a2:c054:2797:59c9:fe2:3c4a:ea5b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 17:08:39'),
(31, 52, 'in', '2026-03-18 21:07:32', '2026-03-18', 0, 24.57212030, 46.60266880, 39.10, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 18:07:32'),
(32, 1, 'in', '2026-03-18 21:48:06', '2026-03-18', 0, 24.57238780, 46.60268620, 9.76, '2a02:9b0:3c:931b:cbc7:e5a2:45f3:e391', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 18:48:06'),
(33, 26, 'in', '2026-03-18 21:48:25', '2026-03-18', 0, 24.57237061, 46.60252539, 14.92, '109.82.228.137', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Mobile/15E148 Safari/604.1', NULL, '2026-03-18 18:48:25'),
(34, 67, 'in', '2026-03-18 21:49:30', '2026-03-18', 0, 24.57233930, 46.60266940, 23.61, '2a02:9b0:3c:931b:e666:925a:e25e:9402', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-18 18:49:30'),
(35, 1, 'in', '2026-03-23 16:01:01', '2026-03-23', 1, 24.57237400, 46.60267180, 12.45, '2a02:9b0:3c:931b:8d38:66df:f202:33de', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:01:01'),
(36, 3, 'in', '2026-03-23 16:01:14', '2026-03-23', 1, 24.57233090, 46.60262810, 20.00, '2a09:bac5:3214:1eb::31:158', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:01:14'),
(37, 53, 'in', '2026-03-23 16:04:48', '2026-03-23', 5, 24.57235150, 46.60266680, 12.64, '2a02:9b0:3c:931b:e1d7:8976:591f:d473', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:04:48'),
(38, 31, 'in', '2026-03-23 16:09:13', '2026-03-23', 9, 24.57245400, 46.60311710, 16.74, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:09:13'),
(39, 55, 'in', '2026-03-23 16:28:21', '2026-03-23', 28, 24.57239450, 46.60269080, 14.90, '2a02:9b0:3c:931b:7401:4488:b8c6:2d06', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:28:21'),
(40, 40, 'in', '2026-03-23 16:33:07', '2026-03-23', 33, 24.57247260, 46.60313710, 15.58, '2a09:bac6:d84d:2c5a::46b:3f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-23 13:33:07'),
(43, 30, 'in', '2026-03-24 07:33:32', '2026-03-24', 684, 24.56629475, 46.62160187, 23.81, '2001:16a2:c214:49f1:a160:2cca:7a63:d0c0', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-24 04:33:32'),
(44, 31, 'in', '2026-03-24 07:36:04', '2026-03-24', 686, 24.57262330, 46.60294500, 5.60, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 04:36:04'),
(45, 35, 'in', '2026-03-24 07:40:00', '2026-03-24', 690, 24.56622360, 46.62176290, 12.86, '2a09:bac6:d848:2c5a::46b:b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 04:40:00'),
(46, 52, 'in', '2026-03-24 07:44:24', '2026-03-24', 0, 24.57245720, 46.60315880, 69.09, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 04:44:24'),
(47, 41, 'in', '2026-03-24 08:01:31', '2026-03-24', 0, 24.56949500, 46.61433670, 14.46, '2a02:9b0:44:2acf:6e97:4fa6:9a5d:bf9', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.51.0-gn', NULL, '2026-03-24 05:01:31'),
(48, 46, 'in', '2026-03-24 08:04:15', '2026-03-24', 0, 24.56952520, 46.61468030, 9.47, '2001:16a2:c218:e33f:746b:a946:c7cd:8dad', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:04:15'),
(49, 48, 'in', '2026-03-24 08:06:55', '2026-03-24', 0, 24.56958340, 46.61437390, 100.00, '2a02:9b0:44:2acf:2d1c:cc1:a5a8:1678', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:06:55'),
(50, 26, 'in', '2026-03-24 08:08:22', '2026-03-24', 0, 24.57240480, 46.60266649, 16.34, '77.232.122.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-24 05:08:22'),
(51, 40, 'in', '2026-03-24 08:09:36', '2026-03-24', 0, 24.57246110, 46.60315290, 14.53, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:09:36'),
(52, 3, 'in', '2026-03-24 08:10:25', '2026-03-24', 0, 24.57235330, 46.60267430, 15.55, '2a09:bac1:27c0:cc0::3b7:47', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:10:25'),
(53, 1, 'in', '2026-03-24 08:10:26', '2026-03-24', 0, 24.57238460, 46.60268420, 14.97, '2a02:9b0:3c:931b:4f2a:e4c7:b312:54e6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:10:26'),
(54, 68, 'in', '2026-03-24 08:11:53', '2026-03-24', 2, 24.57241280, 46.60271160, 33.58, '2a02:9b0:3c:931b:3311:ea36:4bff:7201', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:11:53'),
(55, 28, 'in', '2026-03-24 08:13:01', '2026-03-24', 3, 24.57242090, 46.60271870, 18.57, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:13:01'),
(56, 70, 'in', '2026-03-24 08:15:00', '2026-03-24', 5, 24.57241990, 46.60274100, 16.89, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:15:00'),
(57, 34, 'in', '2026-03-24 08:16:06', '2026-03-24', 6, 24.56627240, 46.62176390, 12.91, '2a02:9b0:40:e04d:80b6:99fa:6298:2d85', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:16:06'),
(58, 32, 'in', '2026-03-24 08:18:03', '2026-03-24', 8, 24.56625770, 46.62176460, 12.61, '2a02:9b0:40:e04d:f199:cce8:9b51:25d9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:18:03'),
(59, 55, 'in', '2026-03-24 08:19:16', '2026-03-24', 9, 24.57235490, 46.60266040, 12.65, '2a02:9b0:3c:931b:559b:fb3d:1de8:5f2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:19:16'),
(60, 53, 'in', '2026-03-24 08:20:28', '2026-03-24', 10, 24.57235560, 46.60266190, 16.09, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:20:28'),
(61, 36, 'in', '2026-03-24 08:20:49', '2026-03-24', 11, 24.57657658, 46.60005360, 999.99, '2a09:bac6:d84b:1eb::31:158', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.119 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-24 05:20:49'),
(62, 29, 'in', '2026-03-24 08:23:35', '2026-03-24', 14, 24.56617450, 46.62176130, 20.21, '2a02:9b0:40:e04d:31e0:e6b5:b842:5b0a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:23:35'),
(63, 43, 'in', '2026-03-24 08:24:06', '2026-03-24', 14, 24.56959000, 46.61422960, 3.91, '2a02:9b0:44:2acf:d776:e1d5:aa86:2064', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:24:06'),
(64, 38, 'in', '2026-03-24 08:24:50', '2026-03-24', 15, 24.56620540, 46.62177430, 11.64, '2a02:9b0:40:e04d:e81a:e9cd:a73d:eebf', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:24:50'),
(65, 67, 'in', '2026-03-24 08:45:33', '2026-03-24', 36, 24.57236230, 46.60268590, 14.37, '2a02:9b0:3c:931b:212b:5446:9e83:3635', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:45:33'),
(66, 71, 'in', '2026-03-24 08:54:08', '2026-03-24', 44, 24.56622160, 46.62179880, 14.86, '109.82.10.70', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 05:54:08'),
(67, 71, 'out', '2026-03-24 12:04:25', '2026-03-24', 0, 24.56614030, 46.62172540, 18.83, '23.251.48.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 09:04:25'),
(68, 71, '', '2026-03-24 14:04:58', '2026-03-24', 0, 24.56617190, 46.62155110, 17.83, '2a02:9b0:40:e04d:33b6:9016:5919:ec23', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 11:04:58'),
(69, 71, '', '2026-03-24 14:05:01', '2026-03-24', 0, 24.56616970, 46.62155940, 13.45, '2a02:9b0:40:e04d:33b6:9016:5919:ec23', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 11:05:01'),
(70, 33, 'in', '2026-03-24 15:36:10', '2026-03-24', 96, 24.56624474, 46.62166874, 16.10, '2a09:bac5:3215:254b::3b7:13', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', NULL, '2026-03-24 12:36:10'),
(71, 34, 'out', '2026-03-24 20:21:42', '2026-03-24', 0, 24.56622800, 46.62176340, 13.98, '2a02:9b0:40:e04d:47df:4a47:51fe:6cd8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-24 17:21:42'),
(72, 54, 'in', '2026-03-25 07:11:32', '2026-03-25', 0, 24.56629015, 46.62165813, 13.80, '93.168.176.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.151 Mobile/15E148 Safari/604.1', NULL, '2026-03-25 04:11:32'),
(73, 34, 'in', '2026-03-25 07:30:47', '2026-03-25', 0, 24.56621050, 46.62177350, 16.36, '109.83.152.191', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:30:47'),
(74, 30, 'in', '2026-03-25 07:41:42', '2026-03-25', 0, 24.56613131, 46.62187863, 15.72, '2001:16a2:c032:f69a:e0e6:77fb:be06:ce25', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-25 04:41:42'),
(75, 38, 'in', '2026-03-25 07:51:20', '2026-03-25', 0, 24.56621290, 46.62176230, 12.81, '2a02:9b0:f:379:4d9e:e7b9:130b:9dde', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:51:20'),
(76, 35, 'in', '2026-03-25 07:51:37', '2026-03-25', 0, 24.56619290, 46.62177840, 13.76, '2a02:9b0:f:379:fca2:feaf:57a5:656e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:51:37'),
(77, 31, 'in', '2026-03-25 07:54:48', '2026-03-25', 0, 24.57261660, 46.60325520, 18.19, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:54:48'),
(78, 68, 'in', '2026-03-25 07:55:02', '2026-03-25', 0, 24.57246160, 46.60275440, 19.20, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:55:02'),
(79, 1, 'in', '2026-03-25 07:57:42', '2026-03-25', 0, 24.57235740, 46.60269940, 27.05, '2a02:9b0:3c:931b:8e47:f40d:f193:d30f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 04:57:42'),
(80, 46, 'in', '2026-03-25 08:03:25', '2026-03-25', 0, 24.56957350, 46.61463430, 3.79, '2001:16a2:c218:e33f:f91d:2c25:7068:dc6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:03:25'),
(81, 41, 'in', '2026-03-25 08:03:40', '2026-03-25', 0, 24.56958000, 46.61440380, 23.69, '2a02:9b0:44:2acf:b4d:8ef4:e48a:dcac', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-03-25 05:03:40'),
(82, 44, 'in', '2026-03-25 08:03:51', '2026-03-25', 0, 24.56956287, 46.61467319, 2.01, '2001:16a4:6e:9733:cae:421d:e66d:9997', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-25 05:03:51'),
(83, 32, 'in', '2026-03-25 08:03:53', '2026-03-25', 0, 24.56618930, 46.62178430, 14.99, '2a02:9b0:f:379:980f:ddc6:854f:95df', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:03:53'),
(84, 29, 'in', '2026-03-25 08:04:04', '2026-03-25', 0, 24.56620750, 46.62178430, 12.34, '2a02:9b0:f:379:f8ed:2bab:f6a8:3dda', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:04:04'),
(85, 3, 'in', '2026-03-25 08:05:20', '2026-03-25', 0, 24.57234680, 46.60266590, 15.83, '104.28.162.133', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:05:20'),
(86, 70, 'in', '2026-03-25 08:06:52', '2026-03-25', 0, 24.57239610, 46.60277810, 37.63, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:06:52'),
(87, 52, 'in', '2026-03-25 08:10:27', '2026-03-25', 0, 24.57236550, 46.60301010, 26.42, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:10:27'),
(88, 26, 'in', '2026-03-25 08:10:44', '2026-03-25', 1, 24.57238644, 46.60257885, 8.95, '109.82.228.137', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-25 05:10:44'),
(89, 36, 'in', '2026-03-25 08:10:48', '2026-03-25', 1, 24.57657658, 46.60005360, 999.99, '2a09:bac6:d84a:2c5a::46b:3f', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.119 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-25 05:10:48'),
(90, 40, 'in', '2026-03-25 08:11:04', '2026-03-25', 1, 24.57245710, 46.60315070, 13.01, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:11:04'),
(91, 28, 'in', '2026-03-25 08:12:16', '2026-03-25', 2, 24.57251160, 46.60278200, 21.42, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:12:16'),
(92, 55, 'in', '2026-03-25 08:14:31', '2026-03-25', 5, 24.57231840, 46.60264290, 18.83, '2a02:9b0:3c:931b:2c25:49df:7405:3b06', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:14:31'),
(93, 67, 'in', '2026-03-25 08:21:34', '2026-03-25', 12, 24.57235330, 46.60266020, 25.59, '2a02:9b0:3c:931b:87d:5d11:2458:50a2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:21:34'),
(94, 71, 'in', '2026-03-25 08:21:58', '2026-03-25', 12, 24.56619770, 46.62178820, 12.20, '2a02:9b0:f:379:ed7d:3ebd:9b25:5aad', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:21:58'),
(95, 53, 'in', '2026-03-25 08:23:41', '2026-03-25', 14, 24.57234510, 46.60266290, 17.73, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:23:41'),
(96, 43, 'in', '2026-03-25 08:23:56', '2026-03-25', 14, 24.56967800, 46.61428950, 31.23, '2a02:9b0:44:2acf:de67:44d:a1dc:2a29', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 05:23:56'),
(97, 70, 'out', '2026-03-25 12:20:09', '2026-03-25', 0, 24.57239580, 46.60276260, 23.01, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 09:20:09'),
(98, 70, 'out', '2026-03-25 12:27:17', '2026-03-25', 0, 24.57239580, 46.60276260, 23.01, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-25 09:27:17'),
(99, 2, 'in', '2026-03-26 07:37:41', '2026-03-26', 0, 24.56629015, 46.62165813, 13.80, '2001:16a2:c056:81a3:6c38:6736:a80b:55a3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.151 Mobile/15E148 Safari/604.1', NULL, '2026-03-26 04:37:41'),
(100, 30, 'in', '2026-03-26 07:37:50', '2026-03-26', 0, 24.56623422, 46.62162798, 12.02, '2a02:9b0:eb:f3d1:2cfb:ed14:14b7:17d', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-26 04:37:50'),
(101, 35, 'in', '2026-03-26 07:51:01', '2026-03-26', 0, 24.56625630, 46.62174950, 13.10, '2a09:bac6:d84c:1eb::31:150', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 04:51:01'),
(102, 34, 'in', '2026-03-26 07:51:19', '2026-03-26', 0, 24.56621180, 46.62178090, 13.17, '37.125.153.127', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 04:51:19'),
(103, 31, 'in', '2026-03-26 07:58:04', '2026-03-26', 0, 24.57279430, 46.60290510, 22.80, '2a02:9b0:3c:931b:72aa:90b0:25dc:7af3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 04:58:04'),
(104, 46, 'in', '2026-03-26 08:00:25', '2026-03-26', 0, 24.56956150, 46.61464430, 3.79, '2001:16a2:c218:e33f:21e5:36f3:b37e:2096', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:00:25'),
(105, 71, 'in', '2026-03-26 08:01:26', '2026-03-26', 0, 24.56617590, 46.62161410, 21.80, '2a02:9b0:eb:f3d1:f555:dfd1:fb35:e051', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:01:26'),
(106, 44, 'in', '2026-03-26 08:01:48', '2026-03-26', 0, 24.56964134, 46.61425597, 2.22, '2a02:9b0:44:2acf:ad15:3abd:574e:c4b', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-26 05:01:48'),
(107, 29, 'in', '2026-03-26 08:01:55', '2026-03-26', 0, 24.56613870, 46.62183690, 12.71, '2a02:9b0:eb:f3d1:d528:b4bf:e2ee:e138', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:01:55'),
(108, 41, 'in', '2026-03-26 08:02:51', '2026-03-26', 0, 24.56959950, 46.61449740, 75.00, '2a02:9b0:44:2acf:b55d:75a6:4bb2:684f', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-03-26 05:02:51'),
(109, 38, 'in', '2026-03-26 08:02:59', '2026-03-26', 0, 24.56622610, 46.62176130, 11.51, '2a02:9b0:eb:f3d1:250a:7dc:2898:94c8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:02:59'),
(110, 43, 'in', '2026-03-26 08:04:21', '2026-03-26', 0, 24.56966980, 46.61433150, 13.45, '2001:16a4:19:8eaf:18a0:4ac2:6b7f:95a9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:04:21'),
(111, 28, 'in', '2026-03-26 08:06:16', '2026-03-26', 0, 24.57242310, 46.60279690, 37.40, '2a02:9b0:3c:931b:e8d4:11a:df12:f489', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:06:16'),
(112, 26, 'in', '2026-03-26 08:06:25', '2026-03-26', 0, 24.57234208, 46.60250369, 32.81, '77.232.122.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-26 05:06:25'),
(113, 7, 'in', '2026-03-26 08:10:51', '2026-03-26', 1, 24.57248560, 46.60275980, 14.64, '2001:16a4:d0:f20e:189e:3239:3438:768c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:10:51'),
(114, 70, 'in', '2026-03-26 08:11:26', '2026-03-26', 1, 24.57246120, 46.60276030, 100.00, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:11:26'),
(115, 3, 'in', '2026-03-26 08:11:57', '2026-03-26', 2, 24.57239440, 46.60268070, 25.11, '104.28.159.88', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:11:57'),
(116, 1, 'in', '2026-03-26 08:12:15', '2026-03-26', 2, 24.57240410, 46.60268170, 22.36, '77.232.122.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:12:15'),
(117, 40, 'in', '2026-03-26 08:12:16', '2026-03-26', 2, 24.57279710, 46.60288070, 28.55, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:12:16'),
(118, 49, 'in', '2026-03-26 08:15:55', '2026-03-26', 6, 24.56955830, 46.61430670, 5.00, '2a02:9b0:44:2acf:dfea:4fb1:8f93:be8b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:15:55'),
(119, 36, 'in', '2026-03-26 08:16:20', '2026-03-26', 6, 24.57657658, 46.60005360, 999.99, '2a09:bac1:2780:cc0::46b:3f', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.119 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-26 05:16:20'),
(120, 32, 'in', '2026-03-26 08:19:58', '2026-03-26', 10, 24.56620250, 46.62177610, 13.69, '2a02:9b0:eb:f3d1:fc33:10c5:dd73:4c19', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:19:58'),
(121, 53, 'in', '2026-03-26 08:20:30', '2026-03-26', 11, 24.57235210, 46.60268550, 19.17, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:20:30'),
(122, 67, 'in', '2026-03-26 08:31:13', '2026-03-26', 21, 24.57238440, 46.60269370, 11.51, '2a02:9b0:3c:931b:3f67:2be3:becf:80ad', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 05:31:13'),
(123, 67, 'out', '2026-03-26 13:19:43', '2026-03-26', 0, 24.57238230, 46.60268660, 11.60, '2001:16a2:ce03:d000:ddf9:1771:40da:62da', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 10:19:43'),
(124, 31, 'out', '2026-03-26 15:01:35', '2026-03-26', 0, 24.57239830, 46.60313170, 6.10, '2a02:cb80:4221:8fbf:7b0e:99fc:62b6:277', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 12:01:35'),
(125, 35, 'out', '2026-03-26 22:27:33', '2026-03-26', 0, 24.56625270, 46.62175770, 13.15, '2a09:bac6:d849:254b::3b7:53', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-26 19:27:33'),
(126, 1, 'in', '2026-03-27 17:29:56', '2026-03-27', 90, 24.57211700, 46.60266870, 43.38, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-27 14:29:56'),
(127, 54, 'in', '2026-03-28 07:31:09', '2026-03-28', 0, 24.56629015, 46.62165813, 13.80, '2001:16a2:c03f:c4b2:4908:6aa6:a69b:7cc3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.151 Mobile/15E148 Safari/604.1', NULL, '2026-03-28 04:31:09'),
(128, 34, 'in', '2026-03-28 07:49:58', '2026-03-28', 0, 24.56622070, 46.62176180, 13.16, '109.82.75.14', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 04:49:58'),
(129, 38, 'in', '2026-03-28 07:51:40', '2026-03-28', 0, 24.56617170, 46.62187530, 4.21, '2a02:9b0:45:36e9:2d29:d932:d7dc:826b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 04:51:40'),
(130, 35, 'in', '2026-03-28 07:57:15', '2026-03-28', 0, 24.56619170, 46.62178330, 13.49, '2a02:9b0:45:36e9:f48e:9faa:ea0c:36e4', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 04:57:15'),
(131, 26, 'in', '2026-03-28 08:01:31', '2026-03-28', 0, 24.57236362, 46.60251128, 35.38, '77.232.122.68', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-28 05:01:31'),
(132, 31, 'in', '2026-03-28 08:01:36', '2026-03-28', 0, 24.57248860, 46.60311510, 18.59, '45.121.214.180', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:01:36'),
(133, 44, 'in', '2026-03-28 08:01:51', '2026-03-28', 0, 24.56947966, 46.61468938, 2.00, '2001:16a4:6e:9733:755f:1c3d:b834:4de2', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-28 05:01:51'),
(134, 46, 'in', '2026-03-28 08:02:17', '2026-03-28', 0, 24.56953400, 46.61467590, 3.79, '2001:16a2:c218:e33f:5a2:e996:4676:d25a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:02:17'),
(135, 71, 'in', '2026-03-28 08:03:08', '2026-03-28', 0, 24.56623160, 46.62180500, 26.72, '149.126.14.19', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:03:08'),
(136, 3, 'in', '2026-03-28 08:05:05', '2026-03-28', 0, 24.57232330, 46.60256830, 3.50, '2a09:bac6:d84f:2541::3b6:20', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:05:05'),
(137, 7, 'in', '2026-03-28 08:07:36', '2026-03-28', 0, 24.57244260, 46.60272140, 20.00, '2a02:9b0:3c:931b:798a:223c:1ad9:58f6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:07:36'),
(138, 67, 'in', '2026-03-28 08:08:02', '2026-03-28', 0, 24.57235700, 46.60263150, 18.45, '2a02:9b0:3c:931b:e2d5:e226:8eda:db49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:08:02'),
(139, 1, 'in', '2026-03-28 08:09:10', '2026-03-28', 0, 24.57237250, 46.60267830, 17.66, '2a02:9b0:3c:931b:26d9:898:83c:6743', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:09:10'),
(140, 43, 'in', '2026-03-28 08:09:28', '2026-03-28', 0, 24.56967950, 46.61428970, 5.44, '2a02:9b0:44:2acf:57f8:d7f9:831c:6e82', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:09:28'),
(141, 29, 'in', '2026-03-28 08:09:52', '2026-03-28', 0, 24.56615640, 46.62187640, 5.16, '2a02:9b0:45:36e9:3ee4:816f:29f1:f453', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:09:52'),
(142, 36, 'in', '2026-03-28 08:10:27', '2026-03-28', 0, 24.57657658, 46.60005360, 999.99, '2a09:bac1:27e0:cc0::3b6:20', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.120 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-28 05:10:27'),
(143, 55, 'in', '2026-03-28 08:13:21', '2026-03-28', 3, 24.57239750, 46.60269680, 16.36, '2a02:9b0:3c:931b:447f:8eea:2af0:d0c6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:13:21'),
(144, 40, 'in', '2026-03-28 08:18:03', '2026-03-28', 8, 24.57244350, 46.60314330, 15.89, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:18:03'),
(145, 53, 'in', '2026-03-28 08:18:56', '2026-03-28', 9, 24.57233700, 46.60265750, 16.15, '109.82.228.137', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:18:56'),
(146, 30, 'in', '2026-03-28 08:24:11', '2026-03-28', 14, 24.56621624, 46.62177680, 48.97, '2001:16a2:c032:f69a:399e:32c2:cb32:40c1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-28 05:24:11'),
(147, 49, 'in', '2026-03-28 08:24:18', '2026-03-28', 14, 24.56962450, 46.61424430, 4.94, '2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 05:24:18'),
(148, 35, 'out', '2026-03-28 22:14:07', '2026-03-28', 0, 24.56620220, 46.62177730, 20.00, '2a02:9b0:45:36e9:e1:1978:7be5:623b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-28 19:14:07'),
(149, 26, 'out', '2026-03-28 23:33:26', '2026-03-28', 0, 24.57230921, 46.60259578, 14.48, '77.232.123.177', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-28 20:33:26'),
(150, 30, 'in', '2026-03-29 07:39:39', '2026-03-29', 0, 24.56625766, 46.62162257, 13.88, '109.82.75.14', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-29 04:39:39'),
(151, 35, 'in', '2026-03-29 07:52:04', '2026-03-29', 0, 24.56623250, 46.62176190, 14.94, '2a09:bac5:3217:254b::3b7:26', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 04:52:04'),
(152, 34, 'in', '2026-03-29 07:52:08', '2026-03-29', 0, 24.56624270, 46.62175820, 12.32, '109.82.75.14', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 04:52:08'),
(153, 41, 'in', '2026-03-29 07:54:48', '2026-03-29', 0, 24.56944240, 46.61439630, 14.21, '2a02:9b0:44:2acf:b898:bb8b:62af:2045', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-03-29 04:54:48'),
(154, 38, 'in', '2026-03-29 07:56:16', '2026-03-29', 0, 24.56624710, 46.62176390, 12.85, '2a02:9b0:45:36e9:f252:2fed:c21d:bbcc', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 04:56:16'),
(155, 54, 'in', '2026-03-29 07:58:56', '2026-03-29', 0, 24.56633380, 46.62165130, 4.53, '2a02:9b0:45:36e9:a0c7:1cbc:ab34:4203', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 04:58:56'),
(156, 31, 'in', '2026-03-29 08:00:32', '2026-03-29', 0, 24.57282050, 46.60290180, 28.21, '2a02:9b0:f2:982e:c1fa:b50:3402:aaa4', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:00:32'),
(157, 46, 'in', '2026-03-29 08:01:24', '2026-03-29', 0, 24.56957210, 46.61464380, 3.79, '2001:16a2:c218:e33f:464:212b:10b8:a63c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:01:24'),
(158, 71, 'in', '2026-03-29 08:03:00', '2026-03-29', 0, 24.56620330, 46.62181340, 11.63, '2a02:9b0:45:36e9:8f1e:5673:8b8a:cae0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:03:00'),
(159, 7, 'in', '2026-03-29 08:03:03', '2026-03-29', 0, 24.57236710, 46.60268920, 24.74, '2a02:9b0:f2:982e:ba78:861d:ae90:aa2a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:03:03'),
(160, 44, 'in', '2026-03-29 08:03:56', '2026-03-29', 0, 24.56950235, 46.61469509, 2.00, '2001:16a4:83:71d7:c517:be2:56c2:558f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-29 05:03:56'),
(161, 49, 'in', '2026-03-29 08:04:06', '2026-03-29', 0, 24.56966620, 46.61432240, 14.56, '2a02:9b0:44:2acf:83ad:8d8:fe22:e7f2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:04:06'),
(162, 29, 'in', '2026-03-29 08:04:10', '2026-03-29', 0, 24.56624270, 46.62178750, 23.68, '2a02:9b0:45:36e9:ba3b:2b1b:3004:45de', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:04:10'),
(163, 26, 'in', '2026-03-29 08:05:05', '2026-03-29', 0, 24.57231420, 46.60261600, 9.02, '37.125.238.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-29 05:05:05'),
(164, 1, 'in', '2026-03-29 08:06:22', '2026-03-29', 0, 24.57233770, 46.60266610, 24.68, '2a02:9b0:f2:982e:5430:90d2:4f9f:2b43', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:06:22'),
(165, 3, 'in', '2026-03-29 08:09:55', '2026-03-29', 0, 24.57231110, 46.60264730, 26.59, '2a02:9b0:f2:982e:c5b8:8504:de6d:c525', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:09:55'),
(166, 70, 'in', '2026-03-29 08:11:59', '2026-03-29', 2, 24.57248200, 46.60275550, 15.64, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:11:59'),
(167, 53, 'in', '2026-03-29 08:16:35', '2026-03-29', 7, 24.57236920, 46.60266450, 15.50, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:16:35'),
(168, 43, 'in', '2026-03-29 08:16:49', '2026-03-29', 7, 24.56964820, 46.61427630, 9.67, '2001:16a4:11:608b:18a1:36e0:a5ce:ece6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:16:49'),
(169, 48, 'in', '2026-03-29 08:18:23', '2026-03-29', 8, 24.56965640, 46.61433810, 12.11, '2a02:9b0:44:2acf:cca:68f7:a763:9930', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:18:23'),
(170, 40, 'in', '2026-03-29 08:18:42', '2026-03-29', 9, 24.57239160, 46.60298210, 29.19, '2a02:9b0:f2:982e:c48c:6a45:2ad5:ed5', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:18:42'),
(171, 67, 'in', '2026-03-29 08:55:18', '2026-03-29', 45, 24.57239930, 46.60268620, 12.17, '2a02:9b0:f2:982e:49f5:9e02:a254:a7b8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-29 05:55:18'),
(172, 30, 'out', '2026-03-29 16:21:35', '2026-03-29', 0, 24.56625643, 46.62162106, 19.20, '2a02:9b0:45:36e9:c5ec:5b71:2bee:a3e7', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-29 13:21:35'),
(173, 67, 'in', '2026-03-30 07:31:10', '2026-03-30', 0, 24.57234040, 46.60263750, 20.01, '2a02:9b0:f2:982e:1a2:99f8:64a3:a377', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 04:31:10'),
(174, 30, 'in', '2026-03-30 07:46:31', '2026-03-30', 0, 24.56629475, 46.62160187, 22.70, '2001:16a2:c056:d7e1:91c4:bbbc:f997:6711', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-30 04:46:31'),
(175, 38, 'in', '2026-03-30 07:48:12', '2026-03-30', 0, 24.56624510, 46.62174910, 13.33, '2a02:9b0:af:9893:72c8:37de:46a5:70bf', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 04:48:12'),
(176, 31, 'in', '2026-03-30 07:54:38', '2026-03-30', 0, 24.57274610, 46.60300540, 35.77, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 04:54:38'),
(177, 1, 'in', '2026-03-30 07:55:30', '2026-03-30', 0, 24.57239990, 46.60267890, 25.21, '2a02:9b0:f2:982e:3676:a018:5d95:4e4a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 04:55:30'),
(178, 35, 'in', '2026-03-30 07:59:05', '2026-03-30', 0, 24.56623950, 46.62176060, 14.48, '2a02:9b0:af:9893:add6:a6f6:dbfd:fb35', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 04:59:05'),
(179, 46, 'in', '2026-03-30 08:01:05', '2026-03-30', 0, 24.56957400, 46.61466440, 4.70, '2001:16a2:c218:e33f:91d2:7378:b5e0:cf59', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:01:05'),
(180, 41, 'in', '2026-03-30 08:01:20', '2026-03-30', 0, 24.56955170, 46.61444830, 33.35, '2001:16a2:c090:1580:2cac:6216:8a65:eb4e', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-03-30 05:01:20'),
(181, 44, 'in', '2026-03-30 08:03:16', '2026-03-30', 0, 24.56950458, 46.61470639, 4.75, '2001:16a4:83:71d7:c517:be2:56c2:558f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-30 05:03:16'),
(182, 71, 'in', '2026-03-30 08:03:57', '2026-03-30', 0, 24.56621160, 46.62179020, 11.72, '2a02:9b0:af:9893:dd86:ecfb:d83:479e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:03:57'),
(183, 47, 'in', '2026-03-30 08:04:12', '2026-03-30', 0, 24.56956830, 46.61423330, 100.00, '2a02:9b0:ee:e9df:9158:5b69:c28c:8b13', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:04:12'),
(184, 29, 'in', '2026-03-30 08:04:20', '2026-03-30', 0, 24.56620030, 46.62178990, 20.63, '2a02:9b0:af:9893:9c44:6365:b735:45aa', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:04:20'),
(185, 34, 'in', '2026-03-30 08:06:07', '2026-03-30', 0, 24.56620790, 46.62177500, 15.06, '2a02:9b0:af:9893:b2cf:15f3:bcc7:587e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:06:07'),
(186, 26, 'in', '2026-03-30 08:06:24', '2026-03-30', 0, 24.57230001, 46.60260731, 19.90, '37.125.238.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-30 05:06:24'),
(187, 43, 'in', '2026-03-30 08:11:36', '2026-03-30', 2, 24.56964020, 46.61425420, 4.97, '2a02:9b0:ee:e9df:e4c7:d189:66b3:54e4', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:11:36'),
(188, 70, 'in', '2026-03-30 08:13:09', '2026-03-30', 3, 24.57248440, 46.60274800, 15.90, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:13:09'),
(189, 36, 'in', '2026-03-30 08:15:04', '2026-03-30', 5, 24.57657658, 46.60005360, 999.99, '2a09:bac5:3212:1eb::31:1bf', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.120 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-30 05:15:04'),
(190, 3, 'in', '2026-03-30 08:15:16', '2026-03-30', 5, 24.57233540, 46.60264850, 13.32, '2a09:bac5:3215:2c5a::46b:76', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:15:16'),
(191, 49, 'in', '2026-03-30 08:16:29', '2026-03-30', 6, 24.56958670, 46.61434670, 6.00, '2a02:9b0:ee:e9df:3cb5:a60b:5159:2854', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:16:29'),
(192, 52, 'in', '2026-03-30 08:17:17', '2026-03-30', 7, 24.56965790, 46.61411120, 14.52, '2a02:9b0:ee:e9df:9d2f:35f6:f732:2bf3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:17:17'),
(193, 53, 'in', '2026-03-30 08:18:07', '2026-03-30', 8, 24.57236550, 46.60267070, 15.55, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:18:07'),
(194, 40, 'in', '2026-03-30 08:19:04', '2026-03-30', 9, 24.57247080, 46.60313250, 25.93, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:19:04'),
(195, 10, 'in', '2026-03-30 08:19:48', '2026-03-30', 10, 24.56963450, 46.61406820, 15.48, '2a02:9b0:ee:e9df:e1d9:8412:e665:3733', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:19:48'),
(196, 22, 'in', '2026-03-30 08:20:58', '2026-03-30', 11, 24.56965070, 46.61409820, 13.34, '2a02:9b0:ee:e9df:9d2f:35f6:f732:2bf3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:20:58'),
(197, 24, 'in', '2026-03-30 08:21:49', '2026-03-30', 12, 24.56955310, 46.61409890, 10.56, '2a02:9b0:ee:e9df:9d2f:35f6:f732:2bf3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:21:49'),
(198, 48, 'in', '2026-03-30 08:27:01', '2026-03-30', 17, 24.56968330, 46.61457500, 4.07, '109.83.101.200', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:27:01'),
(199, 12, 'in', '2026-03-30 08:48:49', '2026-03-30', 39, 24.56964440, 46.61409940, 12.63, '2a02:9b0:ee:e9df:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 05:48:49'),
(200, 72, 'in', '2026-03-30 09:00:10', '2026-03-30', 50, 24.56963490, 46.61414120, 18.56, '2a02:9b0:ee:e9df:b7da:b261:b763:5048', 'Mozilla/5.0 (Linux; Android 15; V2434 Build/AP3A.240905.015.A2_NN_V000L1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/146.0.7680.120 Mobile Safari/537.36 Line/26.3.1/IAB', NULL, '2026-03-30 06:00:10'),
(201, 16, 'in', '2026-03-30 09:00:35', '2026-03-30', 51, 24.56964270, 46.61413570, 16.22, '2a02:9b0:ee:e9df:ca89:e65d:25ae:50fc', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 06:00:35');
INSERT INTO `attendances` (`id`, `employee_id`, `type`, `timestamp`, `attendance_date`, `late_minutes`, `latitude`, `longitude`, `location_accuracy`, `ip_address`, `user_agent`, `notes`, `created_at`) VALUES
(202, 73, 'in', '2026-03-30 09:05:27', '2026-03-30', 55, 24.56963720, 46.61408910, 13.03, '2a02:9b0:ee:e9df:a284:bbe3:208d:e160', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 06:05:27'),
(203, 17, 'in', '2026-03-30 09:05:49', '2026-03-30', 56, 24.56964890, 46.61408340, 12.51, '2a02:9b0:ee:e9df:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 06:05:49'),
(204, 74, 'in', '2026-03-30 09:08:06', '2026-03-30', 58, 24.56963830, 46.61409370, 22.47, '2a02:9b0:ee:e9df:50b1:bb1:a315:320', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 06:08:06'),
(205, 11, 'in', '2026-03-30 09:08:59', '2026-03-30', 59, 24.56963170, 46.61406580, 12.66, '2a02:9b0:ee:e9df:151d:1f26:5bbe:a543', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 06:08:59'),
(206, 17, 'out', '2026-03-30 13:59:51', '2026-03-30', 0, 24.56964070, 46.61408030, 8.45, '2a02:9b0:ee:e9df:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 10:59:51'),
(207, 17, '', '2026-03-30 14:00:03', '2026-03-30', 0, 24.56965740, 46.61410330, 18.30, '2a02:9b0:ee:e9df:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:00:03'),
(208, 11, 'out', '2026-03-30 14:00:09', '2026-03-30', 0, 24.56964870, 46.61411140, 14.40, '2a02:9b0:ee:e9df:f067:6edc:36f3:4c0d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:00:09'),
(209, 12, 'out', '2026-03-30 14:00:38', '2026-03-30', 0, 24.56965100, 46.61409200, 13.86, '2a02:9b0:ee:e9df:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:00:38'),
(210, 12, '', '2026-03-30 14:00:48', '2026-03-30', 0, 24.56965160, 46.61408750, 23.73, '2a02:9b0:ee:e9df:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:00:48'),
(211, 74, 'out', '2026-03-30 14:01:23', '2026-03-30', 0, 24.56965000, 46.61415830, 18.49, '2a02:9b0:ee:e9df:ed52:30ac:606a:76ee', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:01:23'),
(212, 17, '', '2026-03-30 14:01:42', '2026-03-30', 0, 24.56970780, 46.61411820, 12.00, '2a02:9b0:ee:e9df:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:01:42'),
(213, 12, '', '2026-03-30 14:01:44', '2026-03-30', 0, 24.56970270, 46.61410240, 8.21, '2a02:9b0:ee:e9df:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:01:44'),
(214, 73, 'out', '2026-03-30 14:02:44', '2026-03-30', 0, 24.56967700, 46.61408460, 5.51, '2a02:9b0:ee:e9df:b1be:c033:9ed7:d195', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:02:44'),
(215, 43, 'out', '2026-03-30 14:08:06', '2026-03-30', 0, 24.56966190, 46.61430040, 13.65, '2a02:9b0:ee:e9df:9088:425d:1781:c8d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 11:08:06'),
(216, 72, 'out', '2026-03-30 14:09:30', '2026-03-30', 0, 24.56967730, 46.61417720, 14.69, '2a02:9b0:ee:e9df:55ff:c862:5a41:5499', 'Mozilla/5.0 (Linux; Android 15; V2434) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/123.0.6312.118 Mobile Safari/537.36 VivoBrowser/15.0.2.3', NULL, '2026-03-30 11:09:30'),
(217, 72, '', '2026-03-30 14:09:41', '2026-03-30', 0, 24.56960570, 46.61426210, 14.91, '2a02:9b0:ee:e9df:55ff:c862:5a41:5499', 'Mozilla/5.0 (Linux; Android 15; V2434) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/123.0.6312.118 Mobile Safari/537.36 VivoBrowser/15.0.2.3', NULL, '2026-03-30 11:09:41'),
(218, 10, 'out', '2026-03-30 19:39:32', '2026-03-30', 0, 24.56964970, 46.61418080, 18.63, '2a02:9b0:ee:e9df:e1d9:8412:e665:3733', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 16:39:32'),
(219, 73, '', '2026-03-30 20:00:56', '2026-03-30', 0, 24.56966250, 46.61412960, 15.28, '2a02:9b0:ee:e9df:b1be:c033:9ed7:d195', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 17:00:56'),
(220, 72, '', '2026-03-30 20:02:28', '2026-03-30', 0, 24.56964530, 46.61413880, 16.20, '2a02:9b0:ee:e9df:bc12:f8e0:43bb:a518', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 17:02:28'),
(221, 24, 'out', '2026-03-30 20:02:37', '2026-03-30', 0, 24.56966200, 46.61417400, 18.83, '2a02:9b0:ee:e9df:a4f3:4d33:8f5d:9e7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 17:02:37'),
(222, 24, '', '2026-03-30 20:03:16', '2026-03-30', 0, 24.56965340, 46.61423160, 15.16, '2a02:9b0:ee:e9df:a4f3:4d33:8f5d:9e7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 17:03:16'),
(223, 24, '', '2026-03-30 20:06:00', '2026-03-30', 0, 24.56964990, 46.61413850, 10.71, '2a02:9b0:ee:e9df:a4f3:4d33:8f5d:9e7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-30 17:06:00'),
(224, 52, 'in', '2026-03-31 07:14:27', '2026-03-31', 0, 24.57244760, 46.60310870, 24.96, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 04:14:27'),
(225, 38, 'in', '2026-03-31 07:44:38', '2026-03-31', 0, 24.56623480, 46.62175670, 14.89, '2a02:9b0:1:b5c2:dbaf:6fbc:c06f:7ad2', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 04:44:38'),
(226, 30, 'in', '2026-03-31 07:49:48', '2026-03-31', 0, 24.56624754, 46.62158557, 13.98, '37.43.131.182', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-31 04:49:48'),
(227, 41, 'in', '2026-03-31 07:50:49', '2026-03-31', 0, 24.56969340, 46.61448030, 35.08, '2a02:9b0:ee:e9df:9f3d:7152:9f64:429c', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-03-31 04:50:49'),
(228, 35, 'in', '2026-03-31 07:57:09', '2026-03-31', 0, 24.56624090, 46.62175610, 14.36, '2a02:9b0:1:b5c2:8ef:4e53:f8ac:6618', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 04:57:09'),
(229, 31, 'in', '2026-03-31 07:57:14', '2026-03-31', 0, 24.57272830, 46.60264500, 3.50, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 04:57:14'),
(230, 12, 'in', '2026-03-31 08:00:59', '2026-03-31', 0, 24.56968750, 46.61426230, 12.90, '2a02:9b0:ee:e9df:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:00:59'),
(231, 17, 'in', '2026-03-31 08:01:21', '2026-03-31', 0, 24.56964440, 46.61424700, 12.00, '2a02:9b0:ee:e9df:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:01:21'),
(232, 71, 'in', '2026-03-31 08:02:03', '2026-03-31', 0, 24.56618360, 46.62180350, 16.59, '2a02:9b0:1:b5c2:b0a4:c319:bc94:76ce', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:02:03'),
(233, 73, 'in', '2026-03-31 08:02:13', '2026-03-31', 0, 24.56973280, 46.61431810, 24.19, '2a02:9b0:ee:e9df:91df:1640:9b1f:13af', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:02:13'),
(234, 46, 'in', '2026-03-31 08:02:26', '2026-03-31', 0, 24.56954350, 46.61463100, 3.79, '2001:16a4:15:c66e:359e:3969:a424:72d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:02:26'),
(235, 7, 'in', '2026-03-31 08:03:11', '2026-03-31', 0, 24.57246770, 46.60275740, 15.81, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:03:11'),
(236, 47, 'in', '2026-03-31 08:03:18', '2026-03-31', 0, 24.56974170, 46.61447000, 3.40, '2a02:9b0:ee:e9df:99b8:3e9a:2552:a83f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:03:18'),
(237, 51, 'in', '2026-03-31 08:04:29', '2026-03-31', 0, 24.56965260, 46.61428930, 8.41, '2001:16a4:67:395f:6cbb:17ff:fe9f:b0d6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:04:29'),
(238, 72, 'in', '2026-03-31 08:04:37', '2026-03-31', 0, 24.56966730, 46.61421410, 11.72, '2a02:9b0:ee:e9df:1d58:3013:eead:c3e9', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:04:37'),
(239, 11, 'in', '2026-03-31 08:04:52', '2026-03-31', 0, 24.56973670, 46.61414670, 8.60, '2a02:9b0:ee:e9df:d3ef:2044:db7a:4cba', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:04:52'),
(240, 29, 'in', '2026-03-31 08:05:00', '2026-03-31', 0, 24.56621630, 46.62177920, 52.33, '2a02:9b0:1:b5c2:c997:47be:b0c:bd9f', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:05:00'),
(241, 24, 'in', '2026-03-31 08:05:16', '2026-03-31', 0, 24.56969150, 46.61424070, 14.48, '2a02:9b0:ee:e9df:d484:e620:405f:33ed', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:05:16'),
(242, 74, 'in', '2026-03-31 08:05:39', '2026-03-31', 0, 24.56962010, 46.61419730, 37.00, '2a02:9b0:ee:e9df:a39:f989:68e7:8495', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:05:39'),
(243, 16, 'in', '2026-03-31 08:06:07', '2026-03-31', 0, 24.56970690, 46.61426080, 13.19, '2a02:9b0:ee:e9df:a755:96ec:afed:d8e4', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:06:07'),
(244, 44, 'in', '2026-03-31 08:06:20', '2026-03-31', 0, 24.56954788, 46.61466431, 2.25, '2001:16a4:83:71d7:14a8:280e:bb3e:91ca', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-31 05:06:20'),
(245, 1, 'in', '2026-03-31 08:06:20', '2026-03-31', 0, 24.57235930, 46.60266470, 24.04, '2a02:9b0:f2:982e:e628:f623:d1db:984b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:06:20'),
(246, 10, 'in', '2026-03-31 08:07:53', '2026-03-31', 0, 24.56966500, 46.61416730, 19.19, '2a02:9b0:ee:e9df:7bf0:a6a4:f56a:1aef', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:07:53'),
(247, 28, 'in', '2026-03-31 08:09:17', '2026-03-31', 0, 24.57241430, 46.60271790, 290.57, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:09:17'),
(248, 3, 'in', '2026-03-31 08:09:30', '2026-03-31', 0, 24.57232290, 46.60266460, 16.99, '2a09:bac1:27c0:cc0::46b:1a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:09:30'),
(249, 34, 'in', '2026-03-31 08:10:13', '2026-03-31', 0, 24.56623610, 46.62177110, 14.45, '37.43.131.182', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:10:13'),
(250, 43, 'in', '2026-03-31 08:10:33', '2026-03-31', 1, 24.56966820, 46.61431150, 1.60, '2001:16a4:b5:a3e0:18a1:d3ce:b91:41c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:10:33'),
(251, 70, 'in', '2026-03-31 08:13:13', '2026-03-31', 3, 24.57235490, 46.60269190, 15.29, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:13:13'),
(252, 26, 'in', '2026-03-31 08:13:57', '2026-03-31', 4, 24.57236189, 46.60259795, 10.82, '37.125.238.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-03-31 05:13:57'),
(253, 53, 'in', '2026-03-31 08:15:33', '2026-03-31', 6, 24.57235250, 46.60266470, 15.34, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:15:33'),
(254, 67, 'in', '2026-03-31 08:15:54', '2026-03-31', 6, 24.57238250, 46.60247230, 1.46, '2a02:9b0:f2:982e:17b1:8373:b195:260e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:15:54'),
(255, 49, 'in', '2026-03-31 08:17:59', '2026-03-31', 8, 24.56966840, 46.61433270, 15.21, '2a02:9b0:ee:e9df:559e:34f7:af92:4633', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:17:59'),
(256, 36, 'in', '2026-03-31 08:18:17', '2026-03-31', 8, 24.57657658, 46.60005360, 999.99, '2a09:bac1:27e0:cc0::3b7:23', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.120 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-03-31 05:18:17'),
(257, 40, 'in', '2026-03-31 08:18:33', '2026-03-31', 9, 24.57245990, 46.60311880, 17.84, '37.125.238.175', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:18:33'),
(258, 48, 'in', '2026-03-31 08:26:01', '2026-03-31', 16, 24.56965890, 46.61435260, 12.04, '109.83.101.200', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 05:26:01'),
(259, 74, 'out', '2026-03-31 13:40:43', '2026-03-31', 0, 24.56963930, 46.61410920, 15.80, '2a02:9b0:ee:e9df:9d8b:b377:bc2a:be6b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 10:40:43'),
(260, 17, 'out', '2026-03-31 14:01:08', '2026-03-31', 0, 24.56965980, 46.61422390, 16.81, '51.15.91.118', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:01:08'),
(261, 11, 'out', '2026-03-31 14:01:11', '2026-03-31', 0, 24.56965840, 46.61419820, 17.05, '2a02:9b0:ee:e9df:6411:65ec:8f86:95f7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:01:11'),
(262, 17, '', '2026-03-31 14:01:32', '2026-03-31', 0, 24.56968410, 46.61412960, 10.24, '51.15.91.118', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:01:32'),
(263, 72, 'out', '2026-03-31 14:02:01', '2026-03-31', 0, 24.56965580, 46.61415700, 16.33, '2a02:9b0:ee:e9df:ecf:878f:5cd7:3423', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:02:01'),
(264, 72, '', '2026-03-31 14:02:22', '2026-03-31', 0, 24.56970850, 46.61411160, 4.11, '2a02:9b0:ee:e9df:ecf:878f:5cd7:3423', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:02:22'),
(265, 24, 'out', '2026-03-31 14:08:37', '2026-03-31', 0, 24.56966640, 46.61410030, 11.43, '2a02:9b0:ee:e9df:6103:de2c:3c21:e86c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:08:37'),
(266, 24, '', '2026-03-31 14:08:45', '2026-03-31', 0, 24.56965880, 46.61414350, 15.43, '2a02:9b0:ee:e9df:6103:de2c:3c21:e86c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:08:45'),
(267, 24, '', '2026-03-31 14:09:13', '2026-03-31', 0, 24.56965990, 46.61417350, 13.50, '2a02:9b0:ee:e9df:6103:de2c:3c21:e86c', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 11:09:13'),
(268, 47, 'out', '2026-03-31 15:16:37', '2026-03-31', 0, 24.56963170, 46.61425830, 2.47, '2a02:9b0:ee:e9df:4d67:a64:db5:6d19', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 12:16:37'),
(269, 47, '', '2026-03-31 15:16:45', '2026-03-31', 0, 24.56955500, 46.61430500, 4.57, '2a02:9b0:ee:e9df:4d67:a64:db5:6d19', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 12:16:45'),
(270, 55, 'in', '2026-03-31 18:06:49', '2026-03-31', 127, 24.57232580, 46.60266030, 15.96, '2a02:9b0:f2:982e:f914:b875:2a61:9478', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 15:06:49'),
(271, 4, 'in', '2026-03-31 18:08:51', '2026-03-31', 129, 24.57235570, 46.60267230, 15.79, '2a02:9b0:f2:982e:53d9:4c0e:97ea:b29a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 15:08:51'),
(272, 1, 'out', '2026-03-31 22:02:21', '2026-03-31', 0, 24.57211670, 46.60266800, 100.00, '176.17.210.99', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-03-31 19:02:21'),
(273, 38, 'in', '2026-04-01 07:45:27', '2026-04-01', 0, 24.56621330, 46.62179050, 36.31, '2a02:9b0:b3:dbca:67a6:332a:b786:356e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:45:27'),
(274, 41, 'in', '2026-04-01 07:46:56', '2026-04-01', 0, 24.56972200, 46.61456260, 5.80, '2a02:9b0:23:dbe8:3518:e3be:f7b0:298c', 'Mozilla/5.0 (Linux; Android 15; Redmi 13C Build/AP3A.240905.015.A2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.52.2-gn', NULL, '2026-04-01 04:46:56'),
(275, 35, 'in', '2026-04-01 07:50:04', '2026-04-01', 0, 24.56624570, 46.62175370, 20.00, '37.42.53.247', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:50:04'),
(276, 30, 'in', '2026-04-01 07:50:22', '2026-04-01', 0, 24.56625143, 46.62157674, 7.45, '2a02:9b0:b3:dbca:ccd7:8ce3:7c6e:8c9d', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-04-01 04:50:22'),
(277, 73, 'in', '2026-04-01 07:52:08', '2026-04-01', 0, 24.56969110, 46.61422410, 10.70, '2a02:9b0:23:dbe8:ef65:f841:1128:bc73', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:52:08'),
(278, 4, 'in', '2026-04-01 07:52:25', '2026-04-01', 0, 24.57237750, 46.60266730, 15.51, '2a02:9b0:27:140e:7503:bea8:21ee:d7cc', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:52:25'),
(279, 34, 'in', '2026-04-01 07:53:23', '2026-04-01', 0, 24.56619480, 46.62178280, 15.85, '37.42.53.247', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:53:23'),
(280, 52, 'in', '2026-04-01 07:56:28', '2026-04-01', 0, 24.57288530, 46.60317970, 100.00, '2a02:cb80:4270:4ab:2583:aed7:a927:64f6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:56:28'),
(281, 72, 'in', '2026-04-01 07:56:59', '2026-04-01', 0, 24.56960590, 46.61431390, 8.82, '2a02:9b0:23:dbe8:5988:a15c:781e:7bbe', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:56:59'),
(282, 51, 'in', '2026-04-01 07:59:00', '2026-04-01', 0, 24.56969890, 46.61432920, 4.72, '2a02:9b0:23:dbe8:5552:9b8d:abad:458e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 04:59:00'),
(283, 17, 'in', '2026-04-01 08:00:02', '2026-04-01', 0, 24.56969870, 46.61427810, 12.33, '2a02:9b0:23:dbe8:3480:63fa:ccf:e37a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:00:02'),
(284, 31, 'in', '2026-04-01 08:00:14', '2026-04-01', 0, 24.57279000, 46.60311170, 7.60, '176.17.210.99', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:00:14'),
(285, 12, 'in', '2026-04-01 08:01:16', '2026-04-01', 0, 24.56978670, 46.61419150, 21.98, '2a02:9b0:23:dbe8:40e2:b471:837d:14f0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:01:16'),
(286, 46, 'in', '2026-04-01 08:01:16', '2026-04-01', 0, 24.56955520, 46.61465000, 3.79, '2001:16a4:83:fc92:ddd8:3b1f:60bd:19a0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:01:16'),
(287, 26, 'in', '2026-04-01 08:01:50', '2026-04-01', 0, 24.57229404, 46.60261047, 19.83, '2a02:9b0:27:140e:b86d:2c2d:145b:f21', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-04-01 05:01:50'),
(288, 74, 'in', '2026-04-01 08:01:59', '2026-04-01', 0, 24.56970340, 46.61425880, 13.73, '2001:16a4:34:ae6d:2:2:6309:297a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:01:59'),
(289, 24, 'in', '2026-04-01 08:02:15', '2026-04-01', 0, 24.56976240, 46.61408400, 24.44, '2a02:9b0:23:dbe8:10cf:353:c6a9:7140', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:02:15'),
(290, 47, 'in', '2026-04-01 08:02:24', '2026-04-01', 0, 24.56956740, 46.61423830, 21.10, '2a02:9b0:23:dbe8:4c30:4e7:f8fe:2208', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:02:24'),
(291, 10, 'in', '2026-04-01 08:03:06', '2026-04-01', 0, 24.56970040, 46.61433030, 14.38, '2a02:9b0:23:dbe8:5a13:f2f8:c751:1699', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:03:06'),
(292, 71, 'in', '2026-04-01 08:03:45', '2026-04-01', 0, 24.56618660, 46.62179880, 20.56, '2a02:9b0:b3:dbca:f907:8644:9096:b995', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:03:45'),
(293, 44, 'in', '2026-04-01 08:04:41', '2026-04-01', 0, 24.56953137, 46.61469701, 7.00, '2001:16a4:83:71d7:4c2a:c03b:3fe6:70bd', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', NULL, '2026-04-01 05:04:41'),
(294, 16, 'in', '2026-04-01 08:06:17', '2026-04-01', 0, 24.56966210, 46.61426080, 15.49, '2a02:9b0:23:dbe8:5b27:af4e:2ce4:a7a', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:06:17'),
(295, 7, 'in', '2026-04-01 08:08:39', '2026-04-01', 0, 24.57252460, 46.60290920, 30.31, '2001:16a4:55:ce5a:18a1:dbd5:cf02:8ab0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:08:39'),
(296, 3, 'in', '2026-04-01 08:09:23', '2026-04-01', 0, 24.57236090, 46.60268000, 11.71, '2a09:bac5:3215:2541::3b6:1d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:09:23'),
(297, 29, 'in', '2026-04-01 08:09:56', '2026-04-01', 0, 24.56620520, 46.62179190, 15.03, '2a02:9b0:b3:dbca:8c5f:ed72:f1d3:2ac7', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:09:56'),
(298, 43, 'in', '2026-04-01 08:10:45', '2026-04-01', 1, 24.56966320, 46.61432490, 1.70, '2001:16a2:c07d:84db:1:2:8ef1:e99d', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:10:45'),
(299, 28, 'in', '2026-04-01 08:11:30', '2026-04-01', 2, 24.57243760, 46.60273800, 18.34, '2a02:9b0:27:140e:1828:c6f8:37be:fb1b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:11:30'),
(300, 49, 'in', '2026-04-01 08:14:06', '2026-04-01', 4, 24.56968950, 46.61432530, 11.20, '2a02:9b0:23:dbe8:68db:7eb9:8a6c:74a0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:14:06'),
(301, 11, 'in', '2026-04-01 08:16:19', '2026-04-01', 6, 24.56964990, 46.61409560, 15.38, '2a02:9b0:23:dbe8:2521:d2ee:7fc5:bee1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:16:19'),
(302, 36, 'in', '2026-04-01 08:16:30', '2026-04-01', 7, 24.57657658, 46.60005360, 999.99, '176.17.210.99', 'Mozilla/5.0 (Linux; Android 14; en; Infinix X6532 Build/SP1A.210812.016) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.7680.164 HiBrowser/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS/ Mobile Safari/537.36', NULL, '2026-04-01 05:16:30'),
(303, 55, 'in', '2026-04-01 08:16:42', '2026-04-01', 7, 24.57238730, 46.60269110, 20.01, '2a02:9b0:27:140e:3de0:f1b4:70b3:782', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:16:42'),
(304, 70, 'in', '2026-04-01 08:17:22', '2026-04-01', 7, 24.57238770, 46.60269250, 15.02, '2a02:9b0:27:140e:589b:f029:e1b9:d126', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:17:22'),
(305, 40, 'in', '2026-04-01 08:18:33', '2026-04-01', 9, 24.57248210, 46.60306260, 25.22, '176.17.210.99', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:18:33'),
(306, 53, 'in', '2026-04-01 08:19:39', '2026-04-01', 10, 24.57235890, 46.60266420, 26.53, '2a02:9b0:27:140e:8d59:e408:2537:2a33', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:19:39'),
(307, 67, 'in', '2026-04-01 08:26:45', '2026-04-01', 17, 24.57237040, 46.60269290, 11.48, '2a02:9b0:27:140e:b8c7:a814:d79f:2b17', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:26:45'),
(308, 48, 'in', '2026-04-01 08:30:18', '2026-04-01', 20, 24.56957460, 46.61422240, 5.56, '109.83.101.200', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', NULL, '2026-04-01 05:30:18');

-- --------------------------------------------------------

--
-- بنية الجدول `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `audit_log`
--

INSERT INTO `audit_log` (`id`, `admin_id`, `action`, `details`, `target_id`, `ip_address`, `created_at`) VALUES
(28, 1, 'reset_launch', 'تم تصفير جميع السجلات — إعادة إطلاق التطبيق', NULL, '37.217.240.218', '2026-03-17 20:23:17'),
(29, 1, 'login', 'تسجيل دخول ناجح من IP: 37.217.240.218', NULL, '37.217.240.218', '2026-03-17 20:29:00'),
(30, 1, 'login', 'تسجيل دخول ناجح من IP: 37.217.240.218', NULL, '37.217.240.218', '2026-03-17 20:42:41'),
(31, 1, 'login', 'تسجيل دخول ناجح من IP: 37.217.240.218', NULL, '37.217.240.218', '2026-03-17 21:57:01'),
(32, 1, 'delete_attendance', 'حذف سجل حضور ID=19', 19, '37.217.240.218', '2026-03-17 21:57:30'),
(33, 1, 'login', 'تسجيل دخول ناجح من IP: 37.217.240.218', NULL, '37.217.240.218', '2026-03-17 22:24:40'),
(34, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:26:1087:f06e:aaec:d8c3:2313', NULL, '2a02:9b0:26:1087:f06e:aaec:d8c3:2313', '2026-03-17 22:56:43'),
(35, 1, 'add_employee', 'إضافة موظف: ابو بشير', 67, '2a02:9b0:26:1087:f06e:aaec:d8c3:2313', '2026-03-17 22:58:18'),
(36, 1, 'edit_employee', 'تعديل موظف: عبدالله اليمني', 26, '2a02:9b0:26:1087:f06e:aaec:d8c3:2313', '2026-03-17 23:01:16'),
(37, 1, 'edit_employee', 'تعديل موظف: حسني', 2, '2a02:9b0:26:1087:f06e:aaec:d8c3:2313', '2026-03-17 23:05:02'),
(38, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-18 03:07:25'),
(39, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-18 03:07:53'),
(40, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-18 12:08:41'),
(41, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-18 14:34:05'),
(42, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-18 21:14:35'),
(43, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:3c:931b:3dc3:6b23:aecb:1dc8', NULL, '2a02:9b0:3c:931b:3dc3:6b23:aecb:1dc8', '2026-03-18 21:55:27'),
(44, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-19 00:38:09'),
(45, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-21 16:51:14'),
(46, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-21 18:16:30'),
(47, 1, 'add_employee', 'إضافة موظف: ياسر', 68, '109.82.228.137', '2026-03-23 16:02:13'),
(48, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-23 16:22:19'),
(49, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', NULL, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:07:30'),
(50, 1, 'add_employee', 'إضافة موظف: عبدالفتاح شعبان', 69, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:12:08'),
(51, 1, 'edit_employee', 'تعديل موظف: عبدالله احمد', 52, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:12:39'),
(52, 1, 'delete_attendance', 'حذف سجل حضور ID=42', 42, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:13:50'),
(53, 1, 'edit_employee', 'تعديل موظف: عبدالله احمد', 52, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:14:11'),
(54, 1, 'delete_attendance', 'حذف سجل حضور ID=41', 41, '2a02:9b0:40:e04d:c56d:7bf5:8e6e:4f5e', '2026-03-24 00:15:57'),
(55, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 01:50:59'),
(56, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 01:58:21'),
(57, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 06:07:38'),
(58, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 07:24:18'),
(59, 1, 'edit_employee', 'تعديل موظف: شعبان', 38, '2a02:9b0:3c:931b:a1ae:fc26:e47f:ac4c', '2026-03-24 08:02:15'),
(60, 1, 'edit_employee', 'تعديل موظف: حبيب', 28, '2a02:9b0:3c:931b:a1ae:fc26:e47f:ac4c', '2026-03-24 08:12:26'),
(61, 1, 'add_employee', 'إضافة موظف: شاهد', 70, '2a02:9b0:3c:931b:a1ae:fc26:e47f:ac4c', '2026-03-24 08:14:46'),
(62, 1, 'edit_employee', 'تعديل موظف: عنايات', 36, '109.82.228.137', '2026-03-24 08:21:20'),
(63, 1, 'add_employee', 'إضافة موظف: تعظيم', 71, '2a02:9b0:3c:931b:a1ae:fc26:e47f:ac4c', '2026-03-24 08:38:38'),
(64, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:3c:931b:5d47:10c1:59ac:7c86', NULL, '2a02:9b0:3c:931b:5d47:10c1:59ac:7c86', '2026-03-24 13:15:26'),
(65, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 15:56:30'),
(66, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-24 16:38:37'),
(67, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-25 10:18:36'),
(68, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:3c:931b:87a8:d6ff:b893:15f5', NULL, '2a02:9b0:3c:931b:87a8:d6ff:b893:15f5', '2026-03-25 22:42:53'),
(69, 1, 'login', 'تسجيل دخول ناجح من IP: 109.82.228.137', NULL, '109.82.228.137', '2026-03-28 08:57:24'),
(70, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:f2:982e:503b:64d2:b566:8f14', NULL, '2a02:9b0:f2:982e:503b:64d2:b566:8f14', '2026-03-29 08:29:52'),
(71, 1, 'login', 'تسجيل دخول ناجح من IP: 37.125.108.77', NULL, '37.125.108.77', '2026-03-30 07:57:40'),
(72, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:ee:e9df:59bb:5c22:c58a:7433', NULL, '2a02:9b0:ee:e9df:59bb:5c22:c58a:7433', '2026-03-30 08:05:30'),
(73, 1, 'add_employee', 'إضافة موظف: nykoon', 72, '2a02:9b0:ee:e9df:58a3:57a:c210:42a7', '2026-03-30 08:58:38'),
(74, 1, 'add_employee', 'إضافة موظف: tirachot', 73, '2a02:9b0:ee:e9df:58a3:57a:c210:42a7', '2026-03-30 09:04:34'),
(75, 1, 'add_employee', 'إضافة موظف: kittiphong', 74, '2a02:9b0:ee:e9df:58a3:57a:c210:42a7', '2026-03-30 09:06:49'),
(76, 1, 'edit_employee', 'تعديل موظف: محمد بلال', 11, '2a02:9b0:ee:e9df:58a3:57a:c210:42a7', '2026-03-30 09:08:22'),
(77, 1, 'login', 'تسجيل دخول ناجح من IP: 37.125.238.175', NULL, '37.125.238.175', '2026-03-31 07:24:52'),
(78, 1, 'login', 'تسجيل دخول ناجح من IP: 37.125.238.175', NULL, '37.125.238.175', '2026-03-31 08:17:38'),
(79, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:23:dbe8:d531:213:af9d:1765', NULL, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 08:21:27'),
(80, 1, 'delete_employee', 'أرشفة موظف ID=37', 37, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 08:21:55'),
(81, 1, 'delete_employee', 'أرشفة موظف ID=45', 45, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 08:44:20'),
(82, 1, 'edit_employee', 'تعديل موظف: معتز', 42, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 08:44:35'),
(83, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:23:dbe8:d531:213:af9d:1765', NULL, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 09:33:44'),
(84, 1, 'login', 'تسجيل دخول ناجح من IP: 2a02:9b0:23:dbe8:d531:213:af9d:1765', NULL, '2a02:9b0:23:dbe8:d531:213:af9d:1765', '2026-04-01 10:30:18');

-- --------------------------------------------------------

--
-- بنية الجدول `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL DEFAULT 24.57230700,
  `longitude` decimal(11,8) NOT NULL DEFAULT 46.60255200,
  `geofence_radius` int(11) NOT NULL DEFAULT 500,
  `allow_overtime` tinyint(1) NOT NULL DEFAULT 1,
  `overtime_start_after` int(11) NOT NULL DEFAULT 60,
  `overtime_min_duration` int(11) NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `branches`
--

INSERT INTO `branches` (`id`, `name`, `latitude`, `longitude`, `geofence_radius`, `allow_overtime`, `overtime_start_after`, `overtime_min_duration`, `is_active`, `created_at`) VALUES
(1, 'صرح الاوروبي', 24.57221880, 46.60263032, 25, 1, 60, 30, 1, '2026-03-15 17:52:11'),
(2, 'صرح الرئيسي', 24.57232369, 46.60283696, 25, 1, 60, 30, 1, '2026-03-15 17:52:11'),
(3, 'فضاء 1', 24.56963799, 46.61411006, 25, 1, 60, 30, 1, '2026-03-15 17:52:11'),
(4, 'فضاء 2', 24.56613423, 46.62163443, 25, 1, 60, 30, 1, '2026-03-15 17:52:11'),
(5, 'صرح الامريكي', 24.57242126, 46.60304591, 2522, 1, 60, 30, 1, '2026-03-15 17:52:11'),
(6, 'الدهانات والبوية + موبار', 24.56951846, 46.61446154, 25, 1, 60, 30, 1, '2026-03-15 17:59:52');

-- --------------------------------------------------------

--
-- بنية الجدول `branch_shifts`
--

CREATE TABLE `branch_shifts` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `shift_number` tinyint(4) NOT NULL DEFAULT 1,
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `branch_shifts`
--

INSERT INTO `branch_shifts` (`id`, `branch_id`, `shift_number`, `shift_start`, `shift_end`, `is_active`, `created_at`) VALUES
(1, 6, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(2, 5, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(3, 1, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(4, 2, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(5, 3, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(6, 4, 1, '08:10:00', '12:00:00', 1, '2026-03-17 18:56:15'),
(7, 5, 2, '16:00:00', '22:00:00', 1, '2026-03-17 18:58:35'),
(8, 1, 2, '16:00:00', '22:00:00', 1, '2026-03-17 18:59:24'),
(9, 2, 2, '16:00:00', '22:00:00', 1, '2026-03-17 19:00:13'),
(10, 4, 2, '14:00:00', '20:00:00', 1, '2026-03-23 21:09:48');

-- --------------------------------------------------------

--
-- بنية الجدول `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `pin` varchar(10) NOT NULL,
  `pin_changed_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `unique_token` varchar(64) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `device_fingerprint` varchar(64) DEFAULT NULL,
  `device_registered_at` timestamp NULL DEFAULT NULL,
  `device_bind_mode` tinyint(1) NOT NULL DEFAULT 0,
  `security_level` int(11) DEFAULT 2,
  `is_active` tinyint(1) DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `employees`
--

INSERT INTO `employees` (`id`, `name`, `job_title`, `pin`, `pin_changed_at`, `phone`, `profile_photo`, `unique_token`, `branch_id`, `device_fingerprint`, `device_registered_at`, `device_bind_mode`, `security_level`, `is_active`, `deleted_at`, `created_at`) VALUES
(1, 'إسلام', 'موظف', '0672', NULL, '+966549820672', 'profiles/1/photo.jpg', '7fea66411688e9a4bbf19fbd7af426baf816bd6c7f244c669bcfc71442ae2d2a', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(2, 'حسني', 'موظف', '1699', NULL, '+966537491699', NULL, 'ee27b7f4601e7598fdba40e8f92225852be1a2fc86b212e46d600622e8bc4404', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(3, 'بخاري', 'موظف', '4018', NULL, '+923095734018', 'profiles/3/photo.jpg', 'ef27eefd9c716d0770fb0bf44870226aa4f2787ad20342a1e5be4ec12eac47ca', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(4, 'أبو سليمان', 'موظف', '1865', NULL, '+966500651865', NULL, 'c15c72a38084dbdfb989ef92b14db9e9053b6ddafc1585107861dfe50c46bc8d', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(5, 'صابر', 'موظف', '9595', NULL, '+966570899595', NULL, '8e1a38b299dbd67e47dce818f86790e4bf7c9f93a0aba2d5350ba330f3f397ec', 1, NULL, NULL, 0, 2, 0, NULL, '2026-03-15 17:52:11'),
(6, 'زاهر', 'موظف', '1759', NULL, '+966546481759', NULL, '4005b9b6be406fa9720c4ca019d68e64a4e6733642c69e54b8e8acd9a2aac9ab', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(7, 'أيمن', 'موظف', '0870', NULL, '+966555090870', NULL, 'cd1979fe11f4742a3fcc31dd549c729144bcd41501a3adfabf7732c8077d4a56', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(8, 'أمجد', 'موظف', '6370', NULL, '+966555106370', NULL, '75fbac67985d0c7738be029af6e4fcb430673e389265cb3e3ba942fff02f6107', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(9, 'نجيب', 'موظف', '4157', NULL, '+923475914157', NULL, '2f5a2a6cd24d6345564e94e6127f62cdc442c8169bc8f1d848f386b334cb100a', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(10, 'محمد جلال', 'موظف', '3727', NULL, '+966573603727', NULL, '3b9f53c607b6d9d8f943a67e2913fbe4a170652aec9802c40e3537aa520c7b89', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(11, 'محمد بلال', 'موظف', '3694', NULL, '+966503863695', NULL, '850cecdc86c41b50881d3eaa9fcd7131d13d06a82baf302bf4d80546152e63cf', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(12, 'رمضان عباس علي', 'موظف', '9151', NULL, '+966594119151', NULL, '6ba4512375c49cf9e22eab5a88ac72e75216254d282bfd22483d0a8f3a434914', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(13, 'محمد أفريدي', 'موظف', '2089', NULL, '+966565722089', NULL, 'bb3bb294870f66b470c9fc38ac6417c8bc44fd36261545c96d05520c867c11bd', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(14, 'سلفادور ديلا', 'موظف', '6875', NULL, '+966541756875', NULL, '64bbdb6dac0856f7bbf18c11eb56238422404347a745d2aba4cc2a71d1c72bbe', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(15, 'محمد خان', 'موظف', '3035', NULL, '+966594163035', NULL, '3560ffaf38c1d24092040d942e793af07c89f3f1d42c12fc8f947099858a7a32', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(16, 'أندريس بورتس', 'موظف', '7140', NULL, '+966590087140', NULL, '93d94bd07735044302332846fbad10d483e2e94eb5aab1f4c85951b82e382869', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(17, 'حسن (آصف)', 'موظف', '0736', NULL, '+966582670736', NULL, 'd5602cafb20e3fbff9c3e96d35924fa9451caf864b4d45a572fb03e34b849f47', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(18, 'رمضان أويس علي', 'موظف', '6640', NULL, '+966531096640', NULL, '8d30cba84ecbd738f14c53b41010c99fd469fb050e467d2e28bf61df35fd338c', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(19, 'ساكدا بندولا', 'موظف', '6930', NULL, '+966572746930', NULL, '356dab1c7b91b59062ff89d117ac6a23d824a2f33b5e13b2d8fedc262308cc0c', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(20, 'شحاتة', 'موظف', '7065', NULL, '+966545677065', NULL, 'eeedb4dab36f5c8ac0c969fb71f4c5ddbbc3139cb8027129ead75f644d15b391', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(21, 'منذر محمد', 'موظف', '3723', NULL, '+966556593723', NULL, '49a5ce528c8c5086549975a61b56a842f0bb9156f7c9582025fe905144ccdad2', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(22, 'مصطفى عوض سعد', 'موظف', '3900', NULL, '+966555106370', NULL, '233e1ce53dbd2d2c9e421a99e6180bc74078e793e5d4d8785a460665beaa6b2e', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(23, 'عنايات', 'موظف', '9361', NULL, '+966582329361', NULL, 'a5ac0ef7f30402a709ffd1e45512744e4b04ffb712b6fb76c52190a993535c75', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(24, 'محمد خميس', 'موظف', '4390', NULL, '+966153254390', NULL, '338f650f17cfa41f25b2304784465aad524647b88a52b7213bf1e6023d23e819', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(25, 'عبد الهادي يونس', 'موظف', '6196', NULL, '+966159626196', NULL, '7493615993614052460a6e73e09b8390a61fedbf734378311bb0c05f31060326', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(26, 'عبدالله اليمني', 'موظف', '5655', NULL, '+966536765655', 'profiles/26/photo.jpg', '4d9e4c3823d0e0657794005ef75f599043415f0f0aa337ca79df449b8fa07511', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(27, 'أفضل', 'موظف', '8117', NULL, '+966599258117', NULL, '660f11fede99e20075505aad3bb9a08baf7f02732e64e2ddb65e39add6cba133', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(28, 'حبيب', 'موظف', '3203', NULL, '+966573263203', NULL, '2a0a17d242982258d6dc6ef70fe9453404d5aa4919582b836d65f3dad13643c2', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(29, 'إمتي', 'موظف', '6604', NULL, '+966595806604', NULL, 'b52ae6c42df756dd62f362f7b5339e0a443558937b8618aefbd3e03b9f78c880', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(30, 'عرنوس', 'موظف', '9178', NULL, '+966500089178', NULL, 'b050e2b75a96ba8296a9d4a9266b0b2825b979a9f608991a7ec804087558aafa', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(31, 'عرفان', 'موظف', '5093', NULL, '+966597255093', NULL, '30ec88cd33048e6e728567e082b6c64aa1ec819b6d06eeff2604f6803e807940', 5, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(32, 'وسيم', 'موظف', '6242', NULL, '+966531806242', NULL, 'e99864adaa41c2b4eee34112dd13fb487b062184d652c3cfc78a5ef1f720bd99', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(33, 'جهاد', 'موظف', '2355', NULL, '+966508512355', NULL, '23a9df0ad182ec64a01200e5b32590ff5de9d47b9fa8f7842f2be3e61b8ccdec', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(34, 'ابانوب', 'موظف', '1886', NULL, '+966536781886', NULL, '41ed9715d93e698bf2a2059c6b65f263bb46872fcba7dacc8c1141346bf0051d', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(35, 'قتيبة', 'موظف', '4453', NULL, '+966597024453', NULL, '02a425d6b57fab9fd6ac9675e8c62f07b147237a1c38250bb3b28b170c88fe28', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(36, 'عنايات', 'موظف', '1401', NULL, '+966571761401', NULL, 'a3553771498e7aeca69da7b82633bdf3f3ae9042bc3e38f60c8d9cf8c02f5362', 5, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(37, 'وقاص', 'موظف', '7295', NULL, '+966598997295', NULL, '5614b3a849f569d48148348fa0121ffe2a7b15b1cefbd1e08d70b29e32e9f0c4', 5, NULL, NULL, 0, 2, 0, '2026-04-01 05:21:55', '2026-03-15 17:52:11'),
(38, 'شعبان', 'موظف', '3544', NULL, '+966595153544', NULL, '756ee927e87947bb722fab323e5185724f6d5f8aa66690843b68c87338c1c419', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(39, 'مصعب', 'موظف', '2273', NULL, '+966555792273', NULL, 'afc1e8a251a87fc9ff7a240afe544762e5cf13839c9d7ac9d81ba691ef42ff0e', 5, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(40, 'بلال', 'موظف', '4009', NULL, '+966594154009', NULL, '3e27d53f193f52e70a042c8d4418e99e99e8651ae4304aca17bb8819b4f1145a', 5, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 17:52:11'),
(41, 'هيثم', 'موظف', '0542', NULL, '966551400542', NULL, 'b5ba0bf3414ff21140560c59b7507f8b', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(42, 'معتز', 'موظف', '5938', NULL, '9660475938', NULL, 'e807566edaad80fafd48bdcab5bfe01d', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(43, 'صهيب', 'موظف', '9975', NULL, '966558109975', NULL, 'f5c3e78fb366d8733ba6c9a7aa5d3181', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(44, 'خيري', 'موظف', '5401', NULL, '966535115401', NULL, '22ac7bc6159cd27a9847c5093b65a9c5', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(45, 'عبدو بوية', 'موظف', '1820', NULL, '966549601820', NULL, '93b5700bed33b468818ea2fa01b98904', 6, NULL, NULL, 0, 2, 0, '2026-04-01 05:44:20', '2026-03-15 18:00:22'),
(46, 'احمد', 'موظف', '2267', NULL, '966558602267', NULL, 'c841d9675b008df90d2d345a284b7781', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(47, 'حسن', 'موظف', '5050', NULL, '2614655050', NULL, '48047e57fe4ca808b528f34ed04c8f3a', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(48, 'ابو حازم', 'موظف', '7593', NULL, '966560077593', NULL, '8857ca75c0223e9173741822ba2402f3', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(49, 'ابو يحيى', 'موظف', '7631', NULL, '966500047631', NULL, 'a547d496ad9a049c3fd8cafca688a39c', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(50, 'حمادة', 'موظف', '1407', NULL, '966560671407', NULL, '0bcaa4eb487a27a0bb8b889f755f2113', 6, NULL, NULL, 0, 2, 0, NULL, '2026-03-15 18:00:22'),
(51, 'ابراهيم', 'موظف', '3427', NULL, '966508873427', NULL, 'e2a4cabf28c89db6e8ce8801fff0a295', 6, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:00:22'),
(52, 'عبدالله احمد', 'كهربائي', '8146', NULL, '966578448146', 'profiles/52/photo.jpg', '53934016221df44e7467ba7212ae78a9b275fdc255fbf2984aab64a09397f724', 5, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 18:04:52'),
(53, 'وسيم', 'كهربائي', '1153', NULL, '966533891153', 'profiles/53/photo.jpg', '2cf1c26c58431dc286d8aee58b904dd74dc22ecb1755ff69986460ec6838d644', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-15 19:10:46'),
(54, 'محسن', 'جيرات', '2769', NULL, '966537491699', NULL, '01ad15befc8858bab0cee71c09310744f12ce4d421105a484f77f279a396e1ff', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-16 15:58:52'),
(55, 'احمد عبدالسلام', 'محاسب', '0707', NULL, '966576160707', 'profiles/55/photo.jpg', '0f12b111252c586d8f1b6ddbcd1b06ecf9d012ec565e7b179778c994d8c3f109', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-16 17:15:48'),
(67, 'ابو بشير', 'العم', '7870', NULL, '966591817870', 'profiles/67/photo.jpg', '53d7395a9dd8ab41ce5cc62dca2130bdd106ae393ded86ecd5f344859572f80b', 1, NULL, NULL, 0, 2, 1, NULL, '2026-03-17 19:58:18'),
(68, 'ياسر', 'ميكانيك', '2621', NULL, '96654180', NULL, 'a2f5deabed95a86688ae9eb9603d6fe608574e18e545c6638f2a45daa0bc8bed', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-23 13:02:13'),
(69, 'عبدالفتاح شعبان', 'ميكانيكي', '5558', NULL, '966567475558', NULL, '2ef24d4e7a841ee59c5af6258a6f3002ac654d6e4c7fc9ac1d817e639ab793a6', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-23 21:12:08'),
(70, 'شاهد', 'كهرباء', '5458', NULL, '966578225458', NULL, '222c92cde9407e6e88548e8c1b67152cba9c0ffdd2597b52679f5a77bc708239', 2, NULL, NULL, 0, 2, 1, NULL, '2026-03-24 05:14:46'),
(71, 'تعظيم', 'ميكانيكي', '3225', NULL, '966567413225', NULL, '866e41cc7ba8909247a32c590efbc90a813e9bad00fc29f2b96b1e6a61220301', 4, NULL, NULL, 0, 2, 1, NULL, '2026-03-24 05:38:38'),
(72, 'nykoon', 'سمكرة', '3312', NULL, '966570643312', NULL, '3d23de496be9eb3a2bd6113f949b6d1935efd435a5075f6c6d1bf452dba2bccd', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-30 05:58:38'),
(73, 'tirachot', 'سمكرة', '4498', NULL, '966538634498', NULL, 'fb8f6e135d8b82af08c584a042be1b4a134bb7806da49e93bbaf95fe928605ec', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-30 06:04:34'),
(74, 'kittiphong', 'سمكرة', '8033', NULL, '966538728033', NULL, 'db39637e5ee724e1036b930bba33e845a8e183d34fda491c0cea5e8473dcb096', 3, NULL, NULL, 0, 2, 1, NULL, '2026-03-30 06:06:49');

-- --------------------------------------------------------

--
-- بنية الجدول `emp_document_files`
--

CREATE TABLE `emp_document_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','pdf') NOT NULL DEFAULT 'image',
  `original_name` varchar(255) NOT NULL DEFAULT '',
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `emp_document_files`
--

INSERT INTO `emp_document_files` (`id`, `group_id`, `file_path`, `file_type`, `original_name`, `file_size`, `sort_order`, `created_at`) VALUES
(1, 1, 'profiles/52/docs/1/58765704f9e832b4.jpg', 'image', 'WhatsApp Image 2026-03-18 at 9.17.46 PM 1.jpeg', 77329, 0, '2026-03-18 21:22:13'),
(2, 2, 'profiles/55/docs/2/f8b6c21fc6311f2b.jpg', 'image', 'WhatsApp Image 12026-03-18 at 10.01.23 PM.jpeg', 133273, 0, '2026-03-18 22:02:55'),
(3, 3, 'profiles/26/docs/3/0230aef5efa4af21.jpg', 'image', 'WhatsApp Image 2026-03-18 at 10.06.26 PM.jpeg', 207824, 0, '2026-03-18 22:09:01'),
(4, 4, 'profiles/53/docs/4/eedc14802a4e29a3.jpg', 'image', 'WhatsApp Image 2026-03-18 at 10.20.31 PM.jpeg', 552693, 0, '2026-03-18 22:21:21'),
(5, 5, 'profiles/67/docs/5/1a9f668e1f3dac40.jpg', 'image', 'WhatsApp Image 2026-03-18 at 10.26.17 PM.jpeg', 94313, 0, '2026-03-18 22:33:30'),
(6, 6, 'profiles/3/docs/6/f2e08901bf07e6df.jpg', 'image', 'WhatsApp Image 2026-03-18 at 10.31.06 PM.jpeg', 127113, 0, '2026-03-18 22:36:49');

-- --------------------------------------------------------

--
-- بنية الجدول `emp_document_groups`
--

CREATE TABLE `emp_document_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `group_name` varchar(200) NOT NULL DEFAULT '',
  `expiry_date` date NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `emp_document_groups`
--

INSERT INTO `emp_document_groups` (`id`, `employee_id`, `group_name`, `expiry_date`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 52, 'زيارة', '2026-04-25', 0, '2026-03-18 21:21:31', '2026-03-24 12:43:44'),
(2, 55, 'اقامة', '2027-01-25', 0, '2026-03-18 22:02:16', '2026-03-18 22:03:42'),
(3, 26, 'اقامة', '2026-05-05', 0, '2026-03-18 22:08:47', '2026-03-18 22:11:30'),
(4, 53, 'اقامة', '2026-06-06', 0, '2026-03-18 22:21:07', '2026-03-18 22:21:29'),
(5, 67, 'اقامة', '2027-03-18', 0, '2026-03-18 22:33:17', '2026-03-18 22:33:23'),
(6, 3, 'اقامة', '2026-05-11', 0, '2026-03-18 22:36:35', '2026-03-18 22:37:34');

-- --------------------------------------------------------

--
-- بنية الجدول `known_devices`
--

CREATE TABLE `known_devices` (
  `id` int(11) NOT NULL,
  `fingerprint` varchar(64) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 1,
  `first_used_at` timestamp NULL DEFAULT current_timestamp(),
  `last_used_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `known_devices`
--

INSERT INTO `known_devices` (`id`, `fingerprint`, `employee_id`, `usage_count`, `first_used_at`, `last_used_at`) VALUES
(192, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 52, 42, '2026-03-17 17:27:31', '2026-04-01 04:56:27'),
(193, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 52, 2, '2026-03-17 17:27:35', '2026-04-01 04:56:31'),
(194, 'd42729ff440c90b4be214c8071a76605d3e0043856e0294edac6f75fda9cb1fb', 52, 1, '2026-03-17 17:43:01', '2026-03-17 17:43:01'),
(203, 'd86e51b3dbe28ee557c0fd9d575f204bb1efc7a323934490ed53162bffc4a8cb', 47, 29, '2026-03-17 19:34:29', '2026-03-31 12:15:39'),
(204, 'e3672a6662cf1fe4f7617854fefae824d309498143458ace37aed07115005a7e', 43, 22, '2026-03-17 19:34:34', '2026-04-01 05:10:47'),
(205, 'a3373c4606819ca0237ccc1e9ce9ccf9fcb9ba2088c68dd2a787e01cb7d23218', 44, 23, '2026-03-17 19:35:17', '2026-04-01 05:04:43'),
(214, '31b912084ab2c717a07523f18f67d213e20f2be1fea223ebb2a40bb7587bd74c', 55, 23, '2026-03-17 19:54:31', '2026-04-01 05:16:45'),
(216, '184428af8b6d9227ad35a50f8c06bc3d57719271352c1a252abdcb95f5467ab6', 67, 32, '2026-03-17 19:59:16', '2026-04-01 05:26:48'),
(220, 'a3373c4606819ca0237ccc1e9ce9ccf9fcb9ba2088c68dd2a787e01cb7d23218', 26, 45, '2026-03-17 20:01:44', '2026-04-01 05:02:05'),
(226, '18246fc37e1cce5a8aa58503df17c05236b9eb6a55473f2080918a0573be1f14', 1, 34, '2026-03-17 20:03:29', '2026-03-31 19:02:25'),
(229, 'f869a1c7464379628ef341cf45b5565518ebc04d3832c72d3b6f679a9d62a540', 7, 16, '2026-03-17 20:20:05', '2026-04-01 05:08:42'),
(232, '1d25109eda306e691dcd614e6740a2553cee554a5a1cff47f0a0d5de94895af3', 52, 2, '2026-03-17 20:23:04', '2026-03-17 20:24:48'),
(234, 'a3373c4606819ca0237ccc1e9ce9ccf9fcb9ba2088c68dd2a787e01cb7d23218', 8, 2, '2026-03-17 20:26:21', '2026-03-17 20:26:26'),
(236, 'f5709defcee2ea1f4d2b089704cf830b8e4b810e20c762dadb5efedf7eaf8c7f', 9, 1, '2026-03-17 21:15:39', '2026-03-17 21:15:39'),
(237, '6e2b965c15f76f711c201a1e5bc0ab53f4199f1a09ccdcec5de10732bd207f30', 3, 24, '2026-03-17 22:14:24', '2026-04-01 05:09:25'),
(244, 'd86e51b3dbe28ee557c0fd9d575f204bb1efc7a323934490ed53162bffc4a8cb', 44, 4, '2026-03-18 02:23:59', '2026-03-18 02:27:47'),
(248, 'd86e51b3dbe28ee557c0fd9d575f204bb1efc7a323934490ed53162bffc4a8cb', 46, 3, '2026-03-18 02:25:24', '2026-03-25 15:56:38'),
(249, 'd86e51b3dbe28ee557c0fd9d575f204bb1efc7a323934490ed53162bffc4a8cb', 10, 2, '2026-03-18 02:27:04', '2026-03-18 02:27:25'),
(252, 'd86e51b3dbe28ee557c0fd9d575f204bb1efc7a323934490ed53162bffc4a8cb', 43, 11, '2026-03-18 02:32:41', '2026-03-25 15:56:42'),
(257, 'ae39ab525f1002e6f1eadf8cac1bd5f2e3ade85e1bfcbe65ed6e8c21ac01ffb5', 46, 25, '2026-03-18 09:08:19', '2026-04-01 06:05:36'),
(258, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 46, 1, '2026-03-18 09:08:22', '2026-03-18 09:08:22'),
(284, 'b707d2de8d56214e958de6798c75fb5a6b887b84633388c78bc297ba923ce84b', 1, 1, '2026-03-18 18:47:52', '2026-03-18 18:47:52'),
(294, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 1, 1, '2026-03-23 12:59:16', '2026-03-23 12:59:16'),
(300, '6dc421b28065ec10822e39fcff38f0ac460bf753bc975697a5db33ba04709d8c', 53, 26, '2026-03-23 13:04:42', '2026-04-01 05:19:46'),
(303, 'e915fec1d74e1fc2eea0199a75b4327793684c7122275df56cc8a7ab3b271022', 31, 24, '2026-03-23 13:09:06', '2026-04-01 05:00:16'),
(304, 'cd0a8ef843e616979e36056799b19de4f704052173be539ba516535bccae2082', 36, 45, '2026-03-23 13:09:06', '2026-04-01 05:17:15'),
(311, 'fa0922809ce9f30c9621e0fa32cc9e002ec1f03e313cd58980da0fbc8fbe185c', 40, 17, '2026-03-23 13:33:05', '2026-04-01 05:18:36'),
(314, '0d9d2ed0c04c00988e813aaf331f6f126a9652f0a5cd9e24fe05e8170e8c110d', 54, 74, '2026-03-23 14:53:36', '2026-03-29 08:27:37'),
(315, '0950c9145453bfc02f01d0971b91d95990c9800905fa1e14dd32605304f5e955', 35, 29, '2026-03-23 21:05:19', '2026-04-01 04:50:07'),
(316, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 35, 1, '2026-03-23 21:05:22', '2026-03-23 21:05:22'),
(331, '0f291ae5a3ebf8a1621578f8c3eb62a016bae555d63c24b448bc1a2c4835ba3f', 30, 29, '2026-03-24 04:32:58', '2026-04-01 04:50:25'),
(341, '0b2779a6899a57f679d2359bebeb400f0671b2e1ae59cb4c7b282fedadda5d6f', 38, 28, '2026-03-24 04:48:38', '2026-04-01 04:45:30'),
(345, '2c58bc86e4b9265c16ea9a3921ba9bfaef84aae996827c728c300e2716f19355', 27, 5, '2026-03-24 04:59:33', '2026-03-24 05:58:05'),
(347, '9431c34551cef77f3782dd5547bca150fdc358c1ee81e0b420a1a4f42c93d2d0', 41, 19, '2026-03-24 05:01:21', '2026-04-01 04:47:00'),
(353, '27de0be31612302577ca90611a0290e436f3153441ac3fbcb0ded00f7de24cca', 48, 12, '2026-03-24 05:06:47', '2026-04-01 05:30:21'),
(354, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 48, 1, '2026-03-24 05:06:51', '2026-03-24 05:06:51'),
(368, '1d25109eda306e691dcd614e6740a2553cee554a5a1cff47f0a0d5de94895af3', 68, 6, '2026-03-24 05:11:51', '2026-03-26 11:51:18'),
(371, '94ec37e1fa1dcfe4199285159ceb937d82f2b187ab42cebed2508fc7ca348205', 28, 11, '2026-03-24 05:12:58', '2026-04-01 05:11:34'),
(373, '52337b76eb36dae0845cc2b35dfbaad244670441b39074517f4febfa664718a0', 49, 16, '2026-03-24 05:13:46', '2026-04-01 05:14:09'),
(374, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 49, 2, '2026-03-24 05:13:49', '2026-04-01 05:14:05'),
(375, '5ba094a4b6d1461df54d3de98804626c05f6be61315223f6268bfb635ae1969b', 70, 25, '2026-03-24 05:14:55', '2026-04-01 05:17:34'),
(377, 'd63ff0f9b6f25e5f05fd808665dcfa742b05aac1b647a30e9aa5e79f5a65efb2', 34, 23, '2026-03-24 05:16:04', '2026-04-01 05:13:31'),
(378, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 34, 1, '2026-03-24 05:16:07', '2026-03-24 05:16:07'),
(382, '47314e54a7ace9580296989b7bac7290f4526f1bed463bc4d6986e0b5cac03bf', 32, 7, '2026-03-24 05:17:52', '2026-03-26 05:20:00'),
(392, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 53, 1, '2026-03-24 05:20:31', '2026-03-24 05:20:31'),
(398, '79dfd8ed408a90ecdfa4928f467aabb765c2b53b1ad24dd736bc986ad6d367d6', 29, 17, '2026-03-24 05:22:50', '2026-04-01 05:10:00'),
(406, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 29, 1, '2026-03-24 05:39:12', '2026-03-24 05:39:12'),
(407, '0d9ef7512cf95b5f9313f84377895ddf2998d6f557e4d8946f3f2520b730e5b3', 29, 1, '2026-03-24 05:39:16', '2026-03-24 05:39:16'),
(411, '5285e3a02e11752fbd00711b9aa865d6dcc382d09e26bf817a99cc1ea9f07c9a', 71, 31, '2026-03-24 05:54:04', '2026-04-01 05:03:48'),
(416, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 27, 1, '2026-03-24 07:03:42', '2026-03-24 07:03:42'),
(417, '6ce00005d09045e66a52630003bf97ad083839a5ee51cccaeedec19df4bd19cc', 27, 1, '2026-03-24 07:03:45', '2026-03-24 07:03:45'),
(436, 'a3373c4606819ca0237ccc1e9ce9ccf9fcb9ba2088c68dd2a787e01cb7d23218', 33, 2, '2026-03-24 12:36:08', '2026-03-24 12:36:12'),
(469, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 68, 1, '2026-03-25 04:55:03', '2026-03-25 04:55:03'),
(497, 'd12d217df37bf7eea0bfb16563053a88b032327ec057f0ed103121a5df6b5498', 40, 1, '2026-03-25 05:11:07', '2026-03-25 05:11:07'),
(500, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 28, 1, '2026-03-25 05:12:11', '2026-03-25 05:12:11'),
(514, '41eccd21658cb235ba528f4c1a557a16a0261bb7ebbbc1c68db1b05703497014', 45, 5, '2026-03-25 07:39:38', '2026-03-30 05:04:08'),
(515, '41eccd21658cb235ba528f4c1a557a16a0261bb7ebbbc1c68db1b05703497014', 47, 1, '2026-03-25 07:40:56', '2026-03-25 07:40:56'),
(519, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 49, 1, '2026-03-25 08:13:35', '2026-03-25 08:13:35'),
(521, '99b6beba53b575f936021ec5908fa57ceb21c36536369764e668b2b144c75f9b', 51, 1, '2026-03-25 08:23:43', '2026-03-25 08:23:43'),
(522, '3be4d998a3d308f861fe581a828237a2810775c3539e1c5c721abcfe068c40d7', 51, 8, '2026-03-25 08:23:45', '2026-03-28 03:42:58'),
(537, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 67, 1, '2026-03-25 12:33:32', '2026-03-25 12:33:32'),
(565, '0d9d2ed0c04c00988e813aaf331f6f126a9652f0a5cd9e24fe05e8170e8c110d', 2, 12, '2026-03-26 04:36:27', '2026-03-28 04:30:39'),
(580, '4408165787f23dc34bdf9c19b1214acd44595490ca8971a91135de2f6f47b6c3', 35, 1, '2026-03-26 04:51:02', '2026-03-26 04:51:02'),
(587, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 46, 1, '2026-03-26 04:59:24', '2026-03-26 04:59:24'),
(613, 'c1f2c3ffbad766e38e8317493cab76a3c1ec96dd8f01618c3075ca9270684527', 3, 1, '2026-03-26 05:11:56', '2026-03-26 05:11:56'),
(645, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 53, 1, '2026-03-26 11:49:31', '2026-03-26 11:49:31'),
(659, 'd4ec68b3bd0ad493dc4790f6b6e2318ff3bdc7542cffe84faa2290175e5d9847', 51, 1, '2026-03-28 03:42:16', '2026-03-28 03:42:16'),
(672, '643d69b7062c258eb101d9a63c081e1f6aadce25c017ba05b794a5d32cd42059', 3, 3, '2026-03-28 04:50:59', '2026-03-28 04:51:05'),
(697, '8e249a779eca5f1489e2672b2eca0ff373c8ff4519fb1532549f810e7be83c17', 29, 1, '2026-03-28 05:06:48', '2026-03-28 05:06:48'),
(711, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 40, 1, '2026-03-28 05:18:06', '2026-03-28 05:18:06'),
(725, '8e249a779eca5f1489e2672b2eca0ff373c8ff4519fb1532549f810e7be83c17', 53, 1, '2026-03-28 12:10:24', '2026-03-28 12:10:24'),
(757, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 67, 1, '2026-03-29 04:47:19', '2026-03-29 04:47:19'),
(784, '35017486e60029746e0bb6d2019280b76b7ae545262457a1b225ae38862835ec', 54, 1, '2026-03-29 04:57:34', '2026-03-29 04:57:34'),
(785, '8602b28fa06300e4b0b343b4d65f47608eb7082df42011bc2cce935e09810f40', 54, 1, '2026-03-29 04:57:34', '2026-03-29 04:57:34'),
(786, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 54, 1, '2026-03-29 04:57:38', '2026-03-29 04:57:38'),
(787, '49745f31137ae6100efa38bdf8969ced21434eaf540b3ef4a0384dc634aa6a75', 54, 5, '2026-03-29 04:57:53', '2026-03-29 04:59:02'),
(793, 'c580fd50e0ffe809d3536c26fd755052f245a0156d3c2043727ef5f80625481f', 46, 1, '2026-03-29 04:59:39', '2026-03-29 04:59:39'),
(805, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 49, 1, '2026-03-29 05:04:05', '2026-03-29 05:04:05'),
(811, '8e249a779eca5f1489e2672b2eca0ff373c8ff4519fb1532549f810e7be83c17', 1, 1, '2026-03-29 05:06:22', '2026-03-29 05:06:22'),
(815, 'ff40acc15fc6d9e79dcb0552a17163088770ad2465deab743029ba6268748482', 70, 1, '2026-03-29 05:11:57', '2026-03-29 05:11:57'),
(816, '7987c52fbb747bf71dc85ac8b6fc15a034428897e9ab5ff5465f17229ad00cb3', 70, 1, '2026-03-29 05:12:00', '2026-03-29 05:12:00'),
(830, '67cd8a4e3439794c961b5f6d741ad3b9b3183f8ec8f4e6416ecd639d441fdc36', 52, 1, '2026-03-29 09:15:58', '2026-03-29 09:15:58'),
(831, 'c7c7626ebd27239d979fb853ae1dc763295de532bffe145e4a186a0427f133d8', 52, 1, '2026-03-29 09:16:20', '2026-03-29 09:16:20'),
(832, '064e6381119f88727121fb0211d925d5cc5f9b7e8afb79c840e7fced264a6849', 52, 1, '2026-03-29 09:17:45', '2026-03-29 09:17:45'),
(858, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 35, 1, '2026-03-30 04:59:06', '2026-03-30 04:59:06'),
(869, 'f9ee3ee5245ea5ceb96428d42ac817092b945213a3ec78eb97d91ecd28319338', 47, 20, '2026-03-30 05:04:06', '2026-04-01 05:02:41'),
(872, '39cf70c33774fabf7f775c08589a0e38b7ba459cbb5b4467129baba01ac54e9e', 47, 1, '2026-03-30 05:04:11', '2026-03-30 05:04:11'),
(889, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 28, 1, '2026-03-30 05:11:52', '2026-03-30 05:11:52'),
(897, '7987c52fbb747bf71dc85ac8b6fc15a034428897e9ab5ff5465f17229ad00cb3', 3, 1, '2026-03-30 05:15:15', '2026-03-30 05:15:15'),
(901, '074b23d27f7197c06639d6beb9ca2c21de1be1a40369320e8f7487c9e92f17e8', 52, 1, '2026-03-30 05:17:14', '2026-03-30 05:17:14'),
(902, '5cae66f0d595c1728cad0496215da50a9b4d8a14127651e9a9ee31446cba7a6f', 52, 1, '2026-03-30 05:17:19', '2026-03-30 05:17:19'),
(906, '8e249a779eca5f1489e2672b2eca0ff373c8ff4519fb1532549f810e7be83c17', 40, 1, '2026-03-30 05:19:04', '2026-03-30 05:19:04'),
(908, '8afbcd957a595cae08e36d36b5fd60a39f05ddce850f3ec7afcb16c4747d8084', 10, 10, '2026-03-30 05:19:35', '2026-04-01 05:07:20'),
(910, 'cca23970b71213e0378a2a96b90996fbf246642e8142cd74546e60d4b4b19e2f', 52, 1, '2026-03-30 05:20:25', '2026-03-30 05:20:25'),
(911, 'd00148d1c45a994259ad4ba3c2a3e2e7192a2af03f2a3b4d7ab15d3366563e64', 52, 1, '2026-03-30 05:20:30', '2026-03-30 05:20:30'),
(912, '5b5063f048ac337a0882e46989e0a87563c0cb1e219d1b57397754cd46b2fa87', 52, 1, '2026-03-30 05:20:40', '2026-03-30 05:20:40'),
(913, '33ecd91b37477cecbc0f727b6096d330fb63e60e7bdc57d6c2b9ca91d0817dc2', 52, 1, '2026-03-30 05:20:43', '2026-03-30 05:20:43'),
(914, 'c98a9abe5df32682b54108900574476fc4f90437a360f076abf874384e79751d', 22, 1, '2026-03-30 05:20:52', '2026-03-30 05:20:52'),
(915, 'f8dd0e6466ebec2d1077991b97407809f130444433f44ff054e69744810eab51', 22, 1, '2026-03-30 05:21:02', '2026-03-30 05:21:02'),
(917, '583bd4d1165746e2b943e89aa03e83f2b8e701cd938250859da3a7d8ebf85c88', 24, 1, '2026-03-30 05:21:47', '2026-03-30 05:21:47'),
(918, 'cf63e1c62fc0894659b7e5bd3b952965a04592b5bd81ea1c7d16cd34f28ba105', 24, 1, '2026-03-30 05:21:52', '2026-03-30 05:21:52'),
(919, '33b843b0d6e61220dc0f74e3d7bca90f15a13a5cccc63b08bac6d793c0bfa89a', 24, 1, '2026-03-30 05:22:14', '2026-03-30 05:22:14'),
(922, 'dff62bfc17def71b016613fe088bf8996531b3d991b154334453ca982e7f551e', 12, 19, '2026-03-30 05:48:41', '2026-04-01 05:01:20'),
(923, '15151a8aa080fef1f0f41c14d4e8efc8a69fcb9837194a1419eb1f11b713fdab', 17, 21, '2026-03-30 05:48:48', '2026-04-01 05:00:07'),
(924, '8e249a779eca5f1489e2672b2eca0ff373c8ff4519fb1532549f810e7be83c17', 17, 1, '2026-03-30 05:48:51', '2026-03-30 05:48:51'),
(931, 'acf72ad1c01c836be54e455437af22846f2e58bf1a893462102176d1d6a9313c', 72, 13, '2026-03-30 06:00:04', '2026-04-01 04:57:02'),
(932, '1d25109eda306e691dcd614e6740a2553cee554a5a1cff47f0a0d5de94895af3', 16, 7, '2026-03-30 06:00:08', '2026-04-01 05:06:20'),
(934, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 16, 1, '2026-03-30 06:00:13', '2026-03-30 06:00:13'),
(940, '9aafaf222e9902581062b402575e3417de77d94718c756388505de8e507220cc', 73, 13, '2026-03-30 06:05:20', '2026-04-01 04:52:30'),
(942, '7eaabd65444e5367dd907b1ca7a19637f95808d4a83354ed77b83a8cd14f4e07', 73, 1, '2026-03-30 06:05:23', '2026-03-30 06:05:23'),
(947, '05c108a7c3bb86c5223ffc4922e24528df1ef99eacff778233d6671af80f78a0', 12, 1, '2026-03-30 06:05:58', '2026-03-30 06:05:58'),
(949, 'a34d0e2bc56b3757433116a9efdfef2f8595af8357fc83dfc07d2dd06f6f621f', 74, 11, '2026-03-30 06:07:56', '2026-04-01 05:02:02'),
(951, '87cb149bf1d91af725ae87271ea8c251c37fbdb7e1e5f3beb47b40effebb2e60', 11, 16, '2026-03-30 06:08:55', '2026-04-01 05:16:23'),
(955, '3b8d866f57f2720877d5f6f62af3f6b93d4f6d0ef2ec4d50b907a2cbcccef75e', 21, 2, '2026-03-30 06:10:45', '2026-03-30 06:12:07'),
(989, '4a2268d79c1d3f2532929036fb97277d3e06614b8b7caee8c987fcce6346f82e', 72, 5, '2026-03-30 11:09:26', '2026-04-01 04:56:28'),
(995, 'ec1d9cc3095477f921f15055536755659f6d96c4379b7a5c3fba658f58def82b', 53, 1, '2026-03-30 11:59:01', '2026-03-30 11:59:01'),
(1008, '0d9ef7512cf95b5f9313f84377895ddf2998d6f557e4d8946f3f2520b730e5b3', 72, 1, '2026-03-30 17:02:19', '2026-03-30 17:02:19'),
(1009, '93f80848cce7543a0725a9502866a1f42d7443d9b63adfade697954ff179296c', 24, 1, '2026-03-30 17:02:31', '2026-03-30 17:02:31'),
(1011, '17db276640076f4b90e17eb12360ceac11e59f1560aef2b0f3663071b8fb9fb6', 24, 1, '2026-03-30 17:02:40', '2026-03-30 17:02:40'),
(1013, 'e026913d59659acee8b25a6df7ff58d726c22e45e7ea660164bf022218840f4d', 24, 1, '2026-03-30 17:03:19', '2026-03-30 17:03:19'),
(1014, '99eef501400af59a9ad90b34cf69f60aa4d159a813cc3920163bbc183c200fe4', 24, 1, '2026-03-30 17:06:02', '2026-03-30 17:06:02'),
(1047, '68d4e70c5cb0bb681a97e9d3686150bb7736611843290390e196deeea31eaae1', 51, 7, '2026-03-31 05:04:24', '2026-04-01 04:59:02'),
(1048, 'e8416aa9344acbb8b052c62eb5fa64f9a46883b13db58ed384af02e5a563ac84', 51, 1, '2026-03-31 05:04:28', '2026-03-31 05:04:28'),
(1055, 'b249ad053b4b223efade29172797d95634777b7d38e0c65ef54f8e399a8e8350', 29, 1, '2026-03-31 05:04:58', '2026-03-31 05:04:58'),
(1058, '0cc5e1ea1d4f719055fd7e9e9204312937c9491532aa266e8b68bb688d34e232', 24, 1, '2026-03-31 05:05:13', '2026-03-31 05:05:13'),
(1059, '06ecb28c455477bc8c0d359924496a2b3fd8ecf9070579822cb349c44e95c85c', 24, 1, '2026-03-31 05:05:18', '2026-03-31 05:05:18'),
(1082, '42106c6cbd316d698211b7ec4a5eb379859160746782ebf2e2ed5a3daef77f3c', 34, 1, '2026-03-31 05:10:14', '2026-03-31 05:10:14'),
(1095, 'b7c86e36b32cafb8377616e70764377523dbf433a35244ed9bd58f25d58eea2f', 68, 1, '2026-03-31 05:17:40', '2026-03-31 05:17:40'),
(1128, '1ad9fd75670400c15c1c55139e6da39f4541c39aa4a097a149590980d522565b', 24, 1, '2026-03-31 11:08:38', '2026-03-31 11:08:38'),
(1129, '13570259bbf835cd4041f6f7ce7e616afcd2d3c5eab6fe3d250d5285fe47a750', 24, 1, '2026-03-31 11:08:40', '2026-03-31 11:08:40'),
(1130, 'b8adab52e6890c2f2ec1a61dffc2e6f61efec46806e3bc8a9cbb171be94dc6c7', 24, 1, '2026-03-31 11:08:48', '2026-03-31 11:08:48'),
(1131, '5659cc9c000aeb02f842ceeb7683e1259ddf1cb672ea0b507738c4763c01c693', 24, 1, '2026-03-31 11:09:15', '2026-03-31 11:09:15'),
(1133, '49868ebb6e262132d903dcb2bc4ef7986bef00f2270f789332d9daec6332b7e5', 51, 1, '2026-03-31 11:15:04', '2026-03-31 11:15:04'),
(1134, '3dcd41fbe5a3639fe472fdc43a75df5c4239fc3159fe52279da28c8d0abd5f28', 10, 2, '2026-03-31 11:17:04', '2026-03-31 11:17:44'),
(1155, '6412cc90ff53b2140c84a20661bfc7f0e52a51a302c9de0b41db86f3cfa1a6fd', 4, 8, '2026-03-31 15:08:31', '2026-04-01 04:53:08'),
(1166, 'e267365304c2719934c037396c74c150b02c7ab037fd9805f05cabb65b90e21d', 67, 1, '2026-04-01 04:47:04', '2026-04-01 04:47:04'),
(1190, '2c4cfcfeff2670793c4b50e2f6fe0737fa6479493daaf8b1adb311038c2cb620', 46, 1, '2026-04-01 04:59:53', '2026-04-01 04:59:53'),
(1203, 'a70375bd0fe71ee7683b5c809c4296f492fc73c3724ab94f79b611bf5d81e2ba', 24, 1, '2026-04-01 05:02:11', '2026-04-01 05:02:11'),
(1204, '7d28191b32c534d27ffc79272772da8c0c99d459caaca7f86581b7cdb4e4bcf5', 24, 1, '2026-04-01 05:02:18', '2026-04-01 05:02:18'),
(1208, '68d4e70c5cb0bb681a97e9d3686150bb7736611843290390e196deeea31eaae1', 10, 2, '2026-04-01 05:02:56', '2026-04-01 05:03:09'),
(1209, '6b33ff718ee55d3829009d567182b28ee536af6a9a5f41347f957a5ed3dcb4e5', 10, 1, '2026-04-01 05:02:59', '2026-04-01 05:02:59');

-- --------------------------------------------------------

--
-- بنية الجدول `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('annual','sick','unpaid','other') NOT NULL DEFAULT 'annual',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `attempted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(2, '2024_01_01_000001_create_branches_table', 1),
(3, '2024_01_01_000002_create_admins_table', 1),
(4, '2024_01_01_000003_create_employees_table', 1),
(5, '2024_01_01_000004_create_attendances_table', 1),
(6, '2024_01_01_000005_create_settings_table', 1),
(7, '2024_01_01_000006_create_login_attempts_table', 1),
(8, '2024_01_01_000007_create_audit_log_table', 1),
(9, '2024_01_01_000008_create_known_devices_table', 1),
(10, '2024_01_01_000009_create_leaves_table', 1),
(11, '2024_01_01_000010_create_secret_reports_table', 1),
(12, '2024_01_01_000011_create_tampering_cases_table', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `employee_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `data_json`, `created_at`, `read_at`) VALUES
(1, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] وقت تسجيل الدخول المسموح به: 12:00:00 - 14:30:00. الوقت الحالي: 20:27', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:27:34\"}', '2026-03-17 17:27:34', NULL),
(2, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"192.178.15.100\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-17 20:27:36\"}', '2026-03-17 17:27:36', NULL),
(3, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] وقت تسجيل الدخول المسموح به: 12:00:00 - 14:30:00. الوقت الحالي: 20:27', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:27:51\"}', '2026-03-17 17:27:51', NULL),
(4, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 20:43', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:43:04\"}', '2026-03-17 17:43:04', NULL),
(5, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 20:43', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:43:19\"}', '2026-03-17 17:43:19', NULL),
(6, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 20:43', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:43:47\"}', '2026-03-17 17:43:47', NULL),
(7, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 20:47', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:47:19\"}', '2026-03-17 17:47:19', NULL),
(8, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 20:47', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 20:47:24\"}', '2026-03-17 17:47:24', NULL),
(9, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 21:58', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 21:58:06\"}', '2026-03-17 18:58:06', NULL),
(10, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 21:58', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 21:58:08\"}', '2026-03-17 18:58:08', NULL),
(11, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:34:29\"}', '2026-03-17 19:34:29', NULL),
(12, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:34:35\"}', '2026-03-17 19:34:35', NULL),
(13, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 22:35', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:55:3407:e87d:6041:b40b:b854\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-17 22:35:39\"}', '2026-03-17 19:35:39', NULL),
(14, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:36:45\"}', '2026-03-17 19:36:45', NULL),
(15, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:38:25\"}', '2026-03-17 19:38:25', NULL),
(16, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:38:31\"}', '2026-03-17 19:38:31', NULL),
(17, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:38:36\"}', '2026-03-17 19:38:36', NULL),
(18, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:39:06\"}', '2026-03-17 19:39:06', NULL),
(19, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:43:05\"}', '2026-03-17 19:43:05', NULL),
(20, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:43:10\"}', '2026-03-17 19:43:10', NULL),
(21, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.217.240.218\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-17 22:43:14\"}', '2026-03-17 19:43:14', NULL),
(22, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:26:1087:7771:c5b7:995e:9469\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 22:59:16\"}', '2026-03-17 19:59:16', NULL),
(23, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:75:c642:189c:67d3:bdb8:df00\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 23:20:05\"}', '2026-03-17 20:20:05', NULL),
(24, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:75:c642:189c:67d3:bdb8:df00\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 23:20:23\"}', '2026-03-17 20:20:23', NULL),
(25, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:75:c642:189c:67d3:bdb8:df00\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-17 23:20:47\"}', '2026-03-17 20:20:47', NULL),
(26, 9, NULL, 'error_report', '⚠️ خطأ: نجيب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:26:1087:b2b5:c3ff:fe83:363\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 00:15:39\"}', '2026-03-17 21:15:39', NULL),
(27, 9, NULL, 'error_report', '⚠️ خطأ: نجيب', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:26:1087:b2b5:c3ff:fe83:363\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 00:16:07\"}', '2026-03-17 21:16:07', NULL),
(28, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.42.190.41\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:13:37\"}', '2026-03-18 02:13:37', NULL),
(29, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.42.190.41\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:13:46\"}', '2026-03-18 02:13:46', NULL),
(30, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:14:45\"}', '2026-03-18 02:14:45', NULL),
(31, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:14:50\"}', '2026-03-18 02:14:50', NULL),
(32, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:14:53\"}', '2026-03-18 02:14:53', NULL),
(33, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:15:34\"}', '2026-03-18 02:15:34', NULL),
(34, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:15:55\"}', '2026-03-18 02:15:55', NULL),
(35, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:18:50\"}', '2026-03-18 02:18:50', NULL),
(36, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:18:53\"}', '2026-03-18 02:18:53', NULL),
(37, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:19:28\"}', '2026-03-18 02:19:28', NULL),
(38, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:19:29\"}', '2026-03-18 02:19:29', NULL),
(39, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:23:59\"}', '2026-03-18 02:23:59', NULL),
(40, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:24:08\"}', '2026-03-18 02:24:08', NULL),
(41, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:24:19\"}', '2026-03-18 02:24:19', NULL),
(42, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:24:46\"}', '2026-03-18 02:24:46', NULL),
(43, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:25:24\"}', '2026-03-18 02:25:24', NULL),
(44, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:25:45\"}', '2026-03-18 02:25:45', NULL),
(45, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:27:04\"}', '2026-03-18 02:27:04', NULL),
(46, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:27:25\"}', '2026-03-18 02:27:25', NULL),
(47, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:41\"}', '2026-03-18 02:32:41', NULL),
(48, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:45\"}', '2026-03-18 02:32:45', NULL),
(49, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:46\"}', '2026-03-18 02:32:46', NULL),
(50, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:47\"}', '2026-03-18 02:32:47', NULL),
(51, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:48\"}', '2026-03-18 02:32:48', NULL),
(52, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:49\"}', '2026-03-18 02:32:49', NULL),
(53, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:49\"}', '2026-03-18 02:32:49', NULL),
(54, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:50\"}', '2026-03-18 02:32:50', NULL),
(55, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:32:51\"}', '2026-03-18 02:32:51', NULL),
(56, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:33:04\"}', '2026-03-18 02:33:04', NULL),
(57, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:33:06\"}', '2026-03-18 02:33:06', NULL),
(58, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ad:b7e3:4479:5837:4bc7:2ff\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 05:33:12\"}', '2026-03-18 02:33:12', NULL),
(59, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:22:c158:f976:a03b:350c:67f4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 12:07:29\"}', '2026-03-18 09:07:29', NULL),
(60, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"74.125.210.4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-18 12:08:23\"}', '2026-03-18 09:08:23', NULL),
(61, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c054:2797:2183:8106:994:cf74\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 12:08:26\"}', '2026-03-18 09:08:26', NULL),
(62, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:22:c158:f976:a03b:350c:67f4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 12:30:37\"}', '2026-03-18 09:30:37', NULL),
(63, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:22:c158:f976:a03b:350c:67f4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 12:30:45\"}', '2026-03-18 09:30:45', NULL),
(64, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:22:c158:f976:a03b:350c:67f4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 12:55:25\"}', '2026-03-18 09:55:25', NULL),
(65, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:22:c158:f976:a03b:350c:67f4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.51.0-gn\",\"timestamp\":\"2026-03-18 12:55:47\"}', '2026-03-18 09:55:47', NULL),
(66, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 19:42', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3841:c481:fc0d:840f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 19:42:41\"}', '2026-03-18 16:42:41', NULL),
(67, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 19:42', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3841:c481:fc0d:840f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 19:42:52\"}', '2026-03-18 16:42:52', NULL),
(68, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 19:44', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3841:c481:fc0d:840f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 19:44:11\"}', '2026-03-18 16:44:11', NULL),
(69, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 19:44', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3841:c481:fc0d:840f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 19:44:38\"}', '2026-03-18 16:44:38', NULL),
(70, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 19:45', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3841:c481:fc0d:840f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 19:45:12\"}', '2026-03-18 16:45:12', NULL),
(71, 26, NULL, 'error_report', '⚠️ خطأ: عبدالله اليمني', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 20:00', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"77.232.122.216\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.4 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-18 20:00:10\"}', '2026-03-18 17:00:10', NULL),
(72, 26, NULL, 'error_report', '⚠️ خطأ: عبدالله اليمني', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 20:00', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.4 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-18 20:00:22\"}', '2026-03-18 17:00:22', NULL),
(73, 26, NULL, 'error_report', '⚠️ خطأ: عبدالله اليمني', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 20:00', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_4_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.4 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-18 20:00:30\"}', '2026-03-18 17:00:30', NULL),
(74, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 25 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c054:2797:59c9:fe2:3c4a:ea5b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 20:09:02\"}', '2026-03-18 17:09:02', NULL),
(75, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 26 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c054:2797:59c9:fe2:3c4a:ea5b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 20:09:04\"}', '2026-03-18 17:09:04', NULL),
(76, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c054:2797:59c9:fe2:3c4a:ea5b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 20:09:15\"}', '2026-03-18 17:09:15', NULL),
(77, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[attendance_fail] تسجيل الحضور متاح من 21:00 إلى 23:59:00. الوقت الحالي: 20:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:75:c642:189c:67d3:bdb8:df00\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 20:10:25\"}', '2026-03-18 17:10:25', NULL),
(78, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"74.125.210.4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-18 21:47:52\"}', '2026-03-18 18:47:52', NULL),
(79, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 21 متر (الحد المسموح: 20 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:cbc7:e5a2:45f3:e391\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 21:47:56\"}', '2026-03-18 18:47:56', NULL),
(80, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 33 متر (الحد المسموح: 20 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:cbc7:e5a2:45f3:e391\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 21:48:05\"}', '2026-03-18 18:48:05', NULL),
(81, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e666:925a:e25e:9402\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 21:49:11\"}', '2026-03-18 18:49:11', NULL),
(82, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e666:925a:e25e:9402\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 21:49:30\"}', '2026-03-18 18:49:30', NULL),
(83, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-18 22:49:12\"}', '2026-03-18 19:49:12', NULL),
(84, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 00:14', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:75:c642:189c:67d3:bdb8:df00\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-19 00:14:07\"}', '2026-03-18 21:14:07', NULL),
(85, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 11:00 إلى 16:00:00. الوقت الحالي: 00:30', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-19 00:30:16\"}', '2026-03-18 21:30:16', NULL),
(86, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 32 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3214:1eb::31:158\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:01:16\"}', '2026-03-23 13:01:16', NULL),
(87, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:06\"}', '2026-03-23 13:09:06', NULL),
(88, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:13\"}', '2026-03-23 13:09:13', NULL),
(89, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3211:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.7632.159 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:21\"}', '2026-03-23 13:09:21', NULL),
(90, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3211:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.7632.159 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:22\"}', '2026-03-23 13:09:22', NULL),
(91, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3211:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.7632.159 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:35\"}', '2026-03-23 13:09:35', NULL),
(92, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3211:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.7632.159 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-23 16:09:54\"}', '2026-03-23 13:09:54', NULL),
(93, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"74.125.210.9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 00:05:23\"}', '2026-03-23 21:05:23', NULL),
(94, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e7a0:6041:e57c:a778\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 00:13:19\"}', '2026-03-23 21:13:19', NULL),
(95, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2014 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e7a0:6041:e57c:a778\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 00:14:33\"}', '2026-03-23 21:14:33', NULL),
(96, 30, NULL, 'error_report', '⚠️ خطأ: عرنوس', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 46 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c214:49f1:a160:2cca:7a63:d0c0\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-24 07:33:04\"}', '2026-03-24 04:33:04', NULL),
(97, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:36:07\"}', '2026-03-24 04:36:07', NULL),
(98, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:44:24\"}', '2026-03-24 04:44:24', NULL),
(99, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:44:26\"}', '2026-03-24 04:44:26', NULL),
(100, 38, NULL, 'error_report', '⚠️ خطأ: شعبان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2010 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e81a:e9cd:a73d:eebf\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:48:59\"}', '2026-03-24 04:48:59', NULL),
(101, 38, NULL, 'error_report', '⚠️ خطأ: شعبان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2012 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e81a:e9cd:a73d:eebf\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:50:45\"}', '2026-03-24 04:50:45', NULL),
(102, 38, NULL, 'error_report', '⚠️ خطأ: شعبان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2011 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e81a:e9cd:a73d:eebf\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:52:30\"}', '2026-03-24 04:52:30', NULL),
(103, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e895:1d0f:542e:f499\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:59:33\"}', '2026-03-24 04:59:33', NULL);
INSERT INTO `notifications` (`id`, `employee_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `data_json`, `created_at`, `read_at`) VALUES
(104, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e895:1d0f:542e:f499\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:59:35\"}', '2026-03-24 04:59:35', NULL),
(105, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e895:1d0f:542e:f499\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 07:59:49\"}', '2026-03-24 04:59:49', NULL),
(106, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c218:e33f:746b:a946:c7cd:8dad\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:04:17\"}', '2026-03-24 05:04:17', NULL),
(107, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 32 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.249.175\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-24 08:05:56\"}', '2026-03-24 05:05:56', NULL),
(108, 48, NULL, 'error_report', '⚠️ خطأ: ابو حازم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.129\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:06:52\"}', '2026-03-24 05:06:52', NULL),
(109, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 68 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3213:254b::3b7:47\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:10:01\"}', '2026-03-24 05:10:01', NULL),
(110, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 73 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3213:254b::3b7:47\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:10:03\"}', '2026-03-24 05:10:03', NULL),
(111, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 32 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3213:254b::3b7:47\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:10:06\"}', '2026-03-24 05:10:06', NULL),
(112, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:27c0:cc0::3b7:47\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:10:28\"}', '2026-03-24 05:10:28', NULL),
(113, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 45 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a6b:6f47:7b34:ffda\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:13:48\"}', '2026-03-24 05:13:48', NULL),
(114, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.132\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:13:50\"}', '2026-03-24 05:13:50', NULL),
(115, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 30 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a6b:6f47:7b34:ffda\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:13:53\"}', '2026-03-24 05:13:53', NULL),
(116, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 32 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a6b:6f47:7b34:ffda\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:13:58\"}', '2026-03-24 05:13:58', NULL),
(117, 34, NULL, 'error_report', '⚠️ خطأ: ابانوب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:16:08\"}', '2026-03-24 05:16:08', NULL),
(118, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:16:42\"}', '2026-03-24 05:16:42', NULL),
(119, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e895:1d0f:542e:f499\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:17:29\"}', '2026-03-24 05:17:29', NULL),
(120, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:e895:1d0f:542e:f499\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:17:33\"}', '2026-03-24 05:17:33', NULL),
(121, 32, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:f199:cce8:9b51:25d9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:17:58\"}', '2026-03-24 05:17:58', NULL),
(122, 32, NULL, 'error_report', '⚠️ خطأ: وسيم', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:f199:cce8:9b51:25d9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:18:03\"}', '2026-03-24 05:18:03', NULL),
(123, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:1eb::31:158\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:19:15\"}', '2026-03-24 05:19:15', NULL),
(124, 36, NULL, 'error_report', '⚠️ خطأ: وداعة الله', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 552 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:1eb::31:158\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:19:21\"}', '2026-03-24 05:19:21', NULL),
(125, 53, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:20:31\"}', '2026-03-24 05:20:31', NULL),
(126, 53, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.14\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:20:32\"}', '2026-03-24 05:20:32', NULL),
(127, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:1eb::31:158\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:21:50\"}', '2026-03-24 05:21:50', NULL),
(128, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:31e0:e6b5:b842:5b0a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:22:50\"}', '2026-03-24 05:22:50', NULL),
(129, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:31e0:e6b5:b842:5b0a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:23:05\"}', '2026-03-24 05:23:05', NULL),
(130, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 49 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:d776:e1d5:aa86:2064\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:23:22\"}', '2026-03-24 05:23:22', NULL),
(131, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:40:e04d:31e0:e6b5:b842:5b0a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:23:28\"}', '2026-03-24 05:23:28', NULL),
(132, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 43 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:d776:e1d5:aa86:2064\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:23:38\"}', '2026-03-24 05:23:38', NULL),
(133, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:d776:e1d5:aa86:2064\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:24:08\"}', '2026-03-24 05:24:08', NULL),
(134, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 54 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c056:81a3:a84d:c0e5:4945:826\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-24 08:26:35\"}', '2026-03-24 05:26:35', NULL),
(135, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:39:16\"}', '2026-03-24 05:39:16', NULL),
(136, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.13\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 08:39:16\"}', '2026-03-24 05:39:16', NULL),
(137, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:212b:5446:9e83:3635\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:45:22\"}', '2026-03-24 05:45:22', NULL),
(138, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:212b:5446:9e83:3635\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:45:33\"}', '2026-03-24 05:45:33', NULL),
(139, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"143.20.160.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:57:17\"}', '2026-03-24 05:57:17', NULL),
(140, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"143.20.160.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:57:22\"}', '2026-03-24 05:57:22', NULL),
(141, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"143.20.160.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:58:05\"}', '2026-03-24 05:58:05', NULL),
(142, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"143.20.160.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 08:58:21\"}', '2026-03-24 05:58:21', NULL),
(143, 27, NULL, 'error_report', '⚠️ خطأ: أفضل', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-24 10:03:45\"}', '2026-03-24 07:03:45', NULL),
(144, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[js_error] Uncaught Error: Invalid LatLng object: (NaN, NaN) @ https://sarh.io/xml/assets/vendor/leaflet/leaflet.min.js:1', 0, '{\"error_type\":\"js_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::3b7:47\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-24 15:05:43\"}', '2026-03-24 12:05:43', NULL),
(145, 30, NULL, 'error_report', '⚠️ خطأ: عرنوس', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 38 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c032:f69a:e0e6:77fb:be06:ce25\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-25 07:41:21\"}', '2026-03-25 04:41:21', NULL),
(146, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-25 07:55:03\"}', '2026-03-25 04:55:03', NULL),
(147, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.6\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-25 07:55:04\"}', '2026-03-25 04:55:04', NULL),
(148, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f:379:f8ed:2bab:f6a8:3dda\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:03:55\"}', '2026-03-25 05:03:55', NULL),
(149, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f:379:f8ed:2bab:f6a8:3dda\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:04:04\"}', '2026-03-25 05:04:04', NULL),
(150, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"104.28.162.133\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:05:21\"}', '2026-03-25 05:05:21', NULL),
(151, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:06:41\"}', '2026-03-25 05:06:41', NULL),
(152, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:06:50\"}', '2026-03-25 05:06:50', NULL),
(153, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:12:08\"}', '2026-03-25 05:12:08', NULL),
(154, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-25 08:12:12\"}', '2026-03-25 05:12:12', NULL),
(155, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:12:16\"}', '2026-03-25 05:12:16', NULL),
(156, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:87d:5d11:2458:50a2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:21:17\"}', '2026-03-25 05:21:17', NULL),
(157, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:87d:5d11:2458:50a2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:21:35\"}', '2026-03-25 05:21:35', NULL),
(158, 71, NULL, 'error_report', '⚠️ خطأ: تعظيم', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f:379:ed7d:3ebd:9b25:5aad\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:22:17\"}', '2026-03-25 05:22:17', NULL),
(159, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 26 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:de67:44d:a1dc:2a29\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:23:56\"}', '2026-03-25 05:23:56', NULL),
(160, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:de67:44d:a1dc:2a29\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:23:57\"}', '2026-03-25 05:23:57', NULL),
(161, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:de67:44d:a1dc:2a29\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 08:23:58\"}', '2026-03-25 05:23:58', NULL),
(162, 45, NULL, 'error_report', '⚠️ خطأ: عبدو بوية', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-03-25 11:21:00\"}', '2026-03-25 08:21:00', NULL),
(163, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"74.125.210.11\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-25 11:23:44\"}', '2026-03-25 08:23:44', NULL),
(164, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 12:20:09\"}', '2026-03-25 09:20:09', NULL),
(165, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 12:26:38\"}', '2026-03-25 09:26:38', NULL),
(166, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Enable GPS and retry', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3210:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; en; Infinix X6870 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=SA;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 12:44:02\"}', '2026-03-25 09:44:02', NULL),
(167, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3210:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; en; Infinix X6870 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=SA;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 12:44:17\"}', '2026-03-25 09:44:17', NULL),
(168, 7, NULL, 'error_report', '⚠️ خطأ: أيمن', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 13:15', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:d0:f20e:189e:3239:3438:768c\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 13:15:00\"}', '2026-03-25 10:15:00', NULL),
(169, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:10:38\"}', '2026-03-25 11:10:38', NULL),
(170, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:10:39\"}', '2026-03-25 11:10:39', NULL),
(171, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:10:42\"}', '2026-03-25 11:10:42', NULL),
(172, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:10:52\"}', '2026-03-25 11:10:52', NULL),
(173, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:10', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:10:58\"}', '2026-03-25 11:10:58', NULL),
(174, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:00\"}', '2026-03-25 11:11:00', NULL),
(175, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:04\"}', '2026-03-25 11:11:04', NULL),
(176, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:05\"}', '2026-03-25 11:11:05', NULL),
(177, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:06\"}', '2026-03-25 11:11:06', NULL),
(178, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:08\"}', '2026-03-25 11:11:08', NULL),
(179, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:15\"}', '2026-03-25 11:11:15', NULL),
(180, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:17\"}', '2026-03-25 11:11:17', NULL),
(181, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:18\"}', '2026-03-25 11:11:18', NULL),
(182, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:20\"}', '2026-03-25 11:11:20', NULL),
(183, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:21\"}', '2026-03-25 11:11:21', NULL),
(184, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:22\"}', '2026-03-25 11:11:22', NULL),
(185, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:22\"}', '2026-03-25 11:11:22', NULL),
(186, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:23\"}', '2026-03-25 11:11:23', NULL),
(187, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:27\"}', '2026-03-25 11:11:27', NULL),
(188, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 14:11', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:a937:b625:6dbb:bf67\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 14:11:28\"}', '2026-03-25 11:11:28', NULL),
(189, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[js_error] Uncaught Error: Invalid LatLng object: (NaN, NaN) @ https://sarh.io/xml/assets/vendor/leaflet/leaflet.min.js:1', 0, '{\"error_type\":\"js_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 15:09:06\"}', '2026-03-25 12:09:06', NULL),
(190, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:34cd:dc73:b596:3eb8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 15:33:29\"}', '2026-03-25 12:33:29', NULL),
(191, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:34cd:dc73:b596:3eb8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 15:33:30\"}', '2026-03-25 12:33:30', NULL),
(192, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"74.125.210.2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-25 15:33:34\"}', '2026-03-25 12:33:34', NULL),
(193, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:3010:b098:3709:9231\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-25 16:52:17\"}', '2026-03-25 13:52:17', NULL),
(194, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-25 18:56:14\"}', '2026-03-25 15:56:14', NULL),
(195, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-25 18:56:15\"}', '2026-03-25 15:56:15', NULL),
(196, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-25 18:56:38\"}', '2026-03-25 15:56:38', NULL),
(197, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-25 18:56:51\"}', '2026-03-25 15:56:51', NULL),
(198, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-25 18:56:52\"}', '2026-03-25 15:56:52', NULL),
(199, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:87a8:d6ff:b893:15f5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-25 22:42:42\"}', '2026-03-25 19:42:42', NULL),
(200, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 95 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c056:81a3:6c38:6736:a80b:55a3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 07:34:35\"}', '2026-03-26 04:34:35', NULL),
(201, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 94 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c056:81a3:6c38:6736:a80b:55a3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 07:36:17\"}', '2026-03-26 04:36:17', NULL),
(202, 2, NULL, 'error_report', '⚠️ خطأ: حسني', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 95 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c056:81a3:6c38:6736:a80b:55a3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 07:36:38\"}', '2026-03-26 04:36:38', NULL),
(203, 2, NULL, 'error_report', '⚠️ خطأ: حسني', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c056:81a3:6c38:6736:a80b:55a3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 07:37:43\"}', '2026-03-26 04:37:43', NULL),
(204, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.130\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-26 07:51:03\"}', '2026-03-26 04:51:03', NULL),
(205, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-26 07:51:03\"}', '2026-03-26 04:51:03', NULL),
(206, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:72aa:90b0:25dc:7af3\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 07:58:07\"}', '2026-03-26 04:58:07', NULL),
(207, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-26 07:59:25\"}', '2026-03-26 04:59:25', NULL),
(208, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:ad15:3abd:574e:c4b\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 08:01:22\"}', '2026-03-26 05:01:22', NULL);
INSERT INTO `notifications` (`id`, `employee_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `data_json`, `created_at`, `read_at`) VALUES
(209, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:eb:f3d1:d528:b4bf:e2ee:e138\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:01:49\"}', '2026-03-26 05:01:49', NULL),
(210, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:eb:f3d1:d528:b4bf:e2ee:e138\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:01:55\"}', '2026-03-26 05:01:55', NULL),
(211, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:19:8eaf:18a0:4ac2:6b7f:95a9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:04:23\"}', '2026-03-26 05:04:23', NULL),
(212, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e8d4:11a:df12:f489\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:05:25\"}', '2026-03-26 05:05:25', NULL),
(213, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e8d4:11a:df12:f489\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:05:33\"}', '2026-03-26 05:05:33', NULL),
(214, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e8d4:11a:df12:f489\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:05:37\"}', '2026-03-26 05:05:37', NULL),
(215, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e8d4:11a:df12:f489\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:05:38\"}', '2026-03-26 05:05:38', NULL),
(216, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 46 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e8d4:11a:df12:f489\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:05:43\"}', '2026-03-26 05:05:43', NULL),
(217, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.102.9.75\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-26 08:11:57\"}', '2026-03-26 05:11:57', NULL),
(218, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"104.28.159.88\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:11:59\"}', '2026-03-26 05:11:59', NULL),
(219, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[js_error] Uncaught Error: Invalid LatLng object: (NaN, NaN) @ https://sarh.io/xml/assets/vendor/leaflet/leaflet.min.js:1', 0, '{\"error_type\":\"js_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:16:20\"}', '2026-03-26 05:16:20', NULL),
(220, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:16:24\"}', '2026-03-26 05:16:24', NULL),
(221, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[js_error] Uncaught IndexSizeError: Failed to execute \'arc\' on \'CanvasRenderingContext2D\': The radius provided (-2) is negative. @ https://sarh.io/xml/assets/js/radar.js?v=1774502171:363', 0, '{\"error_type\":\"js_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.119 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:16:57\"}', '2026-03-26 05:16:57', NULL),
(222, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3f67:2be3:becf:80ad\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:30:46\"}', '2026-03-26 05:30:46', NULL),
(223, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:3f67:2be3:becf:80ad\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 08:31:10\"}', '2026-03-26 05:31:10', NULL),
(224, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 836 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:eb:f3d1:cda1:f041:f542:f81\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-26 12:41:44\"}', '2026-03-26 09:41:44', NULL),
(225, 41, NULL, 'error_report', '⚠️ خطأ: هيثم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 947 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:cf01:8900:9d35:95b:7ae2:523e\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; Redmi 13C Build\\/AP3A.240905.015.A2) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-26 12:54:43\"}', '2026-03-26 09:54:43', NULL),
(226, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.153.127\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-26 12:56:08\"}', '2026-03-26 09:56:08', NULL),
(227, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.153.127\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-26 12:56:13\"}', '2026-03-26 09:56:13', NULL),
(228, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:ce03:d000:ddf9:1771:40da:62da\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 13:19:43\"}', '2026-03-26 10:19:43', NULL),
(229, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:ce03:d000:ddf9:1771:40da:62da\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 13:19:46\"}', '2026-03-26 10:19:46', NULL),
(230, 53, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-26 14:49:32\"}', '2026-03-26 11:49:32', NULL),
(231, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 14:51', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:2c5a::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 14:51:22\"}', '2026-03-26 11:51:22', NULL),
(232, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 14:51', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:2c5a::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 14:51:26\"}', '2026-03-26 11:51:26', NULL),
(233, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 14:51', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84b:2c5a::46b:3f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 14:51:39\"}', '2026-03-26 11:51:39', NULL),
(234, 71, NULL, 'error_report', '⚠️ خطأ: تعظيم', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:eb:f3d1:1c0c:7eb2:cf8b:3433\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-26 16:43:20\"}', '2026-03-26 13:43:20', NULL),
(235, 36, NULL, 'error_report', '⚠️ خطأ: عنايات', '[js_error] Uncaught Error: Invalid LatLng object: (NaN, NaN) @ https://sarh.io/xml/assets/vendor/leaflet/leaflet.min.js:1', 0, '{\"error_type\":\"js_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:27e0:cc0::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 14; en; Infinix X6532 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.120 HiBrowser\\/v2.25.11.1;lang=en;nation=PK;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-27 01:10:35\"}', '2026-03-26 22:10:35', NULL),
(236, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 03:41', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 03:41:30\"}', '2026-03-28 00:41:30', NULL),
(237, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 04:02', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 04:02:25\"}', '2026-03-28 01:02:25', NULL),
(238, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-28 06:42:17\"}', '2026-03-28 03:42:17', NULL),
(239, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 06:42', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:1f60:57e0:e6b1:a148\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 06:42:25\"}', '2026-03-28 03:42:25', NULL),
(240, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:4908:6aa6:a69b:7cc3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-28 07:30:43\"}', '2026-03-28 04:30:43', NULL),
(241, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:4908:6aa6:a69b:7cc3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-28 07:30:59\"}', '2026-03-28 04:30:59', NULL),
(242, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:4908:6aa6:a69b:7cc3\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-28 07:31:10\"}', '2026-03-28 04:31:10', NULL),
(243, 38, NULL, 'error_report', '⚠️ خطأ: شعبان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:2d29:d932:d7dc:826b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:50:54\"}', '2026-03-28 04:50:54', NULL),
(244, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Enable GPS and retry', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::3b7:12\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; en; Infinix X6870 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.120 HiBrowser\\/v2.25.11.1;lang=en;nation=SA;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:50:59\"}', '2026-03-28 04:50:59', NULL),
(245, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Enable GPS and retry', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::3b7:12\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; en; Infinix X6870 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.120 HiBrowser\\/v2.25.11.1;lang=en;nation=SA;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:51:03\"}', '2026-03-28 04:51:03', NULL),
(246, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Enable GPS and retry', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac1:2780:cc0::3b7:12\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 15; en; Infinix X6870 Build\\/SP1A.210812.016) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.7680.120 HiBrowser\\/v2.25.11.1;lang=en;nation=SA;locale=en_US UWS\\/ Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:51:05\"}', '2026-03-28 04:51:05', NULL),
(247, 38, NULL, 'error_report', '⚠️ خطأ: شعبان', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 25 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:2d29:d932:d7dc:826b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:51:36\"}', '2026-03-28 04:51:36', NULL),
(248, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:f48e:9faa:ea0c:36e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:57:15\"}', '2026-03-28 04:57:15', NULL),
(249, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:f48e:9faa:ea0c:36e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:57:25\"}', '2026-03-28 04:57:25', NULL),
(250, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:f48e:9faa:ea0c:36e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 07:57:48\"}', '2026-03-28 04:57:48', NULL),
(251, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"45.121.214.180\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:01:38\"}', '2026-03-28 05:01:38', NULL),
(252, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 80 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c218:e33f:5a2:e996:4676:d25a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:02:11\"}', '2026-03-28 05:02:11', NULL),
(253, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 49 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c218:e33f:5a2:e996:4676:d25a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:02:15\"}', '2026-03-28 05:02:15', NULL),
(254, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84f:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:04:55\"}', '2026-03-28 05:04:55', NULL),
(255, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84f:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:06\"}', '2026-03-28 05:05:06', NULL),
(256, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac6:d84f:2541::3b6:20\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:08\"}', '2026-03-28 05:05:08', NULL),
(257, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:08\"}', '2026-03-28 05:05:08', NULL),
(258, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:10\"}', '2026-03-28 05:05:10', NULL),
(259, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 36 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:14\"}', '2026-03-28 05:05:14', NULL),
(260, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:33\"}', '2026-03-28 05:05:33', NULL),
(261, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:05:52\"}', '2026-03-28 05:05:52', NULL),
(262, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:06:22\"}', '2026-03-28 05:06:22', NULL),
(263, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.228.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:06:23\"}', '2026-03-28 05:06:23', NULL),
(264, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.134\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-28 08:06:48\"}', '2026-03-28 05:06:48', NULL),
(265, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e2d5:e226:8eda:db49\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:07:46\"}', '2026-03-28 05:07:46', NULL),
(266, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 36 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:3ee4:816f:29f1:f453\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:07:57\"}', '2026-03-28 05:07:57', NULL),
(267, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:3c:931b:e2d5:e226:8eda:db49\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:08:03\"}', '2026-03-28 05:08:03', NULL),
(268, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 32 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:57f8:d7f9:831c:6e82\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:09:24\"}', '2026-03-28 05:09:24', NULL),
(269, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:57f8:d7f9:831c:6e82\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:09:30\"}', '2026-03-28 05:09:30', NULL),
(270, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 25 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:3ee4:816f:29f1:f453\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:09:47\"}', '2026-03-28 05:09:47', NULL),
(271, 40, NULL, 'error_report', '⚠️ خطأ: بلال', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.3\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-28 08:18:06\"}', '2026-03-28 05:18:06', NULL),
(272, 40, NULL, 'error_report', '⚠️ خطأ: بلال', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-28 08:18:07\"}', '2026-03-28 05:18:07', NULL),
(273, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 43 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:23:53\"}', '2026-03-28 05:23:53', NULL),
(274, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:23:58\"}', '2026-03-28 05:23:58', NULL),
(275, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:24:03\"}', '2026-03-28 05:24:03', NULL),
(276, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:24:05\"}', '2026-03-28 05:24:05', NULL),
(277, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:24:07\"}', '2026-03-28 05:24:07', NULL),
(278, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:24:10\"}', '2026-03-28 05:24:10', NULL),
(279, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 27 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:cdf6:5cd8:7c9:8da5\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 08:24:14\"}', '2026-03-28 05:24:14', NULL),
(280, 53, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.134\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-28 15:10:25\"}', '2026-03-28 12:10:25', NULL),
(281, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:e1:1978:7be5:623b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-28 22:14:26\"}', '2026-03-28 19:14:26', NULL),
(282, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 00:48', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3213:2541::3b6:33\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 00:48:55\"}', '2026-03-28 21:48:55', NULL),
(283, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"78.95.196.46\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:41:09\"}', '2026-03-29 04:41:09', NULL),
(284, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"78.95.196.46\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:41:37\"}', '2026-03-29 04:41:37', NULL),
(285, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"78.95.196.46\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:41:45\"}', '2026-03-29 04:41:45', NULL),
(286, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"78.95.196.46\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:42:23\"}', '2026-03-29 04:42:23', NULL),
(287, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:44:07\"}', '2026-03-29 04:44:07', NULL),
(288, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:46:52\"}', '2026-03-29 04:46:52', NULL),
(289, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:47:18\"}', '2026-03-29 04:47:18', NULL),
(290, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.7\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 07:47:20\"}', '2026-03-29 04:47:20', NULL),
(291, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:47:32\"}', '2026-03-29 04:47:32', NULL),
(292, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:48:27\"}', '2026-03-29 04:48:27', NULL),
(293, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:48:44\"}', '2026-03-29 04:48:44', NULL),
(294, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:48:55\"}', '2026-03-29 04:48:55', NULL),
(295, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:49:59\"}', '2026-03-29 04:49:59', NULL),
(296, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:50:10\"}', '2026-03-29 04:50:10', NULL),
(297, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:50:28\"}', '2026-03-29 04:50:28', NULL),
(298, 34, NULL, 'error_report', '⚠️ خطأ: ابانوب', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.82.75.14\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:52:58\"}', '2026-03-29 04:52:58', NULL),
(299, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:54:40\"}', '2026-03-29 04:54:40', NULL),
(300, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:54:48\"}', '2026-03-29 04:54:48', NULL),
(301, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:54:53\"}', '2026-03-29 04:54:53', NULL),
(302, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 61 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c03f:c4b2:9408:f45:92da:42c7\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 26_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) CriOS\\/146.0.7680.151 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 07:55:23\"}', '2026-03-29 04:55:23', NULL),
(303, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.7\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 07:57:38\"}', '2026-03-29 04:57:38', NULL),
(304, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:a0c7:1cbc:ab34:4203\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:57:47\"}', '2026-03-29 04:57:47', NULL),
(305, 54, NULL, 'error_report', '⚠️ خطأ: محسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:a0c7:1cbc:ab34:4203\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 07:58:01\"}', '2026-03-29 04:58:01', NULL),
(306, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 07:59:40\"}', '2026-03-29 04:59:40', NULL),
(307, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:ba3b:2b1b:3004:45de\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:03:50\"}', '2026-03-29 05:03:50', NULL),
(308, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:ba3b:2b1b:3004:45de\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:03:56\"}', '2026-03-29 05:03:56', NULL),
(309, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.3\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 08:04:05\"}', '2026-03-29 05:04:05', NULL),
(310, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 39 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:44:2acf:83ad:8d8:fe22:e7f2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:04:08\"}', '2026-03-29 05:04:08', NULL),
(311, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:ba3b:2b1b:3004:45de\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:04:10\"}', '2026-03-29 05:04:10', NULL),
(312, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.102.9.75\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 08:06:24\"}', '2026-03-29 05:06:24', NULL),
(313, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:5430:90d2:4f9f:2b43\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:06:40\"}', '2026-03-29 05:06:40', NULL);
INSERT INTO `notifications` (`id`, `employee_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `data_json`, `created_at`, `read_at`) VALUES
(314, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.135\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-29 08:12:01\"}', '2026-03-29 05:12:01', NULL),
(315, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:11:608b:18a1:36e0:a5ce:ece6\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:16:48\"}', '2026-03-29 05:16:48', NULL),
(316, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[gps_error] GPS timeout', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:11:608b:18a1:36e0:a5ce:ece6\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:17:36\"}', '2026-03-29 05:17:36', NULL),
(317, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:52:54\"}', '2026-03-29 05:52:54', NULL),
(318, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:54:53\"}', '2026-03-29 05:54:53', NULL),
(319, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:49f5:9e02:a254:a7b8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 08:55:18\"}', '2026-03-29 05:55:18', NULL),
(320, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 12:16', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:b961:324a:dff4:55b9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 12:16:02\"}', '2026-03-29 09:16:02', NULL),
(321, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 12:16', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:b961:324a:dff4:55b9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 12:16:27\"}', '2026-03-29 09:16:27', NULL),
(322, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 12:16', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:b961:324a:dff4:55b9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 12:16:32\"}', '2026-03-29 09:16:32', NULL),
(323, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 15:00 إلى 22:00:00. الوقت الحالي: 12:17', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:45:36e9:b961:324a:dff4:55b9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-29 12:17:48\"}', '2026-03-29 09:17:48', NULL),
(324, 26, NULL, 'error_report', '⚠️ خطأ: عبدالله اليمني', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2468 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"77.232.122.129\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-29 23:40:57\"}', '2026-03-29 20:40:57', NULL),
(325, 45, NULL, 'error_report', '⚠️ خطأ: عبدو بوية', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.108.77\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-03-30 07:57:55\"}', '2026-03-30 04:57:55', NULL),
(326, 45, NULL, 'error_report', '⚠️ خطأ: عبدو بوية', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.108.77\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-03-30 07:59:04\"}', '2026-03-30 04:59:04', NULL),
(327, 35, NULL, 'error_report', '⚠️ خطأ: قتيبة', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 07:59:07\"}', '2026-03-30 04:59:07', NULL),
(328, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:9158:5b69:c28c:8b13\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-30 08:03:13\"}', '2026-03-30 05:03:13', NULL),
(329, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:9158:5b69:c28c:8b13\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-30 08:03:39\"}', '2026-03-30 05:03:39', NULL),
(330, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:af:9893:9c44:6365:b735:45aa\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:04:08\"}', '2026-03-30 05:04:08', NULL),
(331, 45, NULL, 'error_report', '⚠️ خطأ: عبدو بوية', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:59bb:5c22:c58a:7433\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:04:10\"}', '2026-03-30 05:04:10', NULL),
(332, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.93.1\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 08:04:12\"}', '2026-03-30 05:04:12', NULL),
(333, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:9158:5b69:c28c:8b13\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-30 08:05:15\"}', '2026-03-30 05:05:15', NULL),
(334, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 178 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:5d:d9d3:18a1:856d:1309:59c4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:10:44\"}', '2026-03-30 05:10:44', NULL),
(335, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 42 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:e4c7:d189:66b3:54e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:23\"}', '2026-03-30 05:11:23', NULL),
(336, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 30 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:e4c7:d189:66b3:54e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:32\"}', '2026-03-30 05:11:32', NULL),
(337, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:e4c7:d189:66b3:54e4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:38\"}', '2026-03-30 05:11:38', NULL),
(338, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.134\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 08:11:52\"}', '2026-03-30 05:11:52', NULL),
(339, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:53\"}', '2026-03-30 05:11:53', NULL),
(340, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:55\"}', '2026-03-30 05:11:55', NULL),
(341, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 35 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:11:57\"}', '2026-03-30 05:11:57', NULL),
(342, 48, NULL, 'error_report', '⚠️ خطأ: ابو حازم', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.83.101.200\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:12:35\"}', '2026-03-30 05:12:35', NULL),
(343, 48, NULL, 'error_report', '⚠️ خطأ: ابو حازم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 2127 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.83.101.200\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:12:41\"}', '2026-03-30 05:12:41', NULL),
(344, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.131\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 08:15:15\"}', '2026-03-30 05:15:15', NULL),
(345, 3, NULL, 'error_report', '⚠️ خطأ: بخاري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 146 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a09:bac5:3215:2c5a::46b:76\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:15:19\"}', '2026-03-30 05:15:19', NULL),
(346, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3cb5:a60b:5159:2854\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:16:31\"}', '2026-03-30 05:16:31', NULL),
(347, 40, NULL, 'error_report', '⚠️ خطأ: بلال', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.129\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 08:19:05\"}', '2026-03-30 05:19:05', NULL),
(348, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:e1d9:8412:e665:3733\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:19:35\"}', '2026-03-30 05:19:35', NULL),
(349, 24, NULL, 'error_report', '⚠️ خطأ: محمد خميس', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:9d2f:35f6:f732:2bf3\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:22:29\"}', '2026-03-30 05:22:29', NULL),
(350, 48, NULL, 'error_report', '⚠️ خطأ: ابو حازم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 33 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.83.101.200\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:26:59\"}', '2026-03-30 05:26:59', NULL),
(351, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:48:49\"}', '2026-03-30 05:48:49', NULL),
(352, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.131\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 08:48:51\"}', '2026-03-30 05:48:51', NULL),
(353, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:05\"}', '2026-03-30 05:49:05', NULL),
(354, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:09\"}', '2026-03-30 05:49:09', NULL),
(355, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:23\"}', '2026-03-30 05:49:23', NULL),
(356, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:24\"}', '2026-03-30 05:49:24', NULL),
(357, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:28\"}', '2026-03-30 05:49:28', NULL),
(358, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:31\"}', '2026-03-30 05:49:31', NULL),
(359, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:32\"}', '2026-03-30 05:49:32', NULL),
(360, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:48\"}', '2026-03-30 05:49:48', NULL),
(361, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:49:53\"}', '2026-03-30 05:49:53', NULL),
(362, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:50:18\"}', '2026-03-30 05:50:18', NULL),
(363, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:50:25\"}', '2026-03-30 05:50:25', NULL),
(364, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:51:28\"}', '2026-03-30 05:51:28', NULL),
(365, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 08:53:07\"}', '2026-03-30 05:53:07', NULL),
(366, 16, NULL, 'error_report', '⚠️ خطأ: أندريس بورتس', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:ca89:e65d:25ae:50fc\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:00:09\"}', '2026-03-30 06:00:09', NULL),
(367, 16, NULL, 'error_report', '⚠️ خطأ: أندريس بورتس', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.135\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 09:00:13\"}', '2026-03-30 06:00:13', NULL),
(368, 16, NULL, 'error_report', '⚠️ خطأ: أندريس بورتس', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:ca89:e65d:25ae:50fc\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:00:24\"}', '2026-03-30 06:00:24', NULL),
(369, 16, NULL, 'error_report', '⚠️ خطأ: أندريس بورتس', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:ca89:e65d:25ae:50fc\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:00:36\"}', '2026-03-30 06:00:36', NULL),
(370, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:04:37\"}', '2026-03-30 06:04:37', NULL),
(371, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:05:07\"}', '2026-03-30 06:05:07', NULL),
(372, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:05:22\"}', '2026-03-30 06:05:22', NULL),
(373, 73, NULL, 'error_report', '⚠️ خطأ: tirachot', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.133\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 09:05:24\"}', '2026-03-30 06:05:24', NULL),
(374, 17, NULL, 'error_report', '⚠️ خطأ: حسن (آصف)', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:3480:63fa:ccf:e37a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-30 09:05:49\"}', '2026-03-30 06:05:49', NULL),
(375, 12, NULL, 'error_report', '⚠️ خطأ: رمضان عباس علي', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.134\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 09:05:58\"}', '2026-03-30 06:05:58', NULL),
(376, 12, NULL, 'error_report', '⚠️ خطأ: رمضان عباس علي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.129\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 09:05:58\"}', '2026-03-30 06:05:58', NULL),
(377, 21, NULL, 'error_report', '⚠️ خطأ: منذر محمد', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.108.77\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-30 09:10:45\"}', '2026-03-30 06:10:45', NULL),
(378, 21, NULL, 'error_report', '⚠️ خطأ: منذر محمد', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.108.77\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-30 09:12:07\"}', '2026-03-30 06:12:07', NULL),
(379, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:1178:2268:bb15:8cb2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-30 10:15:52\"}', '2026-03-30 07:15:52', NULL),
(380, 53, NULL, 'error_report', '⚠️ خطأ: وسيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.129\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 14:59:02\"}', '2026-03-30 11:59:02', NULL),
(381, 72, NULL, 'error_report', '⚠️ خطأ: nykoon', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-30 20:02:19\"}', '2026-03-30 17:02:19', NULL),
(382, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 06:36:31\"}', '2026-03-31 03:36:31', NULL),
(383, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 06:36', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 06:36:43\"}', '2026-03-31 03:36:43', NULL),
(384, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[attendance_fail] تسجيل الحضور متاح من 07:10 إلى 12:00:00. الوقت الحالي: 06:36', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 06:36:54\"}', '2026-03-31 03:36:54', NULL),
(385, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 820 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.43.131.182\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 07:49:28\"}', '2026-03-31 04:49:28', NULL),
(386, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 07:57:18\"}', '2026-03-31 04:57:18', NULL),
(387, 73, NULL, 'error_report', '⚠️ خطأ: tirachot', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 26 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:91df:1640:9b1f:13af\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:02:15\"}', '2026-03-31 05:02:15', NULL),
(388, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:67:395f:6cbb:17ff:fe9f:b0d6\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:04:31\"}', '2026-03-31 05:04:31', NULL),
(389, 72, NULL, 'error_report', '⚠️ خطأ: nykoon', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:1d58:3013:eead:c3e9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:04:39\"}', '2026-03-31 05:04:39', NULL),
(390, 11, NULL, 'error_report', '⚠️ خطأ: محمد بلال', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:d3ef:2044:db7a:4cba\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:04:52\"}', '2026-03-31 05:04:52', NULL),
(391, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.4\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-31 08:04:59\"}', '2026-03-31 05:04:59', NULL),
(392, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 54 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:05:47\"}', '2026-03-31 05:05:47', NULL),
(393, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 54 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:05:49\"}', '2026-03-31 05:05:49', NULL),
(394, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 152 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:83:71d7:14a8:280e:bb3e:91ca\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-31 08:06:07\"}', '2026-03-31 05:06:07', NULL),
(395, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_network] Connection error', 0, '{\"error_type\":\"attendance_network\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:09:11\"}', '2026-03-31 05:09:11', NULL),
(396, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:99b8:3e9a:2552:a83f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 08:09:39\"}', '2026-03-31 05:09:39', NULL),
(397, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:99b8:3e9a:2552:a83f\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 08:10:14\"}', '2026-03-31 05:10:14', NULL),
(398, 34, NULL, 'error_report', '⚠️ خطأ: ابانوب', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-31 08:10:14\"}', '2026-03-31 05:10:14', NULL),
(399, 34, NULL, 'error_report', '⚠️ خطأ: ابانوب', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.136\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-31 08:10:15\"}', '2026-03-31 05:10:15', NULL),
(400, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:12:29\"}', '2026-03-31 05:12:29', NULL),
(401, 70, NULL, 'error_report', '⚠️ خطأ: شاهد', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"37.125.238.175\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:12:31\"}', '2026-03-31 05:12:31', NULL),
(402, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:17b1:8373:b195:260e\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:15:11\"}', '2026-03-31 05:15:11', NULL),
(403, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 80 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:17b1:8373:b195:260e\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:15:25\"}', '2026-03-31 05:15:25', NULL),
(404, 68, NULL, 'error_report', '⚠️ خطأ: ياسر', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.128\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-31 08:17:41\"}', '2026-03-31 05:17:41', NULL),
(405, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 122 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:559e:34f7:af92:4633\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 08:18:02\"}', '2026-03-31 05:18:02', NULL),
(406, 11, NULL, 'error_report', '⚠️ خطأ: محمد بلال', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:6411:65ec:8f86:95f7\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:01:11\"}', '2026-03-31 11:01:11', NULL),
(407, 73, NULL, 'error_report', '⚠️ خطأ: tirachot', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 26 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:dc8:1c42:37a0:5471\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:01:17\"}', '2026-03-31 11:01:17', NULL),
(408, 12, NULL, 'error_report', '⚠️ خطأ: رمضان عباس علي', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 41 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:40e2:b471:837d:14f0\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:01:31\"}', '2026-03-31 11:01:31', NULL),
(409, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.133\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-03-31 14:15:05\"}', '2026-03-31 11:15:05', NULL),
(410, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:d9fd:bbee:4854:b674\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:15:30\"}', '2026-03-31 11:15:30', NULL),
(411, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:d9fd:bbee:4854:b674\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:15:31\"}', '2026-03-31 11:15:31', NULL),
(412, 24, NULL, 'error_report', '⚠️ خطأ: محمد خميس', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:6103:de2c:3c21:e86c\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/27.0 Chrome\\/125.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 14:15:32\"}', '2026-03-31 11:15:32', NULL),
(413, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:38f9:fbbf:4c26:810f\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36 Edg\\/146.0.0.0\",\"timestamp\":\"2026-03-31 14:17:21\"}', '2026-03-31 11:17:21', NULL),
(414, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 151 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:38f9:fbbf:4c26:810f\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Safari\\/537.36 Edg\\/146.0.0.0\",\"timestamp\":\"2026-03-31 14:17:46\"}', '2026-03-31 11:17:46', NULL),
(415, 44, NULL, 'error_report', '⚠️ خطأ: خيري', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:217b:83:ef0f:7d8\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/26.3 Mobile\\/15E148 Safari\\/604.1\",\"timestamp\":\"2026-03-31 15:14:07\"}', '2026-03-31 12:14:07', NULL),
(416, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:17\"}', '2026-03-31 12:15:17', NULL),
(417, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:18\"}', '2026-03-31 12:15:18', NULL),
(418, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:32\"}', '2026-03-31 12:15:32', NULL),
(419, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:33\"}', '2026-03-31 12:15:33', NULL),
(420, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:35\"}', '2026-03-31 12:15:35', NULL);
INSERT INTO `notifications` (`id`, `employee_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `data_json`, `created_at`, `read_at`) VALUES
(421, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:36\"}', '2026-03-31 12:15:36', NULL),
(422, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_submit_fail] تعذّر تحديد موقعك', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:39\"}', '2026-03-31 12:15:39', NULL),
(423, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 11; Redmi Note 8 Pro Build\\/RP1A.200720.011) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/135.0.7049.79 Mobile Safari\\/537.36 XiaoMi\\/MiuiBrowser\\/14.52.2-gn\",\"timestamp\":\"2026-03-31 15:15:39\"}', '2026-03-31 12:15:39', NULL),
(424, 47, NULL, 'error_report', '⚠️ خطأ: حسن', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 30 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:ee:e9df:4d67:a64:db5:6d19\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 15:16:16\"}', '2026-03-31 12:16:16', NULL),
(425, 4, NULL, 'error_report', '⚠️ خطأ: أبو سليمان', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:f2:982e:53d9:4c0e:97ea:b29a\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 18:08:32\"}', '2026-03-31 15:08:32', NULL),
(426, 1, NULL, 'error_report', '⚠️ خطأ: إسلام', '[gps_error] انتهت مهلة GPS', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-03-31 22:02:40\"}', '2026-03-31 19:02:40', NULL),
(427, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.134\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 07:47:05\"}', '2026-04-01 04:47:05', NULL),
(428, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 41 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:27:140e:b8c7:a814:d79f:2b17\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 07:47:09\"}', '2026-04-01 04:47:09', NULL),
(429, 67, NULL, 'error_report', '⚠️ خطأ: ابو بشير', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 36 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:27:140e:b8c7:a814:d79f:2b17\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 07:49:14\"}', '2026-04-01 04:49:14', NULL),
(430, 4, NULL, 'error_report', '⚠️ خطأ: أبو سليمان', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:27:140e:7503:bea8:21ee:d7cc\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 07:49:58\"}', '2026-04-01 04:49:58', NULL),
(431, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_submit_fail] Could not get location', 0, '{\"error_type\":\"gps_submit_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.2\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 07:56:31\"}', '2026-04-01 04:56:31', NULL),
(432, 52, NULL, 'error_report', '⚠️ خطأ: عبدالله احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.9\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 07:56:32\"}', '2026-04-01 04:56:32', NULL),
(433, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:23:dbe8:5552:9b8d:abad:458e\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 07:58:50\"}', '2026-04-01 04:58:50', NULL),
(434, 51, NULL, 'error_report', '⚠️ خطأ: ابراهيم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 28 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:23:dbe8:5552:9b8d:abad:458e\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 07:58:54\"}', '2026-04-01 04:58:54', NULL),
(435, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 07:59:54\"}', '2026-04-01 04:59:54', NULL),
(436, 31, NULL, 'error_report', '⚠️ خطأ: عرفان', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:00:14\"}', '2026-04-01 05:00:14', NULL),
(437, 46, NULL, 'error_report', '⚠️ خطأ: احمد', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a4:83:fc92:ddd8:3b1f:60bd:19a0\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:01:18\"}', '2026-04-01 05:01:18', NULL),
(438, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_error] اسمح بالموقع من الإعدادات', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:23:dbe8:5a13:f2f8:c751:1699\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:02:56\"}', '2026-04-01 05:02:56', NULL),
(439, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"142.250.32.8\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 08:02:59\"}', '2026-04-01 05:02:59', NULL),
(440, 10, NULL, 'error_report', '⚠️ خطأ: محمد جلال', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 31 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:23:dbe8:5a13:f2f8:c751:1699\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:03:05\"}', '2026-04-01 05:03:05', NULL),
(441, 29, NULL, 'error_report', '⚠️ خطأ: إمتي', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:b3:dbca:8c5f:ed72:f1d3:2ac7\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:09:47\"}', '2026-04-01 05:09:47', NULL),
(442, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 55 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:09:50\"}', '2026-04-01 05:09:50', NULL),
(443, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 55 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:09:52\"}', '2026-04-01 05:09:52', NULL),
(444, 43, NULL, 'error_report', '⚠️ خطأ: صهيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 391 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2001:16a2:c07d:84db:1:2:8ef1:e99d\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) SamsungBrowser\\/29.0 Chrome\\/136.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:10:11\"}', '2026-04-01 05:10:11', NULL),
(445, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:10:28\"}', '2026-04-01 05:10:28', NULL),
(446, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:10:30\"}', '2026-04-01 05:10:30', NULL),
(447, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:10:49\"}', '2026-04-01 05:10:49', NULL),
(448, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"176.17.210.99\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:11:20\"}', '2026-04-01 05:11:20', NULL),
(449, 28, NULL, 'error_report', '⚠️ خطأ: حبيب', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 37 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:27:140e:1828:c6f8:37be:fb1b\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:11:31\"}', '2026-04-01 05:11:31', NULL),
(450, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[gps_error] Allow location in settings', 0, '{\"error_type\":\"gps_error\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"66.249.83.137\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Mobile Safari\\/537.36 (compatible; Google-Read-Aloud; +https:\\/\\/support.google.com\\/webmasters\\/answer\\/1061943)\",\"timestamp\":\"2026-04-01 08:14:06\"}', '2026-04-01 05:14:06', NULL),
(451, 49, NULL, 'error_report', '⚠️ خطأ: ابو يحيى', '[attendance_fail] تم التسجيل مسبقاً خلال آخر 5 دقائق', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"2a02:9b0:23:dbe8:68db:7eb9:8a6c:74a0\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:14:08\"}', '2026-04-01 05:14:08', NULL),
(452, 48, NULL, 'error_report', '⚠️ خطأ: ابو حازم', '[attendance_fail] أنت خارج نطاق العمل! المسافة: 29 متر (الحد المسموح: 25 متر)', 0, '{\"error_type\":\"attendance_fail\",\"page\":\"\\/xml\\/employee\\/attendance.php\",\"ip\":\"109.83.101.200\",\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/146.0.0.0 Mobile Safari\\/537.36\",\"timestamp\":\"2026-04-01 08:30:07\"}', '2026-04-01 05:30:07', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `secret_reports`
--

CREATE TABLE `secret_reports` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `report_text` text DEFAULT NULL,
  `report_type` varchar(50) NOT NULL DEFAULT 'violation',
  `image_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_paths`)),
  `has_image` tinyint(1) DEFAULT 0,
  `image_path` varchar(500) DEFAULT NULL,
  `has_voice` tinyint(1) DEFAULT 0,
  `voice_path` varchar(500) DEFAULT NULL,
  `voice_effect` varchar(20) DEFAULT NULL,
  `status` enum('new','reviewed','in_progress','resolved','dismissed','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`) VALUES
(1, 'work_latitude', '24.572307', 'خط عرض موقع العمل'),
(2, 'work_longitude', '46.602552', 'خط طول موقع العمل'),
(3, 'geofence_radius', '25', 'نصف قطر الجيوفينس بالمتر'),
(4, 'work_start_time', '20:00', 'بداية الدوام الرسمي'),
(5, 'work_end_time', '23:55', 'نهاية الدوام الرسمي'),
(6, 'check_in_start_time', '07:00', 'بداية وقت تسجيل الدخول'),
(7, 'check_in_end_time', '23:00', 'نهاية وقت تسجيل الدخول'),
(8, 'check_out_start_time', '23:20', 'بداية وقت تسجيل الانصراف'),
(9, 'check_out_end_time', '23:55', 'نهاية وقت تسجيل الانصراف'),
(10, 'checkout_show_before', '1', 'دقائق قبل إظهار زر الانصراف'),
(11, 'allow_overtime', '1', 'السماح بالدوام الإضافي'),
(12, 'overtime_start_after', '60', 'دقائق بعد نهاية الدوام لبدء الإضافي'),
(13, 'overtime_min_duration', '30', 'الحد الأدنى للدوام الإضافي بالدقائق'),
(14, 'site_name', 'نظام الحضور والانصراف', 'اسم النظام'),
(15, 'company_name', '', 'اسم الشركة'),
(16, 'timezone', 'Asia/Riyadh', 'المنطقة الزمنية');

-- --------------------------------------------------------

--
-- بنية الجدول `tampering_cases`
--

CREATE TABLE `tampering_cases` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `case_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `severity` enum('low','medium','high') DEFAULT 'medium',
  `details_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `pref_key` varchar(50) NOT NULL,
  `pref_value` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- فهارس للجدول `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- فهارس للجدول `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_date` (`employee_id`,`timestamp`),
  ADD KEY `idx_type_date` (`type`,`timestamp`),
  ADD KEY `idx_attendance_date` (`attendance_date`);

--
-- فهارس للجدول `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- فهارس للجدول `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- فهارس للجدول `branch_shifts`
--
ALTER TABLE `branch_shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_branch_shift` (`branch_id`,`shift_number`);

--
-- فهارس للجدول `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pin` (`pin`),
  ADD UNIQUE KEY `unique_token` (`unique_token`),
  ADD KEY `branch_id` (`branch_id`);

--
-- فهارس للجدول `emp_document_files`
--
ALTER TABLE `emp_document_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_group` (`group_id`);

--
-- فهارس للجدول `emp_document_groups`
--
ALTER TABLE `emp_document_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_emp` (`employee_id`),
  ADD KEY `idx_exp` (`expiry_date`);

--
-- فهارس للجدول `known_devices`
--
ALTER TABLE `known_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fp_emp` (`fingerprint`,`employee_id`),
  ADD KEY `idx_fp` (`fingerprint`),
  ADD KEY `idx_emp` (`employee_id`);

--
-- فهارس للجدول `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_employee_dates` (`employee_id`,`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- فهارس للجدول `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempted_at`);

--
-- فهارس للجدول `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- فهارس للجدول `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_type_read` (`type`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- فهارس للجدول `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`);

--
-- فهارس للجدول `secret_reports`
--
ALTER TABLE `secret_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- فهارس للجدول `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- فهارس للجدول `tampering_cases`
--
ALTER TABLE `tampering_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_type` (`case_type`),
  ADD KEY `idx_date` (`attendance_date`),
  ADD KEY `idx_created` (`created_at`);

--
-- فهارس للجدول `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admin_pref` (`admin_id`,`pref_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `branch_shifts`
--
ALTER TABLE `branch_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `emp_document_files`
--
ALTER TABLE `emp_document_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `emp_document_groups`
--
ALTER TABLE `emp_document_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `known_devices`
--
ALTER TABLE `known_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1252;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=453;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secret_reports`
--
ALTER TABLE `secret_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tampering_cases`
--
ALTER TABLE `tampering_cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- القيود المفروضة على الجداول الملقاة
--

--
-- قيود الجداول `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `branch_shifts`
--
ALTER TABLE `branch_shifts`
  ADD CONSTRAINT `branch_shifts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `known_devices`
--
ALTER TABLE `known_devices`
  ADD CONSTRAINT `known_devices_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaves_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `secret_reports`
--
ALTER TABLE `secret_reports`
  ADD CONSTRAINT `secret_reports_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `tampering_cases`
--
ALTER TABLE `tampering_cases`
  ADD CONSTRAINT `tampering_cases_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
