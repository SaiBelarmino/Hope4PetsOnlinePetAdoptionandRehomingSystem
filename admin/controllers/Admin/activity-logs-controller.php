<?php
namespace App\Controllers\Admin;

require_once __DIR__ . '/../../controllers/BaseController.php';

class ActivityLogsController extends BaseController {
    public static function listRecent(int $limit = 50): array {
        $sql = "SELECT id, admin_id, action, created_at FROM activity_logs ORDER BY created_at DESC LIMIT ?";
        return self::fetchAll($sql, 'i', [$limit]);
    }
}
