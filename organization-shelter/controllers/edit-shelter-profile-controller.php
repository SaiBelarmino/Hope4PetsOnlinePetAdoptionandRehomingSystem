<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class EditShelterProfileController extends BaseController {
    public static function update(int $shelterId, array $data): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE shelters SET name=?, city=?, email=? WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param('sssi', $data['name'], $data['city'], $data['email'], $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows >= 0;
    }
}
?>