-- ========================================
-- HABESHAEQUB - MEMBER MESSAGE SYSTEM (SOCIAL MEDIA STYLE)
-- This will NOT conflict with existing email/SMS notifications table
-- Uses different table names: member_messages, member_message_reads
-- ========================================

-- Member Messages table (like social media posts/announcements)
CREATE TABLE IF NOT EXISTS `member_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `message_id` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique message ID (e.g., MSG-2025-001)',
    
    -- Content in both languages
    `title_en` VARCHAR(255) NOT NULL COMMENT 'Message title in English',
    `title_am` VARCHAR(255) NOT NULL COMMENT 'Message title in Amharic',
    `content_en` TEXT NOT NULL COMMENT 'Message content in English',
    `content_am` TEXT NOT NULL COMMENT 'Message content in Amharic',
    
    -- Message metadata
    `message_type` ENUM('general', 'payment_reminder', 'payout_announcement', 'system_update', 'announcement') DEFAULT 'general',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `target_audience` ENUM('all_members', 'active_members', 'specific_equb', 'individual') DEFAULT 'all_members',
    `equb_settings_id` INT(11) NULL COMMENT 'Target specific EQUB term (optional)',
    `target_member_id` INT(11) NULL COMMENT 'Target specific member (optional)',
    
    -- Admin details
    `created_by_admin_id` INT(11) NOT NULL,
    `created_by_admin_name` VARCHAR(100) NOT NULL,
    
    -- Status and timing
    `status` ENUM('draft', 'active', 'expired', 'deleted') DEFAULT 'active',
    `scheduled_date` DATETIME NULL COMMENT 'Schedule message for future (optional)',
    `expires_at` DATETIME NULL COMMENT 'Auto-expire message (optional)',
    
    -- Engagement metrics
    `total_recipients` INT(11) DEFAULT 0 COMMENT 'Total members who should receive this',
    `total_read` INT(11) DEFAULT 0 COMMENT 'Total members who have read this',
    `total_unread` INT(11) DEFAULT 0 COMMENT 'Total members who have not read this',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    INDEX `idx_message_id` (`message_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`message_type`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_created_by` (`created_by_admin_id`),
    INDEX `idx_target_equb` (`equb_settings_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Member Message read status tracking table
CREATE TABLE IF NOT EXISTS `member_message_reads` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `message_id` INT(11) NOT NULL,
    `member_id` INT(11) NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0 COMMENT '0 = unread, 1 = read',
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_message_member` (`message_id`, `member_id`),
    INDEX `idx_message` (`message_id`),
    INDEX `idx_member` (`member_id`),
    INDEX `idx_is_read` (`is_read`),
    
    FOREIGN KEY (`message_id`) REFERENCES `member_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample messages for testing
INSERT INTO `member_messages` (
    `message_id`, 
    `title_en`, 
    `title_am`, 
    `content_en`, 
    `content_am`, 
    `message_type`, 
    `priority`, 
    `target_audience`, 
    `created_by_admin_id`, 
    `created_by_admin_name`,
    `total_recipients`
) VALUES (
    'MSG-2025-001',
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

-- Insert additional sample messages
INSERT INTO `member_messages` (
    `message_id`, 
    `title_en`, 
    `title_am`, 
    `content_en`, 
    `content_am`, 
    `message_type`, 
    `priority`, 
    `target_audience`, 
    `created_by_admin_id`, 
    `created_by_admin_name`
) VALUES 
(
    'MSG-2025-002',
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
    'MSG-2025-003',
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

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_member_messages_status_type` ON `member_messages` (`status`, `message_type`);
CREATE INDEX IF NOT EXISTS `idx_member_message_reads_unread` ON `member_message_reads` (`member_id`, `is_read`);

-- Create stored procedure for auto-assignment
DELIMITER //
CREATE PROCEDURE CreateMemberMessageForMembers(
    IN message_id INT,
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
        
        INSERT IGNORE INTO member_message_reads (message_id, member_id, is_read)
        VALUES (message_id, member_id, 0);
    END LOOP;
    CLOSE member_cursor;
    
    -- Update total recipients count
    UPDATE member_messages 
    SET total_recipients = (
        SELECT COUNT(*) FROM member_message_reads WHERE member_message_reads.message_id = message_id
    ),
    total_unread = (
        SELECT COUNT(*) FROM member_message_reads WHERE member_message_reads.message_id = message_id AND is_read = 0
    )
    WHERE id = message_id;
END//
DELIMITER ;

-- Create sample message reads for existing members (if any)
INSERT IGNORE INTO member_message_reads (message_id, member_id, is_read)
SELECT mm.id, m.id, 0
FROM member_messages mm
CROSS JOIN members m
WHERE m.is_active = 1 AND mm.target_audience = 'all_members';

-- Update recipient counts
UPDATE member_messages mm
SET total_recipients = (
    SELECT COUNT(*) FROM member_message_reads mmr WHERE mmr.message_id = mm.id
),
total_unread = (
    SELECT COUNT(*) FROM member_message_reads mmr WHERE mmr.message_id = mm.id AND mmr.is_read = 0
),
total_read = (
    SELECT COUNT(*) FROM member_message_reads mmr WHERE mmr.message_id = mm.id AND mmr.is_read = 1
);

-- Verify the tables were created successfully
SELECT 'Member Message System created successfully!' as status;
SELECT COUNT(*) as total_member_messages FROM member_messages;
SELECT COUNT(*) as total_read_records FROM member_message_reads;

-- Show table structure for verification
DESCRIBE member_messages;