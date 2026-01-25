<?php
require_once '../includes/config.php';

class User {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function register($email, $password, $firstName, $lastName, $phone = '', $role = 'user') {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("
                INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$email, $hashedPassword, $firstName, $lastName, $phone, $role])) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log('User::register - ' . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log('User::login - ' . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, email, first_name, last_name, phone, role, 
                       is_verified, created_at, last_login 
                FROM users WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('User::getById - ' . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        try {
            $stmt = $this->conn->query("
                SELECT id, email, first_name, last_name, role, 
                       is_verified, created_at, last_login 
                FROM users ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('User::getAll - ' . $e->getMessage());
            return [];
        }
    }

    public function emailExists($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('User::emailExists - ' . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($id, $firstName, $lastName, $phone = '') {
        try {
            $stmt = $this->conn->prepare("
                UPDATE users SET first_name = ?, last_name = ?, phone = ?, 
                               updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            return $stmt->execute([$firstName, $lastName, $phone, $id]);
        } catch (PDOException $e) {
            error_log('User::updateProfile - ' . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("
                UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            return $stmt->execute([$hashedPassword, $id]);
        } catch (PDOException $e) {
            error_log('User::updatePassword - ' . $e->getMessage());
            return false;
        }
    }

    public function updateLastLogin($id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE users SET last_login = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('User::updateLastLogin - ' . $e->getMessage());
            return false;
        }
    }

    public function verifyUser($id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE users SET is_verified = 1, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('User::verifyUser - ' . $e->getMessage());
            return false;
        }
    }

    public function updateRole($id, $role) {
        try {
            $validRoles = ['user', 'organizer', 'admin'];
            if (!in_array($role, $validRoles)) {
                return ['success' => false, 'message' => 'Invalid role'];
            }
            
            $stmt = $this->conn->prepare("
                UPDATE users SET role = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $result = $stmt->execute([$role, $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'User role updated successfully'];
            }
            
            return ['success' => false, 'message' => 'User not found'];
        } catch (PDOException $e) {
            error_log('User::updateRole - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getUserStats($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed') as total_bookings,
                    (SELECT COUNT(*) FROM events WHERE organizer_id = ?) as events_organized,
                    (SELECT COUNT(*) FROM favorites WHERE user_id = ?) as favorite_events
            ");
            $stmt->execute([$id, $id, $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('User::getUserStats - ' . $e->getMessage());
            return ['total_bookings' => 0, 'events_organized' => 0, 'favorite_events' => 0];
        }
    }

    public function getTotalCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getCountByRole($role) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->execute([$role]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getRecentUsers($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, first_name, last_name, email, role, created_at
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function delete($id) {
        try {
            // Note: In production, consider soft delete instead
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'User not found'];
        } catch (PDOException $e) {
            error_log('User::delete - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>