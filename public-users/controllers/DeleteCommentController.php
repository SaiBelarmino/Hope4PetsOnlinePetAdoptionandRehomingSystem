<?php
// DeleteCommentController.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../controllers/BaseController.php';

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message) {
        if (class_exists('SessionManager')) {
            \SessionManager::setFlash($type, $message);
            return;
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

class DeleteCommentController extends BaseController {
    protected static function getDbConnection() {
        if (method_exists('BaseController', 'getDB')) {
            return BaseController::getDB();
        }
        if (method_exists('BaseController', 'db')) {
            return BaseController::db();
        }
        return null;
    }

    public static function delete(array $data) {
        $commentId = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;
        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            set_flash('error', 'You must be logged in to delete a comment.');
            header('Location: ../views/index.php');
            exit;
        }

        if ($commentId <= 0) {
            set_flash('error', 'Invalid comment id.');
            header('Location: ../views/index.php');
            exit;
        }

        $db = self::getDbConnection();
        if (!$db) { set_flash('error', 'Database connection not available.'); header('Location: ../views/index.php'); exit; }

        // Try pet_comments first
        $stmt = $db->prepare("SELECT pet_id, user_id FROM pet_comments WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $commentId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $postId = 0;
        if ($row) {
            if ((int)$row['user_id'] !== (int)$userId) { set_flash('error', 'You are not allowed to delete this comment.'); $p = $db->prepare("SELECT id FROM posts WHERE pet_id = ? LIMIT 1"); $p->bind_param('i', $row['pet_id']); $p->execute(); $r2 = $p->get_result()->fetch_assoc(); $postId = $r2['id'] ?? 0; header('Location: ../views/PostView.php?id=' . urlencode($postId)); exit; }
            $del = $db->prepare("DELETE FROM pet_comments WHERE id = ?"); if (!$del) { set_flash('error', 'Failed to prepare delete.'); header('Location: ../views/index.php'); exit; } $del->bind_param('i', $commentId); $ok = $del->execute();
            // redirect to post referencing this pet
            $p = $db->prepare("SELECT id FROM posts WHERE pet_id = ? LIMIT 1");
            $p->bind_param('i', $row['pet_id']);
            $p->execute();
            $r2 = $p->get_result()->fetch_assoc();
            $postId = $r2['id'] ?? 0;
        } else {
            // try post_comments
            $stmt2 = $db->prepare("SELECT post_id, user_id FROM post_comments WHERE id = ? LIMIT 1");
            $stmt2->bind_param('i', $commentId);
            $stmt2->execute();
            $r2 = $stmt2->get_result()->fetch_assoc();
            if (!$r2) { set_flash('error', 'Comment not found.'); header('Location: ../views/index.php'); exit; }
            if ((int)$r2['user_id'] !== (int)$userId) { set_flash('error', 'You are not allowed to delete this comment.'); header('Location: ../views/PostView.php?id=' . urlencode($r2['post_id'])); exit; }
            $del = $db->prepare("DELETE FROM post_comments WHERE id = ?"); if (!$del) { set_flash('error', 'Failed to prepare delete.'); header('Location: ../views/index.php'); exit; } $del->bind_param('i', $commentId); $ok = $del->execute();
            $postId = $r2['post_id'];
        }

        if ($ok) {
            set_flash('success', 'Comment deleted.');
        } else {
            set_flash('error', 'Failed to delete comment.');
        }

        header('Location: ../views/PostView.php?id=' . urlencode($postId));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    DeleteCommentController::delete($_POST);
}
?>