<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostViewController extends BaseController {
    /**
     * Fetch a single post with user name, pet name and aggregated counts.
     */
    public static function get(int $postId): ?array {
        $sql = "SELECT p.id, p.user_id, p.content, p.pet_id, p.created_at,
                       u.full_name AS user_name,
                       pet.name AS pet_name,
                       (SELECT COUNT(*) FROM post_reactions r WHERE r.post_id = p.id) AS reaction_count,
                       (SELECT COUNT(*) FROM post_comments c WHERE c.post_id = p.id) AS comment_count
                FROM posts p
                JOIN users u ON u.id = p.user_id
                LEFT JOIN pets pet ON pet.id = p.pet_id
                WHERE p.id = ?";
        return self::fetchOne($sql, 'i', [$postId]);
    }

    /** Fetch photos for a post */
    public static function photos(int $postId): array {
        return self::fetchAll("SELECT photo_path FROM post_photos WHERE post_id = ? ORDER BY id", 'i', [$postId]);
    }

    /** Fetch comments with commenter names */
    public static function comments(int $postId): array {
        $sql = "SELECT c.id, c.user_id, u.full_name AS user_name, c.content, c.created_at
                FROM post_comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.post_id = ?
                ORDER BY c.created_at ASC";
        return self::fetchAll($sql, 'i', [$postId]);
    }

    /** Check if a user reacted */
    public static function userReacted(int $postId, int $userId): bool {
        return (bool) self::fetchValue("SELECT 1 FROM post_reactions WHERE post_id=? AND user_id=? LIMIT 1", 'ii', [$postId, $userId]);
    }
}
?>
