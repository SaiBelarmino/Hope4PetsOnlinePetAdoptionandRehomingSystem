<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionApproveController extends BaseController {
    public static function approve(int $requestId, int $shelterId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE adoption_requests SET status='approved', updated_at=NOW() WHERE id=? AND shelter_id=? AND status='pending'");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $requestId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>