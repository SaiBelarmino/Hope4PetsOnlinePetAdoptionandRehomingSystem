<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MyPostsController extends BaseController {
    public static function list(int $userId, int $limit = 100): array {
        $sql = "SELECT id, title, status, created_at FROM posts WHERE user_id=? ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>