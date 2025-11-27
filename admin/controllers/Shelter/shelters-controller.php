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

    /**
     * Fetches a list of all shelters with owner details for the admin view.
     */
    public static function listAllSheltersWithDetails(string $search = '', string $status = '', string $registeredDate = ''): array {
        $sql = "SELECT 
                    s.id, 
                    s.shelter_name, 
                    s.address, 
                    s.contact_number, 
                    s.is_verified, 
                    s.created_at,
                    u.full_name AS owner_name,
                    u.email AS owner_email
                FROM shelters s
                LEFT JOIN users u ON s.user_id = u.id";
        
        $params = [];
        $types = '';
        $conditions = [];

        if (!empty($search)) {
            $conditions[] = "s.shelter_name LIKE ?";
            $params[] = "%" . $search . "%";
            $types .= 's';
        }

        if ($status === 'verified') {
            $conditions[] = "s.is_verified = 1";
        } elseif ($status === 'unverified') {
            $conditions[] = "s.is_verified = 0";
        }

        if (!empty($registeredDate)) {
            $conditions[] = "DATE(s.created_at) = ?";
            $params[] = $registeredDate;
            $types .= 's';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY s.created_at DESC";
        
        return self::fetchAll($sql, $types, $params) ?? [];
    }

    /**
     * Fetches statistics about shelters.
     * @return array An associative array with 'total', 'verified', and 'unverified' counts.
     */
    public static function getShelterStats(): array {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) AS verified,
                    SUM(CASE WHEN is_verified = 0 THEN 1 ELSE 0 END) AS unverified
                FROM shelters";
        
        $stats = self::fetchOne($sql);

        return [
            'total' => $stats['total'] ?? 0,
            'verified' => $stats['verified'] ?? 0,
            'unverified' => $stats['unverified'] ?? 0,
        ];
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