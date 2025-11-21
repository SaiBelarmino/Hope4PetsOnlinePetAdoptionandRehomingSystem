<?php

require_once __DIR__ . '/../../../controllers/BaseController.php';

class SheltersController extends BaseController {
    public static function listAll(int $limit = 200): array {
        // Use the actual shelters schema: shelter_name, user_id, address, contact_number, is_verified
        $sql = "SELECT id, shelter_name AS name, user_id, address, contact_number, is_verified, created_at
                FROM shelters
                ORDER BY created_at DESC
                LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}


// Minimal controller to satisfy admin/views/Shelter/shelters.php
// Path: admin/controllers/Shelter/shelters-controller.php

class ShelterVerificationRequestsController {
    /**
     * Return a list of shelters with owner name and pet count using the project's schema.
     */
    public static function listSheltersWithOwnerAndCount(): array {
        $sql = "SELECT s.id,
                       s.shelter_name AS shelter_name,
                       s.is_verified,
                       CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,'')) AS owner_name,
                       COUNT(p.id) AS pet_count
                FROM shelters s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN pets p ON p.shelter_id = s.id
                GROUP BY s.id
                ORDER BY s.created_at DESC";

        // Use BaseController helper to fetch data.
        try {
            if (class_exists('BaseController') && method_exists('BaseController', 'fetchAll')) {
                return BaseController::fetchAll($sql) ?? [];
            }
        } catch (Throwable $e) {
            // In case of an error, log it and return an empty array.
            // error_log($e->getMessage());
        }

        // Safe default: empty list
        return [];
    }
}
?>