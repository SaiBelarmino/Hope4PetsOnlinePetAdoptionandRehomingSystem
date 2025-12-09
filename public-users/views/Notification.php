<?php

// This is the main notifications page (e.g., notifications.php)
// It should include the main layout files.
include_once '../include/header.php';
include_once '../include/topbar.php';
require_once '../controllers/NotificationController.php';

$controller = new NotificationController();
$allNotifications = $controller->getAllNotifications();
?>

<div class="container my-4">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">All Notifications</h4>
        </div>
        <div class="card-body p-0">
            <?php if (empty($allNotifications)): ?>
                <div class="p-4 text-center text-muted">
                    You have no notifications.
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($allNotifications as $notification): ?>
                        <li class="list-group-item list-group-item-action d-flex align-items-start gap-3 py-3 <?= $notification['is_read'] ? 'bg-light' : '' ?>">
                            <span class="p-2 rounded-circle d-flex align-items-center justify-content-center <?= htmlspecialchars($notification['bg']) ?>">
                                <i class="ti <?= htmlspecialchars($notification['icon']) ?>"></i>
                            </span>
                            <div class="w-100">
                                <?php if (!empty($notification['url'])): ?>
                                    <a href="<?= htmlspecialchars($notification['url']) ?>" class="text-decoration-none">
                                        <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                    </a>
                                <?php else: ?>
                                    <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                <?php endif; ?>
                                <small class="text-muted"><?= htmlspecialchars($notification['time']) ?></small>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <span class="badge bg-primary rounded-pill">New</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include_once '../include/footer.php';
?>