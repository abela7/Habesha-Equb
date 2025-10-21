-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2025 at 12:38 AM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.11

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `can_manage_swaps` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Permission to manage position swaps'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `phone`, `password`, `is_active`, `language_preference`, `created_at`, `updated_at`, `can_manage_swaps`) VALUES
(8, 'abel', 'abelgoytom77@gmail.com', '+447360436171', '$2y$12$SSw//y2CE/4Q85XAxF4HEee4SX5QtzSifXBX4xHbiSC2X54lZP/eW', 1, 0, '2025-07-29 15:13:13', '2025-10-21 21:18:45', 1);

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
(21, 'abelgoytom77@gmail.com', 'dv_ebf1ad94f36d69f1', '6eb5af17e5f8cfc9d49cfb413dca7f081a5a996a3fda5c5e76d593c212502d6b', '2025-08-23 03:12:34', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36', '143.159.176.60', '2025-08-11 12:07:43', '2025-08-16 03:12:34', 1);

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
(2, 'abeldemessie77@gmail.com', 'account_approved', 4, '2025-08-10 09:31:49', '2025-08-03 07:56:36'),
(3, 'abelgoytom77@gmail.com', 'account_approved', 18, '2025-08-11 14:33:08', '2025-08-03 08:27:16'),
(11, 'abelgoytom77@gmail.com', 'otp_login', 162, '2025-10-21 21:43:19', '2025-08-02 10:30:05'),
(16, 'abeldemessie77@gmail.com', 'otp_login', 4, '2025-08-10 09:33:27', '2025-08-02 10:46:53'),
(88, 'abelgoytom77@gmail.com', 'program_notification', 22, '2025-10-21 19:47:27', '2025-08-08 03:38:56'),
(118, 'barnabasdagnachew25@gmail.com', 'account_approved', 2, '2025-08-11 15:00:27', '2025-08-09 12:44:49'),
(119, 'barnabasdagnachew25@gmail.com', 'otp_login', 8, '2025-10-20 15:58:07', '2025-08-08 14:24:32'),
(120, 'marufnasirrrr@gmail.com', 'account_approved', 2, '2025-08-11 15:00:36', '2025-08-09 13:41:31'),
(124, 'abeldemessie77@gmail.com', 'program_notification', 1, '2025-08-10 09:54:47', '2025-08-10 10:54:47'),
(126, 'fisssaba@gmail.com', 'account_approved', 2, '2025-08-11 15:00:21', '2025-08-11 10:58:25'),
(127, 'fisssaba@gmail.com', 'otp_login', 3, '2025-08-10 13:44:42', '2025-08-10 11:59:25'),
(134, 'fisssaba@gmail.com', 'program_notification', 4, '2025-10-21 21:27:49', '2025-08-10 12:53:48'),
(135, 'barnabasdagnachew25@gmail.com', 'program_notification', 3, '2025-10-21 20:06:13', '2025-08-10 12:53:49'),
(136, 'marufnasirrrr@gmail.com', 'program_notification', 4, '2025-10-21 20:06:05', '2025-08-10 12:53:49'),
(175, 'koketabebe17@gmail.com', 'account_approved', 1, '2025-08-11 15:00:29', '2025-08-12 15:00:29'),
(176, 'biniamtsegay77@gmail.com', 'account_approved', 1, '2025-08-11 15:00:31', '2025-08-12 15:00:31'),
(178, 'kagnew_s@yahoo.com', 'account_approved', 2, '2025-08-19 10:35:06', '2025-08-12 15:00:41'),
(179, 'haderaeldana@gmail.com', 'account_approved', 1, '2025-08-11 15:00:48', '2025-08-12 15:00:48'),
(180, 'eliasfriew616@gmail.com', 'account_approved', 1, '2025-08-11 15:00:59', '2025-08-12 15:00:59'),
(181, 'biniamtsegay77@gmail.com', 'otp_login', 3, '2025-08-28 06:50:16', '2025-08-11 16:01:00'),
(182, 'hagosmahleit@gmail.com', 'account_approved', 1, '2025-08-11 15:01:04', '2025-08-12 15:01:04'),
(183, 'samyshafi01@gmail.com', 'account_approved', 1, '2025-08-11 15:01:08', '2025-08-12 15:01:08'),
(186, 'koketabebe17@gmail.com', 'otp_login', 1, '2025-08-11 15:30:07', '2025-08-11 16:30:07'),
(190, 'samyshafi01@gmail.com', 'otp_login', 2, '2025-08-17 12:24:25', '2025-08-12 22:19:46'),
(194, 'koketabebe17@gmail.com', 'program_notification', 1, '2025-08-16 02:38:44', '2025-08-16 03:38:44'),
(199, 'hagosmahleit@gmail.com', 'otp_login', 2, '2025-10-21 21:31:27', '2025-08-17 13:35:16'),
(201, 'kagnew_s@yahoo.com', 'otp_login', 2, '2025-08-18 23:40:46', '2025-08-19 00:39:50'),
(215, 'eliasfriew616@gmail.com', 'otp_login', 7, '2025-09-12 20:35:56', '2025-09-05 15:47:31'),
(231, 'biniamtsegay77@gmail.com', 'program_notification', 2, '2025-10-21 20:06:09', '2025-10-10 13:22:11'),
(261, 'kagnew_s@yahoo.com', 'program_notification', 1, '2025-10-21 19:39:42', '2025-10-21 20:39:42'),
(265, 'haderaeldana@gmail.com', 'program_notification', 2, '2025-10-21 20:05:58', '2025-10-21 20:58:41'),
(266, 'eliasfriew616@gmail.com', 'program_notification', 1, '2025-10-21 20:00:18', '2025-10-21 21:00:18'),
(267, 'hagosmahleit@gmail.com', 'program_notification', 2, '2025-10-21 20:05:55', '2025-10-21 21:05:38'),
(268, 'samyshafi01@gmail.com', 'program_notification', 2, '2025-10-21 20:11:25', '2025-10-21 21:05:48');

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
(6, 6, 'Once your payout turn is assigned, it cannot be changed.\r\nIf you must request a change, you must notify the admin at least 3 weeks in advance by using the position swap page.', 'አንዴ እቁብ የሚወስዱበት ቀን ከታወቀ በኋላ መቀየር አይቻልም፣ ግዴታ መቀየር አስፈላጊ ሆኖ ከተገኘ ለ እቁብ ሰብሳቢው ቢያንስ ከ 3 ሳምንት በፊት የቦታ መቀያየሪያ ሲስተሙን በመጠቀም ማሳወቅ ይኖርብዎታል።', 1, '2025-07-22 22:28:18', '2025-08-08 04:00:33');

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
(2, 'EQB-2025-001', 'Habesha-Equb', 'A new Equb!', 'active', 11, 11, 10, '2025-07-01', '2026-05-01', '[{\"amount\":1000,\"tag\":\"Full\",\"description\":\"Full member\"},{\"amount\":500,\"tag\":\"Half\",\"description\":\"Half member\"}]', 1000.00, 10, '£', 5, 20.00, 20.00, 2, 0, 'custom', NULL, NULL, NULL, 8, 8, 1, NULL, NULL, 1, 0, 100000.00, 21000.00, 66880.00, '', '2025-07-31 14:18:24', '2025-10-21 00:20:26', 1, 3, 'balanced', NULL);

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
(2, 'JNT-2025-002-115', 2, 'Miki & Koki', 2000.00, 2, 9, 2.00, 'proportional', 0, '2025-08-04 17:20:46', '2025-08-16 03:17:12');

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
  `payout_split_method` enum('equal','proportional','custom') DEFAULT 'equal' COMMENT 'How to split payouts in joint membership',
  `swap_requests_allowed` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Allow member to request position swaps',
  `total_swaps_requested` int(11) DEFAULT 0 COMMENT 'Total swap requests made by member',
  `total_swaps_completed` int(11) DEFAULT 0 COMMENT 'Total successful swaps for member',
  `last_swap_date` timestamp NULL DEFAULT NULL COMMENT 'Date of last completed swap',
  `swap_cooldown_until` timestamp NULL DEFAULT NULL COMMENT 'Member cannot request swaps until this date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `equb_settings_id`, `member_id`, `username`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `status`, `monthly_payment`, `payout_position`, `position_coefficient`, `payout_month`, `total_contributed`, `display_payout_amount`, `has_received_payout`, `guarantor_first_name`, `guarantor_last_name`, `guarantor_phone`, `guarantor_email`, `guarantor_relationship`, `is_active`, `is_approved`, `email_verified`, `join_date`, `last_login`, `notification_preferences`, `go_public`, `language_preference`, `rules_agreed`, `notes`, `created_at`, `updated_at`, `email_notifications`, `payment_reminders`, `swap_terms_allowed`, `membership_type`, `joint_group_id`, `joint_member_count`, `individual_contribution`, `joint_position_share`, `primary_joint_member`, `payout_split_method`, `swap_requests_allowed`, `total_swaps_requested`, `total_swaps_completed`, `last_swap_date`, `swap_cooldown_until`) VALUES
(7, 2, 'HEM-AD537', 'abelgoytom77', 'Abel', 'Demssie', 'Abel Demssie', 'abelgoytom77@gmail.com', '+447360436171', 'active', 1000.00, 1, 1.00, '2025-07-05', 4000.00, 7000.00, 1, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-02', '2025-10-21 21:42:37', 'both', 1, 1, 1, '', '2025-08-02 11:37:05', '2025-10-21 21:42:37', 1, 1, 1, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 7, 0, NULL, NULL),
(8, 2, 'HEM-SF308', 'fisssaba', 'Sabella', 'Fisseha', 'Sabella Fisseha', 'fisssaba@gmail.com', '+447903095312', 'active', 1000.00, 9, 1.00, '2026-03-05', 4000.00, 7000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-02', '2025-08-10 13:44:52', 'both', 1, 1, 0, '', '2025-08-02 12:16:00', '2025-10-21 21:27:48', 1, 1, 1, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 1, 0, NULL, NULL),
(10, 2, 'HEM-BD183', 'barnabasdagnachew25', 'Barnabas', 'Dagnachew', 'Barnabas Dagnachew', 'barnabasdagnachew25@gmail.com', '07904762565', 'active', 1000.00, 5, 1.00, '2025-11-05', 4000.00, 7000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-02', '2025-10-20 15:58:25', 'both', 1, 0, 1, '', '2025-08-02 13:24:34', '2025-10-21 20:06:13', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 1, 0, NULL, NULL),
(12, 2, 'HEM-BT451', 'biniamtsegay77', 'Biniam', 'Tsegaye', 'Biniam Tsegaye', 'biniamtsegay77@gmail.com', '+447514415491', 'active', 1000.00, 7, 1.00, '2026-01-05', 4000.00, 7000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-03', '2025-08-28 06:50:46', 'both', 1, 1, 1, '', '2025-08-03 11:27:46', '2025-10-21 20:06:09', 1, 1, 1, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 0, 0, NULL, NULL),
(13, 2, 'HEM-MN293', 'marufnasirrrr', 'Maruf', 'Nasir', 'Maruf Nasir', 'marufnasirrrr@gmail.com', '07438324115', 'active', 1000.00, 2, 1.00, '2025-08-05', 4000.00, 7000.00, 1, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 12:12:17', '2025-10-21 20:06:04', 1, 1, 1, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 0, 0, NULL, NULL),
(14, 2, 'HEM-MW669', 'kagnew_s', 'Michael', 'Werkeneh', 'Michael Werkeneh', 'kagnew_s@yahoo.com', '+447415329333', 'active', 1000.00, 8, 1.00, '2026-02-05', 1000.00, 10000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 14:47:16', '2025-10-21 19:39:41', 1, 1, 0, 'individual', NULL, 1, 1000.00, 1.0000, 1, 'equal', 1, 0, 0, NULL, NULL),
(16, 2, 'HEM-EH112', 'haderaeldana', 'Eldana', 'Hadera', 'Eldana Hadera', 'haderaeldana@gmail.com', '+447507910126', 'active', 500.00, 10, 0.50, '2026-04-05', 2000.00, 3500.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-03', NULL, 'both', 1, 1, 0, '', '2025-08-03 21:01:16', '2025-10-21 20:05:58', 1, 1, 0, 'joint', 'JNT-2025-002-902', 1, 500.00, 0.5000, 1, 'equal', 1, 0, 0, NULL, NULL),
(17, 2, 'HEM-EF442', 'eliasfriew616', 'ELIAS', 'FRIEW', 'ELIAS FRIEW', 'eliasfriew616@gmail.com', '+447480973939', 'active', 1000.00, 6, 1.00, '2025-12-05', 2500.00, 7000.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-03', '2025-09-12 20:35:55', 'both', 1, 1, 1, '', '2025-08-03 22:58:18', '2025-10-21 20:00:17', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 0, 0, NULL, NULL),
(18, 2, 'HEM-SW198', 'hagosmahleit', 'Sosina', 'Wendmagegn', 'Sosina Wendmagegn', 'hagosmahleit@gmail.com', '07438253791', 'active', 500.00, 10, 0.50, '2026-04-05', 2000.00, 3500.00, 0, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-04', '2025-10-21 21:31:49', 'both', 1, 1, 1, '', '2025-08-04 01:14:28', '2025-10-21 21:31:49', 1, 1, 0, 'joint', 'JNT-2025-002-902', 1, 500.00, 0.5000, 1, 'equal', 1, 0, 0, NULL, NULL),
(20, 2, 'HEM-SS384', 'samyshafi01', 'Samson', 'Shafi', 'Samson Shafi', 'samyshafi01@gmail.com', '07543445583', 'active', 1000.00, 4, 1.00, '2025-10-05', 4000.00, 7000.00, 1, 'Pending', 'Pending', 'Pending', '', '', 1, 1, 1, '2025-08-04', '2025-08-17 12:24:38', 'both', 1, 1, 1, '', '2025-08-04 16:36:28', '2025-10-21 20:11:25', 1, 1, 0, 'individual', NULL, 1, NULL, 1.0000, 1, 'equal', 1, 0, 0, NULL, NULL),
(22, 2, 'HEM-KS1', 'michaelwerkneh', 'Kagnew', 'Shaleka', 'Kagnew Shaleka', 'michael.werkneh@email.com', '+447415329333', 'active', 1000.00, 3, 1.00, '2025-09-05', 0.00, NULL, 0, '', '', '', '', '', 1, 1, 0, '2025-08-19', NULL, 'both', 0, 1, 0, '', '2025-08-19 10:37:33', '2025-08-19 15:55:35', 1, 1, 0, 'individual', NULL, 1, 0.00, 0.0000, 1, 'equal', 1, 0, 0, NULL, NULL);

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
(63, 'NOT-202508-519', 'member', 8, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:21', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:21', '2025-08-11 15:00:21'),
(64, 'NOT-202508-411', 'member', 10, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:27', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:27', '2025-08-11 15:00:27'),
(65, 'NOT-202508-387', 'member', 11, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:29', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:29', '2025-08-11 15:00:29'),
(66, 'NOT-202508-908', 'member', 12, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:31', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:31', '2025-08-11 15:00:31'),
(67, 'NOT-202508-459', 'member', 13, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:36', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:36', '2025-08-11 15:00:36'),
(68, 'NOT-202508-017', 'member', 14, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:41', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:41', '2025-08-11 15:00:41'),
(69, 'NOT-202508-679', 'member', 16, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:48', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:48', '2025-08-11 15:00:48'),
(70, 'NOT-202508-888', 'member', 17, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:00:59', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:00:59', '2025-08-11 15:00:59'),
(71, 'NOT-202508-385', 'member', 18, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:01:04', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:01:04', '2025-08-11 15:01:04'),
(72, 'NOT-202508-169', 'member', 20, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-11 15:01:09', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-11 15:01:09', '2025-08-11 15:01:09'),
(73, 'NTF-20250811-945', 'all_members', NULL, NULL, NULL, 'general', 'email', 'Welcome to HabeshaEqub!', 'Dear our member,\r\n\r\nWelcome to HabeshaEqub. To get started, please watch this short video on YouTube\r\nhttps://youtu.be/HhpoKzyBtk0', 'en', 'sent', '2025-08-11 15:01:27', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:01:27', '2025-08-11 15:01:27'),
(74, 'NTF-20250811-150', 'member', 12, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Biniam የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 1, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=2da8f39cdc1c7a88a999c1f574dc85c4\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:02:08', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:02:08', '2025-08-11 15:02:08'),
(75, 'NTF-20250811-815', 'member', 12, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Biniam የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 1, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=41706a72de1278a76745487e107cc8e1\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:02:38', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:02:38', '2025-08-11 15:02:38'),
(76, 'NTF-20250811-534', 'member', 16, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Eldana የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 5, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=59dd2c196649e683ddd26a9098322e61\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:05:05', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:05:05', '2025-08-11 15:05:05'),
(77, 'NTF-20250811-350', 'member', 16, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Eldana የ August 2025 ወር የእቁብ ክፍያዎን  በJuly 15, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=046beae78e2c2e553c6feaa8addb9bf8\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:05:28', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:05:28', '2025-08-11 15:05:28'),
(78, 'NTF-20250811-283', 'member', 11, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Koki የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=d6298d41fdd6f43353d97b90f44d2816\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:06:02', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:06:02', '2025-08-11 15:06:02'),
(79, 'NTF-20250811-074', 'member', 18, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sosina የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 3, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=8a38ea390cdd7a0ec7a27af967927a05\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:06:43', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:06:43', '2025-08-11 15:06:43'),
(80, 'NTF-20250811-070', 'member', 18, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sosina የ August 2025 ወር የእቁብ ክፍያዎን  በJuly 9, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=0374491460eb136a1be417510a92d2c8\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:07:09', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:07:09', '2025-08-11 15:07:09'),
(81, 'NTF-20250811-003', 'member', 20, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Samson የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 5, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=f76f7d93abad9872324b7d6362bf3eb3\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:07:33', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:07:33', '2025-08-11 15:07:33'),
(82, 'NTF-20250811-980', 'member', 17, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ ELIAS የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 4, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=eabd1dccd2ac3ac888b5f785e6485e83\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:08:09', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:08:09', '2025-08-11 15:08:09'),
(83, 'NTF-20250811-647', 'member', 17, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ ELIAS የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 4, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=b247a2af429fc43eec656ce8921ffde0\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:09:10', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:09:10', '2025-08-11 15:09:10'),
(84, 'NTF-20250811-599', 'member', 10, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Barnabas የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 25, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=949293b3d36cc4939eef16e8716a7d24\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:09:45', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:09:45', '2025-08-11 15:09:45'),
(85, 'NTF-20250811-334', 'member', 10, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Barnabas የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 4, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=7a943e3bf2eb05eaf9dc9fcbf3e0c903\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:10:21', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:10:21', '2025-08-11 15:10:21'),
(86, 'NTF-20250811-910', 'member', 13, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Maruf የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 5, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=5e9b40ba67edb01595945a010dc7c245\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:11:14', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:11:14', '2025-08-11 15:11:14'),
(87, 'NTF-20250811-817', 'member', 8, NULL, NULL, 'general', 'email', 'የ July 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sabella የ July 2025 ወር የእቁብ ክፍያዎን  በJuly 1, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=82124b2cd86dc7a6d810bfbcdcd5f947\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:13:06', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:13:06', '2025-08-11 15:13:06'),
(88, 'NTF-20250811-634', 'member', 8, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sabella የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 5, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=dbafa3609c5a7a936a4c02c0d757ca48\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:13:57', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:13:57', '2025-08-11 15:13:57'),
(89, 'NTF-20250811-994', 'member', 8, NULL, NULL, 'general', 'email', 'የ September 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sabella የ September 2025 ወር የእቁብ ክፍያዎን  በAugust 6, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=f99beccbc31947d9b9913a17bdacaba8\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:14:15', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:14:15', '2025-08-11 15:14:15'),
(90, 'NTF-20250811-354', 'member', 8, NULL, NULL, 'general', 'email', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Sabella የ October 2025 ወር የእቁብ ክፍያዎን  በAugust 6, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=20ca00967f171a2a2fdf1a24e49b6cd2\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:14:36', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:14:36', '2025-08-11 15:14:36'),
(91, 'NTF-20250811-668', 'member', 7, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 1, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=934d2b35f84afb432d09cd7d5ba011e7\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-11 15:19:29', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:19:29', '2025-08-11 15:19:29'),
(92, 'NTF-20250811-674', 'member', 7, NULL, NULL, 'general', '', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nእናመሰግናለን።', 'am', 'sent', '2025-08-11 15:30:15', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 15:30:15', '2025-08-11 15:30:15'),
(93, 'NTF-20250811-006', 'member', 13, NULL, NULL, 'general', '', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'ውድ Maruf የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=8f36c2f80bbf2e8a50564436668c26ea\n\nእናመሰግናለን።', 'am', 'sent', '2025-08-11 20:01:06', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-11 20:01:06', '2025-08-11 20:01:06'),
(94, 'NTF-20250814-721', 'member', 20, NULL, NULL, 'general', 'email', 'የ August 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Samson የ August 2025 ወር የእቁብ ክፍያዎን  በAugust 4, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=369e0cf17ce571a55a702e08220ded25\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-08-14 00:12:55', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-14 00:12:55', '2025-08-14 00:12:55'),
(95, 'NTF-20250816-392', 'member', 11, NULL, NULL, 'general', 'both', 'Thanks for Your Participation.', 'Dear Koki, Thanks for your participation in our equb. As you requested, you are no longer our member. Your 1-month payment will be refunded soon. Hope to see you in our next Equb.\r\nThanks!', 'en', 'sent', '2025-08-16 02:38:44', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-08-16 02:38:44', '2025-08-16 02:38:44'),
(96, 'NOT-202508-632', 'member', 14, NULL, NULL, 'approval', 'email', 'Account Approved', 'Welcome to HabeshaEqub!', 'en', 'sent', '2025-08-19 10:35:06', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 'normal', 'User approved. Email sent', '2025-08-19 10:35:06', '2025-08-19 10:35:06'),
(97, 'NTF-20251002-234', 'member', 20, NULL, NULL, 'general', '', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'ውድ Samson የእቁብ መክፈያዎ በOctober 2, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=5671fd6c4d020c62d3d4f5c539a433e7\n\nእናመሰግናለን።', 'am', 'sent', '2025-10-02 08:33:22', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-02 08:33:22', '2025-10-02 08:33:22'),
(98, 'NTF-20251010-779', 'member', 10, NULL, NULL, 'general', 'both', 'September 2025 Payment Confirmation', 'Dear Barnabas, you have successfully paid this month\'s Equb payment for September 2025 on August 5, 2025.\n\nYou can Access and Download your receipt on: https://habeshaequb.com/receipt.php?rt=008427ef4627b74b2da4b37f4c1e5590\n\nThanks for your payment.', 'en', 'sent', '2025-10-10 12:19:58', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-10 12:19:58', '2025-10-10 12:19:58'),
(99, 'NTF-20251010-757', 'member', 13, NULL, NULL, 'general', 'both', 'የ September 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Maruf የ September 2025 ወር የእቁብ ክፍያዎን  በSeptember 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=f90e336021a017fdff099de7d4c60452\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-10 12:22:05', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-10 12:22:05', '2025-10-10 12:22:05'),
(100, 'NTF-20251010-299', 'member', 12, NULL, NULL, 'general', 'both', 'የ September 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Biniam የ September 2025 ወር የእቁብ ክፍያዎን  በSeptember 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=b4b936c57761f8948624383ba31daf6f\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-10 12:22:11', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-10 12:22:11', '2025-10-10 12:22:11'),
(101, 'NTF-20251010-975', 'member', 7, NULL, NULL, 'general', 'both', 'September 2025 Payment Confirmation', 'Dear Abel, you have successfully paid this month\'s Equb payment for September 2025 on September 2, 2025.\n\nYou can Access and Download your receipt on: https://habeshaequb.com/receipt.php?rt=9c1caf2edb9823dee21b22a923457e63\n\nThanks for your payment.', 'en', 'sent', '2025-10-10 12:23:40', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-10 12:23:40', '2025-10-10 12:23:40'),
(102, 'NTF-20251020-578', 'member', 7, NULL, NULL, 'general', 'both', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel የ October 2025 ወር የእቁብ ክፍያዎን  በOctober 4, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=d66aff81929bbf74a4fa421710311a85\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-20 21:42:36', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-20 21:42:36', '2025-10-20 21:42:36'),
(103, 'NTF-20251020-311', 'member', 20, NULL, NULL, 'general', '', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'ውድ Samson የእቁብ መክፈያዎ በOctober 20, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £9,000.00\n- የአስተዳደር ክፍያ ተተግብሯል: £0.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=bfb4b302082f5e0d9dcaa60b3b6d569a\n\nእናመሰግናለን።', 'am', 'sent', '2025-10-20 21:44:38', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-20 21:44:38', '2025-10-20 21:44:38'),
(104, 'NTF-20251021-736', 'member', 7, NULL, NULL, 'general', 'both', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel የ October 2025 ወር የእቁብ ክፍያዎን  በOctober 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=3da24c43fce0a2001f7c0549134a31d8\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-21 18:13:45', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:13:45', '2025-10-21 18:13:45'),
(105, 'NTF-20251021-422', 'member', 7, NULL, NULL, 'general', 'both', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel,<br /><br />የ October 2025 ወር የእቁብ ክፍያዎን  በOctober 2, 2025 በተሳካ ሁኔታ ከፍለዋል።<br /><br />ደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦<br /><a href=\"https://habeshaequb.com/receipt.php?rt=0489c0a73483f70ccedbe7e07baff9cd\">https://habeshaequb.com/receipt.php?rt=0489c0a73483f70ccedbe7e07baff9cd</a><br /><br />ስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-21 18:20:51', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:20:51', '2025-10-21 18:20:51'),
(106, 'NTF-20251021-057', 'member', 7, NULL, NULL, 'general', 'both', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel,\n\nየ October 2025 ወር የእቁብ ክፍያዎን  በOctober 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦\nhttps://habeshaequb.com/receipt.php?rt=ca4756fb70f61ea142504ce5d91384c7\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-21 18:25:48', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:25:48', '2025-10-21 18:25:48'),
(107, 'NTF-20251021-513', 'member', 7, NULL, NULL, 'general', 'both', 'የ October 2025 ወር ክፍያ ማረጋገጫ', 'ውድ Abel,\n\nየ October 2025 ወር የእቁብ ክፍያዎን  በOctober 2, 2025 በተሳካ ሁኔታ ከፍለዋል።\n\nደረሰኝዎን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦\nhttps://habeshaequb.com/receipt.php?rt=fe0ac187dfd9d961bfe5cb0f310647fd\n\nስለ ክፍያዎ እናመሰግናለን።', 'am', 'sent', '2025-10-21 18:33:08', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:33:08', '2025-10-21 18:33:08'),
(108, 'NTF-20251021-247', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Abel,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=548bf09554de553491bb314ab52ba32e\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 18:40:13', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:40:13', '2025-10-21 18:40:13'),
(109, 'NTF-20251021-755', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Abel,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=dde82d48420107df50c38109068b0cb9\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 18:48:24', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:48:24', '2025-10-21 18:48:24'),
(110, 'NTF-20251021-602', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Abel,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=371c29b123a321f695b7f7cfa44e5003\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 18:50:06', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:50:06', '2025-10-21 18:50:06'),
(111, 'NTF-20251021-712', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Abel,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=9365d3a4a09fb43e728fdbdeb348d677\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 18:56:18', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 18:56:18', '2025-10-21 18:56:18'),
(112, 'NTF-20251021-038', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - July 2025', 'Dear Abel,\n\nYour payment for July 2025 has been successfully confirmed.\n\nPayment Date: July 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=9e656443b1a62afe7649e28073377870\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 19:39:33', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 19:39:33', '2025-10-21 19:39:33'),
(113, 'NTF-20251021-799', 'member', 14, NULL, NULL, 'general', 'both', 'Payment Confirmation - July 2025', 'Dear Michael,\n\nYour payment for July 2025 has been successfully confirmed.\n\nPayment Date: July 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=5a753b97e4da590f09685fb41cb7d9c8\n\nThank you,\nHabeshaEqub Team', 'am', 'sent', '2025-10-21 19:39:41', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 19:39:41', '2025-10-21 19:39:41'),
(114, 'NTF-20251021-028', 'member', 7, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Abel,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=f9536bdb7df7195fbad2a9fa17c2fc18\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 19:47:27', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 19:47:27', '2025-10-21 19:47:27'),
(115, 'NTF-20251021-249', 'member', 13, NULL, NULL, 'general', 'both', 'Payment Confirmation - August 2025', 'Dear Maruf,\n\nYour payment for August 2025 has been successfully confirmed.\n\nPayment Date: August 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=7fe6f477c0bcfeaa8199d10564a9297a\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 19:58:23', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 19:58:23', '2025-10-21 19:58:23'),
(116, 'NTF-20251021-487', 'member', 16, NULL, NULL, 'general', 'both', 'Payment Confirmation - September 2025', 'Dear Eldana,\n\nYour payment for September 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £500.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=9b4f2c76ac558b75a09c0d7fbf3173a3\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 19:58:41', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 19:58:41', '2025-10-21 19:58:41'),
(117, 'NTF-20251021-510', 'member', 17, NULL, NULL, 'general', 'both', 'Payment Confirmation - September 2025', 'Dear ELIAS,\n\nYour payment for September 2025 has been successfully confirmed.\n\nPayment Date: September 2, 2025\nAmount: £500.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=c3358012bfce92a453e84477c043ae44\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:00:17', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:00:17', '2025-10-21 20:00:17'),
(118, 'NTF-20251021-205', 'member', 18, NULL, NULL, 'general', 'both', 'Payment Confirmation - September 2025', 'Dear Sosina,\n\nYour payment for September 2025 has been successfully confirmed.\n\nPayment Date: September 2, 2025\nAmount: £500.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=0f28ade59ca93cf07b7006b54381cba5\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:05:38', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:05:38', '2025-10-21 20:05:38'),
(119, 'NTF-20251021-780', 'member', 20, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Samson,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=88c28362abada1407e4fd3a218eef0c7\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:05:48', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:05:48', '2025-10-21 20:05:48'),
(120, 'NTF-20251021-630', 'member', 18, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Sosina,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £500.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=ed03e93e1d61d1622ea9957b241fad00\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:05:55', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:05:55', '2025-10-21 20:05:55'),
(121, 'NTF-20251021-257', 'member', 16, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Eldana,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £500.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=06a4276ba42e3ba8c1e52f44056726a5\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:05:58', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:05:58', '2025-10-21 20:05:58'),
(122, 'NTF-20251021-366', 'member', 13, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Maruf,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=1fa0c4b6ac60af317e54a1f3c1617ff4\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:06:04', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:06:04', '2025-10-21 20:06:04'),
(123, 'NTF-20251021-793', 'member', 12, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Biniam,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=92cf67002b3d1635453ba8c9f9b62ba1\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:06:09', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:06:09', '2025-10-21 20:06:09'),
(124, 'NTF-20251021-081', 'member', 10, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Barnabas,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: October 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=cfc42bbee29bdfe0461bcd196e1fa5be\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'en', 'sent', '2025-10-21 20:06:13', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:06:13', '2025-10-21 20:06:13'),
(125, 'NTF-20251021-953', 'member', 20, NULL, NULL, 'general', 'both', 'Payment Confirmation - September 2025', 'Dear Samson,\n\nYour payment for September 2025 has been successfully confirmed.\n\nPayment Date: September 2, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=96dafad1f5729d698d510ad03b79ee7d\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 20:11:25', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 20:11:25', '2025-10-21 20:11:25'),
(126, 'NTF-20251021-054', 'member', 8, NULL, NULL, 'general', 'both', 'Payment Confirmation - October 2025', 'Dear Sabella,\n\nYour payment for October 2025 has been successfully confirmed.\n\nPayment Date: August 6, 2025\nAmount: £1,000.00\n\nView your receipt: https://habeshaequb.com/receipt.php?rt=d2ca30705c9b31d3c223847fb4eb78b2\n\nThank you,\nHabeshaEqub Team\n\n---\n\nNeed more information about the equb?\nLogin to your portal to view:\n• Your payment history\n• Payout schedule and positions\n• Member contributions\n• Equb rules and updates\n\nMember Portal: https://habeshaequb.com/user/login.php', 'am', 'sent', '2025-10-21 21:27:48', NULL, NULL, NULL, 8, NULL, NULL, 0, NULL, 'normal', NULL, '2025-10-21 21:27:48', '2025-10-21 21:27:48');

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

--
-- Dumping data for table `notification_reads`
--

INSERT INTO `notification_reads` (`id`, `notification_id`, `member_id`, `is_read`, `read_at`, `created_at`) VALUES
(70, 73, 7, 1, '2025-10-21 21:42:51', '2025-08-11 15:23:00'),
(71, 91, 7, 1, '2025-10-21 21:42:51', '2025-08-11 15:23:05'),
(72, 92, 7, 1, '2025-10-21 21:42:51', '2025-08-11 16:31:04'),
(73, 101, 7, 1, '2025-10-21 21:42:51', '2025-10-20 19:35:38'),
(74, 120, 18, 1, '2025-10-21 21:32:28', '2025-10-21 21:32:28'),
(75, 118, 18, 1, '2025-10-21 21:32:28', '2025-10-21 21:32:28'),
(76, 80, 18, 1, '2025-10-21 21:32:28', '2025-10-21 21:32:28'),
(77, 79, 18, 1, '2025-10-21 21:32:28', '2025-10-21 21:32:28'),
(78, 73, 18, 1, '2025-10-21 21:32:29', '2025-10-21 21:32:29'),
(79, 71, 18, 1, '2025-10-21 21:32:29', '2025-10-21 21:32:29'),
(80, 114, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(81, 112, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(82, 111, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(83, 110, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(84, 109, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(85, 108, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(86, 107, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(87, 106, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(88, 105, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(89, 104, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51'),
(90, 102, 7, 1, '2025-10-21 21:42:51', '2025-10-21 21:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `read_flag` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `member_id`, `read_flag`, `read_at`, `delivered_at`, `created_at`) VALUES
(249, 64, 7, 0, NULL, NULL, '2025-08-11 16:30:15'),
(250, 65, 13, 0, NULL, NULL, '2025-08-11 21:01:07'),
(251, 66, 20, 0, NULL, NULL, '2025-10-02 09:33:12'),
(252, 67, 20, 0, NULL, NULL, '2025-10-02 09:33:22'),
(253, 68, 22, 0, NULL, NULL, '2025-10-02 09:34:25'),
(254, 69, 20, 0, NULL, NULL, '2025-10-20 22:44:29'),
(255, 70, 20, 0, NULL, NULL, '2025-10-20 22:44:38');

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
(14, 'HEP-20250808-609', 12, 1000.00, '2025-07-01', '2025-07-01', 'paid', 'cash', 1, 8, '2025-08-11 15:02:08', 'HER-20250808-063729', '', 0.00, '2025-08-08 05:38:14', '2025-08-11 15:02:08'),
(15, 'HEP-20250808-192', 8, 1000.00, '2025-07-01', '2025-07-01', 'paid', 'bank_transfer', 1, 8, '2025-08-11 15:13:06', 'HER-20250808-110951', '', 0.00, '2025-08-08 10:11:09', '2025-08-11 15:13:06'),
(17, 'HEP-20250808-755', 18, 500.00, '2025-07-01', '2025-07-03', 'paid', 'cash', 1, 8, '2025-08-11 15:06:43', 'HER-20250808-111229', '', 0.00, '2025-08-08 10:12:54', '2025-08-11 15:06:43'),
(18, 'HEP-20250808-646', 10, 1000.00, '2025-07-01', '2025-07-25', 'paid', 'cash', 1, 8, '2025-08-11 15:09:45', 'HER-20250808-111257', '', 0.00, '2025-08-08 10:13:41', '2025-08-11 15:09:45'),
(19, 'HEP-20250808-653', 17, 1000.00, '2025-07-01', '2025-07-04', 'paid', 'cash', 1, 8, '2025-08-11 15:08:09', 'HER-20250808-111352', '', 0.00, '2025-08-08 10:14:18', '2025-08-11 15:08:09'),
(20, 'HEP-20250808-637', 20, 1000.00, '2025-07-01', '2025-07-05', 'paid', 'cash', 1, 8, '2025-08-11 15:07:33', 'HER-20250808-111450', '', 0.00, '2025-08-08 10:15:14', '2025-08-11 15:07:33'),
(21, 'HEP-20250808-379', 16, 500.00, '2025-07-01', '2025-07-05', 'paid', 'cash', 1, 8, '2025-08-11 15:05:04', 'HER-20250808-111540', '', 0.00, '2025-08-08 10:16:02', '2025-08-11 15:05:04'),
(22, 'HEP-20250808-662', 16, 500.00, '2025-08-01', '2025-07-15', 'paid', 'cash', 1, 8, '2025-08-11 15:05:28', 'HER-20250808-111817', '', 0.00, '2025-08-08 10:18:55', '2025-08-11 15:05:28'),
(23, 'HEP-20250808-172', 18, 500.00, '2025-08-01', '2025-07-09', 'paid', 'cash', 1, 8, '2025-08-11 15:07:08', 'HER-20250808-111915', '', 0.00, '2025-08-08 10:19:39', '2025-08-11 15:07:08'),
(24, 'HEP-20250808-277', 7, 1000.00, '2025-08-01', '2025-08-01', 'paid', 'cash', 1, 8, '2025-08-11 15:19:29', 'HER-20250808-111946', '', 0.00, '2025-08-08 10:20:12', '2025-08-11 15:19:29'),
(25, 'HEP-20250808-248', 12, 1000.00, '2025-08-01', '2025-08-01', 'paid', 'cash', 1, 8, '2025-08-11 15:02:38', 'HER-20250808-112013', '', 0.00, '2025-08-08 10:20:30', '2025-08-11 15:02:38'),
(26, 'HEP-20250808-476', 8, 1000.00, '2025-08-01', '2025-08-05', 'paid', 'bank_transfer', 1, 8, '2025-08-11 15:13:57', 'HER-20250808-112031', '', 0.00, '2025-08-08 10:20:59', '2025-08-11 15:13:57'),
(27, 'HEP-20250808-445', 8, 1000.00, '2025-09-01', '2025-08-06', 'paid', 'bank_transfer', 1, 8, '2025-08-11 15:14:15', 'HER-20250808-112814', '', 0.00, '2025-08-08 10:28:38', '2025-08-11 15:14:15'),
(29, 'HEP-20250808-462', 13, 1000.00, '2025-07-01', '2025-07-05', 'paid', 'cash', 1, 8, '2025-08-11 15:11:14', 'HER-20250808-144231', '', 0.00, '2025-08-08 13:42:53', '2025-08-11 15:11:14'),
(30, 'HEP-20250810-624', 8, 1000.00, '2025-10-01', '2025-08-06', 'paid', 'bank_transfer', 1, 8, '2025-10-21 21:27:48', 'HER-20250810-121111', '', 0.00, '2025-08-10 11:11:57', '2025-10-21 21:27:48'),
(31, 'HEP-20250810-079', 10, 1000.00, '2025-08-01', '2025-08-04', 'paid', 'cash', 1, 8, '2025-08-11 15:10:21', 'HER-20250810-215959', '', 20.00, '2025-08-10 21:00:23', '2025-08-11 15:10:21'),
(32, 'HEP-20250811-325', 17, 1000.00, '2025-08-01', '2025-08-04', 'paid', 'cash', 1, 8, '2025-08-11 15:09:10', 'HER-20250811-160831', '', 0.00, '2025-08-11 15:08:57', '2025-08-11 15:09:10'),
(33, 'HEP-20250811-689', 20, 1000.00, '2025-08-01', '2025-08-04', 'paid', 'cash', 1, 8, '2025-08-14 00:12:55', 'HER-20250811-210601', '', 0.00, '2025-08-11 20:07:45', '2025-08-14 00:12:55'),
(34, 'HEP-20251010-355', 10, 1000.00, '2025-09-01', '2025-08-05', 'paid', 'cash', 1, 8, '2025-10-10 12:19:58', 'HER-20251010-131851', '', 0.00, '2025-10-10 12:19:34', '2025-10-10 12:19:58'),
(35, 'HEP-20251010-198', 13, 1000.00, '2025-09-01', '2025-09-02', 'paid', 'cash', 1, 8, '2025-10-10 12:22:04', 'HER-20251010-132103', '', 0.00, '2025-10-10 12:21:35', '2025-10-10 12:22:04'),
(36, 'HEP-20251010-410', 12, 1000.00, '2025-09-01', '2025-09-02', 'paid', 'cash', 1, 8, '2025-10-10 12:22:11', 'HER-20251010-132139', '', 0.00, '2025-10-10 12:21:56', '2025-10-10 12:22:11'),
(37, 'HEP-20251010-662', 7, 1000.00, '2025-09-01', '2025-09-02', 'paid', 'cash', 1, 8, '2025-10-10 12:23:40', 'HER-20251010-132313', '', 0.00, '2025-10-10 12:23:30', '2025-10-10 12:23:40'),
(47, 'HEP-20251021-033', 7, 1000.00, '2025-07-01', '2025-07-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 19:39:33', '', '', 0.00, '2025-10-21 19:38:44', '2025-10-21 19:39:33'),
(48, 'HEP-20251021-810', 14, 1000.00, '2025-07-01', '2025-07-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 19:39:41', '', '', 0.00, '2025-10-21 19:39:20', '2025-10-21 19:39:41'),
(49, 'HEP-20251021-433', 13, 1000.00, '2025-08-01', '2025-08-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 19:58:23', 'HER-20251021-204334', '', 0.00, '2025-10-21 19:43:45', '2025-10-21 19:58:23'),
(50, 'HEP-20251021-381', 16, 500.00, '2025-09-01', '2025-10-02', 'paid', 'cash', 1, 8, '2025-10-21 19:58:41', '', '', 20.00, '2025-10-21 19:46:27', '2025-10-21 19:58:41'),
(51, 'HEP-20251021-391', 7, 1000.00, '2025-10-01', '2025-10-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 19:47:27', 'HER-20251021-204711', '', 0.00, '2025-10-21 19:47:13', '2025-10-21 19:47:27'),
(52, 'HEP-20251021-597', 17, 500.00, '2025-09-01', '2025-09-02', 'paid', 'mobile_money', 1, 8, '2025-10-21 20:00:17', 'HER-20251021-205924', '', 0.00, '2025-10-21 19:59:52', '2025-10-21 20:00:17'),
(53, 'HEP-20251021-993', 10, 1000.00, '2025-10-01', '2025-10-02', 'paid', '', 1, 8, '2025-10-21 20:06:13', '', '', 0.00, '2025-10-21 20:03:13', '2025-10-21 20:06:13'),
(54, 'HEP-20251021-421', 12, 1000.00, '2025-10-01', '2025-10-02', 'paid', '', 1, 8, '2025-10-21 20:06:09', '', '', 0.00, '2025-10-21 20:03:26', '2025-10-21 20:06:09'),
(55, 'HEP-20251021-779', 13, 1000.00, '2025-10-01', '2025-10-02', 'paid', '', 1, 8, '2025-10-21 20:06:04', 'HER-20251021-210333', '', 0.00, '2025-10-21 20:03:41', '2025-10-21 20:06:04'),
(56, 'HEP-20251021-832', 16, 500.00, '2025-10-01', '2025-10-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 20:05:58', 'HER-20251021-210348', '', 0.00, '2025-10-21 20:03:59', '2025-10-21 20:05:58'),
(57, 'HEP-20251021-830', 18, 500.00, '2025-10-01', '2025-10-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 20:05:54', 'HER-20251021-210410', '', 0.00, '2025-10-21 20:04:11', '2025-10-21 20:05:54'),
(58, 'HEP-20251021-049', 20, 1000.00, '2025-10-01', '2025-10-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 20:05:48', 'HER-20251021-210443', '', 0.00, '2025-10-21 20:04:50', '2025-10-21 20:05:48'),
(59, 'HEP-20251021-684', 18, 500.00, '2025-09-01', '2025-09-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 20:05:38', 'HER-20251021-210512', '', 0.00, '2025-10-21 20:05:13', '2025-10-21 20:05:38'),
(60, 'HEP-20251021-298', 20, 1000.00, '2025-09-01', '2025-09-02', 'paid', 'bank_transfer', 1, 8, '2025-10-21 20:11:25', 'HER-20251021-211056', '', 0.00, '2025-10-21 20:10:57', '2025-10-21 20:11:25');

-- --------------------------------------------------------

--
-- Table structure for table `payment_receipts`
--

CREATE TABLE `payment_receipts` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_receipts`
--

INSERT INTO `payment_receipts` (`id`, `payment_id`, `token`, `created_at`) VALUES
(8, 24, 'a2b7bbf643f328c5050e98cb83be7662', '2025-08-11 11:42:47'),
(11, 24, 'a67ecda66b40ba8a7cdf02504c795146', '2025-08-11 14:02:56'),
(12, 31, '1d080b819d3d6d7ff6d24e01a2b533c5', '2025-08-11 14:47:10'),
(13, 14, '2da8f39cdc1c7a88a999c1f574dc85c4', '2025-08-11 15:02:08'),
(14, 25, '41706a72de1278a76745487e107cc8e1', '2025-08-11 15:02:38'),
(15, 21, '59dd2c196649e683ddd26a9098322e61', '2025-08-11 15:05:05'),
(16, 22, '046beae78e2c2e553c6feaa8addb9bf8', '2025-08-11 15:05:28'),
(18, 17, '8a38ea390cdd7a0ec7a27af967927a05', '2025-08-11 15:06:43'),
(19, 23, '0374491460eb136a1be417510a92d2c8', '2025-08-11 15:07:09'),
(20, 20, 'f76f7d93abad9872324b7d6362bf3eb3', '2025-08-11 15:07:33'),
(21, 19, 'eabd1dccd2ac3ac888b5f785e6485e83', '2025-08-11 15:08:09'),
(22, 32, 'b247a2af429fc43eec656ce8921ffde0', '2025-08-11 15:09:10'),
(23, 18, '949293b3d36cc4939eef16e8716a7d24', '2025-08-11 15:09:45'),
(24, 31, '7a943e3bf2eb05eaf9dc9fcbf3e0c903', '2025-08-11 15:10:21'),
(25, 29, '5e9b40ba67edb01595945a010dc7c245', '2025-08-11 15:11:14'),
(26, 15, '82124b2cd86dc7a6d810bfbcdcd5f947', '2025-08-11 15:13:06'),
(27, 26, 'dbafa3609c5a7a936a4c02c0d757ca48', '2025-08-11 15:13:57'),
(28, 27, 'f99beccbc31947d9b9913a17bdacaba8', '2025-08-11 15:14:15'),
(30, 24, '934d2b35f84afb432d09cd7d5ba011e7', '2025-08-11 15:19:29'),
(31, 33, '369e0cf17ce571a55a702e08220ded25', '2025-08-14 00:12:55'),
(32, 34, '008427ef4627b74b2da4b37f4c1e5590', '2025-10-10 12:19:58'),
(33, 35, 'f90e336021a017fdff099de7d4c60452', '2025-10-10 12:22:05'),
(34, 36, 'b4b936c57761f8948624383ba31daf6f', '2025-10-10 12:22:11'),
(35, 37, '9c1caf2edb9823dee21b22a923457e63', '2025-10-10 12:23:40'),
(45, 47, '9e656443b1a62afe7649e28073377870', '2025-10-21 19:39:33'),
(46, 48, '5a753b97e4da590f09685fb41cb7d9c8', '2025-10-21 19:39:41'),
(47, 51, 'f9536bdb7df7195fbad2a9fa17c2fc18', '2025-10-21 19:47:27'),
(48, 49, '7fe6f477c0bcfeaa8199d10564a9297a', '2025-10-21 19:58:23'),
(49, 50, '9b4f2c76ac558b75a09c0d7fbf3173a3', '2025-10-21 19:58:41'),
(50, 52, 'c3358012bfce92a453e84477c043ae44', '2025-10-21 20:00:17'),
(51, 59, '0f28ade59ca93cf07b7006b54381cba5', '2025-10-21 20:05:38'),
(52, 58, '88c28362abada1407e4fd3a218eef0c7', '2025-10-21 20:05:48'),
(53, 57, 'ed03e93e1d61d1622ea9957b241fad00', '2025-10-21 20:05:55'),
(54, 56, '06a4276ba42e3ba8c1e52f44056726a5', '2025-10-21 20:05:58'),
(55, 55, '1fa0c4b6ac60af317e54a1f3c1617ff4', '2025-10-21 20:06:04'),
(56, 54, '92cf67002b3d1635453ba8c9f9b62ba1', '2025-10-21 20:06:09'),
(57, 53, 'cfc42bbee29bdfe0461bcd196e1fa5be', '2025-10-21 20:06:13'),
(58, 60, '96dafad1f5729d698d510ad03b79ee7d', '2025-10-21 20:11:25'),
(59, 30, 'd2ca30705c9b31d3c223847fb4eb78b2', '2025-10-21 21:27:48');

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

--
-- Dumping data for table `payouts`
--

INSERT INTO `payouts` (`id`, `payout_id`, `member_id`, `gross_payout`, `total_amount`, `scheduled_date`, `actual_payout_date`, `status`, `payout_method`, `processed_by_admin_id`, `admin_fee`, `net_amount`, `transaction_reference`, `receipt_issued`, `member_signature`, `payout_notes`, `created_at`, `updated_at`) VALUES
(16, 'PO-20250808-345', 13, 9000.00, 8980.00, '2025-08-11', '2025-08-11', 'completed', 'mixed', 8, 20.00, 7980.00, NULL, 0, 0, '', '2025-08-08 10:28:01', '2025-08-16 03:53:22'),
(17, 'PO-20250811-148', 7, 9000.00, 8980.00, '2025-08-11', '2025-07-05', 'completed', 'mixed', 8, 20.00, 7980.00, NULL, 0, 0, '', '2025-08-11 13:37:42', '2025-08-16 03:53:25'),
(19, 'PO-20251002-718', 22, 10000.00, 9980.00, '2025-10-02', '2025-09-05', 'completed', 'bank_transfer', 8, 20.00, 8980.00, NULL, 0, 0, '', '2025-10-02 08:34:25', '2025-10-21 20:12:46'),
(20, 'PO-20251020-666', 20, 10000.00, 9980.00, '2025-10-05', '2025-10-05', 'completed', 'cash', 8, 20.00, 9000.00, NULL, 0, 0, '', '2025-10-20 21:44:29', '2025-10-21 20:21:44');

-- --------------------------------------------------------

--
-- Table structure for table `payout_receipts`
--

CREATE TABLE `payout_receipts` (
  `id` int(11) NOT NULL,
  `payout_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payout_receipts`
--

INSERT INTO `payout_receipts` (`id`, `payout_id`, `token`, `created_at`) VALUES
(2, 17, '368752119886b849cc41e035eeb09f6b', '2025-08-11 13:37:53'),
(3, 16, '8f36c2f80bbf2e8a50564436668c26ea', '2025-08-11 20:01:06'),
(5, 20, 'bfb4b302082f5e0d9dcaa60b3b6d569a', '2025-10-20 21:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `position_swap_history`
--

CREATE TABLE `position_swap_history` (
  `id` int(11) NOT NULL,
  `swap_request_id` int(11) NOT NULL,
  `member_a_id` int(11) NOT NULL COMMENT 'First member in swap',
  `member_b_id` int(11) NOT NULL COMMENT 'Second member in swap',
  `position_a_before` int(11) NOT NULL COMMENT 'Member A position before swap',
  `position_b_before` int(11) NOT NULL COMMENT 'Member B position before swap',
  `position_a_after` int(11) NOT NULL COMMENT 'Member A position after swap',
  `position_b_after` int(11) NOT NULL COMMENT 'Member B position after swap',
  `swap_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_by_admin_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for completed position swaps';

--
-- Dumping data for table `position_swap_history`
--

INSERT INTO `position_swap_history` (`id`, `swap_request_id`, `member_a_id`, `member_b_id`, `position_a_before`, `position_b_before`, `position_a_after`, `position_b_after`, `swap_date`, `processed_by_admin_id`, `notes`, `created_at`) VALUES
(5, 9, 10, 8, 9, 0, 9, 0, '2025-08-11 15:51:12', 8, 'REJECTED: Not valid', '2025-08-11 15:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `position_swap_requests`
--

CREATE TABLE `position_swap_requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL COMMENT 'Unique request ID (PSR-YYYYMMDD-XXX)',
  `member_id` int(11) NOT NULL COMMENT 'Member requesting the swap',
  `current_position` int(11) NOT NULL COMMENT 'Member current payout position',
  `requested_position` int(11) NOT NULL COMMENT 'Position they want to swap to',
  `target_member_id` int(11) DEFAULT NULL COMMENT 'Member who currently holds requested position',
  `reason` text DEFAULT NULL COMMENT 'Optional reason for swap request',
  `request_type` enum('swap','specific_position') NOT NULL DEFAULT 'swap' COMMENT 'Type of request',
  `status` enum('pending','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `requested_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_response_date` timestamp NULL DEFAULT NULL,
  `processed_by_admin_id` int(11) DEFAULT NULL COMMENT 'Admin who processed the request',
  `admin_notes` text DEFAULT NULL COMMENT 'Admin notes about the decision',
  `completion_date` timestamp NULL DEFAULT NULL,
  `swap_fee` decimal(8,2) DEFAULT 0.00 COMMENT 'Fee charged for position swap (if any)',
  `priority_level` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `member_email_sent` tinyint(1) DEFAULT 0 COMMENT 'Email notification sent to member',
  `admin_email_sent` tinyint(1) DEFAULT 0 COMMENT 'Email notification sent to admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores all position swap requests from members';

--
-- Dumping data for table `position_swap_requests`
--

INSERT INTO `position_swap_requests` (`id`, `request_id`, `member_id`, `current_position`, `requested_position`, `target_member_id`, `reason`, `request_type`, `status`, `requested_date`, `admin_response_date`, `processed_by_admin_id`, `admin_notes`, `completion_date`, `swap_fee`, `priority_level`, `member_email_sent`, `admin_email_sent`, `created_at`, `updated_at`) VALUES
(4, 'PSR-20250807-001', 7, 1, 4, 10, 'I need to change', 'specific_position', 'cancelled', '2025-08-07 20:06:53', NULL, NULL, NULL, NULL, 0.00, 'medium', 0, 0, '2025-08-07 20:06:53', '2025-08-07 20:13:45'),
(9, 'PSR-20250810-001', 10, 9, 4, 8, '', 'specific_position', 'rejected', '2025-08-10 20:50:20', NULL, 8, 'Not valid', NULL, 0.00, 'medium', 0, 0, '2025-08-10 20:50:20', '2025-08-11 15:51:12');

--
-- Triggers `position_swap_requests`
--
DELIMITER $$
CREATE TRIGGER `generate_swap_request_id` BEFORE INSERT ON `position_swap_requests` FOR EACH ROW BEGIN
    DECLARE next_number INT;
    DECLARE date_part VARCHAR(8);
    
    -- Get current date in YYYYMMDD format
    SET date_part = DATE_FORMAT(NOW(), '%Y%m%d');
    
    -- Get next sequential number for today
    SELECT COALESCE(MAX(CAST(SUBSTRING(request_id, -3) AS UNSIGNED)), 0) + 1 
    INTO next_number 
    FROM position_swap_requests 
    WHERE request_id LIKE CONCAT('PSR-', date_part, '-%');
    
    -- Set the request_id if not provided
    IF NEW.request_id IS NULL OR NEW.request_id = '' THEN
        SET NEW.request_id = CONCAT('PSR-', date_part, '-', LPAD(next_number, 3, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `program_notifications`
--

CREATE TABLE `program_notifications` (
  `id` int(11) NOT NULL,
  `notification_code` varchar(32) NOT NULL,
  `created_by_admin_id` int(11) DEFAULT NULL,
  `audience_type` enum('all','equb','members') NOT NULL DEFAULT 'all',
  `equb_settings_id` int(11) DEFAULT NULL,
  `title_en` varchar(255) NOT NULL,
  `title_am` varchar(255) NOT NULL,
  `body_en` text NOT NULL,
  `body_am` text NOT NULL,
  `priority` enum('normal','high') NOT NULL DEFAULT 'normal',
  `status` enum('draft','sent') NOT NULL DEFAULT 'sent',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_notifications`
--

INSERT INTO `program_notifications` (`id`, `notification_code`, `created_by_admin_id`, `audience_type`, `equb_settings_id`, `title_en`, `title_am`, `body_en`, `body_am`, `priority`, `status`, `scheduled_at`, `sent_at`, `created_at`, `updated_at`) VALUES
(30, 'NTF-20250810-881', 8, 'members', 2, 'August 2025 payment', 'August 2025 payment', 'Dear Sabella, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Sabella, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-10 12:02:51', '2025-08-10 12:02:51', '2025-08-10 12:02:51'),
(31, 'NTF-20250810-841', 8, 'members', 2, 'September 2025 payment', 'September 2025 payment', 'Dear Sabella, your payment for September 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Sabella, your payment for September 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-10 12:02:55', '2025-08-10 12:02:55', '2025-08-10 12:02:55'),
(32, 'NTF-20250810-119', 8, 'members', 2, 'October 2025 payment', 'October 2025 payment', 'Dear Sabella, your payment for October 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Sabella, your payment for October 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-10 12:12:03', '2025-08-10 12:12:03', '2025-08-10 12:12:03'),
(34, 'NTF-20250810-342', 8, 'members', NULL, 'HI Konjo', 'HI Konjo', '!', '!', 'normal', 'sent', NULL, '2025-08-10 12:54:42', '2025-08-10 12:54:42', '2025-08-10 12:54:42'),
(35, 'NTF-20250810-925', 8, 'members', NULL, 'Welcome!', 'Welcome!', 'Notification improved!', 'Notification improved!', 'normal', 'sent', NULL, '2025-08-10 12:59:15', '2025-08-10 12:59:15', '2025-08-10 12:59:15'),
(36, 'NTF-20250810-844', 8, 'all', NULL, 'Welcome!', 'Welcome!', 'Test', 'Test', 'normal', 'sent', NULL, '2025-08-10 13:00:43', '2025-08-10 13:00:43', '2025-08-10 13:00:43'),
(37, 'NTF-20250810-422', 8, 'members', NULL, 'Welcome!', 'Welcome!', 'Test', 'Test!', 'normal', 'sent', NULL, '2025-08-10 13:01:42', '2025-08-10 13:01:42', '2025-08-10 13:01:42'),
(38, 'NTF-20250810-786', 8, 'all', NULL, 'Welcome!', 'Welcome!', 'Test', 'Test', 'normal', 'sent', NULL, '2025-08-10 13:02:12', '2025-08-10 13:02:12', '2025-08-10 13:02:12'),
(39, 'NTF-20250810-240', 8, 'members', NULL, 'I love Feven!', 'I love Feven!', 'Feven Is love', 'Feven Is love', 'normal', 'sent', NULL, '2025-08-10 13:03:40', '2025-08-10 13:03:40', '2025-08-10 13:03:40'),
(40, 'NTF-20250810-910', 8, 'members', NULL, 'I love Feven!', 'I love Feven!', 'Feven Is love!', 'Feven Is love!', 'normal', 'sent', NULL, '2025-08-10 13:07:56', '2025-08-10 13:07:56', '2025-08-10 13:07:56'),
(41, 'NTF-20250810-722', 8, 'members', NULL, 'Welcome!', 'Welcome!', 'let\'s back to the privios!', 'let\'s back to the privios!', 'normal', 'sent', NULL, '2025-08-10 13:20:45', '2025-08-10 13:20:45', '2025-08-10 13:20:45'),
(42, 'NTF-20250810-744', 8, 'members', NULL, 'Welcome!', 'Welcome!', 'How Are you', 'How Are you', 'normal', 'sent', NULL, '2025-08-10 13:28:10', '2025-08-10 13:28:10', '2025-08-10 13:28:10'),
(43, 'NTF-20250810-628', 8, 'members', NULL, 'Birthday', 'Birthday', 'Gift', 'Gift', 'normal', 'sent', NULL, '2025-08-10 13:34:37', '2025-08-10 13:34:37', '2025-08-10 13:34:37'),
(44, 'NTF-20250810-170', 8, 'members', NULL, 'Works fine now!', 'Works fine now!', 'Works fine now!', 'Works fine now!', 'normal', 'sent', NULL, '2025-08-10 13:40:10', '2025-08-10 13:40:10', '2025-08-10 13:40:10'),
(45, 'NTF-20250810-995', 8, 'members', NULL, 'Works fine now!', 'Works fine now!', 'Works fine now!', 'Works fine now!', 'normal', 'sent', NULL, '2025-08-10 13:44:13', '2025-08-10 13:44:13', '2025-08-10 13:44:13'),
(46, 'NTF-20250810-536', 8, 'members', NULL, 'FIxed now', 'FIxed now', 'FIxed now', 'FIxed now', 'normal', 'sent', NULL, '2025-08-10 14:38:50', '2025-08-10 14:38:50', '2025-08-10 14:38:50'),
(47, 'NTF-20250810-613', 8, 'members', 2, 'August 2025 payment', 'August 2025 payment', 'Dear Barnabas, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Barnabas, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-10 22:00:43', '2025-08-10 22:00:43', '2025-08-10 22:00:43'),
(48, 'NTF-20250811-152', 8, 'members', NULL, 'Welcome!', 'እንኳን ደህና መጡ', '.', '.', 'normal', 'sent', NULL, '2025-08-11 01:48:49', '2025-08-11 01:48:49', '2025-08-11 01:48:49'),
(49, 'NTF-20250811-372', 8, 'members', NULL, 'Welcome!', 'እንኳን ደህና መጡ', '.', '.', 'normal', 'sent', NULL, '2025-08-11 01:56:58', '2025-08-11 01:56:58', '2025-08-11 01:56:58'),
(50, 'NTF-20250811-945', 8, 'members', NULL, 'FIxed now', 'Works fine now!', '.', '.', 'normal', 'sent', NULL, '2025-08-11 02:02:10', '2025-08-11 02:02:10', '2025-08-11 02:02:10'),
(51, 'NTF-20250811-138', 8, 'members', NULL, 'Welcome!', 'እንኳን ደህና መጡ', ',', ',', 'normal', 'sent', NULL, '2025-08-11 02:04:47', '2025-08-11 02:04:47', '2025-08-11 02:04:47'),
(52, 'NTF-20250811-585', 8, 'members', 2, 'August 2025 payment', 'August 2025 payment', 'Dear Abel, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Abel, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-11 02:05:58', '2025-08-11 02:05:58', '2025-08-11 02:05:58'),
(53, 'NTF-20250811-839', 8, 'all', NULL, 'Welcome!', 'እንኳን ደህና መጡ', '.', '.', 'normal', 'sent', NULL, '2025-08-11 02:18:09', '2025-08-11 02:18:09', '2025-08-11 02:18:09'),
(54, 'NTF-20250811-977', 8, 'all', NULL, '.', '.', '.', '.', 'normal', 'sent', NULL, '2025-08-11 02:18:52', '2025-08-11 02:18:52', '2025-08-11 02:18:52'),
(55, 'NTF-20250811-341', 8, 'members', NULL, 's', 'wq', 'w', 'w', 'normal', 'sent', NULL, '2025-08-11 02:27:51', '2025-08-11 02:27:51', '2025-08-11 02:27:51'),
(56, 'NTF-20250811-557', 8, 'members', NULL, 'fl', 'aw', 'ca', 'wq', 'normal', 'sent', NULL, '2025-08-11 02:32:28', '2025-08-11 02:32:28', '2025-08-11 02:32:28'),
(57, 'NTF-20250811-193', 8, 'members', 2, 'August 2025 payment', 'August 2025 payment', 'Dear Abel, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Abel, your payment for August 2025 has been verified. Thanks for your payment!\n\n- Payment amount: £1,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-11 03:48:28', '2025-08-11 03:48:28', '2025-08-11 03:48:28'),
(58, 'NTF-20250811-736', 8, 'members', 2, 'Payout completed', 'Payout completed', 'Dear Abel, your payout has been completed and recorded.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Abel, your payout has been completed and recorded.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-11 13:56:25', '2025-08-11 13:56:25', '2025-08-11 13:56:25'),
(59, 'NTF-20250811-216', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Abel, your Equb payout has been successfully completed on August 11, 2025.\n\n- Payout amount: £9,980.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=b8e742d937a30b1130e4d348d4b0c14c\n\nThank you.', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የወሰዱት መጠን: £9,980.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=b8e742d937a30b1130e4d348d4b0c14c\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 14:08:36', '2025-08-11 14:08:36', '2025-08-11 14:08:36'),
(60, 'NTF-20250811-835', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Abel, your Equb payout has been successfully completed on August 11, 2025.\n\n- Payout amount: £9,980.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=b8e742d937a30b1130e4d348d4b0c14c\n\nThank you.', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የወሰዱት መጠን: £9,980.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=b8e742d937a30b1130e4d348d4b0c14c\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 14:31:02', '2025-08-11 14:31:02', '2025-08-11 14:31:02'),
(61, 'NTF-20250811-197', 8, 'members', 2, 'Payout scheduled', 'Payout scheduled', 'Dear Abel, your payout has been scheduled for August 11, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Abel, your payout has been scheduled for August 11, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-08-11 14:37:42', '2025-08-11 14:37:42', '2025-08-11 14:37:42'),
(62, 'NTF-20250811-325', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Abel, your Equb payout has been successfully completed on August 11, 2025.\n\n- Net payout: £8,980.00\n- Admin fee applied: £20.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nThank you.', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 14:37:53', '2025-08-11 14:37:53', '2025-08-11 14:37:53'),
(63, 'NTF-20250811-433', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Abel, your Equb payout has been successfully completed on August 11, 2025.\n\n- Net payout: £8,980.00\n- Admin fee applied: £20.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nThank you.', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 15:08:02', '2025-08-11 15:08:02', '2025-08-11 15:08:02'),
(64, 'NTF-20250811-262', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Abel, your Equb payout has been successfully completed on August 11, 2025.\n\n- Net payout: £8,980.00\n- Admin fee applied: £20.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nThank you.', 'ውድ Abel የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=368752119886b849cc41e035eeb09f6b\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 16:30:15', '2025-08-11 16:30:15', '2025-08-11 16:30:15'),
(65, 'NTF-20250811-144', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Maruf, your Equb payout has been successfully completed on August 11, 2025.\n\n- Net payout: £8,980.00\n- Admin fee applied: £20.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=8f36c2f80bbf2e8a50564436668c26ea\n\nThank you.', 'ውድ Maruf የእቁብ መክፈያዎ በAugust 11, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=8f36c2f80bbf2e8a50564436668c26ea\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-08-11 21:01:07', '2025-08-11 21:01:07', '2025-08-11 21:01:07'),
(66, 'NTF-20251002-306', 8, 'members', 2, 'Payout scheduled', 'Payout scheduled', 'Dear Samson, your payout has been scheduled for October 2, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Samson, your payout has been scheduled for October 2, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-10-02 09:33:12', '2025-10-02 09:33:12', '2025-10-02 09:33:12'),
(67, 'NTF-20251002-708', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Samson, your Equb payout has been successfully completed on October 2, 2025.\n\n- Net payout: £8,980.00\n- Admin fee applied: £20.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=5671fd6c4d020c62d3d4f5c539a433e7\n\nThank you.', 'ውድ Samson የእቁብ መክፈያዎ በOctober 2, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £8,980.00\n- የአስተዳደር ክፍያ ተተግብሯል: £20.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=5671fd6c4d020c62d3d4f5c539a433e7\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-10-02 09:33:22', '2025-10-02 09:33:22', '2025-10-02 09:33:22'),
(68, 'NTF-20251002-097', 8, 'members', 2, 'Payout scheduled', 'Payout scheduled', 'Dear Kagnew, your payout has been scheduled for October 2, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Kagnew, your payout has been scheduled for October 2, 2025.\n\n- Payout amount: £8,980.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-10-02 09:34:25', '2025-10-02 09:34:25', '2025-10-02 09:34:25'),
(69, 'NTF-20251020-956', 8, 'members', 2, 'Payout scheduled', 'Payout scheduled', 'Dear Samson, your payout has been scheduled for October 5, 2025.\n\n- Payout amount: £9,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'Dear Samson, your payout has been scheduled for October 5, 2025.\n\n- Payout amount: £9,000.00\n\nFor more information, including accessing the receipt, please check the HabeshaEqub dashboard.', 'normal', 'sent', NULL, '2025-10-20 22:44:29', '2025-10-20 22:44:29', '2025-10-20 22:44:29'),
(70, 'NTF-20251020-434', 8, 'members', 2, 'Congratulations — Your Equb payout is completed!', 'እንኳን ደስ አላችሁ — እቁቡ ተጠናቋል!', 'Dear Samson, your Equb payout has been successfully completed on October 20, 2025.\n\n- Net payout: £9,000.00\n- Admin fee applied: £0.00\n\nYou can view and download your payout receipt here: https://habeshaequb.com/receipt.php?rt=bfb4b302082f5e0d9dcaa60b3b6d569a\n\nThank you.', 'ውድ Samson የእቁብ መክፈያዎ በOctober 20, 2025 በተሳካ ሁኔታ ተጠናቋል።\n\n- የተቀበሉት መጠን (ኔት): £9,000.00\n- የአስተዳደር ክፍያ ተተግብሯል: £0.00\n\nደረሰኙን ለመመልከት እና ለመውሰድ እባክዎን የሚከተለውን ሊንክ ይጎብኙ፦ https://habeshaequb.com/receipt.php?rt=bfb4b302082f5e0d9dcaa60b3b6d569a\n\nእናመሰግናለን።', 'normal', 'sent', NULL, '2025-10-20 22:44:38', '2025-10-20 22:44:38', '2025-10-20 22:44:38');

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
  `otp_type` enum('email_verification','login','otp_login','admin_login') NOT NULL DEFAULT 'email_verification',
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_otps`
--

INSERT INTO `user_otps` (`id`, `user_id`, `email`, `otp_code`, `otp_type`, `expires_at`, `is_used`, `attempt_count`, `created_at`) VALUES
(108, 8, 'abeldemessie77@gmail.com', '7332', 'otp_login', '2025-08-10 09:43:27', 1, 0, '2025-08-10 09:33:27'),
(118, 8, 'fisssaba@gmail.com', '2298', 'otp_login', '2025-08-10 13:54:42', 1, 0, '2025-08-10 13:44:42'),
(131, 8, 'abelgoytom77@gmail.com', '654120', '', '2025-08-11 12:26:48', 0, 0, '2025-08-11 12:16:48'),
(132, 8, 'abelgoytom77@gmail.com', '577992', '', '2025-08-11 12:27:45', 0, 0, '2025-08-11 12:17:45'),
(133, 8, 'abelgoytom77@gmail.com', '303061', '', '2025-08-11 12:31:11', 0, 0, '2025-08-11 12:21:11'),
(134, NULL, 'abelgoytom77@gmail.com', '881173', '', '2025-08-11 12:38:31', 0, 0, '2025-08-11 12:28:31'),
(158, 20, 'samyshafi01@gmail.com', '3561', 'otp_login', '2025-08-17 12:34:24', 1, 0, '2025-08-17 12:24:24'),
(161, 14, 'kagnew_s@yahoo.com', '3570', 'otp_login', '2025-08-18 23:49:50', 0, 0, '2025-08-18 23:39:50'),
(162, 14, 'kagnew_s@yahoo.com', '6925', 'otp_login', '2025-08-18 23:50:45', 0, 0, '2025-08-18 23:40:45'),
(171, 12, 'biniamtsegay77@gmail.com', '1996', 'otp_login', '2025-08-28 07:00:16', 1, 0, '2025-08-28 06:50:16'),
(180, 17, 'eliasfriew616@gmail.com', '7591', 'otp_login', '2025-09-12 20:45:55', 0, 0, '2025-09-12 20:35:55'),
(188, 10, 'barnabasdagnachew25@gmail.com', '8571', 'otp_login', '2025-10-20 16:08:07', 1, 0, '2025-10-20 15:58:07'),
(208, 18, 'hagosmahleit@gmail.com', '2143', 'otp_login', '2025-10-21 21:41:26', 1, 0, '2025-10-21 21:31:26'),
(209, 7, 'abelgoytom77@gmail.com', '9452', 'otp_login', '2025-10-21 21:52:03', 1, 0, '2025-10-21 21:42:03'),
(210, NULL, 'abelgoytom77@gmail.com', '286462', 'admin_login', '2025-10-21 21:53:19', 1, 0, '2025-10-21 21:43:19');

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
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_notification_member` (`notification_id`,`member_id`),
  ADD KEY `idx_recipient_member` (`member_id`);

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
-- Indexes for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_payment` (`payment_id`);

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
-- Indexes for table `payout_receipts`
--
ALTER TABLE `payout_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_payout` (`payout_id`);

--
-- Indexes for table `position_swap_history`
--
ALTER TABLE `position_swap_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `swap_request_id` (`swap_request_id`),
  ADD KEY `member_a_id` (`member_a_id`),
  ADD KEY `member_b_id` (`member_b_id`),
  ADD KEY `swap_date` (`swap_date`),
  ADD KEY `processed_by_admin_id` (`processed_by_admin_id`),
  ADD KEY `idx_swap_history_members` (`member_a_id`,`member_b_id`);

--
-- Indexes for table `position_swap_requests`
--
ALTER TABLE `position_swap_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `target_member_id` (`target_member_id`),
  ADD KEY `status` (`status`),
  ADD KEY `requested_date` (`requested_date`),
  ADD KEY `current_position` (`current_position`),
  ADD KEY `requested_position` (`requested_position`),
  ADD KEY `processed_by_admin_id` (`processed_by_admin_id`),
  ADD KEY `idx_swap_requests_member_status` (`member_id`,`status`),
  ADD KEY `idx_swap_requests_target_status` (`target_member_id`,`status`),
  ADD KEY `idx_swap_requests_positions` (`current_position`,`requested_position`);

--
-- Indexes for table `program_notifications`
--
ALTER TABLE `program_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_code` (`notification_code`),
  ADD KEY `idx_program_notifications_equb` (`equb_settings_id`),
  ADD KEY `fk_program_notifications_admin` (`created_by_admin_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `email_preferences`
--
ALTER TABLE `email_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_rate_limits`
--
ALTER TABLE `email_rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payout_receipts`
--
ALTER TABLE `payout_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `position_swap_history`
--
ALTER TABLE `position_swap_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `position_swap_requests`
--
ALTER TABLE `position_swap_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `program_notifications`
--
ALTER TABLE `program_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

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
-- Constraints for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `fk_recipient_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recipient_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`verified_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD CONSTRAINT `fk_receipt_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payouts_ibfk_2` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payout_receipts`
--
ALTER TABLE `payout_receipts`
  ADD CONSTRAINT `fk_receipt_payout` FOREIGN KEY (`payout_id`) REFERENCES `payouts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `position_swap_history`
--
ALTER TABLE `position_swap_history`
  ADD CONSTRAINT `position_swap_history_ibfk_1` FOREIGN KEY (`swap_request_id`) REFERENCES `position_swap_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `position_swap_history_ibfk_2` FOREIGN KEY (`member_a_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `position_swap_history_ibfk_3` FOREIGN KEY (`member_b_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `position_swap_history_ibfk_4` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `position_swap_requests`
--
ALTER TABLE `position_swap_requests`
  ADD CONSTRAINT `position_swap_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `position_swap_requests_ibfk_2` FOREIGN KEY (`target_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `position_swap_requests_ibfk_3` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `program_notifications`
--
ALTER TABLE `program_notifications`
  ADD CONSTRAINT `fk_program_notifications_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_program_notifications_equb` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
