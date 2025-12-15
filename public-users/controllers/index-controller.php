<?php
/**
 * Index/Dashboard Controller
 * 
 * Provides data for the main dashboard.
 * Shows personalized content based on logged-in user.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Initialize session
SessionManager::init();

class IndexController extends BaseController {
    /**
     * Get featured pets
     */
    public static function featuredPets(int $limit = 6): array {
        return self::fetchAll(
            "SELECT p.id, p.name, p.species, p.breed, p.age, p.gender, p.status, p.location,
                    (SELECT photo_path FROM pet_photos WHERE pet_id = p.id AND is_primary = 1 LIMIT 1) as primary_photo,
                    s.shelter_name
             FROM pets p
             LEFT JOIN shelters s ON p.shelter_id = s.id
             WHERE p.status = 'available'
             ORDER BY p.created_at DESC
             LIMIT ?",
            'i',
            [$limit]
        );
    }
    
    /**
     * Get recent posts (community feed)
     */
    public static function getRecentPosts(int $limit = 10, int $offset = 0): array {
        $userId = $_SESSION['user']['id'] ?? null;
        $likedSelect = $userId
            ? ", (SELECT 1 FROM post_reactions pr WHERE pr.post_id = p.id AND pr.user_id = " . intval($userId) . " LIMIT 1) AS liked_by_me"
            : ", NULL AS liked_by_me";
        $sql = "SELECT p.*, u.full_name, u.profile_photo,
                    (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as reaction_count,
                    (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
                    {$likedSelect}
             FROM posts p
             JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?";
        return self::fetchAll($sql, 'ii', [$limit, $offset]);
    }
    
    /**
     * Get post photos
     */
    public static function getPostPhotos(int $postId): array {
        return self::fetchAll(
            "SELECT * FROM post_photos WHERE post_id = ? ORDER BY id ASC",
            'i',
            [$postId]
        );
    }

    /**
     * Get a single post with user info
     */
    public static function getPostById(int $postId): ?array {
        return self::fetchOne(
            "SELECT p.*, u.full_name, u.profile_photo
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.id = ? LIMIT 1",
            'i',
            [$postId]
        );
    }

    /**
     * Get post videos
     */
    public static function getPostVideos(int $postId): array {
        return self::fetchAll(
            "SELECT * FROM post_videos WHERE post_id = ? ORDER BY id ASC",
            'i',
            [$postId]
        );
    }
    
    /**
     * Get user statistics (if logged in)
     */
    public static function getUserStats(int $userId): array {
        $stats = [
            'my_pets' => self::fetchValue("SELECT COUNT(*) FROM pets WHERE owner_id = ?", 'i', [$userId], 0),
            'my_adoptions' => self::fetchValue("SELECT COUNT(*) FROM adoptions WHERE applicant_id = ?", 'i', [$userId], 0),
            'my_donations' => self::fetchValue("SELECT COUNT(*) FROM donations WHERE donor_id = ?", 'i', [$userId], 0),
            'my_posts' => self::fetchValue("SELECT COUNT(*) FROM posts WHERE user_id = ?", 'i', [$userId], 0),
            'unread_messages' => self::fetchValue("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0", 'i', [$userId], 0),
        ];
        
        return $stats;
    }
    
    /**
     * Get trending shelters
     */
    public static function getTrendingShelters(int $limit = 5): array {
        return self::fetchAll(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM donations WHERE shelter_id = s.id) as donation_count,
                    (SELECT COUNT(*) FROM pets WHERE shelter_id = s.id AND status = 'available') as pet_count
             FROM shelters s
             WHERE s.verified_badge = 1
             ORDER BY donation_count DESC
             LIMIT ?",
            'i',
            [$limit]
        );
    }
}
?>