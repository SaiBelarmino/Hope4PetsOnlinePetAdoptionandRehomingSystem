<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
include dirname(__DIR__, 2) . '/controllers/Post/post-reports-controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['report_id'], $_POST['post_id'])) {
    $action = $_POST['action'];
    $reportId = (int)$_POST['report_id'];
    $postId = (int)$_POST['post_id'];
    $reporterId = (int)($_POST['reporter_id'] ?? 0); // reporter_id is needed for warn/ban

    switch ($action) {
        case 'approve': // Approve content, close report
            PostReportsController::closeReport($reportId);
            break;
        case 'delete_post':
            PostReportsController::deletePost($postId);
            // Reports on this post will be closed/deleted via DB cascade
            break;
        case 'warn':
            if ($reporterId > 0) {
                PostReportsController::warnUser($reporterId);
            }
            PostReportsController::closeReport($reportId);
            break;
        case 'ban':
            if ($reporterId > 0) {
                PostReportsController::banUser($reporterId);
            }
            PostReportsController::closeReport($reportId);
            break;
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


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
                                <th scope="col">Post Content</th>
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
                                        <td><?php echo htmlspecialchars(substr($report['content'], 0, 50)) . (strlen($report['content']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                        <td><?php echo date('F j, Y, g:i a', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline-flex gap-1">
                                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                                <input type="hidden" name="post_id" value="<?php echo $report['post_id']; ?>">
                                                <input type="hidden" name="reporter_id" value="<?php echo $report['reporter_id']; ?>">
                                                
                                                <a href="/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/views/PostView.php?id=<?php echo $report['post_id']; ?>" class="btn btn-sm btn-info" target="_blank" title="View Post">
                                                    👁️ View
                                                </a>

                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve Content (Dismiss Report)">Approve</button>
                                                <button type="submit" name="action" value="delete_post" class="btn btn-sm btn-danger" title="Delete Post" onclick="return confirm('Are you sure you want to permanently delete this post?');">Delete</button>
                                                <button type="submit" name="action" value="warn" class="btn btn-sm btn-warning" title="Warn the user who created the post" onclick="return confirm('Are you sure you want to warn the user who created this post?');">Warn</button>
                                                <button type="submit" name="action" value="ban" class="btn btn-sm btn-dark" title="Ban the user who created the post" onclick="return confirm('Are you sure you want to permanently ban the user who created this post?');">Ban</button>
                                            </form>
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
