-- SQL Script for SMS Integration
-- Run this in phpMyAdmin to add SMS functionality

-- 1. Add SMS configuration settings to system_settings table
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_category`, `setting_type`, `setting_description`, `created_at`, `updated_at`) VALUES
('sms_enabled', '1', 'sms', 'boolean', 'Enable/disable SMS functionality', NOW(), NOW()),
('sms_api_key', '', 'sms', 'password', 'Brevo API key for SMS (starts with xkeysib-)', NOW(), NOW()),
('sms_sender_name', 'HabeshaEqub', 'sms', 'text', 'Sender name for SMS (max 11 chars for UK)', NOW(), NOW()),
('sms_test_mode', '0', 'sms', 'boolean', 'Test mode - logs SMS without sending', NOW(), NOW());

-- 2. Create SMS rate limiting table
CREATE TABLE IF NOT EXISTS `sms_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(20) NOT NULL,
  `sent_count` int(11) DEFAULT 1,
  `last_sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_phone` (`phone_number`),
  KEY `idx_reset_at` (`reset_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Add sms_notifications field to members table (optional - if not using notification_preferences)
-- This allows members to enable/disable SMS notifications individually
ALTER TABLE `members` 
ADD COLUMN `sms_notifications` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Enable/disable SMS notifications (0=No, 1=Yes)' 
AFTER `email_notifications`;

-- Note: The existing notification_preferences field already supports 'sms' and 'both' options
-- So this additional field is optional for more granular control

-- 4. Update notifications table channel enum to ensure 'sms' and 'both' are included (already exists in your schema)
-- No action needed - your notifications table already has: enum('email','sms','both')

-- Success! SMS integration tables are ready.
-- Next steps:
-- 1. Add your Brevo API key to system_settings (setting_key='sms_api_key')
-- 2. Configure sender name if different from 'HabeshaEqub'
-- 3. Test with a small group first

