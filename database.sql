-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 07, 2025 at 01:33 AM
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
(8, 'abel', 'abelgoytom77@gmail.com', '+447360436171', '$2y$12$SSw//y2CE/4Q85XAxF4HEee4SX5QtzSifXBX4xHbiSC2X54lZP/eW', 1, 0, '2025-07-29 15:13:13', '2025-08-06 23:11:55');

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
(7, 'abelgoytom77@gmail.com', 'dv_c3b9db2b1191d5d7', NULL, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '143.159.176.60', '2025-08-02 11:37:05', '2025-08-02 11:38:24', 1),
(8, 'fisssaba@gmail.com', 'dv_709dbc26aaaccdaf', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/138.0.7204.119 Mobile/15E148 Safari/604.1', '31.94.31.42', '2025-08-02 12:16:00', '2025-08-02 12:16:00', 0),
(9, 'abeldemessie77@gmail.com', 'dv_7ddeda88d0c599cc', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '143.159.176.60', '2025-08-02 13:22:38', '2025-08-02 13:22:38', 0),
(10, 'barnabasdagnachew25@gmail.com', 'dv_ea039d75b1a1fe89', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '31.94.34.4', '2025-08-02 13:24:34', '2025-08-02 13:24:34', 0),
(11, 'koketabebe17@gmail.com', 'dv_74f5298ecd624920', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '109.151.51.153', '2025-08-02 14:23:22', '2025-08-02 14:23:22', 0),
(12, 'biniamtsegay77@gmail.com', 'dv_c3b9db2b1191d5d7', NULL, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '148.252.141.193', '2025-08-03 11:27:46', '2025-08-03 11:27:46', 0),
(13, 'marufnasirrrr@gmail.com', 'dv_31a88f4f3f387ee7', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1', '31.94.34.102', '2025-08-03 12:12:17', '2025-08-03 12:12:17', 0),
(14, 'kagnew_s@yahoo.com', 'dv_c3b9db2b1191d5d7', NULL, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '193.237.166.126', '2025-08-03 14:47:16', '2025-08-03 14:47:16', 0),
(15, 'haderaeldaba@gmail.com', 'dv_d52b74bd7763d5be', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '38.114.120.160', '2025-08-03 20:57:37', '2025-08-03 20:57:37', 0),
(16, 'haderaeldana@gmail.com', 'dv_d52b74bd7763d5be', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '38.114.120.160', '2025-08-03 21:01:16', '2025-08-03 21:01:16', 0),
(17, 'eliasfriew616@gmail.com', 'dv_c3b9db2b1191d5d7', NULL, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '92.29.197.246', '2025-08-03 22:58:18', '2025-08-03 22:58:18', 0),
(18, 'hagosmahleit@gmail.com', 'dv_7ddeda88d0c599cc', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '193.237.166.126', '2025-08-04 01:14:28', '2025-08-04 01:14:28', 0),
(19, 'samyshafi01@gmail.com', 'dv_ea039d75b1a1fe89', NULL, NULL, 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '31.94.62.225', '2025-08-04 16:36:28', '2025-08-04 16:36:28', 0),
(20, 'abelgoytom707@gmail.com', 'dv_c3b9db2b1191d5d7', NULL, NULL, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', '193.237.166.126', '2025-08-05 19:07:41', '2025-08-05 19:07:41', 0);

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
(3, 'abelgoytom77@gmail.com', 'account_approved', 10, '2025-08-05 19:31:08', '2025-08-03 08:27:16'),
(11, 'abelgoytom77@gmail.com', 'otp_login', 51, '2025-08-06 23:31:28', '2025-08-02 10:30:05'),
(16, 'abeldemessie77@gmail.com', 'otp_login', 1, '2025-08-02 09:46:53', '2025-08-02 10:46:53');

-- --------------------------------------------------------

--
-- Table structure for table `equb_financial_summary`
--

CREATE TABLE `equb_financial_summary` (
  `id` int(11) NOT NULL,
  `equb_settings_id` int(11) NOT NULL,
  `calculation_date` date NOT NULL,
  `total_members` int(3) NOT NULL,
  `individual_members` int(3) NOT NULL,
  `joint_groups` int(3) NOT NULL,
  `total_monthly_pool` decimal(15,2) NOT NULL,
  `total_pool_duration` decimal(15,2) NOT NULL,
  `total_collected` decimal(15,2) DEFAULT 0.00,
  `total_distributed` decimal(15,2) DEFAULT 0.00,
  `outstanding_balance` decimal(15,2) DEFAULT 0.00,
  `admin_fees_collected` decimal(12,2) DEFAULT 0.00,
  `late_fees_collected` decimal(12,2) DEFAULT 0.00,
  `financial_status` enum('balanced','surplus','deficit') DEFAULT 'balanced',
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equb_financial_summary`
--

INSERT INTO `equb_financial_summary` (`id`, `equb_settings_id`, `calculation_date`, `total_members`, `individual_members`, `joint_groups`, `total_monthly_pool`, `total_pool_duration`, `total_collected`, `total_distributed`, `outstanding_balance`, `admin_fees_collected`, `late_fees_collected`, `financial_status`, `last_updated`) VALUES
(1, 2, '2025-08-03', 1, 1, 0, 1000.00, 9000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'balanced', '2025-08-03 13:24:09');

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
(2, 2, 'If you are unable to pay on time due to an emergency, you must notify the admin as soon as possible. An extension of up to two additional days may be granted.', 'አባላቶች ከአቅም በላይ በሆነ ጉዳይ በሰዓቱ መክፈል ካልቻሉ ለሰብሳቢው ቀድመው ማሳወቅ አለባቸው፣ ይሄም እቁቡን ለመክፈል ተጨማሪ 2 ቀናትን እንዲያገኙ ያስችላቸዋል', 1, '2025-07-22 22:22:15', '2025-08-02 11:00:05'),
(3, 3, 'If payment is not received within this grace period, a late fee of £20 will be charged automatically.', 'እቁቡን በሰዓቱ ካልከፈሉ ተጨማሪ £20 ቅጣት ይከፍላሉ', 1, '2025-07-22 22:23:35', '2025-08-02 11:00:44'),
(4, 4, 'Each member receives their full payout on the 5th day of the month.', 'አባላቶች ወር በገባ በአምስተኛው ቀን እቁባቸውን የሚወስዱ ይሆናል', 1, '2025-07-22 22:24:32', '2025-07-22 22:24:32'),
(5, 5, 'A £10 service fee will be deducted from each payout.', 'ሁሉም አባል ተራው ደርሶ እቁብ ሲወስድ ከሚወስደው ጠቅላላ የእቁብ መጠን ላይ ለስራ ማስኬጃ የሚውል £20 ይቀነስበታል', 1, '2025-07-22 22:26:27', '2025-08-02 11:02:10'),
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
  `regular_payment_tier` decimal(10,2) NOT NULL DEFAULT 1000.00 COMMENT 'Base payment amount that determines position count',
  `calculated_positions` int(3) NOT NULL DEFAULT 0 COMMENT 'Auto-calculated based on contributions and regular tier',
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `supports_joint_membership` tinyint(1) DEFAULT 1 COMMENT 'Whether this equb allows joint memberships',
  `max_joint_members_per_group` tinyint(2) DEFAULT 3 COMMENT 'Maximum members allowed in a joint group',
  `financial_status` enum('balanced','surplus','deficit','under_review') DEFAULT 'balanced',
  `last_financial_audit` timestamp NULL DEFAULT NULL COMMENT 'Last time financial audit was performed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equb_settings`
--

INSERT INTO `equb_settings` (`id`, `equb_id`, `equb_name`, `equb_description`, `status`, `max_members`, `current_members`, `duration_months`, `start_date`, `end_date`, `payment_tiers`, `regular_payment_tier`, `calculated_positions`, `currency`, `payout_day`, `admin_fee`, `late_fee`, `grace_period_days`, `auto_assign_positions`, `position_assignment_method`, `terms_en`, `terms_am`, `special_rules`, `created_by_admin_id`, `managed_by_admin_id`, `approval_required`, `registration_start_date`, `registration_end_date`, `is_public`, `is_featured`, `total_pool_amount`, `collected_amount`, `distributed_amount`, `notes`, `created_at`, `updated_at`, `supports_joint_membership`, `max_joint_members_per_group`, `financial_status`, `last_financial_audit`) VALUES
(2, 'EQB-2025-001', 'Selam Equb', 'A new Equb!', 'active', 11, 11, 10, '2025-07-01', '2026-05-01', '[{\"amount\":1000,\"tag\":\"Full\",\"description\":\"Full member\"},{\"amount\":500,\"tag\":\"Half\",\"description\":\"Half member\"},{\"amount\":1500,\"tag\":\"Full Plus\",\"description\":\"Full plus members \"}]', 1000.00, 10, '£', 5, 20.00, 20.00, 2, 1, 'custom', NULL, NULL, NULL, 8, NULL, 1, NULL, NULL, 1, 0, 100000.00, 2500.00, 17920.00, '', '2025-07-31 14:18:24', '2025-08-06 23:51:02', 1, 3, 'balanced', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `financial_audit_trail`
--

CREATE TABLE `financial_audit_trail` (
  `id` int(11) NOT NULL,
  `equb_settings_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `action_type` enum('payment_added','payment_verified','payout_calculated','payout_processed','joint_split_processed','financial_adjustment') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text NOT NULL,
  `performed_by_admin_id` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'Reference to payment/payout ID',
  `before_balance` decimal(12,2) DEFAULT NULL,
  `after_balance` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_audit_trail`
--

INSERT INTO `financial_audit_trail` (`id`, `equb_settings_id`, `member_id`, `action_type`, `amount`, `description`, `performed_by_admin_id`, `reference_id`, `before_balance`, `after_balance`, `created_at`) VALUES
(1, 2, NULL, 'financial_adjustment', 10000.00, 'SMART FIX: Payout calculations corrected. Duration remains 9 months. Positions: 9. Pool-based calculations applied.', 8, NULL, NULL, NULL, '2025-08-05 13:18:35');

-- --------------------------------------------------------

--
-- Table structure for table `joint_membership_groups`
--

CREATE TABLE `joint_membership_groups` (
  `id` int(11) NOT NULL,
  `joint_group_id` varchar(20) NOT NULL COMMENT 'Unique identifier: JNT-EQB001-001',
  `equb_settings_id` int(11) NOT NULL,
  `group_name` varchar(100) DEFAULT NULL COMMENT 'Optional name for the joint group',
  `total_monthly_payment` decimal(10,2) NOT NULL COMMENT 'Combined monthly payment for the group',
  `member_count` tinyint(2) NOT NULL DEFAULT 2 COMMENT 'Number of members in the joint group',
  `payout_position` int(3) NOT NULL COMMENT 'Shared payout position',
  `position_coefficient` decimal(4,2) DEFAULT 1.00 COMMENT 'How many positions this joint group represents',
  `payout_split_method` enum('equal','proportional','custom') DEFAULT 'equal',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `joint_membership_groups`
--

INSERT INTO `joint_membership_groups` (`id`, `joint_group_id`, `equb_settings_id`, `group_name`, `total_monthly_payment`, `member_count`, `payout_position`, `position_coefficient`, `payout_split_method`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'JNT-2025-002-902', 2, 'Eldana & Sosina', 1000.00, 2, 8, 1.00, 'equal', 1, '2025-08-04 13:57:00', '2025-08-06 17:02:34'),
(2, 'JNT-2025-002-115', 2, 'Miki & Koki', 2000.00, 2, 9, 2.00, 'proportional', 1, '2025-08-04 17:20:46', '2025-08-06 16:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `joint_payout_splits`
--

CREATE TABLE `joint_payout_splits` (
  `id` int(11) NOT NULL,
  `joint_group_id` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `payout_id` int(11) NOT NULL COMMENT 'Reference to main payout record',
  `split_amount` decimal(12,2) NOT NULL COMMENT 'Individual share of the payout',
  `split_percentage` decimal(5,4) NOT NULL COMMENT 'Percentage of total payout (0.5000 = 50%)',
  `payment_method` enum('cash','bank_transfer','mobile_money') DEFAULT 'bank_transfer',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `paid_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `position_coefficient` decimal(4,2) DEFAULT 1.00 COMMENT 'How many positions this member represents (0.5, 1.0, 1.5, 2.0, etc.)',
  `payout_month` date DEFAULT NULL COMMENT 'Month when member receives payout',
  `total_contributed` decimal(10,2) DEFAULT 0.00 COMMENT 'Total amount contributed so far',
  `display_payout_amount` decimal(12,2) DEFAULT NULL COMMENT 'Member-friendly payout amount (hides monthly deduction)',
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
  `language_preference` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Web language: 0=English, 1=Amharic',
  `rules_agreed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Agreed to rules, 0=Not agreed',
  `notes` text DEFAULT NULL COMMENT 'Admin notes about member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable email notifications (0=No, 1=Yes)',
  `payment_reminders` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable payment reminder notifications (0=No, 1=Yes)',
  `swap_terms_allowed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Allow swapping payout terms with other members (0=No, 1=Yes)',
  `membership_type` enum('individual','joint') NOT NULL DEFAULT 'individual' COMMENT 'Type of membership - individual or joint',
  `joint_group_id` varchar(20) DEFAULT NULL COMMENT 'Unique identifier for joint membership group',
  `joint_member_count` tinyint(2) DEFAULT 1 COMMENT 'Number of people in joint membership (1 for individual)',
  `individual_contribution` decimal(10,2) DEFAULT NULL COMMENT 'Individual contribution amount for joint members',
  `joint_position_share` decimal(5,4) DEFAULT 1.0000 COMMENT 'Share of the joint position (0.5 for 50/50 split)',
  `primary_joint_member` tinyint(1) DEFAULT 1 COMMENT '1 if primary contact for joint membership',
  `payout_split_method` enum('equal','proportional','custom') DEFAULT 'equal' COMMENT 'How to split payouts in joint membership'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `equb_settings_id`, `member_id`, `username`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `status`, `monthly_payment`, `payout_position`, `position_coefficient`, `payout_month`, `total_contributed`, `display_payout_amount`, `has_received_payout`, `guarantor_first_name`, `guarantor_last_name`, `guarantor_phone`, `guarantor_email`, `guarantor_relationship`, `is_active`, `is_approved`, `email_verified`, `join_date`, `last_login`, `notification_preferences`, `go_public`, `language_preference`, `rules_agreed`, `notes`, `created_at`, `updated_at`, `email_notifications`, `payment_reminders`, `swap_terms_allowed`, `membership_type`, `joint_group_id`, `joint_member_count`, `individual_contribution`, `joint_position_share`, `primary_joint_member`, `payout_split_method`) VALUES
(7, 2, 'HEM-AD537', 'abelgoytom77', 'Abel', 'Demssie', 'Abel Demssie', 'abelgoytom77@gmail.com', '+447360436171', 'active', 1000.00, 1, 1.00, '2025-07-05', 2000.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-02', '2025-08-06 23:31:43', 'both', 1, 0, 1, '', '2025-08-02 11:37:05', '2025-08-07 00:06:18', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(8, 2, 'HEM-SF308', 'fisssaba', 'Sabella', 'Fisseha', 'Sabella Fisseha', 'fisssaba@gmail.com', '+447903095312', 'active', 1000.00, 7, 1.00, '2026-02-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-02', NULL, 'both', 1, 1, 0, '', '2025-08-02 12:16:00', '2025-08-06 17:02:33', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(10, 2, 'HEM-BD183', 'barnabasdagnachew25', 'Barnabas', 'Dagnachew', 'Barnabas Dagnachew', 'barnabasdagnachew25@gmail.com', '07904762565', 'active', 1000.00, 4, 1.00, '2025-07-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-02', NULL, 'both', 1, 1, 0, '', '2025-08-02 13:24:34', '2025-08-06 17:02:33', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(11, 2, 'HEM-KG456', 'koketabebe17', 'Koki', 'Garoma', 'Koki Garoma', 'koketabebe17@gmail.com', '07903146994', 'active', 500.00, 10, 0.50, '2026-01-05', 0.00, 5000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-02', NULL, 'both', 1, 1, 0, '', '2025-08-02 14:23:22', '2025-08-06 17:02:33', 1, 1, 0, 'joint', 'JNT-2025-002-115', 1, 500.00, 0.5000, 1, 'equal'),
(12, 2, 'HEM-BT451', 'biniamtsegay77', 'Biniam', 'Tsegaye', 'Biniam Tsegaye', 'biniamtsegay77@gmail.com', '+447514415491', 'active', 1000.00, 6, 1.00, '2026-03-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 11:27:46', '2025-08-06 17:02:33', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(13, 2, 'HEM-MN293', 'marufnasirrrr', 'Maruf', 'Nasir', 'Maruf Nasir', 'marufnasirrrr@gmail.com', '07438324115', 'active', 1000.00, 2, 1.00, '2025-10-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 12:12:17', '2025-08-06 17:02:33', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(14, 2, 'HEM-MW669', 'kagnew_s', 'Michael', 'Werkeneh', 'Michael Werkeneh', 'kagnew_s@yahoo.com', '+447415329333', 'active', 1500.00, 9, 1.50, '2025-08-05', 0.00, 15000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 14:47:16', '2025-08-06 17:02:33', 1, 1, 0, 'joint', 'JNT-2025-002-115', 1, 1500.00, 0.5000, 1, 'equal'),
(16, 2, 'HEM-EH112', 'haderaeldana', 'Eldana', 'Hadera', 'Eldana Hadera', 'haderaeldana@gmail.com', '+447507910126', 'active', 500.00, 8, 0.50, '2026-04-05', 500.00, 5000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 21:01:16', '2025-08-06 23:12:17', 1, 1, 0, 'joint', 'JNT-2025-002-902', 1, 500.00, 0.5000, 1, 'equal'),
(17, 2, 'HEM-EF442', 'eliasfriew616', 'ELIAS', 'FRIEW', 'ELIAS FRIEW', 'eliasfriew616@gmail.com', '+447480973939', 'active', 1000.00, 5, 1.00, '2025-11-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 22:58:18', '2025-08-06 17:01:06', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal'),
(18, 2, 'HEM-SW198', 'hagosmahleit', 'Sosina', 'Wendmagegn', 'Sosina Wendmagegn', 'hagosmahleit@gmail.com', '07438253791', 'active', 500.00, 8, 0.50, '2026-04-05', 0.00, 5000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-04', NULL, 'both', 1, 1, 0, '', '2025-08-04 01:14:28', '2025-08-06 17:02:33', 1, 1, 0, 'joint', 'JNT-2025-002-902', 1, 500.00, 0.5000, 1, 'equal'),
(20, 2, 'HEM-SS384', 'samyshafi01', 'Samson', 'Shafi', 'Samson Shafi', 'samyshafi01@gmail.com', '07543445583', 'active', 1000.00, 3, 1.00, '2025-12-05', 0.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 0, 1, '2025-08-04', NULL, 'both', 1, 1, 0, '', '2025-08-04 16:36:28', '2025-08-06 17:02:33', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal');

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
(19, 'NOT-202508-871', 'member', 2, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 09:14:56', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 09:14:56', '2025-08-02 09:14:56'),
(20, 'NOT-202508-860', 'member', 6, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 10:45:38', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 10:45:38', '2025-08-02 10:45:38'),
(21, 'NOT-202508-670', 'member', 7, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-02 11:38:24', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-02 11:38:24', '2025-08-02 11:38:24'),
(22, 'NOT-202508-535', 'member', 7, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-05 19:09:26', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-05 19:09:26', '2025-08-05 19:09:26'),
(23, 'NOT-202508-031', 'member', 7, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-05 19:18:29', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-05 19:18:29', '2025-08-05 19:18:29'),
(24, 'NOT-202508-299', 'member', 7, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-05 19:27:12', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-05 19:27:12', '2025-08-05 19:27:12'),
(25, 'NOT-202508-336', 'member', 7, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-05 19:31:08', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-05 19:31:08', '2025-08-05 19:31:08');

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
(7, 'HEP-20250806-399', 7, 1000.00, '2025-07-01', '2025-07-01', 'paid', 'cash', 1, 8, '2025-08-06 22:18:54', 'HER-20250806-231803', '', 0.00, '2025-08-06 22:18:45', '2025-08-06 22:18:54'),
(8, 'HEP-20250806-296', 16, 500.00, '2025-08-01', '2025-08-02', 'paid', 'cash', 1, 8, '2025-08-06 23:12:17', 'HER-20250806-231930', '', 0.00, '2025-08-06 22:19:47', '2025-08-06 23:12:17'),
(9, 'HEP-20250807-401', 7, 1000.00, '2025-08-01', '2025-08-06', 'paid', 'cash', 1, 8, '2025-08-06 23:34:45', 'HER-20250807-003248', '', 20.00, '2025-08-06 23:33:06', '2025-08-06 23:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `payout_id` varchar(20) NOT NULL COMMENT 'Auto-generated: PO-HEM-AG1-202401',
  `member_id` int(11) NOT NULL,
  `gross_payout` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Full payout amount from coefficient calculation (Position Coefficient × Monthly Pool)',
  `total_amount` decimal(12,2) NOT NULL COMMENT 'Gross payout minus admin fee (what member sees as their entitlement)',
  `scheduled_date` date NOT NULL COMMENT 'When payout was scheduled',
  `actual_payout_date` date DEFAULT NULL COMMENT 'When actually paid out',
  `status` enum('scheduled','processing','completed','cancelled','on_hold') NOT NULL DEFAULT 'scheduled',
  `payout_method` enum('cash','bank_transfer','mobile_money','mixed') DEFAULT 'cash',
  `processed_by_admin_id` int(11) DEFAULT NULL,
  `admin_fee` decimal(8,2) DEFAULT 0.00 COMMENT 'Admin service fee (deducted from gross payout)',
  `net_amount` decimal(12,2) NOT NULL COMMENT 'Final amount member receives (gross - admin fee - monthly contribution)',
  `transaction_reference` varchar(100) DEFAULT NULL COMMENT 'Bank/payment reference',
  `receipt_issued` tinyint(1) DEFAULT 0,
  `member_signature` tinyint(1) DEFAULT 0 COMMENT 'Member confirmed receipt',
  `payout_notes` text DEFAULT NULL COMMENT 'DETAILED NOTES: Cash+transfer combinations, issues, special circumstances, delays, member requests, etc.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(68, 7, 'abelgoytom77@gmail.com', '4609', 'otp_login', '2025-08-06 23:41:27', 1, 0, '2025-08-06 23:31:27');

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
-- Indexes for table `equb_financial_summary`
--
ALTER TABLE `equb_financial_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_equb_date` (`equb_settings_id`,`calculation_date`),
  ADD KEY `idx_calculation_date` (`calculation_date`);

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
-- Indexes for table `financial_audit_trail`
--
ALTER TABLE `financial_audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_equb` (`equb_settings_id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_admin` (`performed_by_admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `joint_membership_groups`
--
ALTER TABLE `joint_membership_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `joint_group_id` (`joint_group_id`),
  ADD KEY `idx_equb_settings` (`equb_settings_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `joint_payout_splits`
--
ALTER TABLE `joint_payout_splits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_joint_group` (`joint_group_id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_payout` (`payout_id`);

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
  ADD KEY `idx_equb_settings` (`equb_settings_id`),
  ADD KEY `idx_membership_type` (`membership_type`),
  ADD KEY `idx_joint_group` (`joint_group_id`),
  ADD KEY `idx_primary_joint` (`primary_joint_member`);

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
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification_member` (`notification_id`,`member_id`),
  ADD KEY `idx_notification` (`notification_id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_is_read` (`is_read`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `email_preferences`
--
ALTER TABLE `email_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_rate_limits`
--
ALTER TABLE `email_rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `equb_financial_summary`
--
ALTER TABLE `equb_financial_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `financial_audit_trail`
--
ALTER TABLE `financial_audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `joint_membership_groups`
--
ALTER TABLE `joint_membership_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `joint_payout_splits`
--
ALTER TABLE `joint_payout_splits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_preferences`
--
ALTER TABLE `email_preferences`
  ADD CONSTRAINT `email_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equb_financial_summary`
--
ALTER TABLE `equb_financial_summary`
  ADD CONSTRAINT `financial_summary_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equb_settings`
--
ALTER TABLE `equb_settings`
  ADD CONSTRAINT `equb_settings_ibfk_1` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `equb_settings_ibfk_2` FOREIGN KEY (`managed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_audit_trail`
--
ALTER TABLE `financial_audit_trail`
  ADD CONSTRAINT `audit_admin_fk` FOREIGN KEY (`performed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_member_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `joint_membership_groups`
--
ALTER TABLE `joint_membership_groups`
  ADD CONSTRAINT `joint_groups_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `joint_payout_splits`
--
ALTER TABLE `joint_payout_splits`
  ADD CONSTRAINT `joint_splits_member_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `joint_splits_payout_fk` FOREIGN KEY (`payout_id`) REFERENCES `payouts` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_reads_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

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
