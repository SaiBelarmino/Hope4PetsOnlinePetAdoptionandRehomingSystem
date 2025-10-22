<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class ProfileController extends BaseController {
    /**
     * Get user profile by ID
     */
    public static function get(int $userId): ?array {
        $sql = "SELECT id, full_name, email, birthday, gender, profile_photo, location, 
                       contact_number, is_verified, created_at, updated_at
                FROM users WHERE id = ?";
        $user = self::fetchOne($sql, 'i', [$userId]);
        if ($user) {
            $user['age'] = self::calculateAge($user['birthday']);
            // Ensure we only treat a user as verified when admin actually approved uploaded ID documents.
            // Relying on the user_documents table's approved status is the source of truth.
            $approvedCount = (int) self::fetchValue("SELECT COUNT(*) FROM user_documents WHERE user_id = ? AND status = 'approved'", 'i', [$userId]) ?? 0;
            $user['is_verified'] = $approvedCount > 0 ? 1 : 0;
        }
        return $user;
    }

    /**
     * Calculate age from birthday
     */
    private static function calculateAge(?string $birthday): ?int {
        if (!$birthday) return null;
        $birthDate = new DateTime($birthday);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
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
    public static function getPosts(int $userId): array {
        $posts = self::fetchAll(
            "SELECT p.*, u.full_name, u.profile_photo,
                    (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as reaction_count,
                    (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC",
            'i',
            [$userId]
        );

        foreach ($posts as &$post) {
            // Get media (photos and videos)
            $photos = self::fetchAll(
                "SELECT CONCAT('/Hope4PetsOnlinePetAdoptionandRehomingSystem/', photo_path) as url, 'image' as type FROM post_photos WHERE post_id = ? ORDER BY id ASC",
                'i',
                [$post['id']]
            );
            $videos = self::fetchAll(
                "SELECT CONCAT('/Hope4PetsOnlinePetAdoptionandRehomingSystem/', video_path) as url, 'video' as type FROM post_videos WHERE post_id = ? ORDER BY id ASC",
                'i',
                [$post['id']]
            );
            $post['media'] = array_merge($photos, $videos);

            // Get comments
            $post['comments'] = self::fetchAll(
                "SELECT c.content, c.created_at, u.full_name as user_name
                 FROM post_comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC",
                'i',
                [$post['id']]
            );
        }

        return $posts;
    }
}
?>