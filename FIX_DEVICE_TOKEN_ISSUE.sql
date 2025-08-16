-- PERMANENT FIX for White Blank Page Issue
-- This SQL will clean up old device_tracking records that have NULL device_token values
-- These NULL records are causing the infinite redirect loop on mobile devices

-- Step 1: Clean up old records with NULL device_token (these are from before the device token feature)
DELETE FROM device_tracking 
WHERE device_token IS NULL 
AND expires_at IS NULL;

-- Step 2: Verify the cleanup worked
SELECT COUNT(*) as remaining_null_tokens 
FROM device_tracking 
WHERE device_token IS NULL;

-- Step 3: Show current valid device tokens (should only show records with actual tokens)
SELECT id, email, device_fingerprint, 
       CASE WHEN device_token IS NOT NULL THEN 'HAS_TOKEN' ELSE 'NULL_TOKEN' END as token_status,
       expires_at, created_at, last_seen
FROM device_tracking 
ORDER BY created_at DESC 
LIMIT 10;
