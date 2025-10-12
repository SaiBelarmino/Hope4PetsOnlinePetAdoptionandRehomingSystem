<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class DonationsController extends BaseController {
    public static function recent(int $limit = 100): array {
        $sql = "SELECT d.id, d.user_id, d.amount, d.created_at, d.shelter_id FROM donations d ORDER BY d.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>