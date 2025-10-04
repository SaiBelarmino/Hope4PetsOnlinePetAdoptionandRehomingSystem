<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PetViewController extends BaseController {
    public static function get(int $petId): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id=?", 'i', [$petId]);
    }
}
?>