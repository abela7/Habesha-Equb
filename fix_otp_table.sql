-- FIX TIME BUG IN OTP SYSTEM - COPY AND PASTE THIS INTO PHPMYADMIN

-- Clear all existing broken OTPs with wrong expiration times
DELETE FROM user_otps;

-- The schema is already correct, the issue was timezone mismatch
-- Now the EmailService uses DATE_ADD(NOW(), INTERVAL 10 MINUTE) to fix this

-- Verify the table is clean
SELECT COUNT(*) as remaining_otps FROM user_otps;

-- Test the time functions work correctly
SELECT 
    NOW() as current_time,
    DATE_ADD(NOW(), INTERVAL 10 MINUTE) as expires_in_10_min,
    TIMESTAMPDIFF(MINUTE, NOW(), DATE_ADD(NOW(), INTERVAL 10 MINUTE)) as minutes_diff;