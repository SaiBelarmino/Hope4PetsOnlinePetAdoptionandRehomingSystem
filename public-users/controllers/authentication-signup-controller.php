<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicAuthenticationSignupController extends BaseController {
    public static function register(array $data): bool {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, status, created_at) VALUES (?,?,?,?,NOW())");
        if (!$stmt) return false;
        $status = 'active';
        $stmt->bind_param('ssss', $data['username'], $data['email'], $hash, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>