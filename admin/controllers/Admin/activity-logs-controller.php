<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class ActivityLogsController extends BaseController {
    public static function getLogs(array $filters = [], int $limit = 50): array {
        $sql = "SELECT al.id, a.username, al.action, al.target_type, al.target_id, al.created_at 
                FROM admin_logs al
                JOIN admins a ON al.admin_id = a.id";
        
        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($filters['search'])) {
            $whereClauses[] = "(a.username LIKE ? OR al.action LIKE ? OR al.target_type LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        if (!empty($filters['admin_id'])) {
            $whereClauses[] = "al.admin_id = ?";
            $params[] = $filters['admin_id'];
            $types .= 'i';
        }

        if (!empty($filters['action_type'])) {
            $whereClauses[] = "al.action LIKE ?";
            $params[] = '%' . $filters['action_type'] . '%';
            $types .= 's';
        }

        if (!empty($filters['start_date'])) {
            $whereClauses[] = "al.created_at >= ?";
            $params[] = $filters['start_date'];
            $types .= 's';
        }

        if (!empty($filters['end_date'])) {
            $whereClauses[] = "al.created_at <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
            $types .= 's';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        return self::fetchAll($sql, $types, $params);
    }

    public static function getAllAdmins(): array {
        $sql = "SELECT id, username FROM admins ORDER BY username ASC";
        return self::fetchAll($sql);
    }
}
