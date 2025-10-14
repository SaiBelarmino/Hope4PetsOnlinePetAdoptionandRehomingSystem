<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class ProfileController extends BaseController {
    /**
     * Get user profile by ID
     */
    public static function get(int $userId): ?array {
        $sql = "SELECT id, full_name, email, birthday, gender, profile_photo, location, 
                       contact_number, is_verified, created_at
                FROM users WHERE id = ?";
        return self::fetchOne($sql, 'i', [$userId]);
    }

    /**
     * Get user's shelter if they have one
     */
    public static function getShelter(int $userId): ?array {
    $sql = "SELECT id, shelter_name, address
        FROM shelters WHERE user_id = ?";
    return self::fetchOne($sql, 'i', [$userId]);
    }

    /**
     * Get user statistics
     */
    public static function getStats(int $userId): array {
        $mysqli = self::db();

        // Count pets (use owner_id)
        $pets = self::fetchValue("SELECT COUNT(*) FROM pets WHERE owner_id = ?", 'i', [$userId]) ?? 0;

        // Count donations (use donor_id)
        $donations = self::fetchValue("SELECT COUNT(*) FROM donations WHERE donor_id = ?", 'i', [$userId]) ?? 0;

        // Count posts
        $posts = self::fetchValue("SELECT COUNT(*) FROM posts WHERE user_id = ?", 'i', [$userId]) ?? 0;

        return [
            'pets' => (int)$pets,
            // 'adoptions' => (int)$adoptions, // Removed because table does not exist
            'donations' => (int)$donations,
            'posts' => (int)$posts
        ];
    }

    /**
     * Get user's recent posts
     */
    public static function getPosts(int $userId, int $limit = 10): array {
        $sql = "SELECT p.id, p.content, p.created_at,
                       (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as reaction_count,
                       (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
                FROM posts p
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        return self::fetchAll($sql, 'ii', [$userId, $limit]);
    }
}
?>