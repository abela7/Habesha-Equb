-- ========================================
-- NOTIFICATION SYSTEM REMOVAL SQL SCRIPT
-- Copy and paste this into phpMyAdmin
-- ========================================

-- Remove tables (foreign keys first)
DROP TABLE IF EXISTS `member_message_reads`;
DROP TABLE IF EXISTS `member_messages`;

-- Remove stored procedure
DROP PROCEDURE IF EXISTS `CreateMemberMessageForMembers`;

-- ========================================
-- CLEANUP COMPLETED
-- ========================================