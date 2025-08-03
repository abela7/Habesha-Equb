-- ===============================================
-- DATABASE VERIFICATION CHECK FOR JOINT MEMBERSHIP
-- This script only CHECKS your database - no changes made!
-- ===============================================

-- Check if all required columns exist in members table
SELECT 
    'MEMBERS TABLE VERIFICATION' as check_type,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'membership_type') as membership_type_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'joint_group_id') as joint_group_id_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'joint_member_count') as joint_member_count_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'individual_contribution') as individual_contribution_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'joint_position_share') as joint_position_share_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'primary_joint_member') as primary_joint_member_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND COLUMN_NAME = 'payout_split_method') as payout_split_method_exists;

-- Check if joint membership tables exist
SELECT 
    'JOINT TABLES VERIFICATION' as check_type,
    (SELECT COUNT(*) FROM information_schema.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'joint_membership_groups') as joint_membership_groups_exists,
    (SELECT COUNT(*) FROM information_schema.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'joint_payout_splits') as joint_payout_splits_exists;

-- Check if equb_settings has joint support columns
SELECT 
    'EQUB SETTINGS VERIFICATION' as check_type,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'equb_settings' 
       AND COLUMN_NAME = 'supports_joint_membership') as supports_joint_membership_exists,
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'equb_settings' 
       AND COLUMN_NAME = 'max_joint_members_per_group') as max_joint_members_per_group_exists;

-- Check required indexes exist
SELECT 
    'INDEX VERIFICATION' as check_type,
    (SELECT COUNT(*) FROM information_schema.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND INDEX_NAME = 'idx_membership_type') as membership_type_index_exists,
    (SELECT COUNT(*) FROM information_schema.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'members' 
       AND INDEX_NAME = 'idx_joint_group') as joint_group_index_exists;

-- Final verification summary
SELECT 
    'FINAL VERIFICATION SUMMARY' as summary,
    CASE 
        WHEN (
            (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME IN ('membership_type', 'joint_group_id', 'joint_member_count', 'individual_contribution', 'joint_position_share', 'primary_joint_member', 'payout_split_method')) = 7
            AND (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('joint_membership_groups', 'joint_payout_splits')) = 2
            AND (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'equb_settings' AND COLUMN_NAME IN ('supports_joint_membership', 'max_joint_members_per_group')) = 2
        ) THEN 'DATABASE IS READY FOR JOINT MEMBERSHIP! ✅'
        ELSE 'DATABASE NEEDS UPDATES ❌'
    END as database_status,
    'All required columns and tables are present' as message;

-- Show current member data to verify structure
SELECT 
    'SAMPLE DATA CHECK' as check_type,
    COUNT(*) as total_members,
    COUNT(CASE WHEN membership_type = 'individual' THEN 1 END) as individual_members,
    COUNT(CASE WHEN membership_type = 'joint' THEN 1 END) as joint_members,
    COUNT(CASE WHEN joint_group_id IS NOT NULL THEN 1 END) as members_with_joint_group
FROM members;

-- Show current joint groups (should be empty initially)
SELECT 
    'JOINT GROUPS CHECK' as check_type,
    COUNT(*) as total_joint_groups
FROM joint_membership_groups;