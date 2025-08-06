-- Fix Member Positions for 10-Month EQUB
-- Current issue: Members are in positions 1-9, but EQUB runs for 10 months

-- Current position summary:
-- Position 1: Abel (individual)
-- Position 2: Maruf (individual) 
-- Position 3: Samson (individual)
-- Position 4: Barnabas (individual)
-- Position 5: Elias (individual)
-- Position 6: Michael + Koki (joint group)
-- Position 7: Biniam (individual)
-- Position 8: Sabella (individual)
-- Position 9: Eldana + Sosina (joint group)
-- Position 10: MISSING!

-- SOLUTION: Move one member to position 10
-- Let's move Sabella from position 8 to position 10

UPDATE members SET payout_position = 10 WHERE id = 8; -- Sabella

-- Update joint group positions to match
UPDATE joint_membership_groups 
SET payout_position = (
    SELECT MIN(m.payout_position) 
    FROM members m 
    WHERE m.joint_group_id = joint_membership_groups.joint_group_id AND m.is_active = 1
)
WHERE equb_settings_id = 2;

-- Verify the positions
SELECT 
    payout_position,
    first_name,
    last_name,
    membership_type,
    position_coefficient,
    CASE 
        WHEN membership_type = 'joint' THEN individual_contribution
        ELSE monthly_payment
    END as contribution
FROM members 
WHERE equb_settings_id = 2 AND is_active = 1
ORDER BY payout_position;