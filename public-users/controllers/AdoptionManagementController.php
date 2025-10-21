<?php
require_once __DIR__ . '/../../controllers/BaseController.php';


class AdoptController extends BaseController {
    public static function details(int $petId): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id=?", 'i', [$petId]);
    }

    public static function request(int $userId, int $petId): bool {
        $mysqli = self::db();
        $stmt = $mysqli->prepare("INSERT INTO adoption_applicants (user_id, pet_id, status, created_at) VALUES (?,?, 'pending', NOW())");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $userId, $petId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function getRequests(int $shelterId): array {
        $query = "SELECT a.id, a.pet_id, a.applicant_id, a.status, a.created_at,
                         p.name AS pet_name, p.pet_photos AS pet_photo, p.owner_id,
                         aa.name AS applicant_name, aa.phone AS applicant_phone, aa.address AS applicant_address, aa.message AS applicant_message, aa.created_at AS applicant_created_at
                  FROM adoptions a
                  JOIN pets p ON a.pet_id = p.id
                  LEFT JOIN adoption_applicants aa ON a.id = aa.adoption_id
                  WHERE a.shelter_id = ?
                  ORDER BY a.created_at DESC";
        return self::fetchAll($query, 'i', [$shelterId]);
    }
}
?>