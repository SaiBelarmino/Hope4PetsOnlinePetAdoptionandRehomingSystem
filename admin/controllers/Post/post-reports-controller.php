<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class PostReportsController extends BaseController {
    public static function listOpen(int $limit = 100): array {
        $sql = "SELECT pr.id, pr.post_id, pr.reporter_id, pr.reason, pr.status, pr.created_at, p.content
                FROM post_reports pr JOIN posts p ON p.id = pr.post_id
                WHERE pr.status = 'open' ORDER BY pr.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }

    public static function closeReport(int $reportId): bool {
        $sql = "UPDATE post_reports SET status = 'closed' WHERE id = ?";
        return self::execute($sql, 'i', [$reportId]);
    }

    public static function deletePost(int $postId): bool {
        // This will also close the related reports due to foreign key constraints with ON DELETE CASCADE
        $sql = "DELETE FROM posts WHERE id = ?";
        return self::execute($sql, 'i', [$postId]);
    }

    public static function hidePost(int $postId): bool {
        $sql = "UPDATE posts SET status = 'hidden' WHERE id = ?";
        return self::execute($sql, 'i', [$postId]);
    }
}
?>