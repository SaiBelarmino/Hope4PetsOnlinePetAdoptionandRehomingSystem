<?php
namespace App\Controllers\Adoption;

require_once __DIR__ . '/../../controllers/BaseController.php';

class AdoptionRequestsController extends BaseController {
    public static function pending(int $limit = 100): array {
        $sql = "SELECT ar.id, ar.user_id, ar.pet_id, ar.status, ar.created_at, u.username, p.name AS pet_name
                FROM adoption_requests ar
                JOIN users u ON u.id = ar.user_id
                JOIN pets p ON p.id = ar.pet_id
                WHERE ar.status = 'pending'
                ORDER BY ar.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
