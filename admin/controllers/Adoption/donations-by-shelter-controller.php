<?php
namespace App\Controllers\Adoption;

require_once __DIR__ . '/../../controllers/BaseController.php';

class DonationsByShelterController extends BaseController {
    public static function totals(): array {
        $sql = "SELECT shelter_id, COALESCE(SUM(amount),0) AS total_amount, COUNT(*) AS total_donations
                FROM donations GROUP BY shelter_id ORDER BY total_amount DESC";
        return self::fetchAll($sql);
    }
}
?>