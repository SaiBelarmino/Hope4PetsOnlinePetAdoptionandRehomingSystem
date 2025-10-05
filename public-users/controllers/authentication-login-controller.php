<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicAuthenticationLoginController extends BaseController {
    /**
     * Attempt to authenticate a user by email/password.
     * Returns user data (without password_hash) or null.
     */
    public static function authenticate(string $email, string $password): ?array {
        $user = self::fetchOne('SELECT id, full_name, email, password_hash, is_verified, profile_photo FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'] ?? '')) return null;
        unset($user['password_hash']);
        return $user;
    }
}
?>