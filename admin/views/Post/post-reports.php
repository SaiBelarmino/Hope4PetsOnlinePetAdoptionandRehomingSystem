<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
include dirname(__DIR__, 2) . '/controllers/Post/post-reports-controller.php';
$reports = PostReportsController::listOpen();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Post Reports</h5>
                <p class="card-subtitle mb-4">Flagged post reports that are currently open.</p>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Post Title</th>
                                <th scope="col">Reason</th>
                                <th scope="col">Reported At</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)) : ?>
                                <tr>
                                    <td colspan="4" class="text-center">No open reports found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($reports as $report) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['title']); ?></td>
                                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                        <td><?php echo date('F j, Y, g:i a', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <a href="/Hope4PetsOnlinePetAdoptionandRehomingSystem/post.php?id=<?php echo $report['post_id']; ?>" class="btn btn-sm btn-info" target="_blank">View Post</a>
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
