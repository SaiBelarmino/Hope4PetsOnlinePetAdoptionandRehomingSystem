<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AuthenticationSignupController extends BaseController {
    public static function register(string $username, string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO admins (username, email, password_hash, role, status, created_at) VALUES (?,?,?,?,?,NOW())");
        if (!$stmt) return false;
        $role = 'admin';
        $status = 'active';
        $stmt->bind_param('sssss', $username, $email, $hash, $role, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>