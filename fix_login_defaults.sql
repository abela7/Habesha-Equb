-- HabeshaEqub: Fix Login Redirect Issue
-- This will fix the OTP login redirect problem and set proper defaults

-- 1. Change language_preference default to 1 (Amharic)
ALTER TABLE `members` 
MODIFY COLUMN `language_preference` TINYINT(1) NOT NULL DEFAULT 1 
COMMENT 'Web language: 0=English, 1=Amharic';

-- 2. Reset all existing members' rules_agreed to 0 
-- (so they go through welcome flow on first OTP login)
UPDATE `members` 
SET `rules_agreed` = 0 
WHERE `rules_agreed` = 1;

-- 3. Optional: Set all existing members to Amharic (language_preference = 1)
-- Uncomment the line below if you want existing members to use Amharic by default
-- UPDATE `members` SET `language_preference` = 1;

-- Verify the changes
SELECT 
    member_id,
    first_name,
    language_preference,
    rules_agreed,
    'Fixed' as status
FROM members 
ORDER BY id;