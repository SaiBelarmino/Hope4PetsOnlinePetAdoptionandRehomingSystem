<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MyDonationsController extends BaseController {
    public static function list(int $userId, int $limit = 100): array {
        $sql = "SELECT id, amount, shelter_id, created_at FROM donations WHERE user_id=? ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>