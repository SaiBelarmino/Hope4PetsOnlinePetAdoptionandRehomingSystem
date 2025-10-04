<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class MarkAdoptedController extends BaseController {
    public static function mark(int $petId, int $shelterId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE pets SET status='adopted', updated_at=NOW() WHERE id=? AND shelter_id=? AND status!='adopted'");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $petId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>