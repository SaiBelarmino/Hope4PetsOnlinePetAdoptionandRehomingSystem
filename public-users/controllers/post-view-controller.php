<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

class PostViewController extends BaseController {
    /**
     * Fetch a single post with user name, pet name and aggregated counts.
     */
    public static function get(int $postId): ?array {
        $sql = "SELECT p.id, p.user_id, p.content, p.pet_id, p.created_at,
                       u.full_name AS user_name, u.profile_photo,
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

    /** Fetch single video for a post (if any) */
    public static function video(int $postId): ?array {
        $v = self::fetchOne("SELECT video_path FROM post_videos WHERE post_id = ? ORDER BY id LIMIT 1", 'i', [$postId]);
        return $v ?: null;
    }

    /** Fetch comments with commenter names */
    public static function comments(int $postId): array {
        $sql = "SELECT c.id, c.user_id, u.full_name AS user_name, u.profile_photo, c.content, c.created_at
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

    /** Toggle reaction (like/unlike) */
    public static function toggleReaction(int $postId, int $userId): array {
        $mysqli = self::db();
        
        // Check if reaction exists
        $exists = self::fetchValue("SELECT id FROM post_reactions WHERE post_id=? AND user_id=?", 'ii', [$postId, $userId]);
        
        if ($exists) {
            // Unlike - remove reaction
            $stmt = $mysqli->prepare("DELETE FROM post_reactions WHERE post_id=? AND user_id=?");
            if (!$stmt) {
                return ['success' => false, 'action' => 'unliked', 'error' => $mysqli->error];
            }
            $stmt->bind_param('ii', $postId, $userId);
            $success = $stmt->execute();
            $error = $stmt->error;
            $stmt->close();
            return ['success' => $success, 'action' => 'unliked', 'error' => $error];
        } else {
            // Like - add reaction
            $stmt = $mysqli->prepare("INSERT INTO post_reactions (post_id, user_id, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) {
                return ['success' => false, 'action' => 'liked', 'error' => $mysqli->error];
            }
            $stmt->bind_param('ii', $postId, $userId);
            $success = $stmt->execute();
            $error = $stmt->error;
            $stmt->close();
            return ['success' => $success, 'action' => 'liked', 'error' => $error];
        }
    }

    /** Add a comment to a post */
    public static function addComment(int $postId, int $userId, string $content): array {
        $mysqli = self::db();
        
        if (trim($content) === '') {
            return ['success' => false, 'message' => 'Comment cannot be empty.'];
        }
        
        $stmt = $mysqli->prepare("INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iis', $postId, $userId, $content);
        $success = $stmt->execute();
        $commentId = $mysqli->insert_id;
        $stmt->close();
        
        return ['success' => $success, 'comment_id' => $commentId, 'message' => $success ? 'Comment added!' : 'Failed to add comment.'];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SessionManager::requireLogin();
    $userId = SessionManager::getUserId();
    $postId = (int)($_POST['post_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($postId <= 0) {
        SessionManager::setFlash('error', 'Invalid post.');
        header('Location: ../views/index.php');
        exit;
    }

    // Toggle Like/Unlike
    if ($action === 'toggle_like') {
        $result = PostViewController::toggleReaction($postId, $userId);
        // Just redirect, no flash message
        header('Location: ../views/post_view.php?id=' . $postId);
        exit;
    }

    // Add Comment
    if (empty($action) && !empty($_POST['comment'])) {
        $comment = trim($_POST['comment']);
        $result = PostViewController::addComment($postId, $userId, $comment);
        // Just redirect, no flash message
        header('Location: ../views/post_view.php?id=' . $postId);
        exit;
    }

    // Default redirect
    header('Location: ../views/post_view.php?id=' . $postId);
    exit;
}
?>
