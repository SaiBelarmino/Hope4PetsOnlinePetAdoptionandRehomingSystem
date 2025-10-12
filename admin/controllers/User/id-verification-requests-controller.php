<?php
namespace App\Controllers\User;

require_once __DIR__ . '/../../controllers/BaseController.php';

class IdVerificationRequestsController extends BaseController {
    public static function pending(int $limit = 100): array {
        $sql = "SELECT id, user_id, document_type, status, created_at FROM id_verification_requests WHERE status='pending' ORDER BY created_at ASC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>