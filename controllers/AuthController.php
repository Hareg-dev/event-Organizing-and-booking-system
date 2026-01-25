<?php
require_once '../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($email, $password) {
        try {
            // Sanitize input
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            if (empty($password)) {
                return ['success' => false, 'message' => 'Password is required'];
            }
            
            $user = $this->userModel->login($email, $password);
            
            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['login_time'] = time();
                
                // Update last login
                $this->userModel->updateLastLogin($user['id']);
                
                return [
                    'success' => true, 
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'role' => $user['role']
                    ]
                ];
            } else {
                // Add delay to prevent brute force attacks
                sleep(1);
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
        } catch (Exception $e) {
            error_log('AuthController::login - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }

    public function register($data) {
        try {
            // Sanitize input
            $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
            $firstName = trim($data['first_name']);
            $lastName = trim($data['last_name']);
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            $role = isset($data['role']) ? $data['role'] : 'user';
            $password = $data['password'];
            
            // Validate input
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
            }
            
            if (strlen($firstName) < 2 || strlen($lastName) < 2) {
                return ['success' => false, 'message' => 'First and last name must be at least 2 characters long'];
            }
            
            // Check if user already exists
            if ($this->userModel->emailExists($email)) {
                return ['success' => false, 'message' => 'An account with this email already exists'];
            }
            
            // Validate role
            $validRoles = ['user', 'organizer'];
            if (!in_array($role, $validRoles)) {
                $role = 'user';
            }
            
            // Create user
            $userId = $this->userModel->register($email, $password, $firstName, $lastName, $phone, $role);
            
            if ($userId) {
                return [
                    'success' => true, 
                    'message' => 'Account created successfully! Please login to continue.',
                    'user_id' => $userId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create account. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log('AuthController::register - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }

    public function logout() {
        try {
            // Clear all session variables
            $_SESSION = array();
            
            // Destroy the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy the session
            session_destroy();
            
            return ['success' => true, 'message' => 'Logged out successfully'];
            
        } catch (Exception $e) {
            error_log('AuthController::logout - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during logout'];
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            return $this->userModel->getById($_SESSION['user_id']);
        } catch (Exception $e) {
            error_log('AuthController::getCurrentUser - ' . $e->getMessage());
            return null;
        }
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public function requireRole($requiredRole) {
        $this->requireLogin();
        
        $user = $this->getCurrentUser();
        if (!$user || $user['role'] !== $requiredRole) {
            header('Location: index.php');
            exit;
        }
    }

    public function requireRoles($requiredRoles) {
        $this->requireLogin();
        
        $user = $this->getCurrentUser();
        if (!$user || !in_array($user['role'], $requiredRoles)) {
            header('Location: index.php');
            exit;
        }
    }

    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    public function updateProfile($data) {
        try {
            if (!$this->isLoggedIn()) {
                return ['success' => false, 'message' => 'Not logged in'];
            }
            
            $userId = $_SESSION['user_id'];
            
            // Sanitize input
            $firstName = trim($data['first_name']);
            $lastName = trim($data['last_name']);
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            
            // Validate input
            if (strlen($firstName) < 2 || strlen($lastName) < 2) {
                return ['success' => false, 'message' => 'First and last name must be at least 2 characters long'];
            }
            
            if ($this->userModel->updateProfile($userId, $firstName, $lastName, $phone)) {
                // Update session variables
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
            
        } catch (Exception $e) {
            error_log('AuthController::updateProfile - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating profile'];
        }
    }

    public function changePassword($currentPassword, $newPassword) {
        try {
            if (!$this->isLoggedIn()) {
                return ['success' => false, 'message' => 'Not logged in'];
            }
            
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'New password must be at least 6 characters long'];
            }
            
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->getById($userId);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            if ($this->userModel->updatePassword($userId, $newPassword)) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
            
        } catch (Exception $e) {
            error_log('AuthController::changePassword - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password'];
        }
    }
}
?>