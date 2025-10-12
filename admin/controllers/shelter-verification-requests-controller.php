<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ShelterVerificationRequestsController extends BaseController {
    public static function pending(int $limit = 100): array {
        $sql = "SELECT id, shelter_id, status, created_at FROM shelter_verification_requests WHERE status='pending' ORDER BY created_at ASC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }

    /**
     * Fetch shelter documents. If $shelterId is null, fetch all pending documents.
     * Returns array of rows with fields: id, shelter_id, doc_type, file_path, status, uploaded_at, reviewed_at
     */
    public static function fetchDocuments(?int $shelterId = null, int $limit = 500): array {
        $sql = "SELECT sd.id, sd.shelter_id, sd.doc_type, sd.file_path, sd.status, sd.uploaded_at, sd.reviewed_at, s.shelter_name as shelter_name, s.user_id as shelter_owner_user_id
                FROM shelter_documents sd
                LEFT JOIN shelters s ON sd.shelter_id = s.id
                WHERE sd.status = 'pending'
                " . ($shelterId ? " AND sd.shelter_id = ?" : "") . "
                ORDER BY sd.uploaded_at ASC
                LIMIT ?";
        if ($shelterId) {
            return self::fetchAll($sql, 'ii', [$shelterId, $limit]);
        }
        return self::fetchAll($sql, 'i', [$limit]);
    }

    public static function getDocumentById($id) {
        $sql = "SELECT id, shelter_id, doc_type, file_path, status, uploaded_at FROM shelter_documents WHERE id = ?";
        return self::fetchOne($sql, 'i', [$id]);
    }

    public static function updateDocumentStatus($id, $status, $adminId) {
        // use BaseController helpers if available
        $sql = "UPDATE shelter_documents SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
        return self::execute($sql, 'sii', [$status, $adminId, $id]);
    }

    public static function listSheltersWithOwnerAndCount(): array {
        $sql = "SELECT s.id, s.shelter_name, s.is_verified, s.user_id,
                       u.full_name AS owner_name, u.email AS owner_email,
                       (SELECT COUNT(*) FROM pets p WHERE p.shelter_id = s.id) AS pet_count
                FROM shelters s
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.shelter_name ASC";
        return self::fetchAll($sql);
    }
}
?>