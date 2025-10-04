<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostReactionsController extends BaseController {
    public static function summaryForPost(int $postId): array {
        $sql = "SELECT reaction_type, COUNT(*) as total FROM post_reactions WHERE post_id = ? GROUP BY reaction_type";
        return self::fetchAll($sql, 'i', [$postId]);
    }
}
?>