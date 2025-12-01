<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class DonationsByShelterController extends BaseController {
    public static function totals(): array {
        $sql = "SELECT 
                    s.name AS shelter_name, 
                    s.id AS shelter_id, 
                    COALESCE(SUM(d.amount), 0) AS total_amount, 
                    COUNT(d.id) AS total_donations
                FROM shelters s
                LEFT JOIN donations d ON s.id = d.shelter_id
                GROUP BY s.id, s.name 
                ORDER BY total_amount DESC";
        return self::fetchAll($sql);
    }
}
?>