<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/BookingController.php';

try {
    $bookingController = new BookingController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $booking = $bookingController->getById($_GET['id']);
                if ($booking) {
                    echo json_encode($booking);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Booking not found']);
                }
            } elseif (isset($_GET['user_id'])) {
                $bookings = $bookingController->getByUser($_GET['user_id']);
                echo json_encode($bookings);
            } elseif (isset($_GET['event_id'])) {
                $bookings = $bookingController->getByEvent($_GET['event_id']);
                echo json_encode($bookings);
            } elseif (isset($_GET['stats']) && isset($_GET['event_id'])) {
                $stats = $bookingController->getEventStats($_GET['event_id']);
                echo json_encode($stats ?: []);
            } elseif (isset($_GET['user_stats']) && isset($_GET['user_id'])) {
                $stats = $bookingController->getUserBookingStats($_GET['user_id']);
                echo json_encode($stats ?: []);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required parameters']);
            }
            break;
            
        case 'POST':
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                break;
            }
            
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'generate_ticket':
                        if (!isset($data['booking_id'])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                            break;
                        }
                        $result = $bookingController->generateTicket($data['booking_id']);
                        echo json_encode($result);
                        break;
                        
                    case 'process_payment':
                        if (!isset($data['booking_id'])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                            break;
                        }
                        $result = $bookingController->processPayment($data['booking_id'], $data);
                        echo json_encode($result);
                        break;
                        
                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid action']);
                }
            } else {
                // Create new booking
                $result = $bookingController->create($data);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                break;
            }
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['action'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data or missing action']);
                break;
            }
            
            switch ($data['action']) {
                case 'confirm':
                    $result = $bookingController->confirm($_GET['id']);
                    echo json_encode($result);
                    break;
                    
                case 'cancel':
                    $result = $bookingController->cancel($_GET['id']);
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
                break;
            }
            
            $result = $bookingController->cancel($_GET['id']);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log('Bookings API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>