# EventHub - Setup & Testing Guide

## 🚀 Quick Start

### Option 1: Using PHP Built-in Server (Recommended)

1. **Install PHP** (if not already installed):
   - Download from: https://www.php.net/downloads.php
   - Or use XAMPP: https://www.apachefriends.org/
   - Make sure PHP is added to your system PATH

2. **Start the Server**:
   ```bash
   # Windows (double-click START_SERVER.bat)
   START_SERVER.bat
   
   # Or manually:
   php -S localhost:8000
   ```

3. **Initialize Database**:
   - Open browser: http://localhost:8000/setup.php
   - This will create the database and sample data

4. **Test the System**:
   - Run tests: http://localhost:8000/TEST_SYSTEM.php
   - Visit homepage: http://localhost:8000/index.php

### Option 2: Using XAMPP

1. **Install XAMPP** from https://www.apachefriends.org/

2. **Copy Project**:
   - Copy project folder to `C:\xampp\htdocs\eventhub\`

3. **Start XAMPP**:
   - Start Apache from XAMPP Control Panel

4. **Access**:
   - Setup: http://localhost/eventhub/setup.php
   - Homepage: http://localhost/eventhub/index.php

---

## 🧪 Testing the System

### Automated Test
1. Start PHP server
2. Visit: http://localhost:8000/TEST_SYSTEM.php
3. Review test results

### Manual Testing

#### 1. **Database Setup**
- Visit: http://localhost:8000/setup.php
- Should create database and sample data
- Test accounts will be created

#### 2. **Homepage**
- Visit: http://localhost:8000/index.php
- Should display events
- Test search and filters

#### 3. **Authentication**
- Visit: http://localhost:8000/register.php
- Create a new account
- Visit: http://localhost:8000/login.php
- Login with test accounts:
  - Admin: admin@eventhub.com / admin123
  - Organizer: organizer@eventhub.com / organizer123
  - User: user@eventhub.com / user123

#### 4. **Event Creation** (Organizer/Admin)
- Login as organizer
- Visit: http://localhost:8000/create-event.php
- Create a new event
- Add ticket types

#### 5. **Booking** (User)
- Login as user
- Browse events
- Click "Book Now"
- Complete booking process

#### 6. **Dashboard**
- Visit: http://localhost:8000/dashboard.php
- View bookings
- View created events (organizer)
- Update profile

#### 7. **Admin Panel** (Admin only)
- Login as admin
- Visit: http://localhost:8000/admin.php
- Manage users and events

---

## 📋 Test Accounts

After running `setup.php`, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@eventhub.com | admin123 |
| Organizer | organizer@eventhub.com | organizer123 |
| User | user@eventhub.com | user123 |

---

## 🔧 Troubleshooting

### PHP Not Found
- Install PHP and add to PATH
- Or use XAMPP

### Database Errors
- Run `setup.php` first
- Check `database/` folder permissions
- Ensure SQLite extension is enabled

### 404 Errors
- Make sure you're in the project root directory
- Check file paths are correct

### Session Issues
- Check PHP session directory is writable
- Clear browser cookies if needed

---

## ✅ System Requirements

- PHP 7.4 or higher
- SQLite extension (usually included)
- PDO extension (usually included)
- Web server (PHP built-in or Apache/Nginx)

---

## 📝 Files Created During Setup

- `database/event.db` - SQLite database file
- Tables: users, events, bookings, categories, ticket_types, reviews, favorites

---

## 🎯 Next Steps After Setup

1. ✅ Run `setup.php` to initialize database
2. ✅ Run `TEST_SYSTEM.php` to verify installation
3. ✅ Login with test accounts
4. ✅ Create test events
5. ✅ Make test bookings
6. ✅ Explore all features

---

**Need Help?** Check the `COMPREHENSIVE_REVIEW.md` for detailed system documentation.

