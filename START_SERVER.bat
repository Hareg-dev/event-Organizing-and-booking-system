@echo off
echo ========================================
echo EventHub - Starting PHP Development Server
echo ========================================
echo.
echo Checking PHP installation...

php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ERROR: PHP is not installed or not in PATH!
    echo.
    echo Please install PHP from: https://www.php.net/downloads.php
    echo Or install XAMPP from: https://www.apachefriends.org/
    echo.
    echo After installation, add PHP to your system PATH or run this from XAMPP.
    echo.
    pause
    exit /b 1
)

echo PHP found! Starting server...
echo.
echo Server will start at: http://localhost:8000
echo.
echo First time setup: http://localhost:8000/setup.php
echo Homepage: http://localhost:8000/index.php
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php -S localhost:8000

pause

