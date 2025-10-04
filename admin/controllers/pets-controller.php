<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PetsController extends BaseController {
    public static function listAll(int $limit = 200): array {
        $sql = "SELECT id, name, species, breed, age, status, created_at FROM pets ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }

    public static function find(int $id): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id = ?", 'i', [$id]);
    }
}
?>