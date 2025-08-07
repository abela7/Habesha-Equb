-- Notifications System Schema

-- Table: notifications
CREATE TABLE IF NOT EXISTS program_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_code VARCHAR(32) NOT NULL UNIQUE,
  created_by_admin_id INT NULL,
  audience_type ENUM('all','equb','members') NOT NULL DEFAULT 'all',
  equb_settings_id INT NULL,
  title_en VARCHAR(255) NOT NULL,
  title_am VARCHAR(255) NOT NULL,
  body_en TEXT NOT NULL,
  body_am TEXT NOT NULL,
  priority ENUM('normal','high') NOT NULL DEFAULT 'normal',
  status ENUM('draft','sent') NOT NULL DEFAULT 'sent',
  scheduled_at DATETIME NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_program_notifications_equb (equb_settings_id),
  CONSTRAINT fk_program_notifications_admin FOREIGN KEY (created_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL,
  CONSTRAINT fk_program_notifications_equb FOREIGN KEY (equb_settings_id) REFERENCES equb_settings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notification_recipients
CREATE TABLE IF NOT EXISTS notification_recipients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NOT NULL,
  member_id INT NOT NULL,
  read_flag TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,
  delivered_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_notification_member (notification_id, member_id),
  INDEX idx_recipient_member (member_id),
  CONSTRAINT fk_recipient_program_notification FOREIGN KEY (notification_id) REFERENCES program_notifications(id) ON DELETE CASCADE,
  CONSTRAINT fk_recipient_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
