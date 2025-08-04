-- ========================================
-- DATABASE FIX: Correct Joint Group Position Logic
-- ========================================
-- This script fixes the critical logical errors in joint group positioning
-- From your database: Joint group JNT-2025-002-902 should share position 9

-- PROBLEM IDENTIFIED:
-- Joint group 'JNT-2025-002-902' has payout_position = 9
-- But members 16 (Eldana) and 18 (Sosina) have positions 5 and 6
-- This creates 11 positions for a 10-month EQUB!

-- SOLUTION:
-- Both joint members should have the same position as their group (position 9)

-- ========================================
-- STEP 1: Fix Member Positions to Match Joint Group
-- ========================================

-- Update joint group members to use their group's position
UPDATE members m 
JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id 
SET m.payout_position = jmg.payout_position 
WHERE m.membership_type = 'joint' AND m.is_active = 1;

-- Specific fix for current data:
-- Members 16 (Eldana) and 18 (Sosina) should both have position 9
UPDATE members 
SET payout_position = 9 
WHERE id IN (16, 18) AND joint_group_id = 'JNT-2025-002-902';

-- ========================================
-- STEP 2: Fix Monthly Payment Logic
-- ========================================

-- Joint members should reflect the group's total payment for calculations
UPDATE members m 
JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id 
SET m.monthly_payment = jmg.total_monthly_payment 
WHERE m.membership_type = 'joint' AND m.is_active = 1;

-- ========================================
-- STEP 3: Verify Current EQUB Statistics
-- ========================================

-- Check current statistics after fix
SELECT 
    'Individual Positions' as type,
    COUNT(*) as count
FROM members 
WHERE membership_type = 'individual' AND is_active = 1

UNION ALL

SELECT 
    'Joint Positions' as type,
    COUNT(DISTINCT joint_group_id) as count
FROM members 
WHERE membership_type = 'joint' AND is_active = 1

UNION ALL

SELECT 
    'Total Positions' as type,
    (
        SELECT COUNT(*) FROM members WHERE membership_type = 'individual' AND is_active = 1
    ) + (
        SELECT COUNT(DISTINCT joint_group_id) FROM members WHERE membership_type = 'joint' AND is_active = 1
    ) as count

UNION ALL

SELECT 
    'Total People' as type,
    COUNT(*) as count
FROM members 
WHERE is_active = 1;

-- ========================================
-- STEP 4: Position Verification Query
-- ========================================

-- This shows the CORRECT position structure after fix
SELECT 
    CASE 
        WHEN m.membership_type = 'joint' THEN CONCAT('Joint Group: ', COALESCE(jmg.group_name, jmg.joint_group_id))
        ELSE CONCAT('Individual: ', m.first_name, ' ', m.last_name)
    END as position_holder,
    CASE 
        WHEN m.membership_type = 'joint' THEN jmg.payout_position
        ELSE m.payout_position
    END as shared_position,
    CASE 
        WHEN m.membership_type = 'joint' THEN jmg.total_monthly_payment
        ELSE m.monthly_payment
    END as monthly_payment,
    CASE 
        WHEN m.membership_type = 'joint' THEN GROUP_CONCAT(CONCAT(m.first_name, ' ', m.last_name) ORDER BY m.primary_joint_member DESC SEPARATOR ' & ')
        ELSE CONCAT(m.first_name, ' ', m.last_name)
    END as members_in_position
FROM members m
LEFT JOIN joint_membership_groups jmg ON m.joint_group_id = jmg.joint_group_id
WHERE m.is_active = 1
GROUP BY 
    CASE 
        WHEN m.membership_type = 'joint' THEN m.joint_group_id
        ELSE m.id
    END
ORDER BY 
    CASE 
        WHEN m.membership_type = 'joint' THEN jmg.payout_position
        ELSE m.payout_position
    END ASC;

-- ========================================
-- EXPECTED RESULT FOR YOUR EQUB:
-- ========================================
-- Position 1: Abel Demssie (£1000/month)
-- Position 2: Maruf Nasir (£1000/month)  
-- Position 3: Samuel Girma (£1000/month)
-- Position 4: Barnabas Dagnachew (£1000/month)
-- Position 7: Biniam Tsegaye (£1000/month)
-- Position 8: ELIAS FRIEW (£1000/month)
-- Position 9: Joint Group: Eldana & Sosina (£1000/month total)
-- Position 10: Michael Werkeneh (£1500/month)
-- Position 11: Sabella Fisseha (£1000/month)
-- 
-- TOTAL: 9 positions for 10 people (correct for traditional EQUB logic)