# 📱 Quick SMS Tab - User Guide

## ✅ What's New

A **dedicated SMS tab** in `admin/notifications.php` that lets you:
1. **Select a member** → System auto-populates their info
2. **Choose a template** → Pre-filled message (or create new)
3. **Send instantly** → One-click SMS sending

**Perfect for**: Quick one-on-one SMS without going through the full notification form!

---

## 🚀 How to Use

### Step 1: Run Database Setup

**Run this SQL** in phpMyAdmin:
```sql
-- File: sql_sms_templates.sql
```

This creates the `sms_templates` table.

---

### Step 2: Access Quick SMS Tab

1. Go to **admin/notifications.php**
2. Click the **"Quick SMS"** tab (next to "Notifications")
3. You'll see 3 steps:

---

### Step 3: Select Member

**Search for member**:
- Type name, code, email, or phone
- Click **Search** button (or press Enter)
- Click on member from results

**Member info auto-displays**:
- Name & Member ID
- Phone number
- Email
- Language preference
- Status (Active/Inactive)

---

### Step 4: Select Template

**Choose existing template**:
- Browse templates in the list
- Click **✓** (checkmark) to use template
- Click **✏️** (pencil) to edit template

**Create new template**:
- Click **"New"** button
- Fill in:
  - Template name (e.g., "Payment Reminder")
  - Category (payment, welcome, reminder, etc.)
  - Title (English + Amharic)
  - Message (English + Amharic)
- Click **"Save Template"**

**Template variables** (auto-replaced):
- `{first_name}` → Member's first name
- `{last_name}` → Member's last name
- `{member_id}` → Member's ID (e.g., HEM-001)
- `{amount}` → Amount (currently shows "N/A")
- `{due_date}` → Due date (currently shows "N/A")

---

### Step 5: Preview & Send

**Preview**:
- Shows member's preferred language
- Displays character count
- Shows phone number

**Send**:
- Click **"Send SMS"** button
- Confirmation dialog appears
- Delivery report shows success/failure

---

## 💡 Workflow Examples

### Example 1: Payment Reminder

1. **Search member**: Type "John" → Select "John Doe"
2. **Select template**: Click "Payment Reminder" template
3. **Message auto-fills**:
   ```
   Hi John, your payment of Birr 500 is due. 
   Please pay by Dec 15, 2025. 
   View: habeshaequb.com/pay
   ```
4. **Click "Send SMS"** → Done!

---

### Example 2: Custom Message

1. **Select member**: Search and select member
2. **Create template**: Click "New" → Fill in custom message
3. **Save template**: Click "Save Template"
4. **Template auto-selects**: Ready to send!
5. **Edit if needed**: Modify message before sending
6. **Send**: Click "Send SMS"

---

## 🎯 Template Management

### Create Template

**Modal fields**:
- **Template Name**: "Payment Reminder", "Welcome Message", etc.
- **Category**: General, Payment, Welcome, Reminder, Alert
- **Title (EN)**: English title
- **Title (AM)**: Amharic title
- **Message (EN)**: English message body
- **Message (AM)**: Amharic message body

**Variables**: Use `{first_name}`, `{last_name}`, `{member_id}` in messages

---

### Edit Template

1. Click **✏️** (pencil) on template card
2. Modal opens with template data
3. Edit fields
4. Click **"Save Template"**
5. Template updates

---

### Delete Template

1. Click **✏️** (pencil) on template card
2. Click **"Delete"** button
3. Confirm deletion
4. Template is archived (soft delete)

---

### Manage Templates

- Click **"Manage Templates"** button
- Shows all templates (including archived)
- Filter and organize templates

---

## 📊 Features

### Auto-Variable Replacement

When you select **member + template**:
- `{first_name}` → Replaced with member's name
- `{last_name}` → Replaced with member's last name
- `{member_id}` → Replaced with member's ID
- Other variables → Can be customized later

---

### Character Counting

**Real-time character count**:
- English: 160 chars max (1 SMS)
- Amharic: 70 chars max (1 SMS)
- Count turns **red** if over limit

---

### Language Detection

**Auto-detects member preference**:
- Member prefers Amharic → Uses Amharic message
- Member prefers English → Uses English message
- Preview shows correct language

---

### Template Usage Tracking

**Templates track**:
- How many times used
- Created date
- Last updated
- Category

**Usage count** increments automatically when SMS is sent!

---

## 🔧 Troubleshooting

### "Send SMS" Button Disabled

**Reasons**:
- No member selected
- No template selected
- Title or message empty

**Fix**: Complete all steps (member + template + fill fields)

---

### Template Not Loading

**Check**:
1. Database table exists (`sms_templates`)
2. Run `sql_sms_templates.sql`
3. Check browser console for errors

---

### Member Not Found

**Check**:
- Member is active (`is_active = 1`)
- Search term matches (name, code, email, phone)
- Try different search terms

---

### SMS Not Sending

**Check**:
1. Member has phone number
2. SMS enabled in System Configuration
3. Brevo API key configured
4. Sufficient SMS credits
5. Server IP authorized in Brevo

---

## 📝 Best Practices

### Template Organization

1. **Use categories**: Payment, Welcome, Reminder, etc.
2. **Clear names**: "Payment Reminder - Urgent" not "Template 1"
3. **Update regularly**: Keep templates current
4. **Archive old**: Delete unused templates

---

### Message Length

**Keep messages short**:
- English: < 160 chars (1 SMS)
- Amharic: < 70 chars (1 SMS)
- Saves money on long messages

---

### Variable Usage

**Use variables wisely**:
- `{first_name}` → Personal touch
- `{member_id}` → Reference number
- `{amount}` → Payment amount (customize)
- `{due_date}` → Due date (customize)

---

## 🎉 Quick Start Checklist

- [ ] Run `sql_sms_templates.sql` in phpMyAdmin
- [ ] Go to admin/notifications.php
- [ ] Click "Quick SMS" tab
- [ ] Create first template (click "New")
- [ ] Search for a test member
- [ ] Select template
- [ ] Preview message
- [ ] Send test SMS to yourself
- [ ] Verify delivery

---

## 🚀 Pro Tips

1. **Create templates first**: Set up common messages before sending
2. **Test with yourself**: Send test SMS to your phone first
3. **Keep templates short**: Better for SMS costs
4. **Use variables**: Makes messages personal
5. **Track usage**: See which templates are used most

---

## 📞 Need Help?

**Common Questions**:

**Q: Can I send to multiple members?**
A: Currently, Quick SMS sends to one member at a time. Use the "Notifications" tab for bulk sending.

**Q: Can I edit message before sending?**
A: Yes! After selecting template, you can edit title and message fields.

**Q: Where are templates stored?**
A: In `sms_templates` database table.

**Q: Can I use templates in regular notifications?**
A: Not yet - this is Quick SMS specific. Future feature!

**Q: How to delete template?**
A: Click edit (pencil icon) → Click "Delete" button.

---

**✨ Enjoy your new Quick SMS feature! ✨**

