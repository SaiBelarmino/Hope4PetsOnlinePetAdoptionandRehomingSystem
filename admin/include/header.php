<header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light d-flex align-items-center px-3 w-100" style="min-height:60px;">
            <ul class="navbar-nav flex-row align-items-center me-auto gap-2">
                <li class="nav-item d-block d-xl-none">
                    <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-icon-hover position-relative" href="javascript:void(0)">
                        <i class="ti ti-bell-ringing"></i>
                        <span class="position-absolute top-0 start-100" style="transform:translate(-70%, -30%);">
                            <span class="spinner-grow spinner-grow-sm text-primary" style="width:.55rem;height:.55rem;" role="status" aria-label="New notifications"></span>
                        </span>
                    </a>
                </li>
                <!-- Theme Toggle -->
                <li class="nav-item">
                    <button id="themeToggle" class="nav-link nav-icon-hover btn btn-link p-0" type="button" aria-label="Toggle dark mode" title="Toggle dark / light mode">
                        <i class="ti ti-moon-stars fs-5 theme-icon theme-icon-dark"></i>
                        <i class="ti ti-sun fs-5 theme-icon theme-icon-light"></i>
                    </button>
                </li>
            </ul>
            <div class="navbar-collapse d-flex justify-content-end px-0" id="navbarNav">
                <ul class="navbar-nav flex-row align-items-center gap-2">
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../../assets/images/profile/user-1.jpg" alt="" width="35" height="35"
                                class="rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                            <div class="message-body">
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                    <i class="ti ti-user fs-6"></i>
                                    <p class="mb-0 fs-3">My Profile</p>
                                </a>
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                    <i class="ti ti-mail fs-6"></i>
                                    <p class="mb-0 fs-3">My Account</p>
                                </a>
                                <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                                    <i class="ti ti-list-check fs-6"></i>
                                    <p class="mb-0 fs-3">My Task</p>
                                </a>
                                <a href="../api/logout.php"
                                    class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>