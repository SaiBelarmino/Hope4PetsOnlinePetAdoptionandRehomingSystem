<!-- External topbar styles -->
<link rel="stylesheet" href="../../assets/css/topbar.css" />
<nav class="topbar navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm py-2 py-lg-2"
    role="navigation" aria-label="Primary">
    <div class="container-fluid align-items-center d-flex gap-2 gap-lg-3">
        <!-- Left: Brand -->
        <a class="navbar-brand d-flex align-items-center" href="./index.php" aria-label="Hope4Pets Home">
            <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets Logo" />
        </a>

        <!-- Desktop Search -->
        <form class="search-desktop d-none d-md-block" role="search" method="get" action="./search.php">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-light border-end-0"><i
                        class="ti ti-search icon-huge-search"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search..."
                    aria-label="Search" />
            </div>
        </form>

        <!-- Center: Icon Nav (desktop) - centered -->
        <!-- Center Icons (absolutely centered) -->
        <div class="nav-center d-none d-lg-flex position-absolute start-50 top-50 translate-middle">
            <ul class="topbar-icons navbar-nav flex-row align-items-center gap-1">
            <li class="nav-item"><a class="nav-link" href="./index.php" title="Home" aria-label="Home"><i class="ti ti-home icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./community.php" title="Community" aria-label="Community"><i class="ti ti-users icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./pets.php" title="Pets" aria-label="Pets"><i class="ti ti-paw icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./shelters.php" title="Shelters" aria-label="Shelters"><i class="ti ti-building-community icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./adoptions.php" title="Adoptions" aria-label="Adoptions"><i class="ti ti-heart-handshake icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./events.php" title="Events" aria-label="Events"><i class="ti ti-calendar-event icon-huge"></i></a></li>
            <li class="nav-item"><a class="nav-link" href="./favorites.php" title="Favorites" aria-label="Favorites"><i class="ti ti-star icon-huge"></i></a></li>
            </ul>
        </div>

        <!-- Right Section -->
        <div class="d-flex align-items-center ms-auto gap-2">
            <div class="d-flex align-items-center gap-1 user-actions">
                 <a class="nav-link position-relative p-0 d-flex align-items-center justify-content-center"
                    href="./messages.php" title="Messages" aria-label="Messages"
                    style="height:44px; width:44px; border-radius:10px;">
                    <i class="ti ti-message-circle icon-huge"></i>
                </a>
                <a class="nav-link position-relative p-0 d-flex align-items-center justify-content-center"
                    href="./notifications.php" title="Notifications" aria-label="Notifications"
                    style="height:44px; width:44px; border-radius:10px;">
                    <i class="ti ti-bell-ringing icon-huge"></i>
                    <span class="notification-dot bg-danger rounded-circle position-absolute" style="width:10px;height:10px;top:4px;right:4px;"></span>
                </a>
                <div class="dropdown">
                    <a class="nav-link p-0 d-flex align-items-center" href="#" id="userMenu" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                        <img src="../../assets/images/profile/user-1.jpg" alt="Profile" width="40" height="40"
                            class="rounded-circle profile-avatar" />
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="./profile.php"><i class="ti ti-user me-1"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="./messages.php"><i class="ti ti-message-circle me-1"></i>
                                Messages</a></li>
                        <li><a class="dropdown-item" href="./settings.php"><i class="ti ti-settings me-1"></i> Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <!-- Fixed logout path -->
                        <li><a class="dropdown-item" href="../user-authentication/authentication-logout.php"><i class="ti ti-logout me-1"></i> Logout</a>
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