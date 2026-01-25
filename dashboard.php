<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/EventController.php';
require_once 'controllers/BookingController.php';

$authController = new AuthController();
$authController->requireLogin();

$user = $authController->getCurrentUser();
$eventController = new EventController();
$bookingController = new BookingController();

// Get user statistics
$userStats = [];
if ($user['role'] === 'organizer' || $user['role'] === 'admin') {
    $userEvents = $eventController->getByOrganizer($user['id']);
    $userStats['events_count'] = count($userEvents);
    $userStats['total_revenue'] = array_sum(array_column($userEvents, 'revenue'));
}

$userBookings = $bookingController->getByUser($user['id']);
$userStats['bookings_count'] = count($userBookings);
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }
        .user-details h1 {
            margin: 0 0 0.5rem;
            color: var(--text-primary);
            font-size: 1.8rem;
        }
        .user-details p {
            margin: 0;
            color: var(--text-secondary);
        }
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 1rem;
        }
        .role-user { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
        .role-organizer { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .role-admin { background: rgba(244, 67, 54, 0.1); color: #f44336; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem;
            font-size: 2rem;
            color: var(--text-primary);
        }
        .stat-card p {
            margin: 0;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .dashboard-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .tab-btn {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(102, 126, 234, 0.2);
            color: var(--text-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .tab-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }
        .tab-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .booking-item, .event-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .booking-item:hover, .event-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-confirmed { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
        .status-pending { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .status-cancelled { background: rgba(244, 67, 54, 0.1); color: #f44336; }
        .status-published { background: rgba(33, 150, 243, 0.1); color: #2196f3; }
        .status-draft { background: rgba(158, 158, 158, 0.1); color: #9e9e9e; }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .dashboard-tabs {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-calendar-star"></i> EventHub
            </div>
            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php if ($user['role'] === 'organizer' || $user['role'] === 'admin'): ?>
                    <a href="create-event.php"><i class="fas fa-plus-circle"></i> Create Event</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['first_name'] ?: $user['email'], 0, 1)) ?>
                </div>
                <div class="user-details">
                    <h1>
                        Welcome back, <?= htmlspecialchars($user['first_name'] ?: 'User') ?>!
                        <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                    </h1>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <p style="margin-top: 0.5rem; color: var(--text-light);">
                        <i class="fas fa-calendar"></i> Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-ticket-alt"></i>
                <h3><?= $userStats['bookings_count'] ?? 0 ?></h3>
                <p>Total Bookings</p>
            </div>
            
            <?php if ($user['role'] === 'organizer' || $user['role'] === 'admin'): ?>
                <div class="stat-card">
                    <i class="fas fa-calendar-plus"></i>
                    <h3><?= $userStats['events_count'] ?? 0 ?></h3>
                    <p>Events Created</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>$<?= number_format($userStats['total_revenue'] ?? 0, 2) ?></h3>
                    <p>Total Revenue</p>
                </div>
            <?php endif; ?>
            
            <div class="stat-card">
                <i class="fas fa-heart"></i>
                <h3>0</h3>
                <p>Favorite Events</p>
            </div>
        </div>

        <div class="dashboard-tabs">
            <button class="tab-btn active" onclick="showTab('bookings')">
                <i class="fas fa-ticket-alt"></i> My Bookings
            </button>
            <?php if ($user['role'] === 'organizer' || $user['role'] === 'admin'): ?>
                <button class="tab-btn" onclick="showTab('events')">
                    <i class="fas fa-calendar-plus"></i> My Events
                </button>
            <?php endif; ?>
            <button class="tab-btn" onclick="showTab('profile')">
                <i class="fas fa-user-cog"></i> Profile Settings
            </button>
        </div>

        <div id="bookings" class="tab-content active">
            <div class="content-card">
                <h2><i class="fas fa-ticket-alt"></i> My Bookings</h2>
                <?php if (empty($userBookings)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <h3>No bookings yet</h3>
                        <p>Start exploring events and book your first ticket!</p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-search"></i> Browse Events
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($userBookings as $booking): ?>
                        <div class="booking-item">
                            <div style="display: flex; justify-content: between; align-items: start; gap: 1rem;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.5rem; color: var(--text-primary);">
                                        <?= htmlspecialchars($booking['title']) ?>
                                    </h4>
                                    <p style="margin: 0 0 0.5rem; color: var(--text-secondary);">
                                        <i class="fas fa-calendar"></i> <?= date('M j, Y g:i A', strtotime($booking['date_start'])) ?>
                                    </p>
                                    <p style="margin: 0 0 0.5rem; color: var(--text-secondary);">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['location']) ?>
                                    </p>
                                    <p style="margin: 0; color: var(--text-secondary);">
                                        <strong>Booking ID:</strong> <?= htmlspecialchars($booking['booking_reference']) ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <div class="status-badge status-<?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </div>
                                    <p style="margin: 0.5rem 0; font-weight: 600; font-size: 1.1rem;">
                                        $<?= number_format($booking['total_price'], 2) ?>
                                    </p>
                                    <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                                        <?= $booking['quantity'] ?> ticket(s)
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($user['role'] === 'organizer' || $user['role'] === 'admin'): ?>
            <div id="events" class="tab-content">
                <div class="content-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h2><i class="fas fa-calendar-plus"></i> My Events</h2>
                        <a href="create-event.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Event
                        </a>
                    </div>
                    
                    <?php if (empty($userEvents)): ?>
                        <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <i class="fas fa-calendar-plus" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <h3>No events created yet</h3>
                            <p>Start creating amazing events for your audience!</p>
                            <a href="create-event.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Create Your First Event
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($userEvents as $event): ?>
                            <div class="event-item">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem; color: var(--text-primary);">
                                            <?= htmlspecialchars($event['title']) ?>
                                        </h4>
                                        <p style="margin: 0 0 0.5rem; color: var(--text-secondary);">
                                            <i class="fas fa-calendar"></i> <?= date('M j, Y g:i A', strtotime($event['date_start'])) ?>
                                        </p>
                                        <p style="margin: 0; color: var(--text-secondary);">
                                            <i class="fas fa-users"></i> <?= $event['bookings_count'] ?? 0 ?> bookings
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div class="status-badge status-<?= $event['status'] ?>">
                                            <?= ucfirst($event['status']) ?>
                                        </div>
                                        <p style="margin: 0.5rem 0; font-weight: 600; font-size: 1.1rem;">
                                            $<?= number_format($event['revenue'] ?? 0, 2) ?>
                                        </p>
                                        <div style="margin-top: 1rem;">
                                            <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-secondary" style="margin-right: 0.5rem;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div id="profile" class="tab-content">
            <div class="content-card">
                <h2><i class="fas fa-user-cog"></i> Profile Settings</h2>
                <div id="profile-message"></div>
                
                <form id="profile-form" style="max-width: 500px;">
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: #f5f5f5;">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const messageDiv = document.getElementById('profile-message');
            
            try {
                const response = await fetch('api/profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_profile',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="message success"><i class="fas fa-check-circle"></i> Profile updated successfully!</div>';
                } else {
                    messageDiv.innerHTML = `<div class="message error"><i class="fas fa-exclamation-circle"></i> ${result.message}</div>`;
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="message error"><i class="fas fa-exclamation-triangle"></i> Connection error. Please try again.</div>';
            }
        });
    </script>
</body>
</html>