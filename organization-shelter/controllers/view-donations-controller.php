<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ViewDonationsController extends BaseController {
    public static function listForShelter(int $shelterId, int $limit = 200): array {
        $sql = "SELECT id, amount, user_id, created_at FROM donations WHERE shelter_id=? ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$shelterId, $limit]);
    }
}
?>