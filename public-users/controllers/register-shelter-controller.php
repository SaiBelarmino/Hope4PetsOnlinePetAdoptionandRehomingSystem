<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class RegisterShelterController extends BaseController {
    public static function register(array $data): bool {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO shelters (name, email, password_hash, status, verification_status, created_at) VALUES (?,?,?,?,?,NOW())");
        if (!$stmt) return false;
        $status = 'pending';
        $verification = 'pending';
        $stmt->bind_param('sssss', $data['name'], $data['email'], $hash, $status, $verification);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>