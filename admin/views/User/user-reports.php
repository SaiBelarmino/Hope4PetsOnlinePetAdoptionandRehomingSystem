<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
include __DIR__ . '/../../controllers/User/user-reports-controller.php';
$reports = UserReportsController::listOpen();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">User Reports</h5>
                <p class="mb-0">Flagged user content and abuse reports.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Reported User</th>
                                <th scope="col">Reporter</th>
                                <th scope="col">Reason</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)) : ?>
                                <tr>
                                    <td colspan="7" class="text-center">No open reports found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($reports as $report) : ?>
                                    <tr>
                                        <th scope="row"><?php echo htmlspecialchars($report['id']); ?></th>
                                        <td><?php echo htmlspecialchars($report['reported_username']); ?></td>
                                        <td><?php echo htmlspecialchars($report['reporter_username']); ?></td>
                                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($report['created_at']))); ?></td>
                                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($report['status']); ?></span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info">View</a>
                                            <a href="#" class="btn btn-sm btn-danger">Dismiss</a>
                                        </td>
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
