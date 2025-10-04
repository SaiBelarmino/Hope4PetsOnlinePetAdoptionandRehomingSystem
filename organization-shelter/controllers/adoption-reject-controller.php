<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionRejectController extends BaseController {
    public static function reject(int $requestId, int $shelterId, string $reason = null): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("UPDATE adoption_requests SET status='rejected', rejection_reason=?, updated_at=NOW() WHERE id=? AND shelter_id=? AND status='pending'");
        if (!$stmt) return false;
        $stmt->bind_param('sii', $reason, $requestId, $shelterId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $mysqli->affected_rows > 0;
    }
}
?>