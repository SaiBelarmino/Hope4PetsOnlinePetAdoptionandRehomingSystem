<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class PetPhotosReportsController extends BaseController {
    public static function listOpen(int $limit = 100): array {
        $sql = "SELECT r.id, r.photo_id, r.user_id, r.reason, r.status, r.created_at
                FROM pet_photos_reports r
                WHERE r.status = 'open' ORDER BY r.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>