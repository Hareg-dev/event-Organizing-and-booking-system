// Enhanced Events Module with Modern Features
const Events = {
    currentFilters: {
        category: '',
        city: '',
        search: ''
    },
    
    init() {
        this.setupEventListeners();
        this.loadFeaturedEvents();
        this.loadEvents();
    },
    
    setupEventListeners() {
        // Category filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                e.target.classList.add('active');
                this.currentFilters.category = e.target.dataset.category;
                this.loadEvents();
            });
        });
        
        // Search input with debounce
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.currentFilters.search = e.target.value;
                    this.loadEvents();
                }, 300);
            });
        }
        
        // City filter
        const cityFilter = document.getElementById('city-filter');
        if (cityFilter) {
            cityFilter.addEventListener('change', (e) => {
                this.currentFilters.city = e.target.value;
                this.loadEvents();
            });
        }
    },
    
    async loadFeaturedEvents() {
        try {
            const response = await fetch('api/events.php?featured=1&limit=6');
            const events = await response.json();
            this.renderEvents(events, 'featured-events');
        } catch (error) {
            console.error('Error loading featured events:', error);
        }
    },
    
    async loadEvents() {
        const loadingEl = document.getElementById('loading');
        if (loadingEl) loadingEl.style.display = 'flex';
        
        try {
            const params = new URLSearchParams();
            if (this.currentFilters.category) params.append('category', this.currentFilters.category);
            if (this.currentFilters.city) params.append('city', this.currentFilters.city);
            if (this.currentFilters.search) params.append('search', this.currentFilters.search);
            
            const response = await fetch(`api/events.php?${params.toString()}`);
            const events = await response.json();
            this.renderEvents(events, 'events-list');
        } catch (error) {
            console.error('Error loading events:', error);
            this.showError('Failed to load events. Please try again.');
        } finally {
            if (loadingEl) loadingEl.style.display = 'none';
        }
    },
    
    renderEvents(events, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        if (!events || events.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: white; padding: 2rem;">No events found.</div>';
            return;
        }
        
        container.innerHTML = events.map(event => this.createEventCard(event)).join('');
    },
    
    createEventCard(event) {
        const eventDate = new Date(event.date_start);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const formattedTime = eventDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const price = parseFloat(event.price || 0);
        const priceDisplay = price === 0 ? 'Free' : `$${price.toFixed(2)}`;
        
        const rating = event.avg_rating ? parseFloat(event.avg_rating).toFixed(1) : null;
        const ratingStars = rating ? '★'.repeat(Math.floor(rating)) + '☆'.repeat(5 - Math.floor(rating)) : '';
        
        return `
            <div class="event-card" data-event-id="${event.id}">
                <div class="event-image">
                    ${event.image_url ? 
                        `<img src="${event.image_url}" alt="${event.title}" onerror="this.style.display='none'">` : 
                        '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: white; font-size: 3rem;"><i class="fas fa-calendar-alt"></i></div>'
                    }
                    ${event.is_featured ? '<div class="event-badge">Featured</div>' : ''}
                </div>
                <div class="event-content">
                    <div class="event-category">${event.category_name || 'General'}</div>
                    <h3 class="event-title">${event.title}</h3>
                    <div class="event-meta">
                        <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                        <span><i class="fas fa-clock"></i> ${formattedTime}</span>
                        <span><i class="fas fa-map-marker-alt"></i> ${event.city || event.location}</span>
                    </div>
                    ${rating ? `<div class="event-rating">${ratingStars} (${event.review_count} reviews)</div>` : ''}
                    <p class="event-description">${event.short_description || (event.description ? event.description.substring(0, 100) + '...' : '')}</p>
                    <div class="event-price">${priceDisplay}</div>
                    <div class="event-actions">
                        <button class="btn btn-primary" onclick="Events.viewEvent(${event.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn btn-secondary" onclick="Events.bookEvent(${event.id})">
                            <i class="fas fa-ticket-alt"></i> Book Now
                        </button>
                    </div>
                </div>
            </div>
        `;
    },
    
    searchEvents() {
        const searchInput = document.getElementById('search-input');
        const cityFilter = document.getElementById('city-filter');
        
        this.currentFilters.search = searchInput?.value || '';
        this.currentFilters.city = cityFilter?.value || '';
        
        this.loadEvents();
    },
    
    viewEvent(id) {
        window.location.href = `event-details.php?id=${id}`;
    },
    
    bookEvent(id) {
        // Check if user is logged in
        fetch('api/auth.php?action=check')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    window.location.href = `book.php?event_id=${id}`;
                } else {
                    if (confirm('You need to login to book tickets. Would you like to login now?')) {
                        window.location.href = `login.php?redirect=book.php?event_id=${id}`;
                    }
                }
            })
            .catch(() => {
                window.location.href = `login.php?redirect=book.php?event_id=${id}`;
            });
    },
    
    showError(message) {
        this.showNotification(message, 'error');
    },
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    },
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;
        
        // Add notification styles if not already present
        if (!document.querySelector('.notification-styles')) {
            const styles = document.createElement('style');
            styles.className = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    color: white;
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    animation: slideIn 0.3s ease;
                }
                .notification-success { background: #4CAF50; }
                .notification-error { background: #f44336; }
                .notification-info { background: #2196F3; }
                .notification button {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 1.2rem;
                    cursor: pointer;
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); }
                    to { transform: translateX(0); }
                }
            `;
            document.head.appendChild(styles);
        }
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
};

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Events.init());
} else {
    Events.init();
}

// Make it global
window.Events = Events;