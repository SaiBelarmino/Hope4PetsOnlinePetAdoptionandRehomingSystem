<?php
// AddCommentController.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/PostManagementController.php';

// helper to set flash messages whether SessionManager exists or not
if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message) {
        if (class_exists('SessionManager')) {
            \SessionManager::setFlash($type, $message);
            return;
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

class AddCommentController extends BaseController {
    protected static function getDbConnection() {
        if (method_exists('BaseController', 'getDB')) {
            return BaseController::getDB();
        }
        if (method_exists('BaseController', 'db')) {
            return BaseController::db();
        }
        return null;
    }

    public static function add(array $data) {
        // Accept multiple possible field names for post id and content
        $postId = (int)trim((string)($data['post_id'] ?? $data['postId'] ?? $data['id'] ?? 0));
        $content = trim((string)($data['comment_text'] ?? $data['content'] ?? $data['comment'] ?? $data['message'] ?? $data['text'] ?? ''));
        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            set_flash('error', 'You must be logged in to comment.');
            header('Location: ../views/PostView.php?id=' . urlencode($postId));
            exit;
        }

        if ($postId <= 0 || $content === '') {
            set_flash('error', 'Invalid comment data.');
            header('Location: ../views/PostView.php?id=' . urlencode($postId));
            exit;
        }

        // Get post to find pet_id
        $post = PostManagementController::getPost($postId);
        if (!$post) {
            set_flash('error', 'Post not found.');
            header('Location: ../views/PostView.php?id=' . urlencode($postId));
            exit;
        }

        $db = self::getDbConnection();
        if (!$db) {
            set_flash('error', 'Database connection not available.');
            header('Location: ../views/PostView.php?id=' . urlencode($postId));
            exit;
        }

        if (!empty($post['pet_id'])) {
            $petId = (int)$post['pet_id'];
            $stmt = $db->prepare("INSERT INTO pet_comments (pet_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
            if (!$stmt) { set_flash('error', 'Failed to prepare statement.'); header('Location: ../views/PostView.php?id=' . urlencode($postId)); exit; }
            $stmt->bind_param('iis', $petId, $userId, $content);
            $ok = $stmt->execute();
        } else {
            $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
            if (!$stmt) { set_flash('error', 'Failed to prepare statement.'); header('Location: ../views/PostView.php?id=' . urlencode($postId)); exit; }
            $stmt->bind_param('iis', $postId, $userId, $content);
            $ok = $stmt->execute();
        }

        if ($ok) {
            set_flash('success', 'Comment added.');
        } else {
            set_flash('error', 'Failed to add comment.');
        }
        header('Location: ../views/PostView.php?id=' . urlencode($postId));
        exit;
    }
}

// If called directly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AddCommentController::add($_POST);
}
?>