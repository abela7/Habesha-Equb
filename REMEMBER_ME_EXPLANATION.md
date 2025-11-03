# Remember Me (7 Days) - Detailed Explanation

## How It Works

When a user checks "Keep me signed in for 7 days" during login, the system implements a dual-persistence mechanism:

### 1. **Device Token Cookie** (Primary Mechanism)
- A secure `device_token` cookie is set with a 7-day expiration
- This cookie is stored in the `device_tracking` database table
- Used for **auto-login** when the user returns after closing the browser

### 2. **Session Cookie Extension** (Secondary Mechanism)
- The PHP session cookie lifetime is extended to 7 days
- Prevents immediate session expiration during active use
- Works alongside the device token for seamless experience

## Flow Diagram

### Initial Login (with "Remember Me" checked):

```
1. User enters OTP and checks "Remember me"
2. Login API (user/api/auth.php):
   ├─ Sets $_SESSION['remember_device'] = true
   ├─ Extends session cookie to 7 days
   ├─ Generates device_token (random 32-byte hex)
   ├─ Stores device_token in database (expires in 7 days)
   └─ Sets device_token cookie (expires in 7 days)
3. User is logged in and redirected
```

### Active Session (User browsing):

```
1. Each page load calls auth_guard.php
2. Checks $_SESSION['remember_device'] or $_COOKIE['device_token']
3. If found → timeout is set to 7 days (168 hours)
4. If not found → timeout is 24 hours
5. Session age is checked against timeout
6. If expired → logout, else continue
```

### Auto-Login (User returns after closing browser):

```
1. User visits any page
2. login.php checks for device_token cookie
3. If found → checkRememberedDevice() runs:
   ├─ Validates token in database
   ├─ Checks expiration
   ├─ Creates NEW session
   ├─ Sets $_SESSION['remember_device'] = true
   ├─ Extends NEW session cookie to 7 days
   └─ User is automatically logged in
4. User redirected to dashboard
```

## Key Files & Functions

### 1. **user/api/auth.php** (Login Handler)
- **Line 346**: Reads `remember_device` checkbox
- **Line 407-421**: Sets `$_SESSION['remember_device']` and extends session cookie
- **Line 430-460**: Creates device_token and sets cookie

### 2. **user/includes/auth_guard.php** (Session Protection)
- **Line 147**: Checks for remember me flag: `$_SESSION['remember_device'] || $_COOKIE['device_token']`
- **Line 148**: Extends timeout to 7 days if remembered
- **Line 149**: Checks if session age exceeds timeout
- **Line 191-193**: Refreshes login time on activity (sliding session)

### 3. **user/includes/device_auth.php** (Auto-Login)
- **Line 40**: Checks for `device_token` cookie
- **Line 88-143**: Validates token, creates session, sets remember flag
- **Line 131-138**: Extends session cookie to 7 days

### 4. **user/includes/session_config.php** (Session Configuration)
- **Line 19**: Sets `session.gc_maxlifetime` to 7 days (keeps session files on server)
- **Line 23-31**: Detects `device_token` cookie and sets `session.cookie_lifetime` accordingly

## Why It Wasn't Working Before

### Issues Fixed:

1. **Missing Session Flag**: `$_SESSION['remember_device']` wasn't set during manual login
   - **Fix**: Added flag setting in `user/api/auth.php` line 408

2. **Session Cookie Expiration**: Session cookie expired when browser closed (lifetime = 0)
   - **Fix**: Extended session cookie to 7 days when "remember me" is checked (line 414-421)

3. **Timeout Detection**: `auth_guard.php` only checked `$_SESSION['auto_login']` (from auto-login)
   - **Fix**: Now also checks `$_SESSION['remember_device']` (line 147)

4. **Cookie Security**: Device token cookie used hardcoded `secure => true`, failing on HTTP
   - **Fix**: Now detects HTTPS and sets secure flag accordingly (line 457)

## Testing the Fix

### Test Case 1: Manual Login with Remember Me
1. Log in with "Remember me" checked
2. Check browser cookies → `device_token` should expire in 7 days
3. Check session cookie → should expire in 7 days
4. Stay logged in for more than 30 minutes → should NOT logout

### Test Case 2: Auto-Login After Browser Close
1. Log in with "Remember me" checked
2. Close browser completely
3. Reopen browser and visit site
4. Should automatically log in without entering credentials

### Test Case 3: Session Timeout
1. Log in with "Remember me" checked
2. Wait 7 days (or modify timeout for testing)
3. Should be logged out and device_token cleared

## Security Considerations

- Device tokens are cryptographically secure (32 random bytes)
- Tokens expire after 7 days
- Tokens are stored in database with expiration tracking
- Session cookies are HttpOnly (prevent JavaScript access)
- Session cookies use SameSite=Lax (CSRF protection)
- Secure flag is set based on HTTPS detection

## Configuration Values

- **Session Timeout (Normal)**: 24 hours
- **Session Timeout (Remember Me)**: 7 days (168 hours)
- **Device Token Expiration**: 7 days
- **Session Cookie Lifetime (Remember Me)**: 7 days (604800 seconds)
- **Server Session Lifetime**: 7 days (604800 seconds)

