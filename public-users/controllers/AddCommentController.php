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

        $isAjax = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );

        $error = null;
        $success = false;
        $newComment = null;

        if (!$userId) {
            $error = 'You must be logged in to comment.';
        } elseif ($postId <= 0 || $content === '') {
            $error = 'Invalid comment data.';
        } else {
            // Only allow comments on posts (not pets) for this controller
            $db = self::getDbConnection();
            if (!$db) {
                $error = 'Database connection not available.';
            } else {
                // Check if post exists in posts table
                $stmt = $db->prepare("SELECT id FROM posts WHERE id = ? LIMIT 1");
                $stmt->bind_param('i', $postId);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows === 0) {
                    $error = 'Post not found.';
                } else {
                    $stmt->close();
                    $stmt = $db->prepare("INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
                    if ($stmt) {
                        $stmt->bind_param('iis', $postId, $userId, $content);
                        $ok = $stmt->execute();
                    } else {
                        $ok = false;
                    }
                    if ($ok) {
                        $success = true;
                        // Prepare new comment data for AJAX response
                        $fullName = $_SESSION['user']['full_name'] ?? 'You';
                        $createdAt = date('Y-m-d H:i');
                        $newComment = [
                            'full_name' => $fullName,
                            'created_at' => $createdAt,
                            'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
                        ];
                    } else {
                        $error = 'Failed to add comment.';
                    }
                }
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true, 'comment' => $newComment]);
            } else {
                echo json_encode(['success' => false, 'error' => $error]);
            }
            exit;
        } else {
            if ($success) {
                set_flash('success', 'Comment added.');
            } else {
                set_flash('error', $error ?: 'Failed to add comment.');
            }
            header('Location: ../views/PostView.php?id=' . urlencode($postId));
            exit;
        }
    }
}

// If called directly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AddCommentController::add($_POST);
}
?>