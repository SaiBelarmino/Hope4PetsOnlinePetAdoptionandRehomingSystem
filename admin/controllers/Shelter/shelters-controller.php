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
                    u.email AS owner_email,
                    (
                        SELECT COUNT(*) 
                        FROM shelter_documents sd 
                        WHERE sd.shelter_id = s.id AND sd.status = 'approved'
                    ) AS approved_docs
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

        // Updated status logic
        if ($status === 'verified') {
            $conditions[] = "s.is_verified = 1 AND (
                SELECT COUNT(*) FROM shelter_documents sd WHERE sd.shelter_id = s.id AND sd.status = 'approved'
            ) > 0";
        } elseif ($status === 'unverified') {
            $conditions[] = "s.is_verified = 0 OR (
                SELECT COUNT(*) FROM shelter_documents sd WHERE sd.shelter_id = s.id AND sd.status = 'approved'
            ) = 0";
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
        
        $results = self::fetchAll($sql, $types, $params) ?? [];

        // Set status for each shelter based on both is_verified and approved_docs
        foreach ($results as &$shelter) {
            $shelter['status'] = (!empty($shelter['is_verified']) && $shelter['approved_docs'] > 0) ? 'Verified' : 'Unverified';
        }

        return $results;
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

    /**
     * Fetch shelter details by ID.
     */
    public static function getShelterById(int $id): ?array {
        $sql = "SELECT s.*, u.full_name AS owner_name, u.email AS owner_email
                FROM shelters s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?
                LIMIT 1";
        return self::fetchOne($sql, 'i', [$id]);
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

    /**
     * Fetch shelter details by ID.
     */
    public static function getShelterById(int $id): ?array {
        $sql = "SELECT s.*, u.full_name AS owner_name, u.email AS owner_email
                FROM shelters s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?
                LIMIT 1";
        return self::fetchOne($sql, 'i', [$id]);
    }
}