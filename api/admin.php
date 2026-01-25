<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/AuthController.php';
require_once '../models/User.php';
require_once '../models/Event.php';
require_once '../models/Booking.php';

try {
    $authController = new AuthController();
    
    // Check admin authentication
    if (!$authController->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $currentUser = $authController->getCurrentUser();
    if ($currentUser['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['stats'])) {
                // Get system statistics
                $userModel = new User();
                $eventModel = new Event();
                $bookingModel = new Booking();
                
                $stats = [
                    'total_users' => $userModel->getTotalCount(),
                    'total_organizers' => $userModel->getCountByRole('organizer'),
                    'total_events' => $eventModel->getTotalCount(),
                    'total_bookings' => $bookingModel->getTotalCount(),
                    'total_revenue' => $bookingModel->getTotalRevenue(),
                    'recent_users' => $userModel->getRecentUsers(5),
                    'recent_events' => $eventModel->getRecentEvents(5)
                ];
                
                echo json_encode($stats);
            } elseif (isset($_GET['users'])) {
                $userModel = new User();
                $users = $userModel->getAll();
                echo json_encode($users);
            } elseif (isset($_GET['events'])) {
                $eventModel = new Event();
                $events = $eventModel->getAllForAdmin();
                echo json_encode($events);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
            }
            break;
            
        case 'PUT':
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['action'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data or missing action']);
                break;
            }
            
            switch ($data['action']) {
                case 'change_user_role':
                    if (!isset($data['user_id']) || !isset($data['role'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'User ID and role are required']);
                        break;
                    }
                    
                    $validRoles = ['user', 'organizer', 'admin'];
                    if (!in_array($data['role'], $validRoles)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid role']);
                        break;
                    }
                    
                    $userModel = new User();
                    $result = $userModel->updateRole($data['user_id'], $data['role']);
                    echo json_encode($result);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['user_id'])) {
                $userModel = new User();
                $result = $userModel->delete($_GET['user_id']);
                echo json_encode($result);
            } elseif (isset($_GET['event_id'])) {
                $eventModel = new Event();
                $result = $eventModel->delete($_GET['event_id']);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log('Admin API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>