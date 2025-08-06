-- CORRECT POSITION ASSIGNMENT LOGIC
-- Based on position coefficients, not member count!

-- Current position coefficients from database:
-- Abel: 1.0 (individual) → Position 1
-- Maruf: 1.0 (individual) → Position 2 
-- Samson: 1.0 (individual) → Position 3
-- Barnabas: 1.0 (individual) → Position 4
-- Elias: 1.0 (individual) → Position 5
-- Biniam: 1.0 (individual) → Position 6
-- Sabella: 1.0 (individual) → Position 7
-- Sosina: 0.5 + Eldana: 0.5 = 1.0 → Position 8 (joint position)
-- Michael: 1.5 = 1.0 + 0.5 → Position 9 (his main position) + Position 10 (his extra + Koki's 0.5)

-- SOLUTION: Reassign positions correctly

-- Individual members (positions 1-7)
UPDATE members SET payout_position = 1 WHERE id = 7;  -- Abel
UPDATE members SET payout_position = 2 WHERE id = 13; -- Maruf  
UPDATE members SET payout_position = 3 WHERE id = 20; -- Samson
UPDATE members SET payout_position = 4 WHERE id = 10; -- Barnabas
UPDATE members SET payout_position = 5 WHERE id = 17; -- Elias
UPDATE members SET payout_position = 6 WHERE id = 12; -- Biniam
UPDATE members SET payout_position = 7 WHERE id = 8;  -- Sabella

-- Joint group 1: Sosina + Eldana (0.5 + 0.5 = 1.0) → Position 8
UPDATE members SET payout_position = 8 WHERE id IN (16, 18); -- Eldana, Sosina

-- Michael's positions: 
-- Position 9: Michael's main 1.0 coefficient  
-- Position 10: Michael's extra 0.5 + Koki's 0.5 = 1.0
UPDATE members SET payout_position = 9 WHERE id = 14;  -- Michael (main position)
UPDATE members SET payout_position = 10 WHERE id = 11; -- Koki (shared with Michael's extra)

-- Update joint group positions to match member positions
UPDATE joint_membership_groups 
SET payout_position = 8 
WHERE joint_group_id = 'JNT-2025-002-902'; -- Eldana & Sosina

UPDATE joint_membership_groups 
SET payout_position = 9 
WHERE joint_group_id = 'JNT-2025-002-115'; -- Michael & Koki (Michael's main position)

-- Verify the result
SELECT 
    m.payout_position,
    m.first_name,
    m.last_name,
    m.membership_type,
    m.position_coefficient,
    CASE 
        WHEN m.membership_type = 'joint' THEN m.individual_contribution
        ELSE m.monthly_payment
    END as contribution,
    m.joint_group_id
FROM members m
WHERE m.equb_settings_id = 2 AND m.is_active = 1
ORDER BY m.payout_position, m.id;

-- Expected result: 10 positions exactly (1-10)
-- Position 8: Sosina + Eldana (shared)
-- Position 9: Michael (main) 
-- Position 10: Koki (+ Michael's extra coefficient)