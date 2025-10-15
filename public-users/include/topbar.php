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
        <form class="d-none d-md-flex flex-grow-0" role="search" method="get" action="./search.php"
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
                <a class="nav-link" style="<?= $iconStyle ?>" href="./community.php" title="Community" aria-label="Community">
                <i class="ti ti-users"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="<?= $iconStyle ?>" href="./pets.php" title="Pets" aria-label="Pets">
                <i class="ti ti-paw"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" style="<?= $iconStyle ?>" href="./shelters.php" title="Shelters" aria-label="Shelters">
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
                <a class="nav-link position-relative p-0 d-flex align-items-center justify-content-center"
                   href="./notifications.php" title="Notifications" aria-label="Notifications"
                   style="<?= $iconStyle ?>height:40px;width:40px;border-radius:50%;background:#f0f2f5;font-size:20px;">
                    <i class="ti ti-bell-ringing"></i>
                    <span class="notification-dot bg-danger rounded-circle position-absolute"
                          style="width:10px;height:10px;top:4px;right:4px;"></span>
                </a>
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
        <form class="w-100 py-3 d-md-none" role="search" method="get" action="./search.php">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light border-end-0"><i
                        class="ti ti-search icon-huge-search"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search..."
                    aria-label="Search" />
            </div>
        </form>
    </div>
</nav>