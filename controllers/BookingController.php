<?php
require_once '../models/Booking.php';
require_once '../controllers/AuthController.php';

class BookingController {
    private $bookingModel;
    private $auth;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->auth = new AuthController();
    }

    public function create($data) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $user = $this->auth->getCurrentUser();
            $data['user_id'] = $user['id'];
            
            // Validate required fields
            $required = ['event_id', 'quantity'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Validate quantity
            if (!is_numeric($data['quantity']) || $data['quantity'] < 1 || $data['quantity'] > 10) {
                return ['success' => false, 'message' => 'Invalid quantity. Must be between 1 and 10.'];
            }
            
            $bookingId = $this->bookingModel->create($data);
            
            if ($bookingId) {
                return [
                    'success' => true, 
                    'message' => 'Booking created successfully',
                    'booking_id' => $bookingId
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create booking'];
            
        } catch (Exception $e) {
            error_log('BookingController::create - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getByUser($userId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return [];
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($currentUser['id'] != $userId && $currentUser['role'] !== 'admin') {
                return [];
            }
            
            return $this->bookingModel->getByUser($userId);
        } catch (Exception $e) {
            error_log('BookingController::getByUser - ' . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return null;
            }
            
            $booking = $this->bookingModel->getById($id);
            if (!$booking) {
                return null;
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($booking['user_id'] != $currentUser['id'] && $currentUser['role'] !== 'admin') {
                return null;
            }
            
            return $booking;
        } catch (Exception $e) {
            error_log('BookingController::getById - ' . $e->getMessage());
            return null;
        }
    }

    public function cancel($id) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $currentUser = $this->auth->getCurrentUser();
            
            if ($this->bookingModel->cancel($id, $currentUser['id'])) {
                return ['success' => true, 'message' => 'Booking cancelled successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to cancel booking'];
            
        } catch (Exception $e) {
            error_log('BookingController::cancel - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function confirm($id) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($currentUser['role'] !== 'admin' && $currentUser['role'] !== 'organizer') {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            if ($this->bookingModel->confirm($id)) {
                return ['success' => true, 'message' => 'Booking confirmed successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to confirm booking'];
            
        } catch (Exception $e) {
            error_log('BookingController::confirm - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while confirming booking'];
        }
    }

    public function getByEvent($eventId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return [];
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($currentUser['role'] !== 'admin' && $currentUser['role'] !== 'organizer') {
                return [];
            }
            
            return $this->bookingModel->getByEvent($eventId);
        } catch (Exception $e) {
            error_log('BookingController::getByEvent - ' . $e->getMessage());
            return [];
        }
    }

    public function getEventStats($eventId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return null;
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($currentUser['role'] !== 'admin' && $currentUser['role'] !== 'organizer') {
                return null;
            }
            
            return $this->bookingModel->getEventStats($eventId);
        } catch (Exception $e) {
            error_log('BookingController::getEventStats - ' . $e->getMessage());
            return null;
        }
    }

    public function generateTicket($bookingId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $booking = $this->getById($bookingId);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }
            
            $ticket = $this->bookingModel->generateTicket($bookingId);
            
            if ($ticket) {
                return [
                    'success' => true,
                    'message' => 'Ticket generated successfully',
                    'ticket' => $ticket
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to generate ticket'];
            
        } catch (Exception $e) {
            error_log('BookingController::generateTicket - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while generating ticket'];
        }
    }

    public function getUserBookingStats($userId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return null;
            }
            
            $currentUser = $this->auth->getCurrentUser();
            if ($currentUser['id'] != $userId && $currentUser['role'] !== 'admin') {
                return null;
            }
            
            $bookings = $this->getByUser($userId);
            
            $stats = [
                'total_bookings' => count($bookings),
                'confirmed_bookings' => 0,
                'pending_bookings' => 0,
                'cancelled_bookings' => 0,
                'total_spent' => 0
            ];
            
            foreach ($bookings as $booking) {
                switch ($booking['status']) {
                    case 'confirmed':
                        $stats['confirmed_bookings']++;
                        $stats['total_spent'] += $booking['total_price'];
                        break;
                    case 'pending':
                        $stats['pending_bookings']++;
                        break;
                    case 'cancelled':
                        $stats['cancelled_bookings']++;
                        break;
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log('BookingController::getUserBookingStats - ' . $e->getMessage());
            return null;
        }
    }

    public function processPayment($bookingId, $paymentData) {
        try {
            // This is a placeholder for payment processing
            // In a real application, you would integrate with payment gateways like Stripe, PayPal, etc.
            
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $booking = $this->getById($bookingId);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }
            
            // Simulate payment processing
            $paymentSuccess = true; // In real implementation, this would be the result from payment gateway
            
            if ($paymentSuccess) {
                $this->bookingModel->confirm($bookingId);
                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_reference' => 'PAY_' . strtoupper(uniqid())
                ];
            } else {
                return ['success' => false, 'message' => 'Payment processing failed'];
            }
            
        } catch (Exception $e) {
            error_log('BookingController::processPayment - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment processing error'];
        }
    }
}
?>