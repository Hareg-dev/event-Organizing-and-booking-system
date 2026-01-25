<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/EventController.php';

$authController = new AuthController();
$authController->requireLogin();

$user = $authController->getCurrentUser();
$eventController = new EventController();

$eventId = $_GET['event_id'] ?? null;
$ticketTypeId = $_GET['ticket_type_id'] ?? null;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .booking-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        .booking-card, .summary-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: fit-content;
        }
        .event-info {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .ticket-selector {
            margin: 2rem 0;
        }
        .ticket-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .ticket-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .ticket-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
        }
        .quantity-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .quantity-btn:hover {
            transform: scale(1.1);
        }
        .quantity-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .quantity-display {
            font-size: 1.2rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        .payment-section {
            background: rgba(76, 175, 80, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .payment-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #667eea;
        }
        .payment-method.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .summary-total {
            border-top: 2px solid #e1e5e9;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
                padding: 1rem;
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
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="booking-container">
        <div class="booking-card">
            <h1><i class="fas fa-ticket-alt"></i> Book Your Tickets</h1>
            
            <div class="event-info">
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p><i class="fas fa-calendar"></i> <?= $eventDate->format('M j, Y g:i A') ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></p>
                <?php if ($event['venue_name']): ?>
                    <p><i class="fas fa-building"></i> <?= htmlspecialchars($event['venue_name']) ?></p>
                <?php endif; ?>
            </div>

            <div id="message"></div>

            <div class="ticket-selector">
                <h3>Select Ticket Type</h3>
                <?php if (!empty($event['ticket_types'])): ?>
                    <?php foreach ($event['ticket_types'] as $index => $ticketType): ?>
                        <div class="ticket-option <?= $ticketTypeId == $ticketType['id'] ? 'selected' : ($index === 0 ? 'selected' : '') ?>" 
                             data-ticket-id="<?= $ticketType['id'] ?>" 
                             data-price="<?= $ticketType['price'] ?>"
                             data-available="<?= $ticketType['available_quantity'] ?>">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4 style="margin: 0 0 0.5rem;"><?= htmlspecialchars($ticketType['name']) ?></h4>
                                    <?php if ($ticketType['description']): ?>
                                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                            <?= htmlspecialchars($ticketType['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 600;">
                                        $<?= number_format($ticketType['price'], 2) ?>
                                    </div>
                                    <div style="color: var(--text-light); font-size: 0.9rem;">
                                        <?= $ticketType['available_quantity'] ?> available
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ticket-option selected" 
                         data-ticket-id="" 
                         data-price="<?= $event['price'] ?>"
                         data-available="<?= $event['available_tickets'] ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0;">General Admission</h4>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.2rem; font-weight: 600;">
                                    <?= $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'Free' ?>
                                </div>
                                <div style="color: var(--text-light); font-size: 0.9rem;">
                                    <?= $event['available_tickets'] ?> available
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="quantity-selector">
                <label style="font-weight: 600;">Quantity:</label>
                <button type="button" class="quantity-btn" id="decrease-qty">
                    <i class="fas fa-minus"></i>
                </button>
                <div class="quantity-display" id="quantity">1</div>
                <button type="button" class="quantity-btn" id="increase-qty">
                    <i class="fas fa-plus"></i>
                </button>
                <span style="margin-left: 1rem; color: var(--text-secondary);">
                    Max: <span id="max-quantity">1</span>
                </span>
            </div>

            <div class="payment-section">
                <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                <div class="payment-method selected" data-method="card">
                    <i class="fas fa-credit-card" style="font-size: 1.5rem; color: #667eea;"></i>
                    <div>
                        <h4 style="margin: 0;">Credit/Debit Card</h4>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Visa, Mastercard, American Express</p>
                    </div>
                </div>
                <div class="payment-method" data-method="paypal">
                    <i class="fab fa-paypal" style="font-size: 1.5rem; color: #0070ba;"></i>
                    <div>
                        <h4 style="margin: 0;">PayPal</h4>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Pay with your PayPal account</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <h3><i class="fas fa-receipt"></i> Booking Summary</h3>
            
            <div style="margin: 1.5rem 0;">
                <div class="summary-item">
                    <span>Event:</span>
                    <span style="font-weight: 600;"><?= htmlspecialchars($event['title']) ?></span>
                </div>
                <div class="summary-item">
                    <span>Date:</span>
                    <span><?= $eventDate->format('M j, Y') ?></span>
                </div>
                <div class="summary-item">
                    <span>Time:</span>
                    <span><?= $eventDate->format('g:i A') ?></span>
                </div>
                <div class="summary-item">
                    <span>Ticket Type:</span>
                    <span id="selected-ticket-name">General Admission</span>
                </div>
                <div class="summary-item">
                    <span>Quantity:</span>
                    <span id="summary-quantity">1</span>
                </div>
                <div class="summary-item">
                    <span>Unit Price:</span>
                    <span id="unit-price">$0.00</span>
                </div>
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>
                <div class="summary-item">
                    <span>Booking Fee (5%):</span>
                    <span id="booking-fee">$0.00</span>
                </div>
            </div>
            
            <div class="summary-total summary-item">
                <span>Total:</span>
                <span id="total-price">$0.00</span>
            </div>

            <button id="book-now-btn" class="btn btn-primary" style="width: 100%; margin-top: 2rem; height: 3.5rem; font-size: 1.1rem;">
                <i class="fas fa-lock"></i> Secure Booking
            </button>

            <div style="margin-top: 1rem; padding: 1rem; background: rgba(76, 175, 80, 0.1); border-radius: 10px; font-size: 0.9rem; color: var(--text-secondary);">
                <p style="margin: 0;"><i class="fas fa-shield-alt"></i> Secure SSL encryption</p>
                <p style="margin: 0.5rem 0 0;"><i class="fas fa-mobile-alt"></i> Instant mobile tickets</p>
            </div>
        </div>
    </main>

    <script>
        let selectedTicketId = null;
        let selectedPrice = 0;
        let quantity = 1;
        let maxQuantity = 1;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const firstTicket = document.querySelector('.ticket-option.selected');
            if (firstTicket) {
                selectTicket(firstTicket);
            }
            updateSummary();
        });

        // Ticket selection
        document.querySelectorAll('.ticket-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.ticket-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectTicket(this);
            });
        });

        function selectTicket(ticketElement) {
            selectedTicketId = ticketElement.dataset.ticketId;
            selectedPrice = parseFloat(ticketElement.dataset.price);
            maxQuantity = parseInt(ticketElement.dataset.available);
            
            document.getElementById('max-quantity').textContent = maxQuantity;
            document.getElementById('selected-ticket-name').textContent = ticketElement.querySelector('h4').textContent;
            
            // Reset quantity if it exceeds max
            if (quantity > maxQuantity) {
                quantity = Math.min(1, maxQuantity);
                document.getElementById('quantity').textContent = quantity;
            }
            
            updateQuantityButtons();
            updateSummary();
        }

        // Quantity controls
        document.getElementById('decrease-qty').addEventListener('click', function() {
            if (quantity > 1) {
                quantity--;
                document.getElementById('quantity').textContent = quantity;
                updateQuantityButtons();
                updateSummary();
            }
        });

        document.getElementById('increase-qty').addEventListener('click', function() {
            if (quantity < maxQuantity) {
                quantity++;
                document.getElementById('quantity').textContent = quantity;
                updateQuantityButtons();
                updateSummary();
            }
        });

        function updateQuantityButtons() {
            document.getElementById('decrease-qty').disabled = quantity <= 1;
            document.getElementById('increase-qty').disabled = quantity >= maxQuantity;
        }

        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        function updateSummary() {
            const subtotal = selectedPrice * quantity;
            const bookingFee = subtotal * 0.05;
            const total = subtotal + bookingFee;

            document.getElementById('summary-quantity').textContent = quantity;
            document.getElementById('unit-price').textContent = '$' + selectedPrice.toFixed(2);
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('booking-fee').textContent = '$' + bookingFee.toFixed(2);
            document.getElementById('total-price').textContent = '$' + total.toFixed(2);
        }

        // Booking submission
        document.getElementById('book-now-btn').addEventListener('click', async function() {
            const messageDiv = document.getElementById('message');
            const selectedPayment = document.querySelector('.payment-method.selected').dataset.method;
            
            const bookingData = {
                event_id: <?= $eventId ?>,
                ticket_type_id: selectedTicketId || null,
                quantity: quantity,
                payment_method: selectedPayment,
                notes: ''
            };

            try {
                const response = await fetch('api/bookings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.innerHTML = '<div class="message success"><i class="fas fa-check-circle"></i> Booking successful! Redirecting to your dashboard...</div>';
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
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