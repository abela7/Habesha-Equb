-- DATABASE UPDATE: Enhanced Payout Structure
-- Add gross_payout column to properly track the full calculation
-- This ensures we store: gross_payout, total_amount (gross-admin), net_amount (gross-admin-monthly)

ALTER TABLE `payouts` 
ADD COLUMN `gross_payout` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Full payout amount from coefficient calculation (Position Coefficient × Monthly Pool)' 
AFTER `member_id`;

-- Update existing records to populate gross_payout based on current data
-- Assuming current total_amount was the gross payout in old system
UPDATE `payouts` 
SET `gross_payout` = `total_amount` + `admin_fee` 
WHERE `gross_payout` = 0.00;

-- Update column comments for clarity
ALTER TABLE `payouts` 
MODIFY COLUMN `total_amount` decimal(12,2) NOT NULL COMMENT 'Gross payout minus admin fee (what member sees as their entitlement)',
MODIFY COLUMN `net_amount` decimal(12,2) NOT NULL COMMENT 'Final amount member receives (gross - admin fee - monthly contribution)',
MODIFY COLUMN `admin_fee` decimal(8,2) DEFAULT 0.00 COMMENT 'Admin service fee (deducted from gross payout)';

-- Verify the structure
DESCRIBE `payouts`;