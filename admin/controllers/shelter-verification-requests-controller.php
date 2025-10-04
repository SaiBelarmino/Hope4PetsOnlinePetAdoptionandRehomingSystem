<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ShelterVerificationRequestsController extends BaseController {
    public static function pending(int $limit = 100): array {
        $sql = "SELECT id, shelter_id, status, created_at FROM shelter_verification_requests WHERE status='pending' ORDER BY created_at ASC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>