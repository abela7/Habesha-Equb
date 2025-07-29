-- HabeshaEqub System Settings Table
-- Run this in phpMyAdmin if the table doesn't auto-create

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_category` VARCHAR(50) DEFAULT 'general',
    `setting_type` VARCHAR(20) DEFAULT 'text',
    `setting_description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`setting_category`),
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_category`, `setting_type`, `setting_description`, `created_at`) VALUES
-- General Settings
('app_name', 'HabeshaEqub', 'general', 'text', 'The name of your application shown throughout the system', NOW()),
('app_description', 'Ethiopian traditional savings group management system', 'general', 'text', 'Brief description of your equb application', NOW()),
('maintenance_mode', '0', 'general', 'boolean', 'Enable to put the system in maintenance mode', NOW()),
('session_timeout', '60', 'general', 'select', 'User session timeout in minutes', NOW()),

-- Default Values
('default_contribution', '1000', 'defaults', 'number', 'Default monthly contribution amount for new members', NOW()),
('default_currency', 'ETB', 'defaults', 'select', 'Default currency for the system', NOW()),
('default_language', 'en', 'defaults', 'select', 'Default language for new users', NOW()),
('auto_activate_members', '1', 'defaults', 'boolean', 'Automatically activate new member registrations', NOW()),

-- System Preferences
('date_format', 'm/d/Y', 'preferences', 'select', 'How dates are displayed throughout the system', NOW()),
('timezone', 'Africa/Addis_Ababa', 'preferences', 'select', 'System timezone for all date/time operations', NOW()),
('items_per_page', '25', 'preferences', 'select', 'Number of items to show per page in lists', NOW()),
('enable_notifications', '1', 'preferences', 'boolean', 'Enable system notifications for users', NOW()),

-- Email Configuration
('smtp_host', '', 'email', 'text', 'SMTP server hostname', NOW()),
('smtp_port', '587', 'email', 'number', 'SMTP server port (587 for TLS, 465 for SSL)', NOW()),
('from_email', '', 'email', 'text', 'Email address used as sender for system emails', NOW()),
('from_name', 'HabeshaEqub System', 'email', 'text', 'Name displayed as sender for system emails', NOW()),

-- Currency Settings
('currency_symbol', 'ETB', 'currency', 'text', 'Symbol to display for currency amounts', NOW()),
('currency_position', 'after', 'currency', 'select', 'Position of currency symbol relative to amount', NOW()),
('decimal_places', '2', 'currency', 'select', 'Number of decimal places to show for currency', NOW()),
('thousands_separator', ',', 'currency', 'select', 'Character used to separate thousands', NOW()); 