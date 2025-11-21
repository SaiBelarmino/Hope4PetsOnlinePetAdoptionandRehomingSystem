<?php

    // Legacy filename kept (plural) for compatibility. Provides AdminDashboardController.
    require_once __DIR__ . '/../../../controllers/BaseController.php';

    class AdminDashboardController extends BaseController {
        /**
         * Return high-level stats for admin dashboard.
         * TODO: Replace placeholder queries with real table names/logic.
         */
        public static function stats(): array {
            return [
                // Pets
                'total_pets' => (int) self::fetchValue("SELECT COUNT(*) FROM pets", '', [] , 0),

                // Users
                'registered_users' => (int) self::fetchValue("SELECT COUNT(*) FROM users", '', [] , 0),

                // Shelters
                'total_shelters' => (int) self::fetchValue("SELECT COUNT(*) FROM shelters", '', [] , 0),

                // Adoption requests
                'adoption_requests_total' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests", '', [] , 0),
                'adoption_requests_pending' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE status='pending'", '', [] , 0),
                'approved_adoptions' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE status='approved'", '', [] , 0),

                // Rehoming / surrender requests
                'rehoming_requests_total' => (int) self::fetchValue("SELECT COUNT(*) FROM rehoming_requests", '', [] , 0),

                // Donations (example existing metric)
                'today_donations' => (float) self::fetchValue("SELECT COALESCE(SUM(amount),0) FROM donations WHERE DATE(created_at)=CURDATE()", '', [] , 0.0)
            ];
        }
    }
?>
