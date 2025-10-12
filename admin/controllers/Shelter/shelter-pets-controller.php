<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class ShelterPetsController extends BaseController {
    public static function forShelter(int $shelterId, int $limit = 200): array {
        $sql = "SELECT id, name, species, status, created_at FROM pets WHERE shelter_id = ? ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'ii', [$shelterId, $limit]);
    }
}
?>