<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicCreatePostController extends BaseController {
    public static function create(array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO posts (user_id, title, body, status, created_at) VALUES (?,?,?,?,NOW())");
        if (!$stmt) return false;
        $status = $data['status'] ?? 'published';
        $stmt->bind_param('isss', $data['user_id'], $data['title'], $data['body'], $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>