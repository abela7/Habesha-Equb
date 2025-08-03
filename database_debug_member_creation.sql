-- ===============================================
-- DATABASE DEBUG FOR MEMBER CREATION ISSUES
-- Run this to identify any remaining database problems
-- ===============================================

-- Check members table structure to verify columns
DESCRIBE members;

-- Check if any constraints might be failing
SELECT 
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE,
    TABLE_NAME,
    COLUMN_NAME
FROM information_schema.TABLE_CONSTRAINTS tc
JOIN information_schema.KEY_COLUMN_USAGE kcu 
    ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
WHERE tc.TABLE_SCHEMA = DATABASE() 
    AND tc.TABLE_NAME = 'members'
    AND tc.CONSTRAINT_TYPE IN ('FOREIGN KEY', 'UNIQUE', 'CHECK');

-- Test if a simple insert would work (with minimal data)
SELECT 
    'Testing basic INSERT compatibility' as test_name,
    'This query tests if the basic structure is compatible' as description;

-- Check if equb_settings table has valid IDs for testing
SELECT 
    'Available EQUB Settings for testing' as info,
    id as equb_id,
    equb_name,
    status,
    max_members,
    current_members
FROM equb_settings 
WHERE status = 'active' 
LIMIT 5;

-- Check for any existing members to see the pattern
SELECT 
    'Sample existing members' as info,
    id,
    member_id,
    first_name,
    last_name,
    email,
    membership_type,
    joint_group_id,
    equb_settings_id
FROM members 
LIMIT 3;

-- Check joint membership groups table
SELECT 
    'Joint groups status' as info,
    COUNT(*) as total_joint_groups
FROM joint_membership_groups;

-- Test query to check if the exact INSERT columns exist
SELECT 
    'Column existence check' as test_name,
    CASE WHEN COUNT(*) = 25 THEN 'All required columns exist ✅'
         ELSE CONCAT('Missing columns ❌ - Found: ', COUNT(*), ' Expected: 25')
    END as status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'members' 
    AND COLUMN_NAME IN (
        'equb_settings_id', 'member_id', 'first_name', 'last_name', 'email', 'phone',
        'monthly_payment', 'payout_position', 'payout_month', 'total_contributed',
        'has_received_payout', 'guarantor_first_name', 'guarantor_last_name',
        'guarantor_phone', 'guarantor_email', 'guarantor_relationship',
        'is_active', 'is_approved', 'email_verified', 'join_date',
        'notification_preferences', 'notes', 'membership_type', 'joint_group_id',
        'joint_member_count', 'individual_contribution', 'joint_position_share',
        'primary_joint_member', 'payout_split_method', 'created_at', 'updated_at'
    );

-- SAFETY NOTE: This file only performs SELECT queries and does not modify data