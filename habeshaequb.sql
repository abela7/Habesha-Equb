-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 02, 2025 at 10:54 AM
-- Server version: 10.11.13-MariaDB-cll-lve
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `habeshjv_habeshaequb`
--

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
  `language_preference` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Web language: 0=English, 1=Amharic',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `phone`, `password`, `is_active`, `language_preference`, `created_at`, `updated_at`) VALUES
(8, 'abel', 'abelgoytom77@gmail.com', '+447360436171', '$2y$12$SSw//y2CE/4Q85XAxF4HEee4SX5QtzSifXBX4xHbiSC2X54lZP/eW', 1, 0, '2025-07-29 15:13:13', '2025-08-01 01:10:10');

-- --------------------------------------------------------

--
-- Table structure for table `device_tracking`
--

CREATE TABLE `device_tracking` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `device_fingerprint` varchar(32) NOT NULL,
  `device_token` varchar(64) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `device_tracking`
--

INSERT INTO `device_tracking` (`id`, `email`, `device_fingerprint`, `device_token`, `expires_at`, `user_agent`, `ip_address`, `created_at`, `last_seen`, `is_approved`) VALUES
(1, 'abelgoytom77@gmail.com', 'dv_52ed2545d59e9df4', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '193.237.166.126', '2025-07-31 14:13:26', '2025-08-02 08:27:16', 1),
(2, 'abeldemessie77@gmail.com', 'dv_52ed2545d59e9df4', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '193.237.166.126', '2025-07-31 16:03:51', '2025-08-02 07:56:36', 1),
(3, 'barnabasdagnachew25@gmail.com', 'dv_f6b20ee8a35c5adb', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '31.94.73.40', '2025-08-01 19:46:47', '2025-08-01 19:46:47', 0),
(4, 'abelgtm@gmail.com', 'dv_7ddeda88d0c599cc', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '143.159.176.60', '2025-08-02 05:34:21', '2025-08-02 05:34:21', 0),
(5, 'abelgtm77@gmail.com', 'dv_7ddeda88d0c599cc', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '143.159.176.60', '2025-08-02 06:25:58', '2025-08-02 07:53:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `email_preferences`
--

CREATE TABLE `email_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receives_notifications` tinyint(1) DEFAULT 1,
  `receives_reminders` tinyint(1) DEFAULT 1,
  `unsubscribe_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_rate_limits`
--

CREATE TABLE `email_rate_limits` (
  `id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `sent_count` int(11) DEFAULT 1,
  `last_sent_at` timestamp NULL DEFAULT current_timestamp(),
  `reset_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_rate_limits`
--

INSERT INTO `email_rate_limits` (`id`, `email_address`, `email_type`, `sent_count`, `last_sent_at`, `reset_at`) VALUES
(1, 'abelgtm77@gmail.com', 'account_approved', 4, '2025-08-02 08:53:48', '2025-08-03 07:53:35'),
(2, 'abeldemessie77@gmail.com', 'account_approved', 2, '2025-08-02 09:14:56', '2025-08-03 07:56:36'),
(3, 'abelgoytom77@gmail.com', 'account_approved', 4, '2025-08-02 09:12:10', '2025-08-03 08:27:16'),
(11, 'abelgoytom77@gmail.com', 'otp_login', 7, '2025-08-02 09:52:54', '2025-08-02 10:30:05'),
(16, 'abeldemessie77@gmail.com', 'otp_login', 1, '2025-08-02 09:46:53', '2025-08-02 10:46:53');

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
-- Table structure for table `equb_settings`
--

CREATE TABLE `equb_settings` (
  `id` int(11) NOT NULL,
  `equb_id` varchar(20) NOT NULL COMMENT 'Auto-generated: EQB-2024-001, EQB-2024-002, etc.',
  `equb_name` varchar(100) NOT NULL COMMENT 'e.g., First Term Equb, Summer 2024 Equb',
  `equb_description` text DEFAULT NULL COMMENT 'Detailed description of this equb term',
  `status` enum('planning','active','completed','suspended','cancelled') NOT NULL DEFAULT 'planning',
  `max_members` int(3) NOT NULL COMMENT 'Maximum number of members for this equb term',
  `current_members` int(3) DEFAULT 0 COMMENT 'Current number of enrolled members',
  `duration_months` int(2) NOT NULL COMMENT 'How many months this equb will run',
  `start_date` date NOT NULL COMMENT 'Equb term start date',
  `end_date` date NOT NULL COMMENT 'Calculated end date based on duration',
  `payment_tiers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON: [{"amount":1000,"tag":"full","description":"Full Member"},{"amount":500,"tag":"half","description":"Half Member"}]' CHECK (json_valid(`payment_tiers`)),
  `currency` varchar(5) DEFAULT '£' COMMENT 'Currency symbol',
  `payout_day` int(2) DEFAULT 5 COMMENT 'Day of month for payouts (default: 5th)',
  `admin_fee` decimal(8,2) DEFAULT 10.00 COMMENT 'Admin service fee per payout',
  `late_fee` decimal(8,2) DEFAULT 20.00 COMMENT 'Late payment penalty',
  `grace_period_days` int(2) DEFAULT 2 COMMENT 'Grace period for late payments',
  `auto_assign_positions` tinyint(1) DEFAULT 1 COMMENT '1=Auto assign payout positions, 0=Manual',
  `position_assignment_method` enum('random','registration_order','payment_amount','custom') DEFAULT 'registration_order',
  `terms_en` text DEFAULT NULL COMMENT 'English terms and conditions for this specific equb',
  `terms_am` text DEFAULT NULL COMMENT 'Amharic terms and conditions for this specific equb',
  `special_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of special rules for this equb term' CHECK (json_valid(`special_rules`)),
  `created_by_admin_id` int(11) NOT NULL,
  `managed_by_admin_id` int(11) DEFAULT NULL COMMENT 'Current managing admin',
  `approval_required` tinyint(1) DEFAULT 1 COMMENT '1=Admin must approve member registrations',
  `registration_start_date` date DEFAULT NULL COMMENT 'When registration opens',
  `registration_end_date` date DEFAULT NULL COMMENT 'When registration closes',
  `is_public` tinyint(1) DEFAULT 1 COMMENT '1=Visible to public, 0=Private/Invitation only',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT '1=Featured on homepage',
  `total_pool_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total expected pool (calculated)',
  `collected_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total collected so far',
  `distributed_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total distributed so far',
  `notes` text DEFAULT NULL COMMENT 'Admin notes about this equb term',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equb_settings`
--

INSERT INTO `equb_settings` (`id`, `equb_id`, `equb_name`, `equb_description`, `status`, `max_members`, `current_members`, `duration_months`, `start_date`, `end_date`, `payment_tiers`, `currency`, `payout_day`, `admin_fee`, `late_fee`, `grace_period_days`, `auto_assign_positions`, `position_assignment_method`, `terms_en`, `terms_am`, `special_rules`, `created_by_admin_id`, `managed_by_admin_id`, `approval_required`, `registration_start_date`, `registration_end_date`, `is_public`, `is_featured`, `total_pool_amount`, `collected_amount`, `distributed_amount`, `notes`, `created_at`, `updated_at`) VALUES
(2, 'EQB-2025-001', 'Selam Equb', 'A new Equb!', 'active', 8, 2, 8, '2025-07-01', '2026-03-01', '[{\"amount\":1000,\"tag\":\"Full\",\"description\":\"Full member\"},{\"amount\":500,\"tag\":\"Half\",\"description\":\"Half member\"}]', '£', 5, 10.00, 20.00, 2, 1, 'custom', NULL, NULL, NULL, 8, NULL, 1, NULL, NULL, 1, 0, 64000.00, 2000.00, 2000.00, '', '2025-07-31 14:18:24', '2025-07-31 18:08:20');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `equb_settings_id` int(11) DEFAULT NULL COMMENT 'Which equb term this member belongs to',
  `member_id` varchar(20) NOT NULL COMMENT 'Auto-generated: HEM-AG1, HEM-AM2, etc.',
  `username` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
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
  `rules_agreed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Agreed to rules, 0=Not agreed',
  `notes` text DEFAULT NULL COMMENT 'Admin notes about member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable email notifications (0=No, 1=Yes)',
  `payment_reminders` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable payment reminder notifications (0=No, 1=Yes)',
  `swap_terms_allowed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Allow swapping payout terms with other members (0=No, 1=Yes)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `equb_settings_id`, `member_id`, `username`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `status`, `monthly_payment`, `payout_position`, `payout_month`, `total_contributed`, `has_received_payout`, `guarantor_first_name`, `guarantor_last_name`, `guarantor_phone`, `guarantor_email`, `guarantor_relationship`, `is_active`, `is_approved`, `email_verified`, `join_date`, `last_login`, `notification_preferences`, `go_public`, `language_preference`, `rules_agreed`, `notes`, `created_at`, `updated_at`, `email_notifications`, `payment_reminders`, `swap_terms_allowed`) VALUES
(1, 2, 'HEM-AD1', 'abelgoytom77', 'Abel', 'Demssie', 'Abel Demssie', 'abelgoytom77@gmail.com', '+447360436171', 'active', 1000.00, 1, '2025-07-05', 1000.00, 1, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-07-31', NULL, 'both', 0, 1, 1, '', '2025-07-31 14:13:25', '2025-08-02 09:12:09', 1, 1, 0),
(2, 2, 'HEM-MW1', 'abeldemessie77', 'Michael', 'werkeneh', 'Michael werkeneh', 'abeldemessie77@gmail.com', '+447415329333', 'active', 1000.00, 6, '2025-12-05', 1000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-07-31', NULL, 'both', 1, 0, 1, '', '2025-07-31 16:03:51', '2025-08-02 09:14:56', 1, 1, 0),
(5, NULL, 'HEM-AG578', 'abelgtm77', 'Abel', 'Goytom', 'Abel Goytom', 'abelgtm77@gmail.com', '+447360436170', 'active', 0.00, 0, NULL, 0.00, 0, 'Pending', 'Pending', 'Pending', NULL, NULL, 1, 1, 1, '2025-08-02', NULL, 'both', 1, 0, 1, NULL, '2025-08-02 06:25:58', '2025-08-02 08:53:48', 1, 1, 0);

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `notification_id`, `recipient_type`, `recipient_id`, `recipient_email`, `recipient_phone`, `type`, `channel`, `subject`, `message`, `language`, `status`, `sent_at`, `delivered_at`, `opened_at`, `clicked_at`, `sent_by_admin_id`, `email_provider_response`, `sms_provider_response`, `retry_count`, `scheduled_for`, `priority`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'NOT-202508-590', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub - Email Configuration Test', 'Test email via PHP mail()', 'en', 'sent', '2025-08-01 13:22:18', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email configuration test - PHP mail() method', '2025-08-01 13:22:18', '2025-08-01 13:22:18'),
(2, 'NOT-202508-496', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub - Email Configuration Test', 'Test email via PHP mail()', 'en', 'sent', '2025-08-01 13:22:48', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email configuration test - PHP mail() method', '2025-08-01 13:22:48', '2025-08-01 13:22:48'),
(3, 'NOT-202508-994', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub - Email Configuration Test', 'Test email via PHP mail()', 'en', 'sent', '2025-08-01 13:23:05', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email configuration test - PHP mail() method', '2025-08-01 13:23:05', '2025-08-01 13:23:05'),
(4, 'NOT-202508-538', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub Email Test', '🔍 Starting email delivery test...\n📧 Test email: abelgoytom77@gmail.com\n📤 From: Habesha-Equb   <admin@habeshaequb.com>\n🌐 SMTP:  smtp-relay.brevo.com:587\n\n🔗 Connecting to SMTP server...\n❌ Connection failed: php_network_getaddresses: getaddrinfo for  smtp-relay.brevo.com failed: Name or service not known (0)', 'en', 'failed', '2025-08-02 03:45:40', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email test failed: 🔍 Starting email delivery test...\n📧 Test email: abelgoytom77@gmail.com\n📤 From: Habesha-Equb   <admin@habeshaequb.com>\n🌐 SMTP:  smtp-relay.brevo.com:587\n\n🔗 Connecting to SMTP server...\n❌ Connection failed: php_network_getaddresses: getaddrinfo for  smtp-relay.brevo.com failed: Name or service not known (0)', '2025-08-02 03:45:40', '2025-08-02 03:45:40'),
(5, 'NOT-202508-761', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub Email Test', '🔍 Starting email delivery test...\n📧 Test email: abelgoytom77@gmail.com\n📤 From: Habesha-Equb   <admin@habeshaequb.com>\n🌐 SMTP:  smtp-relay.brevo.com:587\n\n🔍 Checking DNS resolution...\n❌ DNS resolution failed for  smtp-relay.brevo.com\n🔧 Trying alternative connection method...\n❌ CURL connection failed: URL rejected: Malformed input to a URL function\n🔧 Trying alternative Brevo servers...\n✅ Alternative found: smtp-relay.sendinblue.com → 1.179.115.0\n\n🔗 Attempting socket connection...\n✅ Socket connection successful\n📨 Server welcome: 220 smtp-relay.brevo.com ESMTP Service Ready\n🤝 EHLO response: 250-Hello habeshaequb.com\n   Extension: 250-PIPELINING\n   Extension: 250-8BITMIME\n   Extension: 250-ENHANCEDSTATUSCODES\n   Extension: 250-CHUNKING\n   Extension: 250-STARTTLS\n   Extension: 250-AUTH PLAIN LOGIN CRAM-MD5\n   Extension: 250 SIZE 20971520\n🔐 Starting TLS encryption...\n🔒 STARTTLS response: 220 2.0.0 Ready to start TLS\n✅ TLS encryption enabled\n🔑 Authenticating...\n✅ Authentication successful\n📤 MAIL FROM: 250 2.0.0 Roger, accepting mail from <admin@habeshaequb.com>\n📨 RCPT TO: 250 2.0.0 I\'ll make sure <abelgoytom77@gmail.com> gets this\n📝 DATA: 354 Go ahead. End your data with <CR><LF>.<CR><LF>\n📮 Email sent: 250 2.0.0 OK: queued as <202508020358.47907466195@smtp-relay.sendinblue.com>\n\n🎉 EMAIL DELIVERED SUCCESSFULLY!\n⏱️ Total delivery time: 265.57ms\n📬 Check your inbox for the test email.', 'en', 'sent', '2025-08-02 03:58:54', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email test successful', '2025-08-02 03:58:54', '2025-08-02 03:58:54'),
(6, 'NOT-202508-036', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub Email Test', '🔍 Starting email delivery test...\n📧 Test email: abelgoytom77@gmail.com\n📤 From: Habesha-Equb   <admin@habeshaequb.com>\n🌐 SMTP:  smtp-relay.brevo.com:587\n\n🔍 Checking DNS resolution...\n❌ DNS resolution failed for  smtp-relay.brevo.com\n🔧 Trying alternative connection method...\n❌ CURL connection failed: URL rejected: Malformed input to a URL function\n🔧 Trying alternative Brevo servers...\n✅ Alternative found: smtp-relay.sendinblue.com → 1.179.115.0\n\n🔗 Attempting socket connection...\n✅ Socket connection successful\n📨 Server welcome: 220 smtp-relay.brevo.com ESMTP Service Ready\n🤝 EHLO response: 250-Hello habeshaequb.com\n   Extension: 250-PIPELINING\n   Extension: 250-8BITMIME\n   Extension: 250-ENHANCEDSTATUSCODES\n   Extension: 250-CHUNKING\n   Extension: 250-STARTTLS\n   Extension: 250-AUTH PLAIN LOGIN CRAM-MD5\n   Extension: 250 SIZE 20971520\n🔐 Starting TLS encryption...\n🔒 STARTTLS response: 220 2.0.0 Ready to start TLS\n✅ TLS encryption enabled\n🔑 Authenticating...\n✅ Authentication successful\n📤 MAIL FROM: 250 2.0.0 Roger, accepting mail from <admin@habeshaequb.com>\n📨 RCPT TO: 250 2.0.0 I\'ll make sure <abelgoytom77@gmail.com> gets this\n📝 DATA: 354 Go ahead. End your data with <CR><LF>.<CR><LF>\n📮 Email sent: 250 2.0.0 OK: queued as <202508020409.57302070127@smtp-relay.sendinblue.com>\n\n🎉 EMAIL DELIVERED SUCCESSFULLY!\n⏱️ Total delivery time: 297.89ms\n📬 Check your inbox for the test email.', 'en', 'sent', '2025-08-02 04:09:19', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email test successful', '2025-08-02 04:09:19', '2025-08-02 04:09:19'),
(7, 'NOT-202508-353', 'admin', NULL, 'abelgoytom77@gmail.com', NULL, 'general', 'email', 'HabeshaEqub Email Test', '🔍 Starting email delivery test...\n📧 Test email: abelgoytom77@gmail.com\n📤 From: Habesha Equb <admin@habeshaequb.com>\n🌐 SMTP: smtp-relay.brevo.com:587\n\n🔍 Checking DNS resolution...\n✅ DNS resolved: smtp-relay.brevo.com → 1.179.115.1\n\n🔗 Attempting socket connection...\n✅ Socket connection successful\n📨 Server welcome: 220 smtp-relay.brevo.com ESMTP Service Ready\n🤝 EHLO response: 250-Hello habeshaequb.com\n   Extension: 250-PIPELINING\n   Extension: 250-8BITMIME\n   Extension: 250-ENHANCEDSTATUSCODES\n   Extension: 250-CHUNKING\n   Extension: 250-STARTTLS\n   Extension: 250-AUTH PLAIN LOGIN CRAM-MD5\n   Extension: 250 SIZE 20971520\n🔐 Starting TLS encryption...\n🔒 STARTTLS response: 220 2.0.0 Ready to start TLS\n✅ TLS encryption enabled\n🔑 Authenticating...\n✅ Authentication successful\n📤 MAIL FROM: 250 2.0.0 Roger, accepting mail from <admin@habeshaequb.com>\n📨 RCPT TO: 250 2.0.0 I\'ll make sure <abelgoytom77@gmail.com> gets this\n📝 DATA: 354 Go ahead. End your data with <CR><LF>.<CR><LF>\n📮 Email sent: 250 2.0.0 OK: queued as <202508020410.85138954410@smtp-relay.sendinblue.com>\n\n🎉 EMAIL DELIVERED SUCCESSFULLY!\n⏱️ Total delivery time: 257.48ms\n📬 Check your inbox for the test email.', 'en', 'sent', '2025-08-02 04:10:05', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', 'Email test successful', '2025-08-02 04:10:05', '2025-08-02 04:10:05'),
(8, 'NOT-202508-066', 'member', 5, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 07:53:35', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 07:53:35', '2025-08-02 07:53:35'),
(9, 'NOT-202508-298', 'member', 2, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 07:56:36', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 07:56:36', '2025-08-02 07:56:36'),
(10, 'NOT-202508-605', 'member', 5, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'failed', '2025-08-02 08:09:35', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email failed: Rate limit exceeded for this email type', '2025-08-02 08:09:35', '2025-08-02 08:09:35'),
(11, 'NOT-202508-716', 'member', 2, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'failed', '2025-08-02 08:12:12', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email failed: Rate limit exceeded for this email type', '2025-08-02 08:12:12', '2025-08-02 08:12:12'),
(12, 'NOT-202508-721', 'member', 1, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:27:16', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:27:16', '2025-08-02 08:27:16'),
(13, 'NOT-202508-862', 'member', 5, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:27:40', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:27:40', '2025-08-02 08:27:40'),
(14, 'NOT-202508-669', 'member', 1, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:38:24', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:38:24', '2025-08-02 08:38:24'),
(15, 'NOT-202508-199', 'member', 1, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:40:02', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:40:02', '2025-08-02 08:40:02'),
(16, 'NOT-202508-413', 'member', 5, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:42:01', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:42:01', '2025-08-02 08:42:01'),
(17, 'NOT-202508-880', 'member', 5, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 08:53:48', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 08:53:48', '2025-08-02 08:53:48'),
(18, 'NOT-202508-951', 'member', 1, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 09:12:10', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 09:12:10', '2025-08-02 09:12:10'),
(19, 'NOT-202508-871', 'member', 2, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 09:14:56', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 09:14:56', '2025-08-02 09:14:56');

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
(1, 'HEP-20250731-437', 1, 1000.00, '0000-00-00', '2025-06-01', 'paid', 'cash', 1, 8, '2025-07-31 16:25:20', 'HER-20250731-182450', '', 0.00, '2025-07-31 17:25:20', '2025-07-31 17:25:20'),
(2, 'HEP-20250731-908', 2, 1000.00, '0000-00-00', '2025-06-01', 'paid', 'cash', 1, 8, '2025-07-31 16:26:59', 'HER-20250731-182534', '', 0.00, '2025-07-31 17:25:55', '2025-07-31 17:26:59');

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
(2, 'PAYOUT-AD-062025', 1, 2000.00, '2025-06-01', '2025-07-31', 'completed', 'cash', 8, 20.00, 1980.00, NULL, 0, 0, '', '2025-07-31 18:52:35', '2025-07-31 18:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_category` varchar(50) DEFAULT 'general',
  `setting_type` varchar(20) DEFAULT 'text',
  `setting_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_category`, `setting_type`, `setting_description`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'HabeshaEqub', 'general', 'text', 'The name of your application shown throughout the system', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(2, 'app_description', 'Ethiopian traditional savings group management system', 'general', 'text', 'Brief description of your equb application', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(3, 'maintenance_mode', '0', 'general', 'boolean', 'Enable to put the system in maintenance mode', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(4, 'session_timeout', '60', 'general', 'select', 'User session timeout in minutes', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(5, 'default_contribution', '1000', 'defaults', 'number', 'Default monthly contribution amount for new members', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(6, 'default_currency', 'GBP', 'defaults', 'select', 'Default currency for the system', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(7, 'default_language', 'en', 'defaults', 'select', 'Default language for new users', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(8, 'auto_activate_members', '0', 'defaults', 'boolean', 'Automatically activate new member registrations', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(9, 'date_format', 'm/d/Y', 'preferences', 'select', 'How dates are displayed throughout the system', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(10, 'timezone', 'UTC', 'preferences', 'select', 'System timezone for all date/time operations', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(11, 'items_per_page', '25', 'preferences', 'select', 'Number of items to show per page in lists', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(12, 'enable_notifications', '0', 'preferences', 'boolean', 'Enable system notifications for users', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(13, 'smtp_host', 'smtp-relay.brevo.com', 'email', 'text', 'SMTP server hostname', '2025-07-29 20:54:46', '2025-08-02 04:09:58'),
(14, 'smtp_port', '587', 'email', 'number', 'SMTP server port (587 for TLS, 465 for SSL)', '2025-07-29 20:54:46', '2025-08-02 04:09:58'),
(15, 'from_email', 'admin@habeshaequb.com', 'email', 'text', 'Email address used as sender for system emails', '2025-07-29 20:54:46', '2025-08-02 04:09:58'),
(16, 'from_name', 'Habesha Equb', 'email', 'text', 'Name displayed as sender for system emails', '2025-07-29 20:54:46', '2025-08-02 04:09:58'),
(17, 'currency_symbol', '£', 'currency', 'text', 'Symbol to display for currency amounts', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(18, 'currency_position', 'before', 'currency', 'select', 'Position of currency symbol relative to amount', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(19, 'decimal_places', '2', 'currency', 'select', 'Number of decimal places to show for currency', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(20, 'thousands_separator', ',', 'currency', 'select', 'Character used to separate thousands', '2025-07-29 20:54:46', '2025-08-02 02:25:09'),
(21, 'smtp_username', '92bed1001@smtp-brevo.com', 'email', 'text', 'SMTP authentication username', '2025-08-01 12:59:15', '2025-08-02 04:09:58'),
(22, 'smtp_password', '8VgfHCdmsZX0whkx', 'email', 'password', 'SMTP authentication password', '2025-08-01 12:59:15', '2025-08-02 04:09:58'),
(23, 'smtp_encryption', 'tls', 'email', 'select', 'SMTP encryption method (tls, ssl, none)', '2025-08-01 12:59:15', '2025-08-02 04:09:58'),
(24, 'smtp_auth', '1', 'email', 'boolean', 'Enable SMTP authentication', '2025-08-01 12:59:15', '2025-08-02 04:09:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `otp_type` enum('email_verification','login','otp_login') NOT NULL DEFAULT 'email_verification',
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_otps`
--

INSERT INTO `user_otps` (`id`, `user_id`, `email`, `otp_code`, `otp_type`, `expires_at`, `is_used`, `attempt_count`, `created_at`) VALUES
(17, 1, 'abelgoytom77@gmail.com', '2060', 'otp_login', '2025-08-02 09:03:25', 0, 1, '2025-08-02 09:53:25');

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
-- Indexes for table `device_tracking`
--
ALTER TABLE `device_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_device_unique` (`email`,`device_fingerprint`),
  ADD UNIQUE KEY `device_token_unique` (`device_token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_fingerprint` (`device_fingerprint`),
  ADD KEY `idx_approval` (`is_approved`),
  ADD KEY `expires_at_index` (`expires_at`);

--
-- Indexes for table `email_preferences`
--
ALTER TABLE `email_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unsubscribe_token` (`unsubscribe_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `email_rate_limits`
--
ALTER TABLE `email_rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_type` (`email_address`,`email_type`),
  ADD KEY `idx_reset_at` (`reset_at`);

--
-- Indexes for table `equb_rules`
--
ALTER TABLE `equb_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rule_number` (`rule_number`),
  ADD KEY `idx_rule_number` (`rule_number`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `equb_settings`
--
ALTER TABLE `equb_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equb_id` (`equb_id`),
  ADD UNIQUE KEY `idx_equb_id` (`equb_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_by` (`created_by_admin_id`),
  ADD KEY `idx_managed_by` (`managed_by_admin_id`),
  ADD KEY `idx_public` (`is_public`),
  ADD KEY `idx_featured` (`is_featured`);

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
  ADD KEY `idx_payout_position` (`payout_position`),
  ADD KEY `idx_equb_settings` (`equb_settings_id`);

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
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_category` (`setting_category`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `device_tracking`
--
ALTER TABLE `device_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_preferences`
--
ALTER TABLE `email_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_rate_limits`
--
ALTER TABLE `email_rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `equb_rules`
--
ALTER TABLE `equb_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equb_settings`
--
ALTER TABLE `equb_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_preferences`
--
ALTER TABLE `email_preferences`
  ADD CONSTRAINT `email_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equb_settings`
--
ALTER TABLE `equb_settings`
  ADD CONSTRAINT `equb_settings_ibfk_1` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `equb_settings_ibfk_2` FOREIGN KEY (`managed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
