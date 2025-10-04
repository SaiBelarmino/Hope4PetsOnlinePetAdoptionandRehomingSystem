<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UserReportsController extends BaseController {
    public static function listOpen(int $limit = 100): array {
        $sql = "SELECT r.id, r.reported_user_id, r.reporter_user_id, r.reason, r.status, r.created_at, u.username AS reported_username
                FROM user_reports r JOIN users u ON u.id = r.reported_user_id
                WHERE r.status='open' ORDER BY r.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>