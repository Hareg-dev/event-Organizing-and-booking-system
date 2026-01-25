<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/EventController.php';
require_once 'controllers/BookingController.php';

$authController = new AuthController();
$authController->requireRoles(['organizer', 'admin']);

$user = $authController->getCurrentUser();
$eventController = new EventController();
$bookingController = new BookingController();

// Get organizer's events
$organizerEvents = $eventController->getByOrganizer($user['id']);
$totalRevenue = 0;
$totalBookings = 0;

foreach ($organizerEvents as $event) {
    $bookings = $bookingController->getByEvent($event['id']);
    $totalBookings += count($bookings);
    foreach ($bookings as $booking) {
        if ($booking['status'] === 'confirmed') {
            $totalRevenue += $booking['total_amount'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .organizer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .organizer-header {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.2);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .organizer-header h1 {
            color: #ff9800;
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
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        .event-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .event-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 8px;
        }
        .create-event-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        .create-event-btn:hover {
            transform: scale(1.1);
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
                <a href="organizer.php" class="active"><i class="fas fa-calendar-plus"></i> My Events</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin.php"><i class="fas fa-cog"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="organizer-container">
        <div class="organizer-header">
            <h1><i class="fas fa-user-tie"></i> Organizer Dashboard</h1>
            <p>Manage your events and track performance</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: #4caf50; margin-bottom: 1rem;"></i>
                <h3><?= count($organizerEvents) ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-ticket-alt" style="font-size: 2.5rem; color: #2196f3; margin-bottom: 1rem;"></i>
                <h3><?= $totalBookings ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign" style="font-size: 2.5rem; color: #ff9800; margin-bottom: 1rem;"></i>
                <h3>$<?= number_format($totalRevenue, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line" style="font-size: 2.5rem; color: #9c27b0; margin-bottom: 1rem;"></i>
                <h3><?= count(array_filter($organizerEvents, fn($e) => $e['status'] === 'published')) ?></h3>
                <p>Published Events</p>
            </div>
        </div>

        <div class="section-header">
            <h2><i class="fas fa-calendar-alt"></i> My Events</h2>
            <a href="create-event.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Event
            </a>
        </div>

        <?php if (empty($organizerEvents)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-plus" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                <h3>No Events Yet</h3>
                <p>Create your first event to get started!</p>
                <a href="create-event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Event
                </a>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($organizerEvents as $event): ?>
                    <div class="event-card">
                        <?php if ($event['image_url']): ?>
                            <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($event['title']) ?></h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                            <i class="fas fa-calendar"></i> <?= date('M j, Y g:i A', strtotime($event['date_start'])) ?>
                        </p>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span class="status-badge status-<?= $event['status'] ?>">
                                <?= ucfirst($event['status']) ?>
                            </span>
                            <?php if ($event['is_featured']): ?>
                                <span class="featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php
                        $eventBookings = $bookingController->getByEvent($event['id']);
                        $eventRevenue = array_sum(array_column(array_filter($eventBookings, fn($b) => $b['status'] === 'confirmed'), 'total_amount'));
                        ?>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                            <span><i class="fas fa-ticket-alt"></i> <?= count($eventBookings) ?> bookings</span>
                            <span><i class="fas fa-dollar-sign"></i> $<?= number_format($eventRevenue, 2) ?></span>
                        </div>

                        <div class="event-actions">
                            <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-small btn-secondary">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn btn-small btn-primary" onclick="editEvent(<?= $event['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <?php if ($event['status'] === 'draft'): ?>
                                <button class="btn btn-small btn-success" onclick="publishEvent(<?= $event['id'] ?>)">
                                    <i class="fas fa-upload"></i> Publish
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-small btn-danger" onclick="deleteEvent(<?= $event['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <button class="create-event-btn" onclick="window.location.href='create-event.php'" title="Create New Event">
            <i class="fas fa-plus"></i>
        </button>
    </main>

    <script>
        function editEvent(eventId) {
            window.location.href = `create-event.php?edit=${eventId}`;
        }

        function publishEvent(eventId) {
            if (confirm('Are you sure you want to publish this event?')) {
                fetch(`/api/events.php?id=${eventId}`, {
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
                })
                .catch(error => {
                    alert('Error publishing event');
                });
            }
        }

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                fetch(`/api/events.php?id=${eventId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting event');
                });
            }
        }
    </script>
</body>
</html>