-- ===============================================
-- HABESHA EQUB - CRITICAL LOGICAL FIX
-- Adding Regular Payment Tier Concept
-- ===============================================

-- Add regular payment tier to equb_settings
ALTER TABLE `equb_settings` 
ADD COLUMN `regular_payment_tier` DECIMAL(10,2) NOT NULL DEFAULT 1000.00 COMMENT 'Base payment amount that determines position count' AFTER `payment_tiers`;

-- Add calculated positions (should equal duration_months)
ALTER TABLE `equb_settings` 
ADD COLUMN `calculated_positions` INT(3) NOT NULL DEFAULT 0 COMMENT 'Auto-calculated based on contributions and regular tier' AFTER `regular_payment_tier`;

-- Add member-friendly display amounts
ALTER TABLE `members`
ADD COLUMN `display_payout_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Member-friendly payout amount (hides monthly deduction)' AFTER `total_contributed`;

-- Add position coefficient for joint members (how many positions they represent)
ALTER TABLE `members`
ADD COLUMN `position_coefficient` DECIMAL(4,2) DEFAULT 1.00 COMMENT 'How many positions this member represents (0.5, 1.0, 1.5, 2.0, etc.)' AFTER `payout_position`;

-- Update joint_membership_groups with position coefficient
ALTER TABLE `joint_membership_groups`
ADD COLUMN `position_coefficient` DECIMAL(4,2) DEFAULT 1.00 COMMENT 'How many positions this joint group represents' AFTER `payout_position`;

-- Fix the current Selam Equb with regular tier
UPDATE `equb_settings` 
SET 
    `regular_payment_tier` = 1000.00,
    `calculated_positions` = 10,
    `duration_months` = 10,
    `total_pool_amount` = 100000.00
WHERE `id` = 2;

-- Fix Michael + Koki joint group (should be 2 positions, not 1)
UPDATE `joint_membership_groups` 
SET 
    `position_coefficient` = 2.00,
    `total_monthly_payment` = 2000.00
WHERE `joint_group_id` = 'JNT-2025-002-115';

-- Update Michael's position coefficient (he represents 1.5 positions out of 2)
UPDATE `members` 
SET `position_coefficient` = 1.50 
WHERE `id` = 14; -- Michael

-- Update Koki's position coefficient (she represents 0.5 positions out of 2) 
UPDATE `members` 
SET `position_coefficient` = 0.50 
WHERE `id` = 11; -- Koki

-- Update other joint group members for consistency
UPDATE `members` 
SET `position_coefficient` = 0.50 
WHERE `joint_group_id` = 'JNT-2025-002-902'; -- Eldana & Sosina

-- Update all individual members
UPDATE `members` 
SET `position_coefficient` = 1.00 
WHERE `membership_type` = 'individual';

-- ===============================================
-- VERIFICATION QUERIES (Run these to check)
-- ===============================================

-- Check total position coefficients (should equal calculated_positions)
-- SELECT SUM(position_coefficient) as total_positions FROM members WHERE equb_settings_id = 2 AND is_active = 1;

-- Check member distribution
-- SELECT first_name, last_name, monthly_payment, individual_contribution, position_coefficient, membership_type 
-- FROM members WHERE equb_settings_id = 2 AND is_active = 1 ORDER BY payout_position;