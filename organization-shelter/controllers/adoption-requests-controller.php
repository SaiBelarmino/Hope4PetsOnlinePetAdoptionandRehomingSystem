<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionRequestsController extends BaseController {
    public static function listForShelter(int $shelterId, string $status = null, int $limit = 100): array {
        if ($status) {
            $sql = "SELECT ar.id, ar.user_id, ar.pet_id, ar.status, ar.created_at, u.username, p.name AS pet_name
                    FROM adoption_requests ar
                    JOIN users u ON u.id = ar.user_id
                    JOIN pets p ON p.id = ar.pet_id
                    WHERE ar.shelter_id = ? AND ar.status = ?
                    ORDER BY ar.created_at DESC LIMIT ?";
            return self::fetchAll($sql, 'isi', [$shelterId, $status, $limit]);
        }
        $sql = "SELECT ar.id, ar.user_id, ar.pet_id, ar.status, ar.created_at, u.username, p.name AS pet_name
                FROM adoption_requests ar
                JOIN users u ON u.id = ar.user_id
                JOIN pets p ON p.id = ar.pet_id
                WHERE ar.shelter_id = ?
                ORDER BY ar.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$shelterId, $limit]);
    }
}
?>