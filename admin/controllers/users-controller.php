<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class UsersController extends BaseController {
    public static function listAll(int $limit = 200): array {
        $sql = "SELECT id, username, email, status, created_at FROM users ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
?>