<?php
namespace App\Controllers\Shelter;

require_once __DIR__ . '/../../controllers/BaseController.php';

class SheltersController extends BaseController {
    public static function listAll(int $limit = 200): array {
        $sql = "SELECT id, name, city, status, created_at FROM shelters ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>