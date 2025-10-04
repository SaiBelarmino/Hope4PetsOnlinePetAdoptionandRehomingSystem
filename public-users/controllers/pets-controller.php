<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicPetsController extends BaseController {
    public static function listAvailable(int $limit = 200): array {
        $sql = "SELECT id, name, species, status FROM pets WHERE status='available' ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>