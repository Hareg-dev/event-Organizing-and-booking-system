// Auth module
const Auth = {
    login: function(email, password) {
        return fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', email, password })
        })
        .then(response => response.json());
    },

    register: function(email, password, role) {
        return fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'register', email, password, role })
        })
        .then(response => response.json());
    },

    logout: function() {
        return fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'logout' })
        })
        .then(response => response.json());
    }
};

// Make it global
window.Auth = Auth;