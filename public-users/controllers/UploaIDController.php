<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UploadIdController extends BaseController {
    public static function save(int $userId, array $filesMeta): bool {
        $mysqli = self::db();

        // Ensure user_documents table exists (simple migration fallback)
        $createSql = "CREATE TABLE IF NOT EXISTS user_documents (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            doc_type VARCHAR(100) DEFAULT NULL,
            file_path VARCHAR(500) NOT NULL,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_by BIGINT DEFAULT NULL,
            reviewed_at TIMESTAMP NULL DEFAULT NULL,
            INDEX (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if ($mysqli->query($createSql) === false) {
            error_log('Failed to ensure user_documents table: ' . $mysqli->error);
        }

        $stmt = $mysqli->prepare("INSERT INTO user_documents (user_id, doc_type, file_path, uploaded_at) VALUES (?,?,?,NOW())");
        if (!$stmt) {
            error_log('Prepare failed in UploadIdController::save (user_documents): ' . $mysqli->error);
            return false;
        }

        foreach ($filesMeta as $meta) {
            $docType = $meta['doc_type'] ?? null;
            $path = $meta['path'] ?? '';
            $stmt->bind_param('iss', $userId, $docType, $path);
            if (!$stmt->execute()) {
                error_log('Failed inserting user_documents: ' . $stmt->error);
                $stmt->close();
                return false;
            }
        }

        $stmt->close();
        return true;
    }
}
?>