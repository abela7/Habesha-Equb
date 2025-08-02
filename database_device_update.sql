-- Add device tracking columns for 7-day remember functionality
-- Run this SQL in phpMyAdmin to update your database

-- Add device_token column for secure device identification
ALTER TABLE `device_tracking` 
ADD COLUMN `device_token` VARCHAR(64) NULL AFTER `device_fingerprint`;

-- Add expires_at column for token expiration
ALTER TABLE `device_tracking` 
ADD COLUMN `expires_at` TIMESTAMP NULL AFTER `device_token`;

-- Add unique index on device_token for security
ALTER TABLE `device_tracking` 
ADD UNIQUE INDEX `device_token_unique` (`device_token`);

-- Add index on expires_at for efficient cleanup queries
ALTER TABLE `device_tracking` 
ADD INDEX `expires_at_index` (`expires_at`);

-- Optional: Add composite index for email and device_fingerprint lookups
ALTER TABLE `device_tracking` 
ADD UNIQUE INDEX `email_device_unique` (`email`, `device_fingerprint`);

-- Clean up expired device tokens (optional maintenance query)
-- DELETE FROM device_tracking WHERE expires_at < NOW() AND expires_at IS NOT NULL;