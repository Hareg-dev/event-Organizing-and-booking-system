<?php
require_once 'includes/config.php';

echo "<!DOCTYPE html><html><head><title>EventHub Setup</title><style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5}.card{background:white;padding:30px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1)}.success{color:#4CAF50}.error{color:#f44336}.info{color:#2196F3}</style></head><body><div class='card'>";
echo "<h1>🚀 EventHub Database Setup</h1>";
echo "<p>Setting up your event management system...</p>";

// Read and execute the enhanced schema
$schemaFile = __DIR__ . '/database/schema.sql';
if (file_exists($schemaFile)) {
    $schema = file_get_contents($schemaFile);
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $conn->exec($statement);
                echo "<div class='success'>✓ Executed: " . substr($statement, 0, 50) . "...</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
            }
        }
    }
} else {
    echo "<div class='info'>Creating basic tables...</div>";
    
    // Fallback to basic tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            first_name TEXT,
            last_name TEXT,
            phone TEXT,
            role TEXT DEFAULT 'user' CHECK (role IN ('user', 'organizer', 'admin')),
            is_verified BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )",
        "CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            description TEXT,
            icon TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            short_description TEXT,
            date_start DATETIME NOT NULL,
            date_end DATETIME,
            location TEXT,
            venue_name TEXT,
            address TEXT,
            city TEXT,
            country TEXT,
            max_tickets INTEGER DEFAULT 100,
            available_tickets INTEGER DEFAULT 100,
            price DECIMAL(10,2) DEFAULT 0.00,
            currency TEXT DEFAULT 'USD',
            organizer_id INTEGER,
            category_id INTEGER,
            image_url TEXT,
            status TEXT DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'cancelled', 'sold_out')),
            is_featured BOOLEAN DEFAULT 0,
            tags TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (organizer_id) REFERENCES users(id),
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )",
        "CREATE TABLE IF NOT EXISTS ticket_types (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_id INTEGER,
            name TEXT NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            quantity INTEGER NOT NULL,
            available_quantity INTEGER NOT NULL,
            sale_start DATETIME,
            sale_end DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_reference TEXT UNIQUE NOT NULL,
            user_id INTEGER,
            event_id INTEGER,
            ticket_type_id INTEGER,
            quantity INTEGER NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            booking_fee DECIMAL(10,2) DEFAULT 0.00,
            status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled', 'refunded')),
            payment_status TEXT DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'failed', 'refunded')),
            payment_method TEXT,
            payment_reference TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (event_id) REFERENCES events(id),
            FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
        )"
    ];
    
    foreach ($tables as $sql) {
        try {
            $conn->exec($sql);
            echo "<div class='success'>✓ Table created successfully</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>✗ Error creating table: " . $e->getMessage() . "</div>";
        }
    }
}

// Insert categories
echo "<h3>Creating Categories</h3>";
$categories = [
    ['Music', 'Concerts, festivals, and music events', 'music'],
    ['Sports', 'Sports events and competitions', 'sports'],
    ['Technology', 'Tech conferences and workshops', 'tech'],
    ['Business', 'Business conferences and networking', 'business'],
    ['Arts', 'Art exhibitions and cultural events', 'arts'],
    ['Food', 'Food festivals and culinary events', 'food']
];

foreach ($categories as $cat) {
    try {
        $stmt = $conn->prepare("INSERT OR IGNORE INTO categories (name, description, icon) VALUES (?, ?, ?)");
        $stmt->execute($cat);
        echo "<div class='success'>✓ Category: {$cat[0]}</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    }
}

// Create sample data
echo "<h3>Creating Sample Data</h3>";

try {
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT OR IGNORE INTO users (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin@eventhub.com', $adminPassword, 'Admin', 'User', 'admin']);
    echo "<div class='success'>✓ Admin user created (admin@eventhub.com / admin123)</div>";
    
    // Create organizer user
    $organizerPassword = password_hash('organizer123', PASSWORD_DEFAULT);
    $stmt->execute(['organizer@eventhub.com', $organizerPassword, 'Event', 'Organizer', 'organizer']);
    echo "<div class='success'>✓ Organizer user created (organizer@eventhub.com / organizer123)</div>";
    
    // Create regular user
    $userPassword = password_hash('user123', PASSWORD_DEFAULT);
    $stmt->execute(['user@eventhub.com', $userPassword, 'Regular', 'User', 'user']);
    echo "<div class='success'>✓ Regular user created (user@eventhub.com / user123)</div>";
    
    // Create sample events
    $sampleEvents = [
        [
            'title' => 'Tech Conference 2024',
            'description' => 'Join us for the biggest tech conference of the year featuring industry leaders and cutting-edge innovations.',
            'short_description' => 'The biggest tech conference of 2024',
            'date_start' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'location' => 'Convention Center',
            'city' => 'San Francisco',
            'country' => 'USA',
            'max_tickets' => 500,
            'available_tickets' => 500,
            'price' => 299.99,
            'organizer_id' => 2,
            'category_id' => 3,
            'status' => 'published',
            'is_featured' => 1
        ],
        [
            'title' => 'Summer Music Festival',
            'description' => 'Experience the best live music with top artists from around the world in this amazing outdoor festival.',
            'short_description' => 'Live music festival with top artists',
            'date_start' => date('Y-m-d H:i:s', strtotime('+45 days')),
            'location' => 'Central Park',
            'city' => 'New York',
            'country' => 'USA',
            'max_tickets' => 1000,
            'available_tickets' => 1000,
            'price' => 89.99,
            'organizer_id' => 2,
            'category_id' => 1,
            'status' => 'published',
            'is_featured' => 1
        ],
        [
            'title' => 'Business Networking Event',
            'description' => 'Connect with industry professionals and expand your business network in this exclusive networking event.',
            'short_description' => 'Exclusive business networking opportunity',
            'date_start' => date('Y-m-d H:i:s', strtotime('+15 days')),
            'location' => 'Business Center',
            'city' => 'Chicago',
            'country' => 'USA',
            'max_tickets' => 200,
            'available_tickets' => 200,
            'price' => 49.99,
            'organizer_id' => 2,
            'category_id' => 4,
            'status' => 'published'
        ]
    ];
    
    $eventStmt = $conn->prepare("
        INSERT OR IGNORE INTO events (title, description, short_description, date_start, location, 
                                    city, country, max_tickets, available_tickets, price, 
                                    organizer_id, category_id, status, is_featured)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($sampleEvents as $event) {
        $eventStmt->execute([
            $event['title'], $event['description'], $event['short_description'],
            $event['date_start'], $event['location'], $event['city'], $event['country'],
            $event['max_tickets'], $event['available_tickets'], $event['price'],
            $event['organizer_id'], $event['category_id'], $event['status'], $event['is_featured'] ?? 0
        ]);
        echo "<div class='success'>✓ Sample event created: {$event['title']}</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>✗ Error creating sample data: " . $e->getMessage() . "</div>";
}

echo "<h3 class='success'>🎉 Setup Complete!</h3>";
echo "<div style='background:#e8f5e8;padding:20px;border-radius:8px;margin:20px 0'>";
echo "<h4>Test Accounts:</h4>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@eventhub.com / admin123</li>";
echo "<li><strong>Organizer:</strong> organizer@eventhub.com / organizer123</li>";
echo "<li><strong>User:</strong> user@eventhub.com / user123</li>";
echo "</ul>";
echo "</div>";
echo "<div style='text-align:center;margin:30px 0'>";
echo "<a href='index.php' style='background:#667eea;color:white;padding:15px 30px;text-decoration:none;border-radius:25px;font-size:18px;display:inline-block'>🏠 Go to Homepage</a>";
echo "</div>";
echo "</div></body></html>";
?>