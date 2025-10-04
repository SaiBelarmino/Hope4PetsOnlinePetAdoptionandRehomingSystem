<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MyShelterController extends BaseController {
    public static function info(int $shelterId): ?array {
        return self::fetchOne("SELECT id, name, city, status, verification_status FROM shelters WHERE id=?", 'i', [$shelterId]);
    }
}
?>