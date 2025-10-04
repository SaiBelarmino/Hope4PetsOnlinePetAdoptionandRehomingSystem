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
    // Early theme guard
    (function() {
        try {
            var stored = localStorage.getItem('admin_theme');
            var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var target = stored || (document.cookie.match(/(?:^|; )admin_theme=(dark|light)/) || [])[1] || (systemDark ? 'dark' : 'light');
            if (!document.body.classList.contains('theme-' + target)) {
                document.body.classList.remove('theme-dark','theme-light');
                document.body.classList.add('theme-' + target);
            }
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
                    <a href="./dashboard.php" class="text-nowrap logo-img">
                        <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="logo" class="img-fluid" style="max-height: 40px;" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <!-- Main -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Main</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./shelter-dashboard.php" aria-expanded="false">
                                <span>
                                    <i class="ti ti-home fs-6"></i>
                                </span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>

                        <!-- Pet Management -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Pet Management</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./add_pet.php"><span><i class="ti ti-plus fs-6"></i></span><span class="hide-menu">Add New Pet</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./edit_pet.php"><span><i class="ti ti-edit fs-6"></i></span><span class="hide-menu">Edit Pet Info</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./upload_photos.php"><span><i class="ti ti-photo fs-6"></i></span><span class="hide-menu">Upload Photos</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./mark_adopted.php"><span><i class="ti ti-check fs-6"></i></span><span class="hide-menu">Mark as Adopted</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./remove_listing.php"><span><i class="ti ti-trash fs-6"></i></span><span class="hide-menu">Remove Listing</span></a></li>

                        <!-- Adoption Requests -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Adoption Requests</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./adoption_requests.php"><span><i class="ti ti-file-description fs-6"></i></span><span class="hide-menu">View Requests</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./adoption_approve.php"><span><i class="ti ti-thumb-up fs-6"></i></span><span class="hide-menu">Approve Adoption</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./adoption_reject.php"><span><i class="ti ti-thumb-down fs-6"></i></span><span class="hide-menu">Reject Adoption</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./adoption_completed.php"><span><i class="ti ti-circle-check fs-6"></i></span><span class="hide-menu">Mark Completed</span></a></li>

                        <!-- Donations -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Donations</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./view_donations.php"><span><i class="ti ti-currency-dollar fs-6"></i></span><span class="hide-menu">View Donations</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./donation_filter.php"><span><i class="ti ti-filter fs-6"></i></span><span class="hide-menu">Filter by Date / Donor</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./donation_report.php"><span><i class="ti ti-report fs-6"></i></span><span class="hide-menu">Donation Report</span></a></li>

                        <!-- Shelter Profile & Verification -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Shelter Profile & Verification</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./edit_shelter_profile.php"><span><i class="ti ti-settings fs-6"></i></span><span class="hide-menu">Edit Shelter Profile</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./upload_documents.php"><span><i class="ti ti-upload fs-6"></i></span><span class="hide-menu">Upload Documents</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./verification_status.php"><span><i class="ti ti-badge-check fs-6"></i></span><span class="hide-menu">Verification Status</span></a></li>

                        <!-- Messages -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Messages</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./messages_inbox.php"><span><i class="ti ti-mail fs-6"></i></span><span class="hide-menu">Inbox</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./messages_reply.php"><span><i class="ti ti-send fs-6"></i></span><span class="hide-menu">Send Reply</span></a></li>

                        <!-- Community Posts (optional) -->
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                            <span class="hide-menu">Community Posts</span>
                        </li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./create_post.php"><span><i class="ti ti-square-plus fs-6"></i></span><span class="hide-menu">Create Post</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./upload_post_photos.php"><span><i class="ti ti-photo-plus fs-6"></i></span><span class="hide-menu">Upload Photos</span></a></li>
                        <li class="sidebar-item"><a class="sidebar-link" href="./delete_post.php"><span><i class="ti ti-trash fs-6"></i></span><span class="hide-menu">Delete Own Post</span></a></li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>