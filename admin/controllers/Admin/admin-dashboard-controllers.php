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
				'total_pets' => (int) self::fetchValue("SELECT COUNT(*) FROM pets", '', [] , 0),
				'total_users' => (int) self::fetchValue("SELECT COUNT(*) FROM users", '', [] , 0),
				'pending_adoptions' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE status='pending'", '', [] , 0),
				'today_donations' => (float) self::fetchValue("SELECT COALESCE(SUM(amount),0) FROM donations WHERE DATE(created_at)=CURDATE()", '', [] , 0.0)
			];
		}
	}
?>
