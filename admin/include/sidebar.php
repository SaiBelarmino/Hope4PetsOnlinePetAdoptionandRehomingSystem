
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hope4Pets</title>
    <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo-icon.png" />
    <link rel="stylesheet" href="../../assets/css/styles.min.css" />
    <!-- Dark/Light theme overrides -->
    <link rel="stylesheet" href="../../assets/css/admin-theme.css" />
</head>

<?php
    // Determine theme preference (cookie set by JS) fallback to light
    $adminTheme = isset($_COOKIE['admin_theme']) && in_array($_COOKIE['admin_theme'], ['dark','light'])
        ? $_COOKIE['admin_theme']
        : 'light';
?>
<body class="theme-<?= $adminTheme; ?> preload">
    <!-- Preloader -->
    <div id="preloader" class="h4p-preloader" aria-hidden="true">
        <div class="h4p-preloader-inner">
            <div class="h4p-spinner">
                <span class="h4p-spinner-core"><i class="ti ti-paw"></i></span>
            </div>
            <div class="h4p-preloader-text">Loading…</div>
        </div>
    </div>
    <script>
    // Early theme guard: choose stored theme or system preference before paint, then remove preload to allow transitions.
    (function() {
        try {
            var stored = localStorage.getItem('admin_theme');
            var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var target = stored || (document.cookie.match(/(?:^|; )admin_theme=(dark|light)/) || [])[1] || (systemDark ? 'dark' : 'light');
            if (!document.body.classList.contains('theme-' + target)) {
                document.body.classList.remove('theme-dark','theme-light');
                document.body.classList.add('theme-' + target);
            }
            // Remove preload after a tick
            requestAnimationFrame(function(){ document.body.classList.remove('preload'); });
        } catch(e) { document.body.classList.remove('preload'); }
    })();
    </script>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a href="./admin-dashboard.php" class="text-nowrap logo-img">
                        <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="logo" class="img-fluid" style="max-height: 40px;" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Admin Tools</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./admin-dashboard.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-layout-dashboard fs-6"></i>
                                </span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">User Management</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./users.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-users fs-6"></i>
                                </span>
                                <span class="hide-menu">All Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./id-verification-requests.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-id fs-6"></i>
                                </span>
                                <span class="hide-menu">ID Verification</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Shelter Management</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./shelters.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-building fs-6"></i>
                                </span>
                                <span class="hide-menu">All Shelters</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./shelter-verification-requests.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-file-check fs-6"></i>
                                </span>
                                <span class="hide-menu">Shelter Verification</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./shelter-pets.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-paw fs-6"></i>
                                </span>
                                <span class="hide-menu">Pets under Shelter</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Pet Management</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./pets.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-paw fs-6"></i>
                                </span>
                                <span class="hide-menu">All Pets</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./pet-photos-reports.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-photo fs-6"></i>
                                </span>
                                <span class="hide-menu">Pet Photos / Reports</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./adoption-requests.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-file-description fs-6"></i>
                                </span>
                                <span class="hide-menu">Adoption Requests</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Community/Post</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./posts.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-news fs-6"></i>
                                </span>
                                <span class="hide-menu">All Posts</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./post-comments.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-message-circle fs-6"></i>
                                </span>
                                <span class="hide-menu">Post Comments</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./post-reactions.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-thumb-up fs-6"></i>
                                </span>
                                <span class="hide-menu">Post Reactions</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Reports</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./user-reports.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-report fs-6"></i>
                                </span>
                                <span class="hide-menu">User Reports</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./pet-reports.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-report-analytics fs-6"></i>
                                </span>
                                <span class="hide-menu">Pet Reports</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./post-reports.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-report-analytics fs-6"></i>
                                </span>
                                <span class="hide-menu">Post Reports</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Donations</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./donations.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-currency-dollar fs-6"></i>
                                </span>
                                <span class="hide-menu">All Donations</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./donations-by-shelter.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-building fs-6"></i>
                                </span>
                                <span class="hide-menu">Donations by Shelter</span>
                            </a>
                        </li>
                         <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Messages</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./messages.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-mail fs-6"></i>
                                </span>
                                <span class="hide-menu">User Messages</span>
                            </a>
                        </li>
                        <!-- Category: Admin Tools -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Activity Logs</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./activity-logs.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-clipboard-list fs-6"></i>
                                </span>
                                <span class="hide-menu">Activity Logs</span>
                            </a>
                        </li>
                        <!-- Category: Account -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Account</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./profile-settings.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-settings fs-6"></i>
                                </span>
                                <span class="hide-menu">Profile Settings</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="../api/logout.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-logout fs-6"></i>
                                </span>
                                <span class="hide-menu">Logout</span>
                            </a>
                        </li>
                        
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>