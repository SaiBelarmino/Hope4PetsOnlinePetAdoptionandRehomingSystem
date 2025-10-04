<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PetReportsController extends BaseController {
    public static function listOpen(int $limit = 100): array {
        $sql = "SELECT r.id, r.pet_id, r.user_id, r.reason, r.status, r.created_at, p.name AS pet_name
                FROM pet_reports r JOIN pets p ON p.id = r.pet_id
                WHERE r.status = 'open' ORDER BY r.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>