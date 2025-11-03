# 📧📱 Using Links in Notifications (Email & SMS)

## ✅ Good News: Links Already Work!

Both email and SMS **already support links** - just type them naturally in your message:

---

## 📝 How to Use Links

### Method 1: Full URLs (Recommended)
Just paste the full URL in your message:

**Example Message:**
```
New payment due! View your account: https://habeshaequb.com/user/dashboard.php

Contact us: https://habeshaequb.com/contact
```

**What recipients see:**
- **Email**: Clickable blue link (automatically formatted)
- **SMS**: Plain text link (tap to open on mobile)

---

### Method 2: Short Instructions
For SMS (where character count matters), use short text:

**Example Message:**
```
Payment due!
Log in to view: habeshaequb.com/login

Need help? Call 123-456-7890
```

---

## 📱 SMS-Specific Tips

### Character Limits
- **Standard SMS**: 160 characters (English)
- **Unicode SMS** (Amharic): 70 characters
- **Long SMS**: Split into multiple messages (costs more)

### Best Practices for SMS
1. **Keep links SHORT**:
   - ✅ `habeshaequb.com/pay`
   - ❌ `https://www.habeshaequb.com/user/payments.php?id=123&ref=abc`

2. **Use URL shorteners** (optional):
   - bit.ly
   - tinyurl.com
   - Your own custom shortener

3. **Test character count** (use admin/test-sms.php):
   ```
   Example: "Payment due! View: habeshaequb.com/pay" = ~45 chars
   ```

---

## 📧 Email-Specific Tips

### Rich HTML (Future Enhancement)
Currently, emails send **plain text**. Links work but appear as text.

**Future**: HTML emails with formatted buttons:
```html
<a href="https://habeshaequb.com" style="...">View Dashboard</a>
```

---

## 🧪 Testing Your Links

### 1. Test Email
Go to: **admin/notifications.php**
- Select "Email only"
- Add your test email
- Message: `Test link: https://google.com`
- Send and check your inbox

### 2. Test SMS
Go to: **admin/test-sms.php**
- Enter your phone (+447...)
- Message: `Test link: https://google.com`
- Send and check your phone

---

## 💡 Real-World Examples

### Payment Reminder
```
Hi [Name],

Your Birr 500 payment is due today.

Pay now: habeshaequb.com/pay
View account: habeshaequb.com/dashboard

Questions? Call us: 123-456-7890
```
**Characters**: ~135 (fits in 1 SMS)

---

### Amharic Example
```
ሰላም [Name],

የ500 ብር ክፍያ ዛሬ መክፈል አለበት።

ይክፈሉ: habeshaequb.com/pay

ጥያቄ? ይደውሉ: 123-456-7890
```
**Characters**: ~65 (fits in 1 Unicode SMS)

---

### Position Swap Notification
```
Position swap request approved!

New position: #12
Payout date: Dec 15, 2025

View details: habeshaequb.com/payout-info.php
```

---

## 🔒 Security Tips

1. **Use HTTPS only**: `https://` not `http://`
2. **Short + secure**: Use branded short links if possible
3. **Never include**:
   - Passwords
   - API keys
   - Bank account numbers

---

## 📊 Delivery Report

After sending notifications with links, you'll see:

**Delivery Modal Shows**:
- ✅ Emails sent/failed
- ✅ SMS sent/failed
- ✅ Error details (if any)
- ✅ Message preview (both EN & AM)

**Common Failure Reasons**:
- Invalid phone numbers
- Insufficient SMS credits
- Rate limit exceeded
- Email bounced/invalid

---

## 🚀 Next Steps

1. ✅ Links work now - just use them!
2. ✅ Test with admin/test-sms.php
3. ✅ Check delivery reports after sending
4. 📝 Keep messages concise for SMS
5. 💰 Monitor SMS credit balance in Brevo

---

## 📞 Need Help?

**Common Questions**:

**Q: Why isn't my link clickable in SMS?**
A: SMS links are plain text. Mobile devices auto-detect and make them tappable.

**Q: Can I track link clicks?**
A: Not yet. Use URL shorteners with analytics (bit.ly, etc.)

**Q: How to make links shorter?**
A: 
1. Remove `https://www.` → just use domain
2. Use bit.ly or custom shortener
3. Create short routes: `/pay`, `/login`, `/help`

**Q: Links in Amharic SMS?**
A: Yes! Use English URLs even in Amharic messages:
```
ሰላም! ይክፈሉ: habeshaequb.com/pay
```

---

**✨ You're all set! Start sending notifications with links! ✨**

