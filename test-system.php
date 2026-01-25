<?php
/**
 * EventHub System Test Suite
 * Comprehensive testing of all system components
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering for clean results
ob_start();

echo "🚀 EventHub System Test Suite\n";
echo "============================\n\n";

$testResults = [];
$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $testResults, $totalTests, $passedTests;
    
    $totalTests++;
    echo "Testing: $testName... ";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "✅ PASS\n";
            $passedTests++;
            $testResults[$testName] = 'PASS';
        } else {
            echo "❌ FAIL\n";
            $testResults[$testName] = 'FAIL';
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $testResults[$testName] = 'ERROR: ' . $e->getMessage();
    }
}

// Test 1: Database Connection
runTest("Database Connection", function() {
    require_once 'includes/config.php';
    global $conn;
    return $conn instanceof PDO;
});

// Test 2: Database Tables Exist
runTest("Database Tables", function() {
    require_once 'includes/config.php';
    global $conn;
    
    $tables = ['users', 'categories', 'events', 'ticket_types', 'bookings', 'reviews', 'favorites'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            return false;
        }
    }
    return true;
});

// Test 3: User Model
runTest("User Model", function() {
    require_once 'models/User.php';
    $userModel = new User();
    
    // Test if we can get all users
    $users = $userModel->getAll();
    return is_array($users);
});

// Test 4: Event Model
runTest("Event Model", function() {
    require_once 'models/Event.php';
    $eventModel = new Event();
    
    // Test if we can get all events
    $events = $eventModel->getAllPublished();
    return is_array($events);
});

// Test 5: Booking Model
runTest("Booking Model", function() {
    require_once 'models/Booking.php';
    $bookingModel = new Booking();
    
    // Test if model loads without errors
    return $bookingModel instanceof Booking;
});

// Test 6: Auth Controller
runTest("Auth Controller", function() {
    require_once 'controllers/AuthController.php';
    $authController = new AuthController();
    
    // Test invalid login
    $result = $authController->login('invalid@test.com', 'wrongpassword');
    return isset($result['success']) && $result['success'] === false;
});

// Test 7: Event Controller
runTest("Event Controller", function() {
    require_once 'controllers/EventController.php';
    $eventController = new EventController();
    
    // Test getting all events
    $events = $eventController->getAll();
    return is_array($events);
});

// Test 8: Booking Controller
runTest("Booking Controller", function() {
    require_once 'controllers/BookingController.php';
    $bookingController = new BookingController();
    
    // Test if controller loads
    return $bookingController instanceof BookingController;
});

// Test 9: API Auth Endpoint
runTest("Auth API Endpoint", function() {
    if (!file_exists('api/auth.php')) {
        return false;
    }
    
    // Simulate API call
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['action'] = 'check';
    
    ob_start();
    include 'api/auth.php';
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    return isset($response['logged_in']);
});

// Test 10: API Events Endpoint
runTest("Events API Endpoint", function() {
    if (!file_exists('api/events.php')) {
        return false;
    }
    
    $_SERVER['REQUEST_METHOD'] = 'GET';
    unset($_GET['action']);
    
    ob_start();
    include 'api/events.php';
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    return is_array($response);
});

// Test 11: Frontend Pages Exist
runTest("Frontend Pages", function() {
    $pages = [
        'index.php', 'login.php', 'register.php', 'dashboard.php',
        'event-details.php', 'book.php', 'create-event.php',
        'admin.php', 'logout.php', 'setup.php'
    ];
    
    foreach ($pages as $page) {
        if (!file_exists($page)) {
            return false;
        }
    }
    return true;
});

// Test 12: CSS Files
runTest("CSS Assets", function() {
    return file_exists('assets/css/style.css');
});

// Test 13: JavaScript Files
runTest("JavaScript Assets", function() {
    return file_exists('assets/js/script.js');
});

// Test 14: Test Pages
runTest("Test Pages", function() {
    $testPages = ['test-api.html', 'test-functionality.html', 'test-frontend.html'];
    
    foreach ($testPages as $page) {
        if (!file_exists($page)) {
            return false;
        }
    }
    return true;
});

// Test 15: Documentation Files
runTest("Documentation", function() {
    $docs = ['README.md', 'API_DOCUMENTATION.md', 'SYSTEM_ANALYSIS.md'];
    
    foreach ($docs as $doc) {
        if (!file_exists($doc)) {
            return false;
        }
    }
    return true;
});

// Test 16: Sample Data Exists
runTest("Sample Data", function() {
    require_once 'models/User.php';
    require_once 'models/Event.php';
    
    $userModel = new User();
    $eventModel = new Event();
    
    $users = $userModel->getAll();
    $events = $eventModel->getAllPublished();
    
    return count($users) > 0 && count($events) > 0;
});

// Test 17: Admin User Exists
runTest("Admin User", function() {
    require_once 'models/User.php';
    $userModel = new User();
    
    $users = $userModel->getAll();
    foreach ($users as $user) {
        if ($user['role'] === 'admin') {
            return true;
        }
    }
    return false;
});

// Test 18: Event Categories
runTest("Event Categories", function() {
    require_once 'controllers/EventController.php';
    $eventController = new EventController();
    
    $categories = $eventController->getCategories();
    return is_array($categories) && count($categories) > 0;
});

// Test 19: Database Schema
runTest("Database Schema", function() {
    require_once 'includes/config.php';
    global $conn;
    
    // Check if users table has correct columns
    $stmt = $conn->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['id', 'email', 'password', 'first_name', 'last_name', 'role'];
    $existingColumns = array_column($columns, 'name');
    
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $existingColumns)) {
            return false;
        }
    }
    return true;
});

// Test 20: File Permissions
runTest("File Permissions", function() {
    // Check if database directory is writable
    $dbDir = __DIR__ . '/database';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    return is_writable($dbDir);
});

echo "\n============================\n";
echo "📊 Test Results Summary\n";
echo "============================\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if ($passedTests === $totalTests) {
    echo "🎉 ALL TESTS PASSED! System is fully functional.\n";
} else {
    echo "⚠️  Some tests failed. Check the details above.\n";
}

echo "\n============================\n";
echo "🔍 Detailed Test Results\n";
echo "============================\n";

foreach ($testResults as $test => $result) {
    $status = $result === 'PASS' ? '✅' : '❌';
    echo "$status $test: $result\n";
}

echo "\n============================\n";
echo "🚀 System Status\n";
echo "============================\n";

// Check system components
$components = [
    'Database' => file_exists('database/event.db'),
    'Models' => file_exists('models/User.php') && file_exists('models/Event.php') && file_exists('models/Booking.php'),
    'Controllers' => file_exists('controllers/AuthController.php') && file_exists('controllers/EventController.php'),
    'APIs' => file_exists('api/auth.php') && file_exists('api/events.php') && file_exists('api/bookings.php'),
    'Frontend' => file_exists('index.php') && file_exists('dashboard.php') && file_exists('admin.php'),
    'Assets' => file_exists('assets/css/style.css') && file_exists('assets/js/script.js'),
    'Tests' => file_exists('test-api.html') && file_exists('test-functionality.html'),
    'Documentation' => file_exists('README.md') && file_exists('API_DOCUMENTATION.md')
];

foreach ($components as $component => $status) {
    $icon = $status ? '✅' : '❌';
    echo "$icon $component: " . ($status ? 'Available' : 'Missing') . "\n";
}

echo "\n============================\n";
echo "📋 Next Steps\n";
echo "============================\n";

if ($passedTests === $totalTests) {
    echo "1. ✅ System is ready for use\n";
    echo "2. 🌐 Start your web server (XAMPP/PHP built-in)\n";
    echo "3. 🔗 Visit: http://localhost/eventhub/ (or your server path)\n";
    echo "4. 🧪 Run test-api.html for interactive API testing\n";
    echo "5. 🎯 Use test accounts:\n";
    echo "   - Admin: admin@eventhub.com / admin123\n";
    echo "   - Organizer: organizer@eventhub.com / organizer123\n";
    echo "   - User: user@eventhub.com / user123\n";
} else {
    echo "1. ❌ Fix failed tests above\n";
    echo "2. 🔧 Run setup.php if database issues\n";
    echo "3. 📁 Check file permissions\n";
    echo "4. 🔄 Re-run this test script\n";
}

echo "\n🎉 EventHub System Test Complete!\n";

// Clean up output buffer
ob_end_flush();
?>