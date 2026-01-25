<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/AuthController.php';
require_once '../models/Favorite.php';

try {
    $authController = new AuthController();
    
    if (!$authController->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $favoriteModel = new Favorite();
    $currentUser = $authController->getCurrentUser();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['user_id'])) {
                $favorites = $favoriteModel->getByUser($_GET['user_id']);
                echo json_encode($favorites);
            } elseif (isset($_GET['check']) && isset($_GET['event_id'])) {
                $isFavorite = $favoriteModel->isFavorite($currentUser['id'], $_GET['event_id']);
                echo json_encode(['is_favorite' => $isFavorite]);
            } else {
                $favorites = $favoriteModel->getByUser($currentUser['id']);
                echo json_encode($favorites);
            }
            break;
            
        case 'POST':
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['event_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                break;
            }
            
            $result = $favoriteModel->add($currentUser['id'], $data['event_id']);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            if (!isset($_GET['event_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Event ID required']);
                break;
            }
            
            $result = $favoriteModel->remove($currentUser['id'], $_GET['event_id']);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log('Favorites API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>