<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UploadIdController extends BaseController {
    public static function save(int $userId, array $filesMeta): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO user_id_documents (user_id, file_name, file_path, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        foreach ($filesMeta as $meta) {
            $stmt->bind_param('iss', $userId, $meta['name'], $meta['path']);
            if (!$stmt->execute()) { $stmt->close(); return false; }
        }
        $stmt->close();
        return true;
    }
}
?>