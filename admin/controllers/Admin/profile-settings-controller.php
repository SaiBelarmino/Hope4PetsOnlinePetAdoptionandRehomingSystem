<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class ProfileSettingsController extends BaseController {
    public static function get(int $adminId): ?array {
        return self::fetchOne("SELECT id, username, email, role, status FROM admins WHERE id = ?", 'i', [$adminId]);
    }
}
?>