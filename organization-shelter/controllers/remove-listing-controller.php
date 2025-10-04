<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class RemoveListingController extends BaseController {
    public static function remove(int $petId, int $shelterId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("DELETE FROM pets WHERE id=? AND shelter_id=?");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $petId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>