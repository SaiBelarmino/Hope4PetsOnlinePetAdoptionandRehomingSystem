<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostCommentsController extends BaseController {
    public static function forPost(int $postId, int $limit = 200): array {
        $sql = "SELECT c.id, c.post_id, c.user_id, c.comment, c.created_at, u.username
                FROM post_comments c JOIN users u ON u.id = c.user_id
                WHERE c.post_id = ? ORDER BY c.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$postId, $limit]);
    }
}
?>