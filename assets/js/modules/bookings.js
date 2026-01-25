// Bookings module
const Bookings = {
    loadBookings: function() {
        fetch('/api/bookings.php')
            .then(response => response.json())
            .then(bookings => {
                const bookingsList = document.getElementById('bookings-list');
                if (bookingsList) {
                    bookingsList.innerHTML = '';
                    bookings.forEach(booking => {
                        const bookingCard = document.createElement('div');
                        bookingCard.className = 'event-card';
                        bookingCard.innerHTML = `
                            <h3>${booking.title}</h3>
                            <p>Date: ${booking.date}</p>
                            <p>Location: ${booking.location}</p>
                            <p>Quantity: ${booking.quantity}</p>
                            <p>Status: ${booking.status}</p>
                            ${booking.status === 'pending' ? `<button onclick="Bookings.cancelBooking(${booking.id})">Cancel</button>` : ''}
                        `;
                        bookingsList.appendChild(bookingCard);
                    });
                }
            })
            .catch(error => console.error('Error loading bookings:', error));
    },

    bookTicket: function(eventId, quantity) {
        return fetch('api/bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId, quantity })
        })
        .then(response => response.json());
    },

    cancelBooking: function(id) {
        return fetch(`/api/bookings.php?id=${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                this.loadBookings();
            });
    }
};

// Make it global
window.Bookings = Bookings;