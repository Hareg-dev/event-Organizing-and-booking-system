<?php
// Database configuration (SQLite for simplicity)
define('DB_FILE', __DIR__ . '/../database/event.db');

// Create database directory if it doesn't exist
$dbDir = dirname(DB_FILE);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

// Create PDO connection
try {
    $conn = new PDO('sqlite:' . DB_FILE);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>