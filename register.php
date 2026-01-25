<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-gradient);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h1 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 2rem;
            font-weight: 700;
        }
        .auth-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .form-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 1;
        }
        .form-group input, .form-group select {
            padding-left: 3rem;
            height: 3.5rem;
            border-radius: 12px;
            border: 2px solid #e1e5e9;
            transition: all 0.3s ease;
            font-size: 1rem;
            width: 100%;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .role-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .role-option {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .role-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .role-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        .role-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
            color: #667eea;
        }
        .role-option h4 {
            margin: 0 0 0.25rem;
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        .role-option p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .btn-auth {
            width: 100%;
            height: 3.5rem;
            background: var(--secondary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 1rem 0;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(240, 147, 251, 0.4);
        }
        .auth-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e5e9;
        }
        .auth-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }
        .message.error {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
        .message.success {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="font-size: 1.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-calendar-star"></i> EventHub
                </div>
                <h1>Join EventHub</h1>
                <p>Create your account and start discovering amazing events</p>
            </div>

            <div id="message"></div>

            <form id="register-form">
                <div class="form-row">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Email address" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-phone"></i>
                    <input type="tel" id="phone" name="phone" placeholder="Phone number (optional)">
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password (min 6 characters)" required minlength="6">
                </div>

                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>

                <div style="margin: 1.5rem 0;">
                    <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Choose Your Account Type</h4>
                    <div class="role-selector">
                        <div class="role-option selected" data-role="user">
                            <i class="fas fa-user"></i>
                            <h4>Event Attendee</h4>
                            <p>Browse and book events</p>
                        </div>
                        <div class="role-option" data-role="organizer">
                            <i class="fas fa-calendar-plus"></i>
                            <h4>Event Organizer</h4>
                            <p>Create and manage events</p>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="role" name="role" value="user">

                <button type="submit" class="btn-auth">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('role').value = this.dataset.role;
            });
        });

        // Form validation
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const messageDiv = document.getElementById('message');
            
            // Client-side validation
            if (data.password !== data.confirm_password) {
                messageDiv.innerHTML = '<div class="message error"><i class="fas fa-exclamation-circle"></i> Passwords do not match</div>';
                return;
            }
            
            if (data.password.length < 6) {
                messageDiv.innerHTML = '<div class="message error"><i class="fas fa-exclamation-circle"></i> Password must be at least 6 characters</div>';
                return;
            }
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'register',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="message success"><i class="fas fa-check-circle"></i> Account created successfully! Redirecting to login...</div>';
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    messageDiv.innerHTML = `<div class="message error"><i class="fas fa-exclamation-circle"></i> ${result.message}</div>`;
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="message error"><i class="fas fa-exclamation-triangle"></i> Connection error. Please try again.</div>';
            }
        });

        // Real-time password confirmation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#f44336';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });
    </script>
</body>
</html>