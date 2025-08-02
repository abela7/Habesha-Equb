-- FIX OTP TABLE SCHEMA - COPY AND PASTE THIS INTO PHPMYADMIN

-- Fix the otp_type column to allow 'otp_login'
ALTER TABLE user_otps MODIFY COLUMN otp_type 
ENUM('email_verification','login','otp_login') NOT NULL DEFAULT 'email_verification';

-- Fix the otp_code column length for 4-digit codes  
ALTER TABLE user_otps MODIFY COLUMN otp_code VARCHAR(10) NOT NULL;

-- Clear all existing broken OTPs
DELETE FROM user_otps;

-- Verify the changes
DESCRIBE user_otps;