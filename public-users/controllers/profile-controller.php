<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ProfileController extends BaseController {
    public static function get(int $userId): ?array {
        return self::fetchOne("SELECT id, username, email, status FROM users WHERE id=?", 'i', [$userId]);
    }
}
?>