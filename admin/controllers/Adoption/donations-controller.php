<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class DonationsController extends BaseController
{
    public static function recent(int $limit = 100): array
    {
        $sql = "SELECT d.id, d.donor_id, d.amount, d.created_at, d.shelter_id FROM donations d ORDER BY d.created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }

    public static function getDonations(array $filters = [], string $sortBy = 'd.created_at DESC', int $page = 1, int $limit = 15): array
    {
        $offset = ($page - 1) * $limit;
        $baseSql = "
            SELECT 
                d.id, 
                d.donor_name, 
                d.amount, 
                d.created_at, 
                d.status, 
                d.payment_method,
                COALESCE(u.full_name, d.donor_name) AS donor,
                s.shelter_name
            FROM donations d
            LEFT JOIN users u ON d.donor_id = u.id
            LEFT JOIN shelters s ON d.shelter_id = s.id
        ";

        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $whereClauses[] = "(u.full_name LIKE ? OR d.donor_name LIKE ? OR s.shelter_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            array_push($params, $searchTerm, $searchTerm, $searchTerm);
            $types .= 'sss';
        }

        if (!empty($filters['shelter_id'])) {
            $whereClauses[] = "d.shelter_id = ?";
            $params[] = $filters['shelter_id'];
            $types .= 'i';
        }

        if (!empty($filters['status'])) {
            $whereClauses[] = "d.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($whereClauses)) {
            $baseSql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $baseSql .= " ORDER BY $sortBy LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return self::fetchAll($baseSql, $types, $params);
    }

    public static function getDonationsSummary(): array
    {
        $sql = "SELECT 
                    SUM(amount) as total_donations, 
                    COUNT(DISTINCT COALESCE(donor_id, donor_name)) as total_donors 
                FROM donations WHERE status = 'completed'";
        return self::fetchOne($sql);
    }

    public static function countDonations(array $filters = []): int
    {
        $sql = "SELECT COUNT(d.id) FROM donations d";
        $whereClauses = [];
        $params = [];
        $types = '';
        // Simplified for brevity, but should mirror the filtering in getDonations
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        $result = self::fetchOne($sql, $types, $params);
        return $result['COUNT(d.id)'] ?? 0;
    }

    public static function getAllShelters(): array
    {
        return self::fetchAll("SELECT id, shelter_name FROM shelters ORDER BY shelter_name ASC");
    }

    public static function getDonationsByShelter(): array
    {
        $sql = "
            SELECT 
                s.shelter_name, 
                SUM(d.amount) as total_donations
            FROM donations d
            JOIN shelters s ON d.shelter_id = s.id
            WHERE d.status = 'completed'
            GROUP BY s.shelter_name
            ORDER BY total_donations DESC
        ";
        return self::fetchAll($sql);
    }

    public static function getDonationsByPaymentMethod(): array
    {
        $sql = "
            SELECT 
                payment_method, 
                SUM(amount) as total_donations
            FROM donations
            WHERE status = 'completed'
            GROUP BY payment_method
            ORDER BY total_donations DESC
        ";
        return self::fetchAll($sql);
    }
}
?>