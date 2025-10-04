<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UploadPostPhotosController extends BaseController {
    public static function save(int $postId, array $filesMeta): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO post_photos (post_id, file_name, file_path, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        foreach ($filesMeta as $meta) {
            $stmt->bind_param('iss', $postId, $meta['name'], $meta['path']);
            if (!$stmt->execute()) { $stmt->close(); return false; }
        }
        $stmt->close();
        return true;
    }
}
?>