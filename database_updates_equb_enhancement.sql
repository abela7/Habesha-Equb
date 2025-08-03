-- ===============================================
-- HABESHA EQUB - PROFESSIONAL DATABASE ENHANCEMENTS
-- Top-tier financial system improvements
-- ===============================================

-- Add joint membership support to members table
ALTER TABLE `members` 
ADD COLUMN `membership_type` ENUM('individual', 'joint') NOT NULL DEFAULT 'individual' COMMENT 'Type of membership - individual or joint',
ADD COLUMN `joint_group_id` VARCHAR(20) NULL COMMENT 'Unique identifier for joint membership group',
ADD COLUMN `joint_member_count` TINYINT(2) DEFAULT 1 COMMENT 'Number of people in joint membership (1 for individual)',
ADD COLUMN `individual_contribution` DECIMAL(10,2) NULL COMMENT 'Individual contribution amount for joint members',
ADD COLUMN `joint_position_share` DECIMAL(5,4) DEFAULT 1.0000 COMMENT 'Share of the joint position (0.5 for 50/50 split)',
ADD COLUMN `primary_joint_member` TINYINT(1) DEFAULT 1 COMMENT '1 if primary contact for joint membership',
ADD COLUMN `payout_split_method` ENUM('equal', 'proportional', 'custom') DEFAULT 'equal' COMMENT 'How to split payouts in joint membership';

-- Create joint membership groups table for better management
CREATE TABLE `joint_membership_groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `joint_group_id` VARCHAR(20) NOT NULL COMMENT 'Unique identifier: JNT-EQB001-001',
  `equb_settings_id` INT(11) NOT NULL,
  `group_name` VARCHAR(100) NULL COMMENT 'Optional name for the joint group',
  `total_monthly_payment` DECIMAL(10,2) NOT NULL COMMENT 'Combined monthly payment for the group',
  `member_count` TINYINT(2) NOT NULL DEFAULT 2 COMMENT 'Number of members in the joint group',
  `payout_position` INT(3) NOT NULL COMMENT 'Shared payout position',
  `payout_split_method` ENUM('equal', 'proportional', 'custom') DEFAULT 'equal',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `joint_group_id` (`joint_group_id`),
  KEY `idx_equb_settings` (`equb_settings_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `joint_groups_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create joint payout splits table for tracking individual shares
CREATE TABLE `joint_payout_splits` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `joint_group_id` VARCHAR(20) NOT NULL,
  `member_id` INT(11) NOT NULL,
  `payout_id` INT(11) NOT NULL COMMENT 'Reference to main payout record',
  `split_amount` DECIMAL(12,2) NOT NULL COMMENT 'Individual share of the payout',
  `split_percentage` DECIMAL(5,4) NOT NULL COMMENT 'Percentage of total payout (0.5000 = 50%)',
  `payment_method` ENUM('cash','bank_transfer','mobile_money') DEFAULT 'bank_transfer',
  `transaction_reference` VARCHAR(100) NULL,
  `is_paid` TINYINT(1) DEFAULT 0,
  `paid_date` DATE NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_joint_group` (`joint_group_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_payout` (`payout_id`),
  CONSTRAINT `joint_splits_member_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `joint_splits_payout_fk` FOREIGN KEY (`payout_id`) REFERENCES `payouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced financial tracking table
CREATE TABLE `equb_financial_summary` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `equb_settings_id` INT(11) NOT NULL,
  `calculation_date` DATE NOT NULL,
  `total_members` INT(3) NOT NULL,
  `individual_members` INT(3) NOT NULL,
  `joint_groups` INT(3) NOT NULL,
  `total_monthly_pool` DECIMAL(15,2) NOT NULL,
  `total_pool_duration` DECIMAL(15,2) NOT NULL,
  `total_collected` DECIMAL(15,2) DEFAULT 0.00,
  `total_distributed` DECIMAL(15,2) DEFAULT 0.00,
  `outstanding_balance` DECIMAL(15,2) DEFAULT 0.00,
  `admin_fees_collected` DECIMAL(12,2) DEFAULT 0.00,
  `late_fees_collected` DECIMAL(12,2) DEFAULT 0.00,
  `financial_status` ENUM('balanced', 'surplus', 'deficit') DEFAULT 'balanced',
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_equb_date` (`equb_settings_id`, `calculation_date`),
  KEY `idx_calculation_date` (`calculation_date`),
  CONSTRAINT `financial_summary_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
ALTER TABLE `members` 
ADD INDEX `idx_membership_type` (`membership_type`),
ADD INDEX `idx_joint_group` (`joint_group_id`),
ADD INDEX `idx_primary_joint` (`primary_joint_member`);

-- Add financial audit trail table
CREATE TABLE `financial_audit_trail` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `equb_settings_id` INT(11) NOT NULL,
  `member_id` INT(11) NULL,
  `action_type` ENUM('payment_added', 'payment_verified', 'payout_calculated', 'payout_processed', 'joint_split_processed', 'financial_adjustment') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `description` TEXT NOT NULL,
  `performed_by_admin_id` INT(11) NOT NULL,
  `reference_id` INT(11) NULL COMMENT 'Reference to payment/payout ID',
  `before_balance` DECIMAL(12,2) NULL,
  `after_balance` DECIMAL(12,2) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_equb` (`equb_settings_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_admin` (`performed_by_admin_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_equb_fk` FOREIGN KEY (`equb_settings_id`) REFERENCES `equb_settings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_member_fk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_admin_fk` FOREIGN KEY (`performed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update equb_settings table for enhanced tracking
ALTER TABLE `equb_settings`
ADD COLUMN `supports_joint_membership` TINYINT(1) DEFAULT 1 COMMENT 'Whether this equb allows joint memberships',
ADD COLUMN `max_joint_members_per_group` TINYINT(2) DEFAULT 3 COMMENT 'Maximum members allowed in a joint group',
ADD COLUMN `financial_status` ENUM('balanced', 'surplus', 'deficit', 'under_review') DEFAULT 'balanced',
ADD COLUMN `last_financial_audit` TIMESTAMP NULL COMMENT 'Last time financial audit was performed';

-- ===============================================
-- IMPORTANT: RUN THESE UPDATES AFTER SCHEMA CHANGES
-- ===============================================

-- Update existing members to have individual membership type (already default)
UPDATE `members` SET `membership_type` = 'individual' WHERE `membership_type` IS NULL;

-- Create initial financial summary records for existing equbs
INSERT INTO `equb_financial_summary` (
    `equb_settings_id`, `calculation_date`, `total_members`, `individual_members`, 
    `joint_groups`, `total_monthly_pool`, `total_pool_duration`
)
SELECT 
    es.id,
    CURDATE(),
    COALESCE(COUNT(m.id), 0),
    COALESCE(COUNT(CASE WHEN m.membership_type = 'individual' THEN 1 END), 0),
    0, -- joint_groups (will be 0 initially)
    COALESCE(SUM(m.monthly_payment), 0),
    COALESCE(SUM(m.monthly_payment), 0) * es.duration_months
FROM `equb_settings` es
LEFT JOIN `members` m ON es.id = m.equb_settings_id AND m.is_active = 1
WHERE es.status = 'active'
GROUP BY es.id
ON DUPLICATE KEY UPDATE
    `total_members` = VALUES(`total_members`),
    `individual_members` = VALUES(`individual_members`),
    `total_monthly_pool` = VALUES(`total_monthly_pool`),
    `total_pool_duration` = VALUES(`total_pool_duration`);