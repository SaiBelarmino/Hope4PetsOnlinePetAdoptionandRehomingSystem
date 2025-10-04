<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostReportsController extends BaseController {
    public static function listOpen(int $limit = 100): array {
        $sql = "SELECT pr.id, pr.post_id, pr.user_id, pr.reason, pr.status, pr.created_at, p.title
                FROM post_reports pr JOIN posts p ON p.id = pr.post_id
                WHERE pr.status = 'open' ORDER BY pr.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>