-- SMS Templates Table
-- Allows admins to save reusable SMS templates for quick sending

CREATE TABLE IF NOT EXISTS `sms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL COMMENT 'Template name (e.g., Payment Reminder, Welcome Message)',
  `title_en` varchar(200) NOT NULL COMMENT 'English title',
  `title_am` varchar(200) NOT NULL COMMENT 'Amharic title',
  `body_en` text NOT NULL COMMENT 'English message body',
  `body_am` text NOT NULL COMMENT 'Amharic message body',
  `category` varchar(50) DEFAULT 'general' COMMENT 'Template category (payment, welcome, reminder, etc.)',
  `created_by_admin_id` int(11) NOT NULL COMMENT 'Admin who created this template',
  `usage_count` int(11) DEFAULT 0 COMMENT 'How many times this template was used',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Archived',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `idx_created_by` (`created_by_admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample templates (optional - can be added via UI)
INSERT INTO `sms_templates` (`template_name`, `title_en`, `title_am`, `body_en`, `body_am`, `category`, `created_by_admin_id`) VALUES
('Payment Reminder', 'Payment Due', 'ክፍያ የሚጠይቅ', 'Hi {first_name}, your payment of Birr {amount} is due. Please pay by {due_date}. View: habeshaequb.com/pay', 'ሰላም {first_name}፣ የ {amount} ብር ክፍያ የሚጠይቅ። እባክዎን በ {due_date} ድረስ ይክፈሉ። ይመልከቱ: habeshaequb.com/pay', 'payment', 1),
('Welcome Message', 'Welcome to HabeshaEqub', 'እንኳን ደህና መጡ ወደ HabeshaEqub', 'Welcome {first_name}! Your account is ready. View dashboard: habeshaequb.com/dashboard', 'እንኳን ደህና መጡ {first_name}! አካውንትዎ ዝግጁ ነው። ዳሽቦርድ ይመልከቱ: habeshaequb.com/dashboard', 'welcome', 1);

