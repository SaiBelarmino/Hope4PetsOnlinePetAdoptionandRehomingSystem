<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionCompletedController extends BaseController {
    public static function markComplete(int $requestId, int $shelterId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE adoption_requests SET status='completed', updated_at=NOW() WHERE id=? AND shelter_id=? AND status='approved'");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $requestId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>