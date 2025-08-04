-- ========================================
-- HABESHAEQUB - ADVANCED NOTIFICATION SYSTEM
-- Top-tier notification database structure with multilingual support
-- ========================================

-- Main notifications table with multilingual content
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `notification_id` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique notification ID (e.g., NOTIF-2025-001)',
    
    -- Content in both languages
    `title_en` VARCHAR(255) NOT NULL COMMENT 'Notification title in English',
    `title_am` VARCHAR(255) NOT NULL COMMENT 'Notification title in Amharic',
    `content_en` TEXT NOT NULL COMMENT 'Notification content in English',
    `content_am` TEXT NOT NULL COMMENT 'Notification content in Amharic',
    
    -- Notification metadata
    `notification_type` ENUM('general', 'payment_reminder', 'payout_announcement', 'system_update', 'announcement') DEFAULT 'general',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `target_audience` ENUM('all_members', 'active_members', 'specific_equb', 'individual') DEFAULT 'all_members',
    `equb_settings_id` INT(11) NULL COMMENT 'Target specific EQUB term (optional)',
    `target_member_id` INT(11) NULL COMMENT 'Target specific member (optional)',
    
    -- Admin details
    `created_by_admin_id` INT(11) NOT NULL,
    `created_by_admin_name` VARCHAR(100) NOT NULL,
    
    -- Status and timing
    `status` ENUM('draft', 'active', 'expired', 'deleted') DEFAULT 'active',
    `scheduled_date` DATETIME NULL COMMENT 'Schedule notification for future (optional)',
    `expires_at` DATETIME NULL COMMENT 'Auto-expire notification (optional)',
    
    -- Engagement metrics
    `total_recipients` INT(11) DEFAULT 0 COMMENT 'Total members who should receive this',
    `total_read` INT(11) DEFAULT 0 COMMENT 'Total members who have read this',
    `total_unread` INT(11) DEFAULT 0 COMMENT 'Total members who have not read this',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    INDEX `idx_notification_id` (`notification_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`notification_type`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_created_by` (`created_by_admin_id`),
    INDEX `idx_target_equb` (`equb_settings_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification read status tracking table
CREATE TABLE IF NOT EXISTS `notification_reads` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `notification_id` INT(11) NOT NULL,
    `member_id` INT(11) NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_notification_member` (`notification_id`, `member_id`),
    INDEX `idx_notification` (`notification_id`),
    INDEX `idx_member` (`member_id`),
    INDEX `idx_is_read` (`is_read`),
    
    FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample notification for testing
INSERT INTO `notifications` (
    `notification_id`, 
    `title_en`, 
    `title_am`, 
    `content_en`, 
    `content_am`, 
    `notification_type`, 
    `priority`, 
    `target_audience`, 
    `created_by_admin_id`, 
    `created_by_admin_name`,
    `total_recipients`
) VALUES (
    'NOTIF-2025-001',
    'Welcome to HabeshaEqub!',
    'እንኳን ወደ ሐበሻ ዕቁብ በደህና መጡ!',
    'Welcome to our modern EQUB system! We are excited to have you as part of our financial community. Please make sure to check your dashboard regularly for updates.',
    'ወደ ዘመናዊ የዕቁብ ሥርዓታችን እንኳን በደህና መጡ! በእኛ የገንዘብ ማህበረሰብ አባል መሆንዎን በተመለከተ ተደስተናል። እባክዎ ማሻሻያዎችን ለማግኘት የእርስዎን ዳሽቦርድ በቀጣይነት ይመልከቱ።',
    'announcement',
    'high',
    'all_members',
    1,
    'Admin',
    0
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_notifications_status_type` ON `notifications` (`status`, `notification_type`);
CREATE INDEX IF NOT EXISTS `idx_notification_reads_unread` ON `notification_reads` (`member_id`, `is_read`);

-- Create view for notification statistics
CREATE OR REPLACE VIEW `notification_stats` AS
SELECT 
    n.id,
    n.notification_id,
    n.title_en,
    n.title_am,
    n.notification_type,
    n.priority,
    n.status,
    n.created_at,
    n.total_recipients,
    COUNT(nr.id) as total_delivered,
    SUM(CASE WHEN nr.is_read = 1 THEN 1 ELSE 0 END) as total_read,
    SUM(CASE WHEN nr.is_read = 0 THEN 1 ELSE 0 END) as total_unread,
    ROUND((SUM(CASE WHEN nr.is_read = 1 THEN 1 ELSE 0 END) / COUNT(nr.id)) * 100, 2) as read_percentage
FROM notifications n
LEFT JOIN notification_reads nr ON n.id = nr.notification_id
WHERE n.status = 'active'
GROUP BY n.id
ORDER BY n.created_at DESC;

-- ========================================
-- NOTIFICATION FUNCTIONS AND PROCEDURES
-- ========================================

-- Function to automatically create notification read records for all eligible members
DELIMITER //
CREATE OR REPLACE PROCEDURE CreateNotificationForMembers(
    IN notification_id INT,
    IN target_audience VARCHAR(20),
    IN equb_settings_id INT,
    IN target_member_id INT
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE member_id INT;
    DECLARE member_cursor CURSOR FOR
        SELECT m.id 
        FROM members m 
        WHERE m.is_active = 1 
        AND (
            (target_audience = 'all_members') OR
            (target_audience = 'active_members' AND m.is_active = 1) OR
            (target_audience = 'specific_equb' AND m.equb_settings_id = equb_settings_id) OR
            (target_audience = 'individual' AND m.id = target_member_id)
        );
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN member_cursor;
    read_loop: LOOP
        FETCH member_cursor INTO member_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT IGNORE INTO notification_reads (notification_id, member_id, is_read)
        VALUES (notification_id, member_id, 0);
    END LOOP;
    CLOSE member_cursor;
    
    -- Update total recipients count
    UPDATE notifications 
    SET total_recipients = (
        SELECT COUNT(*) FROM notification_reads WHERE notification_reads.notification_id = notification_id
    ),
    total_unread = (
        SELECT COUNT(*) FROM notification_reads WHERE notification_reads.notification_id = notification_id AND is_read = 0
    )
    WHERE id = notification_id;
END//
DELIMITER ;

-- ========================================
-- SAMPLE DATA FOR TESTING
-- ========================================

-- Insert additional sample notifications
INSERT INTO `notifications` (
    `notification_id`, 
    `title_en`, 
    `title_am`, 
    `content_en`, 
    `content_am`, 
    `notification_type`, 
    `priority`, 
    `target_audience`, 
    `created_by_admin_id`, 
    `created_by_admin_name`
) VALUES 
(
    'NOTIF-2025-002',
    'Monthly Payment Reminder',
    'የወርሃዊ ክፍያ ማስታወሻ',
    'This is a friendly reminder that your monthly EQUB contribution is due soon. Please make sure to submit your payment by the due date to avoid late fees.',
    'ይህ የወርሃዊ የዕቁብ አበርክቶዎ በቅርቡ መድረስ እንዳለበት የሚያሳስብ ወዳጃዊ ማስታወሻ ነው። የዘገየ ክፍያ ቅጣት ለማስወገድ በመጨረሻ ቀን ክፍያዎን ማቅረብዎን ያረጋግጡ።',
    'payment_reminder',
    'medium',
    'all_members',
    1,
    'Admin'
),
(
    'NOTIF-2025-003',
    'System Maintenance Notice',
    'የሥርዓት ጥገና ማሳወቂያ',
    'We will be performing scheduled maintenance on our system this weekend. The platform may be temporarily unavailable during this time.',
    'በዚህ ሳምንት መጨረሻ በሥርዓታችን ላይ የታቀደ ጥገና እናከናውናለን። በዚህ ጊዜ መድረኩ ለጊዜው አይገኝም ሊሆን ይችላል።',
    'system_update',
    'high',
    'all_members',
    1,
    'Admin'
);

-- Show table structure and sample data
SELECT 'Notifications table created successfully!' as status;
SELECT COUNT(*) as total_notifications FROM notifications;
SELECT COUNT(*) as total_read_records FROM notification_reads;