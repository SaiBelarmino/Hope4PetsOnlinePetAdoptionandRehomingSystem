<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class DeletePostController extends BaseController {
    public static function delete(int $postId, int $userId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $postId, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>