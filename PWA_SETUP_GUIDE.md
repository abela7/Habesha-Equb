# HabeshaEqub - Progressive Web App (PWA) Setup Guide

## Overview

Your HabeshaEqub system is now a fully functional Progressive Web App (PWA)! This means users can install it on their mobile devices and use it like a native app, with automatic updates and offline support.

## Features

✅ **Installable** - Users can install the app on their phone/tablet  
✅ **Smart Updates** - Automatically detects and notifies users of updates  
✅ **Offline Support** - Basic pages work offline  
✅ **Native-like Experience** - Full-screen, standalone mode  
✅ **No App Store Required** - Install directly from the browser  

## How to Update the App

### For Regular Updates:

1. **Update the Service Worker Version**
   - Open `service-worker.js`
   - Change the `CACHE_VERSION` constant:
   ```javascript
   const CACHE_VERSION = '1.0.1'; // Increment this
   ```

2. **Update PWA Config** (Optional)
   - Open `includes/pwa-head.php`
   - Update the `cacheVersion` in `PWA_CONFIG`:
   ```javascript
   cacheVersion: '1.0.1'
   ```

3. **Deploy Files**
   - Upload the updated files to your server
   - The service worker will automatically detect the change

4. **Users Get Notified**
   - Users will see an update notification at the bottom of the screen
   - They click "Update" and the app refreshes with new version
   - **No uninstall/reinstall needed!**

### Update Process Flow:

```
Old Version (v1.0.0) → New Version (v1.0.1)
     ↓
Service Worker detects change
     ↓
New version downloads in background
     ↓
User sees "Update Available" notification
     ↓
User clicks "Update"
     ↓
App reloads with new version
     ↓
Old cache automatically cleared
```

## Files Structure

```
/
├── manifest.json              # PWA manifest (app metadata)
├── service-worker.js          # Service worker (caching & updates)
├── offline.html               # Offline fallback page
├── assets/
│   ├── css/
│   │   └── pwa.css           # PWA UI styles
│   └── js/
│       └── pwa-manager.js    # PWA management logic
└── includes/
    ├── pwa-head.php          # PWA meta tags (include in <head>)
    └── pwa-footer.php        # PWA scripts (include before </body>)
```

## Adding PWA Support to New Pages

### For Admin Pages:

1. In the `<head>` section, after favicons:
```php
<!-- PWA Support -->
<?php include '../includes/pwa-head.php'; ?>
```

2. Before closing `</body>` tag:
```php
<!-- PWA Footer -->
<?php include '../includes/pwa-footer.php'; ?>
```

### For User Pages:

Same as above, paths are already handled automatically.

### For Root Pages (like index.php):

```php
<!-- PWA Support -->
<?php include 'includes/pwa-head.php'; ?>

<!-- ... other content ... -->

<!-- PWA Footer -->
<?php include 'includes/pwa-footer.php'; ?>
```

## How Users Install the App

### On Android (Chrome):

1. Visit your website
2. Look for the "Install App" button (bottom right)
3. Or Chrome will show an install banner
4. Tap "Install" or "Add to Home Screen"
5. App is now installed!

### On iOS (Safari):

1. Visit your website
2. Tap the Share button
3. Scroll down and tap "Add to Home Screen"
4. Customize the name (optional)
5. Tap "Add"
6. App is now installed!

### On Desktop (Chrome/Edge):

1. Visit your website
2. Click the install icon in the address bar
3. Or click the "Install App" button
4. App opens in standalone window

## Update Notification System

The app automatically checks for updates every 30 minutes. When an update is detected:

1. **Update Notification Appears** - Bottom of screen
2. **User Clicks "Update"** - App reloads with new version
3. **Seamless Transition** - No data loss, no reinstall needed

### Manual Update Check:

Users can also manually trigger an update check by refreshing the page.

## Testing PWA Features

### Test Installation:

1. Open Chrome DevTools (F12)
2. Go to "Application" tab
3. Click "Service Workers"
4. Check if service worker is registered
5. Go to "Manifest" section
6. Verify manifest is loaded correctly

### Test Updates:

1. Install the app
2. Change `CACHE_VERSION` in `service-worker.js`
3. Upload the file
4. Wait 30 minutes or refresh
5. Update notification should appear

### Test Offline:

1. Install the app
2. Open Chrome DevTools
3. Go to "Network" tab
4. Check "Offline" checkbox
5. Navigate to cached pages
6. Should see offline.html for non-cached pages

## Browser Support

✅ **Chrome/Edge** - Full support  
✅ **Safari (iOS 11.3+)** - Full support  
✅ **Firefox** - Full support  
✅ **Samsung Internet** - Full support  

## Security Considerations

- Service workers only work over HTTPS (or localhost)
- All cached content is served securely
- API calls always go to network (not cached)
- Admin API calls are never cached

## Troubleshooting

### Service Worker Not Registering:

1. Check browser console for errors
2. Ensure site is served over HTTPS
3. Verify `service-worker.js` is accessible
4. Check file permissions

### Updates Not Showing:

1. Clear browser cache
2. Check `CACHE_VERSION` was updated
3. Verify service worker is active
4. Check browser console for errors

### Install Button Not Showing:

1. Check if app is already installed
2. Verify manifest.json is valid
3. Check browser support
4. Try different browser

## Version History

- **v1.0.0** - Initial PWA implementation
  - Basic installation
  - Smart update system
  - Offline support
  - Caching strategy

## Future Enhancements

- Push notifications
- Background sync
- Advanced offline features
- App shortcuts customization

## Support

For issues or questions about the PWA implementation, check:
- Browser console for errors
- Service Worker status in DevTools
- Network tab for failed requests

---

**Happy PWA Development! 🚀**

