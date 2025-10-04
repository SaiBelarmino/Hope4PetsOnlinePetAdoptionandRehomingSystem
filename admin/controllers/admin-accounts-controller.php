<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdminAccountsController extends BaseController {
    public static function listAll(): array {
        return self::fetchAll("SELECT id, username, email, role, status, created_at FROM admins ORDER BY created_at DESC");
    }
}
