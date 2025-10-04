<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UploadDocumentsController extends BaseController {
    public static function save(int $shelterId, array $filesMeta): bool {
        // Placeholder: store metadata in table 'shelter_documents'
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO shelter_documents (shelter_id, file_name, file_path, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        foreach ($filesMeta as $meta) {
            $stmt->bind_param('iss', $shelterId, $meta['name'], $meta['path']);
            if (!$stmt->execute()) { $stmt->close(); return false; }
        }
        $stmt->close();
        return true;
    }
}
?>