<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

require_once dirname(__DIR__, 2) . '/controllers/Pet/pet-reports-controller.php';
$openReports = PetReportsController::listOpen();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Open Pet Reports</h5>
                <p class="mb-0">Flagged pet-related reports that are currently open.</p>

                <div class="table-responsive mt-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Report ID</th>
                                <th scope="col">Pet Name</th>
                                <th scope="col">Reason</th>
                                <th scope="col">Reported On</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($openReports)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center">No open reports found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($openReports as $report) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['id']); ?></td>
                                        <td><?php echo htmlspecialchars($report['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                        <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($report['created_at']))); ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">View</a>
                                            <a href="#" class="btn btn-sm btn-danger">Close</a>
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
