<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub - Premium Event Management & Ticketing</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-calendar-star"></i> EventHub
            </div>
            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <?php session_start(); if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <?php if ($_SESSION['role'] === 'organizer' || $_SESSION['role'] === 'admin'): ?>
                        <a href="create-event.php"><i class="fas fa-plus-circle"></i> Create Event</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Discover Amazing Events</h1>
            <p>Find and book tickets for the best events in your city. From concerts to conferences, we've got you covered.</p>
            
            <div class="search-bar">
                <div class="search-container">
                    <input type="text" id="search-input" placeholder="Search events...">
                    <select id="city-filter">
                        <option value="">All Cities</option>
                        <option value="New York">New York</option>
                        <option value="Los Angeles">Los Angeles</option>
                        <option value="Chicago">Chicago</option>
                        <option value="Miami">Miami</option>
                    </select>
                    <button class="search-btn" onclick="Events.searchEvents()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </section>

        <section class="filter-section">
            <div class="filter-tabs" id="category-filters">
                <div class="filter-tab active" data-category="">All Events</div>
                <div class="filter-tab" data-category="1"><i class="fas fa-music"></i> Music</div>
                <div class="filter-tab" data-category="2"><i class="fas fa-football-ball"></i> Sports</div>
                <div class="filter-tab" data-category="3"><i class="fas fa-laptop-code"></i> Technology</div>
                <div class="filter-tab" data-category="4"><i class="fas fa-briefcase"></i> Business</div>
                <div class="filter-tab" data-category="5"><i class="fas fa-palette"></i> Arts</div>
                <div class="filter-tab" data-category="6"><i class="fas fa-utensils"></i> Food</div>
            </div>
        </section>

        <section class="featured-events" id="featured-section">
            <h2><i class="fas fa-star"></i> Featured Events</h2>
            <div class="events-grid" id="featured-events"></div>
        </section>

        <section class="all-events">
            <h2><i class="fas fa-calendar-alt"></i> All Events</h2>
            <div class="events-grid" id="events-list"></div>
            <div class="loading" id="loading" style="display: none;">
                <div class="spinner"></div>
                <span style="margin-left: 1rem;">Loading events...</span>
            </div>
        </section>
    </main>

    <footer style="background: rgba(0,0,0,0.1); color: white; text-align: center; padding: 2rem; margin-top: 4rem;">
        <p>&copy; 2024 EventHub. All rights reserved. | <a href="#" style="color: #ffd700;">Privacy Policy</a> | <a href="#" style="color: #ffd700;">Terms of Service</a></p>
    </footer>

    <script src="assets/js/modules/events.js"></script>
    <script src="assets/js/modules/auth.js"></script>
    <script src="assets/js/modules/bookings.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>