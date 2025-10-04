<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class EditProfileController extends BaseController {
    public static function update(int $userId, array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $data['username'], $data['email'], $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows >= 0;
    }
}
?>