<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostsController extends BaseController {
    public static function listRecent(int $limit = 100): array {
        $sql = "SELECT id, user_id, title, created_at, status FROM posts ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>