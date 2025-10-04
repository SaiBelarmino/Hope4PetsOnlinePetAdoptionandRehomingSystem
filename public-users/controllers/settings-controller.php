<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class SettingsController extends BaseController {
    public static function preferences(int $userId): array {
        $row = self::fetchOne("SELECT notification_email, theme FROM user_settings WHERE user_id=?", 'i', [$userId]);
        return $row ?? ['notification_email' => 1, 'theme' => 'light'];
    }
}
?>