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
     * Build a storage-relative photo path for a given owner and stored value.
     * If $photo already looks like a URL or starts with '/' it is returned unchanged.
     * Otherwise it returns /storage/uploads/images/{ownerId}/{basename(photo)}
     */
    private static function buildPhotoPath(?int $ownerId, string $photo): string {
        $photo = trim((string)$photo);
        if ($photo === '') return '';
        if (strpos($photo, 'http://') === 0 || strpos($photo, 'https://') === 0) return $photo;
        // if already absolute site-root path, keep
        if (strpos($photo, '/') === 0) return $photo;
        $base = '/storage/uploads/images';
        if ($ownerId && $ownerId > 0) {
            return $base . '/' . (int)$ownerId . '/' . basename($photo);
        }
        return $base . '/' . basename($photo);
    }

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
            "SELECT p.*, p.pet_photos as primary_photo,
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
                    p.pet_photos as primary_photo
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
        // New schema stores the primary photo path in pets.pet_photos (varchar).
        $pet = self::fetchOne("SELECT pet_photos FROM pets WHERE id = ? LIMIT 1", 'i', [$petId]);
        if (!$pet) return [];
        $path = $pet['pet_photos'] ?? '';
        if (empty($path)) return [];
        return [ ['photo_path' => $path, 'is_primary' => 1] ];
    }

    /**
     * Converts a site-root relative path (e.g., /storage/...) into a full absolute HTTP URL,
     * specifically handling the XAMPP sub-directory project structure.
     */
    public static function getPhotoUrl(?int $ownerId, string $photoPath): string {
        // Use existing helper to get the site-root relative path, which is your database value.
        $relativePath = self::buildPhotoPath($ownerId, $photoPath);

        // Fallback to a simple path if none is found.
        if (empty($relativePath) || $relativePath === '/storage/uploads/images') {
             // Default image path. Kung walang default image, palitan ng blank string.
             $relativePath = '/storage/uploads/images/default.png'; 
        }
        
        // If it's already a full URL (http/https), return it
        if (strpos($relativePath, 'http') === 0 || strpos($relativePath, 'https') === 0) {
            return $relativePath;
        }

        // --- CRITICAL XAMPP URL CONSTRUCTION ---
        $scheme = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http'));
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // **Ito ang pinakamahalagang linya:** Explicitly define the project folder name.
        $projectFolderName = 'Hope4PetsOnlinePetAdoptionandRehomingSystem';

        // 1. Clean the relative path (e.g., /storage/uploads... -> storage/uploads...)
        $relativePathClean = ltrim($relativePath, '/');

        // 2. Tiyakin na ang Project Folder Name ay nakalagay sa unahan, maliban kung nandoon na.
        // Dapat maging: /Hope4PetsOnlinePetAdoptionandRehomingSystem/storage/uploads/images/7/file.jpg
        
        $finalWebPath = '';
        if (strpos($relativePathClean, $projectFolderName . '/') === 0) {
             // Kung kasama na ang Project Folder, gamitin na lang ang nalalabi
             $finalWebPath = '/' . $relativePathClean;
        } else {
             // Idagdag ang Project Folder
             $finalWebPath = '/' . $projectFolderName . '/' . $relativePathClean;
        }

        // Return the full absolute HTTP URL
        return rtrim($scheme . '://' . $host, '/') . $finalWebPath;
    }

    /**
     * Show pet management page for an owner (connects controller to view)
     * - Fetches pets for the owner
     * - Attaches photos and resolves a primary photo per pet
     * - Includes the PetManagement view
     */
    public static function showManagement(int $ownerId): void {
        // fetch pets
        $pets = self::getPetsByOwnerId($ownerId);

        // attach photos and resolve primary (normalize stored filenames to storage path)
        foreach ($pets as &$pet) {
            $photos = self::getPetPhotos((int)$pet['id']);
            $pet['photos'] = $photos;

            // determine source value (pets.pet_photos or joined primary_photo or photos list)
            $source = '';
            if (!empty($pet['primary_photo'])) $source = $pet['primary_photo'];
            elseif (!empty($photos[0]['photo_path'])) $source = $photos[0]['photo_path'];
            elseif (!empty($pet['pet_photos'])) $source = $pet['pet_photos'];

            // normalize into full URL using the centralized helper function
            $source = $source ?: ''; // Ensure $source is a string for the helper

            // Determine the final photo source (pet_photos column, or fallback)
            // Note: The logic before this replacement block already correctly finds $source.
            // We just need to use the helper to get the final URL.
            $pet['photo'] = self::getPhotoUrl((int)($pet['owner_id'] ?? $ownerId), $source);
            $pet['photo_raw'] = $source; // Keep raw for debug/reference
        }
        unset($pet);

         // make $pets available to the view
         // view expects $pets variable
         include __DIR__ . '/../views/PetManagement.php';
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
                    vaccine_status = ?, health_status = ?, location = ?, description = ?, status = ? 
             WHERE id = ? AND owner_id = ?"
        );
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param(
            'sssssssssssii',
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
            $data['status'],
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
        
        // Delete the pet
        $stmt = $mysqli->prepare("DELETE FROM pets WHERE id = ? AND owner_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error.'];
        }
        
        $stmt->bind_param('ii', $petId, $ownerId);
        $success = $stmt->execute();
        $stmt->close();
        
        return ['success' => $success, 'message' => $success ? 'Pet deleted!' : 'Failed to delete pet.'];
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
