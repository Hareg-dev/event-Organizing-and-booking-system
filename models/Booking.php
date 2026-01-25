<?php
require_once '../includes/config.php';

class Booking {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();
            
            // Check ticket availability
            $ticketType = $this->getTicketType($data['ticket_type_id']);
            if (!$ticketType || $ticketType['available_quantity'] < $data['quantity']) {
                throw new Exception('Insufficient tickets available');
            }
            
            // Generate unique booking reference
            $bookingRef = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            
            // Calculate pricing
            $unitPrice = $ticketType['price'];
            $subtotal = $unitPrice * $data['quantity'];
            $bookingFee = $subtotal * 0.05; // 5% booking fee
            $totalPrice = $subtotal + $bookingFee;
            
            // Create booking
            $stmt = $this->conn->prepare("
                INSERT INTO bookings (booking_reference, user_id, event_id, ticket_type_id, 
                                    quantity, unit_price, total_price, booking_fee, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $bookingRef, $data['user_id'], $data['event_id'], $data['ticket_type_id'],
                $data['quantity'], $unitPrice, $totalPrice, $bookingFee, $data['notes'] ?? ''
            ]);
            
            if ($result) {
                // Update ticket availability
                $this->updateTicketAvailability($data['ticket_type_id'], $data['quantity']);
                $this->conn->commit();
                return $this->conn->lastInsertId();
            }
            
            throw new Exception('Failed to create booking');
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getByUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT b.*, e.title, e.date_start, e.location, e.venue_name, 
                   tt.name as ticket_type_name, u.first_name, u.last_name
            FROM bookings b 
            JOIN events e ON b.event_id = e.id
            JOIN ticket_types tt ON b.ticket_type_id = tt.id
            JOIN users u ON e.organizer_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("
            SELECT b.*, e.title, e.date_start, e.location, e.venue_name,
                   tt.name as ticket_type_name, u.first_name, u.last_name, u.email
            FROM bookings b 
            JOIN events e ON b.event_id = e.id
            JOIN ticket_types tt ON b.ticket_type_id = tt.id
            JOIN users u ON b.user_id = u.id
            WHERE b.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cancel($id, $userId = null) {
        try {
            $this->conn->beginTransaction();
            
            // Get booking details
            $booking = $this->getById($id);
            if (!$booking) {
                throw new Exception('Booking not found');
            }
            
            if ($userId && $booking['user_id'] != $userId) {
                throw new Exception('Unauthorized');
            }
            
            // Update booking status
            $stmt = $this->conn->prepare("
                UPDATE bookings SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // Restore ticket availability
                $this->restoreTicketAvailability($booking['ticket_type_id'], $booking['quantity']);
                $this->conn->commit();
                return true;
            }
            
            throw new Exception('Failed to cancel booking');
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function confirm($id) {
        $stmt = $this->conn->prepare("
            UPDATE bookings SET status = 'confirmed', payment_status = 'paid', 
                              updated_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function getByEvent($eventId) {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.first_name, u.last_name, u.email, tt.name as ticket_type_name
            FROM bookings b 
            JOIN users u ON b.user_id = u.id
            JOIN ticket_types tt ON b.ticket_type_id = tt.id
            WHERE b.event_id = ? AND b.status != 'cancelled'
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventStats($eventId) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(quantity) as total_tickets_sold,
                SUM(CASE WHEN status = 'confirmed' THEN total_price ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings
            FROM bookings 
            WHERE event_id = ? AND status != 'cancelled'
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTicketType($ticketTypeId) {
        $stmt = $this->conn->prepare("SELECT * FROM ticket_types WHERE id = ?");
        $stmt->execute([$ticketTypeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateTicketAvailability($ticketTypeId, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE ticket_types SET available_quantity = available_quantity - ? 
            WHERE id = ? AND available_quantity >= ?
        ");
        return $stmt->execute([$quantity, $ticketTypeId, $quantity]);
    }

    private function restoreTicketAvailability($ticketTypeId, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE ticket_types SET available_quantity = available_quantity + ? 
            WHERE id = ?
        ");
        return $stmt->execute([$quantity, $ticketTypeId]);
    }

    public function generateTicket($bookingId) {
        $booking = $this->getById($bookingId);
        if (!$booking || $booking['status'] !== 'confirmed') {
            return false;
        }
        
        // Generate QR code data
        $qrData = json_encode([
            'booking_id' => $bookingId,
            'reference' => $booking['booking_reference'],
            'event_id' => $booking['event_id'],
            'quantity' => $booking['quantity']
        ]);
        
        return [
            'booking' => $booking,
            'qr_data' => base64_encode($qrData)
        ];
    }
}
?>