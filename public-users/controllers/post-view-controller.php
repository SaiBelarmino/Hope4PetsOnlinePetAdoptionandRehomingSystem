<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostViewController extends BaseController {
    public static function get(int $postId): ?array {
        return self::fetchOne("SELECT id, user_id, title, body, status, created_at FROM posts WHERE id=?", 'i', [$postId]);
    }
}
?>