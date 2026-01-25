<?php
require_once '../includes/config.php';

class Event {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();
            
            $stmt = $this->conn->prepare("
                INSERT INTO events (title, description, short_description, date_start, date_end, 
                                  location, venue_name, address, city, country, max_tickets, 
                                  available_tickets, price, currency, organizer_id, category_id, 
                                  image_url, tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['title'], $data['description'], $data['short_description'] ?? '',
                $data['date_start'], $data['date_end'] ?? null, $data['location'],
                $data['venue_name'] ?? '', $data['address'] ?? '', $data['city'] ?? '',
                $data['country'] ?? '', $data['max_tickets'] ?? 100, $data['max_tickets'] ?? 100,
                $data['price'] ?? 0.00, $data['currency'] ?? 'USD', $data['organizer_id'],
                $data['category_id'] ?? null, $data['image_url'] ?? '', 
                json_encode($data['tags'] ?? [])
            ]);
            
            $eventId = $this->conn->lastInsertId();
            
            // Create default ticket type if none provided
            if (!isset($data['ticket_types']) || empty($data['ticket_types'])) {
                $this->createTicketType($eventId, [
                    'name' => 'General Admission',
                    'price' => $data['price'] ?? 0.00,
                    'quantity' => $data['max_tickets'] ?? 100
                ]);
            } else {
                foreach ($data['ticket_types'] as $ticketType) {
                    $this->createTicketType($eventId, $ticketType);
                }
            }
            
            $this->conn->commit();
            return $eventId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getAllPublished($filters = []) {
        $sql = "SELECT e.*, c.name as category_name, u.first_name, u.last_name,
                       (SELECT AVG(rating) FROM reviews WHERE event_id = e.id) as avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE event_id = e.id) as review_count
                FROM events e 
                LEFT JOIN categories c ON e.category_id = c.id
                LEFT JOIN users u ON e.organizer_id = u.id
                WHERE e.status = 'published' AND e.date_start > datetime('now')";
        
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND e.category_id = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['city'])) {
            $sql .= " AND e.city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY e.is_featured DESC, e.date_start ASC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT e.*, c.name as category_name, u.first_name, u.last_name, u.email,
                   (SELECT AVG(rating) FROM reviews WHERE event_id = e.id) as avg_rating,
                   (SELECT COUNT(*) FROM reviews WHERE event_id = e.id) as review_count
            FROM events e 
            LEFT JOIN categories c ON e.category_id = c.id
            LEFT JOIN users u ON e.organizer_id = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($event) {
            $event['ticket_types'] = $this->getTicketTypes($id);
            $event['tags'] = json_decode($event['tags'] ?? '[]', true);
        }
        
        return $event;
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE events SET title = ?, description = ?, short_description = ?, 
                            date_start = ?, date_end = ?, location = ?, venue_name = ?,
                            address = ?, city = ?, country = ?, max_tickets = ?,
                            price = ?, currency = ?, category_id = ?, image_url = ?,
                            tags = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['title'], $data['description'], $data['short_description'] ?? '',
            $data['date_start'], $data['date_end'] ?? null, $data['location'],
            $data['venue_name'] ?? '', $data['address'] ?? '', $data['city'] ?? '',
            $data['country'] ?? '', $data['max_tickets'], $data['price'],
            $data['currency'] ?? 'USD', $data['category_id'] ?? null,
            $data['image_url'] ?? '', json_encode($data['tags'] ?? []),
            $data['status'] ?? 'draft', $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByOrganizer($organizerId) {
        $stmt = $this->conn->prepare("
            SELECT e.*, c.name as category_name,
                   (SELECT COUNT(*) FROM bookings WHERE event_id = e.id AND status = 'confirmed') as bookings_count,
                   (SELECT SUM(total_price) FROM bookings WHERE event_id = e.id AND status = 'confirmed') as revenue
            FROM events e 
            LEFT JOIN categories c ON e.category_id = c.id
            WHERE e.organizer_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTicketType($eventId, $data) {
        $stmt = $this->conn->prepare("
            INSERT INTO ticket_types (event_id, name, description, price, quantity, 
                                    available_quantity, sale_start, sale_end)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $eventId, $data['name'], $data['description'] ?? '',
            $data['price'], $data['quantity'], $data['quantity'],
            $data['sale_start'] ?? null, $data['sale_end'] ?? null
        ]);
    }

    public function getTicketTypes($eventId) {
        $stmt = $this->conn->prepare("SELECT * FROM ticket_types WHERE event_id = ? ORDER BY price ASC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTicketAvailability($ticketTypeId, $quantity) {
        $stmt = $this->conn->prepare("UPDATE ticket_types SET available_quantity = available_quantity - ? WHERE id = ?");
        return $stmt->execute([$quantity, $ticketTypeId]);
    }

    public function getFeatured($limit = 6) {
        $stmt = $this->conn->prepare("
            SELECT e.*, c.name as category_name
            FROM events e 
            LEFT JOIN categories c ON e.category_id = c.id
            WHERE e.status = 'published' AND e.is_featured = 1 AND e.date_start > datetime('now')
            ORDER BY e.date_start ASC LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($filters = []) {
        return $this->getAllPublished($filters);
    }

    public function getTotalCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as count FROM events");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getAllForAdmin() {
        try {
            $stmt = $this->conn->query("
                SELECT e.*, c.name as category_name, 
                       u.first_name || ' ' || u.last_name as organizer_name
                FROM events e 
                LEFT JOIN categories c ON e.category_id = c.id 
                LEFT JOIN users u ON e.organizer_id = u.id 
                ORDER BY e.created_at DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getRecentEvents($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT e.*, u.first_name || ' ' || u.last_name as organizer_name
                FROM events e 
                LEFT JOIN users u ON e.organizer_id = u.id 
                ORDER BY e.created_at DESC 
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }