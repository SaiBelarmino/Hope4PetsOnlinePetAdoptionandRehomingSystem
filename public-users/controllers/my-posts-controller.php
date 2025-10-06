<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MyPostsController extends BaseController {
    /**
     * List posts for the logged-in user with aggregate counts and optional pet.
     */
    public static function list(int $userId, int $limit = 100): array {
        $sql = "SELECT p.id, p.content, p.pet_id, p.created_at,
                       pet.name AS pet_name,
                       (SELECT COUNT(*) FROM post_photos pp WHERE pp.post_id = p.id) AS photo_count,
                       (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_id = p.id) AS reaction_count,
                       (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.id) AS comment_count
                FROM posts p
                LEFT JOIN pets pet ON pet.id = p.pet_id
                WHERE p.user_id=?
                ORDER BY p.created_at DESC
                LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>
