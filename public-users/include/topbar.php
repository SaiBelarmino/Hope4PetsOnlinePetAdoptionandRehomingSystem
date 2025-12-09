<!-- External topbar styles -->
<nav class="topbar navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm py-2 py-lg-2"
    style="z-index:1000;"
    role="navigation" aria-label="Primary">
    <div class="container-fluid align-items-center d-flex gap-2 gap-lg-3">
        <!-- Left: Brand -->
        <a class="navbar-brand d-flex align-items-center" href="./index.php" aria-label="Hope4Pets Home"
            style="height:40px; padding:0; line-height:1;">
            <img src="../../assets/images/logos/logo-icon.png" alt="Hope4Pets Logo"
                style="height:40px; width:auto; display:block; object-fit:contain;" />
        </a>

        <!-- Desktop Search -->
        <form class="d-none d-md-flex flex-grow-0" role="search" method="get" action="/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/views/Search.php"
            aria-label="Site search">
            <div class="input-group" style="width:280px;">
                <span class="input-group-text bg-light border-0 rounded-start-pill">
                    <i class="ti ti-search"></i>
                </span>
                <input type="text" name="q" class="form-control bg-light border-0 rounded-end-pill"
                    placeholder="Search Hope4Pets" aria-label="Search">
            </div>
        </form>

        <!-- Center: Icon Nav (desktop) - centered -->
        <!-- Center Icons (Facebook-like sizing) -->
        <div class="nav-center d-none d-lg-flex position-absolute start-50 top-50 translate-middle">
            <ul class="topbar-icons navbar-nav flex-row align-items-center gap-1">
            <?php
            // Common style for FB-like icon buttons
            $iconStyle = "height:56px;width:56px;display:flex;align-items:center;justify-content:center;font-size:24px;";
            ?>
            <li class="nav-item">
                <a class="nav-link" style="<?= $iconStyle ?>" href="./index.php" title="Home" aria-label="Home">
                <i class="ti ti-home"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="<?= $iconStyle ?>" href="./BrowsePet.php" title="Pets" aria-label="Pets">
                <i class="ti ti-paw"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="<?= $iconStyle ?>" href="./FindShelters.php" title="Shelters" aria-label="Shelters">
                <i class="ti ti-building-community"></i>
                </a>
            </li>
            </ul>
        </div>

        <!-- Right Section -->
        <div class="d-flex align-items-center ms-auto gap-2">
            <div class="d-flex align-items-center gap-1 user-actions">
                <a class="nav-link position-relative p-0 d-flex align-items-center justify-content-center"
                   href="./ChatMessages.php" title="Messages" aria-label="Messages"
                   style="<?= $iconStyle ?>height:40px;width:40px;border-radius:50%;background:#f0f2f5;font-size:20px;">
                    <i class="ti ti-message-circle"></i>
                </a>

                <?php
                    // Include the controller to fetch notification data
                    if (!class_exists('NotificationController')) {
                        require_once __DIR__ . '/../controllers/NotificationController.php';
                    }
                    $notificationController = new NotificationController();
                    $recentNotifications = $notificationController->getRecentNotifications();
                    $unreadCount = count(array_filter($recentNotifications, fn($n) => !$n['is_read']));
                ?>
                <div class="dropdown">
                    <a class="nav-link position-relative p-0 d-flex align-items-center justify-content-center"
                       href="#" title="Notifications" aria-label="Notifications" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       style="<?= $iconStyle ?>height:40px;width:40px;border-radius:50%;background:#f0f2f5;font-size:20px;">
                        <i class="ti ti-bell-ringing"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-dot bg-danger rounded-circle position-absolute"
                                  style="width:10px;height:10px;top:4px;right:4px;"></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 350px; padding: 0;">
                        <li class="p-3">
                            <h6 class="mb-0">Notifications</h6>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li style="max-height: 400px; overflow-y: auto;">
                            <ul class="list-unstyled mb-0">
                                <?php if (empty($recentNotifications)): ?>
                                    <li class="py-3 px-3 text-center text-muted">No new notifications</li>
                                <?php else: ?>
                                    <?php foreach ($recentNotifications as $notification): ?>
                                        <li class="border-bottom">
                                            <a class="dropdown-item d-flex align-items-start gap-3 py-3 <?= $notification['is_read'] ? 'bg-light' : '' ?>"
                                               href="<?= !empty($notification['url']) ? htmlspecialchars($notification['url']) : '#' ?>">
                                                <span class="p-2 rounded-circle d-flex align-items-center justify-content-center <?= htmlspecialchars($notification['bg']) ?>">
                                                    <i class="ti <?= htmlspecialchars($notification['icon']) ?>"></i>
                                                </span>
                                                <div>
                                                    <p class="mb-0 small"><?= htmlspecialchars($notification['message']) ?></p>
                                                    <small class="text-muted"><?= htmlspecialchars($notification['time']) ?></small>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li class="py-2">
                            <!-- Change link to open modal instead of navigating -->
                            <a class="dropdown-item text-center" href="#" data-bs-toggle="modal" data-bs-target="#allNotificationsModal">
                                View All Notifications
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="dropdown">
                    <?php if (!function_exists('resolve_profile_photo')) { include __DIR__ . '/profile_helpers.php'; } ?>
                    <a class="nav-link p-0 d-flex align-items-center" href="#" id="userMenu" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                        <?php $avatar = resolve_profile_photo($_SESSION['user']['profile_photo'] ?? null); ?>
                        <img src="<?php echo $avatar; ?>" alt="Profile" width="40" height="40"
                            class="rounded-circle profile-avatar" />
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
                        </li>
                        <?php if(isset($_SESSION['user']['full_name'])): ?>
                          <div class="dropdown-item text-center small text-muted">
                            <?php $avatarSmall = resolve_profile_photo($_SESSION['user']['profile_photo'] ?? null); ?>
                            <img src="<?php echo $avatarSmall; ?>" alt="Profile" width="48" height="48" class="rounded-circle mb-1" />
                            <div><?php echo htmlspecialchars($_SESSION['user']['full_name']); ?></div>
                            <div class="small text-secondary"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></div>
                          </div>
                          <hr class="dropdown-divider" />
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="./settings.php"><i class="ti ti-settings me-1"></i>
                                Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <!-- Fixed logout path -->
                        <li><a class="dropdown-item" href="../user-authentication/authentication-logout.php"><i
                                    class="ti ti-logout me-1"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
            <button class="navbar-toggler ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#communityNav"
                aria-controls="communityNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>

    <!-- Collapsible (Mobile) -->
    <div class="collapse navbar-collapse collapse-area border-top px-3 px-md-4" id="communityNav">
        <form class="w-100 py-3 d-md-none" role="search" method="get" action="/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/views/Search.php">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light border-end-0"><i
                        class="ti ti-search icon-huge-search"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search..."
                    aria-label="Search" />
            </div>
        </form>
    </div>
</nav>

<!-- Modal for all notifications -->
<div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allNotificationsModalLabel">All Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="max-height:500px;overflow-y:auto;">
        <?php
        // Use the controller to get all notifications
        if (!class_exists('NotificationController')) {
            require_once __DIR__ . '/../controllers/NotificationController.php';
        }
        $controller = new NotificationController();
        $allNotifications = $controller->getAllNotifications();
        ?>
        <?php if (empty($allNotifications)): ?>
            <div class="p-4 text-center text-muted">
                You have no notifications.
            </div>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($allNotifications as $notification): ?>
                    <li class="list-group-item list-group-item-action d-flex align-items-start gap-3 py-3 <?= $notification['is_read'] ? 'bg-light' : '' ?>" data-id="<?= $notification['id'] ?>">
                        <span class="p-2 rounded-circle d-flex align-items-center justify-content-center <?= htmlspecialchars($notification['bg']) ?>">
                            <i class="ti <?= htmlspecialchars($notification['icon']) ?>"></i>
                        </span>
                        <div class="w-100">
                            <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($notification['time']) ?></small>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <span class="badge bg-primary rounded-pill">New</span>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-link text-danger ms-auto delete-notification" title="Delete" style="font-size:18px;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateNotificationDot() {
        // Count remaining unread notifications in modal
        const unread = document.querySelectorAll('#allNotificationsModal .list-group-item .badge.bg-primary');
        const dot = document.querySelector('.notification-dot');
        if (unread.length === 0 && dot) {
            dot.style.display = 'none';
        }
    }

    document.querySelectorAll('.delete-notification').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const li = btn.closest('li[data-id]');
            const id = li.getAttribute('data-id');
            if (!confirm('Delete this notification?')) return;
            fetch('../controllers/NotificationController.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_notification&id=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    li.remove();
                    // Check if there are any notifications left
                    const list = document.querySelector('#allNotificationsModal .list-group');
                    if (!list || list.children.length === 0) {
                        const modalBody = document.querySelector('#allNotificationsModal .modal-body');
                        modalBody.innerHTML = '<div class="p-4 text-center text-muted">You have no notifications.</div>';
                    }
                    updateNotificationDot();
                } else {
                    alert('Failed to delete notification.');
                }
            });
        });
    });
});
</script>

