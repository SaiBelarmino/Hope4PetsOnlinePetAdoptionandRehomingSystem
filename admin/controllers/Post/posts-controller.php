<?php


require_once __DIR__ . '/../../../controllers/BaseController.php';

class PostsController extends BaseController {
    public static function list(array $options = []): array {
        // Options for pagination, sorting, filtering
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 10;
        $sort = $options['sort'] ?? 'p.created_at';
        $order = $options['order'] ?? 'DESC';
        $search = $options['search'] ?? '';
        $statusFilter = $options['status'] ?? '';
        $typeFilter = $options['type'] ?? '';

        $offset = ($page - 1) * $limit;

        // Base SQL
        $sql = "SELECT p.id, p.user_id, u.full_name, u.profile_photo AS user_avatar, p.content, p.created_at
                FROM posts p
                JOIN users u ON p.user_id = u.id";
        
        $countSql = "SELECT COUNT(p.id) FROM posts p JOIN users u ON p.user_id = u.id";

        // Conditions
        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($search)) {
            $conditions[] = "(p.content LIKE ? OR u.full_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        if (!empty($statusFilter)) {
            $conditions[] = "p.status = ?";
            $params[] = $statusFilter;
            $types .= 's';
        }

        if (!empty($typeFilter)) {
            $conditions[] = "p.type = ?";
            $params[] = $typeFilter;
            $types .= 's';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
            $countSql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Get total count for pagination
        $totalRecords = self::fetchOne($countSql, $types, $params)[0] ?? 0;
        $totalPages = ceil($totalRecords / $limit);

        // Sorting and Pagination for main query
        $sql .= " ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $posts = self::fetchAll($sql, $types, $params);

        return [
            'posts' => $posts,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'total_records' => $totalRecords
        ];
    }

    public static function listRecent(int $limit = 100): array {
        $sql = "SELECT p.id, p.user_id, u.first_name, u.last_name, p.title, p.created_at, p.status 
                FROM posts p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC 
                LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}