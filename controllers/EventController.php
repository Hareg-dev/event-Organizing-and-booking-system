<?php
require_once '../models/Event.php';
require_once '../controllers/AuthController.php';

class EventController {
    private $eventModel;
    private $auth;

    public function __construct() {
        $this->eventModel = new Event();
        $this->auth = new AuthController();
    }

    public function create($data) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $user = $this->auth->getCurrentUser();
            if ($user['role'] !== 'organizer' && $user['role'] !== 'admin') {
                return ['success' => false, 'message' => 'Insufficient permissions. Only organizers can create events.'];
            }
            
            // Validate input data
            $validation = $this->validateEventData($data);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Add organizer ID
            $data['organizer_id'] = $user['id'];
            
            $eventId = $this->eventModel->create($data);
            
            if ($eventId) {
                return [
                    'success' => true, 
                    'message' => 'Event created successfully',
                    'event_id' => $eventId
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to create event'];
            
        } catch (Exception $e) {
            error_log('EventController::create - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the event'];
        }
    }

    public function getAll($filters = []) {
        try {
            return $this->eventModel->getAllPublished($filters);
        } catch (Exception $e) {
            error_log('EventController::getAll - ' . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return null;
            }
            
            return $this->eventModel->getById($id);
        } catch (Exception $e) {
            error_log('EventController::getById - ' . $e->getMessage());
            return null;
        }
    }

    public function update($id, $data) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $event = $this->eventModel->getById($id);
            if (!$event) {
                return ['success' => false, 'message' => 'Event not found'];
            }
            
            $user = $this->auth->getCurrentUser();
            if ($event['organizer_id'] != $user['id'] && $user['role'] !== 'admin') {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            // Validate input data
            $validation = $this->validateEventData($data, true);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            if ($this->eventModel->update($id, $data)) {
                return ['success' => true, 'message' => 'Event updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update event'];
            
        } catch (Exception $e) {
            error_log('EventController::update - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the event'];
        }
    }

    public function delete($id) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return ['success' => false, 'message' => 'Authentication required'];
            }
            
            $event = $this->eventModel->getById($id);
            if (!$event) {
                return ['success' => false, 'message' => 'Event not found'];
            }
            
            $user = $this->auth->getCurrentUser();
            if ($event['organizer_id'] != $user['id'] && $user['role'] !== 'admin') {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            if ($this->eventModel->delete($id)) {
                return ['success' => true, 'message' => 'Event deleted successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to delete event'];
            
        } catch (Exception $e) {
            error_log('EventController::delete - ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the event'];
        }
    }
    
    public function getFeatured($limit = 6) {
        try {
            return $this->eventModel->getFeatured($limit);
        } catch (Exception $e) {
            error_log('EventController::getFeatured - ' . $e->getMessage());
            return [];
        }
    }
    
    public function getCategories() {
        try {
            return $this->eventModel->getCategories();
        } catch (Exception $e) {
            error_log('EventController::getCategories - ' . $e->getMessage());
            return [];
        }
    }
    
    public function getByOrganizer($organizerId) {
        try {
            if (!$this->auth->isLoggedIn()) {
                return [];
            }
            
            $user = $this->auth->getCurrentUser();
            if ($user['id'] != $organizerId && $user['role'] !== 'admin') {
                return [];
            }
            
            return $this->eventModel->getByOrganizer($organizerId);
        } catch (Exception $e) {
            error_log('EventController::getByOrganizer - ' . $e->getMessage());
            return [];
        }
    }
    
    private function validateEventData($data, $isUpdate = false) {
        $errors = [];
        
        // Required fields for creation
        if (!$isUpdate) {
            $required = ['title', 'description', 'date_start', 'location'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Validate title
        if (isset($data['title'])) {
            if (strlen($data['title']) < 3) {
                $errors[] = 'Title must be at least 3 characters long';
            }
            if (strlen($data['title']) > 200) {
                $errors[] = 'Title must not exceed 200 characters';
            }
        }
        
        // Validate description
        if (isset($data['description'])) {
            if (strlen($data['description']) < 10) {
                $errors[] = 'Description must be at least 10 characters long';
            }
        }
        
        // Validate date
        if (isset($data['date_start'])) {
            $eventDate = strtotime($data['date_start']);
            if (!$eventDate) {
                $errors[] = 'Invalid event start date format';
            } elseif ($eventDate < time()) {
                $errors[] = 'Event start date must be in the future';
            }
        }
        
        // Validate end date if provided
        if (isset($data['date_end']) && !empty($data['date_end'])) {
            $endDate = strtotime($data['date_end']);
            if (!$endDate) {
                $errors[] = 'Invalid event end date format';
            } elseif (isset($eventDate) && $endDate < $eventDate) {
                $errors[] = 'Event end date must be after start date';
            }
        }
        
        // Validate max tickets
        if (isset($data['max_tickets'])) {
            if (!is_numeric($data['max_tickets']) || $data['max_tickets'] < 1) {
                $errors[] = 'Maximum tickets must be a positive number';
            }
            if ($data['max_tickets'] > 10000) {
                $errors[] = 'Maximum tickets cannot exceed 10,000';
            }
        }
        
        // Validate price
        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $errors[] = 'Price must be a non-negative number';
            }
        }
        
        // Validate status
        if (isset($data['status'])) {
            $validStatuses = ['draft', 'published', 'cancelled', 'sold_out'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'Invalid event status';
            }
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Valid' : implode(', ', $errors)
        ];
    }
}
?>