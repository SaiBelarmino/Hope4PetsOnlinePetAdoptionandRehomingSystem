<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class DonationReceiptController extends BaseController {
    public static function get(int $donationId, int $userId): ?array {
        $sql = "SELECT d.id, d.amount, d.created_at, s.name AS shelter_name FROM donations d JOIN shelters s ON s.id = d.shelter_id WHERE d.id=? AND d.user_id=?";
        return self::fetchOne($sql, 'ii', [$donationId, $userId]);
    }
}
?>