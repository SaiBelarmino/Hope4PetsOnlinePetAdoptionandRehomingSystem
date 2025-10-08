<?php
/**
 * Register Shelter Controller
 * 
 * Handles shelter registration linked to logged-in user account.
 * Each user can register one shelter. Shelter data is isolated per user.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Require login
SessionManager::requireLogin();


// Handle shelter registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = SessionManager::getUserId();
    $shelterName = trim($_POST['shelter_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($shelterName)) {
        $errors[] = 'Shelter name is required.';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    
    if (empty($contactNumber)) {
        $errors[] = 'Contact number is required.';
    }
    
    // Check if user already has a shelter
    if (SessionManager::hasShelter()) {
        $errors[] = 'You already have a registered shelter.';
    }
    
    if (empty($errors)) {
        // Register shelter
        $result = RegisterShelterController::register($userId, [
            'shelter_name' => $shelterName,
            'address' => $address,
            'contact_number' => $contactNumber
        ]);
        
        if ($result['success']) {
            // Refresh session to update shelter status
            SessionManager::refreshShelterStatus();
            SessionManager::setFlash('success', 'Shelter registered successfully! Please upload documents for verification.');
            header('Location: ../views/upload_shelter_documents.php');
            exit;
        } else {
            SessionManager::setFlash('error', $result['message']);
            header('Location: ../views/register_shelter.php');
            exit;
        }
    } else {
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: ../views/register_shelter.php');
        exit;
    }
}

class RegisterShelterController extends BaseController {
    /**
     * Register a new shelter for a user
     * 
     * @param int $userId User ID who owns the shelter
     * @param array $data Shelter data (shelter_name, address, contact_number)
     * @return array Result with success status and message
     */
    public static function register(int $userId, array $data): array {
        $mysqli = self::db();
        
        // Check if user already has a shelter
        $existing = self::fetchOne(
            'SELECT id FROM shelters WHERE user_id = ? LIMIT 1',
            'i',
            [$userId]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'You already have a registered shelter.'];
        }
        
        // Insert shelter
        $stmt = $mysqli->prepare(
            "INSERT INTO shelters (user_id, shelter_name, address, contact_number, verified_badge, created_at) 
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param(
            'isss',
            $userId,
            $data['shelter_name'],
            $data['address'],
            $data['contact_number']
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            return ['success' => true, 'message' => 'Shelter registered successfully!'];
        } else {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Get shelter by user ID
     */
    public static function getShelterByUserId(int $userId): ?array {
        return self::fetchOne(
            'SELECT id, user_id, shelter_name, address, contact_number, verified_badge, created_at 
             FROM shelters WHERE user_id = ? LIMIT 1',
            'i',
            [$userId]
        );
    }
}
?>