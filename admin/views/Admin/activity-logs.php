<?php
// Protect this admin view from direct access: require admin session
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
require_once dirname(__DIR__, 2) . '/controllers/Admin/activity-logs-controller.php';

// Fetch filter parameters from GET request
$filters = [
    'search' => $_GET['search'] ?? '',
    'admin_id' => $_GET['admin_id'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'range' => $_GET['range'] ?? ''
];

// Handle quick date filters
if (!empty($filters['range'])) {
    if ($filters['range'] === 'today') {
        $filters['start_date'] = date('Y-m-d');
        $filters['end_date'] = date('Y-m-d');
    } elseif ($filters['range'] === '7days') {
        $filters['start_date'] = date('Y-m-d', strtotime('-7 days'));
        $filters['end_date'] = date('Y-m-d');
    }
}


$logs = ActivityLogsController::getLogs($filters);
$admins = ActivityLogsController::getAllAdmins();
$actionTypes = ['login', 'update', 'delete', 'add', 'create', 'verify', 'reject', 'approve', 'deny', 'cancel', 'resolve', 'dismiss'];

?>

<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<?php include dirname(__DIR__, 2) . '/header.php'; ?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Activity Logs</h5>
            <p class="mb-0">Recent admin activities.</p>

            <form action="activity-logs.php" method="get" class="mt-4">
                <!-- Search and Quick Filters -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by user, action, or module..." value="<?= htmlspecialchars($filters['search']) ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <a href="?range=today" class="btn btn-sm btn-outline-secondary me-1">Today</a>
                        <a href="?range=7days" class="btn btn-sm btn-outline-secondary me-1">Last 7 Days</a>
                        <a href="?" class="btn btn-sm btn-outline-danger">Clear</a>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="row gy-2 gx-3 align-items-center">
                    <div class="col-auto">
                        <label for="admin_id" class="visually-hidden">User</label>
                        <select class="form-select" id="admin_id" name="admin_id" onchange="this.form.submit()">
                            <option value="">All Users</option>
                            <?php foreach ($admins as $admin): ?>
                                <option value="<?= $admin['id'] ?>" <?= ($filters['admin_id'] == $admin['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($admin['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="action_type" class="visually-hidden">Action Type</label>
                        <select class="form-select" id="action_type" name="action_type" onchange="this.form.submit()">
                            <option value="">All Action Types</option>
                             <?php foreach ($actionTypes as $type): ?>
                                <option value="<?= $type ?>" <?= ($filters['action_type'] == $type) ? 'selected' : '' ?>>
                                    <?= ucfirst($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="start_date">From</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($filters['start_date']) ?>">
                    </div>
                    <div class="col-auto">
                        <label for="end_date">To</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($filters['end_date']) ?>">
                    </div>
                    <div class="col-auto">
                         <button type="submit" class="btn btn-info">Apply Custom Range</button>
                    </div>
                    <div class="col-auto ms-auto">
                        <button type="button" class="btn btn-success" onclick="alert('Export functionality to be implemented.');">Export (CSV/PDF)</button>
                    </div>
                </div>
            </form>

            <!-- Activity Logs Table -->
            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Target ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No activity logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?></td>
                                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                                    <td><?= htmlspecialchars($log['username']) ?></td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td><?= htmlspecialchars($log['target_type']) ?></td>
                                    <td><?= htmlspecialchars($log['target_id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>