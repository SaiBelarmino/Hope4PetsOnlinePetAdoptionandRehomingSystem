<?php
/**
 * Adopt Controller
 * 
 * Handles pet adoption requests.
 * Adoption requests are linked to the logged-in user (applicant).
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Require login
SessionManager::requireLogin();

// Handle adoption form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = SessionManager::getUserId();
    $petId = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($petId)) {
        $errors[] = 'Invalid pet selection.';
    }
    
    // Check if pet is still available
    $pet = AdoptController::getPetById($petId);
    if (!$pet) {
        $errors[] = 'Pet not found.';
    } elseif ($pet['status'] !== 'available') {
        $errors[] = 'This pet is no longer available for adoption.';
    }
    
    // Check if user already applied for this pet
    if (AdoptController::hasAlreadyApplied($userId, $petId)) {
        $errors[] = 'You have already applied to adopt this pet.';
    }
    
    if (empty($errors)) {
        // Submit adoption request
        $result = AdoptController::submitAdoptionRequest($userId, [
            'pet_id' => $petId,
            'shelter_id' => $pet['shelter_id'],
            'message' => $message
        ]);
        
        if ($result['success']) {
            SessionManager::setFlash('success', 'Adoption request submitted successfully!');
            header('Location: ../views/adoption_status.php?id=' . $result['adoption_id']);
            exit;
        } else {
            SessionManager::setFlash('error', $result['message']);
            header('Location: ../views/pet_view.php?id=' . $petId);
            exit;
        }
    } else {
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: ../views/pet_view.php?id=' . $petId);
        exit;
    }
}

class AdoptController extends BaseController {
    /**
     * Submit an adoption request
     * 
     * @param int $applicantId User ID applying for adoption
     * @param array $data Adoption data
     * @return array Result with success status and adoption ID
     */
    public static function submitAdoptionRequest(int $applicantId, array $data): array {
        $mysqli = self::db();
        
        // Insert adoption request
        $stmt = $mysqli->prepare(
            "INSERT INTO adoptions (pet_id, applicant_id, shelter_id, status, created_at) 
             VALUES (?, ?, ?, 'applied', NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param(
            'iii',
            $data['pet_id'],
            $applicantId,
            $data['shelter_id']
        );
        
        $success = $stmt->execute();
        
        if (!$success) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to submit adoption request.'];
        }
        
        $adoptionId = $mysqli->insert_id;
        $stmt->close();
        
        // Update pet status to pending
        $updateStmt = $mysqli->prepare("UPDATE pets SET status = 'pending' WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param('i', $data['pet_id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        return [
            'success' => true,
            'message' => 'Adoption request submitted!',
            'adoption_id' => $adoptionId
        ];
    }
    
    /**
     * Check if user has already applied for a pet
     */
    public static function hasAlreadyApplied(int $userId, int $petId): bool {
        $result = self::fetchValue(
            "SELECT COUNT(*) FROM adoptions WHERE applicant_id = ? AND pet_id = ? AND status IN ('applied', 'approved')",
            'ii',
            [$userId, $petId],
            0
        );
        return $result > 0;
    }
    
    /**
     * Get adoption requests by applicant ID (user)
     */
    public static function getAdoptionsByApplicantId(int $applicantId): array {
        return self::fetchAll(
            "SELECT a.*, p.name as pet_name, p.species, p.breed, s.shelter_name,
                    (SELECT photo_path FROM pet_photos WHERE pet_id = p.id AND is_primary = 1 LIMIT 1) as pet_photo
             FROM adoptions a
             JOIN pets p ON a.pet_id = p.id
             LEFT JOIN shelters s ON a.shelter_id = s.id
             WHERE a.applicant_id = ?
             ORDER BY a.created_at DESC",
            'i',
            [$applicantId]
        );
    }
    
    /**
     * Get adoption by ID (with security check)
     */
    public static function getAdoptionById(int $adoptionId, int $applicantId): ?array {
        return self::fetchOne(
            "SELECT a.*, p.name as pet_name, p.species, p.breed, p.description, s.shelter_name, s.contact_number,
                    (SELECT photo_path FROM pet_photos WHERE pet_id = p.id AND is_primary = 1 LIMIT 1) as pet_photo
             FROM adoptions a
             JOIN pets p ON a.pet_id = p.id
             LEFT JOIN shelters s ON a.shelter_id = s.id
             WHERE a.id = ? AND a.applicant_id = ?
             LIMIT 1",
            'ii',
            [$adoptionId, $applicantId]
        );
    }
    
    /**
     * Get pet by ID
     */
    public static function getPetById(int $petId): ?array {
        return self::fetchOne(
            "SELECT * FROM pets WHERE id = ? LIMIT 1",
            'i',
            [$petId]
        );
    }
    
    /**
     * Get adoption requests for a shelter
     */
    public static function getAdoptionsByShelterId(int $shelterId): array {
        return self::fetchAll(
            "SELECT a.*, p.name as pet_name, u.full_name as applicant_name, u.email as applicant_email
             FROM adoptions a
             JOIN pets p ON a.pet_id = p.id
             JOIN users u ON a.applicant_id = u.id
             WHERE a.shelter_id = ?
             ORDER BY a.created_at DESC",
            'i',
            [$shelterId]
        );
    }
    
    /**
     * Update adoption status (for shelter owners)
     */
    public static function updateAdoptionStatus(int $adoptionId, int $shelterId, string $status): array {
        $mysqli = self::db();
        
        // Verify shelter owns this adoption request
        $adoption = self::fetchOne(
            "SELECT * FROM adoptions WHERE id = ? AND shelter_id = ?",
            'ii',
            [$adoptionId, $shelterId]
        );
        
        if (!$adoption) {
            return ['success' => false, 'message' => 'Adoption request not found.'];
        }
        
        // Update status
        $stmt = $mysqli->prepare("UPDATE adoptions SET status = ? WHERE id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param('si', $status, $adoptionId);
        $success = $stmt->execute();
        $stmt->close();
        
        // Update pet status if approved
        if ($success && $status === 'approved') {
            $updatePetStmt = $mysqli->prepare("UPDATE pets SET status = 'adopted' WHERE id = ?");
            if ($updatePetStmt) {
                $updatePetStmt->bind_param('i', $adoption['pet_id']);
                $updatePetStmt->execute();
                $updatePetStmt->close();
            }
        }
        
        return ['success' => $success, 'message' => $success ? 'Status updated!' : 'Failed to update status.'];
    }
}
