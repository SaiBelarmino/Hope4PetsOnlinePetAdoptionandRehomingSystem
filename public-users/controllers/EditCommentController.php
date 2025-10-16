<?php
// EditCommentController.php
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

class EditCommentController extends BaseController {
    protected static function getDbConnection() {
        if (method_exists('BaseController', 'getDB')) {
            return BaseController::getDB();
        }
        if (method_exists('BaseController', 'db')) {
            return BaseController::db();
        }
        return null;
    }

    public static function edit(array $data) {
        $commentId = isset($data['comment_id']) ? (int)$data['comment_id'] : 0;
        $text = trim($data['comment_text'] ?? '');
        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            set_flash('error', 'You must be logged in to edit a comment.');
            header('Location: ../views/index.php');
            exit;
        }

        if ($commentId <= 0 || $text === '') {
            set_flash('error', 'Invalid data.');
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
        $targetTable = '';
        $petId = null;
        $postId = 0;
        if ($row) {
            $targetTable = 'pet_comments';
            $petId = (int)$row['pet_id'];
            if ((int)$row['user_id'] !== (int)$userId) { set_flash('error', 'You are not allowed to edit this comment.'); $p = $db->prepare("SELECT id FROM posts WHERE pet_id = ? LIMIT 1"); $p->bind_param('i', $petId); $p->execute(); $r2 = $p->get_result()->fetch_assoc(); $postId = $r2['id'] ?? 0; header('Location: ../views/PostView.php?id=' . urlencode($postId)); exit; }
        } else {
            // try post_comments
            $stmt2 = $db->prepare("SELECT post_id, user_id FROM post_comments WHERE id = ? LIMIT 1");
            $stmt2->bind_param('i', $commentId);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $row2 = $res2->fetch_assoc();
            if ($row2) {
                $targetTable = 'post_comments';
                if ((int)$row2['user_id'] !== (int)$userId) { set_flash('error', 'You are not allowed to edit this comment.'); header('Location: ../views/index.php'); exit; }
                $postId = (int)$row2['post_id'];
            } else {
                set_flash('error', 'Comment not found.');
                header('Location: ../views/index.php');
                exit;
            }
        }

        if ($targetTable === 'pet_comments') {
            $update = $db->prepare("UPDATE pet_comments SET content = ? WHERE id = ?");
            $update->bind_param('si', $text, $commentId);
            $ok = $update->execute();
            // redirect to post that references this pet
            $p = $db->prepare("SELECT id FROM posts WHERE pet_id = ? LIMIT 1");
            $p->bind_param('i', $petId);
            $p->execute();
            $r2 = $p->get_result()->fetch_assoc();
            $postId = $r2['id'] ?? 0;
        } else {
            $update = $db->prepare("UPDATE post_comments SET content = ? WHERE id = ?");
            $update->bind_param('si', $text, $commentId);
            $ok = $update->execute();
        }

        if ($ok) set_flash('success', 'Comment updated.'); else set_flash('error', 'Failed to update comment.');
        header('Location: ../views/PostView.php?id=' . urlencode($postId));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    EditCommentController::edit($_POST);
}
?>