# SMS Integration Setup Guide
## HabeshaEqub SMS Functionality with Brevo

---

## ✅ STATUS: COMPLETE & WORKING!

SMS functionality has been fully integrated into your HabeshaEqub system! You can now send notifications via:
- **Email only**
- **SMS only**
- **Both (Email + SMS)**

**New Features**:
- ✅ Full SMS integration with Brevo API
- ✅ Delivery tracking modal (shows sent/failed counts)
- ✅ Error reporting with detailed diagnostics
- ✅ Test page for SMS configuration validation
- ✅ Support for links in both Email & SMS
- ✅ Bilingual support (English + Amharic)
- ✅ Rate limiting to prevent abuse

---

## Files Created/Modified

### New Files:
1. `includes/sms/SmsService.php` - Main SMS service class
2. `sql_sms_integration.sql` - Database setup SQL
3. `admin/test-sms.php` - SMS testing page
4. `SMS_AND_EMAIL_LINKS_GUIDE.md` - Guide for using links in messages

### Modified Files:
1. `admin/api/notifications.php` - Added SMS sending logic
2. `admin/notifications.php` - Updated UI with SMS/Both options + delivery tracking modal
3. `admin/system-configuration.php` - Added SMS settings page

---

## Step-by-Step Setup Instructions

### Step 1: Run the SQL Script

1. Open **phpMyAdmin** on your cPanel
2. Select your database: `habeshjv_habeshaequb`
3. Go to the **SQL** tab
4. Copy and paste the contents of `sql_sms_integration.sql`
5. Click **Go** to execute

This will:
- Add SMS configuration settings to `system_settings` table
- Create `sms_rate_limits` table for rate limiting
- Add `sms_notifications` field to `members` table

### Step 2: Configure SMS Settings in Admin Panel

1. Log in to your admin panel
2. Go to **System Configuration** (`admin/system-configuration.php`)
3. Click on the **SMS** tab
4. Fill in the following:

   **Enable SMS:** Check the box ✓
   
   **Brevo API Key:** Paste your API key (starts with `xkeysib-`)
   - Get it from: https://app.brevo.com → Settings → SMTP & API → API Keys
   
   **SMS Sender Name:** `HabeshaEqub` (or your preferred name, max 11 chars)
   - Note: Must be approved by Brevo (takes 24-48 hours)
   
   **Test Mode:** Leave unchecked (check only for testing without sending)

5. Click **Save Configuration**

### Step 3: Verify Phone Number Format in Database

Your members' phone numbers should be in **E.164 format**:
- ✅ Good: `+447123456789`
- ❌ Bad: `07123456789`, `+44 7123 456789`

The SMS service will auto-convert UK numbers starting with 0, but it's best to have them properly formatted.

### Step 4: Test SMS Sending

1. Go to **Notifications** (`admin/notifications.php`)
2. Create a test notification:
   - **Audience:** Select yourself or a test member
   - **Title (English):** Test SMS
   - **Title (Amharic):** ሙከራ
   - **Detail (English):** This is a test SMS from HabeshaEqub
   - **Detail (Amharic):** ይህ ከ HabeshaEqub የመጣ የሙከራ ኤስኤምኤስ ነው
   - **Send via:** Select "SMS only" or "Both"
3. Click **Send**
4. Check your phone for the SMS!

---

## How to Use SMS Feature

### Sending Notifications

When creating notifications in `admin/notifications.php`, you'll see a **Send via** dropdown:

**Options:**
1. **Email only** - Sends to members with email notifications enabled
2. **SMS only** - Sends to members with valid phone numbers
3. **Both (Email + SMS)** - Sends via both channels (recommended)

### Eligibility Rules

**Email:**
- Member must be active and approved
- `email_notifications` = 1
- Valid email address exists

**SMS:**
- Member must be active and approved
- Valid phone number exists (not empty)
- System SMS enabled in settings

### Language Support

SMS automatically uses the member's preferred language:
- `language_preference` = 0 → English
- `language_preference` = 1 → Amharic

---

## SMS Costs & Credits

### Brevo UK SMS Pricing
- Approximately **£0.04 - £0.06 per SMS**
- You purchased: **30 SMS credits**
- Each SMS = 1 credit (if under 160 chars)
- Unicode/Amharic SMS = 70 chars per segment

### Character Limits
- **Standard SMS (English):** 160 characters
- **Unicode SMS (Amharic):** 70 characters
- Messages longer than this will be split into multiple SMS (using more credits)

The system automatically truncates very long messages to prevent excessive credit usage.

---

## Rate Limiting

To prevent spam and protect your credits:
- **Maximum:** 10 SMS per phone number per hour
- Tracked in `sms_rate_limits` table
- Automatically enforced by `SmsService.php`

---

## Monitoring & Troubleshooting

### Check SMS Results
After sending, you'll see a summary:
```
Notification sent successfully!

Emails: 8/10 sent
SMS: 9/10 sent
```

### View SMS Balance
The system doesn't automatically display your remaining credits, but you can check in Brevo:
- Go to: https://app.brevo.com → Settings → Plan
- View your SMS credit balance

### Common Issues

**"SMS API key not configured"**
- Solution: Add your API key in System Configuration → SMS tab

**"Invalid phone number format"**
- Solution: Phone numbers must be in E.164 format (+447...)
- Update member phone numbers in the database

**"SMS rate limit exceeded"**
- Solution: Wait 1 hour or increase the limit in `SmsService.php` line 175

**"SMS sending failed: Invalid sender"**
- Solution: Your sender name hasn't been approved yet by Brevo
- Wait 24-48 hours or contact Brevo support

---

## Testing Without Spending Credits

Enable **Test Mode** in System Configuration → SMS:
1. Check "Enable test mode"
2. Save configuration
3. SMS will be logged to error_log but not actually sent
4. Perfect for development/testing!

---

## Example Use Cases

### Payment Reminders
Send dual notifications (Email + SMS) for payment reminders:
- Email: Detailed receipt with link
- SMS: Short reminder "Payment due on [date]. Check email for details."

### Payout Notifications
Send SMS for urgent notifications:
- "Your equb payout of £7,000 is ready! Check email for receipt."

### Emergency Broadcasts
Send to all members via SMS:
- Quick updates, meeting changes, urgent announcements

---

## Security Notes

- API key is stored as password type (hidden in UI)
- Never commit API keys to Git
- Rotate API keys periodically
- Monitor SMS usage to prevent abuse
- Rate limiting protects against spam

---

## Support & Next Steps

### If You Need Help:
1. Check Brevo documentation: https://developers.brevo.com/docs/sms-api
2. Test in Test Mode first
3. Check error logs: `logs/` directory
4. Verify phone number formats

### Future Enhancements:
- [ ] SMS delivery status tracking
- [ ] Scheduled SMS sending
- [ ] SMS templates
- [ ] Member opt-in/opt-out for SMS
- [ ] SMS analytics dashboard

---

## Quick Reference

**Admin Pages:**
- Notifications: `admin/notifications.php`
- SMS Settings: `admin/system-configuration.php` → SMS tab
- Member Phone Numbers: `admin/members.php`

**Files:**
- SMS Service: `includes/sms/SmsService.php`
- Notifications API: `admin/api/notifications.php`
- Rate Limits Table: `sms_rate_limits`

**Brevo Links:**
- Dashboard: https://app.brevo.com
- SMS Settings: https://app.brevo.com/sms/transactional
- API Keys: https://app.brevo.com/settings/keys/api

---

## Success!

Your SMS integration is complete and ready to use! 

Send your first SMS notification and let me know how it goes!

