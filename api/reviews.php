<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/AuthController.php';
require_once '../models/Review.php';

try {
    $authController = new AuthController();
    $reviewModel = new Review();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['event_id'])) {
                $reviews = $reviewModel->getByEvent($_GET['event_id']);
                echo json_encode($reviews);
            } elseif (isset($_GET['user_id'])) {
                $reviews = $reviewModel->getByUser($_GET['user_id']);
                echo json_encode($reviews);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Event ID or User ID required']);
            }
            break;
            
        case 'POST':
            if (!$authController->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                break;
            }
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                break;
            }
            
            $currentUser = $authController->getCurrentUser();
            $data['user_id'] = $currentUser['id'];
            
            $result = $reviewModel->create($data);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            if (!$authController->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                break;
            }
            
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Review ID required']);
                break;
            }
            
            $currentUser = $authController->getCurrentUser();
            $result = $reviewModel->delete($_GET['id'], $currentUser['id']);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log('Reviews API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>