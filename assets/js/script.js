// Include modules
// Assuming modules are loaded via script tags in HTML

// Main script
document.addEventListener('DOMContentLoaded', function() {
    // Load events if on index page
    if (document.getElementById('events-list')) {
        Events.loadEvents();
    }

    // Load bookings if on dashboard
    if (document.getElementById('bookings-list')) {
        Bookings.loadBookings();
    }

    // Handle login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            Auth.login(email, password).then(data => {
                const messageDiv = document.getElementById('message');
                if (messageDiv) messageDiv.textContent = data.message;
                if (data.success) {
                    window.location.href = 'index.php';
                }
            });
        });
    }

    // Handle register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            Auth.register(email, password, role).then(data => {
                const messageDiv = document.getElementById('message');
                if (messageDiv) messageDiv.textContent = data.message;
                if (data.success) {
                    window.location.href = 'login.php';
                }
            });
        });
    }

    // Handle book form
    const bookForm = document.getElementById('book-form');
    if (bookForm) {
        bookForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const quantity = document.getElementById('quantity').value;
            const urlParams = new URLSearchParams(window.location.search);
            const eventId = urlParams.get('event_id');
            Bookings.bookTicket(eventId, quantity).then(data => {
                const messageDiv = document.getElementById('message');
                if (messageDiv) messageDiv.textContent = data.message;
                if (data.success) {
                    setTimeout(() => window.location.href = 'dashboard.php', 2000);
                }
            });
        });
    }
});