-- HabeshaEqub Position Swap System Database
-- Full SQL script to facilitate position swap requests

-- Create position_swap_requests table
CREATE TABLE IF NOT EXISTS `position_swap_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`),
  KEY `member_id` (`member_id`),
  KEY `target_member_id` (`target_member_id`),
  KEY `status` (`status`),
  KEY `requested_date` (`requested_date`),
  KEY `current_position` (`current_position`),
  KEY `requested_position` (`requested_position`),
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create swap_history table for audit trail
CREATE TABLE IF NOT EXISTS `position_swap_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `swap_request_id` (`swap_request_id`),
  KEY `member_a_id` (`member_a_id`),
  KEY `member_b_id` (`member_b_id`),
  KEY `swap_date` (`swap_date`),
  FOREIGN KEY (`swap_request_id`) REFERENCES `position_swap_requests` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_a_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_b_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add swap-related columns to members table (if not exists)
-- Note: swap_terms_allowed already exists in members table, so we only add the new tracking columns
ALTER TABLE `members` 
ADD COLUMN IF NOT EXISTS `total_swaps_requested` int(11) DEFAULT 0 COMMENT 'Total swap requests made by member',
ADD COLUMN IF NOT EXISTS `total_swaps_completed` int(11) DEFAULT 0 COMMENT 'Total successful swaps for member',
ADD COLUMN IF NOT EXISTS `last_swap_date` timestamp NULL DEFAULT NULL COMMENT 'Date of last completed swap',
ADD COLUMN IF NOT EXISTS `swap_cooldown_until` timestamp NULL DEFAULT NULL COMMENT 'Member cannot request swaps until this date';

-- Add swap management permissions to admins table (if not exists)
ALTER TABLE `admins`
ADD COLUMN IF NOT EXISTS `can_manage_swaps` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Permission to manage position swaps';

-- Note: The system is now ready for production use!
-- No sample data inserted - tables are clean and ready for real swap requests.

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_swap_requests_member_status` ON `position_swap_requests` (`member_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_swap_requests_target_status` ON `position_swap_requests` (`target_member_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_swap_requests_positions` ON `position_swap_requests` (`current_position`, `requested_position`);
CREATE INDEX IF NOT EXISTS `idx_swap_history_members` ON `position_swap_history` (`member_a_id`, `member_b_id`);

-- Create triggers for automatic request ID generation
DELIMITER $$

DROP TRIGGER IF EXISTS `generate_swap_request_id`$$
CREATE TRIGGER `generate_swap_request_id` 
BEFORE INSERT ON `position_swap_requests`
FOR EACH ROW 
BEGIN
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
END$$

DELIMITER ;

-- Add table comments for documentation
ALTER TABLE `position_swap_requests` COMMENT = 'Stores all position swap requests from members';
ALTER TABLE `position_swap_history` COMMENT = 'Audit trail for completed position swaps';

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE ON position_swap_requests TO 'your_app_user'@'localhost';
-- GRANT SELECT, INSERT ON position_swap_history TO 'your_app_user'@'localhost';

-- Success message
SELECT 'Position Swap System database tables created successfully!' as message;
