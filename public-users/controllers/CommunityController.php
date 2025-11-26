<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class CommunityController extends BaseController {

    /**
     * Get all community success stories (text-only)
     */
    public static function getStories($category = '', $theme = ''): array {
        $sql = "SELECT p.id, p.user_id, p.title, p.content, p.created_at, u.full_name,
                   (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as reaction_count,
                   (SELECT COUNT(*) FROM post_comments WHERE post_id = p.id) as comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id";
        $params = [];
        $types = '';

        $where = [];
        if ($category) {
            $where[] = "p.category = ?";
            $params[] = $category;
            $types .= 's';
        }
        if ($theme) {
            $where[] = "p.theme = ?";
            $params[] = $theme;
            $types .= 's';
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY p.created_at DESC";

        $stories = self::fetchAll($sql, $types, $params);

        foreach ($stories as &$story) {
            $story['comments'] = self::fetchAll(
                "SELECT c.content, c.created_at, u.full_name as user_name
                 FROM post_comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ?
                 ORDER BY c.created_at ASC",
                'i',
                [$story['id']]
            );
        }
        return $stories;
    }

    /**
     * Create a new success story
     */
    public static function createStory(int $userId, string $title, string $category, string $theme, string $content): bool {
        $title = trim($title);
        $category = trim($category);
        $theme = trim($theme);
        $content = trim($content);
        if (empty($title) || empty($category) || empty($theme) || empty($content)) return false;

        $sql = "INSERT INTO posts (user_id, title, category, theme, content, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        return self::execute($sql, 'issss', [$userId, $title, $category, $theme, $content]);
    }

    /**
     * Get reactions for a specific story
     */
    public static function getReactions(int $postId): int {
        return (int) self::fetchValue(
            "SELECT COUNT(*) FROM post_reactions WHERE post_id = ?",
            'i',
            [$postId]
        );
    }

    /**
     * Get comments for a specific story
     */
    public static function getComments(int $postId): array {
        return self::fetchAll(
            "SELECT c.content, c.created_at, u.full_name as user_name
             FROM post_comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC",
            'i',
            [$postId]
        );
    }
}
