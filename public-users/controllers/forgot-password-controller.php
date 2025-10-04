<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class ForgotPasswordController extends BaseController {
    public static function createToken(int $userId, string $token): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO password_resets (user_id, token, created_at) VALUES (?,?,NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('is', $userId, $token);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>