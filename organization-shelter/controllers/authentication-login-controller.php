<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ShelterAuthenticationLoginController extends BaseController {
    public static function authenticate(string $email, string $password): ?array {
        $user = self::fetchOne("SELECT id, email, password_hash, name, status FROM shelters WHERE email = ? LIMIT 1", 's', [$email]);
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'] ?? '')) return null;
        unset($user['password_hash']);
        return $user;
    }
}
?>