<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class CommunityController extends BaseController {
    public static function latestPosts(int $limit = 50): array {
        $sql = "SELECT id, user_id, title, created_at FROM posts WHERE status='published' ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>