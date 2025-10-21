-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 01:30 AM
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
-- Database: `abunetdg_fundraising_local`
--
CREATE DATABASE IF NOT EXISTS `abunetdg_fundraising_local` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `abunetdg_fundraising_local`;

-- --------------------------------------------------------

--
-- Table structure for table `ajax_request_log`
--

CREATE TABLE `ajax_request_log` (
  `id` int(11) NOT NULL,
  `request_uuid` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `before_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_json`)),
  `after_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_json`)),
  `ip_address` varbinary(16) DEFAULT NULL,
  `source` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `before_json`, `after_json`, `ip_address`, `source`, `created_at`) VALUES
(1, 6, 'pledge', 1, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Samia Ahmed\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:28:45'),
(2, 6, 'pledge', 2, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Degole seboka\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:29:43'),
(3, 6, 'pledge', 3, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Deborah Seboka\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:30:40'),
(4, 6, 'pledge', 4, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Dinah Seboka\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:31:45'),
(5, 6, 'pledge', 5, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Daniella Seboka\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:32:23'),
(6, 64, 'pledge', 6, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Estemareyam\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:34:39'),
(7, 68, 'payment', 1, 'create_pending', NULL, '{\"amount\":5,\"method\":\"cash\",\"donor\":\"Yeshiwork Berihun\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:36:52'),
(8, 64, 'pledge', 7, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Saron\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:41:47'),
(9, 64, 'pledge', 8, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Samerawt\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:42:57'),
(10, 3, 'pledge', 1, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-185\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:44:29'),
(11, 3, 'pledge', 2, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-186\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:44:40'),
(12, 3, 'pledge', 3, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-187\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:44:53'),
(13, 3, 'pledge', 4, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-188\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:44:55'),
(14, 3, 'pledge', 5, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-189\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:44:59'),
(15, 3, 'pledge', 6, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:45:00'),
(16, 3, 'pledge', 7, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":5,\"cells\":[\"A0505-190\"],\"total_contributors\":1}],\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:45:03'),
(17, 3, 'pledge', 8, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:45:07'),
(18, 54, 'pledge', 9, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:47:11'),
(19, 3, 'pledge', 9, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-191\",\"A0505-192\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:47:24'),
(20, 68, 'pledge', 10, 'create_pending', NULL, '{\"amount\":10,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Elsabet mitku\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:47:36'),
(21, 72, 'payment', 2, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Robel kifle\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:47:42'),
(22, 3, 'pledge', 10, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":10}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a310 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:48:00'),
(23, 61, 'payment', 3, 'create_pending', NULL, '{\"amount\":40,\"method\":\"cash\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:08'),
(24, 6, 'pledge', 11, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Abera\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:33'),
(25, 70, 'payment', 4, 'create_pending', NULL, '{\"amount\":10,\"method\":\"card\",\"donor\":\"Mikael Kifle\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:33'),
(26, 62, 'pledge', 12, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Messing Aregay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:46'),
(27, 49, 'pledge', 13, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Frehiwot Tadese\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:48'),
(28, 60, 'payment', 5, 'create_pending', NULL, '{\"amount\":400,\"method\":\"bank\",\"donor\":\"Mulubrhan s jemaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:48'),
(29, 54, 'pledge', 14, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Mekdese mari yam\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:56'),
(30, 55, 'pledge', 15, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Ghirmay Reda\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:48:57'),
(31, 72, 'payment', 6, 'create_pending', NULL, '{\"amount\":20,\"method\":\"card\",\"donor\":\"Lucas Zewdie\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:06'),
(32, 67, 'pledge', 16, 'create_pending', NULL, '{\"amount\":30,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:15'),
(33, 56, 'payment', 7, 'create_pending', NULL, '{\"amount\":50,\"method\":\"card\",\"donor\":\"Aron\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:20'),
(34, 52, 'pledge', 17, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Serkalem Molla\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:29'),
(35, 3, 'pledge', 11, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-195\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:49:30'),
(36, 70, 'pledge', 18, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Daniel\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:40'),
(37, 3, 'pledge', 12, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-196\",\"A0505-197\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:49:45'),
(38, 64, 'pledge', 19, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Esake\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:49:53'),
(39, 3, 'pledge', 13, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-198\",\"A0505-199\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:01'),
(40, 3, 'pledge', 14, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-204\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:07'),
(41, 54, 'pledge', 20, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Hanna\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:13'),
(42, 3, 'pledge', 15, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:14'),
(43, 3, 'pledge', 16, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":30}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a330 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":15,\"cells\":[\"A0505-205\"],\"total_contributors\":1}],\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:18'),
(44, 73, 'pledge', 21, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Dawit brhane\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:19'),
(45, 57, 'pledge', 22, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Kidist Shewandagn\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:37'),
(46, 6, 'pledge', 23, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Mulutsega Girma\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:39'),
(47, 3, 'pledge', 17, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-206\",\"A0505-207\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:39'),
(48, 55, 'pledge', 24, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Solomon\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:43'),
(49, 60, 'payment', 8, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Daniel Samuel\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:50:47'),
(50, 3, 'pledge', 18, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-208\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:58'),
(51, 3, 'pledge', 19, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":15,\"cells\":[\"A0505-209\"],\"total_contributors\":1}],\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:50:59'),
(52, 3, 'pledge', 20, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-210\",\"A0505-211\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:51:03'),
(53, 3, 'pledge', 21, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-212\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:51:04'),
(54, 3, 'pledge', 22, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-213\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:51:05'),
(55, 3, 'pledge', 23, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-214\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:51:07'),
(56, 3, 'pledge', 24, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-215\",\"A0505-216\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:51:09'),
(57, 54, 'pledge', 25, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Eden\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:10'),
(58, 67, 'pledge', 26, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yohannes Aderajw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:20'),
(59, 60, 'payment', 9, 'create_pending', NULL, '{\"amount\":200,\"method\":\"bank\",\"donor\":\"Tesfaye Tessema\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:36'),
(60, 56, 'pledge', 27, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Brook\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:36'),
(61, 68, 'pledge', 28, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Banchi negash\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:53'),
(62, 69, 'payment', 10, 'create_pending', NULL, '{\"amount\":50,\"method\":\"card\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:51:54'),
(63, 1, 'pledge', 29, 'create_pending', NULL, '{\"amount\":700,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:52:10'),
(64, 6, 'pledge', 30, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Demelash Banjaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:52:29'),
(65, 3, 'pledge', 25, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-218\",\"A0505-219\",\"A0505-220\",\"A0505-221\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:31'),
(66, 3, 'pledge', 26, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:32'),
(67, 3, 'pledge', 27, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-222\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:34'),
(68, 73, 'payment', 11, 'create_pending', NULL, '{\"amount\":5,\"method\":\"card\",\"donor\":\"Cleve brown\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:52:35'),
(69, 3, 'pledge', 28, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-225\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:35'),
(70, 3, 'pledge', 29, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":700}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3700\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":300,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-227\",\"A0505-228\",\"A0505-229\",\"A0505-230\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:38'),
(71, 3, 'pledge', 30, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-231\",\"A0505-232\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:52:39'),
(72, 56, 'pledge', 31, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Boja brook\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:52:50'),
(73, 54, 'payment', 12, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Manchester\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:52:53'),
(74, 60, 'payment', 13, 'create_pending', NULL, '{\"amount\":400,\"method\":\"bank\",\"donor\":\"Samsung Taye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:53:04'),
(75, 55, 'pledge', 32, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Abraham Gobeze\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:53:11'),
(76, 3, 'pledge', 31, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-235\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:53:19'),
(77, 72, 'pledge', 33, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"ALEX ASHENAFI (GLASGOW)\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:53:21'),
(78, 6, 'pledge', 34, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yoseph\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:53:28'),
(79, 65, 'payment', 14, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Bamlak and yannick\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:53:33'),
(80, 3, 'pledge', 32, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-240\",\"A0505-241\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:53:42'),
(81, 3, 'pledge', 33, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-242\",\"A0505-243\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:53:56'),
(82, 3, 'pledge', 34, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-244\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:54:21'),
(83, 49, 'payment', 15, 'create_pending', NULL, '{\"amount\":250,\"method\":\"cash\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:54:32'),
(84, 54, 'pledge', 35, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Maza\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:54:44'),
(85, 66, 'payment', 16, 'create_pending', NULL, '{\"amount\":100,\"method\":\"cash\",\"donor\":\"Aderagw tadele\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:54:58'),
(86, 57, 'payment', 17, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Haseit Desta\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:15'),
(87, 67, 'pledge', 36, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Bruk moges\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:15'),
(88, 60, 'payment', 18, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Kibrom teklu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:17'),
(89, 52, 'payment', 19, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Rahel G\\/selase\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:19'),
(90, 3, 'pledge', 35, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-248\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:55:27'),
(91, 56, 'pledge', 37, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Amen Hailesillase\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:31'),
(92, 3, 'pledge', 36, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-250\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:55:36'),
(93, 55, 'payment', 20, 'create_pending', NULL, '{\"amount\":200,\"method\":\"card\",\"donor\":\"Faniel Tena Gashaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:55:47'),
(94, 68, 'pledge', 38, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Soltana tekle\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:03'),
(95, 72, 'pledge', 39, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Wagi\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:03'),
(96, 61, 'pledge', 40, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:14'),
(97, 3, 'pledge', 37, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-254\",\"A0505-255\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:56:14'),
(98, 3, 'pledge', 38, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-258\",\"A0505-259\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:56:34'),
(99, 60, 'payment', 21, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Efrem\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:35'),
(100, 57, 'pledge', 41, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Senile Gebeeyes\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:38'),
(101, 3, 'pledge', 39, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-260\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:56:40'),
(102, 3, 'pledge', 40, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-261\",\"A0505-262\",\"A0505-263\",\"A0505-264\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:56:47'),
(103, 56, 'pledge', 42, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Nardos\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:51'),
(104, 49, 'payment', 22, 'create_pending', NULL, '{\"amount\":100,\"method\":\"cash\",\"donor\":\"Bayush Kumssa\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:56:57'),
(105, 3, 'pledge', 41, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-266\",\"A0505-267\",\"A0505-268\",\"A0505-269\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:57:03'),
(106, 73, 'payment', 23, 'create_pending', NULL, '{\"amount\":20,\"method\":\"card\",\"donor\":\"Wonwossen redie\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:05'),
(107, 54, 'payment', 24, 'create_pending', NULL, '{\"amount\":100,\"method\":\"cash\",\"donor\":\"Glasgow\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:12'),
(108, 67, 'payment', 25, 'create_pending', NULL, '{\"amount\":30,\"method\":\"cash\",\"donor\":\"Mesfn lodamo\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:17'),
(109, 3, 'pledge', 42, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-270\",\"A0505-271\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:57:19'),
(110, 64, 'payment', 26, 'create_pending', NULL, '{\"amount\":200,\"method\":\"bank\",\"donor\":\"Alemayew hayelu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:34'),
(111, 68, 'pledge', 43, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Adiyam sahle\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:37'),
(112, 70, 'pledge', 44, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Dr Getinet Mekuriaw Tarekegn Glasgow\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:54'),
(113, 57, 'pledge', 45, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Genet Sebehatu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:57:54'),
(114, 6, 'payment', 27, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Yonas abraham\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:58:05'),
(115, 72, 'pledge', 46, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Abebe\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:58:12'),
(116, 3, 'pledge', 43, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-277\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:58:26'),
(117, 3, 'pledge', 44, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-278\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:58:32'),
(118, 3, 'pledge', 45, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-279\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:58:35'),
(119, 62, 'payment', 28, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Fasil Kinde\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:58:37'),
(120, 61, 'pledge', 47, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Elias Hailgebreil\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:58:55'),
(121, 3, 'pledge', 46, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-281\",\"A0505-282\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:59:02'),
(122, 3, 'pledge', 47, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-284\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 08:59:11'),
(123, 65, 'pledge', 48, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Meseret Yohannes\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:14'),
(124, 66, 'pledge', 49, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yakob tesfaye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:15'),
(125, 57, 'pledge', 50, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Damtew ashenafi\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:22'),
(126, 67, 'payment', 29, 'create_pending', NULL, '{\"amount\":20,\"method\":\"cash\",\"donor\":\"Amanuel belay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:29'),
(127, 70, 'pledge', 51, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Behailu Tihitina\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:51'),
(128, 72, 'pledge', 52, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Dawit weledkiros\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:52'),
(129, 6, 'pledge', 53, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"TEWODROS TADESSE\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 08:59:53'),
(130, 73, 'payment', 30, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Mikael teshome\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:02'),
(131, 55, 'payment', 31, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Birhanu Yemenu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:05'),
(132, 71, 'pledge', 54, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:05'),
(133, 52, 'pledge', 55, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:08'),
(134, 60, 'pledge', 56, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Kaletsidk Fasil\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:08'),
(135, 3, 'pledge', 48, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-285\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:09'),
(136, 3, 'pledge', 49, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-286\",\"A0505-287\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:16'),
(137, 3, 'pledge', 50, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-288\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:23'),
(138, 49, 'payment', 32, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Fithi Teklit\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:29'),
(139, 56, 'pledge', 57, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Chemeka\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:37'),
(140, 55, 'payment', 33, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Kesis Mezmur\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:38'),
(141, 3, 'pledge', 51, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-290\",\"A0505-291\",\"A0505-292\",\"A0505-293\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:38'),
(142, 57, 'pledge', 58, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Ehete maream\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:40'),
(143, 64, 'pledge', 59, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Aberham\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:00:49'),
(144, 3, 'pledge', 52, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-295\",\"A0505-296\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:54'),
(145, 3, 'pledge', 53, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-297\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:00:55'),
(146, 3, 'pledge', 54, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-299\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:03'),
(147, 3, 'pledge', 55, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-301\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:12'),
(148, 3, 'pledge', 56, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-302\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:16'),
(149, 62, 'pledge', 60, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Hundaftol Yohannes\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:01:18'),
(150, 3, 'pledge', 57, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-304\",\"A0505-305\",\"A0505-306\",\"A0505-307\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:25'),
(151, 6, 'pledge', 61, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Ephrem Retta\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:01:31'),
(152, 3, 'pledge', 58, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-309\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:34'),
(153, 3, 'pledge', 59, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-310\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:38'),
(154, 3, 'pledge', 60, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-311\",\"A0505-312\",\"A0505-313\",\"A0505-314\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:45'),
(155, 68, 'pledge', 62, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Freweyni mekonen\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:01:48'),
(156, 3, 'pledge', 61, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-315\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:51');
INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `before_json`, `after_json`, `ip_address`, `source`, `created_at`) VALUES
(157, 72, 'payment', 34, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Mahari melash\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:01:51'),
(158, 3, 'pledge', 62, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-316\",\"A0505-317\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:01:56'),
(159, 48, 'pledge', 63, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Esubalew\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:01:57'),
(160, 66, 'payment', 35, 'create_pending', NULL, '{\"amount\":20,\"method\":\"cash\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:02:06'),
(161, 3, 'pledge', 63, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-319\",\"A0505-320\",\"A0505-321\",\"A0505-322\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:02:14'),
(162, 57, 'payment', 36, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Welete tekekehaymanot\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:02:20'),
(163, 6, 'pledge', 64, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Henok senay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:02:25'),
(164, 55, 'pledge', 65, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Welde Giorgis + Menbere Mariam\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:01'),
(165, 70, 'payment', 37, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:10'),
(166, 66, 'pledge', 66, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Gebriye getachew\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:33'),
(167, 64, 'pledge', 67, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yarede mesefen\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:44'),
(168, 6, 'pledge', 68, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Lucy and Dina\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:44'),
(169, 52, 'pledge', 69, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Sara Aregay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:03:49'),
(170, 3, 'pledge', 64, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-324\",\"A0505-325\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:03:54'),
(171, 3, 'pledge', 65, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-326\",\"A0505-327\",\"A0505-328\",\"A0505-329\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:03:57'),
(172, 55, 'payment', 38, 'create_pending', NULL, '{\"amount\":60,\"method\":\"cash\",\"donor\":\"Kesis Senay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:01'),
(173, 67, 'payment', 39, 'create_pending', NULL, '{\"amount\":50,\"method\":\"bank\",\"donor\":\"Ymesgen nesibu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:08'),
(174, 60, 'pledge', 70, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Bemenet Fasil\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:08'),
(175, 3, 'pledge', 66, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-331\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:08'),
(176, 3, 'pledge', 67, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-332\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:14'),
(177, 65, 'pledge', 71, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Dahlak\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:15'),
(178, 3, 'pledge', 68, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-333\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:18'),
(179, 1, 'payment', 1, 'delete', '{\"id\":1,\"donor_name\":\"Yeshiwork Berihun\",\"donor_phone\":\"07878567049\",\"donor_email\":null,\"amount\":\"5.00\",\"method\":\"cash\",\"package_id\":4,\"reference\":\"1301\",\"status\":\"approved\",\"received_by_user_id\":68,\"received_at\":\"2025-08-30 08:36:52\",\"created_at\":\"2025-08-30 08:36:52\"}', NULL, NULL, 'admin', '2025-08-30 09:04:20'),
(180, 3, 'pledge', 69, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-334\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:21'),
(181, 57, 'pledge', 72, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Selam tadese\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:21'),
(182, 3, 'pledge', 70, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-336\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:32'),
(183, 1, 'pledge', 10, 'delete', '{\"id\":10,\"donor_name\":\"Elsabet mitku\",\"donor_phone\":\"07476067852\",\"donor_email\":null,\"package_id\":4,\"source\":\"volunteer\",\"anonymous\":0,\"amount\":\"10.00\",\"type\":\"pledge\",\"status\":\"approved\",\"notes\":\"1202\",\"client_uuid\":\"18e4c044-4cbf-4fe0-814b-48965ad51c19\",\"ip_address\":null,\"user_agent\":null,\"proof_path\":null,\"created_by_user_id\":68,\"approved_by_user_id\":3,\"created_at\":\"2025-08-30 08:47:36\",\"approved_at\":\"2025-08-30 08:48:00\",\"status_changed_at\":\"2025-08-30 08:48:00\"}', NULL, NULL, 'admin', '2025-08-30 09:04:33'),
(184, 3, 'pledge', 71, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-337\",\"A0505-338\",\"A0505-339\",\"A0505-340\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:39'),
(185, 72, 'payment', 40, 'create_pending', NULL, '{\"amount\":200,\"method\":\"card\",\"donor\":\"Kebita\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:40'),
(186, 3, 'pledge', 72, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-341\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:46'),
(187, 73, 'pledge', 73, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Fseha mngstu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:04:47'),
(188, 3, 'pledge', 73, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-344\"]}},\"type\":\"allocated\"}}', 0x5c28b6ef, 'admin', '2025-08-30 09:04:54'),
(189, 49, 'payment', 41, 'create_pending', NULL, '{\"amount\":50,\"method\":\"card\",\"donor\":\"Birhan Reta\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:05:29'),
(190, 67, 'pledge', 74, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Ashenafi yirga\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:05:48'),
(191, 69, 'payment', 42, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:06:17'),
(192, 56, 'payment', 43, 'create_pending', NULL, '{\"amount\":30,\"method\":\"cash\",\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:06:23'),
(193, 66, 'payment', 44, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Wolde senebet\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:06:26'),
(194, 48, 'pledge', 75, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Habte Meskel\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:06:40'),
(195, 55, 'payment', 45, 'create_pending', NULL, '{\"amount\":50,\"method\":\"card\",\"donor\":\"Kesis Solomon\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:07:14'),
(196, 71, 'payment', 46, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Sosna yegzaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:07:39'),
(197, 66, 'payment', 47, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Abel berhe\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:07:44'),
(198, 8, 'pledge', 76, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Alem mulu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:07:46'),
(199, 60, 'pledge', 77, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Fasil Tesfaye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:08:03'),
(200, 1, 'payment', 27, 'delete', '{\"id\":27,\"donor_name\":\"Yonas abraham\",\"donor_phone\":\"07472796824\",\"donor_email\":null,\"amount\":\"100.00\",\"method\":\"bank\",\"package_id\":3,\"reference\":\"1260\",\"status\":\"approved\",\"received_by_user_id\":6,\"received_at\":\"2025-08-30 08:58:05\",\"created_at\":\"2025-08-30 08:58:05\"}', NULL, NULL, 'admin', '2025-08-30 09:08:10'),
(201, 55, 'payment', 48, 'create_pending', NULL, '{\"amount\":70,\"method\":\"cash\",\"donor\":\"D\\/n Hailemariam\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:08:16'),
(202, 3, 'pledge', 74, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-347\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:08:34'),
(203, 3, 'pledge', 75, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-348\",\"A0505-349\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:08:39'),
(204, 3, 'pledge', 76, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-351\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:08:52'),
(205, 3, 'pledge', 77, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-352\",\"A0505-353\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:08:59'),
(206, 65, 'pledge', 78, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Nanny fantahun\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:09:25'),
(207, 64, 'payment', 49, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Heyaw demeke\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:09:26'),
(208, 70, 'pledge', 79, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":1,\"donor\":\"Anonymous\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:09:44'),
(209, 6, 'pledge', 80, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yonas abraham\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:09:45'),
(210, 57, 'payment', 50, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Emebet worku\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:09:52'),
(211, 52, 'pledge', 81, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Meselewerk legese\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:10:48'),
(212, 54, 'pledge', 82, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Abenzer\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:11:34'),
(213, 49, 'payment', 51, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Makers adonnay\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:12:04'),
(214, 60, 'pledge', 83, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Senait Kidene\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:12:14'),
(215, 65, 'pledge', 84, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Ruth dagim\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:12:47'),
(216, 67, 'pledge', 85, 'create_pending', NULL, '{\"amount\":300,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Eliyana yoseph\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:12:50'),
(217, 6, 'payment', 52, 'create_pending', NULL, '{\"amount\":30,\"method\":\"cash\",\"donor\":\"Woltebrhan\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:13:01'),
(218, 73, 'payment', 53, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Yohanis aklilu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:13:02'),
(219, 60, 'pledge', 86, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Mekdelawit s Asefa\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:13:47'),
(220, 57, 'payment', 54, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Welete tinsae\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:13:51'),
(221, 71, 'pledge', 87, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Hiwan gebrehiwit\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:13:56'),
(222, 54, 'pledge', 88, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Amen\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:14:00'),
(223, 3, 'pledge', 88, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:14:08'),
(224, 61, 'pledge', 89, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Samson\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:14:11'),
(225, 3, 'pledge', 89, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":40,\"cells\":[\"A0505-355\"],\"total_contributors\":1}],\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:14:44'),
(226, 3, 'pledge', 78, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-356\",\"A0505-357\",\"A0505-358\",\"A0505-359\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:03'),
(227, 3, 'pledge', 79, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-361\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:12'),
(228, 3, 'pledge', 80, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-362\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:15'),
(229, 3, 'pledge', 81, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-364\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:24'),
(230, 3, 'pledge', 82, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-365\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:26'),
(231, 3, 'pledge', 83, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-366\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:28'),
(232, 3, 'pledge', 84, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-367\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:30'),
(233, 57, 'pledge', 90, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Abeba tamene\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:15:33'),
(234, 3, 'pledge', 85, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":300}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3300\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":100,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-368\",\"A0505-369\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:39'),
(235, 3, 'pledge', 86, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-371\",\"A0505-372\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:46'),
(236, 3, 'pledge', 87, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-374\",\"A0505-375\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:51'),
(237, 3, 'pledge', 90, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-376\",\"A0505-377\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:15:54'),
(238, 52, 'pledge', 91, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Suzane Abera\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:16:01'),
(239, 60, 'pledge', 92, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Desert Haileselassie \\/Grace Jones\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:16:14'),
(240, 55, 'pledge', 93, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Elias Shiferaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:16:15'),
(241, 3, 'pledge', 91, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-378\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:16:28'),
(242, 3, 'pledge', 92, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-379\",\"A0505-380\",\"A0505-381\",\"A0505-382\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:16:34'),
(243, 3, 'pledge', 93, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-383\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:16:39'),
(244, 70, 'payment', 55, 'create_pending', NULL, '{\"amount\":200,\"method\":\"bank\",\"donor\":\"Hailegiyorgis Families\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:16:52'),
(245, 64, 'pledge', 94, 'create_pending', NULL, '{\"amount\":80,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Fekeremareym\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:17:02'),
(246, 65, 'pledge', 95, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Bizu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:17:40'),
(247, 60, 'pledge', 96, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Helen Tesfaye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:17:43'),
(248, 57, 'pledge', 97, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Sentayehu taye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:17:58'),
(249, 73, 'payment', 56, 'create_pending', NULL, '{\"amount\":20,\"method\":\"cash\",\"donor\":\"Dagem dawit\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:18:30'),
(250, 64, 'payment', 57, 'create_pending', NULL, '{\"amount\":20,\"method\":\"cash\",\"donor\":\"Fekeremareyam\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:18:38'),
(251, 3, 'pledge', 94, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":80}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a380 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":60,\"cells\":[\"A0505-385\"],\"total_contributors\":1}],\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:19:05'),
(252, 55, 'pledge', 98, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Tamiru Legesse\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:19:24'),
(253, 6, 'pledge', 99, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Eldana Hagos\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:19:25'),
(254, 3, 'pledge', 95, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-388\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:19:34'),
(255, 3, 'pledge', 96, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-389\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:19:40'),
(256, 65, 'pledge', 100, 'create_pending', NULL, '{\"amount\":400,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Arsema\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:19:58'),
(257, 71, 'payment', 58, 'create_pending', NULL, '{\"amount\":20,\"method\":\"cash\",\"donor\":\"Sosena gizaw\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:19:59'),
(258, 62, 'pledge', 101, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Michael Tesfaye\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:20:26'),
(259, 57, 'payment', 59, 'create_pending', NULL, '{\"amount\":200,\"method\":\"cash\",\"donor\":\"Welete giwergis\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:20:29'),
(260, 3, 'pledge', 97, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-390\",\"A0505-391\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:20:30'),
(261, 3, 'pledge', 98, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-392\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:20:36'),
(262, 3, 'pledge', 99, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-393\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:20:42'),
(263, 3, 'pledge', 100, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":400}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3400\",\"allocation_result\":{\"allocated_amount\":400,\"remaining_amount\":0,\"cells_allocated\":4,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 4 grid cell(s).\",\"area_allocated\":1,\"allocated_cells\":[\"A0505-394\",\"A0505-395\",\"A0505-396\",\"A0505-397\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:20:48'),
(264, 60, 'payment', 60, 'create_pending', NULL, '{\"amount\":100,\"method\":\"card\",\"donor\":\"Mahlet  Tamrat\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:21:01'),
(265, 3, 'pledge', 101, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-398\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:21:03'),
(266, 71, 'pledge', 102, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Meron selish\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:21:08'),
(267, 56, 'pledge', 103, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Chernat\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:22:00'),
(268, 3, 'pledge', 102, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-402\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:22:01'),
(269, 57, 'pledge', 104, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Gelila tezeta\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:22:17'),
(270, 3, 'pledge', 103, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-403\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:22:18'),
(271, 3, 'pledge', 104, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-404\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:22:23'),
(272, 52, 'pledge', 105, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Natinael mesefin\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:22:32'),
(273, 3, 'pledge', 105, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-405\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:22:51'),
(274, 67, 'payment', 61, 'create_pending', NULL, '{\"amount\":100,\"method\":\"bank\",\"donor\":\"Eliyana yoseph\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:23:17'),
(275, 48, 'pledge', 106, 'create_pending', NULL, '{\"amount\":20,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Sara\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:23:32'),
(276, 1, 'payment', 57, 'update', '{\"id\":57,\"donor_name\":\"Fekeremareyam\",\"donor_phone\":\"07479348031\",\"donor_email\":null,\"amount\":\"20.00\",\"method\":\"cash\",\"package_id\":4,\"reference\":\"0159\",\"status\":\"approved\",\"received_by_user_id\":64,\"received_at\":\"2025-08-30 09:18:38\",\"created_at\":\"2025-08-30 09:18:38\"}', '{\"donor_name\":\"Fekeremareyam\",\"amount\":20,\"method\":\"card\",\"status\":\"approved\"}', NULL, 'admin', '2025-08-30 09:23:44'),
(277, 66, 'payment', 62, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Eleni adane\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:23:47'),
(278, 70, 'payment', 63, 'create_pending', NULL, '{\"amount\":10,\"method\":\"cash\",\"donor\":\"Tesfaye Berhane\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:24:19'),
(279, 67, 'pledge', 107, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Muluken Nigatu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:24:30'),
(280, 3, 'pledge', 106, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":20}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a320 tracked. No immediate allocation.\",\"allocation_result\":[{\"donor\":\"Collective (Multiple Donors)\",\"allocated\":100,\"remaining\":0,\"cells\":[\"A0505-406\"],\"total_contributors\":0}],\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:24:30'),
(281, 3, 'pledge', 107, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-408\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:24:50'),
(282, 62, 'pledge', 108, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Michael Gebriyesus\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:25:07'),
(283, 3, 'pledge', 108, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-409\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:26:47'),
(284, 54, 'payment', 64, 'create_pending', NULL, '{\"amount\":100,\"method\":\"cash\",\"donor\":\"Kidus Kidane\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:26:51'),
(285, 48, 'pledge', 109, 'create_pending', NULL, '{\"amount\":10,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Estube\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:27:03'),
(286, 3, 'pledge', 109, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":10}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a310 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:27:26'),
(287, 70, 'pledge', 110, 'create_pending', NULL, '{\"amount\":50,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Geberemeskel Samson\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:27:59'),
(288, 68, 'pledge', 111, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Yeshiwork Berihun\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:29:18'),
(289, 1, 'pledge', 32, 'update', '{\"id\":32,\"donor_name\":\"Abraham Gobeze\",\"donor_phone\":\"07361845060\",\"donor_email\":null,\"package_id\":2,\"source\":\"volunteer\",\"anonymous\":0,\"amount\":\"200.00\",\"type\":\"pledge\",\"status\":\"approved\",\"notes\":\"0904\",\"client_uuid\":\"85466d18-7e77-4089-af0d-4eff91445d4c\",\"ip_address\":null,\"user_agent\":null,\"proof_path\":null,\"created_by_user_id\":55,\"approved_by_user_id\":3,\"created_at\":\"2025-08-30 08:53:11\",\"approved_at\":\"2025-08-30 08:53:42\",\"status_changed_at\":\"2025-08-30 08:53:42\"}', '{\"donor_name\":\"Abraham Gobeze\",\"amount\":100,\"status\":\"approved\",\"anonymous\":0}', NULL, 'admin', '2025-08-30 09:29:28'),
(290, 3, 'pledge', 110, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":50}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Amount \\u00a350 tracked. No immediate allocation.\",\"allocation_result\":null,\"type\":\"accumulated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:29:36'),
(291, 3, 'pledge', 111, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":100}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3100\",\"allocation_result\":{\"allocated_amount\":100,\"remaining_amount\":0,\"cells_allocated\":1,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 1 grid cell(s).\",\"area_allocated\":0.25,\"allocated_cells\":[\"A0505-411\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:29:39'),
(292, 73, 'payment', 65, 'create_pending', NULL, '{\"amount\":20,\"method\":\"card\",\"donor\":\"Mihiret alemayhu\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:29:55'),
(293, 65, 'pledge', 112, 'create_pending', NULL, '{\"amount\":200,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Hans Legese\",\"status\":\"pending\"}', NULL, 'registrar', '2025-08-30 09:30:02'),
(294, 3, 'pledge', 112, 'approve', '{\"status\":\"pending\",\"type\":\"pledge\",\"amount\":200}', '{\"status\":\"approved\",\"grid_allocation\":{\"success\":true,\"message\":\"Allocated appropriate cells for \\u00a3200\",\"allocation_result\":{\"allocated_amount\":200,\"remaining_amount\":0,\"cells_allocated\":2,\"grid_allocation\":{\"success\":true,\"message\":\"Successfully allocated 2 grid cell(s).\",\"area_allocated\":0.5,\"allocated_cells\":[\"A0505-413\",\"A0505-414\"]}},\"type\":\"allocated\"}}', 0x5c28b6ed, 'admin', '2025-08-30 09:30:36'),
(295, 1, 'payment', 59, 'update', '{\"id\":59,\"donor_name\":\"Welete giwergis\",\"donor_phone\":\"07984988480\",\"donor_email\":null,\"amount\":\"200.00\",\"method\":\"cash\",\"package_id\":2,\"reference\":\"0314\",\"status\":\"approved\",\"received_by_user_id\":57,\"received_at\":\"2025-08-30 10:20:29\",\"created_at\":\"2025-08-30 10:20:29\"}', '{\"donor_name\":\"Welete giwergis\",\"amount\":20,\"method\":\"cash\",\"status\":\"approved\"}', NULL, 'admin', '2025-09-01 12:20:02');

-- --------------------------------------------------------

--
-- Table structure for table `counters`
--

CREATE TABLE `counters` (
  `id` tinyint(4) NOT NULL,
  `paid_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pledged_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `recalc_needed` tinyint(1) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counters`
--

INSERT INTO `counters` (`id`, `paid_total`, `pledged_total`, `grand_total`, `version`, `recalc_needed`, `last_updated`) VALUES
(1, 5800.00, 17200.00, 23000.00, 178, 0, '2025-08-30 09:30:36');

-- --------------------------------------------------------

--
-- Table structure for table `custom_amount_tracking`
--

CREATE TABLE `custom_amount_tracking` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allocated_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_amount_tracking`
--

INSERT INTO `custom_amount_tracking` (`id`, `donor_id`, `donor_name`, `total_amount`, `allocated_amount`, `remaining_amount`, `last_updated`, `created_at`) VALUES
(1, 0, 'Collective Custom', 1800.00, 1800.00, 0.00, '2025-08-30 09:30:14', '2025-08-30 04:24:26');

-- --------------------------------------------------------

--
-- Table structure for table `donation_packages`
--

CREATE TABLE `donation_packages` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `sqm_meters` decimal(8,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_packages`
--

INSERT INTO `donation_packages` (`id`, `label`, `sqm_meters`, `price`, `active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, '1 m²', 1.00, 400.00, 1, 1, '2025-08-13 06:31:09', '2025-08-13 22:22:39'),
(2, '1/2 m²', 0.50, 200.00, 1, 2, '2025-08-13 06:31:09', '2025-08-13 06:31:09'),
(3, '1/4 m²', 0.25, 100.00, 1, 3, '2025-08-13 06:31:09', '2025-08-13 06:31:09'),
(4, 'Custom', 0.00, 0.00, 1, 4, '2025-08-13 06:31:09', '2025-08-13 06:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `floor_area_allocations`
--

CREATE TABLE `floor_area_allocations` (
  `id` int(11) NOT NULL,
  `allocation_type` enum('pledge','payment','custom') NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `donor_name` varchar(255) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `area_size` decimal(5,2) NOT NULL COMMENT 'in m²',
  `grid_cells` text NOT NULL COMMENT 'JSON array of assigned grid cell IDs',
  `cell_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of actual cell IDs allocated (e.g., ["A0101-01", "A0505-12"])' CHECK (json_valid(`cell_ids`)),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `allocated_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `floor_area_allocations`
--

INSERT INTO `floor_area_allocations` (`id`, `allocation_type`, `donor_id`, `donor_name`, `package_id`, `amount`, `area_size`, `grid_cells`, `cell_ids`, `status`, `admin_notes`, `allocated_date`, `created_at`) VALUES
(3, 'pledge', 1, 'Abel Demssie', NULL, 400.00, 1.00, '', '[]', 'approved', NULL, '2025-08-29 13:22:43', '2025-08-29 13:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `floor_grid_cells`
--

CREATE TABLE `floor_grid_cells` (
  `id` int(11) NOT NULL,
  `cell_id` varchar(20) NOT NULL COMMENT 'Actual cell ID from floor plan (e.g., A0101-01, A0505-15)',
  `rectangle_id` char(1) NOT NULL COMMENT 'A, B, C, D, E, F, G',
  `cell_type` enum('1x1','1x0.5','0.5x0.5') NOT NULL COMMENT 'Cell size type',
  `area_size` decimal(4,2) NOT NULL COMMENT 'Area in m² (1.0, 0.5, or 0.25)',
  `package_id` int(11) DEFAULT NULL COMMENT 'Links to donation_packages table',
  `status` enum('available','pledged','paid','blocked') DEFAULT 'available',
  `pledge_id` int(11) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `assigned_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `floor_grid_cells`
--

INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(1, 'A0101-47', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(2, 'A0101-48', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(3, 'A0101-49', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(4, 'A0101-50', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(5, 'A0101-51', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(6, 'A0101-52', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(7, 'A0101-53', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(8, 'A0101-54', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(9, 'A0101-55', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(10, 'A0101-56', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(11, 'A0101-57', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(12, 'A0101-58', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(13, 'A0101-59', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(14, 'A0101-60', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(15, 'A0101-61', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(16, 'A0101-62', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(17, 'A0101-63', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(18, 'A0101-64', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(19, 'A0101-65', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(20, 'A0101-66', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(21, 'A0101-67', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(22, 'A0101-68', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(23, 'A0101-69', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(24, 'A0101-70', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(25, 'A0101-71', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(26, 'A0101-72', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(27, 'A0101-73', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(28, 'A0101-74', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(29, 'A0101-75', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(30, 'A0101-76', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(31, 'A0101-77', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(32, 'A0101-78', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(33, 'A0101-79', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(34, 'A0101-80', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(35, 'A0101-81', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(36, 'A0101-82', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(37, 'A0101-83', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(38, 'A0101-84', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(39, 'A0101-85', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(40, 'A0101-86', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(41, 'A0101-87', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(42, 'A0101-88', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(43, 'A0101-89', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(44, 'A0101-90', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(45, 'A0101-91', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(46, 'A0101-92', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(47, 'A0101-93', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(48, 'A0101-94', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(49, 'A0101-95', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(50, 'A0101-96', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(51, 'A0101-97', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(52, 'A0101-98', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(53, 'A0101-99', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(54, 'A0101-100', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(55, 'A0101-101', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(56, 'A0101-102', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(57, 'A0101-103', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(58, 'A0101-104', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(59, 'A0101-105', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(60, 'A0101-106', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(61, 'A0101-107', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(62, 'A0101-108', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(63, 'A0101-109', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(64, 'A0101-110', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(65, 'A0101-111', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(66, 'A0101-112', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(67, 'A0101-113', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(68, 'A0101-114', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(69, 'A0101-115', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(70, 'A0101-116', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(71, 'A0101-117', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(72, 'A0101-118', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(73, 'A0101-119', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(74, 'A0101-120', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(75, 'A0101-121', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(76, 'A0101-122', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(77, 'A0101-123', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(78, 'A0101-124', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(79, 'A0101-125', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(80, 'A0101-126', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(81, 'A0101-127', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(82, 'A0101-128', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(83, 'A0101-129', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(84, 'A0101-130', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(85, 'A0101-131', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(86, 'A0101-132', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(87, 'A0101-133', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(88, 'A0101-134', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(89, 'A0101-135', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(90, 'A0101-136', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(91, 'A0101-137', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(92, 'A0101-138', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(93, 'A0101-139', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(94, 'A0101-140', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(95, 'A0101-141', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(96, 'A0101-142', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(97, 'A0101-143', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(98, 'A0101-144', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(99, 'A0101-145', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(100, 'A0101-146', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(101, 'A0101-147', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(102, 'A0101-148', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(103, 'A0101-149', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(104, 'A0101-150', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(105, 'A0101-151', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(106, 'A0101-152', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(107, 'A0101-153', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(108, 'A0101-154', 'A', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(109, 'B0101-01', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(110, 'B0101-02', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(111, 'B0101-03', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(112, 'B0101-04', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(113, 'B0101-05', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(114, 'B0101-06', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(115, 'B0101-07', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(116, 'B0101-08', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(117, 'B0101-09', 'B', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(118, 'C0101-01', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(119, 'C0101-02', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(120, 'C0101-03', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(121, 'C0101-04', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(122, 'C0101-05', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(123, 'C0101-06', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(124, 'C0101-07', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(125, 'C0101-08', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(126, 'C0101-09', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(127, 'C0101-10', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(128, 'C0101-11', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(129, 'C0101-12', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(130, 'C0101-13', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(131, 'C0101-14', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(132, 'C0101-15', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(133, 'C0101-16', 'C', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(134, 'D0101-01', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(135, 'D0101-02', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(136, 'D0101-03', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(137, 'D0101-04', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(138, 'D0101-05', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(139, 'D0101-06', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(140, 'D0101-07', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(141, 'D0101-08', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(142, 'D0101-09', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(143, 'D0101-10', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(144, 'D0101-11', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(145, 'D0101-12', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(146, 'D0101-13', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(147, 'D0101-14', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(148, 'D0101-15', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(149, 'D0101-16', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(150, 'D0101-17', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(151, 'D0101-18', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(152, 'D0101-19', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(153, 'D0101-20', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(154, 'D0101-21', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(155, 'D0101-22', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(156, 'D0101-23', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(157, 'D0101-24', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(158, 'D0101-25', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(159, 'D0101-26', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(160, 'D0101-27', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(161, 'D0101-28', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(162, 'D0101-29', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(163, 'D0101-30', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(164, 'D0101-31', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(165, 'D0101-32', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(166, 'D0101-33', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(167, 'D0101-34', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(168, 'D0101-35', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(169, 'D0101-36', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(170, 'D0101-37', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(171, 'D0101-38', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(172, 'D0101-39', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(173, 'D0101-40', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(174, 'D0101-41', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(175, 'D0101-42', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(176, 'D0101-43', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(177, 'D0101-44', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(178, 'D0101-45', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(179, 'D0101-46', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(180, 'D0101-47', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(181, 'D0101-48', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(182, 'D0101-49', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(183, 'D0101-50', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(184, 'D0101-51', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(185, 'D0101-52', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(186, 'D0101-53', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(187, 'D0101-54', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(188, 'D0101-55', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(189, 'D0101-56', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(190, 'D0101-57', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(191, 'D0101-58', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(192, 'D0101-59', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(193, 'D0101-60', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(194, 'D0101-61', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(195, 'D0101-62', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(196, 'D0101-63', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(197, 'D0101-64', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(198, 'D0101-65', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(199, 'D0101-66', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(200, 'D0101-67', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(201, 'D0101-68', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(202, 'D0101-69', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(203, 'D0101-70', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(204, 'D0101-71', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(205, 'D0101-72', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(206, 'D0101-73', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(207, 'D0101-74', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(208, 'D0101-75', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(209, 'D0101-76', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(210, 'D0101-77', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(211, 'D0101-78', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(212, 'D0101-79', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(213, 'D0101-80', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(214, 'D0101-81', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(215, 'D0101-82', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(216, 'D0101-83', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(217, 'D0101-84', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(218, 'D0101-85', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(219, 'D0101-86', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(220, 'D0101-87', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(221, 'D0101-88', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(222, 'D0101-89', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(223, 'D0101-90', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(224, 'D0101-91', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(225, 'D0101-92', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(226, 'D0101-93', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(227, 'D0101-94', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(228, 'D0101-95', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(229, 'D0101-96', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(230, 'D0101-97', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(231, 'D0101-98', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(232, 'D0101-99', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(233, 'D0101-100', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(234, 'D0101-101', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(235, 'D0101-102', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(236, 'D0101-103', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(237, 'D0101-104', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(238, 'D0101-105', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(239, 'D0101-106', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(240, 'D0101-107', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(241, 'D0101-108', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(242, 'D0101-109', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(243, 'D0101-110', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(244, 'D0101-111', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(245, 'D0101-112', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(246, 'D0101-113', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(247, 'D0101-114', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(248, 'D0101-115', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(249, 'D0101-116', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(250, 'D0101-117', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(251, 'D0101-118', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(252, 'D0101-119', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(253, 'D0101-120', 'D', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(254, 'E0101-01', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(255, 'E0101-02', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(256, 'E0101-03', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(257, 'E0101-04', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(258, 'E0101-05', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(259, 'E0101-06', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(260, 'E0101-07', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(261, 'E0101-08', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(262, 'E0101-09', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(263, 'E0101-10', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(264, 'E0101-11', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(265, 'E0101-12', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(266, 'E0101-13', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(267, 'E0101-14', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(268, 'E0101-15', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(269, 'E0101-16', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(270, 'E0101-17', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(271, 'E0101-18', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(272, 'E0101-19', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(273, 'E0101-20', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(274, 'E0101-21', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(275, 'E0101-22', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(276, 'E0101-23', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(277, 'E0101-24', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(278, 'E0101-25', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(279, 'E0101-26', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(280, 'E0101-27', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(281, 'E0101-28', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(282, 'E0101-29', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(283, 'E0101-30', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(284, 'E0101-31', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(285, 'E0101-32', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(286, 'E0101-33', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(287, 'E0101-34', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(288, 'E0101-35', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(289, 'E0101-36', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(290, 'E0101-37', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(291, 'E0101-38', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(292, 'E0101-39', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(293, 'E0101-40', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(294, 'E0101-41', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(295, 'E0101-42', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(296, 'E0101-43', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(297, 'E0101-44', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(298, 'E0101-45', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(299, 'E0101-46', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(300, 'E0101-47', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(301, 'E0101-48', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(302, 'E0101-49', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(303, 'E0101-50', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(304, 'E0101-51', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(305, 'E0101-52', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(306, 'E0101-53', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(307, 'E0101-54', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(308, 'E0101-55', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(309, 'E0101-56', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(310, 'E0101-57', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(311, 'E0101-58', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(312, 'E0101-59', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(313, 'E0101-60', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(314, 'E0101-61', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(315, 'E0101-62', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(316, 'E0101-63', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(317, 'E0101-64', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(318, 'E0101-65', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(319, 'E0101-66', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(320, 'E0101-67', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(321, 'E0101-68', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(322, 'E0101-69', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(323, 'E0101-70', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(324, 'E0101-71', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(325, 'E0101-72', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(326, 'E0101-73', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(327, 'E0101-74', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(328, 'E0101-75', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(329, 'E0101-76', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(330, 'E0101-77', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(331, 'E0101-78', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(332, 'E0101-79', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(333, 'E0101-80', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(334, 'E0101-81', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(335, 'E0101-82', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(336, 'E0101-83', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(337, 'E0101-84', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(338, 'E0101-85', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(339, 'E0101-86', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(340, 'E0101-87', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(341, 'E0101-88', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(342, 'E0101-89', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(343, 'E0101-90', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(344, 'E0101-91', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(345, 'E0101-92', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(346, 'E0101-93', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(347, 'E0101-94', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(348, 'E0101-95', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(349, 'E0101-96', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(350, 'E0101-97', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(351, 'E0101-98', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(352, 'E0101-99', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(353, 'E0101-100', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(354, 'E0101-101', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(355, 'E0101-102', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(356, 'E0101-103', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(357, 'E0101-104', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(358, 'E0101-105', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(359, 'E0101-106', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(360, 'E0101-107', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(361, 'E0101-108', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(362, 'E0101-109', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(363, 'E0101-110', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(364, 'E0101-111', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(365, 'E0101-112', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(366, 'E0101-113', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(367, 'E0101-114', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(368, 'E0101-115', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(369, 'E0101-116', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(370, 'E0101-117', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(371, 'E0101-118', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(372, 'E0101-119', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(373, 'E0101-120', 'E', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(374, 'F0101-01', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(375, 'F0101-02', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(376, 'F0101-03', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(377, 'F0101-04', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(378, 'F0101-05', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(379, 'F0101-06', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(380, 'F0101-07', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(381, 'F0101-08', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(382, 'F0101-09', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(383, 'F0101-10', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(384, 'F0101-11', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(385, 'F0101-12', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(386, 'F0101-13', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(387, 'F0101-14', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(388, 'F0101-15', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(389, 'F0101-16', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(390, 'F0101-17', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(391, 'F0101-18', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(392, 'F0101-19', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(393, 'F0101-20', 'F', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(394, 'G0101-01', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(395, 'G0101-02', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(396, 'G0101-03', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(397, 'G0101-04', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(398, 'G0101-05', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(399, 'G0101-06', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(400, 'G0101-07', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(401, 'G0101-08', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(402, 'G0101-09', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(403, 'G0101-10', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(404, 'G0101-11', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(405, 'G0101-12', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(406, 'G0101-13', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(407, 'G0101-14', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(408, 'G0101-15', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(409, 'G0101-16', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(410, 'G0101-17', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(411, 'G0101-18', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(412, 'G0101-19', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(413, 'G0101-20', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(414, 'G0101-21', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(415, 'G0101-22', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(416, 'G0101-23', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(417, 'G0101-24', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(418, 'G0101-25', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(419, 'G0101-26', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(420, 'G0101-27', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(421, 'G0101-28', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(422, 'G0101-29', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(423, 'G0101-30', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(424, 'G0101-31', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(425, 'G0101-32', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(426, 'G0101-33', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(427, 'G0101-34', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(428, 'G0101-35', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(429, 'G0101-36', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(430, 'G0101-37', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(431, 'G0101-38', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(432, 'G0101-39', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(433, 'G0101-40', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(434, 'G0101-41', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(435, 'G0101-42', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(436, 'G0101-43', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(437, 'G0101-44', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(438, 'G0101-45', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(439, 'G0101-46', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(440, 'G0101-47', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(441, 'G0101-48', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(442, 'G0101-49', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(443, 'G0101-50', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(444, 'G0101-51', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(445, 'G0101-52', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(446, 'G0101-53', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(447, 'G0101-54', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(448, 'G0101-55', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(449, 'G0101-56', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(450, 'G0101-57', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(451, 'G0101-58', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(452, 'G0101-59', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(453, 'G0101-60', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(454, 'G0101-61', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(455, 'G0101-62', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(456, 'G0101-63', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(457, 'G0101-64', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(458, 'G0101-65', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(459, 'G0101-66', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(460, 'G0101-67', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(461, 'G0101-68', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(462, 'G0101-69', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(463, 'G0101-70', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(464, 'G0101-71', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(465, 'G0101-72', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(466, 'G0101-73', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(467, 'G0101-74', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(468, 'G0101-75', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(469, 'G0101-76', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(470, 'G0101-77', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(471, 'G0101-78', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(472, 'G0101-79', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(473, 'G0101-80', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(474, 'G0101-81', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(475, 'G0101-82', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(476, 'G0101-83', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(477, 'G0101-84', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(478, 'G0101-85', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(479, 'G0101-86', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(480, 'G0101-87', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(481, 'G0101-88', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(482, 'G0101-89', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(483, 'G0101-90', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(484, 'G0101-91', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(485, 'G0101-92', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(486, 'G0101-93', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(487, 'G0101-94', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(488, 'G0101-95', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(489, 'G0101-96', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(490, 'G0101-97', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(491, 'G0101-98', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(492, 'G0101-99', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(493, 'G0101-100', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(494, 'G0101-101', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(495, 'G0101-102', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(496, 'G0101-103', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(497, 'G0101-104', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(498, 'G0101-105', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(499, 'G0101-106', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(500, 'G0101-107', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(501, 'G0101-108', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(502, 'G0101-109', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(503, 'G0101-110', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(504, 'G0101-111', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(505, 'G0101-112', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(506, 'G0101-113', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(507, 'G0101-114', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(508, 'G0101-115', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(509, 'G0101-116', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(510, 'G0101-117', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(511, 'G0101-118', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(512, 'G0101-119', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(513, 'G0101-120', 'G', '1x1', 1.00, 1, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(514, 'A0105-93', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(515, 'A0105-94', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(516, 'A0105-95', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(517, 'A0105-96', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(518, 'A0105-97', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(519, 'A0105-98', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(520, 'A0105-99', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(521, 'A0105-100', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(522, 'A0105-101', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:09'),
(523, 'A0105-102', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(524, 'A0105-103', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(525, 'A0105-104', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(526, 'A0105-105', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(527, 'A0105-106', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(528, 'A0105-107', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(529, 'A0105-108', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(530, 'A0105-109', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(531, 'A0105-110', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(532, 'A0105-111', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(533, 'A0105-112', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(534, 'A0105-113', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(535, 'A0105-114', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(536, 'A0105-115', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(537, 'A0105-116', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(538, 'A0105-117', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(539, 'A0105-118', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(540, 'A0105-119', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(541, 'A0105-120', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(542, 'A0105-121', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(543, 'A0105-122', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(544, 'A0105-123', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(545, 'A0105-124', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(546, 'A0105-125', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(547, 'A0105-126', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(548, 'A0105-127', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(549, 'A0105-128', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(550, 'A0105-129', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(551, 'A0105-130', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(552, 'A0105-131', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(553, 'A0105-132', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(554, 'A0105-133', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(555, 'A0105-134', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(556, 'A0105-135', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(557, 'A0105-136', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(558, 'A0105-137', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(559, 'A0105-138', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(560, 'A0105-139', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(561, 'A0105-140', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(562, 'A0105-141', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(563, 'A0105-142', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(564, 'A0105-143', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(565, 'A0105-144', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(566, 'A0105-145', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(567, 'A0105-146', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(568, 'A0105-147', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(569, 'A0105-148', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(570, 'A0105-149', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(571, 'A0105-150', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(572, 'A0105-151', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(573, 'A0105-152', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(574, 'A0105-153', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(575, 'A0105-154', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(576, 'A0105-155', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(577, 'A0105-156', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(578, 'A0105-157', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(579, 'A0105-158', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(580, 'A0105-159', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(581, 'A0105-160', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(582, 'A0105-161', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(583, 'A0105-162', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(584, 'A0105-163', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(585, 'A0105-164', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(586, 'A0105-165', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(587, 'A0105-166', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(588, 'A0105-167', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(589, 'A0105-168', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(590, 'A0105-169', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(591, 'A0105-170', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(592, 'A0105-171', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(593, 'A0105-172', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(594, 'A0105-173', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(595, 'A0105-174', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(596, 'A0105-175', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(597, 'A0105-176', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(598, 'A0105-177', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(599, 'A0105-178', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(600, 'A0105-179', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(601, 'A0105-180', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(602, 'A0105-181', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(603, 'A0105-182', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(604, 'A0105-183', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(605, 'A0105-184', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(606, 'A0105-185', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(607, 'A0105-186', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(608, 'A0105-187', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(609, 'A0105-188', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(610, 'A0105-189', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(611, 'A0105-190', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(612, 'A0105-191', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(613, 'A0105-192', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(614, 'A0105-193', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(615, 'A0105-194', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(616, 'A0105-195', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(617, 'A0105-196', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(618, 'A0105-197', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(619, 'A0105-198', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(620, 'A0105-199', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(621, 'A0105-200', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(622, 'A0105-201', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(623, 'A0105-202', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(624, 'A0105-203', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(625, 'A0105-204', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(626, 'A0105-205', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(627, 'A0105-206', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(628, 'A0105-207', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(629, 'A0105-208', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(630, 'A0105-209', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(631, 'A0105-210', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(632, 'A0105-211', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(633, 'A0105-212', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(634, 'A0105-213', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(635, 'A0105-214', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(636, 'A0105-215', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(637, 'A0105-216', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(638, 'A0105-217', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(639, 'A0105-218', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(640, 'A0105-219', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(641, 'A0105-220', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(642, 'A0105-221', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(643, 'A0105-222', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(644, 'A0105-223', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(645, 'A0105-224', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(646, 'A0105-225', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(647, 'A0105-226', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(648, 'A0105-227', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(649, 'A0105-228', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(650, 'A0105-229', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(651, 'A0105-230', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(652, 'A0105-231', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(653, 'A0105-232', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(654, 'A0105-233', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(655, 'A0105-234', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(656, 'A0105-235', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(657, 'A0105-236', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(658, 'A0105-237', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(659, 'A0105-238', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(660, 'A0105-239', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(661, 'A0105-240', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(662, 'A0105-241', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(663, 'A0105-242', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(664, 'A0105-243', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(665, 'A0105-244', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(666, 'A0105-245', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(667, 'A0105-246', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(668, 'A0105-247', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(669, 'A0105-248', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(670, 'A0105-249', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(671, 'A0105-250', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(672, 'A0105-251', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(673, 'A0105-252', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(674, 'A0105-253', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(675, 'A0105-254', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(676, 'A0105-255', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(677, 'A0105-256', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(678, 'A0105-257', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(679, 'A0105-258', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(680, 'A0105-259', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(681, 'A0105-260', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(682, 'A0105-261', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(683, 'A0105-262', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(684, 'A0105-263', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(685, 'A0105-264', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(686, 'A0105-265', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(687, 'A0105-266', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(688, 'A0105-267', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(689, 'A0105-268', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(690, 'A0105-269', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(691, 'A0105-270', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(692, 'A0105-271', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(693, 'A0105-272', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(694, 'A0105-273', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(695, 'A0105-274', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(696, 'A0105-275', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(697, 'A0105-276', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(698, 'A0105-277', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(699, 'A0105-278', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(700, 'A0105-279', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(701, 'A0105-280', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(702, 'A0105-281', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(703, 'A0105-282', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(704, 'A0105-283', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(705, 'A0105-284', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(706, 'A0105-285', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(707, 'A0105-286', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(708, 'A0105-287', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(709, 'A0105-288', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(710, 'A0105-289', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(711, 'A0105-290', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(712, 'A0105-291', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(713, 'A0105-292', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(714, 'A0105-293', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(715, 'A0105-294', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(716, 'A0105-295', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(717, 'A0105-296', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(718, 'A0105-297', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(719, 'A0105-298', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(720, 'A0105-299', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(721, 'A0105-300', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(722, 'A0105-301', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(723, 'A0105-302', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(724, 'A0105-303', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(725, 'A0105-304', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(726, 'A0105-305', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(727, 'A0105-306', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(728, 'A0105-307', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(729, 'A0105-308', 'A', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(730, 'B0105-01', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(731, 'B0105-02', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(732, 'B0105-03', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(733, 'B0105-04', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(734, 'B0105-05', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(735, 'B0105-06', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(736, 'B0105-07', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(737, 'B0105-08', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(738, 'B0105-09', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(739, 'B0105-10', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(740, 'B0105-11', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(741, 'B0105-12', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(742, 'B0105-13', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(743, 'B0105-14', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(744, 'B0105-15', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(745, 'B0105-16', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(746, 'B0105-17', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(747, 'B0105-18', 'B', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(748, 'C0105-01', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(749, 'C0105-02', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(750, 'C0105-03', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(751, 'C0105-04', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(752, 'C0105-05', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(753, 'C0105-06', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(754, 'C0105-07', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(755, 'C0105-08', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(756, 'C0105-09', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(757, 'C0105-10', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(758, 'C0105-11', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(759, 'C0105-12', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(760, 'C0105-13', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(761, 'C0105-14', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(762, 'C0105-15', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(763, 'C0105-16', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(764, 'C0105-17', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(765, 'C0105-18', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(766, 'C0105-19', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(767, 'C0105-20', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(768, 'C0105-21', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(769, 'C0105-22', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(770, 'C0105-23', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(771, 'C0105-24', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(772, 'C0105-25', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(773, 'C0105-26', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(774, 'C0105-27', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(775, 'C0105-28', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(776, 'C0105-29', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(777, 'C0105-30', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(778, 'C0105-31', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(779, 'C0105-32', 'C', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(780, 'D0105-01', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(781, 'D0105-02', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(782, 'D0105-03', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(783, 'D0105-04', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(784, 'D0105-05', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(785, 'D0105-06', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(786, 'D0105-07', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(787, 'D0105-08', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(788, 'D0105-09', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(789, 'D0105-10', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(790, 'D0105-11', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(791, 'D0105-12', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(792, 'D0105-13', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(793, 'D0105-14', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(794, 'D0105-15', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(795, 'D0105-16', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(796, 'D0105-17', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(797, 'D0105-18', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(798, 'D0105-19', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(799, 'D0105-20', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(800, 'D0105-21', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(801, 'D0105-22', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(802, 'D0105-23', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(803, 'D0105-24', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(804, 'D0105-25', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(805, 'D0105-26', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(806, 'D0105-27', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(807, 'D0105-28', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(808, 'D0105-29', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(809, 'D0105-30', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(810, 'D0105-31', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(811, 'D0105-32', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(812, 'D0105-33', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(813, 'D0105-34', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(814, 'D0105-35', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(815, 'D0105-36', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(816, 'D0105-37', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(817, 'D0105-38', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(818, 'D0105-39', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(819, 'D0105-40', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(820, 'D0105-41', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(821, 'D0105-42', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(822, 'D0105-43', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(823, 'D0105-44', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(824, 'D0105-45', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(825, 'D0105-46', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(826, 'D0105-47', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(827, 'D0105-48', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(828, 'D0105-49', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(829, 'D0105-50', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(830, 'D0105-51', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(831, 'D0105-52', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(832, 'D0105-53', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(833, 'D0105-54', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(834, 'D0105-55', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(835, 'D0105-56', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(836, 'D0105-57', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(837, 'D0105-58', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(838, 'D0105-59', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(839, 'D0105-60', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(840, 'D0105-61', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(841, 'D0105-62', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(842, 'D0105-63', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(843, 'D0105-64', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(844, 'D0105-65', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(845, 'D0105-66', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(846, 'D0105-67', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(847, 'D0105-68', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(848, 'D0105-69', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(849, 'D0105-70', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(850, 'D0105-71', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(851, 'D0105-72', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(852, 'D0105-73', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(853, 'D0105-74', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(854, 'D0105-75', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(855, 'D0105-76', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(856, 'D0105-77', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(857, 'D0105-78', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(858, 'D0105-79', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(859, 'D0105-80', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(860, 'D0105-81', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(861, 'D0105-82', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(862, 'D0105-83', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(863, 'D0105-84', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(864, 'D0105-85', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(865, 'D0105-86', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(866, 'D0105-87', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(867, 'D0105-88', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(868, 'D0105-89', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(869, 'D0105-90', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(870, 'D0105-91', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(871, 'D0105-92', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(872, 'D0105-93', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(873, 'D0105-94', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(874, 'D0105-95', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(875, 'D0105-96', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(876, 'D0105-97', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(877, 'D0105-98', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(878, 'D0105-99', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(879, 'D0105-100', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(880, 'D0105-101', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(881, 'D0105-102', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(882, 'D0105-103', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(883, 'D0105-104', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(884, 'D0105-105', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(885, 'D0105-106', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(886, 'D0105-107', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(887, 'D0105-108', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(888, 'D0105-109', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(889, 'D0105-110', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(890, 'D0105-111', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(891, 'D0105-112', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(892, 'D0105-113', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(893, 'D0105-114', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(894, 'D0105-115', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(895, 'D0105-116', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(896, 'D0105-117', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(897, 'D0105-118', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(898, 'D0105-119', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(899, 'D0105-120', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(900, 'D0105-121', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(901, 'D0105-122', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(902, 'D0105-123', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(903, 'D0105-124', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(904, 'D0105-125', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(905, 'D0105-126', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(906, 'D0105-127', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(907, 'D0105-128', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(908, 'D0105-129', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(909, 'D0105-130', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(910, 'D0105-131', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(911, 'D0105-132', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(912, 'D0105-133', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(913, 'D0105-134', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(914, 'D0105-135', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(915, 'D0105-136', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(916, 'D0105-137', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(917, 'D0105-138', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(918, 'D0105-139', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(919, 'D0105-140', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(920, 'D0105-141', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(921, 'D0105-142', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(922, 'D0105-143', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(923, 'D0105-144', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(924, 'D0105-145', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(925, 'D0105-146', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(926, 'D0105-147', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(927, 'D0105-148', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(928, 'D0105-149', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(929, 'D0105-150', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(930, 'D0105-151', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(931, 'D0105-152', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(932, 'D0105-153', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(933, 'D0105-154', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(934, 'D0105-155', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(935, 'D0105-156', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(936, 'D0105-157', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(937, 'D0105-158', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(938, 'D0105-159', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(939, 'D0105-160', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(940, 'D0105-161', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(941, 'D0105-162', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(942, 'D0105-163', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(943, 'D0105-164', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(944, 'D0105-165', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(945, 'D0105-166', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(946, 'D0105-167', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(947, 'D0105-168', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(948, 'D0105-169', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(949, 'D0105-170', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(950, 'D0105-171', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(951, 'D0105-172', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(952, 'D0105-173', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(953, 'D0105-174', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(954, 'D0105-175', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(955, 'D0105-176', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(956, 'D0105-177', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(957, 'D0105-178', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(958, 'D0105-179', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(959, 'D0105-180', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(960, 'D0105-181', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(961, 'D0105-182', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(962, 'D0105-183', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(963, 'D0105-184', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(964, 'D0105-185', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(965, 'D0105-186', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(966, 'D0105-187', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(967, 'D0105-188', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(968, 'D0105-189', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(969, 'D0105-190', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(970, 'D0105-191', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(971, 'D0105-192', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(972, 'D0105-193', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(973, 'D0105-194', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(974, 'D0105-195', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(975, 'D0105-196', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(976, 'D0105-197', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(977, 'D0105-198', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(978, 'D0105-199', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(979, 'D0105-200', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(980, 'D0105-201', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(981, 'D0105-202', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(982, 'D0105-203', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(983, 'D0105-204', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(984, 'D0105-205', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(985, 'D0105-206', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(986, 'D0105-207', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(987, 'D0105-208', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(988, 'D0105-209', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(989, 'D0105-210', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(990, 'D0105-211', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(991, 'D0105-212', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(992, 'D0105-213', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(993, 'D0105-214', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(994, 'D0105-215', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(995, 'D0105-216', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(996, 'D0105-217', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(997, 'D0105-218', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(998, 'D0105-219', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(999, 'D0105-220', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1000, 'D0105-221', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1001, 'D0105-222', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1002, 'D0105-223', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1003, 'D0105-224', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1004, 'D0105-225', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1005, 'D0105-226', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1006, 'D0105-227', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1007, 'D0105-228', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1008, 'D0105-229', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1009, 'D0105-230', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1010, 'D0105-231', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1011, 'D0105-232', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1012, 'D0105-233', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1013, 'D0105-234', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1014, 'D0105-235', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1015, 'D0105-236', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1016, 'D0105-237', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1017, 'D0105-238', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1018, 'D0105-239', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1019, 'D0105-240', 'D', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1020, 'E0105-01', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1021, 'E0105-02', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1022, 'E0105-03', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1023, 'E0105-04', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1024, 'E0105-05', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1025, 'E0105-06', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1026, 'E0105-07', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1027, 'E0105-08', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1028, 'E0105-09', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1029, 'E0105-10', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1030, 'E0105-11', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1031, 'E0105-12', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1032, 'E0105-13', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1033, 'E0105-14', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1034, 'E0105-15', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1035, 'E0105-16', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1036, 'E0105-17', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1037, 'E0105-18', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1038, 'E0105-19', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1039, 'E0105-20', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1040, 'E0105-21', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1041, 'E0105-22', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1042, 'E0105-23', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1043, 'E0105-24', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1044, 'E0105-25', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1045, 'E0105-26', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1046, 'E0105-27', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1047, 'E0105-28', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1048, 'E0105-29', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1049, 'E0105-30', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1050, 'E0105-31', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1051, 'E0105-32', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1052, 'E0105-33', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1053, 'E0105-34', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1054, 'E0105-35', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1055, 'E0105-36', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1056, 'E0105-37', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1057, 'E0105-38', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1058, 'E0105-39', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1059, 'E0105-40', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1060, 'E0105-41', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1061, 'E0105-42', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1062, 'E0105-43', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1063, 'E0105-44', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1064, 'E0105-45', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1065, 'E0105-46', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1066, 'E0105-47', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1067, 'E0105-48', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1068, 'E0105-49', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1069, 'E0105-50', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1070, 'E0105-51', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1071, 'E0105-52', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1072, 'E0105-53', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1073, 'E0105-54', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1074, 'E0105-55', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1075, 'E0105-56', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1076, 'E0105-57', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1077, 'E0105-58', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1078, 'E0105-59', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1079, 'E0105-60', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1080, 'E0105-61', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1081, 'E0105-62', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1082, 'E0105-63', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1083, 'E0105-64', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1084, 'E0105-65', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1085, 'E0105-66', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1086, 'E0105-67', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1087, 'E0105-68', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1088, 'E0105-69', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1089, 'E0105-70', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1090, 'E0105-71', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1091, 'E0105-72', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1092, 'E0105-73', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1093, 'E0105-74', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1094, 'E0105-75', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1095, 'E0105-76', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1096, 'E0105-77', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1097, 'E0105-78', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1098, 'E0105-79', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1099, 'E0105-80', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1100, 'E0105-81', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1101, 'E0105-82', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1102, 'E0105-83', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1103, 'E0105-84', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1104, 'E0105-85', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1105, 'E0105-86', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1106, 'E0105-87', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1107, 'E0105-88', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1108, 'E0105-89', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1109, 'E0105-90', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1110, 'E0105-91', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1111, 'E0105-92', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1112, 'E0105-93', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1113, 'E0105-94', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1114, 'E0105-95', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1115, 'E0105-96', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1116, 'E0105-97', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1117, 'E0105-98', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1118, 'E0105-99', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1119, 'E0105-100', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1120, 'E0105-101', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1121, 'E0105-102', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1122, 'E0105-103', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1123, 'E0105-104', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1124, 'E0105-105', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1125, 'E0105-106', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1126, 'E0105-107', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1127, 'E0105-108', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1128, 'E0105-109', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1129, 'E0105-110', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1130, 'E0105-111', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1131, 'E0105-112', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1132, 'E0105-113', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1133, 'E0105-114', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1134, 'E0105-115', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1135, 'E0105-116', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1136, 'E0105-117', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1137, 'E0105-118', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1138, 'E0105-119', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1139, 'E0105-120', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1140, 'E0105-121', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1141, 'E0105-122', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1142, 'E0105-123', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1143, 'E0105-124', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1144, 'E0105-125', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1145, 'E0105-126', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1146, 'E0105-127', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1147, 'E0105-128', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1148, 'E0105-129', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1149, 'E0105-130', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1150, 'E0105-131', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1151, 'E0105-132', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1152, 'E0105-133', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1153, 'E0105-134', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1154, 'E0105-135', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1155, 'E0105-136', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1156, 'E0105-137', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1157, 'E0105-138', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1158, 'E0105-139', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1159, 'E0105-140', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1160, 'E0105-141', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1161, 'E0105-142', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1162, 'E0105-143', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1163, 'E0105-144', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1164, 'E0105-145', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1165, 'E0105-146', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1166, 'E0105-147', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1167, 'E0105-148', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1168, 'E0105-149', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1169, 'E0105-150', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1170, 'E0105-151', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1171, 'E0105-152', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1172, 'E0105-153', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1173, 'E0105-154', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1174, 'E0105-155', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1175, 'E0105-156', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1176, 'E0105-157', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1177, 'E0105-158', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1178, 'E0105-159', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1179, 'E0105-160', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1180, 'E0105-161', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1181, 'E0105-162', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1182, 'E0105-163', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1183, 'E0105-164', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1184, 'E0105-165', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1185, 'E0105-166', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1186, 'E0105-167', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1187, 'E0105-168', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1188, 'E0105-169', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1189, 'E0105-170', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1190, 'E0105-171', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1191, 'E0105-172', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1192, 'E0105-173', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1193, 'E0105-174', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1194, 'E0105-175', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1195, 'E0105-176', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1196, 'E0105-177', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1197, 'E0105-178', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1198, 'E0105-179', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1199, 'E0105-180', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1200, 'E0105-181', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1201, 'E0105-182', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1202, 'E0105-183', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1203, 'E0105-184', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1204, 'E0105-185', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1205, 'E0105-186', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1206, 'E0105-187', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1207, 'E0105-188', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1208, 'E0105-189', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1209, 'E0105-190', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1210, 'E0105-191', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1211, 'E0105-192', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1212, 'E0105-193', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1213, 'E0105-194', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1214, 'E0105-195', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1215, 'E0105-196', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1216, 'E0105-197', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1217, 'E0105-198', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1218, 'E0105-199', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1219, 'E0105-200', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1220, 'E0105-201', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1221, 'E0105-202', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1222, 'E0105-203', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1223, 'E0105-204', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1224, 'E0105-205', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1225, 'E0105-206', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1226, 'E0105-207', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1227, 'E0105-208', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1228, 'E0105-209', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1229, 'E0105-210', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1230, 'E0105-211', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1231, 'E0105-212', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1232, 'E0105-213', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1233, 'E0105-214', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1234, 'E0105-215', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1235, 'E0105-216', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1236, 'E0105-217', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1237, 'E0105-218', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1238, 'E0105-219', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1239, 'E0105-220', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1240, 'E0105-221', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1241, 'E0105-222', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1242, 'E0105-223', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1243, 'E0105-224', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1244, 'E0105-225', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1245, 'E0105-226', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1246, 'E0105-227', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1247, 'E0105-228', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1248, 'E0105-229', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1249, 'E0105-230', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1250, 'E0105-231', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1251, 'E0105-232', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1252, 'E0105-233', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1253, 'E0105-234', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1254, 'E0105-235', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1255, 'E0105-236', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1256, 'E0105-237', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1257, 'E0105-238', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1258, 'E0105-239', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1259, 'E0105-240', 'E', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1260, 'F0105-01', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1261, 'F0105-02', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1262, 'F0105-03', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1263, 'F0105-04', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1264, 'F0105-05', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1265, 'F0105-06', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1266, 'F0105-07', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1267, 'F0105-08', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1268, 'F0105-09', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1269, 'F0105-10', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1270, 'F0105-11', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1271, 'F0105-12', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1272, 'F0105-13', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1273, 'F0105-14', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1274, 'F0105-15', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1275, 'F0105-16', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1276, 'F0105-17', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1277, 'F0105-18', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1278, 'F0105-19', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1279, 'F0105-20', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1280, 'F0105-21', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1281, 'F0105-22', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1282, 'F0105-23', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1283, 'F0105-24', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1284, 'F0105-25', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1285, 'F0105-26', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1286, 'F0105-27', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1287, 'F0105-28', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1288, 'F0105-29', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1289, 'F0105-30', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1290, 'F0105-31', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1291, 'F0105-32', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1292, 'F0105-33', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1293, 'F0105-34', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1294, 'F0105-35', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1295, 'F0105-36', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1296, 'F0105-37', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1297, 'F0105-38', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1298, 'F0105-39', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1299, 'F0105-40', 'F', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1300, 'G0105-01', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1301, 'G0105-02', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1302, 'G0105-03', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1303, 'G0105-04', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1304, 'G0105-05', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1305, 'G0105-06', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1306, 'G0105-07', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1307, 'G0105-08', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1308, 'G0105-09', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1309, 'G0105-10', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1310, 'G0105-11', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1311, 'G0105-12', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1312, 'G0105-13', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1313, 'G0105-14', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1314, 'G0105-15', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1315, 'G0105-16', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1316, 'G0105-17', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1317, 'G0105-18', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1318, 'G0105-19', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1319, 'G0105-20', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1320, 'G0105-21', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1321, 'G0105-22', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1322, 'G0105-23', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1323, 'G0105-24', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1324, 'G0105-25', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1325, 'G0105-26', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1326, 'G0105-27', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1327, 'G0105-28', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1328, 'G0105-29', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1329, 'G0105-30', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1330, 'G0105-31', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1331, 'G0105-32', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1332, 'G0105-33', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1333, 'G0105-34', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1334, 'G0105-35', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1335, 'G0105-36', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1336, 'G0105-37', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1337, 'G0105-38', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1338, 'G0105-39', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1339, 'G0105-40', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1340, 'G0105-41', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1341, 'G0105-42', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1342, 'G0105-43', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1343, 'G0105-44', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1344, 'G0105-45', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1345, 'G0105-46', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1346, 'G0105-47', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1347, 'G0105-48', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1348, 'G0105-49', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1349, 'G0105-50', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1350, 'G0105-51', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1351, 'G0105-52', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1352, 'G0105-53', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1353, 'G0105-54', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1354, 'G0105-55', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1355, 'G0105-56', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1356, 'G0105-57', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1357, 'G0105-58', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1358, 'G0105-59', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1359, 'G0105-60', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1360, 'G0105-61', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1361, 'G0105-62', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1362, 'G0105-63', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1363, 'G0105-64', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1364, 'G0105-65', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1365, 'G0105-66', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1366, 'G0105-67', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1367, 'G0105-68', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1368, 'G0105-69', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1369, 'G0105-70', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1370, 'G0105-71', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1371, 'G0105-72', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1372, 'G0105-73', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1373, 'G0105-74', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1374, 'G0105-75', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1375, 'G0105-76', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1376, 'G0105-77', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1377, 'G0105-78', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1378, 'G0105-79', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1379, 'G0105-80', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1380, 'G0105-81', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1381, 'G0105-82', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1382, 'G0105-83', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1383, 'G0105-84', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1384, 'G0105-85', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1385, 'G0105-86', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1386, 'G0105-87', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1387, 'G0105-88', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1388, 'G0105-89', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1389, 'G0105-90', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1390, 'G0105-91', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1391, 'G0105-92', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1392, 'G0105-93', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1393, 'G0105-94', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1394, 'G0105-95', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1395, 'G0105-96', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1396, 'G0105-97', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1397, 'G0105-98', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1398, 'G0105-99', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1399, 'G0105-100', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1400, 'G0105-101', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1401, 'G0105-102', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1402, 'G0105-103', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1403, 'G0105-104', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1404, 'G0105-105', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1405, 'G0105-106', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1406, 'G0105-107', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1407, 'G0105-108', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1408, 'G0105-109', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(1409, 'G0105-110', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1410, 'G0105-111', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1411, 'G0105-112', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1412, 'G0105-113', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1413, 'G0105-114', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1414, 'G0105-115', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1415, 'G0105-116', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1416, 'G0105-117', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1417, 'G0105-118', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1418, 'G0105-119', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1419, 'G0105-120', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1420, 'G0105-121', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1421, 'G0105-122', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1422, 'G0105-123', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1423, 'G0105-124', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1424, 'G0105-125', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1425, 'G0105-126', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1426, 'G0105-127', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1427, 'G0105-128', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1428, 'G0105-129', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1429, 'G0105-130', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1430, 'G0105-131', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1431, 'G0105-132', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1432, 'G0105-133', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1433, 'G0105-134', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1434, 'G0105-135', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1435, 'G0105-136', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1436, 'G0105-137', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1437, 'G0105-138', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1438, 'G0105-139', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1439, 'G0105-140', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1440, 'G0105-141', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1441, 'G0105-142', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1442, 'G0105-143', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1443, 'G0105-144', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1444, 'G0105-145', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1445, 'G0105-146', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1446, 'G0105-147', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1447, 'G0105-148', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1448, 'G0105-149', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1449, 'G0105-150', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1450, 'G0105-151', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1451, 'G0105-152', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1452, 'G0105-153', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1453, 'G0105-154', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1454, 'G0105-155', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1455, 'G0105-156', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1456, 'G0105-157', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1457, 'G0105-158', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1458, 'G0105-159', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1459, 'G0105-160', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1460, 'G0105-161', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1461, 'G0105-162', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1462, 'G0105-163', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1463, 'G0105-164', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1464, 'G0105-165', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1465, 'G0105-166', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1466, 'G0105-167', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1467, 'G0105-168', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1468, 'G0105-169', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1469, 'G0105-170', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1470, 'G0105-171', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1471, 'G0105-172', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1472, 'G0105-173', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1473, 'G0105-174', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1474, 'G0105-175', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1475, 'G0105-176', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1476, 'G0105-177', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1477, 'G0105-178', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1478, 'G0105-179', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1479, 'G0105-180', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1480, 'G0105-181', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1481, 'G0105-182', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1482, 'G0105-183', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1483, 'G0105-184', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1484, 'G0105-185', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1485, 'G0105-186', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1486, 'G0105-187', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1487, 'G0105-188', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1488, 'G0105-189', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1489, 'G0105-190', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1490, 'G0105-191', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1491, 'G0105-192', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1492, 'G0105-193', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1493, 'G0105-194', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1494, 'G0105-195', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1495, 'G0105-196', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1496, 'G0105-197', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1497, 'G0105-198', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1498, 'G0105-199', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1499, 'G0105-200', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1500, 'G0105-201', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1501, 'G0105-202', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1502, 'G0105-203', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1503, 'G0105-204', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1504, 'G0105-205', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1505, 'G0105-206', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1506, 'G0105-207', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1507, 'G0105-208', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1508, 'G0105-209', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1509, 'G0105-210', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1510, 'G0105-211', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1511, 'G0105-212', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1512, 'G0105-213', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1513, 'G0105-214', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1514, 'G0105-215', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1515, 'G0105-216', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1516, 'G0105-217', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1517, 'G0105-218', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1518, 'G0105-219', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1519, 'G0105-220', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1520, 'G0105-221', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1521, 'G0105-222', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1522, 'G0105-223', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1523, 'G0105-224', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1524, 'G0105-225', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1525, 'G0105-226', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1526, 'G0105-227', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1527, 'G0105-228', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1528, 'G0105-229', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1529, 'G0105-230', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1530, 'G0105-231', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1531, 'G0105-232', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1532, 'G0105-233', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:10'),
(1533, 'G0105-234', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1534, 'G0105-235', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1535, 'G0105-236', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1536, 'G0105-237', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1537, 'G0105-238', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1538, 'G0105-239', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1539, 'G0105-240', 'G', '1x0.5', 0.50, 2, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1540, 'A0505-185', 'A', '0.5x0.5', 0.25, 3, 'pledged', 1, NULL, 'Samia Ahmed', 100.00, '2025-08-30 08:44:29', '2025-08-20 21:22:11'),
(1541, 'A0505-186', 'A', '0.5x0.5', 0.25, 3, 'pledged', 2, NULL, 'Degole seboka', 100.00, '2025-08-30 08:44:40', '2025-08-20 21:22:11'),
(1542, 'A0505-187', 'A', '0.5x0.5', 0.25, 3, 'pledged', 3, NULL, 'Deborah Seboka', 100.00, '2025-08-30 08:44:53', '2025-08-20 21:22:11'),
(1543, 'A0505-188', 'A', '0.5x0.5', 0.25, 3, 'pledged', 4, NULL, 'Dinah Seboka', 100.00, '2025-08-30 08:44:55', '2025-08-20 21:22:11'),
(1544, 'A0505-189', 'A', '0.5x0.5', 0.25, 3, 'pledged', 5, NULL, 'Daniella Seboka', 100.00, '2025-08-30 08:44:59', '2025-08-20 21:22:11'),
(1545, 'A0505-190', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:45:03', '2025-08-20 21:22:11'),
(1546, 'A0505-191', 'A', '0.5x0.5', 0.25, 3, 'pledged', 9, NULL, 'Anonymous', 100.00, '2025-08-30 08:47:24', '2025-08-20 21:22:11'),
(1547, 'A0505-192', 'A', '0.5x0.5', 0.25, 3, 'pledged', 9, NULL, 'Anonymous', 100.00, '2025-08-30 08:47:24', '2025-08-20 21:22:11'),
(1548, 'A0505-193', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 2, 'Robel kifle', 100.00, '2025-08-30 08:47:53', '2025-08-20 21:22:11'),
(1549, 'A0505-194', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:49:17', '2025-08-20 21:22:11'),
(1550, 'A0505-195', 'A', '0.5x0.5', 0.25, 3, 'pledged', 11, NULL, 'Abera', 100.00, '2025-08-30 08:49:30', '2025-08-20 21:22:11'),
(1551, 'A0505-196', 'A', '0.5x0.5', 0.25, 3, 'pledged', 12, NULL, 'Messing Aregay', 100.00, '2025-08-30 08:49:45', '2025-08-20 21:22:11'),
(1552, 'A0505-197', 'A', '0.5x0.5', 0.25, 3, 'pledged', 12, NULL, 'Messing Aregay', 100.00, '2025-08-30 08:49:45', '2025-08-20 21:22:11'),
(1553, 'A0505-198', 'A', '0.5x0.5', 0.25, 3, 'pledged', 13, NULL, 'Frehiwot Tadese', 100.00, '2025-08-30 08:50:01', '2025-08-20 21:22:11'),
(1554, 'A0505-199', 'A', '0.5x0.5', 0.25, 3, 'pledged', 13, NULL, 'Frehiwot Tadese', 100.00, '2025-08-30 08:50:01', '2025-08-20 21:22:11'),
(1555, 'A0505-200', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 5, 'Mulubrhan s jemaw', 100.00, '2025-08-30 08:50:03', '2025-08-20 21:22:11'),
(1556, 'A0505-201', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 5, 'Mulubrhan s jemaw', 100.00, '2025-08-30 08:50:03', '2025-08-20 21:22:11'),
(1557, 'A0505-202', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 5, 'Mulubrhan s jemaw', 100.00, '2025-08-30 08:50:03', '2025-08-20 21:22:11'),
(1558, 'A0505-203', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 5, 'Mulubrhan s jemaw', 100.00, '2025-08-30 08:50:03', '2025-08-20 21:22:11'),
(1559, 'A0505-204', 'A', '0.5x0.5', 0.25, 3, 'pledged', 14, NULL, 'Mekdese mari yam', 100.00, '2025-08-30 08:50:07', '2025-08-20 21:22:11'),
(1560, 'A0505-205', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:50:18', '2025-08-20 21:22:11'),
(1561, 'A0505-206', 'A', '0.5x0.5', 0.25, 3, 'pledged', 17, NULL, 'Serkalem Molla', 100.00, '2025-08-30 08:50:39', '2025-08-20 21:22:11'),
(1562, 'A0505-207', 'A', '0.5x0.5', 0.25, 3, 'pledged', 17, NULL, 'Serkalem Molla', 100.00, '2025-08-30 08:50:39', '2025-08-20 21:22:11'),
(1563, 'A0505-208', 'A', '0.5x0.5', 0.25, 3, 'pledged', 18, NULL, 'Daniel', 100.00, '2025-08-30 08:50:58', '2025-08-20 21:22:11'),
(1564, 'A0505-209', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:50:59', '2025-08-20 21:22:11'),
(1565, 'A0505-210', 'A', '0.5x0.5', 0.25, 3, 'pledged', 20, NULL, 'Hanna', 100.00, '2025-08-30 08:51:03', '2025-08-20 21:22:11'),
(1566, 'A0505-211', 'A', '0.5x0.5', 0.25, 3, 'pledged', 20, NULL, 'Hanna', 100.00, '2025-08-30 08:51:03', '2025-08-20 21:22:11'),
(1567, 'A0505-212', 'A', '0.5x0.5', 0.25, 3, 'pledged', 21, NULL, 'Dawit brhane', 100.00, '2025-08-30 08:51:04', '2025-08-20 21:22:11'),
(1568, 'A0505-213', 'A', '0.5x0.5', 0.25, 3, 'pledged', 22, NULL, 'Kidist Shewandagn', 100.00, '2025-08-30 08:51:05', '2025-08-20 21:22:11'),
(1569, 'A0505-214', 'A', '0.5x0.5', 0.25, 3, 'pledged', 23, NULL, 'Mulutsega Girma', 100.00, '2025-08-30 08:51:07', '2025-08-20 21:22:11'),
(1570, 'A0505-215', 'A', '0.5x0.5', 0.25, 3, 'pledged', 24, NULL, 'Solomon', 100.00, '2025-08-30 08:51:09', '2025-08-20 21:22:11'),
(1571, 'A0505-216', 'A', '0.5x0.5', 0.25, 3, 'pledged', 24, NULL, 'Solomon', 100.00, '2025-08-30 08:51:09', '2025-08-20 21:22:11'),
(1572, 'A0505-217', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 8, 'Daniel Samuel', 100.00, '2025-08-30 08:51:12', '2025-08-20 21:22:11'),
(1573, 'A0505-218', 'A', '0.5x0.5', 0.25, 3, 'pledged', 25, NULL, 'Eden', 100.00, '2025-08-30 08:52:31', '2025-08-20 21:22:11'),
(1574, 'A0505-219', 'A', '0.5x0.5', 0.25, 3, 'pledged', 25, NULL, 'Eden', 100.00, '2025-08-30 08:52:31', '2025-08-20 21:22:11'),
(1575, 'A0505-220', 'A', '0.5x0.5', 0.25, 3, 'pledged', 25, NULL, 'Eden', 100.00, '2025-08-30 08:52:31', '2025-08-20 21:22:11'),
(1576, 'A0505-221', 'A', '0.5x0.5', 0.25, 3, 'pledged', 25, NULL, 'Eden', 100.00, '2025-08-30 08:52:31', '2025-08-20 21:22:11'),
(1577, 'A0505-222', 'A', '0.5x0.5', 0.25, 3, 'pledged', 27, NULL, 'Brook', 100.00, '2025-08-30 08:52:34', '2025-08-20 21:22:11'),
(1578, 'A0505-223', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 9, 'Tesfaye Tessema', 100.00, '2025-08-30 08:52:34', '2025-08-20 21:22:11'),
(1579, 'A0505-224', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 9, 'Tesfaye Tessema', 100.00, '2025-08-30 08:52:34', '2025-08-20 21:22:11'),
(1580, 'A0505-225', 'A', '0.5x0.5', 0.25, 3, 'pledged', 28, NULL, 'Banchi negash', 100.00, '2025-08-30 08:52:35', '2025-08-20 21:22:11'),
(1581, 'A0505-226', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:52:37', '2025-08-20 21:22:11'),
(1582, 'A0505-227', 'A', '0.5x0.5', 0.25, 3, 'pledged', 29, NULL, 'Anonymous', 100.00, '2025-08-30 08:52:38', '2025-08-20 21:22:11'),
(1583, 'A0505-228', 'A', '0.5x0.5', 0.25, 3, 'pledged', 29, NULL, 'Anonymous', 100.00, '2025-08-30 08:52:38', '2025-08-20 21:22:11'),
(1584, 'A0505-229', 'A', '0.5x0.5', 0.25, 3, 'pledged', 29, NULL, 'Anonymous', 100.00, '2025-08-30 08:52:38', '2025-08-20 21:22:11'),
(1585, 'A0505-230', 'A', '0.5x0.5', 0.25, 3, 'pledged', 29, NULL, 'Anonymous', 100.00, '2025-08-30 08:52:38', '2025-08-20 21:22:11'),
(1586, 'A0505-231', 'A', '0.5x0.5', 0.25, 3, 'pledged', 30, NULL, 'Demelash Banjaw', 100.00, '2025-08-30 08:52:39', '2025-08-20 21:22:11'),
(1587, 'A0505-232', 'A', '0.5x0.5', 0.25, 3, 'pledged', 30, NULL, 'Demelash Banjaw', 100.00, '2025-08-30 08:52:39', '2025-08-20 21:22:11'),
(1588, 'A0505-233', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:52:41', '2025-08-20 21:22:11'),
(1589, 'A0505-234', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:52:41', '2025-08-20 21:22:11'),
(1590, 'A0505-235', 'A', '0.5x0.5', 0.25, 3, 'pledged', 31, NULL, 'Boja brook', 100.00, '2025-08-30 08:53:19', '2025-08-20 21:22:11'),
(1591, 'A0505-236', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 13, 'Samsung Taye', 100.00, '2025-08-30 08:53:33', '2025-08-20 21:22:11'),
(1592, 'A0505-237', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 13, 'Samsung Taye', 100.00, '2025-08-30 08:53:33', '2025-08-20 21:22:11'),
(1593, 'A0505-238', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 13, 'Samsung Taye', 100.00, '2025-08-30 08:53:33', '2025-08-20 21:22:11'),
(1594, 'A0505-239', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 13, 'Samsung Taye', 100.00, '2025-08-30 08:53:33', '2025-08-20 21:22:11'),
(1595, 'A0505-240', 'A', '0.5x0.5', 0.25, 3, 'pledged', 32, NULL, 'Abraham Gobeze', 100.00, '2025-08-30 08:53:42', '2025-08-20 21:22:11'),
(1596, 'A0505-241', 'A', '0.5x0.5', 0.25, 3, 'pledged', 32, NULL, 'Abraham Gobeze', 100.00, '2025-08-30 08:53:42', '2025-08-20 21:22:11'),
(1597, 'A0505-242', 'A', '0.5x0.5', 0.25, 3, 'pledged', 33, NULL, 'ALEX ASHENAFI (GLASGOW)', 100.00, '2025-08-30 08:53:56', '2025-08-20 21:22:11'),
(1598, 'A0505-243', 'A', '0.5x0.5', 0.25, 3, 'pledged', 33, NULL, 'ALEX ASHENAFI (GLASGOW)', 100.00, '2025-08-30 08:53:56', '2025-08-20 21:22:11'),
(1599, 'A0505-244', 'A', '0.5x0.5', 0.25, 3, 'pledged', 34, NULL, 'Yoseph', 100.00, '2025-08-30 08:54:21', '2025-08-20 21:22:11'),
(1600, 'A0505-245', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 14, 'Bamlak and yannick', 100.00, '2025-08-30 08:54:23', '2025-08-20 21:22:11'),
(1601, 'A0505-246', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 15, 'Anonymous', 100.00, '2025-08-30 08:55:23', '2025-08-20 21:22:11'),
(1602, 'A0505-247', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 15, 'Anonymous', 100.00, '2025-08-30 08:55:23', '2025-08-20 21:22:11'),
(1603, 'A0505-248', 'A', '0.5x0.5', 0.25, 3, 'pledged', 35, NULL, 'Maza', 100.00, '2025-08-30 08:55:27', '2025-08-20 21:22:11'),
(1604, 'A0505-249', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 16, 'Aderagw tadele', 100.00, '2025-08-30 08:55:29', '2025-08-20 21:22:11'),
(1605, 'A0505-250', 'A', '0.5x0.5', 0.25, 3, 'pledged', 36, NULL, 'Bruk moges', 100.00, '2025-08-30 08:55:36', '2025-08-20 21:22:11'),
(1606, 'A0505-251', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 17, 'Haseit Desta', 100.00, '2025-08-30 08:56:10', '2025-08-20 21:22:11'),
(1607, 'A0505-252', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 18, 'Kibrom teklu', 100.00, '2025-08-30 08:56:11', '2025-08-20 21:22:11'),
(1608, 'A0505-253', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 19, 'Rahel G/selase', 100.00, '2025-08-30 08:56:12', '2025-08-20 21:22:11'),
(1609, 'A0505-254', 'A', '0.5x0.5', 0.25, 3, 'pledged', 37, NULL, 'Amen Hailesillase', 100.00, '2025-08-30 08:56:14', '2025-08-20 21:22:11'),
(1610, 'A0505-255', 'A', '0.5x0.5', 0.25, 3, 'pledged', 37, NULL, 'Amen Hailesillase', 100.00, '2025-08-30 08:56:14', '2025-08-20 21:22:11'),
(1611, 'A0505-256', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 20, 'Faniel Tena Gashaw', 100.00, '2025-08-30 08:56:23', '2025-08-20 21:22:11'),
(1612, 'A0505-257', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 20, 'Faniel Tena Gashaw', 100.00, '2025-08-30 08:56:23', '2025-08-20 21:22:11'),
(1613, 'A0505-258', 'A', '0.5x0.5', 0.25, 3, 'pledged', 38, NULL, 'Soltana tekle', 100.00, '2025-08-30 08:56:34', '2025-08-20 21:22:11'),
(1614, 'A0505-259', 'A', '0.5x0.5', 0.25, 3, 'pledged', 38, NULL, 'Soltana tekle', 100.00, '2025-08-30 08:56:34', '2025-08-20 21:22:11'),
(1615, 'A0505-260', 'A', '0.5x0.5', 0.25, 3, 'pledged', 39, NULL, 'Wagi', 100.00, '2025-08-30 08:56:40', '2025-08-20 21:22:11'),
(1616, 'A0505-261', 'A', '0.5x0.5', 0.25, 3, 'pledged', 40, NULL, 'Anonymous', 100.00, '2025-08-30 08:56:47', '2025-08-20 21:22:11'),
(1617, 'A0505-262', 'A', '0.5x0.5', 0.25, 3, 'pledged', 40, NULL, 'Anonymous', 100.00, '2025-08-30 08:56:47', '2025-08-20 21:22:11'),
(1618, 'A0505-263', 'A', '0.5x0.5', 0.25, 3, 'pledged', 40, NULL, 'Anonymous', 100.00, '2025-08-30 08:56:47', '2025-08-20 21:22:11'),
(1619, 'A0505-264', 'A', '0.5x0.5', 0.25, 3, 'pledged', 40, NULL, 'Anonymous', 100.00, '2025-08-30 08:56:47', '2025-08-20 21:22:11'),
(1620, 'A0505-265', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 21, 'Efrem', 100.00, '2025-08-30 08:56:54', '2025-08-20 21:22:11'),
(1621, 'A0505-266', 'A', '0.5x0.5', 0.25, 3, 'pledged', 41, NULL, 'Senile Gebeeyes', 100.00, '2025-08-30 08:57:03', '2025-08-20 21:22:11'),
(1622, 'A0505-267', 'A', '0.5x0.5', 0.25, 3, 'pledged', 41, NULL, 'Senile Gebeeyes', 100.00, '2025-08-30 08:57:03', '2025-08-20 21:22:11'),
(1623, 'A0505-268', 'A', '0.5x0.5', 0.25, 3, 'pledged', 41, NULL, 'Senile Gebeeyes', 100.00, '2025-08-30 08:57:03', '2025-08-20 21:22:11'),
(1624, 'A0505-269', 'A', '0.5x0.5', 0.25, 3, 'pledged', 41, NULL, 'Senile Gebeeyes', 100.00, '2025-08-30 08:57:03', '2025-08-20 21:22:11'),
(1625, 'A0505-270', 'A', '0.5x0.5', 0.25, 3, 'pledged', 42, NULL, 'Nardos', 100.00, '2025-08-30 08:57:19', '2025-08-20 21:22:11'),
(1626, 'A0505-271', 'A', '0.5x0.5', 0.25, 3, 'pledged', 42, NULL, 'Nardos', 100.00, '2025-08-30 08:57:19', '2025-08-20 21:22:11'),
(1627, 'A0505-272', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 22, 'Bayush Kumssa', 100.00, '2025-08-30 08:57:25', '2025-08-20 21:22:11'),
(1628, 'A0505-273', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:57:59', '2025-08-20 21:22:11'),
(1629, 'A0505-274', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 08:58:03', '2025-08-20 21:22:11'),
(1630, 'A0505-275', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 26, 'Alemayew hayelu', 100.00, '2025-08-30 08:58:17', '2025-08-20 21:22:11'),
(1631, 'A0505-276', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 26, 'Alemayew hayelu', 100.00, '2025-08-30 08:58:17', '2025-08-20 21:22:11'),
(1632, 'A0505-277', 'A', '0.5x0.5', 0.25, 3, 'pledged', 43, NULL, 'Adiyam sahle', 100.00, '2025-08-30 08:58:26', '2025-08-20 21:22:11'),
(1633, 'A0505-278', 'A', '0.5x0.5', 0.25, 3, 'pledged', 44, NULL, 'Dr Getinet Mekuriaw Tarekegn Glasgow', 100.00, '2025-08-30 08:58:32', '2025-08-20 21:22:11'),
(1634, 'A0505-279', 'A', '0.5x0.5', 0.25, 3, 'pledged', 45, NULL, 'Genet Sebehatu', 100.00, '2025-08-30 08:58:35', '2025-08-20 21:22:11'),
(1635, 'A0505-280', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 27, 'Yonas abraham', 100.00, '2025-08-30 08:58:42', '2025-08-20 21:22:11'),
(1636, 'A0505-281', 'A', '0.5x0.5', 0.25, 3, 'pledged', 46, NULL, 'Abebe', 100.00, '2025-08-30 08:59:02', '2025-08-20 21:22:11'),
(1637, 'A0505-282', 'A', '0.5x0.5', 0.25, 3, 'pledged', 46, NULL, 'Abebe', 100.00, '2025-08-30 08:59:02', '2025-08-20 21:22:11'),
(1638, 'A0505-283', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 28, 'Fasil Kinde', 100.00, '2025-08-30 08:59:06', '2025-08-20 21:22:11'),
(1639, 'A0505-284', 'A', '0.5x0.5', 0.25, 3, 'pledged', 47, NULL, 'Elias Hailgebreil', 100.00, '2025-08-30 08:59:11', '2025-08-20 21:22:11'),
(1640, 'A0505-285', 'A', '0.5x0.5', 0.25, 3, 'pledged', 48, NULL, 'Meseret Yohannes', 100.00, '2025-08-30 09:00:09', '2025-08-20 21:22:11'),
(1641, 'A0505-286', 'A', '0.5x0.5', 0.25, 3, 'pledged', 49, NULL, 'Yakob tesfaye', 100.00, '2025-08-30 09:00:16', '2025-08-20 21:22:11'),
(1642, 'A0505-287', 'A', '0.5x0.5', 0.25, 3, 'pledged', 49, NULL, 'Yakob tesfaye', 100.00, '2025-08-30 09:00:16', '2025-08-20 21:22:11'),
(1643, 'A0505-288', 'A', '0.5x0.5', 0.25, 3, 'pledged', 50, NULL, 'Damtew ashenafi', 100.00, '2025-08-30 09:00:23', '2025-08-20 21:22:11'),
(1644, 'A0505-289', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 24, 'Glasgow', 100.00, '2025-08-30 09:00:36', '2025-08-20 21:22:11'),
(1645, 'A0505-290', 'A', '0.5x0.5', 0.25, 3, 'pledged', 51, NULL, 'Behailu Tihitina', 100.00, '2025-08-30 09:00:38', '2025-08-20 21:22:11'),
(1646, 'A0505-291', 'A', '0.5x0.5', 0.25, 3, 'pledged', 51, NULL, 'Behailu Tihitina', 100.00, '2025-08-30 09:00:38', '2025-08-20 21:22:11'),
(1647, 'A0505-292', 'A', '0.5x0.5', 0.25, 3, 'pledged', 51, NULL, 'Behailu Tihitina', 100.00, '2025-08-30 09:00:38', '2025-08-20 21:22:11'),
(1648, 'A0505-293', 'A', '0.5x0.5', 0.25, 3, 'pledged', 51, NULL, 'Behailu Tihitina', 100.00, '2025-08-30 09:00:38', '2025-08-20 21:22:11'),
(1649, 'A0505-294', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 12, 'Manchester', 100.00, '2025-08-30 09:00:39', '2025-08-20 21:22:11'),
(1650, 'A0505-295', 'A', '0.5x0.5', 0.25, 3, 'pledged', 52, NULL, 'Dawit weledkiros', 100.00, '2025-08-30 09:00:54', '2025-08-20 21:22:11'),
(1651, 'A0505-296', 'A', '0.5x0.5', 0.25, 3, 'pledged', 52, NULL, 'Dawit weledkiros', 100.00, '2025-08-30 09:00:54', '2025-08-20 21:22:11'),
(1652, 'A0505-297', 'A', '0.5x0.5', 0.25, 3, 'pledged', 53, NULL, 'TEWODROS TADESSE', 100.00, '2025-08-30 09:00:55', '2025-08-20 21:22:11'),
(1653, 'A0505-298', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 30, 'Mikael teshome', 100.00, '2025-08-30 09:00:59', '2025-08-20 21:22:11'),
(1654, 'A0505-299', 'A', '0.5x0.5', 0.25, 3, 'pledged', 54, NULL, 'Anonymous', 100.00, '2025-08-30 09:01:03', '2025-08-20 21:22:11'),
(1655, 'A0505-300', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 31, 'Birhanu Yemenu', 100.00, '2025-08-30 09:01:07', '2025-08-20 21:22:11'),
(1656, 'A0505-301', 'A', '0.5x0.5', 0.25, 3, 'pledged', 55, NULL, 'Anonymous', 100.00, '2025-08-30 09:01:12', '2025-08-20 21:22:11'),
(1657, 'A0505-302', 'A', '0.5x0.5', 0.25, 3, 'pledged', 56, NULL, 'Kaletsidk Fasil', 100.00, '2025-08-30 09:01:16', '2025-08-20 21:22:11'),
(1658, 'A0505-303', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 32, 'Fithi Teklit', 100.00, '2025-08-30 09:01:21', '2025-08-20 21:22:11'),
(1659, 'A0505-304', 'A', '0.5x0.5', 0.25, 3, 'pledged', 57, NULL, 'Chemeka', 100.00, '2025-08-30 09:01:25', '2025-08-20 21:22:11'),
(1660, 'A0505-305', 'A', '0.5x0.5', 0.25, 3, 'pledged', 57, NULL, 'Chemeka', 100.00, '2025-08-30 09:01:25', '2025-08-20 21:22:11'),
(1661, 'A0505-306', 'A', '0.5x0.5', 0.25, 3, 'pledged', 57, NULL, 'Chemeka', 100.00, '2025-08-30 09:01:25', '2025-08-20 21:22:11'),
(1662, 'A0505-307', 'A', '0.5x0.5', 0.25, 3, 'pledged', 57, NULL, 'Chemeka', 100.00, '2025-08-30 09:01:25', '2025-08-20 21:22:11'),
(1663, 'A0505-308', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 33, 'Kesis Mezmur', 100.00, '2025-08-30 09:01:31', '2025-08-20 21:22:11'),
(1664, 'A0505-309', 'A', '0.5x0.5', 0.25, 3, 'pledged', 58, NULL, 'Ehete maream', 100.00, '2025-08-30 09:01:34', '2025-08-20 21:22:11'),
(1665, 'A0505-310', 'A', '0.5x0.5', 0.25, 3, 'pledged', 59, NULL, 'Aberham', 100.00, '2025-08-30 09:01:38', '2025-08-20 21:22:11'),
(1666, 'A0505-311', 'A', '0.5x0.5', 0.25, 3, 'pledged', 60, NULL, 'Hundaftol Yohannes', 100.00, '2025-08-30 09:01:45', '2025-08-20 21:22:11'),
(1667, 'A0505-312', 'A', '0.5x0.5', 0.25, 3, 'pledged', 60, NULL, 'Hundaftol Yohannes', 100.00, '2025-08-30 09:01:45', '2025-08-20 21:22:11'),
(1668, 'A0505-313', 'A', '0.5x0.5', 0.25, 3, 'pledged', 60, NULL, 'Hundaftol Yohannes', 100.00, '2025-08-30 09:01:45', '2025-08-20 21:22:11'),
(1669, 'A0505-314', 'A', '0.5x0.5', 0.25, 3, 'pledged', 60, NULL, 'Hundaftol Yohannes', 100.00, '2025-08-30 09:01:45', '2025-08-20 21:22:11'),
(1670, 'A0505-315', 'A', '0.5x0.5', 0.25, 3, 'pledged', 61, NULL, 'Ephrem Retta', 100.00, '2025-08-30 09:01:51', '2025-08-20 21:22:11'),
(1671, 'A0505-316', 'A', '0.5x0.5', 0.25, 3, 'pledged', 62, NULL, 'Freweyni mekonen', 100.00, '2025-08-30 09:01:56', '2025-08-20 21:22:11'),
(1672, 'A0505-317', 'A', '0.5x0.5', 0.25, 3, 'pledged', 62, NULL, 'Freweyni mekonen', 100.00, '2025-08-30 09:01:56', '2025-08-20 21:22:11'),
(1673, 'A0505-318', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 34, 'Mahari melash', 100.00, '2025-08-30 09:02:12', '2025-08-20 21:22:11'),
(1674, 'A0505-319', 'A', '0.5x0.5', 0.25, 3, 'pledged', 63, NULL, 'Esubalew', 100.00, '2025-08-30 09:02:14', '2025-08-20 21:22:11'),
(1675, 'A0505-320', 'A', '0.5x0.5', 0.25, 3, 'pledged', 63, NULL, 'Esubalew', 100.00, '2025-08-30 09:02:14', '2025-08-20 21:22:11'),
(1676, 'A0505-321', 'A', '0.5x0.5', 0.25, 3, 'pledged', 63, NULL, 'Esubalew', 100.00, '2025-08-30 09:02:14', '2025-08-20 21:22:11'),
(1677, 'A0505-322', 'A', '0.5x0.5', 0.25, 3, 'pledged', 63, NULL, 'Esubalew', 100.00, '2025-08-30 09:02:14', '2025-08-20 21:22:11'),
(1678, 'A0505-323', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 36, 'Welete tekekehaymanot', 100.00, '2025-08-30 09:03:52', '2025-08-20 21:22:11'),
(1679, 'A0505-324', 'A', '0.5x0.5', 0.25, 3, 'pledged', 64, NULL, 'Henok senay', 100.00, '2025-08-30 09:03:54', '2025-08-20 21:22:11'),
(1680, 'A0505-325', 'A', '0.5x0.5', 0.25, 3, 'pledged', 64, NULL, 'Henok senay', 100.00, '2025-08-30 09:03:54', '2025-08-20 21:22:11'),
(1681, 'A0505-326', 'A', '0.5x0.5', 0.25, 3, 'pledged', 65, NULL, 'Welde Giorgis + Menbere Mariam', 100.00, '2025-08-30 09:03:57', '2025-08-20 21:22:11'),
(1682, 'A0505-327', 'A', '0.5x0.5', 0.25, 3, 'pledged', 65, NULL, 'Welde Giorgis + Menbere Mariam', 100.00, '2025-08-30 09:03:57', '2025-08-20 21:22:11'),
(1683, 'A0505-328', 'A', '0.5x0.5', 0.25, 3, 'pledged', 65, NULL, 'Welde Giorgis + Menbere Mariam', 100.00, '2025-08-30 09:03:57', '2025-08-20 21:22:11'),
(1684, 'A0505-329', 'A', '0.5x0.5', 0.25, 3, 'pledged', 65, NULL, 'Welde Giorgis + Menbere Mariam', 100.00, '2025-08-30 09:03:57', '2025-08-20 21:22:11'),
(1685, 'A0505-330', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 37, 'Anonymous', 100.00, '2025-08-30 09:04:04', '2025-08-20 21:22:11'),
(1686, 'A0505-331', 'A', '0.5x0.5', 0.25, 3, 'pledged', 66, NULL, 'Gebriye getachew', 100.00, '2025-08-30 09:04:08', '2025-08-20 21:22:11'),
(1687, 'A0505-332', 'A', '0.5x0.5', 0.25, 3, 'pledged', 67, NULL, 'Yarede mesefen', 100.00, '2025-08-30 09:04:14', '2025-08-20 21:22:11'),
(1688, 'A0505-333', 'A', '0.5x0.5', 0.25, 3, 'pledged', 68, NULL, 'Lucy and Dina', 100.00, '2025-08-30 09:04:18', '2025-08-20 21:22:11'),
(1689, 'A0505-334', 'A', '0.5x0.5', 0.25, 3, 'pledged', 69, NULL, 'Sara Aregay', 100.00, '2025-08-30 09:04:21', '2025-08-20 21:22:11'),
(1690, 'A0505-335', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:04:27', '2025-08-20 21:22:11'),
(1691, 'A0505-336', 'A', '0.5x0.5', 0.25, 3, 'pledged', 70, NULL, 'Bemenet Fasil', 100.00, '2025-08-30 09:04:32', '2025-08-20 21:22:11'),
(1692, 'A0505-337', 'A', '0.5x0.5', 0.25, 3, 'pledged', 71, NULL, 'Dahlak', 100.00, '2025-08-30 09:04:39', '2025-08-20 21:22:11'),
(1693, 'A0505-338', 'A', '0.5x0.5', 0.25, 3, 'pledged', 71, NULL, 'Dahlak', 100.00, '2025-08-30 09:04:39', '2025-08-20 21:22:11'),
(1694, 'A0505-339', 'A', '0.5x0.5', 0.25, 3, 'pledged', 71, NULL, 'Dahlak', 100.00, '2025-08-30 09:04:39', '2025-08-20 21:22:11'),
(1695, 'A0505-340', 'A', '0.5x0.5', 0.25, 3, 'pledged', 71, NULL, 'Dahlak', 100.00, '2025-08-30 09:04:39', '2025-08-20 21:22:11'),
(1696, 'A0505-341', 'A', '0.5x0.5', 0.25, 3, 'pledged', 72, NULL, 'Selam tadese', 100.00, '2025-08-30 09:04:46', '2025-08-20 21:22:11'),
(1697, 'A0505-342', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 40, 'Kebita', 100.00, '2025-08-30 09:04:52', '2025-08-20 21:22:11'),
(1698, 'A0505-343', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 40, 'Kebita', 100.00, '2025-08-30 09:04:52', '2025-08-20 21:22:11'),
(1699, 'A0505-344', 'A', '0.5x0.5', 0.25, 3, 'pledged', 73, NULL, 'Fseha mngstu', 100.00, '2025-08-30 09:04:54', '2025-08-20 21:22:11'),
(1700, 'A0505-345', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:05:39', '2025-08-20 21:22:11'),
(1701, 'A0505-346', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:08:11', '2025-08-20 21:22:11'),
(1702, 'A0505-347', 'A', '0.5x0.5', 0.25, 3, 'pledged', 74, NULL, 'Ashenafi yirga', 100.00, '2025-08-30 09:08:34', '2025-08-20 21:22:11'),
(1703, 'A0505-348', 'A', '0.5x0.5', 0.25, 3, 'pledged', 75, NULL, 'Habte Meskel', 100.00, '2025-08-30 09:08:39', '2025-08-20 21:22:11'),
(1704, 'A0505-349', 'A', '0.5x0.5', 0.25, 3, 'pledged', 75, NULL, 'Habte Meskel', 100.00, '2025-08-30 09:08:39', '2025-08-20 21:22:11'),
(1705, 'A0505-350', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 46, 'Sosna yegzaw', 100.00, '2025-08-30 09:08:46', '2025-08-20 21:22:11'),
(1706, 'A0505-351', 'A', '0.5x0.5', 0.25, 3, 'pledged', 76, NULL, 'Alem mulu', 100.00, '2025-08-30 09:08:52', '2025-08-20 21:22:11'),
(1707, 'A0505-352', 'A', '0.5x0.5', 0.25, 3, 'pledged', 77, NULL, 'Fasil Tesfaye', 100.00, '2025-08-30 09:08:59', '2025-08-20 21:22:11'),
(1708, 'A0505-353', 'A', '0.5x0.5', 0.25, 3, 'pledged', 77, NULL, 'Fasil Tesfaye', 100.00, '2025-08-30 09:08:59', '2025-08-20 21:22:11'),
(1709, 'A0505-354', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:09:01', '2025-08-20 21:22:11'),
(1710, 'A0505-355', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:14:44', '2025-08-20 21:22:11'),
(1711, 'A0505-356', 'A', '0.5x0.5', 0.25, 3, 'pledged', 78, NULL, 'Nanny fantahun', 100.00, '2025-08-30 09:15:03', '2025-08-20 21:22:11'),
(1712, 'A0505-357', 'A', '0.5x0.5', 0.25, 3, 'pledged', 78, NULL, 'Nanny fantahun', 100.00, '2025-08-30 09:15:03', '2025-08-20 21:22:11'),
(1713, 'A0505-358', 'A', '0.5x0.5', 0.25, 3, 'pledged', 78, NULL, 'Nanny fantahun', 100.00, '2025-08-30 09:15:03', '2025-08-20 21:22:11'),
(1714, 'A0505-359', 'A', '0.5x0.5', 0.25, 3, 'pledged', 78, NULL, 'Nanny fantahun', 100.00, '2025-08-30 09:15:03', '2025-08-20 21:22:11'),
(1715, 'A0505-360', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 49, 'Heyaw demeke', 100.00, '2025-08-30 09:15:10', '2025-08-20 21:22:11'),
(1716, 'A0505-361', 'A', '0.5x0.5', 0.25, 3, 'pledged', 79, NULL, 'Anonymous', 100.00, '2025-08-30 09:15:12', '2025-08-20 21:22:11'),
(1717, 'A0505-362', 'A', '0.5x0.5', 0.25, 3, 'pledged', 80, NULL, 'Yonas abraham', 100.00, '2025-08-30 09:15:15', '2025-08-20 21:22:11'),
(1718, 'A0505-363', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 50, 'Emebet worku', 100.00, '2025-08-30 09:15:20', '2025-08-20 21:22:11'),
(1719, 'A0505-364', 'A', '0.5x0.5', 0.25, 3, 'pledged', 81, NULL, 'Meselewerk legese', 100.00, '2025-08-30 09:15:24', '2025-08-20 21:22:11'),
(1720, 'A0505-365', 'A', '0.5x0.5', 0.25, 3, 'pledged', 82, NULL, 'Abenzer', 100.00, '2025-08-30 09:15:26', '2025-08-20 21:22:11'),
(1721, 'A0505-366', 'A', '0.5x0.5', 0.25, 3, 'pledged', 83, NULL, 'Senait Kidene', 100.00, '2025-08-30 09:15:28', '2025-08-20 21:22:11'),
(1722, 'A0505-367', 'A', '0.5x0.5', 0.25, 3, 'pledged', 84, NULL, 'Ruth dagim', 100.00, '2025-08-30 09:15:30', '2025-08-20 21:22:11'),
(1723, 'A0505-368', 'A', '0.5x0.5', 0.25, 3, 'pledged', 85, NULL, 'Eliyana yoseph', 100.00, '2025-08-30 09:15:39', '2025-08-20 21:22:11'),
(1724, 'A0505-369', 'A', '0.5x0.5', 0.25, 3, 'pledged', 85, NULL, 'Eliyana yoseph', 100.00, '2025-08-30 09:15:39', '2025-08-20 21:22:11'),
(1725, 'A0505-370', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 53, 'Yohanis aklilu', 100.00, '2025-08-30 09:15:40', '2025-08-20 21:22:11'),
(1726, 'A0505-371', 'A', '0.5x0.5', 0.25, 3, 'pledged', 86, NULL, 'Mekdelawit s Asefa', 100.00, '2025-08-30 09:15:46', '2025-08-20 21:22:11'),
(1727, 'A0505-372', 'A', '0.5x0.5', 0.25, 3, 'pledged', 86, NULL, 'Mekdelawit s Asefa', 100.00, '2025-08-30 09:15:46', '2025-08-20 21:22:11'),
(1728, 'A0505-373', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 54, 'Welete tinsae', 100.00, '2025-08-30 09:15:47', '2025-08-20 21:22:11'),
(1729, 'A0505-374', 'A', '0.5x0.5', 0.25, 3, 'pledged', 87, NULL, 'Hiwan gebrehiwit', 100.00, '2025-08-30 09:15:51', '2025-08-20 21:22:11'),
(1730, 'A0505-375', 'A', '0.5x0.5', 0.25, 3, 'pledged', 87, NULL, 'Hiwan gebrehiwit', 100.00, '2025-08-30 09:15:51', '2025-08-20 21:22:11'),
(1731, 'A0505-376', 'A', '0.5x0.5', 0.25, 3, 'pledged', 90, NULL, 'Abeba tamene', 100.00, '2025-08-30 09:15:54', '2025-08-20 21:22:11'),
(1732, 'A0505-377', 'A', '0.5x0.5', 0.25, 3, 'pledged', 90, NULL, 'Abeba tamene', 100.00, '2025-08-30 09:15:54', '2025-08-20 21:22:11'),
(1733, 'A0505-378', 'A', '0.5x0.5', 0.25, 3, 'pledged', 91, NULL, 'Suzane Abera', 100.00, '2025-08-30 09:16:28', '2025-08-20 21:22:11'),
(1734, 'A0505-379', 'A', '0.5x0.5', 0.25, 3, 'pledged', 92, NULL, 'Desert Haileselassie /Grace Jones', 100.00, '2025-08-30 09:16:34', '2025-08-20 21:22:11'),
(1735, 'A0505-380', 'A', '0.5x0.5', 0.25, 3, 'pledged', 92, NULL, 'Desert Haileselassie /Grace Jones', 100.00, '2025-08-30 09:16:34', '2025-08-20 21:22:11'),
(1736, 'A0505-381', 'A', '0.5x0.5', 0.25, 3, 'pledged', 92, NULL, 'Desert Haileselassie /Grace Jones', 100.00, '2025-08-30 09:16:34', '2025-08-20 21:22:11'),
(1737, 'A0505-382', 'A', '0.5x0.5', 0.25, 3, 'pledged', 92, NULL, 'Desert Haileselassie /Grace Jones', 100.00, '2025-08-30 09:16:34', '2025-08-20 21:22:11'),
(1738, 'A0505-383', 'A', '0.5x0.5', 0.25, 3, 'pledged', 93, NULL, 'Elias Shiferaw', 100.00, '2025-08-30 09:16:39', '2025-08-20 21:22:11'),
(1739, 'A0505-384', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:18:56', '2025-08-20 21:22:11'),
(1740, 'A0505-385', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:19:05', '2025-08-20 21:22:11'),
(1741, 'A0505-386', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 55, 'Hailegiyorgis Families', 100.00, '2025-08-30 09:19:30', '2025-08-20 21:22:11'),
(1742, 'A0505-387', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 55, 'Hailegiyorgis Families', 100.00, '2025-08-30 09:19:30', '2025-08-20 21:22:11'),
(1743, 'A0505-388', 'A', '0.5x0.5', 0.25, 3, 'pledged', 95, NULL, 'Bizu', 100.00, '2025-08-30 09:19:34', '2025-08-20 21:22:11'),
(1744, 'A0505-389', 'A', '0.5x0.5', 0.25, 3, 'pledged', 96, NULL, 'Helen Tesfaye', 100.00, '2025-08-30 09:19:40', '2025-08-20 21:22:11'),
(1745, 'A0505-390', 'A', '0.5x0.5', 0.25, 3, 'pledged', 97, NULL, 'Sentayehu taye', 100.00, '2025-08-30 09:20:30', '2025-08-20 21:22:11'),
(1746, 'A0505-391', 'A', '0.5x0.5', 0.25, 3, 'pledged', 97, NULL, 'Sentayehu taye', 100.00, '2025-08-30 09:20:30', '2025-08-20 21:22:11'),
(1747, 'A0505-392', 'A', '0.5x0.5', 0.25, 3, 'pledged', 98, NULL, 'Tamiru Legesse', 100.00, '2025-08-30 09:20:36', '2025-08-20 21:22:11'),
(1748, 'A0505-393', 'A', '0.5x0.5', 0.25, 3, 'pledged', 99, NULL, 'Eldana Hagos', 100.00, '2025-08-30 09:20:42', '2025-08-20 21:22:11'),
(1749, 'A0505-394', 'A', '0.5x0.5', 0.25, 3, 'pledged', 100, NULL, 'Arsema', 100.00, '2025-08-30 09:20:48', '2025-08-20 21:22:11'),
(1750, 'A0505-395', 'A', '0.5x0.5', 0.25, 3, 'pledged', 100, NULL, 'Arsema', 100.00, '2025-08-30 09:20:48', '2025-08-20 21:22:11'),
(1751, 'A0505-396', 'A', '0.5x0.5', 0.25, 3, 'pledged', 100, NULL, 'Arsema', 100.00, '2025-08-30 09:20:48', '2025-08-20 21:22:11'),
(1752, 'A0505-397', 'A', '0.5x0.5', 0.25, 3, 'pledged', 100, NULL, 'Arsema', 100.00, '2025-08-30 09:20:48', '2025-08-20 21:22:11'),
(1753, 'A0505-398', 'A', '0.5x0.5', 0.25, 3, 'pledged', 101, NULL, 'Michael Tesfaye', 100.00, '2025-08-30 09:21:03', '2025-08-20 21:22:11'),
(1754, 'A0505-399', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 59, 'Welete giwergis', 100.00, '2025-08-30 09:21:05', '2025-08-20 21:22:11'),
(1755, 'A0505-400', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 59, 'Welete giwergis', 100.00, '2025-08-30 09:21:05', '2025-08-20 21:22:11'),
(1756, 'A0505-401', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 60, 'Mahlet  Tamrat', 100.00, '2025-08-30 09:21:07', '2025-08-20 21:22:11'),
(1757, 'A0505-402', 'A', '0.5x0.5', 0.25, 3, 'pledged', 102, NULL, 'Meron selish', 100.00, '2025-08-30 09:22:01', '2025-08-20 21:22:11'),
(1758, 'A0505-403', 'A', '0.5x0.5', 0.25, 3, 'pledged', 103, NULL, 'Chernat', 100.00, '2025-08-30 09:22:18', '2025-08-20 21:22:11'),
(1759, 'A0505-404', 'A', '0.5x0.5', 0.25, 3, 'pledged', 104, NULL, 'Gelila tezeta', 100.00, '2025-08-30 09:22:23', '2025-08-20 21:22:11'),
(1760, 'A0505-405', 'A', '0.5x0.5', 0.25, 3, 'pledged', 105, NULL, 'Natinael mesefin', 100.00, '2025-08-30 09:22:51', '2025-08-20 21:22:11'),
(1761, 'A0505-406', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:24:30', '2025-08-20 21:22:11'),
(1762, 'A0505-407', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 61, 'Eliyana yoseph', 100.00, '2025-08-30 09:24:49', '2025-08-20 21:22:11'),
(1763, 'A0505-408', 'A', '0.5x0.5', 0.25, 3, 'pledged', 107, NULL, 'Muluken Nigatu', 100.00, '2025-08-30 09:24:50', '2025-08-20 21:22:11'),
(1764, 'A0505-409', 'A', '0.5x0.5', 0.25, 3, 'pledged', 108, NULL, 'Michael Gebriyesus', 100.00, '2025-08-30 09:26:47', '2025-08-20 21:22:11'),
(1765, 'A0505-410', 'A', '0.5x0.5', 0.25, 3, 'paid', NULL, 64, 'Kidus Kidane', 100.00, '2025-08-30 09:26:59', '2025-08-20 21:22:11'),
(1766, 'A0505-411', 'A', '0.5x0.5', 0.25, 3, 'pledged', 111, NULL, 'Yeshiwork Berihun', 100.00, '2025-08-30 09:29:39', '2025-08-20 21:22:11'),
(1767, 'A0505-412', 'A', '0.5x0.5', 0.25, 3, 'pledged', NULL, NULL, 'Collective Custom Donors', 100.00, '2025-08-30 09:30:14', '2025-08-20 21:22:11'),
(1768, 'A0505-413', 'A', '0.5x0.5', 0.25, 3, 'pledged', 112, NULL, 'Hans Legese', 100.00, '2025-08-30 09:30:36', '2025-08-20 21:22:11'),
(1769, 'A0505-414', 'A', '0.5x0.5', 0.25, 3, 'pledged', 112, NULL, 'Hans Legese', 100.00, '2025-08-30 09:30:36', '2025-08-20 21:22:11'),
(1770, 'A0505-415', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1771, 'A0505-416', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1772, 'A0505-417', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1773, 'A0505-418', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1774, 'A0505-419', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1775, 'A0505-420', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1776, 'A0505-421', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1777, 'A0505-422', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1778, 'A0505-423', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1779, 'A0505-424', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1780, 'A0505-425', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1781, 'A0505-426', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1782, 'A0505-427', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1783, 'A0505-428', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1784, 'A0505-429', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1785, 'A0505-430', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1786, 'A0505-431', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1787, 'A0505-432', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1788, 'A0505-433', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1789, 'A0505-434', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1790, 'A0505-435', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1791, 'A0505-436', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1792, 'A0505-437', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1793, 'A0505-438', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1794, 'A0505-439', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1795, 'A0505-440', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1796, 'A0505-441', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1797, 'A0505-442', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1798, 'A0505-443', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1799, 'A0505-444', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1800, 'A0505-445', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1801, 'A0505-446', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1802, 'A0505-447', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1803, 'A0505-448', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1804, 'A0505-449', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1805, 'A0505-450', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1806, 'A0505-451', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1807, 'A0505-452', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(1808, 'A0505-453', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1809, 'A0505-454', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1810, 'A0505-455', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1811, 'A0505-456', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1812, 'A0505-457', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1813, 'A0505-458', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1814, 'A0505-459', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1815, 'A0505-460', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1816, 'A0505-461', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1817, 'A0505-462', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1818, 'A0505-463', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1819, 'A0505-464', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1820, 'A0505-465', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1821, 'A0505-466', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1822, 'A0505-467', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1823, 'A0505-468', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1824, 'A0505-469', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1825, 'A0505-470', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1826, 'A0505-471', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1827, 'A0505-472', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1828, 'A0505-473', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1829, 'A0505-474', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1830, 'A0505-475', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1831, 'A0505-476', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1832, 'A0505-477', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1833, 'A0505-478', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1834, 'A0505-479', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1835, 'A0505-480', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1836, 'A0505-481', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1837, 'A0505-482', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1838, 'A0505-483', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1839, 'A0505-484', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1840, 'A0505-485', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1841, 'A0505-486', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1842, 'A0505-487', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1843, 'A0505-488', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1844, 'A0505-489', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1845, 'A0505-490', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1846, 'A0505-491', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1847, 'A0505-492', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1848, 'A0505-493', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1849, 'A0505-494', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1850, 'A0505-495', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1851, 'A0505-496', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1852, 'A0505-497', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1853, 'A0505-498', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1854, 'A0505-499', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1855, 'A0505-500', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1856, 'A0505-501', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1857, 'A0505-502', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1858, 'A0505-503', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1859, 'A0505-504', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1860, 'A0505-505', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1861, 'A0505-506', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1862, 'A0505-507', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1863, 'A0505-508', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1864, 'A0505-509', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1865, 'A0505-510', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1866, 'A0505-511', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1867, 'A0505-512', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1868, 'A0505-513', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1869, 'A0505-514', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1870, 'A0505-515', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1871, 'A0505-516', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1872, 'A0505-517', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1873, 'A0505-518', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1874, 'A0505-519', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1875, 'A0505-520', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1876, 'A0505-521', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1877, 'A0505-522', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1878, 'A0505-523', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1879, 'A0505-524', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1880, 'A0505-525', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1881, 'A0505-526', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1882, 'A0505-527', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1883, 'A0505-528', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1884, 'A0505-529', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1885, 'A0505-530', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1886, 'A0505-531', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1887, 'A0505-532', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1888, 'A0505-533', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1889, 'A0505-534', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1890, 'A0505-535', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1891, 'A0505-536', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1892, 'A0505-537', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1893, 'A0505-538', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1894, 'A0505-539', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1895, 'A0505-540', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1896, 'A0505-541', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1897, 'A0505-542', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1898, 'A0505-543', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1899, 'A0505-544', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1900, 'A0505-545', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1901, 'A0505-546', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1902, 'A0505-547', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1903, 'A0505-548', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1904, 'A0505-549', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1905, 'A0505-550', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1906, 'A0505-551', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1907, 'A0505-552', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1908, 'A0505-553', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1909, 'A0505-554', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1910, 'A0505-555', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1911, 'A0505-556', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1912, 'A0505-557', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1913, 'A0505-558', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1914, 'A0505-559', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1915, 'A0505-560', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1916, 'A0505-561', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1917, 'A0505-562', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1918, 'A0505-563', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1919, 'A0505-564', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1920, 'A0505-565', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1921, 'A0505-566', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1922, 'A0505-567', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1923, 'A0505-568', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1924, 'A0505-569', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1925, 'A0505-570', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1926, 'A0505-571', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1927, 'A0505-572', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1928, 'A0505-573', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1929, 'A0505-574', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1930, 'A0505-575', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1931, 'A0505-576', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1932, 'A0505-577', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1933, 'A0505-578', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1934, 'A0505-579', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1935, 'A0505-580', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1936, 'A0505-581', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1937, 'A0505-582', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1938, 'A0505-583', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1939, 'A0505-584', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1940, 'A0505-585', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1941, 'A0505-586', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1942, 'A0505-587', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1943, 'A0505-588', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1944, 'A0505-589', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1945, 'A0505-590', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1946, 'A0505-591', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1947, 'A0505-592', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1948, 'A0505-593', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1949, 'A0505-594', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1950, 'A0505-595', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1951, 'A0505-596', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1952, 'A0505-597', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1953, 'A0505-598', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1954, 'A0505-599', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1955, 'A0505-600', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1956, 'A0505-601', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1957, 'A0505-602', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1958, 'A0505-603', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1959, 'A0505-604', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1960, 'A0505-605', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1961, 'A0505-606', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1962, 'A0505-607', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1963, 'A0505-608', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1964, 'A0505-609', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1965, 'A0505-610', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1966, 'A0505-611', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1967, 'A0505-612', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1968, 'A0505-613', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1969, 'A0505-614', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1970, 'A0505-615', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1971, 'A0505-616', 'A', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1972, 'B0505-01', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1973, 'B0505-02', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1974, 'B0505-03', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1975, 'B0505-04', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1976, 'B0505-05', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1977, 'B0505-06', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1978, 'B0505-07', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1979, 'B0505-08', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1980, 'B0505-09', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1981, 'B0505-10', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1982, 'B0505-11', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1983, 'B0505-12', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1984, 'B0505-13', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1985, 'B0505-14', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1986, 'B0505-15', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1987, 'B0505-16', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1988, 'B0505-17', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1989, 'B0505-18', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1990, 'B0505-19', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1991, 'B0505-20', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1992, 'B0505-21', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1993, 'B0505-22', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1994, 'B0505-23', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1995, 'B0505-24', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1996, 'B0505-25', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1997, 'B0505-26', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1998, 'B0505-27', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(1999, 'B0505-28', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2000, 'B0505-29', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2001, 'B0505-30', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2002, 'B0505-31', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2003, 'B0505-32', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2004, 'B0505-33', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2005, 'B0505-34', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2006, 'B0505-35', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2007, 'B0505-36', 'B', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2008, 'C0505-01', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2009, 'C0505-02', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2010, 'C0505-03', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2011, 'C0505-04', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2012, 'C0505-05', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2013, 'C0505-06', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2014, 'C0505-07', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2015, 'C0505-08', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2016, 'C0505-09', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2017, 'C0505-10', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2018, 'C0505-11', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2019, 'C0505-12', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2020, 'C0505-13', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2021, 'C0505-14', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2022, 'C0505-15', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2023, 'C0505-16', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2024, 'C0505-17', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2025, 'C0505-18', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2026, 'C0505-19', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2027, 'C0505-20', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2028, 'C0505-21', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2029, 'C0505-22', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2030, 'C0505-23', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2031, 'C0505-24', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2032, 'C0505-25', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2033, 'C0505-26', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2034, 'C0505-27', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2035, 'C0505-28', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2036, 'C0505-29', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2037, 'C0505-30', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2038, 'C0505-31', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2039, 'C0505-32', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2040, 'C0505-33', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2041, 'C0505-34', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2042, 'C0505-35', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2043, 'C0505-36', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2044, 'C0505-37', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2045, 'C0505-38', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2046, 'C0505-39', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2047, 'C0505-40', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2048, 'C0505-41', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2049, 'C0505-42', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2050, 'C0505-43', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2051, 'C0505-44', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2052, 'C0505-45', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2053, 'C0505-46', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2054, 'C0505-47', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2055, 'C0505-48', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2056, 'C0505-49', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2057, 'C0505-50', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2058, 'C0505-51', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2059, 'C0505-52', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2060, 'C0505-53', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2061, 'C0505-54', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2062, 'C0505-55', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2063, 'C0505-56', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2064, 'C0505-57', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2065, 'C0505-58', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2066, 'C0505-59', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2067, 'C0505-60', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2068, 'C0505-61', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2069, 'C0505-62', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2070, 'C0505-63', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2071, 'C0505-64', 'C', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2072, 'D0505-01', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2073, 'D0505-02', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2074, 'D0505-03', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2075, 'D0505-04', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2076, 'D0505-05', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2077, 'D0505-06', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2078, 'D0505-07', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2079, 'D0505-08', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2080, 'D0505-09', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2081, 'D0505-10', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2082, 'D0505-11', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2083, 'D0505-12', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2084, 'D0505-13', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2085, 'D0505-14', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2086, 'D0505-15', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2087, 'D0505-16', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2088, 'D0505-17', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2089, 'D0505-18', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2090, 'D0505-19', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2091, 'D0505-20', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2092, 'D0505-21', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2093, 'D0505-22', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2094, 'D0505-23', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2095, 'D0505-24', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2096, 'D0505-25', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2097, 'D0505-26', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2098, 'D0505-27', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2099, 'D0505-28', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2100, 'D0505-29', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2101, 'D0505-30', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2102, 'D0505-31', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2103, 'D0505-32', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2104, 'D0505-33', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2105, 'D0505-34', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2106, 'D0505-35', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2107, 'D0505-36', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2108, 'D0505-37', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2109, 'D0505-38', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2110, 'D0505-39', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2111, 'D0505-40', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2112, 'D0505-41', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2113, 'D0505-42', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2114, 'D0505-43', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2115, 'D0505-44', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2116, 'D0505-45', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2117, 'D0505-46', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2118, 'D0505-47', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2119, 'D0505-48', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2120, 'D0505-49', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2121, 'D0505-50', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2122, 'D0505-51', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2123, 'D0505-52', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2124, 'D0505-53', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2125, 'D0505-54', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2126, 'D0505-55', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2127, 'D0505-56', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2128, 'D0505-57', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2129, 'D0505-58', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2130, 'D0505-59', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2131, 'D0505-60', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2132, 'D0505-61', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2133, 'D0505-62', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2134, 'D0505-63', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2135, 'D0505-64', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2136, 'D0505-65', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2137, 'D0505-66', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2138, 'D0505-67', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2139, 'D0505-68', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2140, 'D0505-69', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2141, 'D0505-70', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2142, 'D0505-71', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2143, 'D0505-72', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2144, 'D0505-73', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2145, 'D0505-74', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2146, 'D0505-75', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2147, 'D0505-76', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2148, 'D0505-77', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2149, 'D0505-78', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2150, 'D0505-79', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2151, 'D0505-80', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2152, 'D0505-81', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2153, 'D0505-82', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2154, 'D0505-83', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2155, 'D0505-84', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2156, 'D0505-85', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2157, 'D0505-86', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2158, 'D0505-87', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2159, 'D0505-88', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2160, 'D0505-89', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2161, 'D0505-90', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2162, 'D0505-91', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2163, 'D0505-92', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2164, 'D0505-93', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2165, 'D0505-94', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2166, 'D0505-95', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2167, 'D0505-96', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2168, 'D0505-97', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2169, 'D0505-98', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2170, 'D0505-99', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2171, 'D0505-100', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2172, 'D0505-101', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2173, 'D0505-102', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2174, 'D0505-103', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2175, 'D0505-104', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2176, 'D0505-105', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2177, 'D0505-106', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2178, 'D0505-107', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2179, 'D0505-108', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2180, 'D0505-109', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2181, 'D0505-110', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2182, 'D0505-111', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2183, 'D0505-112', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2184, 'D0505-113', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2185, 'D0505-114', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2186, 'D0505-115', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2187, 'D0505-116', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2188, 'D0505-117', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2189, 'D0505-118', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2190, 'D0505-119', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2191, 'D0505-120', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2192, 'D0505-121', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2193, 'D0505-122', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2194, 'D0505-123', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2195, 'D0505-124', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2196, 'D0505-125', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2197, 'D0505-126', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2198, 'D0505-127', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2199, 'D0505-128', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2200, 'D0505-129', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2201, 'D0505-130', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2202, 'D0505-131', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2203, 'D0505-132', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2204, 'D0505-133', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2205, 'D0505-134', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2206, 'D0505-135', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2207, 'D0505-136', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2208, 'D0505-137', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2209, 'D0505-138', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2210, 'D0505-139', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2211, 'D0505-140', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2212, 'D0505-141', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2213, 'D0505-142', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2214, 'D0505-143', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2215, 'D0505-144', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2216, 'D0505-145', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2217, 'D0505-146', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2218, 'D0505-147', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2219, 'D0505-148', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2220, 'D0505-149', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2221, 'D0505-150', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2222, 'D0505-151', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2223, 'D0505-152', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2224, 'D0505-153', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2225, 'D0505-154', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2226, 'D0505-155', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2227, 'D0505-156', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2228, 'D0505-157', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2229, 'D0505-158', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2230, 'D0505-159', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2231, 'D0505-160', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2232, 'D0505-161', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2233, 'D0505-162', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2234, 'D0505-163', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2235, 'D0505-164', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2236, 'D0505-165', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2237, 'D0505-166', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2238, 'D0505-167', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2239, 'D0505-168', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2240, 'D0505-169', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2241, 'D0505-170', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2242, 'D0505-171', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2243, 'D0505-172', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2244, 'D0505-173', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2245, 'D0505-174', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2246, 'D0505-175', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2247, 'D0505-176', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2248, 'D0505-177', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2249, 'D0505-178', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2250, 'D0505-179', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2251, 'D0505-180', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2252, 'D0505-181', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2253, 'D0505-182', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2254, 'D0505-183', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2255, 'D0505-184', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2256, 'D0505-185', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2257, 'D0505-186', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2258, 'D0505-187', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2259, 'D0505-188', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2260, 'D0505-189', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2261, 'D0505-190', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(2262, 'D0505-191', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2263, 'D0505-192', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2264, 'D0505-193', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2265, 'D0505-194', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2266, 'D0505-195', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2267, 'D0505-196', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2268, 'D0505-197', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2269, 'D0505-198', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2270, 'D0505-199', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2271, 'D0505-200', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2272, 'D0505-201', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2273, 'D0505-202', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2274, 'D0505-203', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2275, 'D0505-204', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2276, 'D0505-205', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2277, 'D0505-206', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2278, 'D0505-207', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2279, 'D0505-208', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2280, 'D0505-209', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2281, 'D0505-210', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2282, 'D0505-211', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2283, 'D0505-212', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2284, 'D0505-213', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2285, 'D0505-214', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2286, 'D0505-215', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2287, 'D0505-216', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2288, 'D0505-217', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2289, 'D0505-218', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2290, 'D0505-219', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2291, 'D0505-220', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2292, 'D0505-221', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2293, 'D0505-222', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2294, 'D0505-223', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2295, 'D0505-224', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2296, 'D0505-225', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2297, 'D0505-226', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2298, 'D0505-227', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2299, 'D0505-228', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2300, 'D0505-229', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2301, 'D0505-230', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:11'),
(2302, 'D0505-231', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2303, 'D0505-232', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2304, 'D0505-233', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2305, 'D0505-234', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2306, 'D0505-235', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2307, 'D0505-236', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2308, 'D0505-237', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2309, 'D0505-238', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2310, 'D0505-239', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2311, 'D0505-240', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2312, 'D0505-241', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2313, 'D0505-242', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2314, 'D0505-243', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2315, 'D0505-244', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2316, 'D0505-245', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2317, 'D0505-246', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2318, 'D0505-247', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2319, 'D0505-248', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2320, 'D0505-249', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2321, 'D0505-250', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2322, 'D0505-251', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2323, 'D0505-252', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2324, 'D0505-253', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2325, 'D0505-254', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2326, 'D0505-255', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2327, 'D0505-256', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2328, 'D0505-257', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2329, 'D0505-258', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2330, 'D0505-259', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2331, 'D0505-260', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2332, 'D0505-261', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2333, 'D0505-262', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2334, 'D0505-263', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2335, 'D0505-264', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2336, 'D0505-265', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2337, 'D0505-266', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2338, 'D0505-267', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2339, 'D0505-268', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2340, 'D0505-269', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2341, 'D0505-270', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2342, 'D0505-271', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2343, 'D0505-272', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2344, 'D0505-273', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2345, 'D0505-274', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2346, 'D0505-275', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2347, 'D0505-276', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2348, 'D0505-277', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2349, 'D0505-278', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2350, 'D0505-279', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2351, 'D0505-280', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2352, 'D0505-281', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2353, 'D0505-282', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2354, 'D0505-283', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2355, 'D0505-284', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2356, 'D0505-285', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2357, 'D0505-286', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2358, 'D0505-287', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2359, 'D0505-288', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2360, 'D0505-289', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2361, 'D0505-290', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2362, 'D0505-291', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2363, 'D0505-292', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2364, 'D0505-293', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2365, 'D0505-294', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2366, 'D0505-295', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2367, 'D0505-296', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2368, 'D0505-297', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2369, 'D0505-298', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2370, 'D0505-299', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2371, 'D0505-300', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2372, 'D0505-301', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2373, 'D0505-302', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2374, 'D0505-303', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2375, 'D0505-304', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2376, 'D0505-305', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2377, 'D0505-306', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2378, 'D0505-307', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2379, 'D0505-308', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2380, 'D0505-309', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2381, 'D0505-310', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2382, 'D0505-311', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2383, 'D0505-312', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2384, 'D0505-313', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2385, 'D0505-314', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2386, 'D0505-315', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2387, 'D0505-316', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2388, 'D0505-317', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2389, 'D0505-318', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2390, 'D0505-319', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2391, 'D0505-320', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2392, 'D0505-321', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2393, 'D0505-322', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2394, 'D0505-323', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2395, 'D0505-324', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2396, 'D0505-325', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2397, 'D0505-326', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2398, 'D0505-327', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2399, 'D0505-328', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2400, 'D0505-329', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2401, 'D0505-330', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2402, 'D0505-331', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2403, 'D0505-332', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2404, 'D0505-333', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2405, 'D0505-334', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2406, 'D0505-335', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2407, 'D0505-336', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2408, 'D0505-337', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2409, 'D0505-338', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2410, 'D0505-339', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2411, 'D0505-340', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2412, 'D0505-341', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2413, 'D0505-342', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2414, 'D0505-343', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2415, 'D0505-344', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2416, 'D0505-345', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2417, 'D0505-346', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2418, 'D0505-347', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2419, 'D0505-348', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2420, 'D0505-349', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2421, 'D0505-350', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2422, 'D0505-351', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2423, 'D0505-352', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2424, 'D0505-353', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2425, 'D0505-354', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2426, 'D0505-355', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2427, 'D0505-356', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2428, 'D0505-357', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2429, 'D0505-358', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2430, 'D0505-359', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2431, 'D0505-360', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2432, 'D0505-361', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2433, 'D0505-362', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2434, 'D0505-363', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2435, 'D0505-364', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2436, 'D0505-365', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2437, 'D0505-366', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2438, 'D0505-367', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2439, 'D0505-368', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2440, 'D0505-369', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2441, 'D0505-370', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2442, 'D0505-371', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2443, 'D0505-372', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2444, 'D0505-373', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2445, 'D0505-374', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2446, 'D0505-375', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2447, 'D0505-376', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2448, 'D0505-377', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2449, 'D0505-378', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2450, 'D0505-379', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2451, 'D0505-380', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2452, 'D0505-381', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2453, 'D0505-382', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2454, 'D0505-383', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2455, 'D0505-384', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2456, 'D0505-385', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2457, 'D0505-386', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2458, 'D0505-387', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2459, 'D0505-388', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2460, 'D0505-389', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2461, 'D0505-390', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2462, 'D0505-391', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2463, 'D0505-392', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2464, 'D0505-393', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2465, 'D0505-394', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2466, 'D0505-395', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2467, 'D0505-396', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2468, 'D0505-397', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2469, 'D0505-398', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2470, 'D0505-399', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2471, 'D0505-400', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2472, 'D0505-401', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2473, 'D0505-402', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2474, 'D0505-403', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2475, 'D0505-404', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2476, 'D0505-405', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2477, 'D0505-406', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2478, 'D0505-407', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2479, 'D0505-408', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2480, 'D0505-409', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2481, 'D0505-410', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2482, 'D0505-411', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2483, 'D0505-412', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2484, 'D0505-413', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2485, 'D0505-414', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2486, 'D0505-415', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2487, 'D0505-416', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2488, 'D0505-417', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2489, 'D0505-418', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2490, 'D0505-419', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2491, 'D0505-420', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2492, 'D0505-421', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2493, 'D0505-422', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2494, 'D0505-423', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2495, 'D0505-424', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2496, 'D0505-425', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2497, 'D0505-426', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2498, 'D0505-427', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2499, 'D0505-428', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2500, 'D0505-429', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2501, 'D0505-430', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2502, 'D0505-431', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2503, 'D0505-432', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2504, 'D0505-433', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2505, 'D0505-434', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2506, 'D0505-435', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2507, 'D0505-436', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2508, 'D0505-437', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2509, 'D0505-438', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2510, 'D0505-439', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2511, 'D0505-440', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2512, 'D0505-441', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2513, 'D0505-442', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2514, 'D0505-443', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2515, 'D0505-444', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2516, 'D0505-445', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2517, 'D0505-446', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2518, 'D0505-447', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2519, 'D0505-448', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2520, 'D0505-449', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2521, 'D0505-450', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2522, 'D0505-451', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2523, 'D0505-452', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2524, 'D0505-453', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2525, 'D0505-454', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2526, 'D0505-455', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2527, 'D0505-456', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2528, 'D0505-457', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2529, 'D0505-458', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2530, 'D0505-459', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2531, 'D0505-460', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2532, 'D0505-461', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2533, 'D0505-462', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2534, 'D0505-463', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2535, 'D0505-464', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2536, 'D0505-465', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2537, 'D0505-466', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2538, 'D0505-467', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2539, 'D0505-468', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2540, 'D0505-469', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2541, 'D0505-470', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2542, 'D0505-471', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2543, 'D0505-472', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2544, 'D0505-473', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2545, 'D0505-474', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2546, 'D0505-475', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2547, 'D0505-476', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2548, 'D0505-477', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2549, 'D0505-478', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2550, 'D0505-479', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2551, 'D0505-480', 'D', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2552, 'E0505-01', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2553, 'E0505-02', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2554, 'E0505-03', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2555, 'E0505-04', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2556, 'E0505-05', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2557, 'E0505-06', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2558, 'E0505-07', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2559, 'E0505-08', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2560, 'E0505-09', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2561, 'E0505-10', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2562, 'E0505-11', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2563, 'E0505-12', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2564, 'E0505-13', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2565, 'E0505-14', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2566, 'E0505-15', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2567, 'E0505-16', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2568, 'E0505-17', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2569, 'E0505-18', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2570, 'E0505-19', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2571, 'E0505-20', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2572, 'E0505-21', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2573, 'E0505-22', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2574, 'E0505-23', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2575, 'E0505-24', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2576, 'E0505-25', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2577, 'E0505-26', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2578, 'E0505-27', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2579, 'E0505-28', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2580, 'E0505-29', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2581, 'E0505-30', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2582, 'E0505-31', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2583, 'E0505-32', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2584, 'E0505-33', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2585, 'E0505-34', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2586, 'E0505-35', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2587, 'E0505-36', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2588, 'E0505-37', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2589, 'E0505-38', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2590, 'E0505-39', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2591, 'E0505-40', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2592, 'E0505-41', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2593, 'E0505-42', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2594, 'E0505-43', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2595, 'E0505-44', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2596, 'E0505-45', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2597, 'E0505-46', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2598, 'E0505-47', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2599, 'E0505-48', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2600, 'E0505-49', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2601, 'E0505-50', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2602, 'E0505-51', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2603, 'E0505-52', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2604, 'E0505-53', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2605, 'E0505-54', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2606, 'E0505-55', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2607, 'E0505-56', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2608, 'E0505-57', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2609, 'E0505-58', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2610, 'E0505-59', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2611, 'E0505-60', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2612, 'E0505-61', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2613, 'E0505-62', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2614, 'E0505-63', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2615, 'E0505-64', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2616, 'E0505-65', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2617, 'E0505-66', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2618, 'E0505-67', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2619, 'E0505-68', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2620, 'E0505-69', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2621, 'E0505-70', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2622, 'E0505-71', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2623, 'E0505-72', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2624, 'E0505-73', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2625, 'E0505-74', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2626, 'E0505-75', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2627, 'E0505-76', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2628, 'E0505-77', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2629, 'E0505-78', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2630, 'E0505-79', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2631, 'E0505-80', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2632, 'E0505-81', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2633, 'E0505-82', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2634, 'E0505-83', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2635, 'E0505-84', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2636, 'E0505-85', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2637, 'E0505-86', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2638, 'E0505-87', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2639, 'E0505-88', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2640, 'E0505-89', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2641, 'E0505-90', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2642, 'E0505-91', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2643, 'E0505-92', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2644, 'E0505-93', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2645, 'E0505-94', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2646, 'E0505-95', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2647, 'E0505-96', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2648, 'E0505-97', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2649, 'E0505-98', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2650, 'E0505-99', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2651, 'E0505-100', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2652, 'E0505-101', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2653, 'E0505-102', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2654, 'E0505-103', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2655, 'E0505-104', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2656, 'E0505-105', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2657, 'E0505-106', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2658, 'E0505-107', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2659, 'E0505-108', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2660, 'E0505-109', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2661, 'E0505-110', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2662, 'E0505-111', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2663, 'E0505-112', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2664, 'E0505-113', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2665, 'E0505-114', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2666, 'E0505-115', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2667, 'E0505-116', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2668, 'E0505-117', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2669, 'E0505-118', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2670, 'E0505-119', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2671, 'E0505-120', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2672, 'E0505-121', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2673, 'E0505-122', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2674, 'E0505-123', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2675, 'E0505-124', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2676, 'E0505-125', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2677, 'E0505-126', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2678, 'E0505-127', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2679, 'E0505-128', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2680, 'E0505-129', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2681, 'E0505-130', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2682, 'E0505-131', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2683, 'E0505-132', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2684, 'E0505-133', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2685, 'E0505-134', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2686, 'E0505-135', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2687, 'E0505-136', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2688, 'E0505-137', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2689, 'E0505-138', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2690, 'E0505-139', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2691, 'E0505-140', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2692, 'E0505-141', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2693, 'E0505-142', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2694, 'E0505-143', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2695, 'E0505-144', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2696, 'E0505-145', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2697, 'E0505-146', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2698, 'E0505-147', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2699, 'E0505-148', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2700, 'E0505-149', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2701, 'E0505-150', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2702, 'E0505-151', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2703, 'E0505-152', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2704, 'E0505-153', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2705, 'E0505-154', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2706, 'E0505-155', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2707, 'E0505-156', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2708, 'E0505-157', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2709, 'E0505-158', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2710, 'E0505-159', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2711, 'E0505-160', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2712, 'E0505-161', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2713, 'E0505-162', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2714, 'E0505-163', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(2715, 'E0505-164', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2716, 'E0505-165', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2717, 'E0505-166', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2718, 'E0505-167', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2719, 'E0505-168', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2720, 'E0505-169', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2721, 'E0505-170', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2722, 'E0505-171', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2723, 'E0505-172', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2724, 'E0505-173', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2725, 'E0505-174', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2726, 'E0505-175', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2727, 'E0505-176', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2728, 'E0505-177', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2729, 'E0505-178', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2730, 'E0505-179', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2731, 'E0505-180', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2732, 'E0505-181', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2733, 'E0505-182', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2734, 'E0505-183', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2735, 'E0505-184', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2736, 'E0505-185', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2737, 'E0505-186', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2738, 'E0505-187', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2739, 'E0505-188', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2740, 'E0505-189', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2741, 'E0505-190', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2742, 'E0505-191', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2743, 'E0505-192', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2744, 'E0505-193', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2745, 'E0505-194', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2746, 'E0505-195', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2747, 'E0505-196', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2748, 'E0505-197', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2749, 'E0505-198', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2750, 'E0505-199', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2751, 'E0505-200', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2752, 'E0505-201', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2753, 'E0505-202', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2754, 'E0505-203', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2755, 'E0505-204', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2756, 'E0505-205', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2757, 'E0505-206', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2758, 'E0505-207', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2759, 'E0505-208', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2760, 'E0505-209', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2761, 'E0505-210', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2762, 'E0505-211', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2763, 'E0505-212', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2764, 'E0505-213', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2765, 'E0505-214', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2766, 'E0505-215', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2767, 'E0505-216', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2768, 'E0505-217', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2769, 'E0505-218', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2770, 'E0505-219', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2771, 'E0505-220', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2772, 'E0505-221', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2773, 'E0505-222', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2774, 'E0505-223', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2775, 'E0505-224', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2776, 'E0505-225', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2777, 'E0505-226', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2778, 'E0505-227', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2779, 'E0505-228', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2780, 'E0505-229', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2781, 'E0505-230', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2782, 'E0505-231', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2783, 'E0505-232', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2784, 'E0505-233', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2785, 'E0505-234', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2786, 'E0505-235', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2787, 'E0505-236', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2788, 'E0505-237', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2789, 'E0505-238', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2790, 'E0505-239', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2791, 'E0505-240', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2792, 'E0505-241', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2793, 'E0505-242', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2794, 'E0505-243', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2795, 'E0505-244', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2796, 'E0505-245', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2797, 'E0505-246', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2798, 'E0505-247', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2799, 'E0505-248', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2800, 'E0505-249', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2801, 'E0505-250', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2802, 'E0505-251', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2803, 'E0505-252', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2804, 'E0505-253', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2805, 'E0505-254', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2806, 'E0505-255', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2807, 'E0505-256', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2808, 'E0505-257', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2809, 'E0505-258', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2810, 'E0505-259', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2811, 'E0505-260', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2812, 'E0505-261', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2813, 'E0505-262', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2814, 'E0505-263', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2815, 'E0505-264', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2816, 'E0505-265', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2817, 'E0505-266', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2818, 'E0505-267', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2819, 'E0505-268', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2820, 'E0505-269', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2821, 'E0505-270', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2822, 'E0505-271', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2823, 'E0505-272', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2824, 'E0505-273', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2825, 'E0505-274', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2826, 'E0505-275', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2827, 'E0505-276', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2828, 'E0505-277', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2829, 'E0505-278', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2830, 'E0505-279', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2831, 'E0505-280', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2832, 'E0505-281', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2833, 'E0505-282', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2834, 'E0505-283', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2835, 'E0505-284', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2836, 'E0505-285', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2837, 'E0505-286', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2838, 'E0505-287', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2839, 'E0505-288', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2840, 'E0505-289', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2841, 'E0505-290', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2842, 'E0505-291', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2843, 'E0505-292', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2844, 'E0505-293', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2845, 'E0505-294', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2846, 'E0505-295', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2847, 'E0505-296', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2848, 'E0505-297', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2849, 'E0505-298', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2850, 'E0505-299', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2851, 'E0505-300', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2852, 'E0505-301', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2853, 'E0505-302', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2854, 'E0505-303', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2855, 'E0505-304', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2856, 'E0505-305', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2857, 'E0505-306', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2858, 'E0505-307', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2859, 'E0505-308', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2860, 'E0505-309', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2861, 'E0505-310', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2862, 'E0505-311', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2863, 'E0505-312', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2864, 'E0505-313', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2865, 'E0505-314', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2866, 'E0505-315', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2867, 'E0505-316', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2868, 'E0505-317', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2869, 'E0505-318', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2870, 'E0505-319', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2871, 'E0505-320', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2872, 'E0505-321', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2873, 'E0505-322', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2874, 'E0505-323', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2875, 'E0505-324', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2876, 'E0505-325', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2877, 'E0505-326', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2878, 'E0505-327', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2879, 'E0505-328', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2880, 'E0505-329', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2881, 'E0505-330', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2882, 'E0505-331', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2883, 'E0505-332', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2884, 'E0505-333', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2885, 'E0505-334', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2886, 'E0505-335', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2887, 'E0505-336', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2888, 'E0505-337', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2889, 'E0505-338', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2890, 'E0505-339', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2891, 'E0505-340', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2892, 'E0505-341', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2893, 'E0505-342', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2894, 'E0505-343', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2895, 'E0505-344', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2896, 'E0505-345', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2897, 'E0505-346', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2898, 'E0505-347', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2899, 'E0505-348', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2900, 'E0505-349', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2901, 'E0505-350', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2902, 'E0505-351', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2903, 'E0505-352', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2904, 'E0505-353', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2905, 'E0505-354', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2906, 'E0505-355', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2907, 'E0505-356', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2908, 'E0505-357', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2909, 'E0505-358', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2910, 'E0505-359', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2911, 'E0505-360', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2912, 'E0505-361', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2913, 'E0505-362', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2914, 'E0505-363', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2915, 'E0505-364', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2916, 'E0505-365', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2917, 'E0505-366', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2918, 'E0505-367', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2919, 'E0505-368', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2920, 'E0505-369', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2921, 'E0505-370', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2922, 'E0505-371', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2923, 'E0505-372', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2924, 'E0505-373', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2925, 'E0505-374', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2926, 'E0505-375', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2927, 'E0505-376', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2928, 'E0505-377', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2929, 'E0505-378', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2930, 'E0505-379', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2931, 'E0505-380', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2932, 'E0505-381', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2933, 'E0505-382', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2934, 'E0505-383', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2935, 'E0505-384', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2936, 'E0505-385', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2937, 'E0505-386', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2938, 'E0505-387', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2939, 'E0505-388', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2940, 'E0505-389', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2941, 'E0505-390', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2942, 'E0505-391', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2943, 'E0505-392', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2944, 'E0505-393', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2945, 'E0505-394', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2946, 'E0505-395', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2947, 'E0505-396', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2948, 'E0505-397', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2949, 'E0505-398', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2950, 'E0505-399', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2951, 'E0505-400', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2952, 'E0505-401', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2953, 'E0505-402', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2954, 'E0505-403', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2955, 'E0505-404', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2956, 'E0505-405', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2957, 'E0505-406', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2958, 'E0505-407', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2959, 'E0505-408', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2960, 'E0505-409', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2961, 'E0505-410', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2962, 'E0505-411', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2963, 'E0505-412', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2964, 'E0505-413', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2965, 'E0505-414', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2966, 'E0505-415', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2967, 'E0505-416', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2968, 'E0505-417', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2969, 'E0505-418', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2970, 'E0505-419', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2971, 'E0505-420', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2972, 'E0505-421', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2973, 'E0505-422', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2974, 'E0505-423', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2975, 'E0505-424', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2976, 'E0505-425', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2977, 'E0505-426', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2978, 'E0505-427', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2979, 'E0505-428', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2980, 'E0505-429', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2981, 'E0505-430', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2982, 'E0505-431', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2983, 'E0505-432', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2984, 'E0505-433', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2985, 'E0505-434', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2986, 'E0505-435', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2987, 'E0505-436', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2988, 'E0505-437', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2989, 'E0505-438', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2990, 'E0505-439', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2991, 'E0505-440', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2992, 'E0505-441', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2993, 'E0505-442', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2994, 'E0505-443', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2995, 'E0505-444', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2996, 'E0505-445', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2997, 'E0505-446', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2998, 'E0505-447', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(2999, 'E0505-448', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3000, 'E0505-449', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3001, 'E0505-450', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3002, 'E0505-451', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3003, 'E0505-452', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3004, 'E0505-453', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3005, 'E0505-454', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3006, 'E0505-455', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3007, 'E0505-456', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3008, 'E0505-457', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3009, 'E0505-458', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3010, 'E0505-459', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3011, 'E0505-460', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3012, 'E0505-461', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3013, 'E0505-462', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3014, 'E0505-463', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3015, 'E0505-464', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3016, 'E0505-465', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3017, 'E0505-466', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3018, 'E0505-467', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3019, 'E0505-468', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3020, 'E0505-469', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3021, 'E0505-470', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3022, 'E0505-471', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3023, 'E0505-472', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3024, 'E0505-473', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3025, 'E0505-474', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3026, 'E0505-475', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3027, 'E0505-476', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3028, 'E0505-477', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3029, 'E0505-478', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3030, 'E0505-479', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3031, 'E0505-480', 'E', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3032, 'F0505-01', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3033, 'F0505-02', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3034, 'F0505-03', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3035, 'F0505-04', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3036, 'F0505-05', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3037, 'F0505-06', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3038, 'F0505-07', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3039, 'F0505-08', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3040, 'F0505-09', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3041, 'F0505-10', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3042, 'F0505-11', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3043, 'F0505-12', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3044, 'F0505-13', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3045, 'F0505-14', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3046, 'F0505-15', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3047, 'F0505-16', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3048, 'F0505-17', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3049, 'F0505-18', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3050, 'F0505-19', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3051, 'F0505-20', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3052, 'F0505-21', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3053, 'F0505-22', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3054, 'F0505-23', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3055, 'F0505-24', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3056, 'F0505-25', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3057, 'F0505-26', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3058, 'F0505-27', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3059, 'F0505-28', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3060, 'F0505-29', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3061, 'F0505-30', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3062, 'F0505-31', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3063, 'F0505-32', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3064, 'F0505-33', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3065, 'F0505-34', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3066, 'F0505-35', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3067, 'F0505-36', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3068, 'F0505-37', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3069, 'F0505-38', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3070, 'F0505-39', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3071, 'F0505-40', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3072, 'F0505-41', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3073, 'F0505-42', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3074, 'F0505-43', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3075, 'F0505-44', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3076, 'F0505-45', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3077, 'F0505-46', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3078, 'F0505-47', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3079, 'F0505-48', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3080, 'F0505-49', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3081, 'F0505-50', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3082, 'F0505-51', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3083, 'F0505-52', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3084, 'F0505-53', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3085, 'F0505-54', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3086, 'F0505-55', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3087, 'F0505-56', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3088, 'F0505-57', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3089, 'F0505-58', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3090, 'F0505-59', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3091, 'F0505-60', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3092, 'F0505-61', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3093, 'F0505-62', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3094, 'F0505-63', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3095, 'F0505-64', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3096, 'F0505-65', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3097, 'F0505-66', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3098, 'F0505-67', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3099, 'F0505-68', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3100, 'F0505-69', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3101, 'F0505-70', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3102, 'F0505-71', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3103, 'F0505-72', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3104, 'F0505-73', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3105, 'F0505-74', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3106, 'F0505-75', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3107, 'F0505-76', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3108, 'F0505-77', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3109, 'F0505-78', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3110, 'F0505-79', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3111, 'F0505-80', 'F', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3112, 'G0505-01', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3113, 'G0505-02', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3114, 'G0505-03', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3115, 'G0505-04', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3116, 'G0505-05', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3117, 'G0505-06', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3118, 'G0505-07', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3119, 'G0505-08', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3120, 'G0505-09', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3121, 'G0505-10', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3122, 'G0505-11', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3123, 'G0505-12', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3124, 'G0505-13', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3125, 'G0505-14', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3126, 'G0505-15', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3127, 'G0505-16', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3128, 'G0505-17', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3129, 'G0505-18', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3130, 'G0505-19', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3131, 'G0505-20', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3132, 'G0505-21', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3133, 'G0505-22', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3134, 'G0505-23', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3135, 'G0505-24', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3136, 'G0505-25', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3137, 'G0505-26', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3138, 'G0505-27', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3139, 'G0505-28', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3140, 'G0505-29', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3141, 'G0505-30', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3142, 'G0505-31', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3143, 'G0505-32', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3144, 'G0505-33', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3145, 'G0505-34', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3146, 'G0505-35', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3147, 'G0505-36', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3148, 'G0505-37', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3149, 'G0505-38', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3150, 'G0505-39', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3151, 'G0505-40', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3152, 'G0505-41', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3153, 'G0505-42', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3154, 'G0505-43', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3155, 'G0505-44', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3156, 'G0505-45', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3157, 'G0505-46', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3158, 'G0505-47', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3159, 'G0505-48', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3160, 'G0505-49', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3161, 'G0505-50', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3162, 'G0505-51', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3163, 'G0505-52', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3164, 'G0505-53', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3165, 'G0505-54', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3166, 'G0505-55', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3167, 'G0505-56', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12');
INSERT INTO `floor_grid_cells` (`id`, `cell_id`, `rectangle_id`, `cell_type`, `area_size`, `package_id`, `status`, `pledge_id`, `payment_id`, `donor_name`, `amount`, `assigned_date`, `created_at`) VALUES
(3168, 'G0505-57', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3169, 'G0505-58', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3170, 'G0505-59', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3171, 'G0505-60', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3172, 'G0505-61', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3173, 'G0505-62', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3174, 'G0505-63', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3175, 'G0505-64', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3176, 'G0505-65', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3177, 'G0505-66', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3178, 'G0505-67', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3179, 'G0505-68', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3180, 'G0505-69', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3181, 'G0505-70', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3182, 'G0505-71', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3183, 'G0505-72', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3184, 'G0505-73', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3185, 'G0505-74', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3186, 'G0505-75', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3187, 'G0505-76', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3188, 'G0505-77', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3189, 'G0505-78', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3190, 'G0505-79', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3191, 'G0505-80', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3192, 'G0505-81', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3193, 'G0505-82', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3194, 'G0505-83', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3195, 'G0505-84', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3196, 'G0505-85', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3197, 'G0505-86', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3198, 'G0505-87', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3199, 'G0505-88', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3200, 'G0505-89', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3201, 'G0505-90', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3202, 'G0505-91', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3203, 'G0505-92', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3204, 'G0505-93', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3205, 'G0505-94', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3206, 'G0505-95', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3207, 'G0505-96', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3208, 'G0505-97', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3209, 'G0505-98', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3210, 'G0505-99', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3211, 'G0505-100', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3212, 'G0505-101', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3213, 'G0505-102', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3214, 'G0505-103', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3215, 'G0505-104', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3216, 'G0505-105', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3217, 'G0505-106', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3218, 'G0505-107', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3219, 'G0505-108', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3220, 'G0505-109', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3221, 'G0505-110', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3222, 'G0505-111', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3223, 'G0505-112', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3224, 'G0505-113', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3225, 'G0505-114', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3226, 'G0505-115', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3227, 'G0505-116', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3228, 'G0505-117', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3229, 'G0505-118', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3230, 'G0505-119', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3231, 'G0505-120', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3232, 'G0505-121', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3233, 'G0505-122', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3234, 'G0505-123', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3235, 'G0505-124', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3236, 'G0505-125', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3237, 'G0505-126', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3238, 'G0505-127', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3239, 'G0505-128', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3240, 'G0505-129', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3241, 'G0505-130', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3242, 'G0505-131', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3243, 'G0505-132', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3244, 'G0505-133', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3245, 'G0505-134', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3246, 'G0505-135', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3247, 'G0505-136', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3248, 'G0505-137', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3249, 'G0505-138', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3250, 'G0505-139', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3251, 'G0505-140', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3252, 'G0505-141', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3253, 'G0505-142', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3254, 'G0505-143', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3255, 'G0505-144', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3256, 'G0505-145', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3257, 'G0505-146', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3258, 'G0505-147', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3259, 'G0505-148', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3260, 'G0505-149', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3261, 'G0505-150', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3262, 'G0505-151', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3263, 'G0505-152', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3264, 'G0505-153', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3265, 'G0505-154', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3266, 'G0505-155', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3267, 'G0505-156', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3268, 'G0505-157', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3269, 'G0505-158', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3270, 'G0505-159', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3271, 'G0505-160', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3272, 'G0505-161', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3273, 'G0505-162', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3274, 'G0505-163', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3275, 'G0505-164', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3276, 'G0505-165', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3277, 'G0505-166', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3278, 'G0505-167', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3279, 'G0505-168', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3280, 'G0505-169', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3281, 'G0505-170', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3282, 'G0505-171', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3283, 'G0505-172', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3284, 'G0505-173', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3285, 'G0505-174', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3286, 'G0505-175', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3287, 'G0505-176', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3288, 'G0505-177', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3289, 'G0505-178', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3290, 'G0505-179', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3291, 'G0505-180', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3292, 'G0505-181', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3293, 'G0505-182', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3294, 'G0505-183', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3295, 'G0505-184', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3296, 'G0505-185', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3297, 'G0505-186', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3298, 'G0505-187', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3299, 'G0505-188', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3300, 'G0505-189', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3301, 'G0505-190', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3302, 'G0505-191', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3303, 'G0505-192', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3304, 'G0505-193', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3305, 'G0505-194', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3306, 'G0505-195', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3307, 'G0505-196', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3308, 'G0505-197', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3309, 'G0505-198', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3310, 'G0505-199', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3311, 'G0505-200', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3312, 'G0505-201', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3313, 'G0505-202', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3314, 'G0505-203', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3315, 'G0505-204', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3316, 'G0505-205', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3317, 'G0505-206', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3318, 'G0505-207', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3319, 'G0505-208', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3320, 'G0505-209', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3321, 'G0505-210', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3322, 'G0505-211', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3323, 'G0505-212', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3324, 'G0505-213', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3325, 'G0505-214', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3326, 'G0505-215', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3327, 'G0505-216', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3328, 'G0505-217', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3329, 'G0505-218', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3330, 'G0505-219', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3331, 'G0505-220', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3332, 'G0505-221', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3333, 'G0505-222', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3334, 'G0505-223', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3335, 'G0505-224', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3336, 'G0505-225', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3337, 'G0505-226', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3338, 'G0505-227', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3339, 'G0505-228', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3340, 'G0505-229', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3341, 'G0505-230', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3342, 'G0505-231', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3343, 'G0505-232', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3344, 'G0505-233', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3345, 'G0505-234', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3346, 'G0505-235', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3347, 'G0505-236', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3348, 'G0505-237', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3349, 'G0505-238', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3350, 'G0505-239', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3351, 'G0505-240', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3352, 'G0505-241', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3353, 'G0505-242', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3354, 'G0505-243', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3355, 'G0505-244', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3356, 'G0505-245', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3357, 'G0505-246', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3358, 'G0505-247', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3359, 'G0505-248', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3360, 'G0505-249', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3361, 'G0505-250', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3362, 'G0505-251', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3363, 'G0505-252', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3364, 'G0505-253', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3365, 'G0505-254', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3366, 'G0505-255', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3367, 'G0505-256', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3368, 'G0505-257', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3369, 'G0505-258', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3370, 'G0505-259', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3371, 'G0505-260', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3372, 'G0505-261', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3373, 'G0505-262', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3374, 'G0505-263', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3375, 'G0505-264', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3376, 'G0505-265', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3377, 'G0505-266', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3378, 'G0505-267', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:12'),
(3379, 'G0505-268', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3380, 'G0505-269', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3381, 'G0505-270', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3382, 'G0505-271', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3383, 'G0505-272', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3384, 'G0505-273', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3385, 'G0505-274', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3386, 'G0505-275', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3387, 'G0505-276', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3388, 'G0505-277', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3389, 'G0505-278', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3390, 'G0505-279', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3391, 'G0505-280', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3392, 'G0505-281', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3393, 'G0505-282', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3394, 'G0505-283', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3395, 'G0505-284', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3396, 'G0505-285', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3397, 'G0505-286', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3398, 'G0505-287', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3399, 'G0505-288', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3400, 'G0505-289', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3401, 'G0505-290', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3402, 'G0505-291', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3403, 'G0505-292', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3404, 'G0505-293', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3405, 'G0505-294', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3406, 'G0505-295', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3407, 'G0505-296', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3408, 'G0505-297', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3409, 'G0505-298', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3410, 'G0505-299', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3411, 'G0505-300', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3412, 'G0505-301', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3413, 'G0505-302', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3414, 'G0505-303', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3415, 'G0505-304', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3416, 'G0505-305', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3417, 'G0505-306', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3418, 'G0505-307', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3419, 'G0505-308', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3420, 'G0505-309', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3421, 'G0505-310', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3422, 'G0505-311', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3423, 'G0505-312', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3424, 'G0505-313', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3425, 'G0505-314', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3426, 'G0505-315', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3427, 'G0505-316', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3428, 'G0505-317', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3429, 'G0505-318', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3430, 'G0505-319', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3431, 'G0505-320', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3432, 'G0505-321', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3433, 'G0505-322', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3434, 'G0505-323', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3435, 'G0505-324', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3436, 'G0505-325', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3437, 'G0505-326', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3438, 'G0505-327', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3439, 'G0505-328', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3440, 'G0505-329', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3441, 'G0505-330', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3442, 'G0505-331', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3443, 'G0505-332', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3444, 'G0505-333', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3445, 'G0505-334', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3446, 'G0505-335', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3447, 'G0505-336', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3448, 'G0505-337', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3449, 'G0505-338', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3450, 'G0505-339', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3451, 'G0505-340', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3452, 'G0505-341', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3453, 'G0505-342', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3454, 'G0505-343', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3455, 'G0505-344', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3456, 'G0505-345', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3457, 'G0505-346', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3458, 'G0505-347', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3459, 'G0505-348', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3460, 'G0505-349', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3461, 'G0505-350', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3462, 'G0505-351', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3463, 'G0505-352', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3464, 'G0505-353', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3465, 'G0505-354', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3466, 'G0505-355', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3467, 'G0505-356', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3468, 'G0505-357', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3469, 'G0505-358', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3470, 'G0505-359', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3471, 'G0505-360', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3472, 'G0505-361', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3473, 'G0505-362', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3474, 'G0505-363', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3475, 'G0505-364', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3476, 'G0505-365', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3477, 'G0505-366', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3478, 'G0505-367', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3479, 'G0505-368', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3480, 'G0505-369', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3481, 'G0505-370', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3482, 'G0505-371', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3483, 'G0505-372', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3484, 'G0505-373', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3485, 'G0505-374', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3486, 'G0505-375', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3487, 'G0505-376', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3488, 'G0505-377', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3489, 'G0505-378', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3490, 'G0505-379', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3491, 'G0505-380', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3492, 'G0505-381', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3493, 'G0505-382', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3494, 'G0505-383', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3495, 'G0505-384', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3496, 'G0505-385', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3497, 'G0505-386', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3498, 'G0505-387', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3499, 'G0505-388', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3500, 'G0505-389', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3501, 'G0505-390', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3502, 'G0505-391', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3503, 'G0505-392', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3504, 'G0505-393', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3505, 'G0505-394', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3506, 'G0505-395', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3507, 'G0505-396', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3508, 'G0505-397', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3509, 'G0505-398', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3510, 'G0505-399', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3511, 'G0505-400', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3512, 'G0505-401', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3513, 'G0505-402', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3514, 'G0505-403', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3515, 'G0505-404', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3516, 'G0505-405', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3517, 'G0505-406', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3518, 'G0505-407', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3519, 'G0505-408', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3520, 'G0505-409', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3521, 'G0505-410', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3522, 'G0505-411', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3523, 'G0505-412', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3524, 'G0505-413', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3525, 'G0505-414', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3526, 'G0505-415', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3527, 'G0505-416', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3528, 'G0505-417', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3529, 'G0505-418', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3530, 'G0505-419', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3531, 'G0505-420', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3532, 'G0505-421', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3533, 'G0505-422', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3534, 'G0505-423', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3535, 'G0505-424', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3536, 'G0505-425', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3537, 'G0505-426', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3538, 'G0505-427', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3539, 'G0505-428', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3540, 'G0505-429', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3541, 'G0505-430', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3542, 'G0505-431', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3543, 'G0505-432', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3544, 'G0505-433', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3545, 'G0505-434', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3546, 'G0505-435', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3547, 'G0505-436', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3548, 'G0505-437', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3549, 'G0505-438', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3550, 'G0505-439', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3551, 'G0505-440', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3552, 'G0505-441', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3553, 'G0505-442', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3554, 'G0505-443', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3555, 'G0505-444', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3556, 'G0505-445', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3557, 'G0505-446', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3558, 'G0505-447', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3559, 'G0505-448', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3560, 'G0505-449', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3561, 'G0505-450', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3562, 'G0505-451', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3563, 'G0505-452', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3564, 'G0505-453', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3565, 'G0505-454', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3566, 'G0505-455', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3567, 'G0505-456', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3568, 'G0505-457', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3569, 'G0505-458', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3570, 'G0505-459', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3571, 'G0505-460', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3572, 'G0505-461', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3573, 'G0505-462', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3574, 'G0505-463', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3575, 'G0505-464', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3576, 'G0505-465', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3577, 'G0505-466', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3578, 'G0505-467', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3579, 'G0505-468', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3580, 'G0505-469', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3581, 'G0505-470', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3582, 'G0505-471', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3583, 'G0505-472', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3584, 'G0505-473', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3585, 'G0505-474', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3586, 'G0505-475', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3587, 'G0505-476', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3588, 'G0505-477', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3589, 'G0505-478', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3590, 'G0505-479', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13'),
(3591, 'G0505-480', 'G', '0.5x0.5', 0.25, 3, 'available', NULL, NULL, NULL, NULL, NULL, '2025-08-20 21:22:13');

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(10) UNSIGNED NOT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(30) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','card','bank','other') NOT NULL DEFAULT 'cash',
  `package_id` int(11) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','voided') NOT NULL DEFAULT 'pending',
  `received_by_user_id` int(11) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `donor_name`, `donor_phone`, `donor_email`, `amount`, `method`, `package_id`, `reference`, `status`, `received_by_user_id`, `received_at`, `created_at`) VALUES
(2, 'Robel kifle', '07954793921', NULL, 100.00, 'card', 3, '0252', 'approved', 72, '2025-08-30 08:47:42', '2025-08-30 08:47:42'),
(3, 'Anonymous', '07478906895', NULL, 40.00, 'cash', 4, '0351', 'approved', 61, '2025-08-30 08:48:08', '2025-08-30 08:48:08'),
(4, 'Mikael Kifle', '07827628601', NULL, 10.00, 'card', 4, '1051', 'approved', 70, '2025-08-30 08:48:33', '2025-08-30 08:48:33'),
(5, 'Mulubrhan s jemaw', '07412535133', NULL, 400.00, 'bank', 1, '1003', 'approved', 60, '2025-08-30 08:48:48', '2025-08-30 08:48:48'),
(6, 'Lucas Zewdie', '07960600131', NULL, 20.00, 'card', 4, '0253', 'approved', 72, '2025-08-30 08:49:06', '2025-08-30 08:49:06'),
(7, 'Aron', '07401478608', NULL, 50.00, 'card', 4, '1401', 'approved', 56, '2025-08-30 08:49:20', '2025-08-30 08:49:20'),
(8, 'Daniel Samuel', '07401399936', NULL, 100.00, 'bank', 3, '1001', 'approved', 60, '2025-08-30 08:50:47', '2025-08-30 08:50:47'),
(9, 'Tesfaye Tessema', '07397121515', NULL, 200.00, 'bank', 2, '1002', 'approved', 60, '2025-08-30 08:51:36', '2025-08-30 08:51:36'),
(10, 'Anonymous', '07770422453', NULL, 50.00, 'card', 4, '0751', 'approved', 69, '2025-08-30 08:51:54', '2025-08-30 08:51:54'),
(11, 'Cleve brown', '07901189532', NULL, 5.00, 'card', 4, '0852', 'approved', 73, '2025-08-30 08:52:35', '2025-08-30 08:52:35'),
(12, 'Manchester', '07392250909', NULL, 100.00, 'card', 3, '1455', 'approved', 54, '2025-08-30 08:52:53', '2025-08-30 08:52:53'),
(13, 'Samsung Taye', '07983535670', NULL, 400.00, 'bank', 1, '1004', 'approved', 60, '2025-08-30 08:53:04', '2025-08-30 08:53:04'),
(14, 'Bamlak and yannick', '07534087362', NULL, 100.00, 'card', 3, '1201', 'approved', 65, '2025-08-30 08:53:33', '2025-08-30 08:53:33'),
(15, 'Anonymous', '07555808564', NULL, 250.00, 'cash', 4, '0102', 'approved', 49, '2025-08-30 08:54:32', '2025-08-30 08:54:32'),
(16, 'Aderagw tadele', '07307070874', NULL, 100.00, 'cash', 3, '0051', 'approved', 66, '2025-08-30 08:54:58', '2025-08-30 08:54:58'),
(17, 'Haseit Desta', '07956053210', NULL, 100.00, 'card', 3, '0302', 'approved', 57, '2025-08-30 08:55:15', '2025-08-30 08:55:15'),
(18, 'Kibrom teklu', '07908074937', NULL, 100.00, 'bank', 3, '1005', 'approved', 60, '2025-08-30 08:55:17', '2025-08-30 08:55:17'),
(19, 'Rahel G/selase', '07477575455', NULL, 100.00, 'card', 3, '0202', 'approved', 52, '2025-08-30 08:55:19', '2025-08-30 08:55:19'),
(20, 'Faniel Tena Gashaw', '07950827195', NULL, 200.00, 'card', 2, '0905', 'approved', 55, '2025-08-30 08:55:47', '2025-08-30 08:55:47'),
(21, 'Efrem', '07494116274', NULL, 100.00, 'bank', 3, '1006', 'approved', 60, '2025-08-30 08:56:35', '2025-08-30 08:56:35'),
(22, 'Bayush Kumssa', '07883520733', NULL, 100.00, 'cash', 4, '0103', 'approved', 49, '2025-08-30 08:56:57', '2025-08-30 08:56:57'),
(23, 'Wonwossen redie', '07400576655', NULL, 20.00, 'card', 4, '0853', 'approved', 73, '2025-08-30 08:57:05', '2025-08-30 08:57:05'),
(24, 'Glasgow', '07477012068', NULL, 100.00, 'cash', 3, '1457', 'approved', 54, '2025-08-30 08:57:12', '2025-08-30 08:57:12'),
(25, 'Mesfn lodamo', '07840567941', NULL, 30.00, 'cash', 4, '1354', 'approved', 67, '2025-08-30 08:57:17', '2025-08-30 08:57:17'),
(26, 'Alemayew hayelu', '07539479598', NULL, 200.00, 'bank', 2, '0155', 'approved', 64, '2025-08-30 08:57:34', '2025-08-30 08:57:34'),
(28, 'Fasil Kinde', '07861779409', NULL, 100.00, 'bank', 3, '0402', 'approved', 62, '2025-08-30 08:58:37', '2025-08-30 08:58:37'),
(29, 'Amanuel belay', '07392425512', NULL, 20.00, 'cash', 4, '1355', 'approved', 67, '2025-08-30 08:59:29', '2025-08-30 08:59:29'),
(30, 'Mikael teshome', '07749750938', NULL, 100.00, 'card', 4, '0854', 'approved', 73, '2025-08-30 09:00:02', '2025-08-30 09:00:02'),
(31, 'Birhanu Yemenu', '07340231221', NULL, 100.00, 'card', 3, '0906', 'approved', 55, '2025-08-30 09:00:05', '2025-08-30 09:00:05'),
(32, 'Fithi Teklit', '07537919468', NULL, 100.00, 'card', 3, '0104', 'approved', 49, '2025-08-30 09:00:29', '2025-08-30 09:00:29'),
(33, 'Kesis Mezmur', '07883746201', NULL, 100.00, 'card', 3, '0907', 'approved', 55, '2025-08-30 09:00:38', '2025-08-30 09:00:38'),
(34, 'Mahari melash', '07479537728', NULL, 100.00, 'bank', 3, '0258', 'approved', 72, '2025-08-30 09:01:51', '2025-08-30 09:01:51'),
(35, 'Anonymous', '07925460025', NULL, 20.00, 'cash', 4, '0053', 'approved', 66, '2025-08-30 09:02:06', '2025-08-30 09:02:06'),
(36, 'Welete tekekehaymanot', '07932920957', NULL, 100.00, 'bank', 3, '0307', 'approved', 57, '2025-08-30 09:02:20', '2025-08-30 09:02:20'),
(37, 'Anonymous', '07830500872', NULL, 100.00, 'bank', 3, '1054', 'approved', 70, '2025-08-30 09:03:10', '2025-08-30 09:03:10'),
(38, 'Kesis Senay', '07493724331', NULL, 60.00, 'cash', 4, '0902', 'approved', 55, '2025-08-30 09:04:01', '2025-08-30 09:04:01'),
(39, 'Ymesgen nesibu', '07852462473', NULL, 50.00, 'bank', 4, '1356', 'approved', 67, '2025-08-30 09:04:08', '2025-08-30 09:04:08'),
(40, 'Kebita', '07886108997', NULL, 200.00, 'card', 2, '0259', 'approved', 72, '2025-08-30 09:04:40', '2025-08-30 09:04:40'),
(41, 'Birhan Reta', '07481157860', NULL, 50.00, 'card', 4, '0105', 'approved', 49, '2025-08-30 09:05:29', '2025-08-30 09:05:29'),
(42, 'Anonymous', '07774251350', NULL, 10.00, 'cash', 4, '0752', 'approved', 69, '2025-08-30 09:06:17', '2025-08-30 09:06:17'),
(43, 'Anonymous', '07468702638', NULL, 30.00, 'cash', 4, '1407', 'approved', 56, '2025-08-30 09:06:23', '2025-08-30 09:06:23'),
(44, 'Wolde senebet', '07925452112', NULL, 10.00, 'cash', 4, '0055', 'approved', 66, '2025-08-30 09:06:26', '2025-08-30 09:06:26'),
(45, 'Kesis Solomon', '07492211963', NULL, 50.00, 'card', 4, '0909', 'approved', 55, '2025-08-30 09:07:14', '2025-08-30 09:07:14'),
(46, 'Sosna yegzaw', '07719665417', NULL, 100.00, 'bank', 3, '0003', 'approved', 71, '2025-08-30 09:07:39', '2025-08-30 09:07:39'),
(47, 'Abel berhe', '07492712214', NULL, 10.00, 'cash', 4, '0056', 'approved', 66, '2025-08-30 09:07:44', '2025-08-30 09:07:44'),
(48, 'D/n Hailemariam', '07383955546', NULL, 70.00, 'cash', 4, '0910', 'approved', 55, '2025-08-30 09:08:16', '2025-08-30 09:08:16'),
(49, 'Heyaw demeke', '07305276115', NULL, 100.00, 'bank', 3, '0158', 'approved', 64, '2025-08-30 09:09:26', '2025-08-30 09:09:26'),
(50, 'Emebet worku', '07903011312', NULL, 100.00, 'bank', 3, '0309', 'approved', 57, '2025-08-30 09:09:52', '2025-08-30 09:09:52'),
(51, 'Makers adonnay', '07440566628', NULL, 10.00, 'cash', 4, '0106', 'approved', 49, '2025-08-30 09:12:04', '2025-08-30 09:12:04'),
(52, 'Woltebrhan', '07463496760', NULL, 30.00, 'cash', 4, '1265', 'approved', 6, '2025-08-30 09:13:01', '2025-08-30 09:13:01'),
(53, 'Yohanis aklilu', '07949146267', NULL, 100.00, 'card', 3, '0856', 'approved', 73, '2025-08-30 09:13:02', '2025-08-30 09:13:02'),
(54, 'Welete tinsae', '07378954333', NULL, 100.00, 'bank', 3, '0310', 'approved', 57, '2025-08-30 09:13:51', '2025-08-30 09:13:51'),
(55, 'Hailegiyorgis Families', '07478119519', NULL, 200.00, 'bank', 2, '1056', 'approved', 70, '2025-08-30 09:16:52', '2025-08-30 09:16:52'),
(56, 'Dagem dawit', '07988005934', NULL, 20.00, 'cash', 4, '0857', 'approved', 73, '2025-08-30 09:18:30', '2025-08-30 09:18:30'),
(57, 'Fekeremareyam', '07479348031', '', 20.00, 'card', 4, '0159', 'approved', 64, '2025-08-30 09:18:38', '2025-08-30 09:18:38'),
(58, 'Sosena gizaw', '07709665417', NULL, 20.00, 'cash', 4, '0002', 'approved', 71, '2025-08-30 09:19:59', '2025-08-30 09:19:59'),
(59, 'Welete giwergis', '07984988480', '', 20.00, 'cash', 2, '0314', 'approved', 57, '2025-08-30 09:20:29', '2025-08-30 09:20:29'),
(60, 'Mahlet  Tamrat', '07468861426', NULL, 100.00, 'card', 3, '1014', 'approved', 60, '2025-08-30 09:21:01', '2025-08-30 09:21:01'),
(61, 'Eliyana yoseph', '07365050066', NULL, 100.00, 'bank', 3, '1358', 'approved', 67, '2025-08-30 09:23:17', '2025-08-30 09:23:17'),
(62, 'Eleni adane', '07444164568', NULL, 10.00, 'cash', 4, '0057', 'approved', 66, '2025-08-30 09:23:47', '2025-08-30 09:23:47'),
(63, 'Tesfaye Berhane', '07907038985', NULL, 10.00, 'cash', 4, '1057', 'approved', 70, '2025-08-30 09:24:19', '2025-08-30 09:24:19'),
(64, 'Kidus Kidane', '07932739010', NULL, 100.00, 'cash', 3, '0251', 'approved', 54, '2025-08-30 09:26:51', '2025-08-30 09:26:51'),
(65, 'Mihiret alemayhu', '07496348507', NULL, 20.00, 'card', 4, '0858', 'approved', 73, '2025-08-30 09:29:55', '2025-08-30 09:29:55');

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

CREATE TABLE `pledges` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(30) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `source` enum('self','volunteer') NOT NULL DEFAULT 'volunteer',
  `anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('pledge','paid') NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `client_uuid` char(36) DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `status_changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pledges`
--

INSERT INTO `pledges` (`id`, `donor_name`, `donor_phone`, `donor_email`, `package_id`, `source`, `anonymous`, `amount`, `type`, `status`, `notes`, `client_uuid`, `ip_address`, `user_agent`, `proof_path`, `created_by_user_id`, `approved_by_user_id`, `created_at`, `approved_at`, `status_changed_at`) VALUES
(1, 'Samia Ahmed', '07495532455', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1251', '63732bd9-6576-4187-85d0-bcefc16a4250', NULL, NULL, NULL, 6, 3, '2025-08-30 08:28:45', '2025-08-30 08:44:29', '2025-08-30 08:44:29'),
(2, 'Degole seboka', '07468604352', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1252', 'a7a0e157-1f82-4a46-b4db-fc31da39da69', NULL, NULL, NULL, 6, 3, '2025-08-30 08:29:43', '2025-08-30 08:44:40', '2025-08-30 08:44:40'),
(3, 'Deborah Seboka', '07467864877', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1253', 'd9df3bba-ca03-4e47-9eb8-e9ed236e82ff', NULL, NULL, NULL, 6, 3, '2025-08-30 08:30:40', '2025-08-30 08:44:53', '2025-08-30 08:44:53'),
(4, 'Dinah Seboka', '07939886855', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1254', '60c2f2ec-d5f6-45f0-b141-a2e9bc2941c4', NULL, NULL, NULL, 6, 3, '2025-08-30 08:31:45', '2025-08-30 08:44:55', '2025-08-30 08:44:55'),
(5, 'Daniella Seboka', '07494977614', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1255', '47cc4c97-7348-4087-92dc-8f3c2c6e9bc3', NULL, NULL, NULL, 6, 3, '2025-08-30 08:32:23', '2025-08-30 08:44:59', '2025-08-30 08:44:59'),
(6, 'Estemareyam', '07925460139', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0151', 'd136db24-412b-459b-bead-f7e3e106050d', NULL, NULL, NULL, 64, 3, '2025-08-30 08:34:39', '2025-08-30 08:45:00', '2025-08-30 08:45:00'),
(7, 'Saron', '07365727715', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0152', '34450ab0-ae21-42c7-97f4-09b678d7d3cf', NULL, NULL, NULL, 64, 3, '2025-08-30 08:41:47', '2025-08-30 08:45:03', '2025-08-30 08:45:03'),
(8, 'Samerawt', '07923923753', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0153', '9938dffa-2ff0-4ebe-9024-78762740944a', NULL, NULL, NULL, 64, 3, '2025-08-30 08:42:57', '2025-08-30 08:45:07', '2025-08-30 08:45:07'),
(9, 'Anonymous', '07479378452', NULL, 2, 'volunteer', 1, 200.00, 'pledge', 'approved', '1451', '70b3e7cb-1252-46b2-b765-020aeaf17708', NULL, NULL, NULL, 54, 3, '2025-08-30 08:47:11', '2025-08-30 08:47:24', '2025-08-30 08:47:24'),
(11, 'Abera', '07735842081', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1256', 'e1707ab5-eeb6-49e6-8f38-6e052ea94da1', NULL, NULL, NULL, 6, 3, '2025-08-30 08:48:33', '2025-08-30 08:49:30', '2025-08-30 08:49:30'),
(12, 'Messing Aregay', '07566231484', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0401', '341092ad-a458-4417-a232-a9c09b5c2d15', NULL, NULL, NULL, 62, 3, '2025-08-30 08:48:46', '2025-08-30 08:49:45', '2025-08-30 08:49:45'),
(13, 'Frehiwot Tadese', '07392964781', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0101', '112d6369-1078-4362-ab4a-0aa1f069a979', NULL, NULL, NULL, 49, 3, '2025-08-30 08:48:48', '2025-08-30 08:50:01', '2025-08-30 08:50:01'),
(14, 'Mekdese mari yam', '07886438486', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1452', '425cf6e4-12be-4905-bef9-a80f0e7b9bd8', NULL, NULL, NULL, 54, 3, '2025-08-30 08:48:56', '2025-08-30 08:50:07', '2025-08-30 08:50:07'),
(15, 'Ghirmay Reda', '07424505092', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0901', '4f242d41-f8a5-471e-8185-ca86fbf0e64f', NULL, NULL, NULL, 55, 3, '2025-08-30 08:48:57', '2025-08-30 08:50:14', '2025-08-30 08:50:14'),
(16, 'Anonymous', '07525067721', NULL, 4, 'volunteer', 1, 30.00, 'pledge', 'approved', '1351', '2ddd651d-cb84-4aa9-97b2-41f27c008cf1', NULL, NULL, NULL, 67, 3, '2025-08-30 08:49:15', '2025-08-30 08:50:18', '2025-08-30 08:50:18'),
(17, 'Serkalem Molla', '07412196103', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0201', 'e12fc800-7cc3-4795-ad52-35f7893bb9ed', NULL, NULL, NULL, 52, 3, '2025-08-30 08:49:29', '2025-08-30 08:50:39', '2025-08-30 08:50:39'),
(18, 'Daniel', '07366286122', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1052', 'f25f3a4c-7b11-42f9-aa2f-24388c5de673', NULL, NULL, NULL, 70, 3, '2025-08-30 08:49:40', '2025-08-30 08:50:58', '2025-08-30 08:50:58'),
(19, 'Esake', '07587492748', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0155', '14e0d0de-c09b-456f-a57e-0daaed9b0514', NULL, NULL, NULL, 64, 3, '2025-08-30 08:49:53', '2025-08-30 08:50:59', '2025-08-30 08:50:59'),
(20, 'Hanna', '07386002223', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1453', '09d414d9-f68b-4e25-88c7-091a002be7cb', NULL, NULL, NULL, 54, 3, '2025-08-30 08:50:13', '2025-08-30 08:51:02', '2025-08-30 08:51:02'),
(21, 'Dawit brhane', '07424573093', NULL, 4, 'volunteer', 0, 100.00, 'pledge', 'approved', '0851', '94423b64-098d-44fa-befd-81b519657145', NULL, NULL, NULL, 73, 3, '2025-08-30 08:50:19', '2025-08-30 08:51:04', '2025-08-30 08:51:04'),
(22, 'Kidist Shewandagn', '07590914561', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0301', '95bbf132-8338-442a-aca0-667684532e79', NULL, NULL, NULL, 57, 3, '2025-08-30 08:50:37', '2025-08-30 08:51:05', '2025-08-30 08:51:05'),
(23, 'Mulutsega Girma', '07904692115', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1257', '66daaf3d-cb79-4754-b320-d704ab026697', NULL, NULL, NULL, 6, 3, '2025-08-30 08:50:39', '2025-08-30 08:51:07', '2025-08-30 08:51:07'),
(24, 'Solomon', '07908269003', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0902', '498f345e-f1b1-4a81-a1fd-0a8a1fbc57b1', NULL, NULL, NULL, 55, 3, '2025-08-30 08:50:43', '2025-08-30 08:51:09', '2025-08-30 08:51:09'),
(25, 'Eden', '07342442373', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1454', 'a7a0af7d-b402-4946-be9a-34389c60fdf6', NULL, NULL, NULL, 54, 3, '2025-08-30 08:51:10', '2025-08-30 08:52:31', '2025-08-30 08:52:31'),
(26, 'Yohannes Aderajw', '07481239147', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '1352', 'e162472c-b2e0-4b6f-82b2-bf19a3478616', NULL, NULL, NULL, 67, 3, '2025-08-30 08:51:20', '2025-08-30 08:52:32', '2025-08-30 08:52:32'),
(27, 'Brook', '07310274617', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1402', 'e2f6a6fc-e13e-4937-b0bc-6c42e4501731', NULL, NULL, NULL, 56, 3, '2025-08-30 08:51:36', '2025-08-30 08:52:34', '2025-08-30 08:52:34'),
(28, 'Banchi negash', '07397748078', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1303', '56bf789e-4fc5-4016-a018-1385b98341c7', NULL, NULL, NULL, 68, 3, '2025-08-30 08:51:53', '2025-08-30 08:52:35', '2025-08-30 08:52:35'),
(29, 'ከብላክ ፑል የመጡ ምዕመናን', '07360436777', '', 4, 'volunteer', 0, 700.00, 'pledge', '', '0000', '000d16ea-3b3d-49f1-b7ae-413d7d89e9cc', NULL, NULL, NULL, 1, 3, '2025-08-30 08:52:10', '2025-08-30 08:52:38', '2025-08-30 08:52:38'),
(30, 'Demelash Banjaw', '07908621593', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1258', '21e1354f-1c44-4c80-963b-b5814254e465', NULL, NULL, NULL, 6, 3, '2025-08-30 08:52:29', '2025-08-30 08:52:39', '2025-08-30 08:52:39'),
(31, 'Boja brook', '07473774102', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1404', '7e6556e5-05c8-492f-83c8-077d7f936495', NULL, NULL, NULL, 56, 3, '2025-08-30 08:52:50', '2025-08-30 08:53:19', '2025-08-30 08:53:19'),
(32, 'Abraham Gobeze', '07361845061', '', 2, 'volunteer', 0, 100.00, 'pledge', 'approved', '0904', '85466d18-7e77-4089-af0d-4eff91445d4c', NULL, NULL, NULL, 55, 3, '2025-08-30 08:53:11', '2025-08-30 08:53:42', '2025-08-30 08:53:42'),
(33, 'ALEX ASHENAFI (GLASGOW)', '07380176691', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0254', 'b65e401f-74ff-458b-baa8-cab784f6bb50', NULL, NULL, NULL, 72, 3, '2025-08-30 08:53:21', '2025-08-30 08:53:56', '2025-08-30 08:53:56'),
(34, 'Yoseph', '07915920663', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1259', '2278b1f0-cfba-4475-bf9b-03ff70fb5bbf', NULL, NULL, NULL, 6, 3, '2025-08-30 08:53:28', '2025-08-30 08:54:21', '2025-08-30 08:54:21'),
(35, 'Maza', '07404290971', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1456', 'a6b72f40-c057-4aaa-92e8-209b30a00ab9', NULL, NULL, NULL, 54, 3, '2025-08-30 08:54:44', '2025-08-30 08:55:27', '2025-08-30 08:55:27'),
(36, 'Bruk moges', '07538000787', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1353', '6de843a6-c942-4cb2-b643-1dbeaa2c5c57', NULL, NULL, NULL, 67, 3, '2025-08-30 08:55:15', '2025-08-30 08:55:36', '2025-08-30 08:55:36'),
(37, 'Amen Hailesillase', '07940010678', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1403', 'f5364f93-f0ee-4fb4-b5e7-1184fd1b5181', NULL, NULL, NULL, 56, 3, '2025-08-30 08:55:31', '2025-08-30 08:56:14', '2025-08-30 08:56:14'),
(38, 'Soltana tekle', '07594102313', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1304', 'e1d9db37-85e9-4242-b642-07381e297db8', NULL, NULL, NULL, 68, 3, '2025-08-30 08:56:03', '2025-08-30 08:56:34', '2025-08-30 08:56:34'),
(39, 'Wagi', '07867158031', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0255', '88d2b3f9-e16c-4eb7-aef3-0a45b73c6e32', NULL, NULL, NULL, 72, 3, '2025-08-30 08:56:03', '2025-08-30 08:56:40', '2025-08-30 08:56:40'),
(40, 'Anonymous', '07476211172', NULL, 1, 'volunteer', 1, 400.00, 'pledge', 'approved', '0352', '36ac9255-5dbd-492b-ab68-c304adc013d3', NULL, NULL, NULL, 61, 3, '2025-08-30 08:56:14', '2025-08-30 08:56:47', '2025-08-30 08:56:47'),
(41, 'Senile Gebeeyes', '07984287303', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '0303', 'd9b8389c-5d1f-4dbe-80b4-17ef574e0183', NULL, NULL, NULL, 57, 3, '2025-08-30 08:56:38', '2025-08-30 08:57:03', '2025-08-30 08:57:03'),
(42, 'Nardos', '07464578594', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1405', 'f297ed8c-3168-48b0-86de-484e008f7233', NULL, NULL, NULL, 56, 3, '2025-08-30 08:56:51', '2025-08-30 08:57:19', '2025-08-30 08:57:19'),
(43, 'Adiyam sahle', '07541250970', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1305', '9e8e003c-5a76-4fba-9fd6-63704707a723', NULL, NULL, NULL, 68, 3, '2025-08-30 08:57:37', '2025-08-30 08:58:26', '2025-08-30 08:58:26'),
(44, 'Dr Getinet Mekuriaw Tarekegn Glasgow', '07519734921', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1053', '08f95f98-6b8d-4d05-90e4-209fdeefdabe', NULL, NULL, NULL, 70, 3, '2025-08-30 08:57:54', '2025-08-30 08:58:32', '2025-08-30 08:58:32'),
(45, 'Genet Sebehatu', '07476740578', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0304', '8ff9e335-56e1-493c-8f7a-5b4c5a459416', NULL, NULL, NULL, 57, 3, '2025-08-30 08:57:54', '2025-08-30 08:58:35', '2025-08-30 08:58:35'),
(46, 'Abebe', '07424783538', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0256', '5992543d-5856-4228-ad6c-bf4a6eec47f7', NULL, NULL, NULL, 72, 3, '2025-08-30 08:58:12', '2025-08-30 08:59:02', '2025-08-30 08:59:02'),
(47, 'Elias Hailgebreil', '07823998547', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0353', '8abe7ffc-c031-4eb7-af30-752060095d73', NULL, NULL, NULL, 61, 3, '2025-08-30 08:58:55', '2025-08-30 08:59:11', '2025-08-30 08:59:11'),
(48, 'Meseret Yohannes', '07440608564', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1202', 'ef3c8d8f-0c69-4d5d-9ed6-07fb322a2855', NULL, NULL, NULL, 65, 3, '2025-08-30 08:59:14', '2025-08-30 09:00:09', '2025-08-30 09:00:09'),
(49, 'Yakob tesfaye', '07479237625', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0052', '1fa53853-616a-4d14-b3d1-afe177fa5a56', NULL, NULL, NULL, 66, 3, '2025-08-30 08:59:15', '2025-08-30 09:00:16', '2025-08-30 09:00:16'),
(50, 'Damtew ashenafi', '07587377063', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0305', '3e10ef30-4e8f-4085-856e-a8dc82e16249', NULL, NULL, NULL, 57, 3, '2025-08-30 08:59:22', '2025-08-30 09:00:23', '2025-08-30 09:00:23'),
(51, 'Behailu Tihitina', '07830500873', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1054', 'de222549-b0e5-40c1-9e05-5b8e6664818a', NULL, NULL, NULL, 70, 3, '2025-08-30 08:59:51', '2025-08-30 09:00:38', '2025-08-30 09:00:38'),
(52, 'Dawit weledkiros', '07788162987', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0257', 'f1d26615-0139-4f7d-9d2b-56f95e1e8609', NULL, NULL, NULL, 72, 3, '2025-08-30 08:59:52', '2025-08-30 09:00:54', '2025-08-30 09:00:54'),
(53, 'TEWODROS TADESSE', '07479024740', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1261', 'd17f53d2-d720-46af-8046-9c287a22bb7b', NULL, NULL, NULL, 6, 3, '2025-08-30 08:59:53', '2025-08-30 09:00:55', '2025-08-30 09:00:55'),
(54, 'Anonymous', '07449479316', NULL, 3, 'volunteer', 1, 100.00, 'pledge', 'approved', '0001', 'db3301f0-9afc-4b43-96b7-cb23e1cbf1e1', NULL, NULL, NULL, 71, 3, '2025-08-30 09:00:05', '2025-08-30 09:01:03', '2025-08-30 09:01:03'),
(55, 'Anonymous', '07742051164', NULL, 3, 'volunteer', 1, 100.00, 'pledge', 'approved', '0203', '0cc86500-eaef-4ca4-a7e3-da1888078ef7', NULL, NULL, NULL, 52, 3, '2025-08-30 09:00:08', '2025-08-30 09:01:12', '2025-08-30 09:01:12'),
(56, 'Kaletsidk Fasil', '07731749674', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1007', '69354304-4190-459b-90b7-f3f49e8e0c56', NULL, NULL, NULL, 60, 3, '2025-08-30 09:00:08', '2025-08-30 09:01:16', '2025-08-30 09:01:16'),
(57, 'Chemeka', '07832203020', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1406', 'f144dc44-d5a9-4f23-b1ba-6aa7cbca1848', NULL, NULL, NULL, 56, 3, '2025-08-30 09:00:37', '2025-08-30 09:01:25', '2025-08-30 09:01:25'),
(58, 'Ehete maream', '07564754310', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0306', '535c578c-9b63-4213-89d2-7da2720db44a', NULL, NULL, NULL, 57, 3, '2025-08-30 09:00:40', '2025-08-30 09:01:34', '2025-08-30 09:01:34'),
(59, 'Aberham', '07350891894', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0156', 'e5f8fefe-18e2-472d-8259-323e009414cd', NULL, NULL, NULL, 64, 3, '2025-08-30 09:00:49', '2025-08-30 09:01:38', '2025-08-30 09:01:38'),
(60, 'Hundaftol Yohannes', '07554113335', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '0403', '46bbfdfa-4a9c-4445-9f3e-b88ed2908f8c', NULL, NULL, NULL, 62, 3, '2025-08-30 09:01:18', '2025-08-30 09:01:45', '2025-08-30 09:01:45'),
(61, 'Ephrem Retta', '07386740405', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1262', 'f35eb875-2176-478a-8a06-bcce94ef140a', NULL, NULL, NULL, 6, 3, '2025-08-30 09:01:31', '2025-08-30 09:01:51', '2025-08-30 09:01:51'),
(62, 'Freweyni mekonen', '07895724124', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1306', '0ee3dbae-7c91-467d-92d1-e5fd4af56899', NULL, NULL, NULL, 68, 3, '2025-08-30 09:01:48', '2025-08-30 09:01:56', '2025-08-30 09:01:56'),
(63, 'Esubalew', '07393455999', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1101', '2b36816a-23a4-460f-9eb7-26773a4ba2f8', NULL, NULL, NULL, 48, 3, '2025-08-30 09:01:57', '2025-08-30 09:02:14', '2025-08-30 09:02:14'),
(64, 'Henok senay', '07473327733', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1263', '5389bcdc-932c-4c0a-87f2-dff6dfcc9196', NULL, NULL, NULL, 6, 3, '2025-08-30 09:02:25', '2025-08-30 09:03:54', '2025-08-30 09:03:54'),
(65, 'Welde Giorgis + Menbere Mariam', '07438854196', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '0908', 'e0b55248-247a-498c-b9f4-06fdd19adb88', NULL, NULL, NULL, 55, 3, '2025-08-30 09:03:01', '2025-08-30 09:03:57', '2025-08-30 09:03:57'),
(66, 'Gebriye getachew', '07470200855', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0054', 'b10e8d51-e72c-4f5a-8f7c-b758a8391176', NULL, NULL, NULL, 66, 3, '2025-08-30 09:03:33', '2025-08-30 09:04:08', '2025-08-30 09:04:08'),
(67, 'Yarede mesefen', '07391949285', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0157', 'e52986ee-521e-4da7-a4c9-da89ee5af3f7', NULL, NULL, NULL, 64, 3, '2025-08-30 09:03:44', '2025-08-30 09:04:14', '2025-08-30 09:04:14'),
(68, 'Lucy and Dina', '07459059542', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1264', 'a4a4a058-e4cb-446b-a1e7-948756446215', NULL, NULL, NULL, 6, 3, '2025-08-30 09:03:44', '2025-08-30 09:04:18', '2025-08-30 09:04:18'),
(69, 'Sara Aregay', '07928499001', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0204', '1182963c-4c08-4c75-935c-e435140e6a09', NULL, NULL, NULL, 52, 3, '2025-08-30 09:03:49', '2025-08-30 09:04:21', '2025-08-30 09:04:21'),
(70, 'Bemenet Fasil', '07949673168', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1008', 'f6b5a45e-bd2c-49af-a757-98da37083c7c', NULL, NULL, NULL, 60, 3, '2025-08-30 09:04:08', '2025-08-30 09:04:32', '2025-08-30 09:04:32'),
(71, 'Dahlak', '07956275687', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1203', '7685ef77-dd11-49d6-91ba-865a16af1b48', NULL, NULL, NULL, 65, 3, '2025-08-30 09:04:15', '2025-08-30 09:04:39', '2025-08-30 09:04:39'),
(72, 'Selam tadese', '07391460812', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0308', '6bf1ba3a-407e-44ca-8569-85b2da4c62ae', NULL, NULL, NULL, 57, 3, '2025-08-30 09:04:21', '2025-08-30 09:04:46', '2025-08-30 09:04:46'),
(73, 'Fseha mngstu', '07428015543', NULL, 4, 'volunteer', 0, 100.00, 'pledge', 'approved', '0855', '0cc67423-2cf3-4462-a256-43d31be203fa', NULL, NULL, NULL, 73, 3, '2025-08-30 09:04:47', '2025-08-30 09:04:54', '2025-08-30 09:04:54'),
(74, 'Ashenafi yirga', '07399662345', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1357', '2807f9fb-dcd2-4e8e-81f1-2c1182ec3f0b', NULL, NULL, NULL, 67, 3, '2025-08-30 09:05:48', '2025-08-30 09:08:34', '2025-08-30 09:08:34'),
(75, 'Habte Meskel', '07504672995', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1102', '6efea69d-5669-4d4b-bfbc-6c7219b12a9c', NULL, NULL, NULL, 48, 3, '2025-08-30 09:06:40', '2025-08-30 09:08:39', '2025-08-30 09:08:39'),
(76, 'Alem mulu', '07867102289', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0451', '32029981-0aa9-42c5-b627-03ab7ee88fdf', NULL, NULL, NULL, 8, 3, '2025-08-30 09:07:46', '2025-08-30 09:08:52', '2025-08-30 09:08:52'),
(77, 'Fasil Tesfaye', '07731749673', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1009', '61e0f4b9-8f02-4494-a7a9-7087045adcb5', NULL, NULL, NULL, 60, 3, '2025-08-30 09:08:03', '2025-08-30 09:08:59', '2025-08-30 09:08:59'),
(78, 'Nanny fantahun', '07874365490', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1204', '639de6c0-90f2-4514-9f99-8dbf4878555c', NULL, NULL, NULL, 65, 3, '2025-08-30 09:09:25', '2025-08-30 09:15:03', '2025-08-30 09:15:03'),
(79, 'Anonymous', '07502028128', NULL, 3, 'volunteer', 1, 100.00, 'pledge', 'approved', '1055', '5be5053b-9648-4bb6-90ce-11c8df8ac0b8', NULL, NULL, NULL, 70, 3, '2025-08-30 09:09:44', '2025-08-30 09:15:12', '2025-08-30 09:15:12'),
(80, 'Yonas abraham', '07472796824', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1260', '83fe2b8d-8d9f-48ef-8d37-5c0cc024775c', NULL, NULL, NULL, 6, 3, '2025-08-30 09:09:45', '2025-08-30 09:15:15', '2025-08-30 09:15:15'),
(81, 'Meselewerk legese', '07508684764', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0205', '9eabc1c8-d010-440c-b563-c77ed1a75972', NULL, NULL, NULL, 52, 3, '2025-08-30 09:10:48', '2025-08-30 09:15:24', '2025-08-30 09:15:24'),
(82, 'Abenzer', '07392662088', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1458', 'f181adc5-102e-4423-8c0f-79175b15432e', NULL, NULL, NULL, 54, 3, '2025-08-30 09:11:34', '2025-08-30 09:15:26', '2025-08-30 09:15:26'),
(83, 'Senait Kidene', '07401924609', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1010', '1407ab3e-c406-4125-885b-e58c5a317e14', NULL, NULL, NULL, 60, 3, '2025-08-30 09:12:14', '2025-08-30 09:15:28', '2025-08-30 09:15:28'),
(84, 'Ruth dagim', '07925464119', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1205', '15d8c6e4-34a7-4737-acf2-5c4cabd9eea7', NULL, NULL, NULL, 65, 3, '2025-08-30 09:12:47', '2025-08-30 09:15:30', '2025-08-30 09:15:30'),
(85, 'Eliyana yoseph', '07365050065', NULL, 4, 'volunteer', 0, 300.00, 'pledge', 'approved', '1358', 'ab860c43-8c08-4dfd-b2f1-c4a33bd266b8', NULL, NULL, NULL, 67, 3, '2025-08-30 09:12:50', '2025-08-30 09:15:39', '2025-08-30 09:15:39'),
(86, 'Mekdelawit s Asefa', '07550759144', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1011', '71c8ae2b-5cfa-4784-a503-505497a500f9', NULL, NULL, NULL, 60, 3, '2025-08-30 09:13:47', '2025-08-30 09:15:46', '2025-08-30 09:15:46'),
(87, 'Hiwan gebrehiwit', '07491874339', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0004', 'eb0a2595-12a0-4dcf-a77f-b7c0ef271a93', NULL, NULL, NULL, 71, 3, '2025-08-30 09:13:56', '2025-08-30 09:15:51', '2025-08-30 09:15:51'),
(88, 'Amen', '07350940720', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '1459', 'b831d5ac-a9cc-4d8c-982c-5e08dad60564', NULL, NULL, NULL, 54, 3, '2025-08-30 09:14:00', '2025-08-30 09:14:08', '2025-08-30 09:14:08'),
(89, 'Samson', '07543445583', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '0354', 'f20b29c6-3a61-4144-b0fd-c6da9c7f84c5', NULL, NULL, NULL, 61, 3, '2025-08-30 09:14:11', '2025-08-30 09:14:44', '2025-08-30 09:14:44'),
(90, 'Abeba tamene', '07415867316', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0311', '0646421f-18d8-4bae-a5d2-9c40c823a359', NULL, NULL, NULL, 57, 3, '2025-08-30 09:15:33', '2025-08-30 09:15:54', '2025-08-30 09:15:54'),
(91, 'Suzane Abera', '07776896937', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0206', 'ec2c4ef9-1dc1-45f8-ab33-89e10618727b', NULL, NULL, NULL, 52, 3, '2025-08-30 09:16:01', '2025-08-30 09:16:28', '2025-08-30 09:16:28'),
(92, 'Desert Haileselassie /Grace Jones', '07500657641', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1012', 'fdf45570-4b22-4674-8ff0-d0fde524faf7', NULL, NULL, NULL, 60, 3, '2025-08-30 09:16:14', '2025-08-30 09:16:34', '2025-08-30 09:16:34'),
(93, 'Elias Shiferaw', '07459192832', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0911', 'd0ee6702-947d-4379-bd9b-d8c4124c68d7', NULL, NULL, NULL, 55, 3, '2025-08-30 09:16:15', '2025-08-30 09:16:39', '2025-08-30 09:16:39'),
(94, 'Fekeremareym', '07479348032', NULL, 4, 'volunteer', 0, 80.00, 'pledge', 'approved', '0159', '0d1e2343-e4ef-4239-9a77-431c85477fd0', NULL, NULL, NULL, 64, 3, '2025-08-30 09:17:02', '2025-08-30 09:19:05', '2025-08-30 09:19:05'),
(95, 'Bizu', '07477615562', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1206', '33d75df5-6bf4-43db-8806-c5036d2e264d', NULL, NULL, NULL, 65, 3, '2025-08-30 09:17:40', '2025-08-30 09:19:34', '2025-08-30 09:19:34'),
(96, 'Helen Tesfaye', '07393584714', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1013', '6e76a89a-e406-4ef1-8ef1-4ee1b61a4661', NULL, NULL, NULL, 60, 3, '2025-08-30 09:17:43', '2025-08-30 09:19:40', '2025-08-30 09:19:40'),
(97, 'Sentayehu taye', '07592516006', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '0312', 'a0ee25eb-3ffe-46e8-9634-6913b227d534', NULL, NULL, NULL, 57, 3, '2025-08-30 09:17:58', '2025-08-30 09:20:30', '2025-08-30 09:20:30'),
(98, 'Tamiru Legesse', '07455880899', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0912', '6d089fc3-bf5f-4949-a0f4-fff3794e54a5', NULL, NULL, NULL, 55, 3, '2025-08-30 09:19:24', '2025-08-30 09:20:36', '2025-08-30 09:20:36'),
(99, 'Eldana Hagos', '07919618142', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1266', 'c55546b3-7aa3-4b30-85d8-1c2254f68d38', NULL, NULL, NULL, 6, 3, '2025-08-30 09:19:25', '2025-08-30 09:20:42', '2025-08-30 09:20:42'),
(100, 'Arsema', '07301212456', NULL, 1, 'volunteer', 0, 400.00, 'pledge', 'approved', '1207', 'b03a96ca-1606-408d-be17-26a51b4864e1', NULL, NULL, NULL, 65, 3, '2025-08-30 09:19:58', '2025-08-30 09:20:48', '2025-08-30 09:20:48'),
(101, 'Michael Tesfaye', '07476336051', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0405', '4a60f542-8493-4b3c-a9db-9390e6f746b9', NULL, NULL, NULL, 62, 3, '2025-08-30 09:20:26', '2025-08-30 09:21:02', '2025-08-30 09:21:02'),
(102, 'Meron selish', '07939875169', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0005', 'db704148-5c2c-45dc-ba64-1e73501502cf', NULL, NULL, NULL, 71, 3, '2025-08-30 09:21:08', '2025-08-30 09:22:01', '2025-08-30 09:22:01'),
(103, 'Chernat', '07770540106', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1408', 'dd17bfdd-f068-4215-9890-37387bc6b7af', NULL, NULL, NULL, 56, 3, '2025-08-30 09:22:00', '2025-08-30 09:22:18', '2025-08-30 09:22:18'),
(104, 'Gelila tezeta', '07414886466', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0314', 'ee1c70fd-56cd-460b-95bc-cf74239401dd', NULL, NULL, NULL, 57, 3, '2025-08-30 09:22:17', '2025-08-30 09:22:23', '2025-08-30 09:22:23'),
(105, 'Natinael mesefin', '07578686080', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0207', '0b51283c-619d-4e32-ac3c-4029f9245851', NULL, NULL, NULL, 52, 3, '2025-08-30 09:22:32', '2025-08-30 09:22:51', '2025-08-30 09:22:51'),
(106, 'Sara', '07479464264', NULL, 4, 'volunteer', 0, 20.00, 'pledge', 'approved', '1103', '6b4d3dbb-10ff-469b-93d4-8818c5dec969', NULL, NULL, NULL, 48, 3, '2025-08-30 09:23:32', '2025-08-30 09:24:30', '2025-08-30 09:24:30'),
(107, 'Muluken Nigatu', '07466298718', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1359', '3f931977-c11f-4bb8-8172-26264917fb5e', NULL, NULL, NULL, 67, 3, '2025-08-30 09:24:30', '2025-08-30 09:24:50', '2025-08-30 09:24:50'),
(108, 'Michael Gebriyesus', '07378607172', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '0404', '27baac21-8960-4be7-8723-07d2ffe9dbe7', NULL, NULL, NULL, 62, 3, '2025-08-30 09:25:07', '2025-08-30 09:26:47', '2025-08-30 09:26:47'),
(109, 'Estube', '07931261431', NULL, 4, 'volunteer', 0, 10.00, 'pledge', 'approved', '1104', '55102b2e-841b-4d0a-a612-a4c678a4b452', NULL, NULL, NULL, 48, 3, '2025-08-30 09:27:03', '2025-08-30 09:27:26', '2025-08-30 09:27:26'),
(110, 'Geberemeskel Samson', '07466654403', NULL, 4, 'volunteer', 0, 50.00, 'pledge', 'approved', '1058', '0bfac9fa-5514-4fcc-8b1e-a08af5c077b7', NULL, NULL, NULL, 70, 3, '2025-08-30 09:27:59', '2025-08-30 09:29:36', '2025-08-30 09:29:36'),
(111, 'Yeshiwork Berihun', '07878567049', NULL, 3, 'volunteer', 0, 100.00, 'pledge', 'approved', '1301', '6b5f389d-f467-4f26-b212-752d44e23ce2', NULL, NULL, NULL, 68, 3, '2025-08-30 09:29:18', '2025-08-30 09:29:39', '2025-08-30 09:29:39'),
(112, 'Hans Legese', '07915846445', NULL, 2, 'volunteer', 0, 200.00, 'pledge', 'approved', '1208', 'af16cb3f-e675-4114-a5c6-c36158a6a14d', NULL, NULL, NULL, 65, 3, '2025-08-30 09:30:02', '2025-08-30 09:30:36', '2025-08-30 09:30:36');

-- --------------------------------------------------------

--
-- Table structure for table `projector_commands`
--

CREATE TABLE `projector_commands` (
  `id` int(11) NOT NULL,
  `command_type` enum('announcement','footer_message','effect','setting') NOT NULL,
  `command_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`command_data`)),
  `created_by_user_id` int(11) DEFAULT NULL,
  `executed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projector_commands`
--

INSERT INTO `projector_commands` (`id`, `command_type`, `command_data`, `created_by_user_id`, `executed`, `created_at`) VALUES
(1, 'setting', '{\"command\":\"updateSettings\",\"data\":{\"refreshRate\":10,\"displayTheme\":\"celebration\",\"showTicker\":true,\"showProgress\":true,\"showQR\":true,\"showClock\":true},\"timestamp\":1755141337221}', 1, 1, '2025-08-14 04:15:37'),
(2, 'setting', '{\"command\":\"updateSettings\",\"data\":{\"refreshRate\":10,\"displayTheme\":\"celebration\",\"showTicker\":true,\"showProgress\":false,\"showQR\":true,\"showClock\":true},\"timestamp\":1755141369145}', 1, 1, '2025-08-14 04:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `projector_footer`
--

CREATE TABLE `projector_footer` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projector_footer`
--

INSERT INTO `projector_footer` (`id`, `message`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'ለመስጠት እጃቹህ የተዘረጋ በሙሉ የጻድቁ አባታችን አቡነ ተክለሃይማኖት ረድኤት በረከት አይለያቹህ።', 1, '2025-08-14 04:24:51', '2025-08-29 23:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `submission_time` datetime NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `phone_number`, `submission_time`, `metadata`, `created_at`) VALUES
(1, '193.237.166.126', '07415329339', '2025-08-26 02:55:15', '{\"type\":\"pledge\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1 m\\u00b2\"}', '2025-08-26 02:55:15'),
(2, '193.237.166.126', '07415329339', '2025-08-26 02:55:15', '{\"type\":\"pledge\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1 m\\u00b2\",\"partial_success\":true}', '2025-08-26 02:55:15'),
(3, '193.237.166.126', '07415323933', '2025-08-26 02:55:55', '{\"type\":\"pledge\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1 m\\u00b2\"}', '2025-08-26 02:55:55'),
(4, '193.237.166.126', '07360436170', '2025-08-26 02:57:01', '{\"type\":\"pledge\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Linux; Android 10; K) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Mobile Safari\\/537.36\",\"package\":\"1 m\\u00b2\"}', '2025-08-26 02:57:01'),
(5, '193.237.166.126', '07360436179', '2025-08-26 13:09:59', '{\"type\":\"paid\",\"amount\":200,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1\\/2 m\\u00b2\"}', '2025-08-26 13:09:59'),
(6, '193.237.166.126', '07432435431', '2025-08-26 13:12:23', '{\"type\":\"paid\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1 m\\u00b2\"}', '2025-08-26 13:12:23'),
(7, '193.237.166.126', '07432435431', '2025-08-26 13:12:23', '{\"type\":\"paid\",\"amount\":400,\"anonymous\":0,\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/139.0.0.0 Safari\\/537.36\",\"package\":\"1 m\\u00b2\",\"partial_success\":true}', '2025-08-26 13:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `registrar_applications`
--

CREATE TABLE `registrar_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `passcode` varchar(10) DEFAULT NULL,
  `passcode_hash` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registrar_applications`
--

INSERT INTO `registrar_applications` (`id`, `name`, `email`, `phone`, `status`, `passcode`, `passcode_hash`, `notes`, `approved_by_user_id`, `approved_at`, `created_at`, `updated_at`) VALUES
(4, 'Samia Ahmed', 'Samia_feoral1987@yahoo.co.uk', '7495532455', 'approved', NULL, '$2y$10$oiYMWd1KPmrfuOaq8TtUL.W6daCCkWhlnOBd0KJdGbT3yZrhQHS/i', NULL, 1, '2025-08-30 03:59:16', '2025-08-28 21:48:57', '2025-08-30 03:59:16'),
(5, 'Henok Yemane', 'hyemane76@yahoo.co.uk', '07506421367', 'approved', NULL, '$2y$10$gqiamn56eLkkh2EQj7teN.unl6rq02XQ5EfoWj6D1u9EP.ohfv8lu', NULL, 1, '2025-08-30 03:58:15', '2025-08-28 21:49:00', '2025-08-30 03:58:15'),
(6, 'Woinshet', 'm.woynie485@gmail.com', '07932978367', 'approved', NULL, '$2y$10$ecgI1FhHY/l4KbqrVI8.0evgeOBhBYuHnZx707tHfeI63.nFvsZJm', NULL, 1, '2025-08-30 03:57:28', '2025-08-28 21:50:07', '2025-08-30 03:57:28'),
(7, 'Bisrat sewbesew', 'bisrats1999@gmail.com', '07886225123', 'approved', NULL, '$2y$10$Zmh8.GIx5y8/7gxUmZ7hROSIbba4X70FYkpkxVAwBrN5HxIKsDBbu', NULL, 1, '2025-08-30 03:55:07', '2025-08-28 21:51:09', '2025-08-30 03:55:07'),
(8, 'Kebram', 'kebramyirsa@gmail.com', '07459259509', 'approved', NULL, '$2y$10$j7rrBhiRu/7Bp/rJ64bNsOVMpzvY7h9afYDNtIZFXLi5JOqSKXMoG', NULL, 1, '2025-08-30 03:54:18', '2025-08-28 22:00:19', '2025-08-30 03:54:18'),
(9, 'Tigist Legesse', 'tigistlegesse189@gmail.com', '07385549114', 'approved', NULL, '$2y$10$FLQPOsLwQIoAVxer4bJ36eoxR4roUgpQqdZICgPrNDa1ClTNHagDO', NULL, 1, '2025-08-30 03:53:10', '2025-08-28 22:00:38', '2025-08-30 03:53:10'),
(10, 'Mhret Desta', 'mhretdesta3@gmail.com', '07311305605', 'approved', NULL, '$2y$10$WYTb2Ox6V7K9oSEWxmBHQuOlQ3aiuOEvbWwHWBaYtctQeGPyzRFsW', NULL, 1, '2025-08-30 03:52:22', '2025-08-28 22:07:57', '2025-08-30 03:52:22'),
(11, 'Emebet Feisa Asrat', 'genetmekonnen78@gmail.com', '07960907926', 'approved', NULL, '$2y$10$dk.pR6VbAI5Y5IQdDRKt/e7BNyaXd/HupEe1LNFBDr1K2fCN1DbYW', NULL, 1, '2025-08-30 03:51:46', '2025-08-28 22:08:36', '2025-08-30 03:51:46'),
(16, 'Serkalem Molla', 'sermrms@gmail.com', '07412196108', 'approved', NULL, '$2y$10$3cMicWdwI1ljrhE2kXZ.SuWQANxXjqpCJgmnNSp8khJbhx2/j0buW', NULL, 1, '2025-08-30 03:51:07', '2025-08-28 23:19:38', '2025-08-30 03:51:07'),
(26, 'Betremariem Dessey', 'betremariemd@gmail.com', '07740792092', 'approved', NULL, '$2y$10$9hfPbtqsYqaG/bLW7FhKPeKi3/PPa5XDrJVbo2O3pphKzfF7S0KUq', NULL, 1, '2025-08-30 03:50:10', '2025-08-29 04:07:44', '2025-08-30 03:50:10'),
(27, 'Hailemeskel molla', 'biruk400425@gmail.com', '07463287119', 'approved', NULL, '$2y$10$5kiDwbOYZ.Admy3S7sUiD.qeJaRUnlz9VsJI7sRdDF35XsjuORrgW', NULL, 1, '2025-08-30 03:49:27', '2025-08-29 11:50:07', '2025-08-30 03:49:27'),
(28, 'Girma Birhan', 'girmanana@gmail.com', '07873725678', 'approved', NULL, '$2y$10$oBVVPBl00CO5gK9gPBEJ0OUJesQIHUSKLFBeTWEZ6LH9CEov6ZaMy', NULL, 1, '2025-08-30 03:48:48', '2025-08-29 16:45:34', '2025-08-30 03:48:48'),
(29, 'Nebyat Wendwesen', 'nebyw1505@gmail.com', '07403706577', 'approved', NULL, '$2y$10$BUeHvdi8gNhBS3uBj7lNxuHripYdw7VU2o68TqSGzrrXrBgabkUoK', NULL, 1, '2025-08-30 03:47:27', '2025-08-29 16:49:52', '2025-08-30 03:47:27'),
(30, 'Birhanu Worku', 'birhanugw@gmail.com', '07428105118', 'approved', NULL, '$2y$10$Rsya2IgXQQhXZxGi8dMfGOLPkxeNK8DUc/oBGEYeXhO4Icvw7Grzu', NULL, 1, '2025-08-29 21:20:42', '2025-08-29 21:19:51', '2025-08-29 21:20:42'),
(31, 'Samuel Gebreab', 'aregaysami921@gmail.com', '07453303053', 'approved', NULL, '$2y$10$obuszU77Ei6Cr4BV9B4hIuSZBGzGp7Ys6Mb/uOX/ikqSFFkkcXJTe', NULL, 1, '2025-08-29 21:20:09', '2025-08-29 21:20:01', '2025-08-29 21:20:09'),
(37, 'Semane', 'semaneamare@gmail.com', '7930353681', 'approved', NULL, '$2y$10$grYMsG71nOBOTZrcDeyNceZIWTjfKt3UM926U22jt.LUGiQriVXBS', NULL, 1, '2025-08-30 07:52:09', '2025-08-30 07:05:11', '2025-08-30 07:52:09'),
(38, 'Rarity kibrom', 'dsami7496@gmail.com', '07742059068', 'approved', NULL, '$2y$10$0kpwpcKL2hoEFg5VWUQsy.CRdM1gS3NI9uNl1gunR5hnqjJLqIwva', NULL, 1, '2025-08-30 08:18:19', '2025-08-30 07:37:33', '2025-08-30 08:18:19'),
(39, 'MR MICHAEL TESFAYE', 'mtesfaye735@gmail.com', '07476336051', 'approved', NULL, '$2y$10$0I0.pTpGmkyMf9WrPGN5LO59lVP.hR6TMf4/qge/w1.krpSwGzz6e', NULL, 1, '2025-08-30 07:51:47', '2025-08-30 07:44:24', '2025-08-30 07:51:47'),
(40, 'MR MICHAEL TESFAYE', 'tesmic735@gmail.comt', '447476336051', 'approved', NULL, '$2y$10$sd33unQHp4mKU67S8PA8ROXfVh58rzTbzMod09n2K0rO9Z86KqJeC', NULL, 1, '2025-08-30 07:51:26', '2025-08-30 07:45:59', '2025-08-30 07:51:26'),
(41, 'Yonatan kidanemariyam', 'abemelik36@gmail.com', '07828556674', 'approved', NULL, '$2y$10$65S0febXWiHWfwd6NNou4OkhbBizCNgAmLjD.hVj2dvQTmEMqcA6.', NULL, 1, '2025-08-30 07:51:10', '2025-08-30 07:46:06', '2025-08-30 07:51:10'),
(42, 'Henok', 'hd2072054@gmail.com', '07411002386', 'approved', NULL, '$2y$10$zm14LKPj.V56bBDVa1WvkuT0H1vv.900pAKWUextuZ3pYxjWtZxeC', NULL, 1, '2025-08-30 08:18:03', '2025-08-30 08:05:06', '2025-08-30 08:18:03'),
(43, 'Dereje Argaw Woldeselassie', 'argawdereje@yahoo.it', '07383333847', 'approved', NULL, '$2y$10$5ymq1mHG6PL9h0TBLKxMe.QDB4AcB9yFfwZ.V1fUDDquxVtIctHY2', NULL, 1, '2025-08-30 08:17:47', '2025-08-30 08:09:27', '2025-08-30 08:17:47'),
(44, 'Belen Wondimagegne', 'belenmekon3n@gmail.com', '07770422453', 'approved', NULL, '$2y$10$9NT3tp8PGdXV55r7K1.AeeG2ykPuOO6jTuiloq67t0.t52ExrN/vW', NULL, 1, '2025-08-30 08:23:05', '2025-08-30 08:22:54', '2025-08-30 08:23:05'),
(45, 'Tesfaye Hailemichael', 'tesamen12@gmail.com', '07454767141', 'approved', NULL, '$2y$10$YPceOUQ4cOBzkjq0ISdClOdKf5v9UcuRVRz6wQQA7DaHoEMJrwq0q', NULL, 1, '2025-08-30 08:29:46', '2025-08-30 08:23:00', '2025-08-30 08:29:46'),
(46, 'Kidus Feresenbet', 'kidus143kidane@gmail.com', '07932739010', 'approved', NULL, '$2y$10$SLAsJbN1hdsGHFm9llBA6O1VoMDf/TFcloVhsdIHAmQcfpFLcapvG', NULL, 1, '2025-08-30 08:33:46', '2025-08-30 08:28:45', '2025-08-30 08:33:46'),
(47, 'Meron Selish', 'merinati21@gmail.com', '07939875169', 'approved', NULL, '$2y$10$UhixZbE67CzfHj9Kzhs9z.daNDFGd4dhVvsXfS3KQjPZG0FHPCNI.', NULL, 1, '2025-08-30 08:33:14', '2025-08-30 08:31:13', '2025-08-30 08:33:14'),
(48, 'Yohanis aklilu', 'yohanisaklilu5@gmail.com', '07949146267', 'approved', NULL, '$2y$10$BuLoDSJvxQn/063M5LGCS.qUNOX48CfoiCW995k4wh3T2kBrgywDy', NULL, 1, '2025-08-30 08:36:12', '2025-08-30 08:35:51', '2025-08-30 08:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` tinyint(4) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL DEFAULT 100000.00,
  `currency_code` char(3) NOT NULL DEFAULT 'GBP',
  `display_token` char(64) NOT NULL,
  `display_token_expires_at` datetime DEFAULT NULL,
  `projector_names_mode` enum('full','first_initial','off') NOT NULL DEFAULT 'full',
  `refresh_seconds` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `projector_display_mode` varchar(10) DEFAULT 'amount',
  `projector_language` varchar(5) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `target_amount`, `currency_code`, `display_token`, `display_token_expires_at`, `projector_names_mode`, `refresh_seconds`, `version`, `created_at`, `updated_at`, `projector_display_mode`, `projector_language`) VALUES
(1, 100000.00, 'GBP', '7856996902e5612296dd487a8b8a85564407222cbfd8c032b06eb641249505c3', NULL, 'full', 4, 1, '2025-08-11 22:40:50', '2025-08-30 09:08:22', 'both', 'am');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','registrar') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `role`, `password_hash`, `active`, `login_attempts`, `locked_until`, `created_at`, `last_login_at`) VALUES
(1, 'Abel Demssie', '07360436171', 'abelgoytom77@gmail.com', 'admin', '$2y$10$tknh9YlSfER5kAEA4KR8GOachPvlyctfmoW1HwKXVvijNxztHpF2G', 1, 0, NULL, '2025-08-12 01:42:00', '2025-08-30 08:33:00'),
(3, 'Geda Gemechu', '07393180103', 'gedaats@gmail.com', 'admin', '$2y$10$lDJ7d6RchAE1j1oVNccuY.sEKm83hG6amQWLxx2Kiwc0Osom6Zr2a', 1, 0, NULL, '2025-08-14 21:24:39', '2025-08-30 08:39:44'),
(4, 'Kesis Birhanu', '07473822244', 'admin@example.com', 'admin', '$2y$10$OPTwUOGyw9hKB2S/H.vEcOQSTL9jaBKztEkyLRhWKfmGjg8rpogLG', 1, 0, NULL, '2025-08-14 22:18:34', '2025-08-28 20:21:44'),
(6, 'Degole Seboka', '07468604352', 'degole@gmail.com', 'registrar', '$2y$10$KhO74usEwBsCZnJYPTXQ5.BcY7EaYbWjX9MbsFDzIcy7ZnOgIPfpS', 1, 0, NULL, '2025-08-24 11:34:27', '2025-08-30 08:11:54'),
(7, 'Abeselom Tadesse', '07490447376', 'abeselom@gmail.com', 'registrar', '$2y$10$R08Oq6GuQnrDvZbWH/bHbu82FPKz00h0ux.JVPqH2z36Q1wQuriFO', 1, 0, NULL, '2025-08-24 11:38:02', '2025-08-24 11:38:29'),
(8, 'Birhane Bartley', '07951403936', 'birhane@gmial.com', 'registrar', '$2y$10$YdeufmrcC5930edbNVE9f.5cTOmbaSFgn/IY1Mela.7r82E4le.me', 1, 0, NULL, '2025-08-24 11:39:11', '2025-08-30 08:46:38'),
(9, 'Kesis Dagmawi', '07474 962830', 'Kesisdagmawi@gmail.com', 'registrar', '$2y$10$oFmVUNMykZrU0frrxNvqS.9b8IdON.bttANpffx5SKc.BXgERVw6.', 1, 0, NULL, '2025-08-24 11:40:15', '2025-08-24 11:40:56'),
(10, 'Yonas Legese', '07453545654', 'yonas@gmail.com', 'registrar', '$2y$10$oJFxKq1KC1xEiv9XVjBP1eXaVs6HPNsZAOdJ3MD82N2LQ9NAjM7Yi', 1, 0, NULL, '2025-08-24 11:40:59', '2025-08-24 11:51:53'),
(11, 'Gabriel Mader', '07388418902', 'Gabriel@gmail.com', 'registrar', '$2y$10$vYv/emsoXJrMY/Bt2AvmVe9xFpOa3Cgv/TUxhEXurMu94ye/XHs5u', 1, 0, NULL, '2025-08-24 11:42:01', '2025-08-30 09:07:19'),
(18, 'Wagi', '07867158031', 'wagi@gmail.com', 'admin', '$2y$10$BpKKjyLQXx765CZ7s179N.wMIqHYJrRZTOuPKUWxN1KbpadiyPJmC', 1, 0, NULL, '2025-08-26 21:35:06', '2025-08-30 08:39:42'),
(41, 'Samuel Gebreab', '07453303053', 'aregaysami921@gmail.com', 'registrar', '$2y$10$N4Vh9B7KjSxfDrFla5QBH.CjZjnk7eUnoFdZJl293WyAY5J3Gh3cS', 1, 0, NULL, '2025-08-29 21:20:09', '2025-08-30 08:23:52'),
(42, 'Birhanu Worku', '07428105118', 'birhanugw@gmail.com', 'registrar', '$2y$10$CBKEHZvdfqgCn2sgipe4veLAUOLSVE7p.kbWcfMMLF1FCDUaQ1n3u', 1, 0, NULL, '2025-08-29 21:20:42', '2025-08-30 03:42:15'),
(48, 'Nebyat Wendwesen', '07403706577', 'nebyw1505@gmail.com', 'registrar', '$2y$10$BUeHvdi8gNhBS3uBj7lNxuHripYdw7VU2o68TqSGzrrXrBgabkUoK', 1, 0, NULL, '2025-08-30 03:47:27', '2025-08-30 07:34:17'),
(49, 'Girma Birhan', '07873725678', 'girmanana@gmail.com', 'registrar', '$2y$10$oBVVPBl00CO5gK9gPBEJ0OUJesQIHUSKLFBeTWEZ6LH9CEov6ZaMy', 1, 0, NULL, '2025-08-30 03:48:48', '2025-08-30 08:48:08'),
(50, 'Hailemeskel molla', '07463287119', 'biruk400425@gmail.com', 'registrar', '$2y$10$5kiDwbOYZ.Admy3S7sUiD.qeJaRUnlz9VsJI7sRdDF35XsjuORrgW', 1, 0, NULL, '2025-08-30 03:49:27', NULL),
(51, 'Betremariem Dessey', '07740792092', 'betremariemd@gmail.com', 'registrar', '$2y$10$9hfPbtqsYqaG/bLW7FhKPeKi3/PPa5XDrJVbo2O3pphKzfF7S0KUq', 1, 0, NULL, '2025-08-30 03:50:10', NULL),
(52, 'Serkalem Molla', '07412196108', 'sermrms@gmail.com', 'registrar', '$2y$10$3cMicWdwI1ljrhE2kXZ.SuWQANxXjqpCJgmnNSp8khJbhx2/j0buW', 1, 0, NULL, '2025-08-30 03:51:07', '2025-08-30 08:10:20'),
(53, 'Emebet Feisa Asrat', '07960907926', 'genetmekonnen78@gmail.com', 'registrar', '$2y$10$dk.pR6VbAI5Y5IQdDRKt/e7BNyaXd/HupEe1LNFBDr1K2fCN1DbYW', 1, 0, NULL, '2025-08-30 03:51:46', NULL),
(54, 'Mhret Desta', '07311305605', 'mhretdesta3@gmail.com', 'registrar', '$2y$10$WYTb2Ox6V7K9oSEWxmBHQuOlQ3aiuOEvbWwHWBaYtctQeGPyzRFsW', 1, 0, NULL, '2025-08-30 03:52:22', '2025-08-30 08:19:20'),
(55, 'Tigist Legesse', '07385549114', 'tigistlegesse189@gmail.com', 'registrar', '$2y$10$CYiaj7tQtX.6thPboXWtjue5XOWkx7LNYhQjWgg3CASO7/Y1g1bty', 1, 0, NULL, '2025-08-30 03:53:10', '2025-08-30 08:35:20'),
(56, 'Kebram', '07459259509', 'kebramyirsa@gmail.com', 'registrar', '$2y$10$j7rrBhiRu/7Bp/rJ64bNsOVMpzvY7h9afYDNtIZFXLi5JOqSKXMoG', 1, 0, NULL, '2025-08-30 03:54:18', '2025-08-30 08:44:20'),
(57, 'Bisrat sewbesew', '07886225123', 'bisrats1999@gmail.com', 'registrar', '$2y$10$Zmh8.GIx5y8/7gxUmZ7hROSIbba4X70FYkpkxVAwBrN5HxIKsDBbu', 1, 0, NULL, '2025-08-30 03:55:07', '2025-08-30 08:18:10'),
(58, 'Woinshet', '07932978367', 'm.woynie485@gmail.com', 'registrar', '$2y$10$ecgI1FhHY/l4KbqrVI8.0evgeOBhBYuHnZx707tHfeI63.nFvsZJm', 1, 0, NULL, '2025-08-30 03:57:28', NULL),
(59, 'Henok Yemane', '07506421367', 'hyemane76@yahoo.co.uk', 'registrar', '$2y$10$gqiamn56eLkkh2EQj7teN.unl6rq02XQ5EfoWj6D1u9EP.ohfv8lu', 1, 0, NULL, '2025-08-30 03:58:15', '2025-08-30 04:20:23'),
(60, 'Samia Ahmed', '07495532455', 'Samia_feoral1987@yahoo.co.uk', 'registrar', '$2y$10$xPH7.eVEhBHdl1f/EwAor.naINf/Wxe8uzQCB/3pUp0rFS0Q6djpa', 1, 0, NULL, '2025-08-30 03:59:16', '2025-08-30 08:46:48'),
(61, 'Yonatan kidanemariyam', '07828556674', 'abemelik36@gmail.com', 'registrar', '$2y$10$65S0febXWiHWfwd6NNou4OkhbBizCNgAmLjD.hVj2dvQTmEMqcA6.', 1, 0, NULL, '2025-08-30 07:51:10', '2025-08-30 08:18:37'),
(62, 'MR MICHAEL TESFAYE', '447476336051', 'tesmic735@gmail.comt', 'registrar', '$2y$10$sd33unQHp4mKU67S8PA8ROXfVh58rzTbzMod09n2K0rO9Z86KqJeC', 1, 0, NULL, '2025-08-30 07:51:26', '2025-08-30 07:52:44'),
(63, 'MR MICHAEL TESFAYE', '07476336051', 'mtesfaye735@gmail.com', 'registrar', '$2y$10$0I0.pTpGmkyMf9WrPGN5LO59lVP.hR6TMf4/qge/w1.krpSwGzz6e', 1, 0, NULL, '2025-08-30 07:51:47', NULL),
(64, 'Semane', '7930353681', 'semaneamare@gmail.com', 'registrar', '$2y$10$grYMsG71nOBOTZrcDeyNceZIWTjfKt3UM926U22jt.LUGiQriVXBS', 1, 0, NULL, '2025-08-30 07:52:09', '2025-08-30 08:10:04'),
(65, 'Dereje Argaw Woldeselassie', '07383333847', 'argawdereje@yahoo.it', 'registrar', '$2y$10$5ymq1mHG6PL9h0TBLKxMe.QDB4AcB9yFfwZ.V1fUDDquxVtIctHY2', 1, 0, NULL, '2025-08-30 08:17:47', '2025-08-30 08:30:44'),
(66, 'Henok', '07411002386', 'hd2072054@gmail.com', 'registrar', '$2y$10$zm14LKPj.V56bBDVa1WvkuT0H1vv.900pAKWUextuZ3pYxjWtZxeC', 1, 0, NULL, '2025-08-30 08:18:03', '2025-08-30 08:35:15'),
(67, 'Rarity kibrom', '07742059068', 'dsami7496@gmail.com', 'registrar', '$2y$10$0kpwpcKL2hoEFg5VWUQsy.CRdM1gS3NI9uNl1gunR5hnqjJLqIwva', 1, 0, NULL, '2025-08-30 08:18:19', '2025-08-30 08:19:26'),
(68, 'Milena Birhane', '07365957727', 'milena@gmail.com', 'registrar', '$2y$10$7HmHaoknw7Tuf8bpXqlVfeGwSpxJ6Nh.1ZDosPfl6TdHJSo.rv1Pa', 1, 0, NULL, '2025-08-30 08:20:30', '2025-08-30 08:27:19'),
(69, 'Belen Wondimagegne', '07770422453', 'belenmekon3n@gmail.com', 'registrar', '$2y$10$9NT3tp8PGdXV55r7K1.AeeG2ykPuOO6jTuiloq67t0.t52ExrN/vW', 1, 0, NULL, '2025-08-30 08:23:05', '2025-08-30 08:39:23'),
(70, 'Tesfaye Hailemichael', '07454767141', 'tesamen12@gmail.com', 'registrar', '$2y$10$9808FK8oKMCJtptgmGJohOQqMc9FWJR3dBSZrw1awfNj8EQfoJtoa', 1, 0, NULL, '2025-08-30 08:29:46', '2025-08-30 08:31:11'),
(71, 'Meron Selish', '07939875169', 'merinati21@gmail.com', 'registrar', '$2y$10$UhixZbE67CzfHj9Kzhs9z.daNDFGd4dhVvsXfS3KQjPZG0FHPCNI.', 1, 0, NULL, '2025-08-30 08:33:14', '2025-08-30 08:35:16'),
(72, 'Kidus Feresenbet', '07932739010', 'kidus143kidane@gmail.com', 'registrar', '$2y$10$SLAsJbN1hdsGHFm9llBA6O1VoMDf/TFcloVhsdIHAmQcfpFLcapvG', 1, 0, NULL, '2025-08-30 08:33:46', '2025-08-30 08:35:48'),
(73, 'Yohanis aklilu', '07949146267', 'yohanisaklilu5@gmail.com', 'registrar', '$2y$10$BuLoDSJvxQn/063M5LGCS.qUNOX48CfoiCW995k4wh3T2kBrgywDy', 1, 0, NULL, '2025-08-30 08:36:12', '2025-08-30 08:38:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_blocklist`
--

CREATE TABLE `user_blocklist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blocked_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `pair_min_user_id` int(11) NOT NULL,
  `pair_max_user_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `attachment_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `client_uuid` char(36) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `sender_deleted_at` datetime DEFAULT NULL,
  `recipient_deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ajax_request_log`
--
ALTER TABLE `ajax_request_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`request_uuid`),
  ADD KEY `idx_user_time` (`user_id`,`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`,`created_at`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`);

--
-- Indexes for table `counters`
--
ALTER TABLE `counters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_amount_tracking`
--
ALTER TABLE `custom_amount_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `donor_unique` (`donor_id`),
  ADD KEY `remaining_amount_idx` (`remaining_amount`);

--
-- Indexes for table `donation_packages`
--
ALTER TABLE `donation_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `floor_area_allocations`
--
ALTER TABLE `floor_area_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor_idx` (`donor_id`),
  ADD KEY `package_idx` (`package_id`),
  ADD KEY `status_idx` (`status`);

--
-- Indexes for table `floor_grid_cells`
--
ALTER TABLE `floor_grid_cells`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cell_id` (`cell_id`),
  ADD KEY `status_idx` (`status`),
  ADD KEY `rectangle_idx` (`rectangle_id`),
  ADD KEY `cell_type_idx` (`cell_type`),
  ADD KEY `package_idx` (`package_id`),
  ADD KEY `pledge_idx` (`pledge_id`),
  ADD KEY `payment_idx` (`payment_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message` (`message_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_received_by` (`received_by_user_id`),
  ADD KEY `idx_payments_pledge_created` (`created_at`),
  ADD KEY `idx_payments_created_at` (`created_at`),
  ADD KEY `idx_payments_method_created` (`method`,`created_at`),
  ADD KEY `fk_payments_package` (`package_id`),
  ADD KEY `idx_status_received` (`status`,`received_at`),
  ADD KEY `idx_status_created` (`status`,`created_at`);

--
-- Indexes for table `pledges`
--
ALTER TABLE `pledges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pledges_client_uuid` (`client_uuid`),
  ADD KEY `fk_pledges_approved_by` (`approved_by_user_id`),
  ADD KEY `idx_pledges_status_created` (`status`,`created_at`),
  ADD KEY `idx_pledges_source_status_created` (`source`,`status`,`created_at`),
  ADD KEY `idx_pledges_approved_at` (`approved_at`),
  ADD KEY `idx_pledges_created_by` (`created_by_user_id`),
  ADD KEY `idx_pledges_anonymous` (`anonymous`),
  ADD KEY `idx_pledges_status_changed` (`status_changed_at`),
  ADD KEY `fk_pledges_package` (`package_id`),
  ADD KEY `idx_status_approved` (`status`,`approved_at`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_user_status` (`created_by_user_id`,`status`);

--
-- Indexes for table `projector_commands`
--
ALTER TABLE `projector_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_executed_created` (`executed`,`created_at`);

--
-- Indexes for table `projector_footer`
--
ALTER TABLE `projector_footer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_visibility` (`is_visible`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_time` (`ip_address`,`submission_time`),
  ADD KEY `idx_phone_time` (`phone_number`,`submission_time`),
  ADD KEY `idx_submission_time` (`submission_time`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `registrar_applications`
--
ALTER TABLE `registrar_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_registrar_email` (`email`),
  ADD UNIQUE KEY `uq_registrar_phone` (`phone`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `fk_approved_by` (`approved_by_user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_settings_id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_phone` (`phone`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_block_pair` (`user_id`,`blocked_user_id`),
  ADD KEY `fk_block_blocked` (`blocked_user_id`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dm_client_uuid` (`client_uuid`),
  ADD KEY `idx_dm_recipient_unread` (`recipient_user_id`,`read_at`),
  ADD KEY `idx_dm_sender_created` (`sender_user_id`,`created_at`),
  ADD KEY `idx_dm_pair_created` (`pair_min_user_id`,`pair_max_user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ajax_request_log`
--
ALTER TABLE `ajax_request_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `custom_amount_tracking`
--
ALTER TABLE `custom_amount_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donation_packages`
--
ALTER TABLE `donation_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `floor_area_allocations`
--
ALTER TABLE `floor_area_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `floor_grid_cells`
--
ALTER TABLE `floor_grid_cells`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3592;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `pledges`
--
ALTER TABLE `pledges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `projector_commands`
--
ALTER TABLE `projector_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projector_footer`
--
ALTER TABLE `projector_footer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `registrar_applications`
--
ALTER TABLE `registrar_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ajax_request_log`
--
ALTER TABLE `ajax_request_log`
  ADD CONSTRAINT `ajax_request_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_attach_message` FOREIGN KEY (`message_id`) REFERENCES `user_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_package` FOREIGN KEY (`package_id`) REFERENCES `donation_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pledges`
--
ALTER TABLE `pledges`
  ADD CONSTRAINT `fk_pledges_approved_by` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledges_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledges_package` FOREIGN KEY (`package_id`) REFERENCES `donation_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `registrar_applications`
--
ALTER TABLE `registrar_applications`
  ADD CONSTRAINT `fk_registrar_app_approved_by` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  ADD CONSTRAINT `fk_block_blocked` FOREIGN KEY (`blocked_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_block_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `fk_dm_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Database: `assignment_tracker`
--
CREATE DATABASE IF NOT EXISTS `assignment_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `assignment_tracker`;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_criteria`
--

CREATE TABLE `assessment_criteria` (
  `criteria_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `criteria_text` varchar(255) NOT NULL,
  `status` enum('Not Started','In Progress','Completed') DEFAULT 'Not Started',
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_criteria`
--

INSERT INTO `assessment_criteria` (`criteria_id`, `assignment_id`, `criteria_text`, `status`, `is_completed`, `completed_at`, `notes`) VALUES
(26, 3, 'AC 11.1: Distinguish between hardware and software components of a computer system and give examples of each.', 'Not Started', 1, '2025-07-02 18:24:32', NULL),
(27, 3, 'AC 11.2: Explain the standardisation of hardware and software.', 'Not Started', 1, '2025-07-02 18:24:35', NULL),
(28, 3, 'AC 11.3: Describe and explain the purpose and use of the control unit, arithmetic and logic unit (ALU), main memory and auxiliary storage.', 'Not Started', 1, '2025-07-02 18:24:39', NULL),
(29, 3, 'AC 21.1: Describe methods of data capture and identify appropriate contexts for their use.', 'Not Started', 1, '2025-07-02 18:24:40', NULL),
(30, 3, 'AC 21.2: Explain possible sources and types of error that can occur during data capture.', 'Not Started', 1, '2025-07-02 18:24:42', NULL),
(31, 3, 'AC 21.3: Distinguish between the accuracy and validity of data.', 'Not Started', 1, '2025-07-02 18:24:44', NULL),
(32, 3, 'AC 21.4: Describe a range of validation and verification methods, identifying their purpose and use.', 'Not Started', 1, '2025-07-02 18:24:45', NULL),
(33, 3, 'AC 31.1: Identify common storage devices and explain their use.', 'Not Started', 1, '2025-07-02 18:24:47', NULL),
(34, 3, 'AC 31.2: Describe the characteristics of current storage technologies.', 'Not Started', 1, '2025-07-02 18:24:51', NULL),
(35, 3, 'AC 31.3: Explain the need to back up and archive data.', 'Not Started', 1, '2025-07-02 18:26:56', NULL),
(36, 3, 'AC 41.1: Explain the characteristics of common devices and communication devices and identify appropriate contexts for their use.', 'Not Started', 1, '2025-07-02 18:26:57', NULL),
(37, 3, 'AC 51.1: Explain the appropriate methods of electronic data exchange, e.g. telephone, fibre optic cable, satellite, EDI, email, Internet etc, for a given situation.', 'Not Started', 1, '2025-07-02 18:27:01', NULL),
(38, 4, 'AC 11.1: Manipulate and simplify expressions which include negative and fractional indices.', 'Not Started', 1, '2025-07-02 19:10:48', NULL),
(39, 4, 'AC 21.1: Write a surd in its simplest form.', 'Not Started', 1, '2025-07-02 19:10:52', NULL),
(40, 4, 'AC 21.2: Simplify a surd by rationalising the denominator.', 'Not Started', 0, NULL, NULL),
(41, 4, 'AC 31.1: Form and/or solve a linear equation.', 'Not Started', 0, NULL, NULL),
(42, 4, 'AC 31.2: Complete the square.', 'Not Started', 0, NULL, NULL),
(43, 4, 'AC 31.3: Form and/or solve a quadratic equation by factorising or by the quadratic formula.', 'Not Started', 0, NULL, NULL),
(44, 4, 'AC 31.4: Form and/or solve linear simultaneous equations or non-linear simultaneous equations.', 'Not Started', 0, NULL, NULL),
(45, 4, 'AC 31.5: Simplify an algebraic fraction, including after addition, subtraction or multiplication.', 'Not Started', 0, NULL, NULL),
(46, 4, 'AC 41.1: Use function notation.', 'Not Started', 0, NULL, NULL),
(47, 4, 'AC 41.2: Sketch the graph of a quadratic function or a cubic function (with zero number term).', 'Not Started', 0, NULL, NULL),
(48, 4, 'AC 41.3: Find the domain of a function.', 'Not Started', 0, NULL, NULL),
(49, 4, 'AC 41.4: Find the range of a function.', 'Not Started', 0, NULL, NULL),
(50, 4, 'AC 41.5: Find composite functions.', 'Not Started', 0, NULL, NULL),
(51, 4, 'AC 41.6: Find the inverse function of a function.', 'Not Started', 0, NULL, NULL),
(52, 5, 'AC 11.1: Explain appropriate database terminology.', 'Not Started', 1, '2025-07-03 17:32:07', NULL),
(53, 5, 'AC 11.2: Analyse factors that contribute to the effectiveness of a database system.', 'Not Started', 1, '2025-07-03 17:32:13', NULL),
(54, 5, 'AC 41.2: Explain the issues that influence interface design.', 'Not Started', 1, '2025-07-03 17:32:13', NULL),
(55, 5, 'AC 21.1: Use a database management system to create a relational database (a minimum of four tables) to manage data.', 'Not Started', 1, '2025-07-03 17:32:14', NULL),
(56, 5, 'AC 31.1: Produce printed reports based on a query.', 'Not Started', 1, '2025-07-03 17:32:16', NULL),
(57, 5, 'AC 31.2: Use a range of query features to extract and present information from the database, e.g. sorting, subtotal.', 'Not Started', 1, '2025-07-03 17:32:16', NULL),
(58, 5, 'AC 31.3: Apply compound queries that use mathematical and Boolean operators.', 'Not Started', 1, '2025-07-03 17:32:17', NULL),
(59, 5, 'AC 41.1: Create an interface to extract, add and update information held within the database.', 'Not Started', 1, '2025-07-03 17:32:17', NULL),
(60, 5, 'AC 51.1: Carry out ongoing review and modify accordingly.', 'Not Started', 1, '2025-07-03 17:32:18', NULL),
(61, 5, 'AC 51.2: Record and justify changes.', 'Not Started', 1, '2025-07-03 17:32:19', NULL),
(62, 5, 'AC 51.3: Evaluate the created database.', 'Not Started', 1, '2025-07-03 17:32:20', NULL),
(63, 6, 'AC 11.1: Solve a problem involving midpoint, gradient or equation of a line joining two points, or an equation of their perpendicular bisector.', 'Not Started', 1, '2025-07-03 17:32:47', NULL),
(64, 6, 'AC 21.1: Differentiate simple functions (eg, ax n, e x, ln (x), sin (x), cos (x), etc).', 'Not Started', 1, '2025-07-03 17:32:47', NULL),
(65, 6, 'AC 21.2: Apply differentiation in terms of the gradient of a curve or the rate of change of a variable.', 'Not Started', 1, '2025-07-03 17:32:48', NULL),
(66, 6, 'AC 21.3: Solve a problem involving the tangent or the normal to a curve at a particular point.', 'Not Started', 1, '2025-07-03 17:32:48', NULL),
(67, 6, 'AC 31.1: Integrate simple functions (ax n, e x, sin (x),cos (x), etc).', 'Not Started', 1, '2025-07-03 17:32:49', NULL),
(68, 6, 'AC 31.2: Perform a definite integral calculation.', 'Not Started', 1, '2025-07-03 17:32:49', NULL),
(69, 6, 'AC 31.3: Find the area enclosed by a curve and the x axis or between two curves.', 'Not Started', 1, '2025-07-03 17:32:50', NULL),
(70, 7, 'AC 11.1: Outline what \"artificial intelligence\" means.', 'Not Started', 1, '2025-07-03 17:33:44', NULL),
(71, 7, 'AC 11.2: Explain the differences between: (a) artificial narrow intelligence (b) artificial general intelligence (c) artificial super intelligence.', 'Not Started', 1, '2025-07-03 17:33:46', NULL),
(72, 7, 'AC 11.3: Discuss the challenges in achieving artificial intelligence.', 'Not Started', 1, '2025-07-03 17:33:48', NULL),
(73, 7, 'AC 11.4: Analyse the successes and failures of artificial intelligence.', 'Not Started', 1, '2025-07-03 17:33:49', NULL),
(74, 7, 'AC 21.1: Outline what \"machine learning\" means.', 'Not Started', 1, '2025-07-03 17:33:50', NULL),
(75, 7, 'AC 21.2: Explain the following types of machine learning: (a) supervised (b) unsupervised (c) reinforcement', 'Not Started', 0, NULL, NULL),
(76, 7, 'AC 21.3: Investigate the uses and limitations of machine learning.', 'Not Started', 0, NULL, NULL),
(77, 7, 'AC 21.4: Examine the difference between artificial intelligence and machine learning.', 'Not Started', 0, NULL, NULL),
(78, 7, 'AC 31.1: Outline what \"deep learning\" means.', 'Not Started', 0, NULL, NULL),
(79, 7, 'AC 31.2: Examine deep learning architecture.', 'Not Started', 0, NULL, NULL),
(80, 7, 'AC 31.3: Discuss what deep learning can and cannot currently do.', 'Not Started', 0, NULL, NULL),
(81, 7, 'AC 31.4: Investigate three current areas of research in deep learning.', 'Not Started', 0, NULL, NULL),
(82, 8, 'AC 11.1: Find both the first and second derivative of simple functions (ax n, e x, sin (x), cos (x), etc)', 'Not Started', 1, '2025-07-03 20:49:37', NULL),
(83, 8, 'AC 11.2: Find the location of stationary points on a curve and determine their type.', 'Not Started', 1, '2025-07-03 20:49:39', NULL),
(84, 8, 'AC 11.3: Solve an optimisation problem.', 'Not Started', 1, '2025-07-03 20:49:41', NULL),
(85, 8, 'AC 21.1: Solve a problem involving differentiate using the chain rule.', 'Not Started', 1, '2025-07-03 20:55:47', NULL),
(86, 8, 'AC 21.2: Solve a problem involving differentiate using the product rule.', 'Not Started', 1, '2025-07-03 20:55:49', NULL),
(87, 8, 'AC 21.3: Solve a problem involving differentiate using the quotient rule.', 'Not Started', 1, '2025-07-03 20:55:50', NULL),
(88, 8, 'AC 21.4: Solve a problem involving a related rate of change.', 'Not Started', 1, '2025-07-03 20:55:51', NULL),
(92, 10, 'AC 11.1: Explain cyber security.', 'Not Started', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `assignment_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) NOT NULL DEFAULT 1,
  `deadline` date NOT NULL,
  `status` enum('Active','Completed','Submitted') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`assignment_id`, `title`, `description`, `credits`, `deadline`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Unit 5: Components of Computer Systems', '', 6, '2025-01-18', 'Submitted', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'Unit 6: Algebra and Functions', '', 3, '2025-01-30', '', '2025-07-02 15:57:52', '2025-07-02 15:57:52'),
(5, 'Unit 8: Database Development', '', 6, '2025-03-17', 'Submitted', '2025-07-02 15:58:21', '2025-07-03 15:32:28'),
(6, 'Unit 9: Calculus', '', 3, '2025-04-07', 'Completed', '2025-07-02 16:02:09', '2025-07-03 15:32:50'),
(7, 'Unit 10: AI, Machine Learning and Deep Learning', '', 3, '2025-05-12', '', '2025-07-02 16:12:18', '2025-07-02 16:12:18'),
(8, 'Unit 16: Further Differentiation', '', 3, '2025-07-07', 'Submitted', '2025-07-02 16:23:57', '2025-07-03 20:11:02'),
(10, 'Unit 7: Cyber Security', NULL, 3, '2025-07-07', 'Active', '2025-07-04 00:18:46', '2025-07-04 00:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `final_submission_checklist`
--

CREATE TABLE `final_submission_checklist` (
  `checklist_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `check_item` varchar(150) NOT NULL,
  `is_checked` tinyint(1) DEFAULT 0,
  `checked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  ADD PRIMARY KEY (`criteria_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`assignment_id`);

--
-- Indexes for table `final_submission_checklist`
--
ALTER TABLE `final_submission_checklist`
  ADD PRIMARY KEY (`checklist_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  MODIFY `criteria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `final_submission_checklist`
--
ALTER TABLE `final_submission_checklist`
  MODIFY `checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  ADD CONSTRAINT `assessment_criteria_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE;

--
-- Constraints for table `final_submission_checklist`
--
ALTER TABLE `final_submission_checklist`
  ADD CONSTRAINT `final_submission_checklist_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`assignment_id`) ON DELETE CASCADE;
--
-- Database: `fundraising`
--
CREATE DATABASE IF NOT EXISTS `fundraising` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fundraising`;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `action` varchar(50) NOT NULL,
  `before_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_json`)),
  `after_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_json`)),
  `ip_address` varbinary(16) DEFAULT NULL,
  `source` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `before_json`, `after_json`, `ip_address`, `source`, `created_at`) VALUES
(1, NULL, 'pledge', 2, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Test Public Donor\",\"status\":\"pending\"}', NULL, 'public', '2025-08-21 20:03:35'),
(2, NULL, 'pledge', 5, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Test Fix Donor\",\"status\":\"pending\"}', NULL, 'public', '2025-08-21 20:14:28'),
(3, NULL, 'pledge', 6, 'create_pending', NULL, '{\"amount\":100,\"type\":\"pledge\",\"anonymous\":0,\"donor\":\"Final Test Donor\",\"status\":\"pending\"}', NULL, 'public', '2025-08-21 20:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `counters`
--

CREATE TABLE `counters` (
  `id` tinyint(4) NOT NULL,
  `paid_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pledged_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `recalc_needed` tinyint(1) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counters`
--

INSERT INTO `counters` (`id`, `paid_total`, `pledged_total`, `grand_total`, `version`, `recalc_needed`, `last_updated`) VALUES
(1, 0.00, 0.00, 0.00, 1, 0, '2025-08-14 16:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `donation_packages`
--

CREATE TABLE `donation_packages` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `sqm_meters` decimal(8,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_packages`
--

INSERT INTO `donation_packages` (`id`, `label`, `sqm_meters`, `price`, `active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, '1 m²', 1.00, 400.00, 1, 1, '2025-08-13 05:31:09', '2025-08-13 21:22:39'),
(2, '1/2 m²', 0.50, 200.00, 1, 2, '2025-08-13 05:31:09', '2025-08-13 05:31:09'),
(3, '1/4 m²', 0.25, 100.00, 1, 3, '2025-08-13 05:31:09', '2025-08-13 05:31:09'),
(4, 'Custom', 0.00, 0.00, 1, 4, '2025-08-13 05:31:09', '2025-08-13 05:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` int(10) UNSIGNED NOT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(30) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','card','bank','other') NOT NULL DEFAULT 'cash',
  `package_id` int(11) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','voided') NOT NULL DEFAULT 'pending',
  `received_by_user_id` int(11) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

CREATE TABLE `pledges` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(30) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `source` enum('self','volunteer') NOT NULL DEFAULT 'volunteer',
  `anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('pledge','paid') NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `client_uuid` char(36) DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `status_changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `custom_allocation_status` enum('pending','allocated','partial') DEFAULT 'pending' COMMENT 'Status of custom amount allocation',
  `allocated_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount allocated to floor map cells',
  `remaining_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Remaining amount pending allocation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pledges`
--

INSERT INTO `pledges` (`id`, `donor_name`, `donor_phone`, `donor_email`, `package_id`, `source`, `anonymous`, `amount`, `type`, `status`, `notes`, `client_uuid`, `ip_address`, `user_agent`, `proof_path`, `created_by_user_id`, `approved_by_user_id`, `created_at`, `approved_at`, `status_changed_at`, `custom_allocation_status`, `allocated_amount`, `remaining_amount`) VALUES
(1, 'Test Self Donor', '07123456789', 'test@example.com', 3, 'self', 0, 100.00, 'pledge', 'pending', 'Test self-pledged donation', '8d5584f1-7ec4-11f0-b91e-e86a64fe676c', NULL, NULL, NULL, NULL, NULL, '2025-08-21 19:24:05', NULL, '2025-08-21 19:24:05', 'pending', 0.00, 0.00),
(2, 'Test Public Donor', '07987654321', NULL, 3, 'self', 0, 100.00, 'pledge', 'pending', '', 'test-uuid-68a77b9748ec8', NULL, NULL, NULL, NULL, NULL, '2025-08-21 20:03:35', NULL, '2025-08-21 20:03:35', 'pending', 0.00, 0.00),
(5, 'Test Fix Donor', '07555123456', NULL, 3, 'self', 0, 100.00, 'pledge', 'pending', '', '550e8400-e29b-41d4-a716-446655440000', NULL, NULL, NULL, NULL, NULL, '2025-08-21 20:14:28', NULL, '2025-08-21 20:14:28', 'pending', 0.00, 0.00),
(6, 'Final Test Donor', '07666123456', NULL, 3, 'self', 0, 100.00, 'pledge', 'pending', '', 'final-test-68a77f26e1cff', NULL, NULL, NULL, NULL, NULL, '2025-08-21 20:18:46', NULL, '2025-08-21 20:18:46', 'pending', 0.00, 0.00);

--
-- Triggers `pledges`
--
DELIMITER $$
CREATE TRIGGER `trg_pledges_status_changed` BEFORE UPDATE ON `pledges` FOR EACH ROW BEGIN
  IF NEW.status <> OLD.status THEN
    SET NEW.status_changed_at = CURRENT_TIMESTAMP;
    IF NEW.status = 'approved' AND OLD.status <> 'approved' THEN
      SET NEW.approved_at = IFNULL(NEW.approved_at, CURRENT_TIMESTAMP);
    END IF;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `projector_commands`
--

CREATE TABLE `projector_commands` (
  `id` int(11) NOT NULL,
  `command_type` enum('announcement','footer_message','effect','setting') NOT NULL,
  `command_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`command_data`)),
  `created_by_user_id` int(11) DEFAULT NULL,
  `executed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projector_commands`
--

INSERT INTO `projector_commands` (`id`, `command_type`, `command_data`, `created_by_user_id`, `executed`, `created_at`) VALUES
(1, 'setting', '{\"command\":\"updateSettings\",\"data\":{\"refreshRate\":10,\"displayTheme\":\"celebration\",\"showTicker\":true,\"showProgress\":true,\"showQR\":true,\"showClock\":true},\"timestamp\":1755141337221}', 1, 1, '2025-08-14 03:15:37'),
(2, 'setting', '{\"command\":\"updateSettings\",\"data\":{\"refreshRate\":10,\"displayTheme\":\"celebration\",\"showTicker\":true,\"showProgress\":false,\"showQR\":true,\"showClock\":true},\"timestamp\":1755141369145}', 1, 1, '2025-08-14 03:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `projector_footer`
--

CREATE TABLE `projector_footer` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projector_footer`
--

INSERT INTO `projector_footer` (`id`, `message`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 'ለመስጠት እጃቹህ የተዘረጋ በሙሉ ጻድቁ አባታችን አቡነ ተክለሃይማኖት በበረከት ይጎብኟቹህ!', 1, '2025-08-14 03:24:51', '2025-08-14 15:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` tinyint(4) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL DEFAULT 100000.00,
  `currency_code` char(3) NOT NULL DEFAULT 'GBP',
  `display_token` char(64) NOT NULL,
  `display_token_expires_at` datetime DEFAULT NULL,
  `projector_names_mode` enum('full','first_initial','off') NOT NULL DEFAULT 'full',
  `refresh_seconds` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `projector_display_mode` varchar(10) DEFAULT 'amount'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `target_amount`, `currency_code`, `display_token`, `display_token_expires_at`, `projector_names_mode`, `refresh_seconds`, `version`, `created_at`, `updated_at`, `projector_display_mode`) VALUES
(1, 30000.00, 'GBP', '7856996902e5612296dd487a8b8a85564407222cbfd8c032b06eb641249505c3', NULL, 'full', 4, 1, '2025-08-11 21:40:50', '2025-08-14 11:02:41', 'sqm');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','registrar') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `role`, `password_hash`, `active`, `login_attempts`, `locked_until`, `created_at`, `last_login_at`) VALUES
(1, 'Abel Demssiee', '07360436171', 'abelgoytom77@gmail.com', 'admin', '$2y$10$BoI2Vo56X9.NRbZ0RylNW.iR7wf.t60fNBfYz0jDgHWFxrCtCc45m', 1, 0, NULL, '2025-08-12 00:42:00', '2025-08-13 22:26:17'),
(2, 'Maeruf Nasir', '07438 324115', 'marufnasirrrr@gmail.com', 'registrar', '$2y$10$Wz63j3l2uEZZC1P8sNalUuVfCjWHWm4dKGYAMkhj.I36vypvdbS06', 1, 0, NULL, '2025-08-12 18:08:21', '2025-08-13 23:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_blocklist`
--

CREATE TABLE `user_blocklist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blocked_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_messages`
--

CREATE TABLE `user_messages` (
  `id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `pair_min_user_id` int(11) NOT NULL,
  `pair_max_user_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `attachment_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `client_uuid` char(36) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `sender_deleted_at` datetime DEFAULT NULL,
  `recipient_deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`,`created_at`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`);

--
-- Indexes for table `counters`
--
ALTER TABLE `counters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donation_packages`
--
ALTER TABLE `donation_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message` (`message_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_received_by` (`received_by_user_id`),
  ADD KEY `idx_payments_pledge_created` (`created_at`),
  ADD KEY `idx_payments_created_at` (`created_at`),
  ADD KEY `idx_payments_method_created` (`method`,`created_at`),
  ADD KEY `fk_payments_package` (`package_id`),
  ADD KEY `idx_status_received` (`status`,`received_at`),
  ADD KEY `idx_status_created` (`status`,`created_at`);

--
-- Indexes for table `pledges`
--
ALTER TABLE `pledges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pledges_client_uuid` (`client_uuid`),
  ADD KEY `fk_pledges_approved_by` (`approved_by_user_id`),
  ADD KEY `idx_pledges_status_created` (`status`,`created_at`),
  ADD KEY `idx_pledges_source_status_created` (`source`,`status`,`created_at`),
  ADD KEY `idx_pledges_approved_at` (`approved_at`),
  ADD KEY `idx_pledges_created_by` (`created_by_user_id`),
  ADD KEY `idx_pledges_anonymous` (`anonymous`),
  ADD KEY `idx_pledges_status_changed` (`status_changed_at`),
  ADD KEY `fk_pledges_package` (`package_id`),
  ADD KEY `idx_status_approved` (`status`,`approved_at`),
  ADD KEY `idx_status_created` (`status`,`created_at`),
  ADD KEY `idx_user_status` (`created_by_user_id`,`status`);

--
-- Indexes for table `projector_commands`
--
ALTER TABLE `projector_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_executed_created` (`executed`,`created_at`);

--
-- Indexes for table `projector_footer`
--
ALTER TABLE `projector_footer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_visibility` (`is_visible`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_settings_id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_phone` (`phone`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_block_pair` (`user_id`,`blocked_user_id`),
  ADD KEY `fk_block_blocked` (`blocked_user_id`);

--
-- Indexes for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dm_client_uuid` (`client_uuid`),
  ADD KEY `idx_dm_recipient_unread` (`recipient_user_id`,`read_at`),
  ADD KEY `idx_dm_sender_created` (`sender_user_id`,`created_at`),
  ADD KEY `idx_dm_pair_created` (`pair_min_user_id`,`pair_max_user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donation_packages`
--
ALTER TABLE `donation_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pledges`
--
ALTER TABLE `pledges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projector_commands`
--
ALTER TABLE `projector_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projector_footer`
--
ALTER TABLE `projector_footer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_messages`
--
ALTER TABLE `user_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_attach_message` FOREIGN KEY (`message_id`) REFERENCES `user_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_package` FOREIGN KEY (`package_id`) REFERENCES `donation_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pledges`
--
ALTER TABLE `pledges`
  ADD CONSTRAINT `fk_pledges_approved_by` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledges_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pledges_package` FOREIGN KEY (`package_id`) REFERENCES `donation_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_blocklist`
--
ALTER TABLE `user_blocklist`
  ADD CONSTRAINT `fk_block_blocked` FOREIGN KEY (`blocked_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_block_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_messages`
--
ALTER TABLE `user_messages`
  ADD CONSTRAINT `fk_dm_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Database: `gcsedb`
--
CREATE DATABASE IF NOT EXISTS `gcsedb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gcsedb`;
--
-- Database: `gcse_tracker`
--
CREATE DATABASE IF NOT EXISTS `gcse_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gcse_tracker`;

-- --------------------------------------------------------

--
-- Table structure for table `access_assignments`
--

CREATE TABLE `access_assignments` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `unit_overview` text DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `overview` text DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `guidance` text DEFAULT NULL,
  `word_limit` int(11) DEFAULT NULL,
  `credits` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_criteria` int(11) DEFAULT 0,
  `completed_criteria` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `estimated_hours` int(11) DEFAULT NULL,
  `actual_hours` int(11) DEFAULT 0,
  `submitted_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_assignments`
--

INSERT INTO `access_assignments` (`id`, `unit_id`, `unit_overview`, `title`, `overview`, `question_text`, `guidance`, `word_limit`, `credits`, `due_date`, `description`, `status`, `created_at`, `updated_at`, `total_criteria`, `completed_criteria`, `progress_percentage`, `priority`, `estimated_hours`, `actual_hours`, `submitted_date`) VALUES
(1, 10, 'Write a journal article that shows your understanding of AI, Machine Learning, and Deep Learning, including analysis of three current areas in deep learning.', 'AI, Machine Learning and Deep Learning', '<p><strong>The article should explain the concepts, compare types of AI and ML, discuss benefits and risks, and present three current research areas in Deep Learning. Stick to a journal-style format with Harvard references.</strong></p>', '<p>What does **Artificial Intelligence (AI)** mean? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n2. Explain the differences between: \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Artificial Narrow Intelligence \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Artificial General Intelligence \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Artificial Super Intelligence \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n3. What are the **challenges** in achieving AI? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n4. Discuss **successes and failures** in AI. \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n5. What is **Machine Learning (ML)**? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n6. Describe types of ML: \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Supervised \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Unsupervised \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n - Reinforcement \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n7. What are the **uses and limitations** of ML? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n8. Compare **Machine Learning vs AI**. \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n9. What is **Deep Learning**? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n10. What can/can\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\'t Deep Learning currently do? \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n11. Explore **Deep Learning architecture**. \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\n12. Research **3 current areas** in Deep Learning.</p>', NULL, 2000, 3, '2025-04-10', 'Understanding and implementing AI, ML, and DL concepts', 'in_progress', '2025-04-01 16:51:13', '2025-04-01 21:48:44', 12, 7, 58.00, 'high', 20, 0, NULL),
(10, NULL, NULL, 'The Safe and Ethical Use of Generative Artificial Intelligence', NULL, NULL, NULL, NULL, 3, '2025-04-20', NULL, 'not_started', '2025-04-01 21:09:10', '2025-04-01 21:09:10', 0, 0, 0.00, 'high', 0, 0, NULL),
(11, NULL, NULL, 'Software Development', NULL, NULL, NULL, NULL, 6, '2025-05-12', NULL, 'not_started', '2025-04-01 21:09:10', '2025-04-01 21:09:10', 0, 0, 0.00, 'high', 48, 0, NULL),
(12, NULL, NULL, 'Study Skills Portfolio Building', NULL, NULL, NULL, NULL, 0, '2025-06-16', NULL, 'not_started', '2025-04-01 21:09:10', '2025-04-01 21:09:10', 0, 0, 0.00, 'high', 16, 0, NULL),
(13, NULL, NULL, 'Programming Constructs', NULL, NULL, NULL, NULL, 6, '2025-06-22', NULL, 'not_started', '2025-04-01 21:09:10', '2025-04-01 21:09:10', 0, 0, 0.00, 'high', 48, 0, NULL),
(14, NULL, NULL, 'Web Page Design and Production', NULL, NULL, NULL, NULL, 3, '2025-06-20', NULL, 'not_started', '2025-04-01 21:09:10', '2025-04-01 21:09:10', 0, 0, 0.00, 'high', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `access_course_units`
--

CREATE TABLE `access_course_units` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(10) NOT NULL,
  `unit_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `is_graded` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_course_units`
--

INSERT INTO `access_course_units` (`id`, `unit_code`, `unit_name`, `description`, `credits`, `is_graded`, `created_at`, `updated_at`) VALUES
(1, 'U1', 'Preparing for Success', 'Unit 1: Preparing for Success', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(2, 'U2', 'Academic Writing Skills', 'Unit 2: Academic Writing Skills', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(3, 'U3', 'Reading & Note Making', 'Unit 3: Reading & Note Making', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(4, 'U4', 'Use of Information and Communication Technology', 'Unit 4: Use of Information and Communication Technology', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(5, 'U5', 'Components of Computer Systems', 'Unit 5: Components of Computer Systems', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(6, 'U6', 'Algebra and Functions', 'Unit 6: Algebra and Functions', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(7, 'U7', 'Cyber Security Fundamentals', 'Unit 7: Cyber Security Fundamentals', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(8, 'U8', 'Database Development', 'Unit 8: Database Development', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(9, 'U9', 'Calculus', 'Unit 9: Calculus', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(10, 'U10', 'AI, Machine Learning and Deep Learning', 'Unit 10: AI, Machine Learning and Deep Learning', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(11, 'U11', 'The Safe and Ethical Use of Generative Artificial Intelligence', 'Unit 11: The Safe and Ethical Use of Generative Artificial Intelligence', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(12, 'U12', 'Software Development', 'Unit 12: Software Development', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(13, 'U13', 'Pure Maths', 'Unit 13: Pure Maths', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(14, 'U14', 'Study Skills Portfolio Building', 'Unit 14: Study Skills Portfolio Building', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(15, 'U15', 'Programming Constructs', 'Unit 15: Programming Constructs', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(16, 'U16', 'Further Differentiation', 'Unit 16: Further Differentiation', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(17, 'U17', 'Web Page Design and Production', 'Unit 17: Web Page Design and Production', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33'),
(18, 'U18', 'Further Trigonometry', 'Unit 18: Further Trigonometry', 3, 1, '2025-04-01 16:53:33', '2025-04-01 16:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_criteria`
--

CREATE TABLE `assessment_criteria` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `criteria_code` varchar(10) NOT NULL,
  `criteria_text` text NOT NULL,
  `grade_required` enum('pass','merit','distinction') NOT NULL DEFAULT 'pass',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_criteria`
--

INSERT INTO `assessment_criteria` (`id`, `assignment_id`, `criteria_code`, `criteria_text`, `grade_required`, `created_at`) VALUES
(1, 1, 'AC 11.1', 'Outline what artificial intelligence means', 'distinction', '2025-04-01 17:58:50'),
(2, 1, 'AC 11.2', 'Explain the differences between: ANI, AGI, ASI', 'distinction', '2025-04-01 17:58:50'),
(3, 1, 'AC 11.3', 'Discuss the challenges in achieving artificial intelligence', 'distinction', '2025-04-01 17:58:50'),
(4, 1, 'AC 11.4', 'Analyse the successes and failures of artificial intelligence', 'distinction', '2025-04-01 17:58:50'),
(5, 1, 'AC 21.1', 'Outline what \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\"machine learning\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\" means', 'distinction', '2025-04-01 17:58:50'),
(6, 1, 'AC 21.2', 'Explain types of machine learning: supervised, unsupervised, reinforcement', 'distinction', '2025-04-01 17:58:50'),
(7, 1, 'AC 21.3', 'Investigate the uses and limitations of machine learning', 'distinction', '2025-04-01 17:58:50'),
(8, 1, 'AC 21.4', 'Examine the difference between AI and ML', 'distinction', '2025-04-01 17:58:50'),
(9, 1, 'AC 31.1', 'Outline what \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\"deep learning\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\" means', 'distinction', '2025-04-01 17:58:50'),
(10, 1, 'AC 31.2', 'Examine deep learning architecture', 'distinction', '2025-04-01 17:58:50'),
(11, 1, 'AC 31.3', 'Discuss what deep learning can and cannot currently do', 'distinction', '2025-04-01 17:58:50'),
(12, 1, 'AC 31.4', 'Investigate three current areas of research in deep learning', 'merit', '2025-04-01 17:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_criteria_progress`
--

CREATE TABLE `assignment_criteria_progress` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_criteria_progress`
--

INSERT INTO `assignment_criteria_progress` (`id`, `assignment_id`, `criteria_id`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES
(74, 1, 1, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(75, 1, 2, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(76, 1, 3, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(77, 1, 4, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(78, 1, 5, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(79, 1, 6, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44'),
(80, 1, 7, 'completed', NULL, '2025-04-01 21:48:44', '2025-04-01 20:48:44', '2025-04-01 20:48:44');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_guidance`
--

CREATE TABLE `assignment_guidance` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `guidance_text` text NOT NULL,
  `guidance_type` enum('general','research','reference','technical') NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_guidance`
--

INSERT INTO `assignment_guidance` (`id`, `assignment_id`, `guidance_text`, `guidance_type`, `created_at`) VALUES
(1, 1, 'Follow journal article style (refer to Academic Writing Skills unit, Section 1).', 'general', '2025-04-01 17:58:50'),
(2, 1, 'Include images/diagrams where helpful.', 'general', '2025-04-01 17:58:50'),
(3, 1, 'Use Harvard referencing throughout.', 'general', '2025-04-01 17:58:50'),
(4, 1, 'Must be your original work with proper citation.', 'general', '2025-04-01 17:58:50'),
(5, 1, 'Word Limit: 2,000 words max', 'general', '2025-04-01 17:58:50'),
(6, 1, 'Include reference list and bibliography.', 'general', '2025-04-01 17:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_progress`
--

CREATE TABLE `assignment_progress` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `criteria_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time_spent` decimal(4,2) DEFAULT NULL,
  `progress_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_progress_log`
--

CREATE TABLE `assignment_progress_log` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `action_type` enum('started','updated','completed','time_logged') NOT NULL,
  `description` text NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_progress_log`
--

INSERT INTO `assignment_progress_log` (`id`, `assignment_id`, `action_type`, `description`, `logged_at`) VALUES
(1, 1, 'updated', 'Updated criteria status to: Completed', '2025-04-01 18:05:33'),
(2, 1, 'updated', 'Updated criteria status to: Completed', '2025-04-01 18:05:43'),
(3, 1, 'updated', 'Updated criteria status to: Not_started', '2025-04-01 18:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_resources`
--

CREATE TABLE `assignment_resources` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('word_doc','pdf','powerpoint','excel','image','link','other') NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_extension` varchar(10) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_required` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment_resources`
--

INSERT INTO `assignment_resources` (`id`, `assignment_id`, `title`, `type`, `file_name`, `file_extension`, `file_size`, `file_path`, `upload_date`, `last_modified`, `is_required`, `download_count`, `mime_type`, `description`) VALUES
(1, 1, 'Assignment Brief - AI and Machine Learning', 'word_doc', 'AI_ML_Assignment_Brief.docx', 'docx', 245000, 'uploads/assignments/unit11/AI_ML_Assignment_Brief.docx', '2025-04-01 16:37:41', '2025-04-01 16:37:41', 1, 0, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Official assignment brief document with all requirements and marking criteria'),
(2, 1, 'Research Template', 'word_doc', 'AI_ML_Research_Template.docx', 'docx', 125000, 'uploads/assignments/unit11/AI_ML_Research_Template.docx', '2025-04-01 16:37:41', '2025-04-01 16:37:41', 1, 0, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Template for organizing research findings and analysis');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `color`, `display_order`, `created_at`) VALUES
(1, 'Spiritual Life', 'fas fa-pray', '#cdaf56', 1, '2025-04-03 16:33:24'),
(2, 'Physical Health', 'fas fa-heartbeat', '#4CAF50', 2, '2025-04-03 16:33:24'),
(3, 'Mental Growth', 'fas fa-brain', '#2196F3', 3, '2025-04-03 16:33:24'),
(4, 'Productivity', 'fas fa-tasks', '#9C27B0', 4, '2025-04-03 16:33:24'),
(5, 'Spiritual Life', 'fas fa-pray', '#cdaf56', 1, '2025-04-03 16:36:01'),
(6, 'Physical Health', 'fas fa-heartbeat', '#4CAF50', 2, '2025-04-03 16:36:01'),
(7, 'Mental Growth', 'fas fa-brain', '#2196F3', 3, '2025-04-03 16:36:01'),
(8, 'Productivity', 'fas fa-tasks', '#9C27B0', 4, '2025-04-03 16:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `eng_sections`
--

CREATE TABLE `eng_sections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `section_number` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eng_sections`
--

INSERT INTO `eng_sections` (`id`, `name`, `section_number`, `description`, `created_at`) VALUES
(1, 'Foundational Grammar', 1, 'Core grammar concepts and language mechanics', '2025-03-31 21:29:19'),
(2, 'Reading Comprehension', 2, 'Understanding and analyzing written texts', '2025-03-31 21:29:19'),
(3, 'Extended Reading Analysis', 3, 'In-depth analysis of literary texts and perspectives', '2025-03-31 21:29:19'),
(4, 'Writing Skills', 4, 'Developing writing techniques and structures', '2025-03-31 21:29:19'),
(5, 'Transactional and Creative Writing', 5, 'Different forms of writing and creative expression', '2025-03-31 21:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `eng_section_progress`
--

CREATE TABLE `eng_section_progress` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `total_subsections` int(11) DEFAULT 0,
  `total_topics` int(11) DEFAULT 0,
  `completed_topics` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `total_time_spent_seconds` bigint(20) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eng_study_time_tracking`
--

CREATE TABLE `eng_study_time_tracking` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `status` enum('active','paused','completed') DEFAULT 'active',
  `last_pause_time` datetime DEFAULT NULL,
  `accumulated_seconds` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eng_subsections`
--

CREATE TABLE `eng_subsections` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subsection_number` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eng_subsections`
--

INSERT INTO `eng_subsections` (`id`, `section_id`, `name`, `subsection_number`, `description`, `created_at`) VALUES
(69, 1, 'Parts of Speech', '1.1', 'Understanding different types of words and their functions', '2025-03-31 21:32:06'),
(70, 1, 'Sentence Structure', '1.2', 'Types and components of sentences', '2025-03-31 21:32:06'),
(71, 1, 'Punctuation', '1.3', 'Rules and usage of punctuation marks', '2025-03-31 21:32:06'),
(72, 1, 'Tenses', '1.4', 'Understanding and using different verb tenses', '2025-03-31 21:32:06'),
(73, 2, 'Reading Techniques', '2.1', 'Methods for effective reading and understanding', '2025-03-31 21:32:29'),
(74, 2, 'Understanding Fiction', '2.2', 'Analyzing fictional texts and their elements', '2025-03-31 21:32:29'),
(75, 2, 'Language Analysis', '2.3', 'Examining language use and literary devices', '2025-03-31 21:32:29'),
(76, 2, 'Structure Analysis', '2.4', 'Understanding text organization and structure', '2025-03-31 21:32:29'),
(77, 3, 'Literary Perspectives', '3.1', 'Different ways of interpreting texts', '2025-03-31 21:33:23'),
(78, 3, 'Audience and Purpose', '3.2', 'Understanding target readers and writer\'s intent', '2025-03-31 21:33:23'),
(79, 3, 'Language and Structure', '3.3', 'Analyzing language and structural features', '2025-03-31 21:33:23'),
(80, 3, 'Building Interpretation', '3.4', 'Developing analytical skills and interpretations', '2025-03-31 21:33:23'),
(81, 4, 'Writing Preparation', '4.1', 'Planning and organizing writing', '2025-03-31 21:33:23'),
(82, 4, 'Sentence & Punctuation for Effect', '4.2', 'Using language features for impact', '2025-03-31 21:33:23'),
(83, 5, 'Forms of Transactional Writing', '5.1', 'Different types of formal writing', '2025-03-31 21:33:23'),
(84, 5, 'Narrative and Descriptive Techniques', '5.2', 'Creative writing methods', '2025-03-31 21:33:23'),
(85, 5, 'Finalising Writing', '5.3', 'Editing and polishing written work', '2025-03-31 21:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `eng_subsection_progress`
--

CREATE TABLE `eng_subsection_progress` (
  `id` int(11) NOT NULL,
  `subsection_id` int(11) NOT NULL,
  `total_topics` int(11) DEFAULT 0,
  `completed_topics` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `total_time_spent_seconds` bigint(20) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eng_topics`
--

CREATE TABLE `eng_topics` (
  `id` int(11) NOT NULL,
  `subsection_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eng_topics`
--

INSERT INTO `eng_topics` (`id`, `subsection_id`, `name`, `description`, `created_at`) VALUES
(10, 69, 'Nouns & Pronouns', 'Understanding naming words and their substitutes', '2025-03-31 21:43:01'),
(11, 69, 'Verbs', 'Action and state words', '2025-03-31 21:43:01'),
(12, 69, 'Adjectives', 'Descriptive modifiers', '2025-03-31 21:43:01'),
(13, 69, 'Adverbs', 'Verb, adjective, and sentence modifiers', '2025-03-31 21:43:01'),
(14, 69, 'Articles & Determiners', 'Words that introduce nouns', '2025-03-31 21:43:01'),
(15, 69, 'Prepositions', 'Relationship words', '2025-03-31 21:43:01'),
(16, 69, 'Conjunctions', 'Connecting words', '2025-03-31 21:43:01'),
(17, 69, 'Interjections', 'Exclamatory words', '2025-03-31 21:43:01'),
(18, 70, 'Simple Sentences', 'Basic sentence structures', '2025-03-31 21:43:01'),
(19, 70, 'Compound Sentences', 'Connected simple sentences', '2025-03-31 21:43:01'),
(20, 70, 'Complex Sentences', 'Independent and dependent clauses', '2025-03-31 21:43:01'),
(21, 70, 'Compound-Complex Sentences', 'Multiple clause structures', '2025-03-31 21:43:01'),
(22, 70, 'Sentence Types', 'Declarative, interrogative, imperative, exclamatory', '2025-03-31 21:43:01'),
(23, 70, 'Subject-Verb Agreement', 'Matching subjects with verbs', '2025-03-31 21:43:01'),
(24, 71, 'Full Stops, Question Marks, Exclamation Marks', 'End punctuation', '2025-03-31 21:43:53'),
(25, 71, 'Commas', 'Uses and rules for commas', '2025-03-31 21:43:53'),
(26, 71, 'Semicolons & Colons', 'Advanced punctuation', '2025-03-31 21:43:53'),
(27, 71, 'Quotation Marks & Dialogue', 'Punctuating speech and quotations', '2025-03-31 21:43:53'),
(28, 71, 'Apostrophes', 'Possession and contraction', '2025-03-31 21:43:53'),
(29, 71, 'Hyphens & Dashes', 'Joining and separating punctuation', '2025-03-31 21:43:53'),
(30, 71, 'Brackets & Parentheses', 'Enclosing additional information', '2025-03-31 21:43:53'),
(31, 72, 'Present Tense', 'Current time verbs', '2025-03-31 21:43:53'),
(32, 72, 'Past Tense', 'Previous time verbs', '2025-03-31 21:43:53'),
(33, 72, 'Future Tense', 'Coming time verbs', '2025-03-31 21:43:53'),
(34, 72, 'Conditional Forms', 'Hypothetical situations', '2025-03-31 21:43:53'),
(35, 72, 'Active & Passive Voice', 'Subject-action relationships', '2025-03-31 21:43:53'),
(36, 73, 'Skimming & Scanning', 'Quick reading methods', '2025-03-31 21:43:53'),
(37, 73, 'Inference & Deduction', 'Drawing conclusions from text', '2025-03-31 21:43:53'),
(38, 73, 'Contextual Understanding', 'Understanding context', '2025-03-31 21:43:53'),
(39, 73, 'Critical Reading', 'Analyzing and evaluating texts', '2025-03-31 21:43:53'),
(40, 73, 'Summarizing & Paraphrasing', 'Condensing and rephrasing text', '2025-03-31 21:43:53'),
(41, 74, 'Plot Analysis', 'Understanding story structure', '2025-03-31 21:43:53'),
(42, 74, 'Character Development', 'Analyzing character growth', '2025-03-31 21:43:53'),
(43, 74, 'Setting & Atmosphere', 'Environment and mood', '2025-03-31 21:43:53'),
(44, 74, 'Themes & Motifs', 'Main ideas and recurring elements', '2025-03-31 21:43:53'),
(45, 74, 'Narrative Perspective', 'Point of view and narration', '2025-03-31 21:43:53'),
(46, 75, 'Identifying Literary Devices', 'Recognizing writing techniques', '2025-03-31 21:45:07'),
(47, 75, 'Analyzing Word Choice', 'Examining vocabulary selection', '2025-03-31 21:45:07'),
(48, 75, 'Tone & Mood', 'Emotional impact of writing', '2025-03-31 21:45:07'),
(49, 75, 'Imagery & Symbolism', 'Visual and symbolic elements', '2025-03-31 21:45:07'),
(50, 75, 'Sound Devices', 'Phonetic techniques', '2025-03-31 21:45:07'),
(51, 76, 'Text Organization', 'Overall text structure', '2025-03-31 21:45:07'),
(52, 76, 'Beginning, Middle, End Structure', 'Narrative progression', '2025-03-31 21:45:07'),
(53, 76, 'Paragraph Structure', 'Paragraph organization', '2025-03-31 21:45:07'),
(54, 76, 'Tension & Climax', 'Building and resolving conflict', '2025-03-31 21:45:07'),
(55, 76, 'Foreshadowing & Flashback', 'Time manipulation in narrative', '2025-03-31 21:45:07'),
(56, 77, 'Unit 3: Themes and Ideas', 'Different ways to read texts', '2025-03-31 21:45:07'),
(57, 77, 'Unit 4: Ideas and Perspectives', 'Understanding period influences', '2025-03-31 21:45:07'),
(58, 77, 'Unit 5: The Writer\'s Perspective', 'Cultural influences on interpretation', '2025-03-31 21:45:07'),
(61, 78, 'Unit 6: Audience and Purpose in Fiction', 'Understanding intended readers', '2025-03-31 21:45:07'),
(62, 78, 'Unit 7: Audience and Purpose in Non-Fiction', 'Understanding author\'s goals', '2025-03-31 21:45:07'),
(66, 79, 'Unit 8: Features of Language in Fiction', 'Deep examination of language choices', '2025-03-31 21:45:07'),
(67, 79, 'Unit 9: Features of Language in Non-Fiction', 'Complex structural devices', '2025-03-31 21:45:07'),
(68, 79, 'Unit 10: Language and Structure', 'Sustained figurative language', '2025-03-31 21:45:07'),
(71, 80, 'Unit 11: Communicating Ideas', 'Choosing supporting quotations', '2025-03-31 21:45:56'),
(72, 80, 'Unit 12: Language Choices', 'Building analytical responses', '2025-03-31 21:45:56'),
(73, 80, 'Unit 13: Structural Devices', 'Considering different interpretations', '2025-03-31 21:45:56'),
(74, 80, 'Unit 14: Selecting Appropriate Examples', 'Assessing effectiveness', '2025-03-31 21:45:56'),
(75, 80, 'Unit 15: Comparing Texts', 'Comparing different texts', '2025-03-31 21:45:56'),
(76, 81, 'Unit 18: Planning Your Writing', 'Methods for organizing writing', '2025-03-31 21:45:56'),
(77, 81, 'Unit 19: Beginnings, Middles, and Endings', 'Brainstorming and research', '2025-03-31 21:45:56'),
(78, 81, 'Unit 20: Writing for Audience and Purpose', 'Organizing content effectively', '2025-03-31 21:45:56'),
(81, 82, 'Unit 21: Using Punctuation', 'Using different sentence types', '2025-03-31 21:45:56'),
(82, 82, 'Unit 22: Using Sentences and Punctuation for Effect', 'Creating effects with punctuation', '2025-03-31 21:45:56'),
(86, 83, 'Unit 23: Form in Transactional Writing', 'Formal and informal correspondence', '2025-03-31 21:45:56'),
(87, 83, 'Unit 24: Ideas for Writing', 'Informative writing', '2025-03-31 21:45:56'),
(91, 84, 'Unit 25: Writing Narratives', 'Narrative organization', '2025-03-31 21:45:56'),
(92, 84, 'Unit 26: Writing Descriptions', 'Developing believable characters', '2025-03-31 21:45:56'),
(93, 84, 'Unit 27: Writing Monologues', 'Creating vivid environments', '2025-03-31 21:45:56'),
(94, 84, 'Unit 28: Crafting Language for Effect', 'Sensory and detailed description', '2025-03-31 21:45:56'),
(96, 85, 'Unit 29: Checking and Editing', 'Improving written work', '2025-03-31 21:45:56'),
(97, 85, 'Unit 30: Writing Texts', 'Checking for accuracy', '2025-03-31 21:45:56'),
(101, 80, 'Unit 16: Analysing Fictional Texts', 'Analysis of fiction texts', '2025-03-31 21:48:27'),
(102, 80, 'Unit 17: Analysing Non-Fictional Texts', 'Analysis of non-fiction texts', '2025-03-31 21:48:27');

-- --------------------------------------------------------

--
-- Table structure for table `eng_topic_progress`
--

CREATE TABLE `eng_topic_progress` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `total_time_spent` int(11) DEFAULT 0,
  `confidence_level` int(11) DEFAULT 0,
  `last_studied` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eng_topic_progress`
--

INSERT INTO `eng_topic_progress` (`id`, `topic_id`, `status`, `total_time_spent`, `confidence_level`, `last_studied`, `completion_date`, `notes`) VALUES
(1, 36, 'in_progress', 0, 3, '2025-04-01 00:09:23', NULL, ''),
(2, 10, 'not_started', 0, 0, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `exam_date` datetime NOT NULL,
  `duration` int(11) DEFAULT 120,
  `location` varchar(100) DEFAULT NULL,
  `exam_board` varchar(50) DEFAULT NULL,
  `paper_code` varchar(20) DEFAULT NULL,
  `importance` int(11) DEFAULT 3,
  `notes` text DEFAULT NULL,
  `section_a_topics` text DEFAULT NULL,
  `section_b_topics` text DEFAULT NULL,
  `total_marks` int(11) DEFAULT 0,
  `calculator_allowed` tinyint(1) DEFAULT 0,
  `formula_sheet_provided` tinyint(1) DEFAULT 0,
  `equipment_needed` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `revision_resources` text DEFAULT NULL,
  `exam_tips` text DEFAULT NULL,
  `syllabus_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `subject_id`, `title`, `exam_date`, `duration`, `location`, `exam_board`, `paper_code`, `importance`, `notes`, `section_a_topics`, `section_b_topics`, `total_marks`, `calculator_allowed`, `formula_sheet_provided`, `equipment_needed`, `special_instructions`, `revision_resources`, `exam_tips`, `syllabus_link`) VALUES
(1, 2, 'Mathematics Paper 1: Non-Calculator (Higher Tier)', '2025-05-15 09:00:00', 90, 'Main Hall', 'Edexcel', 'MATH1', 5, 'This is the first of three papers for the Edexcel GCSE Maths Higher Tier. No calculator allowed. Paper contributes one-third to the overall Maths GCSE grade.', 'Covers:\n\r\n- Number\n\r\n- Algebra\n\r\n- Ratio, proportion and rates of change\n\r\n- Geometry and measures\n\r\n- Probability\n\r\n- Statistics\n\n\r\nSkills Tested:\n\r\n- Manual calculation skills\n\r\n- Algebraic manipulation\n\r\n- Applying maths to problem-solving situations\n\r\n- Interpreting data and diagrams', 'All questions are compulsory.\n\r\n- Mixture of short, structured and multi-step questions\n\r\n- Word problems, diagrams, real-world maths scenarios', 80, 0, 1, 'Black pen\nPencil\nRuler\nRubber\nProtractor\nCompass\nScientific calculator (not allowed for Paper 1)', '- Show full working out\n\r\n- Write clearly and label diagrams\n\r\n- Attempt every question\n\r\n- Answer all questions in the spaces provided', '1. Pearson Maths Higher Tier Revision Guide\n\r\n2. Exam-style practice papers (non-calculator)\n\r\n3. GCSE Maths Tutor YouTube\n\r\n4. Corbettmaths revision cards\n\r\n5. Dr Frost Maths practice sets', '- Read each question carefully\n\r\n- Show full method even if unsure\n\r\n- Watch your units and rounding\n\r\n- Check your work with estimation', 'https://qualifications.pearson.com/content/dam/pdf/GCSE/mathematics/2015/specification-and-sample-assesment/gcse-maths-2015-specification.pdf'),
(2, 2, 'Mathematics Paper 2: Calculator (Higher Tier)', '2025-06-04 09:00:00', 90, 'Main Hall', 'Edexcel', 'MATH2', 5, 'This is the second of three papers for Edexcel GCSE Maths Higher Tier. A calculator is allowed. Paper contributes one-third to the overall Maths GCSE grade.', 'Covers:\n\r\n- Number\n\r\n- Algebra\n\r\n- Ratio, proportion and rates of change\n\r\n- Geometry and measures\n\r\n- Probability\n\r\n- Statistics\n\n\r\nSkills Tested:\n\r\n- Calculator-based problem solving\n\r\n- Interpreting multi-step problems\n\r\n- Working with percentages, indices, graphs, etc.', 'All questions are compulsory.\n\r\n- Real-world applications of maths\n\r\n- Use of formulae, diagrams, conversions\n\r\n- Mix of short and long questions requiring written methods', 80, 1, 1, 'Black pen\nPencil\nRuler\nRubber\nProtractor\nCompass\nScientific calculator', '- Use your calculator efficiently\n\r\n- Show working even when using a calculator\n\r\n- Write clearly and neatly\n\r\n- Round answers only when instructed', '1. Pearson Maths Higher Tier Workbook\n\r\n2. JustMaths practice sets\n\r\n3. Corbettmaths and Maths Genie topic videos\n\r\n4. Examwizard past papers\n\r\n5. Dr Frost diagnostic quizzes', '- Use the calculator for accuracy\n\r\n- Check mode (degrees/radians) before you begin\n\r\n- Use formula sheet to save time\n\r\n- Estimate answers to catch errors', 'https://qualifications.pearson.com/content/dam/pdf/GCSE/mathematics/2015/specification-and-sample-assesment/gcse-maths-2015-specification.pdf'),
(3, 2, 'Mathematics Paper 3: Calculator (Higher Tier)', '2025-06-11 09:00:00', 90, 'Main Hall', 'Edexcel', 'MATH3', 5, 'This is the third and final paper for Edexcel GCSE Maths Higher Tier. A calculator is allowed. Paper contributes one-third to the overall Maths GCSE grade.', 'Covers:\n\r\n- Number\n\r\n- Algebra\n\r\n- Ratio, proportion and rates of change\n\r\n- Geometry and measures\n\r\n- Probability\n\r\n- Statistics\n\n\r\nSkills Tested:\n\r\n- Deep understanding across all topics\n\r\n- Linking multiple mathematical skills in one question\n\r\n- Problem-solving and logical reasoning', 'All questions are compulsory.\n\r\n- Longer, more challenging questions often appear here\n\r\n- Expect multi-step reasoning and application\n\r\n- Requires strong topic crossover understanding', 80, 1, 1, 'Black pen\nPencil\nRuler\nRubber\nProtractor\nCompass\nScientific calculator', '- Don’t panic on tricky questions – break them down\n\r\n- Show all working\n\r\n- Use formula sheet and calculator together wisely\n\r\n- Answer all questions – attempt even hard ones', '1. Advanced Maths Problem Packs\n\r\n2. Mixed-topic mock papers\n\r\n3. Corbettmaths Practice Papers\n\r\n4. Hegarty Maths Tasks\n\r\n5. GCSE Maths Tutor challenge questions', '- Paper 3 often includes trickier, unseen question types\n\r\n- Stay calm and take time to understand the problem\n\r\n- Label diagrams clearly\n\r\n- Check calculations at the end', 'https://qualifications.pearson.com/content/dam/pdf/GCSE/mathematics/2015/specification-and-sample-assesment/gcse-maths-2015-specification.pdf'),
(4, 1, 'English Language Paper 1: Fiction and Imaginative Writing', '2025-05-23 09:00:00', 105, 'Main Hall', 'Edexcel', 'ENG1', 5, 'Paper 1 = 50% of overall English Language GCSE grade. Focus on 19th-century fiction and creative writing.', 'SECTION A - READING (40 marks, 55 minutes):\n\r\n- Question 1 (4 marks): Identify 4 things from text\n\r\n- Question 2 (6 marks): Language analysis\n\r\n- Question 3 (6 marks): Structure analysis\n\r\n- Question 4 (24 marks): Evaluation\n\n\r\nSkills Tested:\n\r\n- Understanding explicit & implicit meanings\n\r\n- Analysing writer\'s use of language and structure\n\r\n- Evaluating text effectiveness\n\r\n- Selecting and using evidence', 'SECTION B - CREATIVE WRITING (40 marks, 50 minutes):\n\r\n- 24 marks: Content & Organisation\n\r\n- 16 marks: Spelling, Punctuation & Grammar (SPaG)\n\n\r\nTask Types:\n\r\n- Narrative story writing\n\r\n- Descriptive scene writing\n\n\r\nSkills Tested:\n\r\n- Creative use of language\n\r\n- Using structure effectively\n\r\n- Clear voice and sense of audience/purpose\n\r\n- Technical accuracy (SPaG)', 64, 0, 0, 'Black pen (required)\nHighlighter (optional but recommended)\nEraser', '- Answer ALL questions in Section A\n\r\n- Choose ONE question from Section B\n\r\n- SPaG is assessed (especially in Section B)\n\r\n- No calculator or dictionary allowed\n\r\n- Spend about 55 minutes on Section A\n\r\n- Spend about 50 minutes on Section B (35 mins writing + 15 mins planning & checking)', '1. Official Pearson Revision Guide\n\r\n2. Mr Bruff YouTube Channel\n\r\n3. Past Papers and Mark Schemes\n\r\n4. Examiner Reports\n\r\n5. Practice 19th Century Fiction Extracts\n\r\n6. Creative Writing Prompts Collection', 'Reading Section (Q1-Q4):\n\r\n- Use quotations in every answer (except Q1)\n\r\n- For language (Q2), focus on effect on the reader\n\r\n- For structure (Q3), think about beginning-middle-end, contrast, or shifts\n\r\n- In evaluation (Q4), use your opinion, backed up with analysis\n\n\r\nWriting Section:\n\r\n- Plan for 5 minutes: structure your ideas\n\r\n- Use sensory language – what can you see, hear, feel?\n\r\n- Use a range of sentence lengths and punctuation\n\r\n- Use paragraphs clearly – don\'t write in one chunk!\n\r\n- Proofread at the end – 16 SPaG marks matter!', 'https://qualifications.pearson.com/content/dam/pdf/GCSE/English%20Language/2015/specification-and-sample-assessments/9781446914281_GCSE_2015_L12_EngLang.pdf'),
(5, 1, 'English Language Paper 2: Non-Fiction and Transactional Writing', '2025-06-06 09:00:00', 125, 'Main Hall', 'Edexcel', 'ENG2', 5, 'Paper 2 = 50% of overall English Language GCSE grade. Focus on non-fiction reading and transactional writing.', 'SECTION A - READING (56 marks, ~70 minutes):\n\r\n- 2 unseen non-fiction texts (20th & 21st century)\n\r\n- Q1 (4 marks): Identify 4 facts/ideas\n\r\n- Q2 (6 marks): Language analysis (Text 1)\n\r\n- Q3 (6 marks): Structure analysis (Text 1)\n\r\n- Q4 (15 marks): Evaluation (Text 1)\n\r\n- Q5 (1-2 marks): Key ideas from Text 2\n\r\n- Q6 (15 marks): Compare writer\'s viewpoints\n\r\n- Q7 (9 marks): Synthesis of key ideas\n\n\r\nSkills Tested:\n\r\n- Reading comprehension\n\r\n- Analysing language and structure\n\r\n- Evaluating techniques and effects\n\r\n- Comparing perspectives and viewpoints', 'SECTION B - TRANSACTIONAL WRITING (40 marks, ~55 minutes):\n\r\n- 24 marks: Content & Organisation\n\r\n- 16 marks: Spelling, Punctuation & Grammar (SPaG)\n\n\r\nTask Types:\n\r\n- Article, Letter, Speech, Review, Leaflet, Essay\n\n\r\nSkills Tested:\n\r\n- Adapting tone and structure for audience/purpose\n\r\n- Organising ideas clearly\n\r\n- Persuasive and clear writing techniques\n\r\n- Technical accuracy (SPaG)', 96, 0, 0, 'Black pen (required)\nHighlighter (optional but recommended)\nEraser', '- Answer ALL questions in Section A\n\r\n- Choose ONE task from Section B\n\r\n- SPaG is assessed (especially in Section B)\n\r\n- No calculator or dictionary allowed\n\r\n- Spend about 70 minutes on Section A\n\r\n- Spend about 55 minutes on Section B (plan, write, and proofread)', '1. Pearson Revision Guide\n\r\n2. Mr Bruff YouTube Channel\n\r\n3. Past Papers and Mark Schemes\n\r\n4. Examiner Reports\n\r\n5. Sample Transactional Writing Tasks\n\r\n6. Annotated Non-Fiction Extracts', 'Reading Section (Q1–Q7):\n\r\n- Read both texts carefully and annotate key points\n\r\n- Use quotations and analyse techniques\n\r\n- Use comparative connectives in Q6 (e.g., similarly, however)\n\r\n- Focus on key differences/similarities in Q7\n\n\r\nWriting Section:\n\r\n- Know your format: speech, letter, article, etc.\n\r\n- Use rhetorical techniques (AFOREST)\n\r\n- Structure clearly: intro, main points, conclusion\n\r\n- Proofread your writing – 16 SPaG marks are critical!', 'https://qualifications.pearson.com/content/dam/pdf/GCSE/English%20Language/2015/specification-and-sample-assessments/9781446914281_GCSE_2015_L12_EngLang.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `exam_reports`
--

CREATE TABLE `exam_reports` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `point_rule_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-check-circle' COMMENT 'Font Awesome icon class name',
  `target_time` time DEFAULT NULL,
  `current_points` int(11) DEFAULT 0,
  `total_completions` int(11) DEFAULT 0,
  `total_procrastinated` int(11) DEFAULT 0,
  `total_skips` int(11) DEFAULT 0,
  `current_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habits`
--

INSERT INTO `habits` (`id`, `category_id`, `point_rule_id`, `name`, `description`, `icon`, `target_time`, `current_points`, `total_completions`, `total_procrastinated`, `total_skips`, `current_streak`, `longest_streak`, `success_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(11, 9, 2, 'Call home', '', 'fas fa-check-circle', '10:00:00', 0, 0, 0, 0, 0, 0, 0.00, 1, '2025-04-01 23:24:52', '2025-04-01 23:24:52'),
(13, 12, 2, 'Reading Books', '', 'fas fa-check-circle', '15:00:00', 0, 0, 0, 0, 0, 0, 0.00, 1, '2025-04-02 13:49:34', '2025-04-02 15:01:15');

-- --------------------------------------------------------

--
-- Table structure for table `habit_categories`
--

CREATE TABLE `habit_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `icon` varchar(50) DEFAULT 'fas fa-folder' COMMENT 'Font Awesome icon class name',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habit_categories`
--

INSERT INTO `habit_categories` (`id`, `name`, `description`, `color`, `icon`, `display_order`, `created_at`) VALUES
(1, 'Spiritual Life', 'Religious and spiritual activities', '#e6d305', 'fas fa-pray', 1, '2025-04-01 21:34:00'),
(2, 'Education', 'Learning and academic activities', '#1E90FF', 'fas fa-graduation-cap', 2, '2025-04-01 21:34:00'),
(3, 'Self Care', 'Personal care and wellbeing', '#4682B4', 'fas fa-spa', 3, '2025-04-01 21:34:00'),
(4, 'Work', 'Professional and career activities', '#2E8B57', 'fas fa-briefcase', 4, '2025-04-01 21:34:00'),
(5, 'Finance', 'Financial management and goals', '#DAA520', 'fas fa-coins', 5, '2025-04-01 21:34:00'),
(6, 'Family', 'Family relationships and responsibilities', '#FF69B4', 'fas fa-home', 6, '2025-04-01 21:34:00'),
(7, 'Health', 'Physical health and fitness', '#32CD32', 'fas fa-heartbeat', 7, '2025-04-01 21:34:00'),
(8, 'Sleep', 'Sleep schedule and routine', '#483D8B', 'fas fa-bed', 8, '2025-04-01 21:34:00'),
(9, 'Social', 'Social connections and relationships', '#FF7F50', 'fas fa-users', 9, '2025-04-01 21:34:00'),
(10, 'Personal Growth', 'Self-improvement and development', '#9370DB', 'fas fa-brain', 10, '2025-04-01 21:34:00'),
(12, 'Reading', NULL, '#56a5cd', 'fas fa-book', 11, '2025-04-02 13:48:53');

-- --------------------------------------------------------

--
-- Table structure for table `habit_completions`
--

CREATE TABLE `habit_completions` (
  `id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `completion_date` date NOT NULL,
  `completion_time` time NOT NULL,
  `status` enum('completed','procrastinated','skipped') NOT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `habit_performance_view`
-- (See below for the actual view)
--
CREATE TABLE `habit_performance_view` (
`id` int(11)
,`name` varchar(255)
,`category` varchar(50)
,`point_rule` varchar(50)
,`current_points` int(11)
,`total_completions` int(11)
,`total_procrastinated` int(11)
,`total_skips` int(11)
,`average_points_per_day` decimal(24,2)
,`success_rate` decimal(5,2)
,`current_streak` int(11)
,`longest_streak` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `habit_point_rules`
--

CREATE TABLE `habit_point_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `completion_points` int(11) NOT NULL,
  `procrastinated_points` int(11) NOT NULL,
  `skip_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habit_point_rules`
--

INSERT INTO `habit_point_rules` (`id`, `name`, `description`, `completion_points`, `procrastinated_points`, `skip_points`, `created_at`) VALUES
(1, 'Basic Habit', 'Simple daily habits', 5, 2, -3, '2025-04-01 21:34:00'),
(2, 'Important Habit', 'Key daily activities', 10, 4, -7, '2025-04-01 21:34:00'),
(3, 'Critical Habit', 'Essential daily practices', 20, 8, -12, '2025-04-01 21:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `habit_progress`
--

CREATE TABLE `habit_progress` (
  `id` int(11) NOT NULL,
  `habit_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('completed','pending','skipped') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `habit_reasons`
--

CREATE TABLE `habit_reasons` (
  `id` int(11) NOT NULL,
  `reason_text` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `habit_reasons`
--

INSERT INTO `habit_reasons` (`id`, `reason_text`, `is_default`, `created_at`) VALUES
(1, 'Using social media', 1, '2025-04-02 02:07:26'),
(2, 'Being lazy', 1, '2025-04-02 02:07:26'),
(3, 'Being moody', 1, '2025-04-02 02:07:26'),
(4, 'Being careless', 1, '2025-04-02 02:07:26'),
(5, 'Being stressed', 1, '2025-04-02 02:07:26'),
(6, 'Chatting with people', 1, '2025-04-02 02:07:26'),
(7, 'Super busy', 1, '2025-04-02 02:07:26'),
(8, 'Tired of this habit', 1, '2025-04-02 02:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `math_sections`
--

CREATE TABLE `math_sections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `section_number` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `math_sections`
--

INSERT INTO `math_sections` (`id`, `name`, `section_number`, `description`, `created_at`) VALUES
(1, 'Number', 1, 'Core number operations, place value, and numerical concepts', '2025-03-31 21:08:17'),
(2, 'Algebra', 2, 'Equations, expressions, functions, and algebraic manipulation', '2025-03-31 21:08:17'),
(3, 'Ratio, Proportion and Rates of Change', 3, 'Relationships between quantities and rates', '2025-03-31 21:08:17'),
(4, 'Geometry and Measure', 4, 'Shape, space, and measurement concepts', '2025-03-31 21:08:17'),
(5, 'Probability', 5, 'Chance, likelihood, and probability calculations', '2025-03-31 21:08:17'),
(6, 'Statistics', 6, 'Data handling, analysis, and interpretation', '2025-03-31 21:08:17');

-- --------------------------------------------------------

--
-- Table structure for table `math_subsections`
--

CREATE TABLE `math_subsections` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subsection_number` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `math_subsections`
--

INSERT INTO `math_subsections` (`id`, `section_id`, `name`, `subsection_number`, `description`, `created_at`) VALUES
(1, 1, 'Basic Number Operations', '1.1', 'Fundamental operations and number concepts', '2025-03-31 21:09:37'),
(2, 1, 'Advanced Number Concepts', '1.2', 'Advanced operations and number theory', '2025-03-31 21:09:37'),
(3, 1, 'Accuracy and Estimation', '1.3', 'Working with approximations and errors', '2025-03-31 21:09:37'),
(4, 2, 'Basic Algebraic Manipulation', '2.1', 'Core algebraic operations and concepts', '2025-03-31 21:09:37'),
(5, 2, 'Equations and Inequalities', '2.2', 'Solving various types of equations and inequalities', '2025-03-31 21:09:37'),
(6, 2, 'Functions and Graphs', '2.3', 'Understanding and working with different types of functions', '2025-03-31 21:09:37'),
(7, 2, 'Advanced Algebra', '2.4', 'Complex algebraic concepts and proofs', '2025-03-31 21:09:37'),
(8, 3, 'Ratio', '3.1', 'Understanding and working with ratios', '2025-03-31 21:09:37'),
(9, 3, 'Percentages', '3.2', 'Calculations involving percentages', '2025-03-31 21:09:37'),
(10, 3, 'Proportion', '3.3', 'Direct and inverse proportion', '2025-03-31 21:09:37'),
(11, 3, 'Compound Measures', '3.4', 'Working with compound units and measures', '2025-03-31 21:09:37'),
(12, 4, 'Basic Geometry', '4.1', 'Fundamental geometric concepts', '2025-03-31 21:09:37'),
(13, 4, 'Transformations', '4.2', 'Geometric transformations and their properties', '2025-03-31 21:09:37'),
(14, 4, '3D Geometry', '4.3', 'Three-dimensional shapes and their properties', '2025-03-31 21:09:37'),
(15, 4, 'Advanced Geometry', '4.4', 'Complex geometric concepts and proofs', '2025-03-31 21:09:37'),
(16, 4, 'Trigonometry', '4.5', 'Trigonometric ratios and applications', '2025-03-31 21:09:37'),
(17, 5, 'Basic Probability', '5.1', 'Fundamental concepts of probability', '2025-03-31 21:09:37'),
(18, 5, 'Probability Diagrams', '5.2', 'Visual representations of probability', '2025-03-31 21:09:37'),
(19, 5, 'Advanced Probability', '5.3', 'Complex probability concepts and calculations', '2025-03-31 21:09:37'),
(20, 6, 'Data Representation', '6.1', 'Different ways to present and visualize data', '2025-03-31 21:09:37'),
(21, 6, 'Data Analysis', '6.2', 'Statistical measures and analysis techniques', '2025-03-31 21:09:37'),
(22, 6, 'Statistical Reasoning', '6.3', 'Drawing conclusions from statistical data', '2025-03-31 21:09:37');

-- --------------------------------------------------------

--
-- Table structure for table `math_topics`
--

CREATE TABLE `math_topics` (
  `id` int(11) NOT NULL,
  `subsection_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `math_topics`
--

INSERT INTO `math_topics` (`id`, `subsection_id`, `name`, `description`, `created_at`) VALUES
(186, 1, 'Understanding place value', 'Learn about the position and value of digits in numbers', '2025-03-31 21:14:18'),
(187, 1, 'Operations with integers, decimals and fractions', 'Perform calculations with different number types', '2025-03-31 21:14:18'),
(188, 1, 'Order of operations (BIDMAS/BODMAS)', 'Understanding the correct order of mathematical operations', '2025-03-31 21:14:18'),
(189, 1, 'Factors, multiples, and prime numbers', 'Explore number relationships and prime factorization', '2025-03-31 21:14:18'),
(190, 1, 'HCF and LCM', 'Find highest common factors and lowest common multiples', '2025-03-31 21:14:18'),
(191, 1, 'Powers and roots', 'Work with exponents and square/cube roots', '2025-03-31 21:14:18'),
(192, 2, 'Surds', 'Simplifying and rationalizing surds', '2025-03-31 21:14:18'),
(193, 2, 'Index laws', 'Working with negative and fractional indices', '2025-03-31 21:14:18'),
(194, 2, 'Standard form calculations', 'Calculations with numbers in scientific notation', '2025-03-31 21:14:18'),
(195, 2, 'Upper and lower bounds', 'Understanding limits of accuracy', '2025-03-31 21:14:18'),
(196, 2, 'Recurring decimals to fractions', 'Converting between decimal and fraction forms', '2025-03-31 21:14:18'),
(197, 2, 'Product rule for counting', 'Understanding combinatorial counting principles', '2025-03-31 21:14:18'),
(198, 3, 'Rounding to decimal places and significant figures', 'Different methods of approximation', '2025-03-31 21:14:18'),
(199, 3, 'Error intervals', 'Understanding and calculating error ranges', '2025-03-31 21:14:18'),
(200, 3, 'Limits of accuracy', 'Working with measurements and precision', '2025-03-31 21:14:18'),
(201, 3, 'Working with bounds', 'Calculations involving upper and lower bounds', '2025-03-31 21:14:18'),
(202, 3, 'Estimation techniques', 'Methods for approximating calculations', '2025-03-31 21:14:18'),
(203, 4, 'Collecting like terms', 'Simplifying algebraic expressions', '2025-03-31 21:14:18'),
(204, 4, 'Substitution', 'Replacing variables with values', '2025-03-31 21:14:18'),
(205, 4, 'Expanding brackets', 'Single, double, and triple bracket expansion', '2025-03-31 21:14:18'),
(206, 4, 'Factorizing expressions', 'Finding common factors and quadratic factorization', '2025-03-31 21:14:18'),
(207, 4, 'Laws of indices', 'Rules for working with powers', '2025-03-31 21:14:18'),
(208, 4, 'Algebraic fractions', 'Operations with fractional expressions', '2025-03-31 21:14:18'),
(209, 5, 'Solving linear equations', 'Methods for solving first-degree equations', '2025-03-31 21:14:18'),
(210, 5, 'Solving quadratic equations by factorization', 'Finding solutions using factoring', '2025-03-31 21:14:18'),
(211, 5, 'Quadratic formula method', 'Using the formula to solve quadratic equations', '2025-03-31 21:14:18'),
(212, 5, 'Completing the square', 'Alternative method for solving quadratics', '2025-03-31 21:14:18'),
(213, 5, 'Linear inequalities', 'Solving and representing inequalities', '2025-03-31 21:14:18'),
(214, 5, 'Quadratic inequalities', 'Solving second-degree inequalities', '2025-03-31 21:14:18'),
(215, 5, 'Graphical inequalities', 'Representing inequalities on graphs', '2025-03-31 21:14:18'),
(216, 5, 'Simultaneous equations (linear)', 'Solving systems of linear equations', '2025-03-31 21:14:18'),
(217, 5, 'Simultaneous equations (linear/quadratic)', 'Solving mixed systems of equations', '2025-03-31 21:14:18'),
(218, 6, 'Linear graphs', 'Understanding and plotting straight lines', '2025-03-31 21:14:18'),
(219, 6, 'Quadratic graphs', 'Parabolas and their properties', '2025-03-31 21:14:18'),
(220, 6, 'Cubic and reciprocal graphs', 'Higher degree and reciprocal functions', '2025-03-31 21:14:18'),
(221, 6, 'Exponential graphs', 'Understanding exponential growth and decay', '2025-03-31 21:14:18'),
(222, 6, 'Graph transformations', 'Translations, reflections, and stretches', '2025-03-31 21:14:18'),
(223, 6, 'Coordinate geometry', 'Working with coordinates and equations', '2025-03-31 21:14:18'),
(224, 6, 'Perpendicular lines', 'Finding perpendicular line equations', '2025-03-31 21:14:18'),
(225, 6, 'Equation of a circle and tangents', 'Circle equations and their properties', '2025-03-31 21:14:18'),
(226, 6, 'Linear sequences', 'Arithmetic progressions and nth term', '2025-03-31 21:14:18'),
(227, 6, 'Quadratic and geometric sequences', 'More complex sequence types', '2025-03-31 21:14:18'),
(228, 7, 'Algebraic proof', 'Proving mathematical statements algebraically', '2025-03-31 21:14:18'),
(229, 7, 'Function notation', 'Understanding and using function notation', '2025-03-31 21:14:18'),
(230, 7, 'Inverse and composite functions', 'Working with function operations', '2025-03-31 21:14:18'),
(231, 7, 'Iterative methods', 'Finding solutions by iteration', '2025-03-31 21:14:18'),
(232, 7, 'Turning points', 'Finding and using maxima and minima', '2025-03-31 21:14:18'),
(233, 7, 'Completing the square', 'Using completing the square for various purposes', '2025-03-31 21:14:18'),
(234, 8, 'Simplifying ratios', 'Techniques for reducing ratios to their simplest form', '2025-03-31 21:14:18'),
(235, 8, 'Dividing quantities in a ratio', 'Solving problems involving sharing in a ratio', '2025-03-31 21:14:18'),
(236, 8, 'Multi-part ratios', 'Working with ratios involving three or more parts', '2025-03-31 21:14:18'),
(237, 8, 'Converting between fractions and ratios', 'Understanding the relationship between fractions and ratios', '2025-03-31 21:14:18'),
(238, 8, 'Problem solving with ratios', 'Applied ratio problems in real-world contexts', '2025-03-31 21:14:18'),
(239, 9, 'Percentage calculations', 'Basic percentage operations and conversions', '2025-03-31 21:14:18'),
(240, 9, 'Percentage increase and decrease', 'Calculating percentage changes', '2025-03-31 21:14:18'),
(241, 9, 'Reverse percentages', 'Finding original values from percentage changes', '2025-03-31 21:14:18'),
(242, 9, 'Compound percentage change', 'Multiple percentage changes', '2025-03-31 21:14:18'),
(243, 9, 'Simple and compound interest', 'Financial applications of percentages', '2025-03-31 21:14:18'),
(244, 9, 'Depreciation', 'Calculating value decrease over time', '2025-03-31 21:14:18'),
(245, 10, 'Direct proportion', 'Understanding direct relationships', '2025-03-31 21:14:18'),
(246, 10, 'Inverse proportion', 'Understanding inverse relationships', '2025-03-31 21:14:18'),
(247, 10, 'Graphs of proportion relationships', 'Visualizing proportional relationships', '2025-03-31 21:14:18'),
(248, 10, 'Rates of change', 'Understanding how quantities change relative to each other', '2025-03-31 21:14:18'),
(249, 10, 'Growth and decay problems', 'Applications of exponential change', '2025-03-31 21:14:18'),
(250, 11, 'Speed, distance and time', 'Understanding and using speed calculations', '2025-03-31 21:14:18'),
(251, 11, 'Density, mass and volume', 'Working with density relationships', '2025-03-31 21:14:18'),
(252, 11, 'Pressure, force and area', 'Understanding pressure calculations', '2025-03-31 21:14:18'),
(253, 11, 'Velocity-time graphs', 'Interpreting motion graphs', '2025-03-31 21:14:18'),
(254, 11, 'Gradient as rate of change', 'Understanding gradient in real contexts', '2025-03-31 21:14:18'),
(255, 11, 'Area under a graph', 'Finding distance from velocity-time graphs', '2025-03-31 21:14:18'),
(256, 12, 'Angle facts', 'Understanding angles in lines, triangles, and polygons', '2025-03-31 21:14:18'),
(257, 12, 'Properties of 2D shapes', 'Exploring characteristics of 2D shapes', '2025-03-31 21:14:18'),
(258, 12, 'Area and perimeter calculations', 'Finding perimeter and area of shapes', '2025-03-31 21:14:18'),
(259, 12, 'Circle terminology', 'Understanding parts of circles', '2025-03-31 21:14:18'),
(260, 12, 'Circumference and area of circles', 'Calculations with circles', '2025-03-31 21:14:18'),
(261, 12, 'Arc lengths and sectors', 'Working with parts of circles', '2025-03-31 21:14:18'),
(262, 12, 'Bearings', 'Three-figure bearings and navigation', '2025-03-31 21:14:18'),
(263, 13, 'Reflection', 'Mirror images and reflection lines', '2025-03-31 21:14:18'),
(264, 13, 'Rotation', 'Rotating shapes around points', '2025-03-31 21:14:18'),
(265, 13, 'Translation', 'Moving shapes using vectors', '2025-03-31 21:14:18'),
(266, 13, 'Enlargement', 'Positive and negative scale factors', '2025-03-31 21:14:18'),
(267, 13, 'Combined transformations', 'Multiple transformation sequences', '2025-03-31 21:14:18'),
(268, 14, 'Properties of 3D shapes', 'Understanding 3D shape characteristics', '2025-03-31 21:14:18'),
(269, 14, 'Volume and surface area', 'Calculations with 3D shapes', '2025-03-31 21:14:18'),
(270, 14, 'Plans and elevations', '2D representations of 3D objects', '2025-03-31 21:14:18'),
(271, 14, 'Prisms and cylinders', 'Properties and calculations', '2025-03-31 21:14:18'),
(272, 14, 'Cones, pyramids and spheres', 'Advanced 3D shape work', '2025-03-31 21:14:18'),
(273, 15, 'Congruence and similarity', 'Understanding shape relationships', '2025-03-31 21:14:18'),
(274, 15, 'Scale factors', 'Length, area, and volume relationships', '2025-03-31 21:14:18'),
(275, 15, 'Construction techniques', 'Geometric construction methods', '2025-03-31 21:14:18'),
(276, 15, 'Loci', 'Paths and regions', '2025-03-31 21:14:18'),
(277, 15, 'Circle theorems', 'Proving and applying circle properties', '2025-03-31 21:14:18'),
(278, 15, 'Vectors', 'Vector arithmetic and geometry', '2025-03-31 21:14:18'),
(279, 15, 'Geometric proof', 'Formal geometric reasoning', '2025-03-31 21:14:18'),
(280, 16, 'Pythagoras theorem', '2D and 3D applications', '2025-03-31 21:14:18'),
(281, 16, 'Trigonometric ratios', 'Sine, cosine, and tangent', '2025-03-31 21:14:18'),
(282, 16, 'Sine and cosine rules', 'Non-right-angled triangles', '2025-03-31 21:14:18'),
(283, 16, 'Area of a triangle', 'Using ½ab sin C', '2025-03-31 21:14:18'),
(284, 16, 'Exact trigonometric values', 'Standard angle values', '2025-03-31 21:14:18'),
(285, 16, '3D trigonometry', 'Trigonometry in three dimensions', '2025-03-31 21:14:18'),
(286, 16, 'Trigonometric graphs', 'Properties and transformations', '2025-03-31 21:14:18'),
(287, 17, 'Probability scale and notation', 'Understanding probability basics', '2025-03-31 21:14:18'),
(288, 17, 'Mutually exclusive events', 'Events that cannot occur together', '2025-03-31 21:14:18'),
(289, 17, 'Exhaustive events', 'Complete set of possible outcomes', '2025-03-31 21:14:18'),
(290, 17, 'Theoretical probability', 'Calculating expected probabilities', '2025-03-31 21:14:18'),
(291, 17, 'Experimental probability', 'Observed frequencies', '2025-03-31 21:14:18'),
(292, 17, 'Relative frequency', 'Long-run probability', '2025-03-31 21:14:18'),
(293, 18, 'Probability trees (independent events)', 'Tree diagrams for independent events', '2025-03-31 21:14:18'),
(294, 18, 'Probability trees (dependent events)', 'Tree diagrams for dependent events', '2025-03-31 21:14:18'),
(295, 18, 'Venn diagrams', 'Set notation and probability', '2025-03-31 21:14:18'),
(296, 18, 'Set notation', 'Mathematical notation for sets', '2025-03-31 21:14:18'),
(297, 18, 'Two-way tables', 'Organizing probability data', '2025-03-31 21:14:18'),
(298, 18, 'Frequency trees', 'Representing frequency relationships', '2025-03-31 21:14:18'),
(299, 19, 'Conditional probability', 'Probability given conditions', '2025-03-31 21:14:18'),
(300, 19, 'Combined events', 'Multiple event probability', '2025-03-31 21:14:18'),
(301, 19, 'Probability equations', 'Mathematical probability rules', '2025-03-31 21:14:18'),
(302, 19, 'Expected outcomes', 'Calculating expected values', '2025-03-31 21:14:18'),
(303, 20, 'Tables and charts', 'Different ways to present data', '2025-03-31 21:14:18'),
(304, 20, 'Pie charts', 'Circular representation of data', '2025-03-31 21:14:18'),
(305, 20, 'Stem and leaf diagrams', 'Ordered data presentation', '2025-03-31 21:14:18'),
(306, 20, 'Frequency polygons', 'Line graphs for frequency', '2025-03-31 21:14:18'),
(307, 20, 'Scatter graphs and correlation', 'Relationships between variables', '2025-03-31 21:14:18'),
(308, 20, 'Time series graphs', 'Data trends over time', '2025-03-31 21:14:18'),
(309, 21, 'Averages', 'Mean, median, and mode', '2025-03-31 21:14:18'),
(310, 21, 'Range and interquartile range', 'Measures of spread', '2025-03-31 21:14:18'),
(311, 21, 'Calculating from frequency tables', 'Working with grouped data', '2025-03-31 21:14:18'),
(312, 21, 'Box plots', 'Displaying data distribution', '2025-03-31 21:14:18'),
(313, 21, 'Cumulative frequency graphs', 'Running totals of frequency', '2025-03-31 21:14:18'),
(314, 21, 'Histograms', 'Equal and unequal class widths', '2025-03-31 21:14:18'),
(315, 22, 'Sampling methods', 'Different ways to collect data', '2025-03-31 21:14:18'),
(316, 22, 'Bias in sampling', 'Understanding data collection issues', '2025-03-31 21:14:18'),
(317, 22, 'Comparing distributions', 'Analyzing different data sets', '2025-03-31 21:14:18'),
(318, 22, 'Interpreting statistical measures', 'Understanding data analysis', '2025-03-31 21:14:18'),
(319, 22, 'Drawing conclusions from data', 'Making statistical inferences', '2025-03-31 21:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `overall_progress`
--

CREATE TABLE `overall_progress` (
  `id` int(11) NOT NULL,
  `total_sections` int(11) DEFAULT 0,
  `total_subsections` int(11) DEFAULT 0,
  `total_topics` int(11) DEFAULT 0,
  `completed_topics` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `total_study_time` int(11) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overall_progress`
--

INSERT INTO `overall_progress` (`id`, `total_sections`, `total_subsections`, `total_topics`, `completed_topics`, `progress_percentage`, `total_study_time`, `last_updated`) VALUES
(1, 6, 22, 48, 0, 0.00, 30, '2025-03-28 05:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `subject_id`, `title`, `type`, `link`, `notes`) VALUES
(1, 1, 'English Language Revision Guide', 'book', NULL, 'CGP revision guide with practice questions'),
(2, 1, 'Macbeth Analysis Video', 'video', 'https://example.com/macbeth', 'Mr. Bruff analysis of key scenes'),
(3, 2, 'Algebra Cheat Sheet', 'document', 'https://example.com/algebra', 'Formula reference sheet'),
(4, 2, 'Corbett Maths', 'website', 'https://corbettmaths.com', 'Great for practice questions and video explanations');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `planned_date` date NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `priority` varchar(10) DEFAULT 'medium',
  `completed` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section_progress`
--

CREATE TABLE `section_progress` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `total_subsections` int(11) DEFAULT 0,
  `total_topics` int(11) DEFAULT 0,
  `completed_topics` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `total_time_spent_seconds` bigint(20) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `section_progress`
--
DELIMITER $$
CREATE TRIGGER `update_overall_progress` AFTER UPDATE ON `section_progress` FOR EACH ROW BEGIN
    UPDATE overall_progress
    SET 
        completed_topics = (
            SELECT SUM(completed_topics) 
            FROM section_progress
        ),
        progress_percentage = (
            SELECT SUM(completed_topics) * 100.0 / SUM(total_topics)
            FROM section_progress
        ),
        total_study_time = (
            SELECT COALESCE(SUM(total_time_spent), 0)
            FROM topic_progress
        ),
        last_updated = CURRENT_TIMESTAMP
    WHERE id = 1;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `subject_id`, `date`, `duration`, `notes`) VALUES
(1, 1, '2025-03-15', 60, 'Worked on Shakespeare quotes'),
(2, 1, '2025-03-18', 45, 'Practiced creative writing'),
(3, 2, '2025-03-16', 90, 'Solved quadratic equations'),
(4, 2, '2025-03-20', 75, 'Reviewed trigonometry');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `theme` varchar(10) DEFAULT 'light',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `theme`, `last_updated`) VALUES
(1, 'light', '2025-03-27 04:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `study_time_tracking`
--

CREATE TABLE `study_time_tracking` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `status` enum('active','paused','completed') DEFAULT 'active',
  `last_pause_time` datetime DEFAULT NULL,
  `accumulated_seconds` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT '#007bff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `color`) VALUES
(1, 'English', '#28a745'),
(2, 'Math', '#dc3545');

-- --------------------------------------------------------

--
-- Table structure for table `subsection_progress`
--

CREATE TABLE `subsection_progress` (
  `id` int(11) NOT NULL,
  `subsection_id` int(11) NOT NULL,
  `total_topics` int(11) DEFAULT 0,
  `completed_topics` int(11) DEFAULT 0,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `total_time_spent_seconds` bigint(20) DEFAULT 0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `parent_task_id` int(11) DEFAULT NULL COMMENT 'For subtasks - references parent task',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `task_type` enum('one-time','recurring') NOT NULL DEFAULT 'one-time',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `estimated_duration` int(11) DEFAULT 0 COMMENT 'Estimated duration in minutes',
  `due_date` date DEFAULT NULL,
  `due_time` time DEFAULT NULL,
  `status` enum('pending','in_progress','completed','not_done','snoozed') DEFAULT 'pending',
  `completion_percentage` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `category_id`, `parent_task_id`, `title`, `description`, `task_type`, `priority`, `estimated_duration`, `due_date`, `due_time`, `status`, `completion_percentage`, `is_active`, `created_at`, `updated_at`) VALUES
(51, 1, NULL, 'Go church ', 'Kidase', '', 'high', 120, '2025-04-05', '10:00:00', 'pending', 0.00, 1, '2025-04-05 01:35:37', '2025-04-05 02:57:07'),
(52, 4, NULL, 'Review Chapter 1 Math Notes', 'Focus on algebra section.', 'one-time', 'high', 60, '2025-03-29', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(53, 5, NULL, 'Plan Weekly Meals', 'Include healthy options.', 'one-time', 'medium', 30, '2025-03-31', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(54, 6, NULL, 'Submit Access Assignment Draft', 'Unit 10 draft - AI/ML.', 'one-time', 'high', 120, '2025-04-02', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(55, 3, NULL, 'Call Utility Company', 'Query recent bill amount.', 'one-time', 'low', 15, '2025-04-03', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(56, 1, NULL, 'Read Spiritual Text', 'Morning reflection reading.', 'one-time', 'medium', 30, '2025-04-04', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(57, 4, NULL, 'Attend English Revision Webinar', 'Focus on Paper 1 techniques.', 'one-time', 'high', 90, '2025-04-05', NULL, 'not_done', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 03:01:42'),
(58, 5, NULL, 'Go for a 30-min Walk', 'During lunch break.', 'one-time', 'medium', 30, '2025-04-05', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(59, 3, NULL, 'Work on Task Management UI', 'Implement date filtering.', 'one-time', 'high', 180, '2025-04-05', NULL, 'completed', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 03:01:17'),
(60, 4, NULL, 'Outline Math Paper 1 Strategy', 'Non-calculator approach.', 'one-time', 'medium', 45, '2025-04-05', NULL, 'completed', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 03:22:48'),
(61, 3, NULL, 'Update Project Documentation', 'Add details about security middleware.', 'one-time', 'low', 60, '2025-04-05', NULL, 'not_done', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 03:15:14'),
(62, 2, NULL, 'Prepare Presentation Slides', 'For Self-Development group.', 'one-time', 'medium', 90, '2025-04-07', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(63, 4, NULL, 'Complete Math Practice Paper 2', 'Calculator paper.', 'one-time', 'high', 120, '2025-04-10', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(64, 5, NULL, 'Schedule Dentist Appointment', 'Routine check-up.', 'one-time', 'low', 10, '2025-04-12', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(65, 6, NULL, 'Research University Open Days', 'Check dates for preferred unis.', 'one-time', 'medium', 60, '2025-04-15', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(66, 1, NULL, 'Meditate for 15 minutes', 'Evening mindfulness session.', 'one-time', 'medium', 15, '2025-04-06', NULL, 'pending', 0.00, 1, '2025-04-05 02:28:45', '2025-04-05 02:28:45'),
(67, 6, NULL, 'Abel', 'wow', 'one-time', 'high', 10, '2025-04-05', '06:32:02', 'completed', 0.00, 1, '2025-04-05 02:52:52', '2025-04-06 01:45:49'),
(68, 3, NULL, 'Google', '', 'one-time', 'medium', 0, '2025-04-05', '06:33:39', 'completed', 0.00, 1, '2025-04-05 02:54:28', '2025-04-06 01:45:53'),
(69, 9, NULL, 'Google AI', '', 'one-time', 'high', 20, '2025-04-05', '05:10:00', 'not_done', 0.00, 1, '2025-04-05 03:10:43', '2025-04-05 03:54:41'),
(70, 3, NULL, 'Oh I see', '', '', 'medium', 0, '2025-04-05', '06:33:00', 'pending', 0.00, 1, '2025-04-05 03:34:00', '2025-04-05 03:34:00'),
(71, 2, NULL, 'YES ', '', 'recurring', 'high', 0, '2025-04-05', '07:50:00', 'pending', 0.00, 1, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(72, 9, NULL, 'Mukera', '', 'one-time', 'medium', 16, '2025-04-06', '05:33:26', 'completed', 0.00, 1, '2025-04-06 03:18:09', '2025-04-06 03:18:42');

-- --------------------------------------------------------

--
-- Table structure for table `task_categories`
--

CREATE TABLE `task_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-tasks' COMMENT 'Font Awesome icon class name',
  `color` varchar(7) DEFAULT '#6c757d',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_categories`
--

INSERT INTO `task_categories` (`id`, `name`, `description`, `icon`, `color`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Spiritual Life', 'Religious and spiritual activities', 'fas fa-pray', '#e6d305', 1, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(2, 'Self-Development', 'Personal growth and learning', 'fas fa-brain', '#9370DB', 2, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(3, 'Productivity', 'Task management and planning', 'fas fa-tasks', '#4682B4', 3, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(4, 'Study', 'Academic work and revision', 'fas fa-book-reader', '#1E90FF', 4, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(5, 'Health', 'Physical health and fitness', 'fas fa-heartbeat', '#32CD32', 5, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(6, 'Study', 'Access to Higher Education coursework', 'fas fa-trophy', '#2ce2d6', 6, 1, '2025-04-02 19:13:35', '2025-04-04 13:02:25'),
(9, 'Education', 'General educational activities', 'fas fa-school', '#4169E1', 9, 1, '2025-04-02 19:13:35', '2025-04-02 19:13:35'),
(10, 'Uncategorized', NULL, 'fas fa-folder', '#6c757d', 0, 1, '2025-04-02 23:56:40', '2025-04-02 23:56:40');

-- --------------------------------------------------------

--
-- Table structure for table `task_checklist_items`
--

CREATE TABLE `task_checklist_items` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_instances`
--

CREATE TABLE `task_instances` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `due_time` time DEFAULT NULL,
  `status` enum('pending','in_progress','completed','not_done','snoozed') DEFAULT 'pending',
  `time_spent` int(11) DEFAULT 0 COMMENT 'Time spent in minutes',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_instances`
--

INSERT INTO `task_instances` (`id`, `task_id`, `due_date`, `due_time`, `status`, `time_spent`, `notes`, `created_at`, `updated_at`) VALUES
(259, 71, '2025-04-05', '07:50:00', 'completed', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:49:26'),
(260, 71, '2025-04-09', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(261, 71, '2025-04-11', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(262, 71, '2025-04-12', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(263, 71, '2025-04-16', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(264, 71, '2025-04-18', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(265, 71, '2025-04-19', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(266, 71, '2025-04-23', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(267, 71, '2025-04-25', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(268, 71, '2025-04-26', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(269, 71, '2025-04-30', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(270, 71, '2025-05-02', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12'),
(271, 71, '2025-05-03', '07:50:00', 'pending', 0, NULL, '2025-04-05 03:48:12', '2025-04-05 03:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `task_recurrence_rules`
--

CREATE TABLE `task_recurrence_rules` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL,
  `times_per_period` int(11) NOT NULL DEFAULT 1,
  `specific_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of days ["monday", "wednesday", "friday"]' CHECK (json_valid(`specific_days`)),
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `last_generated_date` date DEFAULT NULL COMMENT 'Track last instance generation',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_recurrence_rules`
--

INSERT INTO `task_recurrence_rules` (`id`, `task_id`, `frequency`, `times_per_period`, `specific_days`, `start_date`, `end_date`, `last_generated_date`, `is_active`, `created_at`, `updated_at`) VALUES
(15, 71, 'weekly', 1, '[3,5,6]', '2025-04-05', NULL, NULL, 1, '2025-04-05 03:48:12', '2025-04-05 03:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `task_tags`
--

CREATE TABLE `task_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#6c757d',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_tag_relations`
--

CREATE TABLE `task_tag_relations` (
  `task_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_time_logs`
--

CREATE TABLE `task_time_logs` (
  `id` int(11) NOT NULL,
  `task_instance_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0 COMMENT 'Duration in minutes',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topic_images`
--

CREATE TABLE `topic_images` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topic_notes`
--

CREATE TABLE `topic_notes` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `edited_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic_notes`
--

INSERT INTO `topic_notes` (`id`, `topic_id`, `content`, `created_at`, `updated_at`, `edited_at`) VALUES
(11, 186, '<p>This is very nice! i like it </p>', '2025-03-31 22:07:53', '2025-03-31 22:07:53', '2025-03-31 22:07:53');

-- --------------------------------------------------------

--
-- Table structure for table `topic_progress`
--

CREATE TABLE `topic_progress` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `total_time_spent` int(11) DEFAULT 0,
  `confidence_level` int(11) DEFAULT 0,
  `last_studied` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic_progress`
--

INSERT INTO `topic_progress` (`id`, `topic_id`, `status`, `total_time_spent`, `confidence_level`, `last_studied`, `completion_date`, `notes`) VALUES
(84, 186, 'not_started', 0, 0, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `topic_questions`
--

CREATE TABLE `topic_questions` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `status` enum('pending','answered') DEFAULT 'pending',
  `answer` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic_questions`
--

INSERT INTO `topic_questions` (`id`, `topic_id`, `question`, `status`, `answer`, `created_at`, `edited_at`, `is_correct`) VALUES
(10, 186, '<p>1+1</p>', 'pending', '<p>2</p>', '2025-03-31 22:08:03', '2025-03-31 22:08:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `topic_ratings`
--

CREATE TABLE `topic_ratings` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topic_resources`
--

CREATE TABLE `topic_resources` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `resource_type` enum('youtube','image') NOT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(50) DEFAULT NULL,
  `unit_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  `is_graded` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `unit_code`, `unit_name`, `description`, `credits`, `is_graded`, `created_at`) VALUES
(7, 'UNIT001', 'AI and Machine Learning', 'Introduction to AI, Machine Learning and Deep Learning', 20, 1, '2025-04-01 17:40:06');

-- --------------------------------------------------------

--
-- Structure for view `habit_performance_view`
--
DROP TABLE IF EXISTS `habit_performance_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `habit_performance_view`  AS SELECT `h`.`id` AS `id`, `h`.`name` AS `name`, `hc`.`name` AS `category`, `hpr`.`name` AS `point_rule`, `h`.`current_points` AS `current_points`, `h`.`total_completions` AS `total_completions`, `h`.`total_procrastinated` AS `total_procrastinated`, `h`.`total_skips` AS `total_skips`, round((`h`.`total_completions` * `hpr`.`completion_points` + `h`.`total_procrastinated` * `hpr`.`procrastinated_points`) / nullif(`h`.`total_completions` + `h`.`total_procrastinated` + `h`.`total_skips`,0),2) AS `average_points_per_day`, `h`.`success_rate` AS `success_rate`, `h`.`current_streak` AS `current_streak`, `h`.`longest_streak` AS `longest_streak` FROM ((`habits` `h` join `habit_categories` `hc` on(`h`.`category_id` = `hc`.`id`)) join `habit_point_rules` `hpr` on(`h`.`point_rule_id` = `hpr`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_assignments`
--
ALTER TABLE `access_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `access_assignments_ibfk_1` (`unit_id`);

--
-- Indexes for table `access_course_units`
--
ALTER TABLE `access_course_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `assignment_criteria_progress`
--
ALTER TABLE `assignment_criteria_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `assignment_guidance`
--
ALTER TABLE `assignment_guidance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `assignment_progress`
--
ALTER TABLE `assignment_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `assignment_progress_log`
--
ALTER TABLE `assignment_progress_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `assignment_resources`
--
ALTER TABLE `assignment_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eng_sections`
--
ALTER TABLE `eng_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eng_section_progress`
--
ALTER TABLE `eng_section_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_id` (`section_id`);

--
-- Indexes for table `eng_study_time_tracking`
--
ALTER TABLE `eng_study_time_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `eng_subsections`
--
ALTER TABLE `eng_subsections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `eng_subsection_progress`
--
ALTER TABLE `eng_subsection_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subsection_id` (`subsection_id`);

--
-- Indexes for table `eng_topics`
--
ALTER TABLE `eng_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subsection_id` (`subsection_id`);

--
-- Indexes for table `eng_topic_progress`
--
ALTER TABLE `eng_topic_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `topic_id` (`topic_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `exam_reports`
--
ALTER TABLE `exam_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `point_rule_id` (`point_rule_id`);

--
-- Indexes for table `habit_categories`
--
ALTER TABLE `habit_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `habit_completions`
--
ALTER TABLE `habit_completions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_habit_date` (`habit_id`,`completion_date`);

--
-- Indexes for table `habit_point_rules`
--
ALTER TABLE `habit_point_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `habit_progress`
--
ALTER TABLE `habit_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_habit_date` (`habit_id`,`date`);

--
-- Indexes for table `habit_reasons`
--
ALTER TABLE `habit_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `math_sections`
--
ALTER TABLE `math_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `math_subsections`
--
ALTER TABLE `math_subsections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `math_topics`
--
ALTER TABLE `math_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subsection_id` (`subsection_id`);

--
-- Indexes for table `overall_progress`
--
ALTER TABLE `overall_progress`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `section_progress`
--
ALTER TABLE `section_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_id` (`section_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_time_tracking`
--
ALTER TABLE `study_time_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subsection_progress`
--
ALTER TABLE `subsection_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subsection_id` (`subsection_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `parent_task_id` (`parent_task_id`);

--
-- Indexes for table `task_categories`
--
ALTER TABLE `task_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `task_instances`
--
ALTER TABLE `task_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `task_recurrence_rules`
--
ALTER TABLE `task_recurrence_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `task_tags`
--
ALTER TABLE `task_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_tag_relations`
--
ALTER TABLE `task_tag_relations`
  ADD PRIMARY KEY (`task_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `task_time_logs`
--
ALTER TABLE `task_time_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_instance_id` (`task_instance_id`);

--
-- Indexes for table `topic_images`
--
ALTER TABLE `topic_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `topic_notes`
--
ALTER TABLE `topic_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `topic_progress`
--
ALTER TABLE `topic_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `topic_id` (`topic_id`);

--
-- Indexes for table `topic_questions`
--
ALTER TABLE `topic_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `topic_ratings`
--
ALTER TABLE `topic_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_topic_rating` (`topic_id`);

--
-- Indexes for table `topic_resources`
--
ALTER TABLE `topic_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_assignments`
--
ALTER TABLE `access_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `access_course_units`
--
ALTER TABLE `access_course_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `assignment_criteria_progress`
--
ALTER TABLE `assignment_criteria_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `assignment_guidance`
--
ALTER TABLE `assignment_guidance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignment_progress`
--
ALTER TABLE `assignment_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment_progress_log`
--
ALTER TABLE `assignment_progress_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignment_resources`
--
ALTER TABLE `assignment_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `eng_sections`
--
ALTER TABLE `eng_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `eng_section_progress`
--
ALTER TABLE `eng_section_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `eng_study_time_tracking`
--
ALTER TABLE `eng_study_time_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eng_subsections`
--
ALTER TABLE `eng_subsections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `eng_subsection_progress`
--
ALTER TABLE `eng_subsection_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `eng_topics`
--
ALTER TABLE `eng_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `eng_topic_progress`
--
ALTER TABLE `eng_topic_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exam_reports`
--
ALTER TABLE `exam_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `habit_categories`
--
ALTER TABLE `habit_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `habit_completions`
--
ALTER TABLE `habit_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `habit_point_rules`
--
ALTER TABLE `habit_point_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `habit_progress`
--
ALTER TABLE `habit_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `habit_reasons`
--
ALTER TABLE `habit_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `math_sections`
--
ALTER TABLE `math_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `math_subsections`
--
ALTER TABLE `math_subsections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `math_topics`
--
ALTER TABLE `math_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=320;

--
-- AUTO_INCREMENT for table `overall_progress`
--
ALTER TABLE `overall_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section_progress`
--
ALTER TABLE `section_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `study_time_tracking`
--
ALTER TABLE `study_time_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subsection_progress`
--
ALTER TABLE `subsection_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `task_categories`
--
ALTER TABLE `task_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_instances`
--
ALTER TABLE `task_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `task_recurrence_rules`
--
ALTER TABLE `task_recurrence_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `task_tags`
--
ALTER TABLE `task_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_time_logs`
--
ALTER TABLE `task_time_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topic_images`
--
ALTER TABLE `topic_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topic_notes`
--
ALTER TABLE `topic_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `topic_progress`
--
ALTER TABLE `topic_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `topic_questions`
--
ALTER TABLE `topic_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `topic_ratings`
--
ALTER TABLE `topic_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `topic_resources`
--
ALTER TABLE `topic_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_assignments`
--
ALTER TABLE `access_assignments`
  ADD CONSTRAINT `access_assignments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `access_course_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assessment_criteria`
--
ALTER TABLE `assessment_criteria`
  ADD CONSTRAINT `assessment_criteria_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_criteria_progress`
--
ALTER TABLE `assignment_criteria_progress`
  ADD CONSTRAINT `assignment_criteria_progress_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_criteria_progress_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `assessment_criteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_guidance`
--
ALTER TABLE `assignment_guidance`
  ADD CONSTRAINT `assignment_guidance_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_progress`
--
ALTER TABLE `assignment_progress`
  ADD CONSTRAINT `assignment_progress_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_progress_ibfk_2` FOREIGN KEY (`criteria_id`) REFERENCES `assessment_criteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_progress_log`
--
ALTER TABLE `assignment_progress_log`
  ADD CONSTRAINT `progress_log_assignment_fk` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_resources`
--
ALTER TABLE `assignment_resources`
  ADD CONSTRAINT `assignment_resources_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `access_assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_section_progress`
--
ALTER TABLE `eng_section_progress`
  ADD CONSTRAINT `eng_section_progress_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `eng_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_study_time_tracking`
--
ALTER TABLE `eng_study_time_tracking`
  ADD CONSTRAINT `eng_study_time_tracking_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `eng_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_subsections`
--
ALTER TABLE `eng_subsections`
  ADD CONSTRAINT `eng_subsections_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `eng_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_subsection_progress`
--
ALTER TABLE `eng_subsection_progress`
  ADD CONSTRAINT `eng_subsection_progress_ibfk_1` FOREIGN KEY (`subsection_id`) REFERENCES `eng_subsections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_topics`
--
ALTER TABLE `eng_topics`
  ADD CONSTRAINT `eng_topics_ibfk_1` FOREIGN KEY (`subsection_id`) REFERENCES `eng_subsections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eng_topic_progress`
--
ALTER TABLE `eng_topic_progress`
  ADD CONSTRAINT `eng_topic_progress_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `eng_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exam_reports`
--
ALTER TABLE `exam_reports`
  ADD CONSTRAINT `exam_reports_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `habits_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `habit_categories` (`id`),
  ADD CONSTRAINT `habits_ibfk_2` FOREIGN KEY (`point_rule_id`) REFERENCES `habit_point_rules` (`id`);

--
-- Constraints for table `habit_completions`
--
ALTER TABLE `habit_completions`
  ADD CONSTRAINT `habit_completions_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`);

--
-- Constraints for table `habit_progress`
--
ALTER TABLE `habit_progress`
  ADD CONSTRAINT `habit_progress_ibfk_1` FOREIGN KEY (`habit_id`) REFERENCES `habits` (`id`);

--
-- Constraints for table `math_subsections`
--
ALTER TABLE `math_subsections`
  ADD CONSTRAINT `math_subsections_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `math_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `math_topics`
--
ALTER TABLE `math_topics`
  ADD CONSTRAINT `math_topics_ibfk_1` FOREIGN KEY (`subsection_id`) REFERENCES `math_subsections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `section_progress`
--
ALTER TABLE `section_progress`
  ADD CONSTRAINT `section_progress_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `math_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `study_time_tracking`
--
ALTER TABLE `study_time_tracking`
  ADD CONSTRAINT `study_time_tracking_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subsection_progress`
--
ALTER TABLE `subsection_progress`
  ADD CONSTRAINT `subsection_progress_ibfk_1` FOREIGN KEY (`subsection_id`) REFERENCES `math_subsections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `task_categories` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_checklist_items`
--
ALTER TABLE `task_checklist_items`
  ADD CONSTRAINT `task_checklist_items_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_instances`
--
ALTER TABLE `task_instances`
  ADD CONSTRAINT `task_instances_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_recurrence_rules`
--
ALTER TABLE `task_recurrence_rules`
  ADD CONSTRAINT `task_recurrence_rules_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_tag_relations`
--
ALTER TABLE `task_tag_relations`
  ADD CONSTRAINT `task_tag_relations_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_tag_relations_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `task_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_time_logs`
--
ALTER TABLE `task_time_logs`
  ADD CONSTRAINT `task_time_logs_ibfk_1` FOREIGN KEY (`task_instance_id`) REFERENCES `task_instances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_images`
--
ALTER TABLE `topic_images`
  ADD CONSTRAINT `topic_images_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_notes`
--
ALTER TABLE `topic_notes`
  ADD CONSTRAINT `topic_notes_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_progress`
--
ALTER TABLE `topic_progress`
  ADD CONSTRAINT `topic_progress_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_questions`
--
ALTER TABLE `topic_questions`
  ADD CONSTRAINT `topic_questions_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_ratings`
--
ALTER TABLE `topic_ratings`
  ADD CONSTRAINT `topic_ratings_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `topic_resources`
--
ALTER TABLE `topic_resources`
  ADD CONSTRAINT `topic_resources_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `math_topics` (`id`) ON DELETE CASCADE;
--
-- Database: `geez_restaurant`
--
CREATE DATABASE IF NOT EXISTS `geez_restaurant` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `geez_restaurant`;

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_locations`
--

CREATE TABLE `cleaning_locations` (
  `location_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cleaning_locations`
--

INSERT INTO `cleaning_locations` (`location_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Kitchen', 'Hello feven', 1, '2025-06-10 12:00:00', '2025-06-10 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_log`
--

CREATE TABLE `cleaning_log` (
  `log_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `completed_date` date NOT NULL,
  `completed_time` time NOT NULL,
  `completed_by_user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_by_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cleaning_task`
--

CREATE TABLE `cleaning_task` (
  `task_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `content` varchar(255) DEFAULT NULL COMMENT 'Typical content stored in the equipment',
  `quantity_in_stock` int(11) DEFAULT NULL COMMENT 'Current quantity of items in stock',
  `min_stock_quantity` int(11) DEFAULT NULL COMMENT 'Minimum desired stock quantity',
  `min_temp` decimal(5,1) NOT NULL,
  `max_temp` decimal(5,1) NOT NULL,
  `check_frequency` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_waste_log`
--

CREATE TABLE `food_waste_log` (
  `waste_id` int(11) NOT NULL,
  `food_item` varchar(100) NOT NULL,
  `waste_type` varchar(50) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `weight_kg` decimal(8,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `waste_date` date NOT NULL,
  `action_taken` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Geez Restaurant', '2025-04-09 02:58:10', NULL),
(2, 'company_address', '123 Main Street, Anytown, USA 12345', '2025-04-09 02:58:10', NULL),
(3, 'date_format', 'Y-m-d', '2025-04-09 02:58:10', NULL),
(4, 'time_format', 'H:i', '2025-04-09 02:58:10', NULL),
(5, 'temperature_unit', 'C', '2025-04-09 02:58:10', NULL),
(6, 'weight_unit', 'kg', '2025-04-09 02:58:10', NULL),
(7, 'currency_symbol', '$', '2025-04-09 02:58:10', NULL),
(8, 'items_per_page', '20', '2025-04-09 02:58:10', NULL),
(9, 'installation_date', '2025-04-09', '2025-04-09 02:58:10', NULL),
(10, 'company_phone', '(555) 123-4567', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(11, 'company_email', 'info@geezrestaurant.com', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(12, 'enable_notifications', '0', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(13, 'maintenance_mode', '0', '2025-04-09 03:00:31', '2025-04-09 03:00:31'),
(14, 'sample_data_installed', '1', '2025-04-09 03:00:31', '2025-04-09 03:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Geez Restaurant Food Hygiene & Safety Management System', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(2, 'company_name', 'Geez Restaurant', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(3, 'temperature_unit', 'C', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(4, 'date_format', 'd/m/Y', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(5, 'time_format', 'H:i', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(6, 'items_per_page', '20', '2025-04-09 02:58:24', '2025-04-09 02:58:24'),
(7, 'enable_email_notifications', '0', '2025-04-09 02:58:24', '2025-04-09 02:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `temperature_checks`
--

CREATE TABLE `temperature_checks` (
  `check_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `temperature` decimal(5,1) NOT NULL,
  `is_compliant` tinyint(1) NOT NULL,
  `corrective_action` text DEFAULT NULL,
  `check_date` date NOT NULL,
  `check_time` time NOT NULL,
  `checked_by_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `initials` varchar(10) DEFAULT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `initials`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$2W/ck3zFb1F1Yu.44nxnwOlYiTVl4JubyQPAQIoS2NSgDKF8dywym', 'Abel Demssie', NULL, 'Ab', 'admin', 1, '2025-04-15 23:40:37', '2025-04-09 02:58:10', NULL),
(4, 'Michael', '$2y$10$jDjUQsrJQPSAH2WoferrmufqrYXkdaq6kHGdEy7im.ZOb6FfAxOCy', 'Michael Werkneh', NULL, 'RM', 'admin', 1, NULL, '2025-04-09 03:00:30', NULL),
(5, 'Ruth', '$2y$10$r2XhskKlz0kcWJFpMSQS/uouFKhC9.qlwodlnNtPF/JGZGI1IZgUK', 'Ruth Alemu', NULL, 'HC', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(6, 'Mahlet', '$2y$10$.cQJe3OkedhEwv/SBQ8NB.MLuOUjZqoxh2OW9gWPIDhcIekZ9FtKW', 'Mahlet Zerfu', NULL, 'KS', 'staff', 1, NULL, '2025-04-09 03:00:30', NULL),
(7, 'Yonas', '$2y$10$H31N0s2wkPuJkiRBr3pqNesDjK7BoeEMk2UYG2LDp6Bz2viMmksBm', 'Kibrom Zenebe', NULL, 'SS', 'staff', 0, NULL, '2025-04-09 03:00:30', NULL),
(8, 'sara', '$2y$10$jEfGGOqO37M10Mnbfr8KmO.YHK/Fi2qc7/tYF4kcmoBAMpYI6TjJO', 'Sara Teshome', NULL, NULL, 'manager', 1, NULL, '0000-00-00 00:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cleaning_locations`
--
ALTER TABLE `cleaning_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `completed_by_user_id` (`completed_by_user_id`),
  ADD KEY `verified_by_user_id` (`verified_by_user_id`);

--
-- Indexes for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD PRIMARY KEY (`waste_id`),
  ADD KEY `recorded_by_user_id` (`recorded_by_user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  ADD PRIMARY KEY (`check_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `checked_by_user_id` (`checked_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cleaning_locations`
--
ALTER TABLE `cleaning_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  MODIFY `waste_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cleaning_log`
--
ALTER TABLE `cleaning_log`
  ADD CONSTRAINT `cleaning_log_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `cleaning_task` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cleaning_log_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `cleaning_locations` (`location_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cleaning_log_ibfk_3` FOREIGN KEY (`completed_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cleaning_log_ibfk_4` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cleaning_task`
--
ALTER TABLE `cleaning_task`
  ADD CONSTRAINT `cleaning_task_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `cleaning_locations` (`location_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_waste_log`
--
ALTER TABLE `food_waste_log`
  ADD CONSTRAINT `food_waste_log_ibfk_1` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `temperature_checks`
--
ALTER TABLE `temperature_checks`
  ADD CONSTRAINT `temperature_checks_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temperature_checks_ibfk_2` FOREIGN KEY (`checked_by_user_id`) REFERENCES `users` (`user_id`);
--
-- Database: `habeshaequb`
--
CREATE DATABASE IF NOT EXISTS `habeshaequb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `habeshaequb`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `phone`, `password`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'Abeldemssie', NULL, NULL, '$2y$12$t42lLluGvefREVG44PN20.Ar4fdU8aEzadsvzV7BYn/gVM3zzArjW', 1, '2025-07-22 08:19:10', '2025-07-22 08:19:24'),
(4, 'abela', NULL, NULL, '$2y$12$KJjtNQ0EBbCS8x7sp77eJuzgBjDzTseNZoD6Mk5XGSgM39hfHODFy', 1, '2025-07-24 21:19:50', '2025-07-24 21:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `equb_rules`
--

CREATE TABLE `equb_rules` (
  `id` int(11) NOT NULL,
  `rule_number` int(11) NOT NULL,
  `rule_en` text NOT NULL,
  `rule_am` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equb_rules`
--

INSERT INTO `equb_rules` (`id`, `rule_number`, `rule_en`, `rule_am`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly payments are due on the 1st day of each month.', 'ሁሉም አባላት እቁባቸውን በወሩ የመጀመሪያ ቀን መክፈል አለባቸው', 1, '2025-07-22 21:51:08', '2025-07-22 21:59:41'),
(2, 2, 'If you are unable to pay on time due to an emergency, you must notify the admin as soon as possible. An extension of up to two additional days may be granted.', 'አባላቶች ከአቅም በላይ በሆነ ጉዳይ በሰዓቱ መክፈል ካልቻሉ ለሰብሳቢው ቀድመው ማሳወቅ አለባቸው፣ ይሄም እቁቡን ለመክፈል ተጨማሪ 2 ቀናትን እንዲያገኙ ያስችልዎታል', 1, '2025-07-22 22:22:15', '2025-07-22 22:22:15'),
(3, 3, 'If payment is not received within this grace period, a late fee of £20 will be charged automatically.', 'እቁቡን በሰአቱ ካልከፈሉ ተጨማሪ £20 ቅጣት ይከፍላሉ', 1, '2025-07-22 22:23:35', '2025-07-22 22:23:35'),
(4, 4, 'Each member receives their full payout on the 5th day of the month.', 'አባላቶች ወር በገባ በአምስተኛው ቀን እቁባቸውን የሚወስዱ ይሆናል', 1, '2025-07-22 22:24:32', '2025-07-22 22:24:32'),
(5, 5, 'A £10 service fee will be deducted from each payout.', 'ሁሉም አባል ተራው ደርሶ እቁብ ሲወስድ ለእቁብ ስራ ማስኬጃ የሚውል £10 ይቀነስበታል', 1, '2025-07-22 22:26:27', '2025-07-22 22:26:27'),
(6, 6, 'Once your payout turn is assigned, it cannot be changed.\r\nIf you must request a change, you must notify the admin at least 3 weeks in advance.', 'አንዴ እቁብ የሚወስዱበት ቀን ከታወቀ በኋላ መቀየር አይቻልም፣ ግዴታ መቀየር አስፈላጊ ሆኖ ከተገኘ ለሰብሳቢው ቢያንስ ከ 3 ሳምንት በፊት ማሳወቅ ይኖርብዎታል', 1, '2025-07-22 22:28:18', '2025-07-22 22:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `member_id` varchar(20) NOT NULL COMMENT 'Auto-generated: HEM-AG1, HEM-AM2, etc.',
  `username` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT '6 digit alphanumeric',
  `status` varchar(20) DEFAULT 'active',
  `monthly_payment` decimal(10,2) NOT NULL COMMENT 'Monthly contribution amount',
  `payout_position` int(3) NOT NULL COMMENT 'Position in payout rotation (1,2,3...)',
  `payout_month` date DEFAULT NULL COMMENT 'Month when member receives payout',
  `total_contributed` decimal(10,2) DEFAULT 0.00 COMMENT 'Total amount contributed so far',
  `has_received_payout` tinyint(1) DEFAULT 0 COMMENT '1 if already received payout',
  `guarantor_first_name` varchar(50) NOT NULL,
  `guarantor_last_name` varchar(50) NOT NULL,
  `guarantor_phone` varchar(20) NOT NULL,
  `guarantor_email` varchar(100) DEFAULT NULL,
  `guarantor_relationship` varchar(50) DEFAULT NULL COMMENT 'Relationship to member',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Admin approval status',
  `email_verified` tinyint(1) DEFAULT 0,
  `join_date` date NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `notification_preferences` set('email','sms','both') DEFAULT 'both',
  `go_public` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Public visibility: 1=Yes (public), 0=No (private)',
  `language_preference` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Web language: 0=English, 1=Amharic',
  `notes` text DEFAULT NULL COMMENT 'Admin notes about member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `member_id`, `username`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `password`, `status`, `monthly_payment`, `payout_position`, `payout_month`, `total_contributed`, `has_received_payout`, `guarantor_first_name`, `guarantor_last_name`, `guarantor_phone`, `guarantor_email`, `guarantor_relationship`, `is_active`, `is_approved`, `email_verified`, `join_date`, `last_login`, `notification_preferences`, `go_public`, `language_preference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'HEM-MW1', 'michael', 'Michael', 'Werkneh', 'Michael Werkneh', 'michael.werkneh@email.com', '+447123456789', '$2y$12$kdwSiI7P37OpM7OVFH4rMOHH2W7Yywf08O3DItvLGCVH6ZKqI/qBi', 'active', 1000.00, 1, '2025-07-05', 6000.00, 1, 'Sarah', 'Werkneh', '+447123456790', 'sarah.werkneh@email.com', 'Wife', 1, 1, 1, '2024-05-15', '2025-07-23 20:42:30', 'email,sms', 1, 1, 'First member - received June payout', '2025-07-22 07:24:42', '2025-07-24 02:43:20'),
(2, 'HEM-MN2', NULL, 'Maeruf', 'Nasir', NULL, 'maeruf.nasir@email.com', '+447234567890', 'MN456B', 'active', 1000.00, 2, '2025-08-05', 1000.00, 0, 'Ahmed', 'Nasir', '+447234567891', 'ahmed.nasir@email.com', 'Brother', 1, 1, 1, '2024-05-15', '2024-06-18 13:15:00', 'email', 1, 1, 'Active member - good payment record', '2025-07-22 07:24:42', '2025-07-24 02:43:23'),
(3, 'HEM-TE3', NULL, 'Teddy', 'Elias', NULL, 'teddy.elias@email.com', '+447345678901', 'TE789C', 'active', 500.00, 3, '2025-09-05', 1500.00, 0, 'Helen', 'Elias', '+447345678902', 'helen.elias@email.com', 'Mother', 1, 1, 1, '2024-05-15', '2024-06-19 15:45:00', 'email,sms', 1, 1, 'Reliable member', '2025-07-22 07:24:42', '2025-07-24 02:43:26'),
(4, 'HEM-KG4', NULL, 'Kokit', 'Gormesa', NULL, 'kokit.gormesa@email.com', '+447456789012', 'KG012D', 'active', 1000.00, 4, '2025-10-05', 1000.00, 0, 'Dawit', 'Gormesa', '+447456789013', 'dawit.gormesa@email.com', 'Husband', 1, 1, 1, '2024-05-15', '2024-06-17 11:20:00', 'sms', 1, 1, 'New member - very enthusiastic', '2025-07-22 07:24:42', '2025-07-24 02:43:30'),
(5, 'HEM-MA5', NULL, 'Mahlet', 'Ayalew', NULL, 'mahlet.ayalew@email.com', '+447567890123', 'MA345E', 'active', 1000.00, 5, '2025-11-05', 1000.00, 0, 'Bereket', 'Ayalew', '+447567890124', 'bereket.ayalew@email.com', 'Father', 1, 1, 1, '2024-05-15', '2024-06-21 08:10:00', 'email,sms', 1, 1, 'Last position - patient member', '2025-07-22 07:24:42', '2025-07-24 02:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `notification_id` varchar(20) NOT NULL COMMENT 'Auto-generated: NOT-202401-001',
  `recipient_type` enum('member','admin','all_members','all_admins') NOT NULL,
  `recipient_id` int(11) DEFAULT NULL COMMENT 'Member or Admin ID (NULL for broadcast)',
  `recipient_email` varchar(100) DEFAULT NULL,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `type` enum('payment_reminder','payout_alert','welcome','approval','general','emergency') NOT NULL,
  `channel` enum('email','sms','both') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `language` enum('en','am') DEFAULT 'en',
  `status` enum('pending','sent','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL COMMENT 'Email opened timestamp',
  `clicked_at` timestamp NULL DEFAULT NULL COMMENT 'Link clicked timestamp',
  `sent_by_admin_id` int(11) DEFAULT NULL,
  `email_provider_response` varchar(500) DEFAULT NULL COMMENT 'Email service response',
  `sms_provider_response` varchar(500) DEFAULT NULL COMMENT 'SMS service response',
  `retry_count` int(2) DEFAULT 0,
  `scheduled_for` timestamp NULL DEFAULT NULL COMMENT 'Scheduled sending time',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `notes` text DEFAULT NULL COMMENT 'Admin notes about notification',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(20) NOT NULL COMMENT 'Auto-generated: PAY-HEM-AG1-202401',
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_month` date NOT NULL COMMENT 'Which month this payment is for (YYYY-MM-01)',
  `payment_date` date DEFAULT NULL COMMENT 'Actual date payment was made',
  `status` enum('pending','paid','late','missed') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','bank_transfer','mobile_money') DEFAULT 'cash',
  `verified_by_admin` tinyint(1) DEFAULT 0,
  `verified_by_admin_id` int(11) DEFAULT NULL,
  `verification_date` timestamp NULL DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Payment notes/comments',
  `late_fee` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_id`, `member_id`, `amount`, `payment_month`, `payment_date`, `status`, `payment_method`, `verified_by_admin`, `verified_by_admin_id`, `verification_date`, `receipt_number`, `notes`, `late_fee`, `created_at`, `updated_at`) VALUES
(1, 'PAY-MW1-062024', 1, 1000.00, '0000-00-00', '2024-06-01', 'paid', 'cash', 1, 3, '2025-07-22 17:31:06', 'RCP-MW1-001', 'June payment - on time', 0.00, '2025-07-22 07:24:42', '2025-07-22 16:31:06'),
(3, 'PAY-MW1-122024', 1, 500.00, '2024-12-01', '2024-12-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(4, 'PAY-MW2-122024', 2, 500.00, '2024-12-01', '2024-12-01', 'paid', 'cash', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(5, 'PAY-MW3-122024', 3, 500.00, '0000-00-00', '2025-07-22', 'paid', 'bank_transfer', 1, 3, '2025-07-22 19:33:09', '', '', 0.00, '2025-07-22 17:36:50', '2025-07-22 18:33:09'),
(6, 'PAY-MW1-112024', 1, 500.00, '2024-11-01', '2024-11-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(7, 'PAY-MW2-112024', 2, 500.00, '2024-11-01', '2024-11-01', 'paid', 'mobile_money', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(8, 'PAY-MW3-112024', 3, 500.00, '2024-11-01', '2024-11-03', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(9, 'PAY-MW1-102024', 1, 500.00, '2024-10-01', '2024-10-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(10, 'PAY-MW2-102024', 2, 500.00, '2024-10-01', '2024-10-01', 'paid', 'cash', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(11, 'PAY-MW3-102024', 3, 500.00, '2024-10-01', '2024-10-02', 'paid', 'bank_transfer', 1, 3, '2025-07-22 19:43:11', NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 19:43:11'),
(12, 'PAY-MW1-092024', 1, 500.00, '2024-09-01', '2024-09-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(13, 'PAY-MW2-092024', 2, 500.00, '2024-09-01', '2024-09-01', 'paid', 'cash', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(14, 'PAY-MW3-092024', 3, 500.00, '2024-09-01', '2024-09-03', 'paid', 'mobile_money', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(15, 'PAY-MW1-082024', 1, 500.00, '2024-08-01', '2024-08-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(16, 'PAY-MW2-082024', 2, 500.00, '2024-08-01', '2024-08-01', 'paid', 'cash', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(17, 'PAY-MW3-082024', 3, 500.00, '2024-08-01', '2024-08-02', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(18, 'PAY-MW1-072024', 1, 500.00, '2024-07-01', '2024-07-01', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(19, 'PAY-MW2-072024', 2, 500.00, '2024-07-01', '2024-07-01', 'paid', 'mobile_money', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50'),
(20, 'PAY-MW3-072024', 3, 500.00, '2024-07-01', '2024-07-03', 'paid', 'bank_transfer', 0, NULL, NULL, NULL, NULL, 0.00, '2025-07-22 17:36:50', '2025-07-22 17:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `payout_id` varchar(20) NOT NULL COMMENT 'Auto-generated: PO-HEM-AG1-202401',
  `member_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL COMMENT 'Total payout amount',
  `scheduled_date` date NOT NULL COMMENT 'When payout was scheduled',
  `actual_payout_date` date DEFAULT NULL COMMENT 'When actually paid out',
  `status` enum('scheduled','processing','completed','cancelled','on_hold') NOT NULL DEFAULT 'scheduled',
  `payout_method` enum('cash','bank_transfer','mobile_money','mixed') DEFAULT 'cash',
  `processed_by_admin_id` int(11) DEFAULT NULL,
  `admin_fee` decimal(8,2) DEFAULT 0.00 COMMENT 'Admin service fee',
  `net_amount` decimal(12,2) NOT NULL COMMENT 'Amount after fees',
  `transaction_reference` varchar(100) DEFAULT NULL COMMENT 'Bank/payment reference',
  `receipt_issued` tinyint(1) DEFAULT 0,
  `member_signature` tinyint(1) DEFAULT 0 COMMENT 'Member confirmed receipt',
  `payout_notes` text DEFAULT NULL COMMENT 'DETAILED NOTES: Cash+transfer combinations, issues, special circumstances, delays, member requests, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payouts`
--

INSERT INTO `payouts` (`id`, `payout_id`, `member_id`, `total_amount`, `scheduled_date`, `actual_payout_date`, `status`, `payout_method`, `processed_by_admin_id`, `admin_fee`, `net_amount`, `transaction_reference`, `receipt_issued`, `member_signature`, `payout_notes`, `created_at`, `updated_at`) VALUES
(1, 'PAYOUT-MW1-062024', 1, 5000.00, '2024-06-15', '2024-06-15', 'completed', 'bank_transfer', 3, 50.00, 4950.00, 'TXN-MW1-20240615', 1, 0, 'First equib payout - Michael Werkneh - June 2024. Total collected: £5000, Admin fee: £50, Net payout: £4950', '2025-07-22 07:24:42', '2025-07-22 16:06:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `equb_rules`
--
ALTER TABLE `equb_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rule_number` (`rule_number`),
  ADD KEY `idx_rule_number` (`rule_number`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_member_id` (`member_id`),
  ADD UNIQUE KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_approved` (`is_approved`),
  ADD KEY `idx_payout_position` (`payout_position`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_id` (`notification_id`),
  ADD UNIQUE KEY `idx_notification_id` (`notification_id`),
  ADD KEY `idx_recipient` (`recipient_type`,`recipient_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_channel` (`channel`),
  ADD KEY `idx_sent_at` (`sent_at`),
  ADD KEY `idx_scheduled` (`scheduled_for`),
  ADD KEY `sent_by_admin_id` (`sent_by_admin_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_id` (`payment_id`),
  ADD UNIQUE KEY `idx_payment_id` (`payment_id`),
  ADD UNIQUE KEY `idx_member_month` (`member_id`,`payment_month`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_month` (`payment_month`),
  ADD KEY `idx_verified` (`verified_by_admin`),
  ADD KEY `verified_by_admin_id` (`verified_by_admin_id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payout_id` (`payout_id`),
  ADD UNIQUE KEY `idx_payout_id` (`payout_id`),
  ADD KEY `idx_member_id` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_date` (`scheduled_date`),
  ADD KEY `idx_actual_date` (`actual_payout_date`),
  ADD KEY `processed_by_admin_id` (`processed_by_admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `equb_rules`
--
ALTER TABLE `equb_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sent_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`verified_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payouts_ibfk_2` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
--
-- Database: `hmamat`
--
CREATE DATABASE IF NOT EXISTS `hmamat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hmamat`;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `default_points` int(11) NOT NULL DEFAULT 5,
  `day_of_week` int(11) DEFAULT NULL CHECK (`day_of_week` between 1 and 7),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `description`, `default_points`, `day_of_week`, `created_at`) VALUES
(1, 'ጸሎት', 'በየቀኑ መጸለይ', 10, NULL, '2025-04-13 13:37:22');

-- --------------------------------------------------------

--
-- Table structure for table `activity_miss_reasons`
--

CREATE TABLE `activity_miss_reasons` (
  `id` int(11) NOT NULL,
  `reason_text` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'Amhaslassie', '$2y$10$TTUTcF4Yc00X8VeB5p2LZOCwIxNxEIF7xCetvmVb.yy8Ome5u0.5q', '2025-04-13 13:28:18');

-- --------------------------------------------------------

--
-- Table structure for table `daily_messages`
--

CREATE TABLE `daily_messages` (
  `id` int(11) NOT NULL,
  `day_of_week` int(11) DEFAULT NULL CHECK (`day_of_week` between 1 and 7),
  `message_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `baptism_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `baptism_name`, `password`, `created_at`) VALUES
(1, 'Amhaslassie', '$2y$10$2gqeQonycI1gpWCmrkAtk.dlqcFFVFuldJvFL1J9RBiJgUndrNvSe', '2025-04-13 13:54:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `date_completed` date NOT NULL,
  `status` enum('done','not_done') NOT NULL DEFAULT 'done',
  `reason_id` int(11) DEFAULT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `last_active` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip_address`, `device_info`, `last_active`) VALUES
(1, 1, 'ef9119fb60f841171d73dcf30bcd966aba6957b6770715d1ff2cc6067e3f6a36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-13 13:54:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_miss_reasons`
--
ALTER TABLE `activity_miss_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `daily_messages`
--
ALTER TABLE `daily_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `baptism_name` (`baptism_name`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `reason_id` (`reason_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_miss_reasons`
--
ALTER TABLE `activity_miss_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `daily_messages`
--
ALTER TABLE `daily_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_activity_log_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_activity_log_ibfk_3` FOREIGN KEY (`reason_id`) REFERENCES `activity_miss_reasons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
--
-- Database: `home`
--
CREATE DATABASE IF NOT EXISTS `home` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `home`;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `Room_Id` int(11) NOT NULL,
  `Room_Name` int(11) NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`Room_Id`, `Room_Name`, `Price`) VALUES
(1, 0, 30),
(1, 0, 30);

-- --------------------------------------------------------

--
-- Table structure for table `tekeray`
--

CREATE TABLE `tekeray` (
  `Name` int(11) NOT NULL,
  `Phone num` varchar(10) NOT NULL,
  `Email` varchar(10) NOT NULL,
  `Age` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tekeray`
--

INSERT INTO `tekeray` (`Name`, `Phone num`, `Email`, `Age`) VALUES
(0, '000000000', 'djahsajssa', '20'),
(0, '000000000', 'djahsajssa', '20');
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

--
-- Dumping data for table `pma__designer_settings`
--

INSERT INTO `pma__designer_settings` (`username`, `settings_data`) VALUES
('root', '{\"snap_to_grid\":\"off\",\"angular_direct\":\"direct\",\"relation_lines\":\"true\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"gcse_tracker\",\"table\":\"task_recurrence_rules\"},{\"db\":\"gcse_tracker\",\"table\":\"task_instances\"},{\"db\":\"gcse_tracker\",\"table\":\"tasks\"},{\"db\":\"gcse_tracker\",\"table\":\"habit_completions\"},{\"db\":\"gcse_tracker\",\"table\":\"exam_reports\"},{\"db\":\"gcse_tracker\",\"table\":\"task_checklist_items\"},{\"db\":\"gcse_tracker\",\"table\":\"task_categories\"},{\"db\":\"gcse_tracker\",\"table\":\"taskcategories\"},{\"db\":\"gcse_tracker\",\"table\":\"habits\"},{\"db\":\"gcse_tracker\",\"table\":\"habit_reasons\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'gcse_tracker', 'habit_completions', '{\"sorted_col\":\"`habit_completions`.`completion_date` DESC\"}', '2025-04-02 14:07:03');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-04-08 07:47:18', '{\"Console\\/Mode\":\"collapse\",\"lang\":\"en_GB\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `restaurant_managment_local`
--
CREATE DATABASE IF NOT EXISTS `restaurant_managment_local` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `restaurant_managment_local`;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` enum('ingredients','beverages','supplies','packaging','cleaning','equipment') NOT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `conversion_rates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conversion_rates`)),
  `current_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `reserved_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `reorder_level` decimal(10,3) NOT NULL,
  `max_level` decimal(10,3) DEFAULT NULL,
  `minimum_order_qty` decimal(10,3) DEFAULT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `location` enum('main_kitchen','cold_storage','dry_storage','freezer','bar','prep_area') NOT NULL,
  `storage_requirements` varchar(255) DEFAULT NULL,
  `shelf_life_days` int(11) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `allergen_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allergen_info`)),
  `status` enum('active','inactive','discontinued') NOT NULL DEFAULT 'active',
  `last_stock_update` timestamp NULL DEFAULT NULL,
  `average_daily_usage` decimal(10,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(12, '2025_09_17_214939_create_staff_types_table', 1),
(13, '2025_09_17_214951_create_staff_table', 1),
(14, '2025_09_18_025239_create_staff_profiles_table', 1),
(21, '2025_09_18_101631_create_cache_table', 2),
(22, '2025_09_18_103757_create_staff_attendance_table', 3),
(23, '2025_09_18_103758_create_staff_shifts_table', 3),
(24, '2025_09_18_103759_create_staff_shift_assignments_table', 3),
(25, '2025_09_18_103801_create_staff_tasks_table', 3),
(26, '2025_09_18_103802_create_staff_task_assignments_table', 3),
(27, '2025_09_18_103807_create_staff_performance_reviews_table', 4),
(28, '2025_09_18_103808_create_staff_payroll_records_table', 4),
(29, '2025_09_18_110809_enhance_staff_tables_with_production_features', 5),
(34, '2025_09_18_112400_add_audit_fields_to_staff_types_table', 6),
(35, '2025_09_18_203332_create_staff_performance_goals_table', 6),
(36, '2025_09_18_203350_create_staff_performance_templates_table', 6),
(37, '2025_09_18_203405_create_staff_performance_metrics_table', 6),
(38, '2025_09_18_203421_create_staff_performance_review_acknowledgements_table', 7),
(39, '2025_09_18_230003_enhance_staff_tasks_table_with_advanced_features', 8),
(40, '2025_09_18_230029_enhance_staff_task_assignments_table_with_advanced_features', 9),
(41, '2025_09_18_230911_create_staff_task_dependencies_table', 10),
(42, '2025_09_18_231112_create_staff_task_comments_table', 11),
(43, '2025_09_18_231309_create_staff_task_attachments_table', 12),
(44, '2025_09_18_232421_create_staff_task_time_entries_table', 13),
(45, '2025_09_18_232544_create_staff_task_notifications_table', 14),
(46, '2025_09_23_002004_create_task_settings_tables', 15),
(47, '2025_09_23_174531_enhance_task_system_with_scheduling_and_notes', 16),
(48, '2025_09_23_190454_fix_staff_tasks_column_sizes', 17);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(26) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0Oci07kP1efuHMcC41Jbl7l6AXGBlvBIDYVixJdZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidHJLNjRjQXZXQ2hkcjZ0MmZBRE85SGRQcGJSUmQxRGJhWnhkMk1aTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758662604),
('1aS283QTWsP4TnHkaqXr8bYU6TnrBYVBbzok8lcA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWkza0pOeVhka2RqM3N5VE1TSlpHWmNmempxdFBwZFo5UU1oT0FrYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758659958),
('5b9ClSNViRRcOfA8R3wg05flgokGSqBnA5WABlph', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWlp1d05qVTVwbmhYcDJjbWR1QlRFR0hqOWtzS0JHdG81QXVtWm9wRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758655217),
('7sopLnh4r7UHOYCmGdwa98GbIgNiuh7CijBXR4cp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTUwRWRkTmNadmNCUzRuaFlIbDllUUVLMGlPWDlxNUcyUnk2VlhobiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758659223),
('7x24C3BxkvpnEbBWkFXCFAMA5npPaAnI4IytGBlt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZWl4bUt6Q3MwUlBEaWFjb3BPUWxnRFZSTTJlQ2p0STJEc0FJZVo2SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657836),
('b5CG80Nq9d3PwQjb2ZgqRZVOPVzCQWwWehFdgtnV', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOFNoMWtoZERTenB2ZjI4TFVNbFdmbGcyV1N1VHZvOGRSZUZLRDY2ZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758660223),
('b5TU9ugXPblx9xAQCHFYBJZG0TlARJkkf2fgGBkQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicjZHZ3IwMnp2elpmc3dVMDBDUEZ1djZOMzNkNUJhRVFuT2xOdmxTdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758663139),
('BfTQtlOEvNJcsoKhFQRyj5RijsBtYzXVrvAruRLp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVRuS0duWjZ6Q0tRNWN6WXJQdWtlZlJRQ2dkNkpUb3dXUkp3QzlrUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758656738),
('DlWyMJJ8E6N2nDAy9qxlBWmb9WDDKgctp2WWKM6c', '01K5EWWJVSJVYF2QWSTER2YVNK', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiME5Xc0NrRXA4ODJUQWEyakZmSHdJU2V5aTJTa3IxVm1lTmxMZlNIVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZi90YXNrcy8wMUs1Vzk0TlFRTTZTS0I3TjVZODBLRkVUNi9tb2RhbCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjI2OiIwMUs1RVdXSlZTSlZZRjJRV1NURVIyWVZOSyI7fQ==', 1758664487),
('IVwux6w3tOfbBa2l74EWBZ4835Axt6oKYmbnghZ2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN0Y4T3g3NzBRbGpJRXdVVUc2S2hzeEtBUVBVY0hxZTR0bjh3UnN2biI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657367),
('KdpImZAVBBz5X2O2IAPou4wCg5ZQ3vJWd1hgQ8Rq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieDVjU0d1dEhzdWNzd0RNdlhtMkFSYUZ3RXdjNkNzcG5QRW43Y2x0MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758653859),
('Krefir4IlR1fphOnMJm9E6y4dO45ecXFtbTBpKb3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSElaMlB3RzRWeDZsQW9MajZ2OUZHNEk1ejB2blhKSUFUYWZlN2VpTSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657729),
('lL1T2tQcDyqDgjHWxaLM0AnVokt0NDM5Csi4TuLn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN3c3RlNXeTFWWGRQRTNBNlpyWEl0WGJkZXJTMlBPT0F4Y0xBVWFVayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657851),
('LLxT9NzVElScFwiMuP19ITLcQSTEITIje2vzlgJO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMjZmVDg4RlQ2TlJoU0hsYTByZG5URlRrZGU4UWxENnR5RXRpUDBQeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758722548),
('LoyjceOiyNlPZQhBj3lNTXXsGEbXn9VaoGFIls87', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieE9TenlocVpCbmdMd25yUWJybWZabmZodExPTlhDODZlV0dMQ0VrayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758662380),
('lwYTuxGfiBPa8PVIgIyTNO8nJJx6hiGa31PtRATx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidUFjOTVSdHlaSVlVczFBTlVEcEJBVHl4M3VCUHR2a2FFZjNEYUxEeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758654318),
('obXcAxUGwursyJRYwbLG5wzb0N3ZfUFwKDzLJCX7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ3NJZXR4OXdqSXBmSVZlT2FBQjVzVFRTSjRGOFVtWEVNNkkwUDFrMSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657388),
('oJAiln4b3zYB4uZvbK2fEQVZ9M9d6IfFVnF5GJCY', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXNUSlA1eGV5VUdrTXJJOW9qZkp6TjhiUW5CUTBLTkNsdkd4aVFETSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657241),
('rFcO90UvqAeq8tYNzhAEqBnlEKTllEeescNBsdDH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUjA4cm1VdTdZMjlnUE9rVE85VXNCRThjcGZoR3M4WTFGektuUjduYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758657235),
('RpsoPA631nlhWV8glXXgi7mGO4T552KpD5PyHkek', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiak1hQ3FUVlpFVklDdUttckVCb1BNMGY4RVlNNHU1UTlKc05nU3ZNdSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758659668),
('RqvLDWtn3vi4jv4XTBjRJT39J9ZOi56xIVaZwkNP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid2pvNkZxeFdnOHlzcHNhR3JQOHlIMWZxcVhJNks4VVY5QTN5dHpGRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758653750),
('rwRhOu7Tbh5MRWSSVNoOoSPJJIy006dX65wrKQbK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUDlkU0FVMHZrbDJlQVhTMG1ZNENhcTZxbTZ1bnYzdzR3emRwTkdhSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758660218),
('STdC60fjrfc779TdvE2jAsiiI3hZlxXuIFelpz4u', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiblBlUlJNSGJPbnR6V2FLRmFRSEp6OEZrV0lFWXFjRW5Ua0R6aEtVZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758658297),
('sxorpr9EZDmOBuGADCHTN4knLtfCh61VG8EZzUW6', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZUJCdDlyMUdMQzJyam5DeFZRdGc5WkNLSzMxUmRHRThvNTdhRDVlWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758661132),
('sXQJoX5JiNLccAWSj0px5EhqAQPibvMZMDhxCojU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1l4VkI3VFZHbVM3N205aE1OZHg5UEpLUk1TUmlMNTlXc0NIc2huOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758655792),
('t0cVdIJSgzuhFGBr3ATV1h9ObkXZfoIrZKNuXN19', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMXowMVNJWFB5TzFSdG52Nkl6NmRQUjA3OWw1OHRGelg0cGJEanpMcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758659156),
('VFTqJKLQnYn7IGSV9roazFPcJ72SS2A4oWeSJK9Y', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMTlQWDhhZEtFdkVGU01hMTViZDNid1RjWEFpN2NjQm5VNlpuN2xwciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758658897),
('vTxOs80k9KzVE2m4nRnvmGjMIzKwkJFzOAo24sWg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTzNLWllQUHUxbTNUMTFZVlZWeHUyeVc2REFlaUEwcXo4c1l1SXAxNCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758655590),
('whNoXm4vAkkS71pjNpBdSKcM9PHR36KVfT3jywc5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYTBtTHY1aXBlMlhJSUFXcGpUM0R4RlJSZmEyR2xKRDNUaEdWOXlLWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758656110),
('WKvc135e3NyBQnuJ2Juwgeut2dW1iBJDERVMFcAC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWw5TVZUOUpjSzFYbWlwRDFMb3pGNTA5a1F1Z2xSUEtHQlVScVk2UyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758660920),
('xiCKCzorPwLOnSh0RsJYZzfdgTRFYCMOPmjpE7g4', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmlwR1BmTlV0eGRTbGRoTTFrT0I0aGtocW1aRnJ3c1JldnVPRVkzdCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758655574),
('Y1m3XW6SK0yQH3jp2FLMEe4YM9RaOfJq1KROQ6xm', '01K5EWWJVSJVYF2QWSTER2YVNK', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNmtiWno1cTMwMWxTVWR4RnluOW9QWlZNdWpSSWJMNlJHYm9EcjZwNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjI2OiIwMUs1RVdXSlZTSlZZRjJRV1NURVIyWVZOSyI7fQ==', 1759271588),
('Yf2aee82O3qzqdhPGK5BVO20h3JHaGJWTrTY9FfU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMURsUk9OOUxKTThwT0dLTXBnVlBVUWVZd3hLNjRVbUg2M0ZFSlE2TSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9zdGFmZi90YXNrcy8wMUs1VzAwM0RKSjVFQUM5UkNEM0g5M0EzUS9lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758657696),
('yPL8BWEUcgNFJamOstDyiuuEPeRUoA4e95R58Jeg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-GB) WindowsPowerShell/5.1.26100.6584', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN3YyTVpJR2RMdDNWcTJScGh5YkFxTFM1dUFKalA4SGZObkRiVTZLZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1758656117);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` char(26) NOT NULL,
  `first_name` varchar(255) NOT NULL COMMENT 'Staff member first name',
  `last_name` varchar(255) NOT NULL COMMENT 'Staff member last name',
  `username` varchar(255) NOT NULL COMMENT 'Unique username for login',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `staff_type_id` char(26) NOT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT 'Email address (optional for now)',
  `phone` varchar(255) DEFAULT NULL COMMENT 'Phone number',
  `hire_date` date DEFAULT NULL COMMENT 'Date of employment',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active' COMMENT 'Account status',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Last login timestamp',
  `last_login_ip` varchar(45) DEFAULT NULL COMMENT 'Last login IP address',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `first_name`, `last_name`, `username`, `password`, `staff_type_id`, `email`, `phone`, `hire_date`, `status`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5E5GE8XBXG9V91DMK9NEB4Q', 'System', 'Administrator', 'admin', '$2y$12$.1Mmt3nC3D.rGIVlMBA/BuRz7QeukX4xcERCp1SKlUwlA/ZJMAB9C', '01K5E5G8J1MH9KM7A0S1E6N915', 'admin@geez-restaurant.com', NULL, '2023-09-18', 'active', '2025-09-18 09:25:16', '::1', NULL, '2025-09-18 09:21:49', '2025-09-18 09:25:16', NULL),
('01K5E5GEKPVSGT34548HFMDNJE', 'Restaurant', 'Manager', 'manager', '$2y$12$JlaLbhX.usx2ElRiPMiAE.h/dvVamCvnbTqWzj50g0cZOxwp1fgze', '01K5E5G8JEM6M9N29AY8AC0601', 'manager@geez-restaurant.com', '+447415239333', '2024-09-18', 'active', NULL, NULL, NULL, '2025-09-18 09:21:50', '2025-09-18 14:19:48', NULL),
('01K5EPMK63B2S7RXXYJVZ4QX12', 'Sara', 'Teshome', 'sara_teshome', '$2y$12$FH6yN/XLGr9aFscPXZygde3gR3e3Dnkio1vSxHEZrANkdJJvezwWe', '01K5E5G8JEM6M9N29AY8AC0601', NULL, NULL, '2025-09-18', 'active', NULL, NULL, NULL, '2025-09-18 14:21:11', '2025-09-18 14:21:11', NULL),
('01K5EWWJVSJVYF2QWSTER2YVNK', 'Michael', 'werkeneh', 'michael_werkeneh', '$2y$12$4CB/wJ.V/MFFzpCSVhuN2eG2xtaycRiTj64idO4IndHPM9A8PslAm', '01K5E5G8JEM6M9N29AY8AC0601', NULL, NULL, '2025-09-18', 'active', '2025-09-30 21:31:21', '127.0.0.1', NULL, '2025-09-18 16:10:24', '2025-09-30 21:31:21', NULL),
('01K5F98HP8FD798MEYHBXEAFZ7', 'Sarah', 'Johnson', 'sarah.johnson', '$2y$12$1nRhKR4QCa/GvDk3N.b6pun6o99pq6PRPfevqN2QhxWcM0FYgFMKa', '01K5E5G8K4ASYPQGVXH16TZSN5', 'sarah.johnson@restaurant.com', '+1234567890', '2025-01-18', 'active', NULL, NULL, NULL, '2025-09-18 19:46:39', '2025-09-18 19:46:39', NULL),
('01K5F98J6H3MWA78DAFZTJCGJZ', 'Mike', 'Chen', 'mike.chen', '$2y$12$5HcrkIEWUU8CwSjHEU5Y.uLsSIhkm8fpPSN9Sebno0UAnn7JkkRJG', '01K5F98H2BHP481A4QPWW8PAWA', 'mike.chen@restaurant.com', '+1234567892', '2024-07-18', 'active', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5VZ1ZSB273NTKPBMXDEADHP', 'Test', 'Admin', 'testadmin', '$2y$12$PQRhAKkrkkhsjXv5neayBeyHXNwBV7U2aGfhUt9IwTF.9MPaRGbLO', '01K5E5G8JEM6M9N29AY8AC0601', 'test@admin.com', '1234567890', '2025-09-23', 'active', NULL, NULL, NULL, '2025-09-23 17:58:26', '2025-09-23 17:58:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `clock_in` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'When staff clocked in',
  `clock_out` timestamp NULL DEFAULT NULL COMMENT 'When staff clocked out',
  `status` enum('present','absent','late','early_leave','overtime') NOT NULL DEFAULT 'present',
  `hours_worked` decimal(5,2) DEFAULT NULL COMMENT 'Calculated work hours',
  `notes` text DEFAULT NULL COMMENT 'Manager or staff notes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_payroll_records`
--

CREATE TABLE `staff_payroll_records` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `pay_period_start` date NOT NULL COMMENT 'Start of pay period',
  `pay_period_end` date NOT NULL COMMENT 'End of pay period',
  `regular_hours` decimal(5,2) DEFAULT NULL COMMENT 'Regular hours worked',
  `overtime_hours` decimal(5,2) DEFAULT NULL COMMENT 'Overtime hours worked',
  `gross_pay` decimal(12,2) NOT NULL,
  `deductions` decimal(12,2) DEFAULT NULL,
  `net_pay` decimal(12,2) NOT NULL,
  `status` enum('draft','calculated','approved','paid') NOT NULL DEFAULT 'draft',
  `processed_by` char(26) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL COMMENT 'When payroll was processed',
  `notes` text DEFAULT NULL COMMENT 'Payroll notes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance_goals`
--

CREATE TABLE `staff_performance_goals` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `goal_title` varchar(255) NOT NULL,
  `goal_description` text DEFAULT NULL,
  `target_value` decimal(12,2) DEFAULT NULL,
  `current_value` decimal(12,2) DEFAULT 0.00,
  `measurement_unit` varchar(255) NOT NULL COMMENT 'e.g., %, hours, ETB, count',
  `goal_type` enum('individual','team','department') NOT NULL DEFAULT 'individual',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `start_date` date NOT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('active','completed','cancelled','overdue') NOT NULL DEFAULT 'active',
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_performance_goals`
--

INSERT INTO `staff_performance_goals` (`id`, `staff_id`, `goal_title`, `goal_description`, `target_value`, `current_value`, `measurement_unit`, `goal_type`, `priority`, `start_date`, `target_date`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5F98J78F6FK5AKH7SMJE24G', '01K5F98HP8FD798MEYHBXEAFZ7', 'Improve Customer Satisfaction Score', 'Achieve and maintain a customer satisfaction rating of 4.5/5 or higher', 4.50, 4.20, 'rating (1-5)', 'individual', 'high', '2025-07-01', '2025-09-30', 'active', NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J7DREC91HXB2GFETXFC', '01K5F98HP8FD798MEYHBXEAFZ7', 'Reduce Order Errors', 'Reduce order errors to less than 2% of total orders taken', 2.00, 3.50, '%', 'individual', 'medium', '2025-09-01', '2025-09-30', 'active', NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J7GSQX1YA8QVKTR9WVE', '01K5F98J6H3MWA78DAFZTJCGJZ', 'Improve Kitchen Efficiency', 'Reduce average order preparation time to under 12 minutes', 12.00, 14.50, 'minutes', 'individual', 'high', '2025-07-01', '2025-09-30', 'active', NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J7PSDPP1P0H1TWZ3R28', '01K5F98J6H3MWA78DAFZTJCGJZ', 'Zero Food Safety Violations', 'Maintain perfect food safety record with zero violations', 0.00, 0.00, 'violations', 'individual', 'urgent', '2025-01-01', '2025-12-31', 'completed', NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance_metrics`
--

CREATE TABLE `staff_performance_metrics` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `metric_name` varchar(255) NOT NULL COMMENT 'e.g., punctuality, orders_per_hour',
  `metric_value` decimal(12,2) NOT NULL,
  `measurement_period` enum('daily','weekly','monthly') NOT NULL,
  `recorded_date` date NOT NULL,
  `data_source` enum('manual','attendance','tasks','reviews') NOT NULL DEFAULT 'manual',
  `notes` text DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_performance_metrics`
--

INSERT INTO `staff_performance_metrics` (`id`, `staff_id`, `metric_name`, `metric_value`, `measurement_period`, `recorded_date`, `data_source`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5F98J7V0RVRYQ6CF8G1KF23', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.10, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J7Z46KRZNCFHAT6WEPZ', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.20, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J83ZTJGEQ60XDRVG2WS', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.20, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J870DRPM0G0BWH6PDTM', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.30, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8AX20GCE5QGDPYC1M5', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.20, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8DT7C5R98BCEWCA33Z', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.40, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8H9E1J2TZAWYSP65YR', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.30, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8N194AC8FHFTB0CNFZ', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.20, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8RGQ6QCZP26DXP39FW', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.50, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8WS1BQVRTKMH1QDS51', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.40, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J8ZV6F02NQ2YW5P5ZSV', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.30, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J929Z952PER8F46X64B', '01K5F98HP8FD798MEYHBXEAFZ7', 'customer_satisfaction', 4.20, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J97N8358DP08T4Z6MAG', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 12.00, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9AJ34DGZ78BKVXCWVV', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 14.00, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9E6QHF7VZM5896DQWJ', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 13.00, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9HY30P19ER35HJJXQ1', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 15.00, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9NR6HWE69GA09N9P0T', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 16.00, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9STZB89WQ9RGKA7PD2', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 14.00, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9VGADEVFMAXFMDWSNN', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 15.00, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J9ZVNR13EP8D9TPAC85', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 17.00, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JA22DCQBYMEQXJRSQAE', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 16.00, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JA65VC8YEZVTVTRZ6A5', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 15.00, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JA8BFTQDNJSPPCQQDKB', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 16.00, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JABN5R05T33Z6Q716PX', '01K5F98HP8FD798MEYHBXEAFZ7', 'orders_per_hour', 18.00, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JAETCE0CHJFQMNGJ0Z9', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 96.50, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JAJCEWYC7WDFQDRXKXA', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.20, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JAN7G0QAJ7F39CQFT4X', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 96.80, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JATPKKDCP8766A1SR2Y', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.50, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JAXVW014CW0NQ6DPJAN', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 96.90, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JAZNZ5D88NB91GFXSYD', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.80, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JB3Z2P5FERZ03GA1TEK', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.10, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JB7R7VHJG2JEFK3YQJP', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 96.70, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBAV4TWPGD8YWJ947VM', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 98.20, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBESC2VEBQV9EESGY3E', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.60, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBH0V6T5HHKEQ5320XC', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.30, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBN75GMWE8SDPR7QJH7', '01K5F98HP8FD798MEYHBXEAFZ7', 'order_accuracy', 97.90, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBSP4D2D9XDEDWYHZPF', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 15.20, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBWK6H8T98Y6ERQETGH', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.80, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JBZTCVAWWNE8YKJK934', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.50, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JC3EBFRBEK0F7XJF253', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.20, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JC7AKVS3RHTDYAFJPGP', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.00, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCA71T534CM58NX45RN', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 13.80, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCE5RXHEDY6EBEGFFXP', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 13.50, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCHRYABAKENKRB99KT0', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 13.20, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCMV8CNJ3S5FVBQBDZJ', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.10, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCRB8EY8E0HCV6KETGV', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 13.90, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCW53WBFTDW735ERKJH', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.20, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JCZE751T3X28HK3WZ4S', '01K5F98J6H3MWA78DAFZTJCGJZ', 'order_prep_time', 14.50, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JD2GF4WYZ1ZDNDD8659', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 8.50, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JD60ZGPZWQS8VTXJTFW', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 7.80, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDA97JM0EPEZ2NTMWQM', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 7.20, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDEJ9GP832BECFNMGK7', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 6.90, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDH0X9ATBV85G0TGTQB', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 6.50, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDMZSAJW90JR31P1P1K', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 6.20, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDQ1MZ2XGY139J2Q126', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 5.80, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDVV8HXQA2EA96DWDPM', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 5.50, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JDYSSMNE0G70JYCRK8R', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 6.10, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JE1E06JQ5RE9DDY15HF', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 5.90, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JE4E8RAWXGJ49PDV1VC', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 5.70, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JE8ETHZDXA9EY9V95JH', '01K5F98J6H3MWA78DAFZTJCGJZ', 'food_waste_percentage', 5.40, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JEBKBJNZZMTNRWMNZ9Q', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 25.00, 'weekly', '2025-07-03', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JEGEFDTAZ7P5TDF8T8T', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 27.00, 'weekly', '2025-07-10', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JEKAHRT6Z77BB4MPSC4', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 28.00, 'weekly', '2025-07-17', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JEN9SS5FNGYX2PT6HSB', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 29.00, 'weekly', '2025-07-24', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JERMMDDS06KNNY2H3S6', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 30.00, 'weekly', '2025-07-31', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JEW7CEXMG8N70S31FK4', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 32.00, 'weekly', '2025-08-07', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JF0BHR1ME9TH2FRQNVV', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 33.00, 'weekly', '2025-08-14', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JF31FZ1NAD8ZX3KBG5D', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 35.00, 'weekly', '2025-08-21', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JF5EGEMD4GX4JR21606', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 34.00, 'weekly', '2025-08-28', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JF8RAVDRVJ70TNZT0ZY', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 33.00, 'weekly', '2025-09-04', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JFCAJ36QBHD88Q5BDJN', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 35.00, 'weekly', '2025-09-11', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98JFFRKH8J8GS1DSACFP9', '01K5F98J6H3MWA78DAFZTJCGJZ', 'dishes_per_hour', 36.00, 'weekly', '2025-09-18', 'manual', NULL, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance_reviews`
--

CREATE TABLE `staff_performance_reviews` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `review_period_start` date NOT NULL COMMENT 'Start of review period',
  `review_period_end` date NOT NULL COMMENT 'End of review period',
  `overall_rating` decimal(3,2) NOT NULL COMMENT 'Overall performance rating',
  `punctuality_rating` decimal(3,2) DEFAULT NULL COMMENT 'Punctuality rating',
  `quality_rating` decimal(3,2) DEFAULT NULL COMMENT 'Work quality rating',
  `teamwork_rating` decimal(3,2) DEFAULT NULL COMMENT 'Teamwork rating',
  `customer_service_rating` decimal(3,2) DEFAULT NULL COMMENT 'Customer service rating',
  `strengths` text DEFAULT NULL COMMENT 'Employee strengths',
  `areas_for_improvement` text DEFAULT NULL COMMENT 'Areas needing improvement',
  `goals` text DEFAULT NULL COMMENT 'Goals for next period',
  `reviewer_id` char(26) NOT NULL,
  `review_date` date NOT NULL COMMENT 'Date review was conducted',
  `status` enum('draft','completed','acknowledged') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_performance_reviews`
--

INSERT INTO `staff_performance_reviews` (`id`, `staff_id`, `review_period_start`, `review_period_end`, `overall_rating`, `punctuality_rating`, `quality_rating`, `teamwork_rating`, `customer_service_rating`, `strengths`, `areas_for_improvement`, `goals`, `reviewer_id`, `review_date`, `status`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`) VALUES
('01K5F98JFNM2DSKNWB7N3XSXN8', '01K5F98HP8FD798MEYHBXEAFZ7', '2025-06-01', '2025-08-31', 4.20, 4.50, 4.00, 4.30, 4.10, 'Excellent customer interaction skills, always punctual, great team player', 'Could improve order accuracy, needs to learn new POS system features', 'Achieve 4.5+ customer satisfaction rating, reduce order errors to <2%', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-09-04', 'completed', '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL, NULL, NULL),
('01K5F98JFW3K0EWSKA0X1QMVEM', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-06-01', '2025-08-31', 4.60, 4.80, 4.70, 4.40, NULL, 'Exceptional food quality, excellent food safety practices, efficient kitchen management', 'Could mentor junior kitchen staff more, explore new cooking techniques', 'Reduce prep time to <12 minutes, train 2 junior chefs, develop 3 new seasonal dishes', '01K5F98HP8FD798MEYHBXEAFZ7', '2025-09-11', 'completed', '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance_review_acknowledgements`
--

CREATE TABLE `staff_performance_review_acknowledgements` (
  `id` char(26) NOT NULL,
  `performance_review_id` char(26) NOT NULL,
  `acknowledged_by` char(26) NOT NULL,
  `acknowledged_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance_templates`
--

CREATE TABLE `staff_performance_templates` (
  `id` char(26) NOT NULL,
  `staff_type_id` char(26) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `review_frequency` enum('monthly','quarterly','annual') NOT NULL DEFAULT 'quarterly',
  `rating_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of criteria with weights, e.g. [{"key":"punctuality","weight":20}]' CHECK (json_valid(`rating_criteria`)),
  `version` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_performance_templates`
--

INSERT INTO `staff_performance_templates` (`id`, `staff_type_id`, `template_name`, `review_frequency`, `rating_criteria`, `version`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5F98J6YAPZP19YVZ68723T3', '01K5E5G8K4ASYPQGVXH16TZSN5', 'Waiter Quarterly Review', 'quarterly', '[{\"key\":\"customer_service\",\"weight\":30,\"description\":\"Customer interaction and satisfaction\"},{\"key\":\"punctuality\",\"weight\":20,\"description\":\"Timeliness and attendance\"},{\"key\":\"teamwork\",\"weight\":20,\"description\":\"Collaboration with team members\"},{\"key\":\"order_accuracy\",\"weight\":20,\"description\":\"Accuracy in taking and serving orders\"},{\"key\":\"appearance\",\"weight\":10,\"description\":\"Professional appearance and hygiene\"}]', 1, 1, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL),
('01K5F98J73FW3365AY4QH0NTXN', '01K5F98H2BHP481A4QPWW8PAWA', 'Chef Quarterly Review', 'quarterly', '[{\"key\":\"food_quality\",\"weight\":35,\"description\":\"Quality and consistency of food preparation\"},{\"key\":\"food_safety\",\"weight\":25,\"description\":\"Adherence to food safety protocols\"},{\"key\":\"efficiency\",\"weight\":20,\"description\":\"Speed and efficiency in kitchen operations\"},{\"key\":\"teamwork\",\"weight\":15,\"description\":\"Collaboration with kitchen staff\"},{\"key\":\"creativity\",\"weight\":5,\"description\":\"Innovation and creativity in dishes\"}]', 1, 1, NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `address` text DEFAULT NULL,
  `emergency_contacts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of emergency contacts with name, phone, relationship' CHECK (json_valid(`emergency_contacts`)),
  `date_of_birth` date DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL COMMENT 'URL to profile photo (S3, local storage, etc.)',
  `hourly_rate` decimal(12,2) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL COMMENT 'Auto-generated employee ID (e.g., EMP-0001)',
  `notes` text DEFAULT NULL COMMENT 'HR notes, special requirements, etc.',
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `staff_id`, `address`, `emergency_contacts`, `date_of_birth`, `photo_url`, `hourly_rate`, `employee_id`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5F98HRKGK9VWW12PF33PZQT', '01K5F98HP8FD798MEYHBXEAFZ7', '123 Main Street, City, State 12345', '\"[{\\\"name\\\":\\\"John Johnson\\\",\\\"relationship\\\":\\\"Spouse\\\",\\\"phone\\\":\\\"+1234567891\\\"}]\"', '2000-09-18', NULL, 15.50, 'EMP001', 'Excellent customer service skills, very reliable', NULL, NULL, '2025-09-18 19:46:39', '2025-09-18 19:46:39', NULL),
('01K5F98J6SBVFN5QN0GRWPXH37', '01K5F98J6H3MWA78DAFZTJCGJZ', '456 Oak Avenue, City, State 12345', '\"[{\\\"name\\\":\\\"Lisa Chen\\\",\\\"relationship\\\":\\\"Wife\\\",\\\"phone\\\":\\\"+1234567893\\\"}]\"', '1993-09-18', NULL, 22.00, 'EMP002', 'Experienced chef with excellent knife skills and food safety knowledge', NULL, NULL, '2025-09-18 19:46:40', '2025-09-18 19:46:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_shifts`
--

CREATE TABLE `staff_shifts` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Shift name (e.g., Morning Shift)',
  `start_time` time NOT NULL COMMENT 'Shift start time',
  `end_time` time NOT NULL COMMENT 'Shift end time',
  `break_duration` int(11) DEFAULT NULL COMMENT 'Break duration in minutes',
  `days_of_week` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of weekdays [1,2,3,4,5]' CHECK (json_valid(`days_of_week`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether shift is active',
  `created_by` char(26) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_shift_assignments`
--

CREATE TABLE `staff_shift_assignments` (
  `id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `staff_shift_id` char(26) NOT NULL,
  `assigned_date` date NOT NULL COMMENT 'Date this shift is assigned for',
  `status` enum('scheduled','confirmed','cancelled','completed') NOT NULL DEFAULT 'scheduled',
  `assigned_by` char(26) NOT NULL,
  `notes` text DEFAULT NULL COMMENT 'Assignment notes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_tasks`
--

CREATE TABLE `staff_tasks` (
  `id` char(26) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Task title',
  `description` text DEFAULT NULL COMMENT 'Detailed task description',
  `instructions` text DEFAULT NULL COMMENT 'Detailed instructions for completing the task',
  `task_type` varchar(100) NOT NULL,
  `priority` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether task is active',
  `created_by` char(26) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `estimated_hours` decimal(12,2) DEFAULT NULL COMMENT 'Estimated time to complete',
  `scheduled_date` date DEFAULT NULL COMMENT 'Default date when task should be performed',
  `scheduled_time` time DEFAULT NULL COMMENT 'Default time when task should be performed',
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Expected duration in minutes',
  `is_template` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is a reusable template',
  `template_name` varchar(255) DEFAULT NULL COMMENT 'Template name for reusable tasks',
  `recurrence_pattern` varchar(20) NOT NULL DEFAULT 'none' COMMENT 'none, daily, weekly, monthly',
  `recurrence_type` enum('none','daily','weekly','monthly','custom') NOT NULL DEFAULT 'none',
  `recurrence_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Detailed recurrence configuration' CHECK (json_valid(`recurrence_config`)),
  `recurrence_interval` int(11) NOT NULL DEFAULT 1 COMMENT 'Every N days/weeks/months',
  `recurrence_end_date` date DEFAULT NULL COMMENT 'When to stop recurring',
  `requires_approval` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether completion requires approval',
  `auto_assign` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether to auto-assign to staff',
  `default_assignees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Default staff members to assign this task to' CHECK (json_valid(`default_assignees`)),
  `approval_workflow` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Approval workflow configuration' CHECK (json_valid(`approval_workflow`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Task tags for better organization' CHECK (json_valid(`tags`)),
  `updated_by` char(26) DEFAULT NULL COMMENT 'Last updated by staff member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_tasks`
--

INSERT INTO `staff_tasks` (`id`, `title`, `description`, `instructions`, `task_type`, `priority`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`, `category`, `estimated_hours`, `scheduled_date`, `scheduled_time`, `duration_minutes`, `is_template`, `template_name`, `recurrence_pattern`, `recurrence_type`, `recurrence_config`, `recurrence_interval`, `recurrence_end_date`, `requires_approval`, `auto_assign`, `default_assignees`, `approval_workflow`, `tags`, `updated_by`) VALUES
('01K5FNJCX026TQD7DWDFKYAMEH', 'Daily Kitchen Prep', 'Prepare vegetables, check inventory, and set up cooking stations for the day.', NULL, 'daily', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:21:45', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'kitchen', 2.50, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNJCXECSMMNMNPPTDEA7ED', 'Weekly Deep Clean', 'Deep clean all kitchen equipment, sanitize surfaces, and organize storage areas.', NULL, 'weekly', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:21:45', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'cleaning', 4.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX83YK2ZFJBJB18JGCWB', 'Daily Kitchen Prep', 'Prepare vegetables, check inventory, and set up cooking stations for the day.', NULL, 'daily', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'kitchen', 2.50, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX8HG51MEAXDA849ZYAM', 'Weekly Deep Clean', 'Deep clean all kitchen equipment, sanitize surfaces, and organize storage areas.', NULL, 'weekly', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'cleaning', 4.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX8PZ5XA8BF8WFWAPP50', 'Menu Update Project', 'Review current menu items, analyze sales data, and propose new seasonal dishes.', NULL, 'one_time', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'administration', 8.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX8X4SCCDZJJ9878PEEX', 'Equipment Maintenance Check', 'Inspect all kitchen equipment, check for wear and tear, and schedule repairs if needed.', NULL, 'monthly', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'maintenance', 3.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX90DV11PJSM0NA305TP', 'Customer Service Training', 'Conduct training session on customer service best practices and complaint handling.', NULL, 'one_time', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'service', 2.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FNSX93028E61P14CW8NNCC', 'Inventory Stock Count', 'Count all inventory items, update stock levels, and identify items needing reorder.', NULL, 'weekly', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:25:51', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'inventory', 3.50, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FP4DETH7DREWBF7CJA6B5R', 'Kitchen Prep', 'Prepare vegetables, check inventory, and set up cooking stations for the day.', NULL, 'daily-tasks', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'food-prep', 2.50, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, '[]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5FP4DF8427N0G51H5EGAX68', 'Weekly Deep Clean', 'Deep clean all kitchen equipment, sanitize surfaces, and organize storage areas.', NULL, 'weekly', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'cleaning', 4.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FP4DFBWD26GCGWMXTQTTK6', 'Menu Update Project', 'Review current menu items, analyze sales data, and propose new seasonal dishes.', NULL, 'one_time', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'administration', 8.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FP4DFFTQTT7CNCMY4N9F6T', 'Equipment Maintenance Check', 'Inspect all kitchen equipment, check for wear and tear, and schedule repairs if needed.', NULL, 'monthly', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'maintenance', 3.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FP4DFRMCPPZTJSNVEEDGRZ', 'Customer Service Training', 'Conduct training session on customer service best practices and complaint handling.', NULL, 'one_time', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'service', 2.00, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5FP4DFVKRFPD43ANF4V5R4W', 'Inventory Stock Count', 'Count all inventory items, update stock levels, and identify items needing reorder.', NULL, 'weekly', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', 'inventory', 3.50, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5W003DJJ5EAC9RCD3H93A3Q', 'Buy Duqet', 'bUY 1kg Teff duqet', 'from kaka supermarket', 'one-time-tasks', 'low', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 18:14:53', '2025-09-23 18:57:48', '2025-09-23 18:57:48', 'inventory-management', NULL, '2025-09-23', '21:00:00', NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, '[\"01K5EWWJVSJVYF2QWSTER2YVNK\"]', NULL, '[\"inventory\"]', NULL),
('01K5W300AB10VED9SARP0N3XJQ', 'Buy Duqet', 'Buy 1kg Raggi floor from the saka', 'Get the floor from the supermarket and drop to the kitchen.', 'one-time-tasks', 'urgent', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 19:07:16', '2025-09-23 19:07:54', '2025-09-23 19:07:54', 'inventory-management', 0.25, '2025-09-24', '13:30:00', 30, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, '[\"01K5EWWJVSJVYF2QWSTER2YVNK\"]', NULL, '[\"inventory\"]', NULL),
('01K5W3CJ97ZEEWCB960G3CDAGB', 'Buy Duqet', 'Buy 2 Kg Raggi floor', 'Buy the floor and drop in the restaurant.', 'one-time-tasks', 'urgent', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 19:14:07', '2025-09-23 19:25:32', '2025-09-23 19:25:32', 'inventory-management', 0.00, '2025-09-24', '13:00:00', 30, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, '[\"01K5EWWJVSJVYF2QWSTER2YVNK\"]', NULL, '[\"inventory\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W473D7449Q9S3RZTQ9KAZT', 'Buy Duqet', 'Buy 2 Kg Raggie floor from Saka', 'Buy the floor and drop it to the restaurant', 'one-time-tasks', 'urgent', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 19:28:37', '2025-09-23 19:43:31', '2025-09-23 19:43:31', 'inventory-management', 0.50, '2025-09-24', '13:00:00', 30, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, '[\"01K5EWWJVSJVYF2QWSTER2YVNK\"]', NULL, '[\"inventory\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W4XVZ2HGYNSCRB73C234Q3', 'Buy Duqet', 'Buy 2 kg Raggie floor', 'Drop it to the restaurant', 'one-time-tasks', 'urgent', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 19:41:03', '2025-09-23 19:43:31', '2025-09-23 19:43:31', 'inventory-management', NULL, '2025-09-24', '13:00:00', 30, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, NULL, NULL, '[\"inventory\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W57W9A6G09E1KDZ5AP7W83', 'Updated Workflow Task', 'Updated description', 'Follow the steps carefully', 'one-time-tasks', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:46:31', '2025-09-23 19:46:31', NULL, 'inventory-management', 2.50, '2025-09-23', '14:30:00', 150, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, '[\"test\",\"workflow\"]', '01K5E5GE8XBXG9V91DMK9NEB4Q'),
('01K5W58JGQ6CQ34BM67R696GD4', 'Controller Test Task', 'Testing controller method', NULL, 'one-time-tasks', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:46:53', '2025-09-23 19:46:53', NULL, 'inventory-management', NULL, NULL, NULL, NULL, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
('01K5W59XFH62G8XY6Q5WC15GRM', 'Test Task 21', 'Testing without auto assign', 'Test instructions', 'one-time-tasks', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:47:37', '2025-09-23 20:48:05', NULL, 'inventory-management', 2.50, '2025-09-23', '13:00:00', 150, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 1, NULL, NULL, '[\"test\",\"scenario1\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W59XGJAJEDK383CQSFAEP6', 'Test Task 21', 'Testing with auto assign but no staff', 'Test instructions', 'one-time-tasks', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:47:38', '2025-09-23 19:49:55', NULL, 'inventory-management', 2.50, '2025-09-23', '14:30:00', 150, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 1, NULL, NULL, '[\"test\",\"scenario2\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W59XH038QEV5MRZWSWZ7E9', 'Test Task 33', 'Testing with auto assign and staff', 'Test instructions', 'one-time-tasks', 'medium', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:47:38', '2025-09-23 20:45:56', NULL, 'inventory-management', 2.50, '2025-09-23', '02:49:00', 150, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 1, '[\"01K5E5GE8XBXG9V91DMK9NEB4Q\"]', NULL, '[\"test\",\"scenario3\"]', '01K5EWWJVSJVYF2QWSTER2YVNK'),
('01K5W5B50C7HA529MF047P0WK6', 'Updated Edit Test Task', 'This task has been updated', 'Updated instructions', 'one-time-tasks', 'high', 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23 19:48:18', '2025-09-23 19:48:18', NULL, 'inventory-management', 3.50, '2025-09-24', '15:00:00', 210, 0, NULL, 'none', 'none', NULL, 1, NULL, 0, 0, NULL, NULL, '[\"updated\",\"test\"]', '01K5E5GE8XBXG9V91DMK9NEB4Q'),
('01K5W94NQQM6SKB7N5Y80KFET6', 'Buy', 'Buy 1kg', 'buy new', 'daily-tasks', 'low', 1, '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23 20:54:40', '2025-09-23 20:54:40', NULL, 'inventory-management', 0.00, '2025-09-24', '12:00:00', 28, 0, NULL, 'none', 'none', NULL, 1, NULL, 1, 1, '[\"01K5EWWJVSJVYF2QWSTER2YVNK\"]', NULL, '[\"Bar\"]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_assignments`
--

CREATE TABLE `staff_task_assignments` (
  `id` char(26) NOT NULL,
  `staff_task_id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `assigned_date` date NOT NULL COMMENT 'Date task was assigned',
  `due_date` date DEFAULT NULL COMMENT 'When task should be completed',
  `scheduled_date` date DEFAULT NULL COMMENT 'Specific date this assignment should be completed',
  `scheduled_time` time DEFAULT NULL COMMENT 'Specific time this assignment should be completed',
  `scheduled_datetime` datetime DEFAULT NULL COMMENT 'Combined scheduled date and time',
  `status` enum('pending','in_progress','completed','cancelled','overdue') NOT NULL DEFAULT 'pending',
  `is_overdue` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this assignment is overdue',
  `overdue_since` datetime DEFAULT NULL COMMENT 'When this assignment became overdue',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'When staff started the task',
  `actual_start_time` datetime DEFAULT NULL COMMENT 'Actual time work started',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'When task was completed',
  `actual_end_time` datetime DEFAULT NULL COMMENT 'Actual time work ended',
  `notes` text DEFAULT NULL COMMENT 'Task completion notes',
  `assignment_notes` text DEFAULT NULL COMMENT 'Notes specific to this assignment',
  `completion_notes` text DEFAULT NULL COMMENT 'Notes added when task is completed',
  `assigned_by` char(26) NOT NULL,
  `completed_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `progress_percentage` int(11) NOT NULL DEFAULT 0 COMMENT 'Task completion percentage (0-100)',
  `estimated_hours` decimal(12,2) DEFAULT NULL COMMENT 'Estimated hours for this assignment',
  `actual_hours` decimal(12,2) DEFAULT NULL COMMENT 'Actual hours worked',
  `break_minutes` int(11) NOT NULL DEFAULT 0 COMMENT 'Break time taken during task',
  `priority_override` varchar(20) DEFAULT NULL COMMENT 'Override task priority: low, medium, high, urgent',
  `urgency_level` enum('low','normal','high','critical') NOT NULL DEFAULT 'normal',
  `reminder_sent_at` timestamp NULL DEFAULT NULL COMMENT 'When reminder was last sent',
  `reminder_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Schedule for sending reminders' CHECK (json_valid(`reminder_schedule`)),
  `last_reminder_sent` datetime DEFAULT NULL COMMENT 'When last reminder was sent',
  `reminder_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of reminders sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_task_assignments`
--

INSERT INTO `staff_task_assignments` (`id`, `staff_task_id`, `staff_id`, `assigned_date`, `due_date`, `scheduled_date`, `scheduled_time`, `scheduled_datetime`, `status`, `is_overdue`, `overdue_since`, `started_at`, `actual_start_time`, `completed_at`, `actual_end_time`, `notes`, `assignment_notes`, `completion_notes`, `assigned_by`, `completed_by`, `created_at`, `updated_at`, `deleted_at`, `updated_by`, `progress_percentage`, `estimated_hours`, `actual_hours`, `break_minutes`, `priority_override`, `urgency_level`, `reminder_sent_at`, `reminder_schedule`, `last_reminder_sent`, `reminder_count`) VALUES
('01K5FNSX9DN812TMWAVVQRX5J0', '01K5FNSX83YK2ZFJBJB18JGCWB', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-19', '2025-09-25', NULL, NULL, NULL, 'in_progress', 0, NULL, '2025-09-18 23:25:51', NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:25:51', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 62, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FNSX9MB1D12HDKH28SWG2M', '01K5FNSX83YK2ZFJBJB18JGCWB', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-09-18', '2025-09-20', NULL, NULL, NULL, 'in_progress', 1, '2025-09-23 17:52:59', '2025-09-15 23:25:51', NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:25:51', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 15, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DG50JV483A237Z4JVBK', '01K5FP4DETH7DREWBF7CJA6B5R', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-13', '2025-10-02', NULL, NULL, NULL, 'pending', 0, NULL, NULL, NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 69, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGAEGYMQVQPAJS25AYG', '01K5FP4DETH7DREWBF7CJA6B5R', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-09-15', '2025-10-03', NULL, NULL, NULL, 'completed', 0, NULL, '2025-09-18 23:31:35', NULL, '2025-09-18 23:31:35', NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 100, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGFG402WBGVQT1MCBRY', '01K5FP4DF8427N0G51H5EGAX68', '01K5F98J6H3MWA78DAFZTJCGJZ', '2025-09-16', '2025-09-22', NULL, NULL, NULL, 'overdue', 1, '2025-09-23 17:52:59', NULL, NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 40, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGMPSCC6TQ5JDHAZ1BP', '01K5FP4DFBWD26GCGWMXTQTTK6', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-14', '2025-09-30', NULL, NULL, NULL, 'completed', 0, NULL, '2025-09-17 23:31:35', NULL, '2025-09-16 23:31:35', NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 100, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGRN1ZG61P8FH94ZAYS', '01K5FP4DFBWD26GCGWMXTQTTK6', '01K5EPMK63B2S7RXXYJVZ4QX12', '2025-09-15', '2025-09-27', NULL, NULL, NULL, 'completed', 0, NULL, '2025-09-15 23:31:35', NULL, '2025-09-18 23:31:35', NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', '01K5EPMK63B2S7RXXYJVZ4QX12', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 100, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGV40KY4CZYS2TV8B3E', '01K5FP4DFFTQTT7CNCMY4N9F6T', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-16', '2025-09-28', NULL, NULL, NULL, 'pending', 0, NULL, NULL, NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 71, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DGZJH2V9BHS27G6SFRN', '01K5FP4DFFTQTT7CNCMY4N9F6T', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-19', '2025-09-30', NULL, NULL, NULL, 'in_progress', 0, NULL, '2025-09-15 23:31:35', NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 18, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DH45EH7T14QQW18Y23P', '01K5FP4DFRMCPPZTJSNVEEDGRZ', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18', '2025-09-26', NULL, NULL, NULL, 'in_progress', 0, NULL, '2025-09-16 23:31:35', NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 9, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DHA5JGMGN17GTGZYHXF', '01K5FP4DFRMCPPZTJSNVEEDGRZ', '01K5EPMK63B2S7RXXYJVZ4QX12', '2025-09-16', '2025-10-03', NULL, NULL, NULL, 'in_progress', 0, NULL, '2025-09-16 23:31:35', NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 6, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DHESNB79CCPPQ4RMXRQ', '01K5FP4DFVKRFPD43ANF4V5R4W', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-18', '2025-09-28', NULL, NULL, NULL, 'pending', 0, NULL, NULL, NULL, NULL, NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 24, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5FP4DHJ5AF7MDJHNC860PFS', '01K5FP4DFVKRFPD43ANF4V5R4W', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-14', '2025-09-22', NULL, NULL, NULL, 'completed', 0, NULL, '2025-09-15 23:31:35', NULL, '2025-09-18 23:31:35', NULL, 'Sample assignment for testing purposes.', NULL, NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-18 23:31:35', '2025-09-23 19:22:15', '2025-09-23 19:22:15', NULL, 100, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W003DR6N9ND5YFYXQ76QYE', '01K5W003DJJ5EAC9RCD3H93A3Q', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-23', '2025-09-23', '21:00:00', '2025-09-23 21:00:00', 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 18:14:53', '2025-09-23 19:25:50', '2025-09-23 19:25:50', NULL, 0, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W300ASYZWNH5PVZDQM5WCJ', '01K5W300AB10VED9SARP0N3XJQ', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-24', '2025-09-24', '13:30:00', '2025-09-24 13:30:00', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 19:07:16', '2025-09-23 19:25:50', '2025-09-23 19:25:50', NULL, 0, 0.25, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W3CJ9J3AG2YYT9ME25W72H', '01K5W3CJ97ZEEWCB960G3CDAGB', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-24', '2025-09-24', '13:00:00', '2025-09-24 13:00:00', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 19:14:07', '2025-09-23 19:14:38', '2025-09-23 19:14:38', NULL, 0, 0.00, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W473DH0YR5RKSARA9ASDGZ', '01K5W473D7449Q9S3RZTQ9KAZT', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-24', '2025-09-24', '13:00:00', '2025-09-24 13:00:00', 'in_progress', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 19:28:37', '2025-09-23 19:36:37', '2025-09-23 19:36:37', NULL, 50, 0.50, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W4XVZFG3S0YBNR267FDTPC', '01K5W4XVZ2HGYNSCRB73C234Q3', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-24', '2025-09-24', '13:00:00', '2025-09-24 13:00:00', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 19:41:03', '2025-09-23 19:41:18', '2025-09-23 19:41:18', NULL, 0, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W57W9SGNEQYSJ14Z5M7H3W', '01K5W57W9A6G09E1KDZ5AP7W83', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', '14:30:00', '2025-09-23 14:30:00', 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:46:31', '2025-09-23 19:46:31', NULL, NULL, 0, 2.50, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W58JH7Z4N1VKBKC9Q87PVZ', '01K5W58JGQ6CQ34BM67R696GD4', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', '15:30:00', '2025-09-23 15:30:00', 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:46:53', '2025-09-23 19:46:53', NULL, NULL, 0, 3.00, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W59XFZETQ6BPKDCXD2CSFR', '01K5W59XFH62G8XY6Q5WC15GRM', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', '14:30:00', NULL, 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:47:37', '2025-09-23 20:47:46', '2025-09-23 20:47:46', NULL, 0, 2.50, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W59XGN9XGW4RZ7T1MM9FY6', '01K5W59XGJAJEDK383CQSFAEP6', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', '14:30:00', NULL, 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:47:38', '2025-09-23 19:49:55', '2025-09-23 19:49:55', NULL, 0, 2.50, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W59XH3MZJCSWPJ1Y98H7VP', '01K5W59XH038QEV5MRZWSWZ7E9', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', '14:30:00', NULL, 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:47:38', '2025-09-23 20:45:29', '2025-09-23 20:45:29', NULL, 0, 2.50, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W5B50SQRM24CRBGN1VKMHV', '01K5W5B50C7HA529MF047P0WK6', '01K5E5GE8XBXG9V91DMK9NEB4Q', '2025-09-23', '2025-09-23', '2025-09-23', NULL, NULL, 'overdue', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Initial assignment', NULL, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 19:48:18', '2025-09-23 19:48:18', NULL, NULL, 0, NULL, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0),
('01K5W94NR32XXZ7C68CHKG1152', '01K5W94NQQM6SKB7N5Y80KFET6', '01K5EWWJVSJVYF2QWSTER2YVNK', '2025-09-23', '2025-09-24', '2025-09-24', '12:00:00', '2025-09-24 12:00:00', 'pending', 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Auto-assigned when task was created.', NULL, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 20:54:40', '2025-09-23 20:54:40', NULL, NULL, 0, 0.00, NULL, 0, NULL, 'normal', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_attachments`
--

CREATE TABLE `staff_task_attachments` (
  `id` char(26) NOT NULL COMMENT 'ULID primary key',
  `task_assignment_id` char(26) NOT NULL COMMENT 'Related task assignment',
  `staff_id` char(26) NOT NULL COMMENT 'Staff member who uploaded',
  `file_name` varchar(255) NOT NULL COMMENT 'Original file name',
  `file_path` varchar(500) NOT NULL COMMENT 'Storage path',
  `file_size` int(11) NOT NULL COMMENT 'File size in bytes',
  `mime_type` varchar(100) NOT NULL COMMENT 'File MIME type',
  `description` text DEFAULT NULL COMMENT 'File description',
  `storage_disk` varchar(50) NOT NULL DEFAULT 'local' COMMENT 'Storage disk (local, s3, etc.)',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether file is publicly accessible',
  `downloaded_at` timestamp NULL DEFAULT NULL COMMENT 'Last download timestamp',
  `download_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of downloads',
  `created_by` char(26) NOT NULL COMMENT 'Staff member who created attachment',
  `updated_by` char(26) DEFAULT NULL COMMENT 'Staff member who last updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_comments`
--

CREATE TABLE `staff_task_comments` (
  `id` char(26) NOT NULL COMMENT 'ULID primary key',
  `task_assignment_id` char(26) NOT NULL COMMENT 'Related task assignment',
  `staff_id` char(26) NOT NULL COMMENT 'Staff member who commented',
  `comment` text NOT NULL COMMENT 'Comment content',
  `comment_type` varchar(20) NOT NULL DEFAULT 'comment' COMMENT 'comment, update, status_change, attachment',
  `is_internal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Internal comment (not visible to assignee)',
  `created_by` char(26) NOT NULL COMMENT 'Staff member who created comment',
  `updated_by` char(26) DEFAULT NULL COMMENT 'Staff member who last updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_dependencies`
--

CREATE TABLE `staff_task_dependencies` (
  `id` char(26) NOT NULL COMMENT 'ULID primary key',
  `task_id` char(26) NOT NULL COMMENT 'Task that depends on another',
  `depends_on_task_id` char(26) NOT NULL COMMENT 'Task that must be completed first',
  `dependency_type` varchar(30) NOT NULL DEFAULT 'finish_to_start' COMMENT 'finish_to_start, start_to_start, finish_to_finish, start_to_finish',
  `lag_days` int(11) NOT NULL DEFAULT 0 COMMENT 'Days to wait after dependency completes',
  `created_by` char(26) NOT NULL COMMENT 'Staff member who created dependency',
  `updated_by` char(26) DEFAULT NULL COMMENT 'Staff member who last updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_notifications`
--

CREATE TABLE `staff_task_notifications` (
  `id` char(26) NOT NULL COMMENT 'ULID primary key',
  `task_assignment_id` char(26) NOT NULL COMMENT 'FK -> staff_task_assignments.id',
  `staff_id` char(26) NOT NULL COMMENT 'Notification recipient',
  `notification_type` varchar(32) NOT NULL COMMENT 'assignment, reminder, overdue, due_soon, completed, comment',
  `title` varchar(255) NOT NULL COMMENT 'Notification title',
  `message` text NOT NULL COMMENT 'Notification message',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether notification was read',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'When notification was sent',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'When notification was read',
  `created_by` char(26) NOT NULL COMMENT 'Staff member who created notification',
  `updated_by` char(26) DEFAULT NULL COMMENT 'Staff member who last updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_task_time_entries`
--

CREATE TABLE `staff_task_time_entries` (
  `id` char(26) NOT NULL COMMENT 'ULID primary key',
  `task_assignment_id` char(26) NOT NULL COMMENT 'FK -> staff_task_assignments.id',
  `staff_id` char(26) NOT NULL COMMENT 'Staff member who logged the time',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Time entry start',
  `end_time` timestamp NULL DEFAULT NULL COMMENT 'Time entry end',
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Cached duration in minutes',
  `description` text DEFAULT NULL COMMENT 'What was worked on',
  `is_billable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether time is billable',
  `created_by` char(26) NOT NULL COMMENT 'Staff member who created entry',
  `updated_by` char(26) DEFAULT NULL COMMENT 'Staff member who last updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_types`
--

CREATE TABLE `staff_types` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Internal name (system_admin, administrator, etc.)',
  `display_name` varchar(255) NOT NULL COMMENT 'Human readable name',
  `description` text DEFAULT NULL COMMENT 'Role description and responsibilities',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether this staff type is active',
  `priority` int(11) NOT NULL DEFAULT 0 COMMENT 'Priority level for access control (higher = more access)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` char(26) DEFAULT NULL,
  `updated_by` char(26) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_types`
--

INSERT INTO `staff_types` (`id`, `name`, `display_name`, `description`, `is_active`, `priority`, `created_at`, `updated_at`, `deleted_at`, `created_by`, `updated_by`) VALUES
('01K5E5G8J1MH9KM7A0S1E6N915', 'system_admin', 'System Admin', 'Full system access and configuration', 1, 100, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8JEM6M9N29AY8AC0601', 'administrator', 'Administrator', 'Administrative access to restaurant operations', 1, 80, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8JPZ2VM3Z5FMQ70HZF9', 'management', 'Management', 'Management level access and oversight', 1, 60, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8JSNR0X00MC71D24K8X', 'chief', 'Chief', 'Head chef with kitchen management responsibilities', 1, 50, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8JXK8YGE13TYS0PJ4F9', 'kitchen_porter', 'Kitchen Porter', 'Kitchen support and cleaning duties', 1, 25, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8K1CKY8QR5AVMYCTMGX', 'injera_maker', 'Injera Maker', 'Specialized injera production and baking', 1, 30, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5E5G8K4ASYPQGVXH16TZSN5', 'waiter', 'Waiter', 'Front-of-house customer service and order taking', 1, 35, '2025-09-18 09:21:43', '2025-09-18 09:21:43', NULL, NULL, NULL),
('01K5EA2753Q4PT58FPXH7J5SFB', 'bar_tender', 'Bar Tender', 'keep working in the bar', 1, 50, '2025-09-18 10:41:26', '2025-09-18 10:41:26', NULL, NULL, NULL),
('01K5EA9V5D6XKAJJ49DKF4QS9X', 'dabo_gagari', 'Dabo Gagari', 'Nice dabo maker', 1, 50, '2025-09-18 10:45:36', '2025-09-18 10:58:07', NULL, NULL, NULL),
('01K5F98H2BHP481A4QPWW8PAWA', 'chef', 'Chef', 'Kitchen staff responsible for food preparation and cooking', 1, 50, '2025-09-18 19:46:39', '2025-09-18 19:46:39', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_categories`
--

CREATE TABLE `task_categories` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6B7280',
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` char(26) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_categories`
--

INSERT INTO `task_categories` (`id`, `name`, `slug`, `description`, `color`, `icon`, `parent_id`, `is_active`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5VS9RN6CQE3XKSDD4ZFK0HJ', 'Kitchen Operations', 'kitchen-operations', 'Tasks related to kitchen operations and food preparation', '#10B981', 'fas fa-utensils', NULL, 1, 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNCB12PD5YYJ99SY07Y', 'Customer Service', 'customer-service', 'Tasks related to customer service and front-of-house operations', '#3B82F6', 'fas fa-users', NULL, 1, 2, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNE6SYCCDD377YHQSCX', 'Cleaning & Sanitation', 'cleaning-sanitation', 'Cleaning, sanitizing, and hygiene-related tasks', '#06B6D4', 'fas fa-broom', NULL, 1, 3, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNM92BWG2BYC3HP4GBC', 'Inventory Management', 'inventory-management', 'Stock management, ordering, and inventory-related tasks', '#8B5CF6', 'fas fa-boxes', NULL, 1, 4, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNPXJF9VH30WWCX4739', 'Administration', 'administration', 'Administrative tasks, paperwork, and management duties', '#F59E0B', 'fas fa-clipboard-list', NULL, 1, 5, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNSFPHX3TQPW9TWT6DJ', 'Training & Development', 'training-development', 'Staff training, skill development, and educational tasks', '#EF4444', 'fas fa-graduation-cap', NULL, 1, 6, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNXPBND7WCCA1HM1SRF', 'Food Prep', 'food-prep', 'Food preparation and cooking tasks', '#059669', 'fas fa-cut', '01K5VS9RN6CQE3XKSDD4ZFK0HJ', 1, 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RNZZ79ZP0YP8JZPHY90', 'Equipment Maintenance', 'equipment-maintenance', 'Kitchen equipment maintenance and repairs', '#047857', 'fas fa-wrench', '01K5VS9RN6CQE3XKSDD4ZFK0HJ', 1, 2, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_notes`
--

CREATE TABLE `task_notes` (
  `id` char(26) NOT NULL,
  `staff_task_id` char(26) NOT NULL,
  `staff_task_assignment_id` char(26) DEFAULT NULL,
  `staff_id` char(26) NOT NULL,
  `note_type` enum('instruction','progress','issue','completion','general') NOT NULL DEFAULT 'general',
  `content` text NOT NULL COMMENT 'Note content',
  `is_private` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether note is private to creator',
  `is_important` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether note is marked as important',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_priorities`
--

CREATE TABLE `task_priorities` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6B7280',
  `icon` varchar(255) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_priorities`
--

INSERT INTO `task_priorities` (`id`, `name`, `slug`, `description`, `color`, `icon`, `level`, `is_active`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5VS9RMTSY9ZM211ES0QV8CT', 'Low', 'low', 'Low priority tasks that can be completed when time allows', '#6B7280', 'fas fa-arrow-down', 1, 1, 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RMZHM65SX7J4VRXP9CR', 'Medium', 'medium', 'Standard priority tasks for regular operations', '#F59E0B', 'fas fa-minus', 2, 1, 2, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RN1J6JBR4GRSMKSB8XF', 'High', 'high', 'Important tasks that should be completed soon', '#EF4444', 'fas fa-arrow-up', 3, 1, 3, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RN3GTSHV8H5CKT0DYY9', 'Urgent', 'urgent', 'Critical tasks that require immediate attention', '#DC2626', 'fas fa-exclamation-triangle', 4, 1, 4, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_reminders`
--

CREATE TABLE `task_reminders` (
  `id` char(26) NOT NULL,
  `staff_task_assignment_id` char(26) NOT NULL,
  `reminder_type` enum('due_soon','overdue','scheduled','custom') NOT NULL DEFAULT 'due_soon',
  `scheduled_for` datetime NOT NULL COMMENT 'When reminder should be sent',
  `sent_at` datetime DEFAULT NULL COMMENT 'When reminder was actually sent',
  `status` enum('pending','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  `message` text DEFAULT NULL COMMENT 'Custom reminder message',
  `delivery_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'How reminder should be delivered (email, notification, etc.)' CHECK (json_valid(`delivery_methods`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_statuses`
--

CREATE TABLE `task_statuses` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6B7280',
  `icon` varchar(255) DEFAULT NULL,
  `type` enum('pending','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_tags`
--

CREATE TABLE `task_tags` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6B7280',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_tags`
--

INSERT INTO `task_tags` (`id`, `name`, `slug`, `description`, `color`, `is_active`, `usage_count`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5VS9RP2ZZJ288GJ0KB1AQ5D', 'urgent', 'urgent', 'Tasks that need immediate attention', '#DC2626', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RP5GCTM6MKX2NNTGVKK', 'daily', 'daily', 'Tasks performed every day', '#10B981', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RP8Y5E7XTPRT933PE56', 'weekly', 'weekly', 'Tasks performed weekly', '#3B82F6', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RPBE4XY96DZDYFTYN3Y', 'equipment', 'equipment', 'Equipment-related tasks', '#8B5CF6', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RPEDZWWFGRRMZG4XJF5', 'safety', 'safety', 'Safety and compliance tasks', '#EF4444', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RPGNA8S9Q5T9Z0MZETS', 'training', 'training', 'Training and educational tasks', '#F59E0B', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RPJ9Y20D6ZPPMDG60TX', 'customer-facing', 'customer-facing', 'Tasks that involve customer interaction', '#06B6D4', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RPM7A1529BGXAC6QZ0V', 'inventory', 'inventory', 'Inventory and stock management tasks', '#8B5CF6', 1, 0, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VTB8E2KBW0X5KFM238M0N7', 'Bar', 'bar', 'Tasks related to the bar', '#26f50a', 1, 0, '01K5EWWJVSJVYF2QWSTER2YVNK', NULL, '2025-09-23 16:36:07', '2025-09-23 16:36:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_templates`
--

CREATE TABLE `task_templates` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`template_data`)),
  `task_type_id` char(26) DEFAULT NULL,
  `task_category_id` char(26) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_time_entries`
--

CREATE TABLE `task_time_entries` (
  `id` char(26) NOT NULL,
  `staff_task_assignment_id` char(26) NOT NULL,
  `staff_id` char(26) NOT NULL,
  `start_time` datetime NOT NULL COMMENT 'When work started',
  `end_time` datetime DEFAULT NULL COMMENT 'When work ended',
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `description` text DEFAULT NULL COMMENT 'Description of work done',
  `entry_type` enum('work','break','interruption') NOT NULL DEFAULT 'work',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_types`
--

CREATE TABLE `task_types` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6B7280',
  `icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` char(26) NOT NULL,
  `updated_by` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_types`
--

INSERT INTO `task_types` (`id`, `name`, `slug`, `description`, `color`, `icon`, `is_active`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01K5VS9RKY7HDZGFN62XV4X6QX', 'Daily Tasks', 'daily-tasks', 'Recurring daily tasks that need to be completed every day', '#10B981', 'fas fa-calendar-day', 1, 1, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RMET6AAFAG6088XHJ4C', 'Weekly Tasks', 'weekly-tasks', 'Tasks that are performed on a weekly basis', '#3B82F6', 'fas fa-calendar-week', 1, 2, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RMG36YBC702YB249313', 'Monthly Tasks', 'monthly-tasks', 'Tasks that are performed monthly', '#8B5CF6', 'fas fa-calendar-alt', 1, 3, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RMJHSFNATQY7ASWHY6D', 'One-time Tasks', 'one-time-tasks', 'Special projects or one-time assignments', '#F59E0B', 'fas fa-tasks', 1, 4, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL),
('01K5VS9RMN5AXQA47RD206E3PR', 'Maintenance', 'maintenance', 'Equipment and facility maintenance tasks', '#EF4444', 'fas fa-tools', 1, 5, '01K5E5GE8XBXG9V91DMK9NEB4Q', NULL, '2025-09-23 16:17:50', '2025-09-23 16:17:50', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_username_unique` (`username`),
  ADD KEY `staff_username_index` (`username`),
  ADD KEY `staff_staff_type_id_index` (`staff_type_id`),
  ADD KEY `staff_status_index` (`status`),
  ADD KEY `staff_last_login_at_index` (`last_login_at`),
  ADD KEY `staff_first_name_last_name_index` (`first_name`,`last_name`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_attendance_staff_id_clock_in_index` (`staff_id`,`clock_in`),
  ADD KEY `staff_attendance_status_index` (`status`),
  ADD KEY `staff_attendance_clock_in_index` (`clock_in`),
  ADD KEY `idx_staff_clock_in` (`staff_id`,`clock_in`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `staff_attendance_created_by_foreign` (`created_by`),
  ADD KEY `staff_attendance_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `staff_payroll_records`
--
ALTER TABLE `staff_payroll_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_payroll_per_period` (`staff_id`,`pay_period_start`,`pay_period_end`),
  ADD KEY `staff_payroll_records_staff_id_pay_period_start_index` (`staff_id`,`pay_period_start`),
  ADD KEY `staff_payroll_records_status_index` (`status`),
  ADD KEY `staff_payroll_records_processed_by_index` (`processed_by`),
  ADD KEY `staff_payroll_records_pay_period_start_pay_period_end_index` (`pay_period_start`,`pay_period_end`),
  ADD KEY `idx_pay_period` (`pay_period_start`,`pay_period_end`),
  ADD KEY `staff_payroll_records_created_by_foreign` (`created_by`),
  ADD KEY `staff_payroll_records_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `staff_performance_goals`
--
ALTER TABLE `staff_performance_goals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_goal_per_start_date` (`staff_id`,`goal_title`,`start_date`),
  ADD KEY `staff_performance_goals_staff_id_status_index` (`staff_id`,`status`),
  ADD KEY `staff_performance_goals_target_date_index` (`target_date`),
  ADD KEY `staff_performance_goals_priority_index` (`priority`),
  ADD KEY `staff_performance_goals_goal_type_index` (`goal_type`);

--
-- Indexes for table `staff_performance_metrics`
--
ALTER TABLE `staff_performance_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_metric_per_period` (`staff_id`,`metric_name`,`recorded_date`,`measurement_period`),
  ADD KEY `staff_performance_metrics_staff_id_recorded_date_index` (`staff_id`,`recorded_date`),
  ADD KEY `staff_performance_metrics_measurement_period_index` (`measurement_period`),
  ADD KEY `staff_performance_metrics_data_source_index` (`data_source`),
  ADD KEY `staff_performance_metrics_metric_name_recorded_date_index` (`metric_name`,`recorded_date`);

--
-- Indexes for table `staff_performance_reviews`
--
ALTER TABLE `staff_performance_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review_per_period` (`staff_id`,`review_period_start`,`review_period_end`),
  ADD KEY `staff_performance_reviews_staff_id_review_date_index` (`staff_id`,`review_date`),
  ADD KEY `staff_performance_reviews_reviewer_id_index` (`reviewer_id`),
  ADD KEY `staff_performance_reviews_status_index` (`status`),
  ADD KEY `idx_review_period` (`review_period_start`,`review_period_end`),
  ADD KEY `staff_performance_reviews_created_by_foreign` (`created_by`),
  ADD KEY `staff_performance_reviews_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `staff_performance_review_acknowledgements`
--
ALTER TABLE `staff_performance_review_acknowledgements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review_acknowledgement` (`performance_review_id`,`acknowledged_by`),
  ADD KEY `idx_review_ack_review_id` (`performance_review_id`),
  ADD KEY `idx_review_ack_staff_id` (`acknowledged_by`),
  ADD KEY `idx_review_ack_date` (`acknowledged_at`);

--
-- Indexes for table `staff_performance_templates`
--
ALTER TABLE `staff_performance_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_template_per_type_version` (`staff_type_id`,`template_name`,`version`),
  ADD KEY `staff_performance_templates_staff_type_id_is_active_index` (`staff_type_id`,`is_active`),
  ADD KEY `staff_performance_templates_review_frequency_index` (`review_frequency`),
  ADD KEY `staff_performance_templates_version_index` (`version`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_profiles_employee_id_unique` (`employee_id`),
  ADD KEY `staff_profiles_staff_id_index` (`staff_id`),
  ADD KEY `staff_profiles_employee_id_index` (`employee_id`),
  ADD KEY `staff_profiles_created_by_index` (`created_by`),
  ADD KEY `staff_profiles_updated_by_index` (`updated_by`);

--
-- Indexes for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_shifts_is_active_index` (`is_active`),
  ADD KEY `staff_shifts_created_by_index` (`created_by`),
  ADD KEY `staff_shifts_start_time_end_time_index` (`start_time`,`end_time`);

--
-- Indexes for table `staff_shift_assignments`
--
ALTER TABLE `staff_shift_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_shift_per_day` (`staff_id`,`assigned_date`,`staff_shift_id`),
  ADD KEY `staff_shift_assignments_staff_id_assigned_date_index` (`staff_id`,`assigned_date`),
  ADD KEY `staff_shift_assignments_staff_shift_id_index` (`staff_shift_id`),
  ADD KEY `staff_shift_assignments_status_index` (`status`),
  ADD KEY `staff_shift_assignments_assigned_by_index` (`assigned_by`),
  ADD KEY `idx_assigned_date` (`assigned_date`),
  ADD KEY `idx_assignment_status` (`status`),
  ADD KEY `staff_shift_assignments_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `staff_tasks`
--
ALTER TABLE `staff_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_tasks_task_type_index` (`task_type`),
  ADD KEY `staff_tasks_priority_index` (`priority`),
  ADD KEY `staff_tasks_is_active_index` (`is_active`),
  ADD KEY `staff_tasks_created_by_index` (`created_by`),
  ADD KEY `idx_staff_tasks_category` (`category`),
  ADD KEY `idx_staff_tasks_template` (`is_template`),
  ADD KEY `idx_staff_tasks_recurrence` (`recurrence_pattern`),
  ADD KEY `idx_staff_tasks_updated_by` (`updated_by`),
  ADD KEY `idx_staff_tasks_deleted_at` (`deleted_at`),
  ADD KEY `idx_task_schedule` (`scheduled_date`,`scheduled_time`),
  ADD KEY `idx_auto_assign` (`auto_assign`);

--
-- Indexes for table `staff_task_assignments`
--
ALTER TABLE `staff_task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_task_assignment_per_day` (`staff_task_id`,`staff_id`,`assigned_date`),
  ADD KEY `staff_task_assignments_completed_by_foreign` (`completed_by`),
  ADD KEY `staff_task_assignments_staff_id_assigned_date_index` (`staff_id`,`assigned_date`),
  ADD KEY `staff_task_assignments_staff_task_id_index` (`staff_task_id`),
  ADD KEY `staff_task_assignments_status_index` (`status`),
  ADD KEY `staff_task_assignments_due_date_index` (`due_date`),
  ADD KEY `staff_task_assignments_assigned_by_index` (`assigned_by`),
  ADD KEY `idx_status_due_date` (`status`,`due_date`),
  ADD KEY `idx_task_assignments_progress` (`progress_percentage`),
  ADD KEY `idx_task_assignments_priority` (`priority_override`),
  ADD KEY `idx_task_assignments_updated_by` (`updated_by`),
  ADD KEY `idx_task_assignments_deleted_at` (`deleted_at`),
  ADD KEY `idx_assignment_schedule` (`scheduled_date`,`scheduled_time`),
  ADD KEY `idx_overdue_status` (`is_overdue`,`status`),
  ADD KEY `idx_urgency` (`urgency_level`),
  ADD KEY `idx_scheduled_datetime` (`scheduled_datetime`);

--
-- Indexes for table `staff_task_attachments`
--
ALTER TABLE `staff_task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_task_attachments_updated_by` (`updated_by`),
  ADD KEY `idx_task_attachments_assignment_id` (`task_assignment_id`),
  ADD KEY `idx_task_attachments_staff_id` (`staff_id`),
  ADD KEY `idx_task_attachments_mime_type` (`mime_type`),
  ADD KEY `idx_task_attachments_storage_disk` (`storage_disk`),
  ADD KEY `idx_task_attachments_public` (`is_public`),
  ADD KEY `idx_task_attachments_created_by` (`created_by`),
  ADD KEY `idx_task_attachments_created_at` (`created_at`),
  ADD KEY `idx_task_attachments_deleted_at` (`deleted_at`);

--
-- Indexes for table `staff_task_comments`
--
ALTER TABLE `staff_task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_task_comments_updated_by` (`updated_by`),
  ADD KEY `idx_task_comments_assignment_id` (`task_assignment_id`),
  ADD KEY `idx_task_comments_staff_id` (`staff_id`),
  ADD KEY `idx_task_comments_type` (`comment_type`),
  ADD KEY `idx_task_comments_internal` (`is_internal`),
  ADD KEY `idx_task_comments_created_by` (`created_by`),
  ADD KEY `idx_task_comments_created_at` (`created_at`),
  ADD KEY `idx_task_comments_deleted_at` (`deleted_at`);

--
-- Indexes for table `staff_task_dependencies`
--
ALTER TABLE `staff_task_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_task_dependency` (`task_id`,`depends_on_task_id`,`deleted_at`),
  ADD KEY `fk_task_deps_updated_by` (`updated_by`),
  ADD KEY `idx_task_deps_task_id` (`task_id`),
  ADD KEY `idx_task_deps_depends_on_id` (`depends_on_task_id`),
  ADD KEY `idx_task_deps_type` (`dependency_type`),
  ADD KEY `idx_task_deps_created_by` (`created_by`),
  ADD KEY `idx_task_deps_deleted_at` (`deleted_at`);

--
-- Indexes for table `staff_task_notifications`
--
ALTER TABLE `staff_task_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_assignment_id` (`task_assignment_id`),
  ADD KEY `idx_notifications_staff_id` (`staff_id`),
  ADD KEY `idx_notifications_type` (`notification_type`),
  ADD KEY `idx_notifications_read` (`is_read`),
  ADD KEY `idx_notifications_sent_at` (`sent_at`),
  ADD KEY `idx_notifications_created_by` (`created_by`),
  ADD KEY `idx_notifications_deleted_at` (`deleted_at`),
  ADD KEY `fk_notifications_updated_by` (`updated_by`);

--
-- Indexes for table `staff_task_time_entries`
--
ALTER TABLE `staff_task_time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_time_entries_assignment_id` (`task_assignment_id`),
  ADD KEY `idx_time_entries_staff_id` (`staff_id`),
  ADD KEY `idx_time_entries_start_time` (`start_time`),
  ADD KEY `idx_time_entries_billable` (`is_billable`),
  ADD KEY `idx_time_entries_created_by` (`created_by`),
  ADD KEY `idx_time_entries_deleted_at` (`deleted_at`),
  ADD KEY `fk_time_entries_updated_by` (`updated_by`);

--
-- Indexes for table `staff_types`
--
ALTER TABLE `staff_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_types_name_unique` (`name`),
  ADD KEY `staff_types_name_index` (`name`),
  ADD KEY `staff_types_is_active_index` (`is_active`),
  ADD KEY `staff_types_priority_index` (`priority`),
  ADD KEY `staff_types_created_by_foreign` (`created_by`),
  ADD KEY `staff_types_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `task_categories`
--
ALTER TABLE `task_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_categories_name_unique` (`name`),
  ADD UNIQUE KEY `task_categories_slug_unique` (`slug`),
  ADD KEY `task_categories_created_by_foreign` (`created_by`),
  ADD KEY `task_categories_updated_by_foreign` (`updated_by`),
  ADD KEY `task_categories_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `task_categories_parent_id_sort_order_index` (`parent_id`,`sort_order`),
  ADD KEY `task_categories_slug_index` (`slug`);

--
-- Indexes for table `task_notes`
--
ALTER TABLE `task_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_notes_staff_id_foreign` (`staff_id`),
  ADD KEY `idx_task_notes_timeline` (`staff_task_id`,`created_at`),
  ADD KEY `idx_assignment_notes_timeline` (`staff_task_assignment_id`,`created_at`),
  ADD KEY `idx_note_type` (`note_type`),
  ADD KEY `idx_important_notes` (`is_important`);

--
-- Indexes for table `task_priorities`
--
ALTER TABLE `task_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_priorities_name_unique` (`name`),
  ADD UNIQUE KEY `task_priorities_slug_unique` (`slug`),
  ADD UNIQUE KEY `task_priorities_level_unique` (`level`),
  ADD KEY `task_priorities_created_by_foreign` (`created_by`),
  ADD KEY `task_priorities_updated_by_foreign` (`updated_by`),
  ADD KEY `task_priorities_is_active_level_index` (`is_active`,`level`),
  ADD KEY `task_priorities_slug_index` (`slug`);

--
-- Indexes for table `task_reminders`
--
ALTER TABLE `task_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_reminders_staff_task_assignment_id_foreign` (`staff_task_assignment_id`),
  ADD KEY `idx_reminder_schedule` (`scheduled_for`,`status`),
  ADD KEY `idx_reminder_type` (`reminder_type`);

--
-- Indexes for table `task_statuses`
--
ALTER TABLE `task_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_statuses_name_unique` (`name`),
  ADD UNIQUE KEY `task_statuses_slug_unique` (`slug`),
  ADD KEY `task_statuses_created_by_foreign` (`created_by`),
  ADD KEY `task_statuses_updated_by_foreign` (`updated_by`),
  ADD KEY `task_statuses_is_active_type_sort_order_index` (`is_active`,`type`,`sort_order`),
  ADD KEY `task_statuses_slug_index` (`slug`);

--
-- Indexes for table `task_tags`
--
ALTER TABLE `task_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_tags_name_unique` (`name`),
  ADD UNIQUE KEY `task_tags_slug_unique` (`slug`),
  ADD KEY `task_tags_created_by_foreign` (`created_by`),
  ADD KEY `task_tags_updated_by_foreign` (`updated_by`),
  ADD KEY `task_tags_is_active_usage_count_index` (`is_active`,`usage_count`),
  ADD KEY `task_tags_slug_index` (`slug`);

--
-- Indexes for table `task_templates`
--
ALTER TABLE `task_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_templates_slug_unique` (`slug`),
  ADD KEY `task_templates_task_type_id_foreign` (`task_type_id`),
  ADD KEY `task_templates_task_category_id_foreign` (`task_category_id`),
  ADD KEY `task_templates_updated_by_foreign` (`updated_by`),
  ADD KEY `task_templates_is_active_is_public_index` (`is_active`,`is_public`),
  ADD KEY `task_templates_created_by_is_active_index` (`created_by`,`is_active`),
  ADD KEY `task_templates_slug_index` (`slug`);

--
-- Indexes for table `task_time_entries`
--
ALTER TABLE `task_time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_time_entries_timeline` (`staff_task_assignment_id`,`start_time`),
  ADD KEY `idx_staff_time_entries` (`staff_id`,`start_time`),
  ADD KEY `idx_entry_type` (`entry_type`);

--
-- Indexes for table `task_types`
--
ALTER TABLE `task_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_types_name_unique` (`name`),
  ADD UNIQUE KEY `task_types_slug_unique` (`slug`),
  ADD KEY `task_types_created_by_foreign` (`created_by`),
  ADD KEY `task_types_updated_by_foreign` (`updated_by`),
  ADD KEY `task_types_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `task_types_slug_index` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_staff_type_id_foreign` FOREIGN KEY (`staff_type_id`) REFERENCES `staff_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `staff_attendance_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_attendance_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_attendance_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_payroll_records`
--
ALTER TABLE `staff_payroll_records`
  ADD CONSTRAINT `staff_payroll_records_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_payroll_records_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_payroll_records_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_payroll_records_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_performance_reviews`
--
ALTER TABLE `staff_performance_reviews`
  ADD CONSTRAINT `staff_performance_reviews_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_performance_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_performance_reviews_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_performance_reviews_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `staff_profiles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_profiles_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_profiles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  ADD CONSTRAINT `staff_shifts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_shift_assignments`
--
ALTER TABLE `staff_shift_assignments`
  ADD CONSTRAINT `staff_shift_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_assignments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_assignments_staff_shift_id_foreign` FOREIGN KEY (`staff_shift_id`) REFERENCES `staff_shifts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_assignments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_tasks`
--
ALTER TABLE `staff_tasks`
  ADD CONSTRAINT `fk_staff_tasks_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_assignments`
--
ALTER TABLE `staff_task_assignments`
  ADD CONSTRAINT `staff_task_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_task_assignments_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_task_assignments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_task_assignments_staff_task_id_foreign` FOREIGN KEY (`staff_task_id`) REFERENCES `staff_tasks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_task_assignments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_attachments`
--
ALTER TABLE `staff_task_attachments`
  ADD CONSTRAINT `fk_task_attachments_assignment_id` FOREIGN KEY (`task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_attachments_created_by` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_attachments_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_attachments_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_comments`
--
ALTER TABLE `staff_task_comments`
  ADD CONSTRAINT `fk_task_comments_assignment_id` FOREIGN KEY (`task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_comments_created_by` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_comments_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_comments_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_dependencies`
--
ALTER TABLE `staff_task_dependencies`
  ADD CONSTRAINT `fk_task_deps_created_by` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_deps_depends_on_id` FOREIGN KEY (`depends_on_task_id`) REFERENCES `staff_tasks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_deps_task_id` FOREIGN KEY (`task_id`) REFERENCES `staff_tasks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_deps_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_notifications`
--
ALTER TABLE `staff_task_notifications`
  ADD CONSTRAINT `fk_notifications_assignment_id` FOREIGN KEY (`task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_task_time_entries`
--
ALTER TABLE `staff_task_time_entries`
  ADD CONSTRAINT `fk_time_entries_assignment_id` FOREIGN KEY (`task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_time_entries_created_by` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_time_entries_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_time_entries_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `staff_types`
--
ALTER TABLE `staff_types`
  ADD CONSTRAINT `staff_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_types_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_categories`
--
ALTER TABLE `task_categories`
  ADD CONSTRAINT `task_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `task_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_notes`
--
ALTER TABLE `task_notes`
  ADD CONSTRAINT `task_notes_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_notes_staff_task_assignment_id_foreign` FOREIGN KEY (`staff_task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_notes_staff_task_id_foreign` FOREIGN KEY (`staff_task_id`) REFERENCES `staff_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_priorities`
--
ALTER TABLE `task_priorities`
  ADD CONSTRAINT `task_priorities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_priorities_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_reminders`
--
ALTER TABLE `task_reminders`
  ADD CONSTRAINT `task_reminders_staff_task_assignment_id_foreign` FOREIGN KEY (`staff_task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_statuses`
--
ALTER TABLE `task_statuses`
  ADD CONSTRAINT `task_statuses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_statuses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_tags`
--
ALTER TABLE `task_tags`
  ADD CONSTRAINT `task_tags_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_tags_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_templates`
--
ALTER TABLE `task_templates`
  ADD CONSTRAINT `task_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_templates_task_category_id_foreign` FOREIGN KEY (`task_category_id`) REFERENCES `task_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `task_templates_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `task_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `task_time_entries`
--
ALTER TABLE `task_time_entries`
  ADD CONSTRAINT `task_time_entries_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_time_entries_staff_task_assignment_id_foreign` FOREIGN KEY (`staff_task_assignment_id`) REFERENCES `staff_task_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_types`
--
ALTER TABLE `task_types`
  ADD CONSTRAINT `task_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `task_types_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
