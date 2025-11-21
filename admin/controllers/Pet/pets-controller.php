<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class PetsController extends BaseController {
    public static function listAll(array $filters = [], int $limit = 200): array {
        $sql = "SELECT id, name, species, breed, age, status, created_at FROM pets";
        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($filters['name'])) {
            $whereClauses[] = "name LIKE ?";
            $params[] = '%' . $filters['name'] . '%';
            $types .= 's';
        }
        if (!empty($filters['species'])) {
            $whereClauses[] = "species = ?";
            $params[] = $filters['species'];
            $types .= 's';
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        if (!empty($filters['min_age'])) {
            $whereClauses[] = "age >= ?";
            $params[] = $filters['min_age'];
            $types .= 'i';
        }
        if (!empty($filters['max_age'])) {
            $whereClauses[] = "age <= ?";
            $params[] = $filters['max_age'];
            $types .= 'i';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        return self::fetchAll($sql, $types, $params);
    }

    public static function getDistinctSpecies(): array {
        return self::fetchAll("SELECT DISTINCT species FROM pets ORDER BY species ASC");
    }

    public static function find(int $id): ?array {
        return self::fetchOne("SELECT id, name, species, breed, age, status, description FROM pets WHERE id = ?", 'i', [$id]);
    }
}
?>