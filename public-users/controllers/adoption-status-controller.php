<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionStatusController extends BaseController {
    public static function listMine(int $userId, int $limit = 100): array {
        $sql = "SELECT ar.id, ar.pet_id, ar.status, ar.created_at, p.name AS pet_name
                FROM adoption_requests ar JOIN pets p ON p.id = ar.pet_id
                WHERE ar.user_id=? ORDER BY ar.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>