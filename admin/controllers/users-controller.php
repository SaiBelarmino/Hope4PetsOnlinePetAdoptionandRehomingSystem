<?php
require_once __DIR__ . '/../../controllers/BaseController.php';

/**
 * UsersController
 * Data access & actions for users table.
 * NOTE: Provided schema does NOT include 'username' or 'status' columns shown in original code.
 * Using columns from schema: id, full_name, email, profile_photo, is_verified, created_at, updated_at, gender, location, contact_number, birthday.
 * We will derive a logical status using an added virtual / assumed column or you can later add a real column:
 *   - active (is_verified = 1)
 *   - pending (is_verified = 0)
 *   - banned (if a future 'banned_at' or 'is_banned' column exists). For now we simulate by separate table/flag; here we check optional column if exists.
 * Adjust queries if you later add: ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0;
 */
class UsersController extends BaseController {

    /**
     * Cache flag for presence of is_banned column.
     */
    protected static ?bool $hasBannedColumn = null;

    /**
     * Detect if users table has is_banned column (once per request).
     */
    protected static function hasBanned(): bool {
        if (self::$hasBannedColumn !== null) return self::$hasBannedColumn;
        $mysqli = self::db();
        $dbName = $mysqli->query('SELECT DATABASE() AS d')->fetch_assoc()['d'] ?? null;
        if (!$dbName) { self::$hasBannedColumn = false; return false; }
        $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_banned' LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) { self::$hasBannedColumn = false; return false; }
        $stmt->bind_param('s', $dbName);
        $stmt->execute();
        $res = $stmt->get_result();
        self::$hasBannedColumn = $res && $res->fetch_assoc() ? true : false;
        $stmt->close();
        return self::$hasBannedColumn;
    }

    /**
     * Build dynamic WHERE clause based on filters.
     * @param array $filters
     * @return array [sqlFragment, types, params]
     */
    protected static function buildWhere(array $filters): array {
        $where = [];
        $types = '';
        $params = [];

        // Text search across id, full_name, email
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = '(CAST(u.id AS CHAR) LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)';

            $types .= 'sss';
            $params[] = $q; $params[] = $q; $params[] = $q;
        }
        // Verification status
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            $where[] = 'u.is_verified = ?';
            $types .= 'i';
            $params[] = (int)$filters['verified'];
        }
        // Date range (created_at)
        if (!empty($filters['from'])) {
            $where[] = 'DATE(u.created_at) >= ?';
            $types .= 's';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'DATE(u.created_at) <= ?';
            $types .= 's';
            $params[] = $filters['to'];
        }
        // Optional gender filter
        if (!empty($filters['gender'])) {
            $where[] = 'u.gender = ?';
            $types .= 's';
            $params[] = $filters['gender'];
        }
        // If project later adds is_banned column and filter provided
        if (self::hasBanned() && isset($filters['banned']) && $filters['banned'] !== '') {
            $where[] = 'u.is_banned = ?';
            $types .= 'i';
            $params[] = (int)$filters['banned'];
        }

        $fragment = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        return [$fragment, $types, $params];
    }

    /**
     * List users with filters & pagination.
     * @param array $filters ['q','verified','from','to','gender','banned','limit','page']
     * @return array [data, pagination]
     */
    public static function list(array $filters = []): array {
        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 50;
        $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
        $offset = ($page - 1) * $limit;

        [$where, $types, $params] = self::buildWhere($filters);

        // Count total for pagination
        $countSql = "SELECT COUNT(*) AS c FROM users u $where";
        $countRow = self::fetchOne($countSql, $types, $params);
        $total = (int)($countRow['c'] ?? 0);

    $bannedSelect = self::hasBanned() ? 'u.is_banned AS is_banned,' : '0 AS is_banned,';
    $sql = "SELECT u.id, u.full_name, u.email, u.profile_photo, u.is_verified, u.gender, u.location, u.contact_number, u.birthday, u.created_at, u.updated_at, $bannedSelect
        u.updated_at AS last_update_marker
        FROM users u $where
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?";
        $listTypes = $types . 'ii';
        $listParams = array_merge($params, [$limit, $offset]);
        $rows = self::fetchAll($sql, $listTypes, $listParams);

        // Derive status text
        foreach ($rows as &$r) {
            $r['status'] = ($r['is_banned'] ?? 0) ? 'banned' : (($r['is_verified'] ?? 0) ? 'active' : 'pending');
            $r['name'] = $r['full_name'];
            $r['avatar'] = $r['profile_photo'];
            $r['last_active'] = $r['updated_at'];
            $r['role'] = 'user'; // default unless roles are introduced
        }
        unset($r);

        $pages = $limit ? (int)ceil($total / $limit) : 1;
        return [
            'data' => $rows,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'limit' => $limit
            ]
        ];
    }

    /**
     * Aggregate statistics for dashboard cards.
     */
    public static function stats(): array {
        $total = (int) self::fetchValue('SELECT COUNT(*) AS v FROM users');
        $active = (int) self::fetchValue('SELECT COUNT(*) AS v FROM users WHERE is_verified = 1');
        $pending = (int) self::fetchValue('SELECT COUNT(*) AS v FROM users WHERE is_verified = 0');
        $banned = 0;
        if (self::hasBanned()) {
            $banned = (int) self::fetchValue('SELECT COUNT(*) AS v FROM users WHERE is_banned = 1');
        }
        return [
            'total' => $total,
            'active' => $active,
            'pending' => $pending,
            'banned' => $banned,
        ];
    }

    public static function getById(int $id): ?array {
    $selectBanned = self::hasBanned() ? 'u.is_banned AS is_banned' : '0 AS is_banned';
    $row = self::fetchOne("SELECT u.*, $selectBanned FROM users u WHERE u.id = ?", 'i', [$id]);
        if ($row) {
            $row['status'] = ($row['is_banned'] ?? 0) ? 'banned' : (($row['is_verified'] ?? 0) ? 'active' : 'pending');
        }
        return $row;
    }

    /**
     * Update user fields.
     * Accepts subset of columns: full_name, email, gender, location, contact_number, birthday, is_verified
     */
    public static function update(int $id, array $data): bool {
        $allowed = ['full_name'=>'s','email'=>'s','gender'=>'s','location'=>'s','contact_number'=>'s','birthday'=>'s','is_verified'=>'i'];
        $fields = [];
        $types = '';
        $params = [];
        foreach ($allowed as $col => $typ) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = ?";
                $types .= $typ;
                // coerce types
                if ($typ === 'i') $params[] = (int)$data[$col]; else $params[] = $data[$col];
            }
        }
        if (empty($fields)) return false;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = $id;
        return (bool) self::execute($sql, $types, $params);
    }

    /**
     * Activate (verify) user.
     */
    public static function activate(int $id): bool {
        $sql = 'UPDATE users SET is_verified = 1 WHERE id = ?';
        return self::execute($sql, 'i', [$id]);
    }

    /**
     * Set pending (unverify) user.
     */
    public static function setPending(int $id): bool {
        $sql = 'UPDATE users SET is_verified = 0 WHERE id = ?';
        return self::execute($sql, 'i', [$id]);
    }

    /**
     * Ban user (requires is_banned column to exist).
     */
    public static function ban(int $id): bool {
    if (!self::hasBanned()) return false; // cannot ban without column
    $sql = 'UPDATE users SET is_banned = 1 WHERE id = ?';
    return self::execute($sql, 'i', [$id]);
    }

    /**
     * Unban user.
     */
    public static function unban(int $id): bool {
    if (!self::hasBanned()) return false;
    $sql = 'UPDATE users SET is_banned = 0 WHERE id = ?';
    return self::execute($sql, 'i', [$id]);
    }

    /**
     * Bulk action wrapper.
     * @param string $action activate|pending|ban|unban|delete
     * @param int[] $ids
     */
    public static function bulk(string $action, array $ids): int {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) return 0;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        switch ($action) {
            case 'activate':
                $sql = "UPDATE users SET is_verified = 1 WHERE id IN ($in)"; break;
            case 'pending':
                $sql = "UPDATE users SET is_verified = 0 WHERE id IN ($in)"; break;
            case 'ban':
                if (!self::hasBanned()) return 0; $sql = "UPDATE users SET is_banned = 1 WHERE id IN ($in)"; break;
            case 'unban':
                if (!self::hasBanned()) return 0; $sql = "UPDATE users SET is_banned = 0 WHERE id IN ($in)"; break;
            case 'delete':
                $sql = "DELETE FROM users WHERE id IN ($in)"; break;
            default:
                return 0;
        }
        return self::execute($sql, $types, $ids, true);
    }

    /**
     * Low-level execute wrapper returning success or affected rows.
     * @param string $sql
     * @param string $types
     * @param array $params
     * @param bool $returnAffected if true returns affected_rows else bool
     */
    protected static function execute(string $sql, string $types = '', array $params = [], bool $returnAffected = false) {
        $mysqli = self::db();
        if ($types !== '' && $params) {
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) return $returnAffected ? 0 : false;
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $returnAffected ? $affected : ($affected >= 0);
        }
        $res = $mysqli->query($sql);
        if ($returnAffected) return $mysqli->affected_rows;
        return $res === true;
    }

    /**
     * Simple CSV export (returns string). Consider streaming for large datasets.
     */
    public static function exportCsv(array $filters = []): string {
        $filters['limit'] = $filters['limit'] ?? 10000; // cap export
        $list = self::list($filters);
        $rows = $list['data'];
        $out = fopen('php://temp', 'w+');
        $header = ['ID','Full Name','Email','Verified'];
        if (self::hasBanned()) $header[] = 'Banned';
        $header[] = 'Created At';
        fputcsv($out, $header);
        foreach ($rows as $r) {
            $data = [
                $r['id'],
                $r['full_name'],
                $r['email'],
                (int)$r['is_verified'],
            ];
            if (self::hasBanned()) $data[] = (int)($r['is_banned'] ?? 0);
            $data[] = $r['created_at'];
            fputcsv($out, $data);
        }
        rewind($out);
        return stream_get_contents($out);
    }
}
?>