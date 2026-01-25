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
    <title>Login - EventHub</title>
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
            max-width: 450px;
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
            background: var(--primary-gradient);
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
        .form-group input {
            padding-left: 3rem;
            height: 3.5rem;
            border-radius: 12px;
            border: 2px solid #e1e5e9;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .form-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-auth {
            width: 100%;
            height: 3.5rem;
            background: var(--primary-gradient);
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
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
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
        .demo-accounts {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .demo-accounts h4 {
            margin: 0 0 0.5rem;
            color: #667eea;
        }
        .demo-accounts div {
            margin: 0.25rem 0;
            color: var(--text-secondary);
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
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue</p>
            </div>

            <div id="message"></div>

            <form id="login-form">
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Email address" required>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-auth">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="demo-accounts">
                <h4><i class="fas fa-user-cog"></i> Demo Accounts</h4>
                <div><strong>Admin:</strong> admin@eventhub.com / admin123</div>
                <div><strong>Organizer:</strong> organizer@eventhub.com / organizer123</div>
                <div><strong>User:</strong> user@eventhub.com / user123</div>
            </div>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Create one here</a></p>
                <p><a href="index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.innerHTML = '<div class="message success"><i class="fas fa-check-circle"></i> Login successful! Redirecting...</div>';
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    messageDiv.innerHTML = `<div class="message error"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="message error"><i class="fas fa-exclamation-triangle"></i> Connection error. Please try again.</div>';
            }
        });

        // Demo account quick login
        document.querySelectorAll('.demo-accounts div').forEach(div => {
            div.style.cursor = 'pointer';
            div.addEventListener('click', function() {
                const text = this.textContent;
                if (text.includes('admin@eventhub.com')) {
                    document.getElementById('email').value = 'admin@eventhub.com';
                    document.getElementById('password').value = 'admin123';
                } else if (text.includes('organizer@eventhub.com')) {
                    document.getElementById('email').value = 'organizer@eventhub.com';
                    document.getElementById('password').value = 'organizer123';
                } else if (text.includes('user@eventhub.com')) {
                    document.getElementById('email').value = 'user@eventhub.com';
                    document.getElementById('password').value = 'user123';
                }
            });
        });
    </script>
</body>
</html>