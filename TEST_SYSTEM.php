<?php
/**
 * EventHub System Test Script
 * Run this after starting the PHP server to verify system functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>EventHub System Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-item { padding: 10px; margin: 5px 0; border-left: 4px solid #ddd; }
        .pass { border-left-color: #4caf50; background: #e8f5e9; }
        .fail { border-left-color: #f44336; background: #ffebee; }
        .info { border-left-color: #2196f3; background: #e3f2fd; }
        h1 { color: #667eea; }
        h2 { color: #333; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        .summary { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🔍 EventHub System Test Results</h1>";

$tests = [];
$passCount = 0;
$failCount = 0;

// Test 1: PHP Version
echo "<div class='test-section'><h2>1. PHP Environment</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<div class='test-item pass'>✓ PHP Version: $phpVersion (OK)</div>";
    $tests[] = ['name' => 'PHP Version', 'status' => 'PASS'];
    $passCount++;
} else {
    echo "<div class='test-item fail'>✗ PHP Version: $phpVersion (Requires 7.4+)</div>";
    $tests[] = ['name' => 'PHP Version', 'status' => 'FAIL'];
    $failCount++;
}

// Test 2: Required Extensions
echo "<h3>Required Extensions:</h3>";
$extensions = ['pdo', 'pdo_sqlite', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='test-item pass'>✓ $ext extension loaded</div>";
        $tests[] = ['name' => "Extension: $ext", 'status' => 'PASS'];
        $passCount++;
    } else {
        echo "<div class='test-item fail'>✗ $ext extension NOT loaded</div>";
        $tests[] = ['name' => "Extension: $ext", 'status' => 'FAIL'];
        $failCount++;
    }
}
echo "</div>";

// Test 3: File Structure
echo "<div class='test-section'><h2>2. File Structure</h2>";
$requiredFiles = [
    'index.php' => 'Homepage',
    'login.php' => 'Login page',
    'register.php' => 'Registration page',
    'dashboard.php' => 'User dashboard',
    'create-event.php' => 'Event creation',
    'event-details.php' => 'Event details',
    'book.php' => 'Booking page',
    'admin.php' => 'Admin panel',
    'setup.php' => 'Database setup',
    'includes/config.php' => 'Database config',
    'controllers/AuthController.php' => 'Auth controller',
    'controllers/EventController.php' => 'Event controller',
    'controllers/BookingController.php' => 'Booking controller',
    'models/User.php' => 'User model',
    'models/Event.php' => 'Event model',
    'models/Booking.php' => 'Booking model',
    'api/auth.php' => 'Auth API',
    'api/events.php' => 'Events API',
    'api/bookings.php' => 'Bookings API',
    'assets/css/style.css' => 'Stylesheet',
    'assets/js/script.js' => 'Main script',
    'database/schema.sql' => 'Database schema'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-item pass'>✓ $file - $description</div>";
        $tests[] = ['name' => "File: $file", 'status' => 'PASS'];
        $passCount++;
    } else {
        echo "<div class='test-item fail'>✗ $file - MISSING</div>";
        $tests[] = ['name' => "File: $file", 'status' => 'FAIL'];
        $failCount++;
    }
}
echo "</div>";

// Test 4: Database Connection
echo "<div class='test-section'><h2>3. Database Configuration</h2>";
$dbFile = __DIR__ . '/database/event.db';
$dbDir = __DIR__ . '/database';

if (is_dir($dbDir)) {
    echo "<div class='test-item pass'>✓ Database directory exists</div>";
    $passCount++;
} else {
    echo "<div class='test-item fail'>✗ Database directory missing</div>";
    $failCount++;
}

if (file_exists($dbFile)) {
    echo "<div class='test-item pass'>✓ Database file exists</div>";
    $passCount++;
    
    // Try to connect
    try {
        require_once 'includes/config.php';
        echo "<div class='test-item pass'>✓ Database connection successful</div>";
        $passCount++;
        
        // Check tables
        $tables = ['users', 'events', 'bookings', 'categories', 'ticket_types'];
        $stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table'");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Database Tables:</h3>";
        foreach ($tables as $table) {
            if (in_array($table, $existingTables)) {
                echo "<div class='test-item pass'>✓ Table '$table' exists</div>";
                $passCount++;
            } else {
                echo "<div class='test-item fail'>✗ Table '$table' missing - Run setup.php</div>";
                $failCount++;
            }
        }
    } catch (Exception $e) {
        echo "<div class='test-item fail'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        $failCount++;
    }
} else {
    echo "<div class='test-item info'>⚠ Database file not found - Run setup.php first</div>";
}
echo "</div>";

// Test 5: Directory Permissions
echo "<div class='test-section'><h2>4. Directory Permissions</h2>";
$dirs = ['database', 'assets/css', 'assets/js', 'assets/js/modules'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<div class='test-item pass'>✓ $dir is writable</div>";
            $passCount++;
        } else {
            echo "<div class='test-item info'>⚠ $dir exists but may not be writable</div>";
        }
    } else {
        echo "<div class='test-item fail'>✗ $dir directory missing</div>";
        $failCount++;
    }
}
echo "</div>";

// Test 6: API Endpoints (if database is set up)
echo "<div class='test-section'><h2>5. API Endpoints</h2>";
$apiFiles = [
    'api/auth.php' => 'Authentication API',
    'api/events.php' => 'Events API',
    'api/bookings.php' => 'Bookings API'
];

foreach ($apiFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-item pass'>✓ $file - $description</div>";
        $passCount++;
    } else {
        echo "<div class='test-item fail'>✗ $file - MISSING</div>";
        $failCount++;
    }
}
echo "</div>";

// Summary
echo "<div class='test-section summary'>";
echo "<h2>📊 Test Summary</h2>";
echo "<p><strong>Total Tests:</strong> " . ($passCount + $failCount) . "</p>";
echo "<p><strong>Passed:</strong> <span style='color: #4caf50; font-weight: bold;'>$passCount</span></p>";
echo "<p><strong>Failed:</strong> <span style='color: #f44336; font-weight: bold;'>$failCount</span></p>";

if ($failCount == 0) {
    echo "<p style='color: #4caf50; font-size: 1.2em; font-weight: bold;'>✓ All tests passed! System is ready.</p>";
    echo "<p><a href='index.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Homepage</a></p>";
} else {
    echo "<p style='color: #f44336; font-size: 1.2em; font-weight: bold;'>⚠ Some tests failed. Please fix the issues above.</p>";
    if (!file_exists($dbFile)) {
        echo "<p><strong>Next Step:</strong> <a href='setup.php' style='background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Run Database Setup</a></p>";
    }
}
echo "</div>";

echo "</body></html>";
?>

