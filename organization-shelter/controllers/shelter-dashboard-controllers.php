<?php
// ShelterDashboardController: provides stats for a specific shelter.
require_once __DIR__ . '/../../controllers/BaseController.php';

class ShelterDashboardController extends BaseController {
    public static function stats(int $shelterId): array {
        return [
            'pets_total' => (int) self::fetchValue("SELECT COUNT(*) FROM pets WHERE shelter_id=?", 'i', [$shelterId], 0),
            'pets_adopted' => (int) self::fetchValue("SELECT COUNT(*) FROM pets WHERE shelter_id=? AND status='adopted'", 'i', [$shelterId], 0),
            'adoptions_pending' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE shelter_id=? AND status='pending'", 'i', [$shelterId], 0),
            'donations_total' => (float) self::fetchValue("SELECT COALESCE(SUM(amount),0) FROM donations WHERE shelter_id=?", 'i', [$shelterId], 0.0)
        ];
    }
}
?>