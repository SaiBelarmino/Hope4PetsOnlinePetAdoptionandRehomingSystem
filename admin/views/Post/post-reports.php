<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
include dirname(__DIR__, 2) . '/controllers/Post/post-reports-controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['report_id'], $_POST['post_id'])) {
    $action = $_POST['action'];
    $reportId = (int)$_POST['report_id'];
    $postId = (int)$_POST['post_id'];

    switch ($action) {
        case 'approve': // Approve content, close report
        case 'reject':  // Reject report, close report
            PostReportsController::closeReport($reportId);
            break;
        case 'delete_post':
            PostReportsController::deletePost($postId);
            // Reports on this post will be closed/deleted via DB cascade
            break;
        case 'hide_post':
            PostReportsController::hidePost($postId);
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
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                                <input type="hidden" name="post_id" value="<?php echo $report['post_id']; ?>">
                                                
                                                <a href="/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/views/PostView.php?id=<?php echo $report['post_id']; ?>" class="btn btn-sm btn-info" target="_blank" title="View Post">
                                                    <i class="ti ti-eye"></i>
                                                </a>

                                                <button type="submit" name="action" value="approve" class="btn btn-sm" title="Approve Content (Dismiss Report)">✅</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm" title="Dismiss Report">❌</button>
                                                <button type="submit" name="action" value="delete_post" class="btn btn-sm" title="Delete Post" onclick="return confirm('Are you sure you want to permanently delete this post?');">🗑️</button>
                                                <button type="submit" name="action" value="hide_post" class="btn btn-sm" title="Hide Post" onclick="return confirm('Are you sure you want to hide this post from public view?');">👁️</button>
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
