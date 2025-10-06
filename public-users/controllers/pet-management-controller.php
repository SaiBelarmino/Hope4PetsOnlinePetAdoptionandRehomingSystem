<?php
/**
 * Pet Management Controller
 * 
 * Handles CRUD operations for pets.
 * Users can only manage their own pets.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class PetManagementController extends BaseController {
    /**
     * Create a new pet listing
     */
    public static function createPet(int $ownerId, array $data): array {
        $mysqli = self::db();
        
        // Insert pet
        $stmt = $mysqli->prepare(
            "INSERT INTO pets (owner_id, shelter_id, name, species, breed, age, gender, size, vaccine_status, health_status, location, description, status, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        
        $stmt->bind_param(
            'iissssssssss',
            $ownerId,
            $data['shelter_id'],
            $data['name'],
            $data['species'],
            $data['breed'],
            $data['age'],
            $data['gender'],
            $data['size'],
            $data['vaccine_status'],
            $data['health_status'],
            $data['location'],
            $data['description']
        );
        
        $success = $stmt->execute();
        
        if (!$success) {
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to create pet listing.'];
        }
        
        $petId = $mysqli->insert_id;
        $stmt->close();
        
        return [
            'success' => true,
            'message' => 'Pet listed successfully!',
            'pet_id' => $petId
        ];
    }
    
    /**
     * Upload pet photo
     */
    public static function uploadPetPhoto(int $petId, string $photoPath, bool $isPrimary = false): bool {
        $mysqli = self::db();
        
        // If this is primary, unset other primary photos for this pet
        if ($isPrimary) {
            $updateStmt = $mysqli->prepare("UPDATE pet_photos SET is_primary = 0 WHERE pet_id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param('i', $petId);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }
        
        // Insert photo
        $stmt = $mysqli->prepare("INSERT INTO pet_photos (pet_id, photo_path, is_primary) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        
        $primary = $isPrimary ? 1 : 0;
        $stmt->bind_param('isi', $petId, $photoPath, $primary);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Get pets by owner ID
     */
    public static function getPetsByOwnerId(int $ownerId): array {
        return self::fetchAll(
            "SELECT p.*, 
                    (SELECT photo_path FROM pet_photos WHERE pet_id = p.id AND is_primary = 1 LIMIT 1) as primary_photo,
                    (SELECT COUNT(*) FROM adoptions WHERE pet_id = p.id) as adoption_requests
             FROM pets p
             WHERE p.owner_id = ?
             ORDER BY p.created_at DESC",
            'i',
            [$ownerId]
        );
    }
    
    /**
     * Get all available pets (public view)
     */
    public static function getAvailablePets(int $limit = 20, int $offset = 0): array {
        return self::fetchAll(
            "SELECT p.*, u.full_name as owner_name, s.shelter_name,
                    (SELECT photo_path FROM pet_photos WHERE pet_id = p.id AND is_primary = 1 LIMIT 1) as primary_photo
             FROM pets p
             JOIN users u ON p.owner_id = u.id
             LEFT JOIN shelters s ON p.shelter_id = s.id
             WHERE p.status = 'available'
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            'ii',
            [$limit, $offset]
        );
    }
    
    /**
     * Get pet by ID with details
     */
    public static function getPetById(int $petId): ?array {
        return self::fetchOne(
            "SELECT p.*, u.full_name as owner_name, u.contact_number as owner_contact, s.shelter_name
             FROM pets p
             JOIN users u ON p.owner_id = u.id
             LEFT JOIN shelters s ON p.shelter_id = s.id
             WHERE p.id = ?
             LIMIT 1",
            'i',
            [$petId]
        );
    }
    
    /**
     * Get pet photos
     */
    public static function getPetPhotos(int $petId): array {
        return self::fetchAll(
            "SELECT * FROM pet_photos WHERE pet_id = ? ORDER BY is_primary DESC, id ASC",
            'i',
            [$petId]
        );
    }
    
    /**
     * Update pet
     */
    public static function updatePet(int $petId, int $ownerId, array $data): array {
        $mysqli = self::db();
        
        // Verify ownership
        $pet = self::fetchOne("SELECT owner_id FROM pets WHERE id = ?", 'i', [$petId]);
        if (!$pet || $pet['owner_id'] != $ownerId) {
            return ['success' => false, 'message' => 'You do not have permission to edit this pet.'];
        }
        
        $stmt = $mysqli->prepare(
            "UPDATE pets SET name = ?, species = ?, breed = ?, age = ?, gender = ?, size = ?, 
                    vaccine_status = ?, health_status = ?, location = ?, description = ?, updated_at = NOW() 
             WHERE id = ? AND owner_id = ?"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param(
            'ssssssssssii',
            $data['name'],
            $data['species'],
            $data['breed'],
            $data['age'],
            $data['gender'],
            $data['size'],
            $data['vaccine_status'],
            $data['health_status'],
            $data['location'],
            $data['description'],
            $petId,
            $ownerId
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        return ['success' => $success, 'message' => $success ? 'Pet updated!' : 'Failed to update pet.'];
    }
    
    /**
     * Delete pet (or mark as removed)
     */
    public static function deletePet(int $petId, int $ownerId): array {
        $mysqli = self::db();
        
        // Verify ownership
        $pet = self::fetchOne("SELECT owner_id FROM pets WHERE id = ?", 'i', [$petId]);
        if (!$pet || $pet['owner_id'] != $ownerId) {
            return ['success' => false, 'message' => 'You do not have permission to delete this pet.'];
        }
        
        // Mark as removed instead of deleting
        $stmt = $mysqli->prepare("UPDATE pets SET status = 'removed' WHERE id = ? AND owner_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param('ii', $petId, $ownerId);
        $success = $stmt->execute();
        $stmt->close();
        
        return ['success' => $success, 'message' => $success ? 'Pet removed!' : 'Failed to remove pet.'];
    }
    
    /**
     * Add comment to pet
     */
    public static function addComment(int $petId, int $userId, string $content): array {
        $mysqli = self::db();
        
        $stmt = $mysqli->prepare("INSERT INTO pet_comments (pet_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param('iis', $petId, $userId, $content);
        $success = $stmt->execute();
        $commentId = $mysqli->insert_id;
        $stmt->close();
        
        return ['success' => $success, 'comment_id' => $commentId];
    }
    
    /**
     * Get pet comments
     */
    public static function getComments(int $petId): array {
        return self::fetchAll(
            "SELECT c.*, u.full_name, u.profile_photo 
             FROM pet_comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.pet_id = ?
             ORDER BY c.created_at DESC",
            'i',
            [$petId]
        );
    }
    
    /**
     * Toggle reaction
     */
    public static function toggleReaction(int $petId, int $userId, string $reactionType = 'like'): array {
        $mysqli = self::db();
        
        // Check if already reacted
        $existing = self::fetchOne(
            "SELECT id FROM pet_reactions WHERE pet_id = ? AND user_id = ?",
            'ii',
            [$petId, $userId]
        );
        
        if ($existing) {
            // Remove reaction
            $stmt = $mysqli->prepare("DELETE FROM pet_reactions WHERE pet_id = ? AND user_id = ?");
            $stmt->bind_param('ii', $petId, $userId);
            $stmt->execute();
            $stmt->close();
            return ['success' => true, 'action' => 'removed'];
        } else {
            // Add reaction
            $stmt = $mysqli->prepare("INSERT INTO pet_reactions (pet_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('iis', $petId, $userId, $reactionType);
            $success = $stmt->execute();
            $stmt->close();
            return ['success' => $success, 'action' => 'added'];
        }
    }
}
