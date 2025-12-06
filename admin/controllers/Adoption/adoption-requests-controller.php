<?php

require_once __DIR__ . '/../../../controllers/BaseController.php';

class AdoptionRequestsController extends BaseController {
    public static function getAdoptionRequests(int $limit = 100): array {
        $sql = "SELECT 
                    a.id, 
                    a.status, 
                    a.created_at,
                    p.name AS pet_name,
                    u.full_name AS applicant_name,
                    s.shelter_name,
                    aa.name as adoption_applicant_name,
                    aa.phone as adoption_applicant_phone,
                    aa.address as adoption_applicant_address
                FROM adoptions a
                JOIN pets p ON a.pet_id = p.id
                JOIN users u ON a.applicant_id = u.id
                LEFT JOIN shelters s ON a.shelter_id = s.id
                LEFT JOIN adoption_applicants aa ON a.id = aa.adoption_id
                ORDER BY a.created_at DESC 
                LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
