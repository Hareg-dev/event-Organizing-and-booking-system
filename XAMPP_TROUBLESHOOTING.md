# EventHub - XAMPP Troubleshooting Guide

## 🔧 Common Issues & Solutions

### Issue 1: "Page Not Found" or 404 Error
**Solution:**
- Ensure files are in `C:\xampp\htdocs\eventhub\`
- Visit: `http://localhost/eventhub/` (not just `localhost`)
- Check Apache is running in XAMPP Control Panel

### Issue 2: Database Connection Error
**Solution:**
- Run setup first: `http://localhost/eventhub/setup.php`
- Check if `database` folder exists in your project
- Ensure SQLite extension is enabled in PHP

### Issue 3: CSS/JS Not Loading
**Solution:**
- Check file paths in browser developer tools (F12)
- Ensure `assets` folder is copied correctly
- Clear browser cache (Ctrl+F5)

### Issue 4: Session/Login Issues
**Solution:**
- Check if `session.save_path` is writable in PHP
- Restart Apache in XAMPP
- Clear browser cookies for localhost

### Issue 5: API Endpoints Not Working
**Solution:**
- Check if `api` folder exists with all PHP files
- Ensure Apache mod_rewrite is enabled
- Check PHP error logs in XAMPP

## 🚀 Quick Start Commands

1. **Start XAMPP**: Open XAMPP Control Panel → Start Apache
2. **Setup Database**: Visit `http://localhost/eventhub/setup.php`
3. **Access Site**: Visit `http://localhost/eventhub/`
4. **Login**: Use demo accounts from setup page

## 📱 Test Checklist

- [ ] Homepage loads with events
- [ ] Login works with demo accounts
- [ ] Dashboard shows user-specific content
- [ ] Event creation works (as organizer)
- [ ] Booking system works (as user)
- [ ] Admin panel accessible (as admin)

## 🔍 Debug Mode

Add this to any PHP file for debugging:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

## 📞 Support

If issues persist:
1. Check XAMPP error logs: `C:\xampp\apache\logs\error.log`
2. Check PHP version: Visit `http://localhost/eventhub/phpinfo.php`
3. Verify file permissions on the project folder