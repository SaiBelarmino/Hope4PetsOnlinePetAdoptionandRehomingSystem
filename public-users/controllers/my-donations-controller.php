<?php
/**
 * My Donations Controller
 * 
 * Shows donation history for the logged-in user only.
 * Each user sees only their own donations.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Require login
SessionManager::requireLogin();

// Get user ID from session
$userId = SessionManager::getUserId();

class MyDonationsController extends BaseController {
    /**
     * Get donations made by the logged-in user
     */
    public static function list(int $userId, int $limit = 100): array {
        return self::fetchAll(
            "SELECT d.*, s.shelter_name, s.address
             FROM donations d
             LEFT JOIN shelters s ON d.shelter_id = s.id
             WHERE d.donor_id = ?
             ORDER BY d.created_at DESC
             LIMIT ?",
            'ii',
            [$userId, $limit]
        );
    }
    
    /**
     * Get donation statistics for user
     */
    public static function getStats(int $userId): array {
        $stats = [
            'total_donated' => self::fetchValue(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donor_id = ?",
                'i',
                [$userId],
                0
            ),
            'donation_count' => self::fetchValue(
                "SELECT COUNT(*) FROM donations WHERE donor_id = ?",
                'i',
                [$userId],
                0
            ),
            'shelters_supported' => self::fetchValue(
                "SELECT COUNT(DISTINCT shelter_id) FROM donations WHERE donor_id = ?",
                'i',
                [$userId],
                0
            ),
        ];
        
        return $stats;
    }
}

// Fetch data for view
$pageTitle = 'My Donations';
$donations = MyDonationsController::list($userId);
$stats = MyDonationsController::getStats($userId);
?>