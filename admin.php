<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/EventController.php';
require_once 'controllers/BookingController.php';
require_once 'models/User.php';

$authController = new AuthController();
$authController->requireRole('admin');

$user = $authController->getCurrentUser();
$eventController = new EventController();
$bookingController = new BookingController();
$userModel = new User();

// Get system statistics
$allUsers = $userModel->getAll();
$allEvents = $eventController->getAll();
$systemStats = [
    'total_users' => count($allUsers),
    'total_organizers' => count(array_filter($allUsers, fn($u) => $u['role'] === 'organizer')),
    'total_events' => count($allEvents),
    'published_events' => count(array_filter($allEvents, fn($e) => $e['status'] === 'published'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .admin-header {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .admin-header h1 {
            color: #f44336;
            margin: 0;
            font-size: 2.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.users { border-left: 4px solid #2196f3; }
        .stat-card.organizers { border-left: 4px solid #ff9800; }
        .stat-card.events { border-left: 4px solid #4caf50; }
        .stat-card.revenue { border-left: 4px solid #9c27b0; }
        
        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }
        .management-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .user-item, .event-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 15px;
        }
        .btn-danger {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
        .btn-warning {
            background: rgba(255, 152, 0, 0.1);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.2);
        }
        .btn-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            .management-grid {
                grid-template-columns: 1fr;
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
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="admin.php" class="active"><i class="fas fa-cog"></i> Admin Panel</a>
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-shield-alt"></i> Admin Control Panel</h1>
            <p>Manage users, events, and system settings</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card users">
                <i class="fas fa-users" style="font-size: 2.5rem; color: #2196f3; margin-bottom: 1rem;"></i>
                <h3><?= $systemStats['total_users'] ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card organizers">
                <i class="fas fa-user-tie" style="font-size: 2.5rem; color: #ff9800; margin-bottom: 1rem;"></i>
                <h3><?= $systemStats['total_organizers'] ?></h3>
                <p>Event Organizers</p>
            </div>
            <div class="stat-card events">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #4caf50; margin-bottom: 1rem;"></i>
                <h3><?= $systemStats['total_events'] ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card revenue">
                <i class="fas fa-chart-line" style="font-size: 2.5rem; color: #9c27b0; margin-bottom: 1rem;"></i>
                <h3><?= $systemStats['published_events'] ?></h3>
                <p>Published Events</p>
            </div>
        </div>

        <div class="management-grid">
            <div class="management-card">
                <h2><i class="fas fa-users"></i> User Management</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach (array_slice($allUsers, 0, 10) as $userItem): ?>
                        <div class="user-item">
                            <div>
                                <h4 style="margin: 0;"><?= htmlspecialchars(($userItem['first_name'] ?: '') . ' ' . ($userItem['last_name'] ?: '')) ?></h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    <?= htmlspecialchars($userItem['email']) ?>
                                </p>
                                <span class="role-badge role-<?= $userItem['role'] ?>"><?= ucfirst($userItem['role']) ?></span>
                            </div>
                            <div class="action-buttons">
                                <?php if ($userItem['role'] !== 'admin'): ?>
                                    <button class="btn btn-small btn-warning" onclick="changeUserRole(<?= $userItem['id'] ?>, 'organizer')">
                                        <i class="fas fa-user-tie"></i>
                                    </button>
                                    <button class="btn btn-small btn-danger" onclick="deleteUser(<?= $userItem['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($allUsers) > 10): ?>
                    <p style="text-align: center; margin-top: 1rem; color: var(--text-secondary);">
                        Showing 10 of <?= count($allUsers) ?> users
                    </p>
                <?php endif; ?>
            </div>

            <div class="management-card">
                <h2><i class="fas fa-calendar-alt"></i> Event Management</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach (array_slice($allEvents, 0, 10) as $eventItem): ?>
                        <div class="event-item">
                            <div>
                                <h4 style="margin: 0;"><?= htmlspecialchars($eventItem['title']) ?></h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    <?= date('M j, Y', strtotime($eventItem['date_start'])) ?>
                                </p>
                                <span class="status-badge status-<?= $eventItem['status'] ?>"><?= ucfirst($eventItem['status']) ?></span>
                            </div>
                            <div class="action-buttons">
                                <a href="event-details.php?id=<?= $eventItem['id'] ?>" class="btn btn-small btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($eventItem['status'] === 'draft'): ?>
                                    <button class="btn btn-small btn-success" onclick="publishEvent(<?= $eventItem['id'] ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-small btn-danger" onclick="deleteEvent(<?= $eventItem['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($allEvents) > 10): ?>
                    <p style="text-align: center; margin-top: 1rem; color: var(--text-secondary);">
                        Showing 10 of <?= count($allEvents) ?> events
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 3rem;">
            <div class="management-card">
                <h2><i class="fas fa-cogs"></i> System Actions</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <button class="btn btn-primary" onclick="exportData()">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    <button class="btn btn-warning" onclick="clearCache()">
                        <i class="fas fa-broom"></i> Clear Cache
                    </button>
                    <button class="btn btn-success" onclick="sendNotifications()">
                        <i class="fas fa-bell"></i> Send Notifications
                    </button>
                    <button class="btn btn-secondary" onclick="viewLogs()">
                        <i class="fas fa-file-alt"></i> View Logs
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function changeUserRole(userId, newRole) {
            if (confirm(`Change user role to ${newRole}?`)) {
                fetch('api/admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'change_user_role',
                        user_id: userId,
                        role: newRole
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch('api/admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_user',
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function publishEvent(eventId) {
            if (confirm('Publish this event?')) {
                fetch('api/events.php?id=' + eventId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: 'published'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                fetch('api/events.php?id=' + eventId, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function exportData() {
            alert('Data export functionality would be implemented here');
        }

        function clearCache() {
            alert('Cache clearing functionality would be implemented here');
        }

        function sendNotifications() {
            alert('Notification system would be implemented here');
        }

        function viewLogs() {
            alert('Log viewing functionality would be implemented here');
        }
    </script>
</body>
</html>