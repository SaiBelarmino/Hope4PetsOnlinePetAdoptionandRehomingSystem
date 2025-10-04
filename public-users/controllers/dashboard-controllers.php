<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PublicDashboardController extends BaseController {
	public static function stats(int $userId): array {
		return [
			'my_pets_adopted' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE user_id=? AND status='completed'", 'i', [$userId], 0),
			'my_donations' => (float) self::fetchValue("SELECT COALESCE(SUM(amount),0) FROM donations WHERE user_id=?", 'i', [$userId], 0.0),
			'my_posts' => (int) self::fetchValue("SELECT COUNT(*) FROM posts WHERE user_id=?", 'i', [$userId], 0)
		];
	}
}
?>
