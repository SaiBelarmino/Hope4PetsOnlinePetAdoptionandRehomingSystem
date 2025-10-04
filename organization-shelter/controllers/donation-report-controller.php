<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class DonationReportController extends BaseController {
    public static function summary(int $shelterId): array {
        $sql = "SELECT COUNT(*) AS total_donations, COALESCE(SUM(amount),0) AS total_amount FROM donations WHERE shelter_id=?";
        $row = self::fetchOne($sql, 'i', [$shelterId]);
        return $row ?? ['total_donations' => 0, 'total_amount' => 0];
    }
}
?>