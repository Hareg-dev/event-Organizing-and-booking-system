<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/AuthController.php';

try {
    $authController = new AuthController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Check authentication status
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            if ($authController->isLoggedIn()) {
                $user = $authController->getCurrentUser();
                echo json_encode([
                    'logged_in' => true,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                echo json_encode(['logged_in' => false]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
        exit;
    }
    
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data or missing action']);
        exit;
    }
    
    switch ($data['action']) {
        case 'login':
            if (empty($data['email']) || empty($data['password'])) {
                echo json_encode(['success' => false, 'message' => 'Email and password are required']);
                break;
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }
            
            $result = $authController->login($data['email'], $data['password']);
            echo json_encode($result);
            break;
            
        case 'register':
            // Validate required fields
            $required = ['email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                    exit;
                }
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }
            
            // Validate password
            if (strlen($data['password']) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                break;
            }
            
            // Validate role
            $validRoles = ['user', 'organizer'];
            if (!isset($data['role']) || !in_array($data['role'], $validRoles)) {
                $data['role'] = 'user';
            }
            
            // Validate names
            if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
                echo json_encode(['success' => false, 'message' => 'First and last name must be at least 2 characters long']);
                break;
            }
            
            // Validate phone if provided
            if (!empty($data['phone']) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $data['phone'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }
            
            $result = $authController->register($data);
            echo json_encode($result);
            break;
            
        case 'logout':
            $result = $authController->logout();
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log('Auth API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
?>