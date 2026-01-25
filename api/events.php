<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/EventController.php';

try {
    $eventController = new EventController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $event = $eventController->getById($_GET['id']);
                if ($event) {
                    echo json_encode($event);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Event not found']);
                }
            } elseif (isset($_GET['featured'])) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
                $events = $eventController->getFeatured($limit);
                echo json_encode($events);
            } elseif (isset($_GET['categories'])) {
                $categories = $eventController->getCategories();
                echo json_encode($categories);
            } else {
                $filters = [
                    'category' => $_GET['category'] ?? '',
                    'city' => $_GET['city'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : null
                ];
                $events = $eventController->getAll($filters);
                echo json_encode($events);
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
            
            // Validate required fields
            $required = ['title', 'description', 'date_start', 'location'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
                    exit;
                }
            }
            
            $result = $eventController->create($data);
            echo json_encode($result);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Event ID is required']);
                break;
            }
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
                break;
            }
            
            $result = $eventController->update($_GET['id'], $data);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Event ID is required']);
                break;
            }
            
            $result = $eventController->delete($_GET['id']);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log('Events API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>