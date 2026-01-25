<?php
require_once '../includes/config.php';

class Review {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reviews (user_id, event_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, datetime('now'))
            ");
            
            $result = $stmt->execute([
                $data['user_id'],
                $data['event_id'],
                $data['rating'],
                $data['comment']
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Review created successfully',
                    'review_id' => $this->conn->lastInsertId()
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create review'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getByEvent($eventId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.event_id = ? 
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$eventId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getByUser($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT r.*, e.title as event_title 
                FROM reviews r 
                JOIN events e ON r.event_id = e.id 
                WHERE r.user_id = ? 
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function delete($reviewId, $userId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$reviewId, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Review deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Review not found or unauthorized'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>