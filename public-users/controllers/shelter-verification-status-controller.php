<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ShelterVerificationStatusController extends BaseController {
    public static function get(int $shelterId): array {
        $row = self::fetchOne("SELECT verification_status, updated_at FROM shelters WHERE id=?", 'i', [$shelterId]);
        return $row ?? ['verification_status' => 'unknown'];
    }
}
?>