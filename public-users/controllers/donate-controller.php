<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class DonateController extends BaseController {
    public static function make(int $userId, int $shelterId, float $amount): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO donations (user_id, shelter_id, amount, created_at) VALUES (?,?,?,NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('iid', $userId, $shelterId, $amount);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>