<?php
require_once '../includes/config.php';

class Favorite {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function add($userId, $eventId) {
        try {
            // Check if already exists
            if ($this->isFavorite($userId, $eventId)) {
                return ['success' => false, 'message' => 'Event already in favorites'];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO favorites (user_id, event_id, created_at) 
                VALUES (?, ?, datetime('now'))
            ");
            
            $result = $stmt->execute([$userId, $eventId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Event added to favorites'];
            }
            
            return ['success' => false, 'message' => 'Failed to add to favorites'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function remove($userId, $eventId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM favorites WHERE user_id = ? AND event_id = ?");
            $result = $stmt->execute([$userId, $eventId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Event removed from favorites'];
            }
            
            return ['success' => false, 'message' => 'Event not in favorites'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getByUser($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT f.*, e.title, e.date_start, e.image_url, e.location 
                FROM favorites f 
                JOIN events e ON f.event_id = e.id 
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function isFavorite($userId, $eventId) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCount($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>