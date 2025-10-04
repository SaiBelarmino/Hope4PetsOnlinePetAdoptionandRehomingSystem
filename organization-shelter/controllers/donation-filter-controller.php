<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class DonationFilterController extends BaseController {
    public static function filterByDateRange(int $shelterId, string $start, string $end): array {
        $sql = "SELECT id, amount, created_at FROM donations WHERE shelter_id=? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC";
        return self::fetchAll($sql, 'iss', [$shelterId, $start, $end]);
    }
}
?>