<?php
session_start();
require_once 'controllers/AuthController.php';
require_once 'controllers/EventController.php';

$authController = new AuthController();
$authController->requireRoles(['organizer', 'admin']);

$user = $authController->getCurrentUser();
$eventController = new EventController();
$categories = $eventController->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .create-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .create-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .ticket-types {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .ticket-type {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e1e5e9;
        }
        .ticket-type:last-child {
            margin-bottom: 0;
        }
        .btn-add-ticket {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border: 2px dashed #667eea;
            padding: 1rem;
            border-radius: 10px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-add-ticket:hover {
            background: rgba(102, 126, 234, 0.2);
        }
        .btn-remove {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            float: right;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .create-container {
                padding: 1rem;
            }
            .create-card {
                padding: 2rem;
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
                <a href="create-event.php" class="active"><i class="fas fa-plus-circle"></i> Create Event</a>
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="create-container">
        <div class="create-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1><i class="fas fa-calendar-plus"></i> Create New Event</h1>
                <p style="color: var(--text-secondary);">Fill in the details to create your amazing event</p>
            </div>

            <div id="message"></div>

            <form id="create-event-form">
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter event title">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Base Price ($)</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" value="0" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_description">Short Description</label>
                    <input type="text" id="short_description" name="short_description" maxlength="150" placeholder="Brief description for event cards">
                </div>

                <div class="form-group">
                    <label for="description">Full Description *</label>
                    <textarea id="description" name="description" rows="4" required placeholder="Detailed event description"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_start">Start Date & Time *</label>
                        <input type="datetime-local" id="date_start" name="date_start" required>
                    </div>
                    <div class="form-group">
                        <label for="date_end">End Date & Time</label>
                        <input type="datetime-local" id="date_end" name="date_end">
                    </div>
                </div>

                <div class="form-group">
                    <label for="venue_name">Venue Name</label>
                    <input type="text" id="venue_name" name="venue_name" placeholder="Event venue name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="Street address">
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" required placeholder="City">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" placeholder="Country" value="USA">
                    </div>
                    <div class="form-group">
                        <label for="max_tickets">Max Tickets</label>
                        <input type="number" id="max_tickets" name="max_tickets" min="1" value="100" placeholder="100">
                    </div>
                </div>

                <div class="ticket-types">
                    <h3><i class="fas fa-ticket-alt"></i> Ticket Types</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">Create different ticket types with varying prices</p>
                    
                    <div id="ticket-types-container">
                        <div class="ticket-type">
                            <button type="button" class="btn-remove" onclick="removeTicketType(this)" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ticket Name</label>
                                    <input type="text" name="ticket_types[0][name]" placeholder="General Admission" value="General Admission">
                                </div>
                                <div class="form-group">
                                    <label>Price ($)</label>
                                    <input type="number" name="ticket_types[0][price]" min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" name="ticket_types[0][quantity]" min="1" value="100">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" name="ticket_types[0][description]" placeholder="Ticket description">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-add-ticket" onclick="addTicketType()">
                        <i class="fas fa-plus"></i> Add Another Ticket Type
                    </button>
                </div>

                <div class="form-group">
                    <label for="image_url">Event Image URL</label>
                    <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" style="width: auto; margin-right: 0.5rem;">
                            Featured Event
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; height: 3.5rem; font-size: 1.1rem;">
                    <i class="fas fa-calendar-plus"></i> Create Event
                </button>
            </form>
        </div>
    </main>

    <script>
        let ticketTypeCount = 1;

        function addTicketType() {
            const container = document.getElementById('ticket-types-container');
            const ticketType = document.createElement('div');
            ticketType.className = 'ticket-type';
            ticketType.innerHTML = `
                <button type="button" class="btn-remove" onclick="removeTicketType(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ticket Name</label>
                        <input type="text" name="ticket_types[${ticketTypeCount}][name]" placeholder="VIP, Early Bird, etc.">
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="ticket_types[${ticketTypeCount}][price]" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="ticket_types[${ticketTypeCount}][quantity]" min="1">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="ticket_types[${ticketTypeCount}][description]" placeholder="Ticket description">
                    </div>
                </div>
            `;
            container.appendChild(ticketType);
            ticketTypeCount++;
            
            // Show remove buttons when there's more than one ticket type
            updateRemoveButtons();
        }

        function removeTicketType(button) {
            button.parentElement.remove();
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const ticketTypes = document.querySelectorAll('.ticket-type');
            ticketTypes.forEach((type, index) => {
                const removeBtn = type.querySelector('.btn-remove');
                removeBtn.style.display = ticketTypes.length > 1 ? 'block' : 'none';
            });
        }

        // Form submission
        document.getElementById('create-event-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key.includes('ticket_types')) {
                    // Handle ticket types array
                    if (!data.ticket_types) data.ticket_types = [];
                    const match = key.match(/ticket_types\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const index = parseInt(match[1]);
                        const field = match[2];
                        if (!data.ticket_types[index]) data.ticket_types[index] = {};
                        data.ticket_types[index][field] = value;
                    }
                } else {
                    data[key] = value;
                }
            }
            
            // Clean up ticket types array
            data.ticket_types = data.ticket_types.filter(type => type && type.name);
            
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch('api/events.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="message success"><i class="fas fa-check-circle"></i> Event created successfully! Redirecting...</div>';
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

        // Set minimum date to today
        document.getElementById('date_start').min = new Date().toISOString().slice(0, 16);
        document.getElementById('date_end').min = new Date().toISOString().slice(0, 16);
    </script>
</body>
</html>