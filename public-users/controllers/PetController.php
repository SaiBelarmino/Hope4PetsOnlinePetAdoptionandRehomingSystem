<?php
// Public-facing PetController - lightweight facade for fetching pets for public pages
// Relies on PetManagementController for DB access and photo URL helpers.

require_once __DIR__ . '/PetManagementController.php';

class PetController {
    /**
     * Get available pets for public listing with normalized photo URLs and attached photos array.
     * Returns an array of pets suitable for views.
     */
    public static function fetchAvailablePets(int $limit = 999, int $offset = 0): array {
        try {
            $pets = PetManagementController::getAvailablePets($limit, $offset);

            foreach ($pets as &$pet) {
                $ownerId = (int)($pet['owner_id'] ?? 0);

                // Fetch supplemental photos list
                $photos = PetManagementController::getPetPhotos((int)($pet['id'] ?? 0));
                $pet['photos'] = $photos;

                // Determine best source for primary image
                $source = '';
                if (!empty($pet['primary_photo'])) {
                    $source = $pet['primary_photo'];
                } elseif (!empty($photos[0]['photo_path'])) {
                    $source = $photos[0]['photo_path'];
                } elseif (!empty($pet['pet_photos'])) {
                    $source = $pet['pet_photos'];
                }

                // Normalize to a full URL using the shared helper
                $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $source ?: '/storage/uploads/images/default.png');
                $pet['photo_raw'] = $source;
            }
            unset($pet);

            return $pets;
        } catch (Throwable $e) {
            error_log('PetController::fetchAvailablePets error: ' . $e->getMessage());
            return [];
        }
    }
}
