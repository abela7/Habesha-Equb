-- ===============================================
-- SIMPLE MEMBER CREATION DIAGNOSTIC TEST
-- Run these queries one by one to identify the issue
-- ===============================================

-- 1. Check members table structure
DESCRIBE members;

-- 2. Count existing members
SELECT 'Current members count' as info, COUNT(*) as total FROM members;

-- 3. Check available EQUB settings
SELECT 
    'Available EQUB for testing' as info,
    id, equb_name, status, current_members, max_members
FROM equb_settings 
WHERE status = 'active';

-- 4. Test the exact INSERT that's failing (with sample data)
-- IMPORTANT: Change the email to something unique before running!
INSERT INTO members (
    equb_settings_id, member_id, first_name, last_name, email, phone, 
    monthly_payment, payout_position, payout_month, total_contributed, 
    has_received_payout, guarantor_first_name, guarantor_last_name, 
    guarantor_phone, guarantor_email, guarantor_relationship, 
    is_active, is_approved, email_verified, join_date, 
    notification_preferences, notes, membership_type, joint_group_id,
    joint_member_count, individual_contribution, joint_position_share,
    primary_joint_member, payout_split_method, created_at, updated_at
) VALUES (
    2, 'TEST-123', 'Test', 'User', 'test.unique.email@example.com', '1234567890',
    1000.00, 99, NULL, 0, 0, 'Test', 'Guarantor', 
    '0987654321', 'guarantor@test.com', 'Friend', 
    1, 1, 0, CURDATE(), 'email,sms', 'Test member', 'individual', NULL,
    1, NULL, 1.0000, 1, 'equal', NOW(), NOW()
);

-- 5. If the INSERT worked, clean up the test data
DELETE FROM members WHERE member_id = 'TEST-123';

-- 6. Check if all required columns exist
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'members'
    AND COLUMN_NAME IN (
        'membership_type', 'joint_group_id', 'joint_member_count',
        'individual_contribution', 'joint_position_share', 
        'primary_joint_member', 'payout_split_method'
    )
ORDER BY COLUMN_NAME;