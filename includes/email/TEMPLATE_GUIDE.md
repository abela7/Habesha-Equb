# HabeshaEqub Email Templates Guide

## 📧 Available Templates

### ✅ Ready-to-Use Templates:
1. **`account_approved.html`** - Welcome email when user is approved
2. **`email_verification.html`** - Email verification with OTP
3. **`welcome_pending.html`** - Registration confirmation while waiting approval  
4. **`otp_login.html`** - Login OTP code for passwordless authentication
5. **`base_template.html`** - Master template for creating new email types

---

## 🎨 Design Features

### Color Palette (From Website):
- **Cream Background:** `#F1ECE2`
- **Dark Purple:** `#4D4052` (main text)
- **Darker Purple:** `#301934` (headers, important)
- **Gold:** `#DAA520` (primary actions, logo)
- **Light Gold:** `#CDAF56` (secondary accents)
- **Brown:** `#5D4225` (tertiary elements)

### Logo Design:
- **Clean table-based approach** (no CSS flexbox that triggers spam)
- **Golden circle with "H"** for HabeshaEqub branding
- **60px diameter** with proper line-height centering

### Layout Structure:
- **600px max width** for desktop
- **Mobile responsive** with proper scaling
- **Table-based layout** for maximum email client compatibility
- **No external dependencies** or images

---

## 🔧 How to Use Base Template

### Step 1: Copy Base Template
```html
cp includes/email/templates/base_template.html includes/email/templates/your_new_email.html
```

### Step 2: Replace Variables
Replace these placeholders in your new template:

```html
{{email_title}} → Your Email Title
{{banner_color}} → Banner background color
{{banner_text_color}} → Banner text color  
{{banner_title}} → Main banner heading
{{banner_subtitle}} → Banner subtitle
{{main_content}} → Your email body content
{{footer_message}} → Custom footer message
{{footer_disclaimer}} → Footer disclaimer text
```

### Step 3: Color Combinations
Use these tested combinations:

**Approval/Success Emails:**
```html
{{banner_color}} = #DAA520 (Gold)
{{banner_text_color}} = #301934 (Dark Purple)
```

**Verification/Info Emails:**
```html
{{banner_color}} = #CDAF56 (Light Gold)  
{{banner_text_color}} = #301934 (Dark Purple)
```

**Waiting/Pending Emails:**
```html
{{banner_color}} = #5D4225 (Brown)
{{banner_text_color}} = #FFFFFF (White)
```

**Error/Alert Emails:**
```html
{{banner_color}} = #301934 (Dark Purple)
{{banner_text_color}} = #FFFFFF (White)
```

---

## 📋 Template Variables

### Common Variables Used in All Templates:
- `{{first_name}}` - User's first name
- `{{last_name}}` - User's last name  
- `{{email}}` - User's email address
- `{{member_id}}` - User's member ID

### Specific Template Variables:

**account_approved.html:**
- `{{login_url}}` - Login page URL

**email_verification.html & otp_login.html:**
- `{{otp_code}}` - 6-digit verification code

**welcome_pending.html:**
- `{{registration_date}}` - When user registered

---

## 🛡️ Spam-Safe Guidelines

### ✅ What to DO:
- Use **table-based layouts** only
- Keep **inline CSS** only  
- Use **standard web fonts**
- Use **text-based content** (no images)
- Keep **clean, professional language**
- Test with **multiple email providers**

### ❌ What to AVOID:
- **Emojis** (🎉, 📧, etc.)
- **CSS flexbox or grid** (use tables)
- **External images or CSS files**
- **JavaScript or interactive elements**  
- **Marketing-style language**
- **ALL CAPS text**
- **Excessive exclamation marks**

---

## 🔄 EmailService Integration

### Add New Template to EmailService.php:

```php
// In EmailService.php loadEmailTemplate() method
case 'your_new_template':
    $template_file = __DIR__ . '/templates/your_new_email.html';
    break;
```

### Call from PHP:
```php
$emailService = new EmailService();
$result = $emailService->send(
    $user_email,
    'your_new_template',
    [
        'first_name' => $user_name,
        'custom_variable' => $custom_value
    ]
);
```

---

## 🧪 Testing Checklist

Before using any new template:

### ✅ Technical Tests:
- [ ] HTML validates (no syntax errors)
- [ ] Renders correctly in Gmail
- [ ] Renders correctly in Outlook  
- [ ] Renders correctly on mobile
- [ ] All variables are replaced properly
- [ ] No "Images hidden" warnings

### ✅ Content Tests:
- [ ] Professional tone and language
- [ ] Clear call-to-action
- [ ] Contact information included
- [ ] Proper disclaimers
- [ ] Matches brand colors exactly

### ✅ Spam Tests:
- [ ] Arrives in inbox (not spam)
- [ ] No external resources
- [ ] Clean HTML structure
- [ ] Professional sender reputation

---

## 📞 Support

For questions about email templates:
- **Technical Issues:** Check EmailService.php logs
- **Design Changes:** Follow color palette guidelines
- **Spam Issues:** Review spam-safe guidelines above

---

*Last Updated: $(date)*
*Version: 1.0*