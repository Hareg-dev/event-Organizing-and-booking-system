<?php
session_start();
require_once 'controllers/EventController.php';

$eventController = new EventController();
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    header('Location: index.php');
    exit;
}

$event = $eventController->getById($eventId);
if (!$event) {
    header('Location: index.php');
    exit;
}

$eventDate = new DateTime($event['date_start']);
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .event-hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        url('<?= $event['image_url'] ?: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 400"><rect fill="%23667eea" width="1200" height="400"/></svg>' ?>');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
        }
        .event-content {
            background: white;
            margin: -100px auto 0;
            max-width: 1000px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }
        .event-header {
            padding: 2rem;
            border-bottom: 1px solid #eee;
        }
        .event-body {
            padding: 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }
        .ticket-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            border: 2px solid #e9ecef;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .ticket-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .price-tag {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .event-body {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .event-hero {
                height: 250px;
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
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="event-hero">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem; width: 100%;">
            <div style="max-width: 600px;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">
                    <?= htmlspecialchars($event['title']) ?>
                </h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">
                    <?= htmlspecialchars($event['short_description'] ?: substr($event['description'], 0, 150) . '...') ?>
                </p>
            </div>
        </div>
    </div>

    <main style="padding: 0 2rem 4rem;">
        <div class="event-content">
            <div class="event-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <?= $event['category_name'] ? strtoupper($event['category_name']) : 'GENERAL' ?>
                        </div>
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; color: #666;">
                            <span><i class="fas fa-calendar"></i> <?= $eventDate->format('M j, Y') ?></span>
                            <span><i class="fas fa-clock"></i> <?= $eventDate->format('g:i A') ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                            <?php if ($event['avg_rating']): ?>
                                <span><i class="fas fa-star" style="color: #ffd700;"></i> <?= number_format($event['avg_rating'], 1) ?> (<?= $event['review_count'] ?> reviews)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="price-tag">
                            <?= $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'Free' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="event-body">
                <div class="event-details">
                    <h2 style="margin-bottom: 1rem;">About This Event</h2>
                    <div style="line-height: 1.8; color: #555;">
                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                    </div>

                    <?php if ($event['venue_name'] || $event['address']): ?>
                        <h3 style="margin: 2rem 0 1rem;">Venue Information</h3>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                            <?php if ($event['venue_name']): ?>
                                <h4 style="margin: 0 0 0.5rem;"><?= htmlspecialchars($event['venue_name']) ?></h4>
                            <?php endif; ?>
                            <?php if ($event['address']): ?>
                                <p style="margin: 0; color: #666;">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= htmlspecialchars($event['address']) ?>
                                    <?php if ($event['city']): ?>, <?= htmlspecialchars($event['city']) ?><?php endif; ?>
                                    <?php if ($event['country']): ?>, <?= htmlspecialchars($event['country']) ?><?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <h3 style="margin: 2rem 0 1rem;">Organizer</h3>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; background: var(--primary-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                            <?= strtoupper(substr($event['first_name'] ?: 'O', 0, 1)) ?>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?= htmlspecialchars(($event['first_name'] ?: '') . ' ' . ($event['last_name'] ?: '')) ?></h4>
                            <p style="margin: 0; color: #666;"><?= htmlspecialchars($event['email']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="booking-section">
                    <div style="position: sticky; top: 2rem;">
                        <h3 style="margin-bottom: 1.5rem;">Book Your Tickets</h3>
                        
                        <?php if (isset($event['ticket_types']) && !empty($event['ticket_types'])): ?>
                            <?php foreach ($event['ticket_types'] as $ticketType): ?>
                                <div class="ticket-card">
                                    <h4 style="margin: 0 0 0.5rem;"><?= htmlspecialchars($ticketType['name']) ?></h4>
                                    <?php if ($ticketType['description']): ?>
                                        <p style="margin: 0 0 1rem; color: #666; font-size: 0.9rem;">
                                            <?= htmlspecialchars($ticketType['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 1.2rem; font-weight: 600;">
                                            $<?= number_format($ticketType['price'], 2) ?>
                                        </span>
                                        <span style="color: #666; font-size: 0.9rem;">
                                            <?= $ticketType['available_quantity'] ?> available
                                        </span>
                                    </div>
                                    <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" 
                                            onclick="bookTicket(<?= $event['id'] ?>, <?= $ticketType['id'] ?>)">
                                        Select Tickets
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="ticket-card">
                                <h4 style="margin: 0 0 0.5rem;">General Admission</h4>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 1.2rem; font-weight: 600;">
                                        <?= $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'Free' ?>
                                    </span>
                                    <span style="color: #666; font-size: 0.9rem;">
                                        <?= $event['available_tickets'] ?> available
                                    </span>
                                </div>
                                <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" 
                                        onclick="bookTicket(<?= $event['id'] ?>)">
                                    Book Now
                                </button>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; color: #666;">
                            <p style="margin: 0;"><i class="fas fa-shield-alt"></i> Secure booking with instant confirmation</p>
                            <p style="margin: 0.5rem 0 0;"><i class="fas fa-mobile-alt"></i> Mobile tickets available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function bookTicket(eventId, ticketTypeId = null) {
            <?php if ($isLoggedIn): ?>
                const url = ticketTypeId ? 
                    `book.php?event_id=${eventId}&ticket_type_id=${ticketTypeId}` : 
                    `book.php?event_id=${eventId}`;
                window.location.href = url;
            <?php else: ?>
                if (confirm('You need to login to book tickets. Would you like to login now?')) {
                    const redirect = ticketTypeId ? 
                        `book.php?event_id=${eventId}&ticket_type_id=${ticketTypeId}` : 
                        `book.php?event_id=${eventId}`;
                    window.location.href = `login.php?redirect=${encodeURIComponent(redirect)}`;
                }
            <?php endif; ?>
        }
    </script>
</body>
</html>