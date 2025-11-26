<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

class PetsController extends BaseController {

    /**
     * Get all pets available (optionally filtered by type or status)
     */
    public static function getPets(string $species = '', string $status = ''): array {
        $sql = "SELECT id, name, species, age, gender, photo, status, description, created_at 
                FROM pets";
        $params = [];
        $types = '';
        $where = [];

        if (!empty($species)) {
            $where[] = "species = ?";
            $params[] = $species;
            $types .= 's';
        }

        if (!empty($status)) {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }

        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY created_at DESC";

        return self::fetchAll($sql, $types, $params);
    }

    /**
     * Get total number of pets
     */
    public static function getTotalPets(): int {
        return (int) self::fetchValue("SELECT COUNT(*) FROM pets");
    }

    /**
     * Get featured pets
     */
    public static function getFeaturedPets(): array {
        return self::fetchAll(
            "SELECT id, name, species, age, gender, photo, description 
             FROM pets 
             WHERE is_featured = 1 
             ORDER BY created_at DESC"
        );
    }

    /**
     * Get newly added pets
     */
    public static function getNewArrivals(int $limit = 6): array {
        return self::fetchAll(
            "SELECT id, name, species, age, gender, photo, description 
             FROM pets 
             ORDER BY created_at DESC 
             LIMIT ?",
            'i',
            [$limit]
        );
    }

    /**
     * Get pet by ID
     */
    public static function getPetById(int $petId): ?array {
        return self::fetchOne(
            "SELECT id, name, species, age, gender, photo, status, description 
             FROM pets 
             WHERE id = ?",
            'i',
            [$petId]
        );
    }
}
