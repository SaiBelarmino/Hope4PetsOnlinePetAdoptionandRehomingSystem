<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PostManagementController extends BaseController {
    // Get a single post by id
    public static function getPost(int $postId): ?array {
        return self::fetchOne("SELECT p.id, p.user_id, p.content, p.created_at, p.pet_id, u.full_name, u.profile_photo,
            (SELECT COUNT(*) FROM pet_comments pc WHERE pc.pet_id = p.pet_id) AS comment_count
            FROM posts p
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.id = ?", 'i', [$postId]);
    }

    public static function getPostPhotos(int $postId): array {
        return self::fetchAll("SELECT id, post_id, photo_path FROM post_photos WHERE post_id = ? ORDER BY id ASC", 'i', [$postId]);
    }

    public static function getPostVideos(int $postId): array {
        return self::fetchAll("SELECT id, post_id, video_path FROM post_videos WHERE post_id = ? ORDER BY id ASC", 'i', [$postId]);
    }

    public static function getComments(int $postId): array {
        // Determine whether post references a pet
        $post = self::getPost($postId);
        if (!$post) return [];
        if (!empty($post['pet_id'])) {
            // comments stored in pet_comments
            return self::fetchAll("SELECT pc.id, pc.pet_id, pc.user_id, pc.content AS comment_text, pc.created_at, u.full_name, u.profile_photo
                FROM pet_comments pc
                LEFT JOIN users u ON u.id = pc.user_id
                WHERE pc.pet_id = ?
                ORDER BY pc.created_at ASC", 'i', [(int)$post['pet_id']]);
        } else {
            // comments stored in post_comments (posts that aren't pet posts)
            return self::fetchAll("SELECT pc.id, pc.post_id, pc.user_id, pc.content AS comment_text, pc.created_at, u.full_name, u.profile_photo
                FROM post_comments pc
                LEFT JOIN users u ON u.id = pc.user_id
                WHERE pc.post_id = ?
                ORDER BY pc.created_at ASC", 'i', [$postId]);
        }
    }
}
?>