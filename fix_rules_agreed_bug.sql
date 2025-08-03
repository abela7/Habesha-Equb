-- Fix for rules_agreed bug in registration
-- This SQL script fixes users who were incorrectly set to rules_agreed = 1 during registration
-- These users should go through the welcome page and rules agreement flow

-- Reset rules_agreed for users who registered but never actually went through the welcome flow
-- We identify them as users who:
-- 1. Are approved (is_approved = 1) 
-- 2. Are active (is_active = 1)
-- 3. Have rules_agreed = 1 (incorrectly set during registration)
-- 4. Either have never logged in (last_login IS NULL) OR logged in very recently (suggesting they bypassed welcome)

UPDATE members 
SET rules_agreed = 0, 
    updated_at = CURRENT_TIMESTAMP
WHERE is_approved = 1 
  AND is_active = 1 
  AND rules_agreed = 1
  AND (
    last_login IS NULL 
    OR last_login >= '2025-07-31 00:00:00'  -- Adjust this date based on when the bug was introduced
  );

-- Optional: Check how many users will be affected before running the update
-- SELECT COUNT(*) as affected_users 
-- FROM members 
-- WHERE is_approved = 1 
--   AND is_active = 1 
--   AND rules_agreed = 1
--   AND (last_login IS NULL OR last_login >= '2025-07-31 00:00:00');

-- Optional: See which users will be affected
-- SELECT id, member_id, first_name, last_name, email, created_at, last_login, rules_agreed
-- FROM members 
-- WHERE is_approved = 1 
--   AND is_active = 1 
--   AND rules_agreed = 1
--   AND (last_login IS NULL OR last_login >= '2025-07-31 00:00:00');