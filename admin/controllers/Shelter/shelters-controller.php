<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class SheltersController extends BaseController {
    public static function listAll(int $limit = 200): array {
        $sql = "SELECT id, name, city, status, created_at FROM shelters ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}


// Minimal controller to satisfy admin/views/Shelter/shelters.php
// Path: admin/controllers/Shelter/shelters-controller.php

class ShelterVerificationRequestsController {
    /**
     * Return a list of shelters with owner name and pet count.
     * By default this returns an empty array so the view can render safely.
     * If a Database class exists at config/Database.php, a best-effort query is attempted.
     */
    public static function listSheltersWithOwnerAndCount(): array {
        // Attempt to load a Database class if available (non-fatal)
        $dbConfigPath = dirname(__DIR__, 3) . '/config/Database.php';
        if (file_exists($dbConfigPath)) {
            try {
                include_once $dbConfigPath;
                if (class_exists('Database')) {
                    $dbInstance = Database::getInstance();
                    if (method_exists($dbInstance, 'getConnection')) {
                        $db = $dbInstance->getConnection();
                        // Best-effort query; table/column names may vary in your schema.
                        $sql = "SELECT s.id, s.name AS shelter_name, s.is_verified, 
                                   CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,'')) AS owner_name, 
                                   COUNT(p.id) AS pet_count
                                FROM shelters s
                                LEFT JOIN users u ON s.owner_id = u.id
                                LEFT JOIN pets p ON p.shelter_id = s.id
                                GROUP BY s.id";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        return $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            } catch (Throwable $e) {
                // Ignore and fall through to empty array to keep view functional.
            }
        }

        // Safe default: empty list
        return [];
    }
}
?>