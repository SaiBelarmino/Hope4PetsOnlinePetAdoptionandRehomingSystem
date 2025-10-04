<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicSheltersController extends BaseController {
    public static function listPublic(int $limit = 200): array {
        $sql = "SELECT id, name, city, verification_status FROM shelters WHERE status='active' ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>