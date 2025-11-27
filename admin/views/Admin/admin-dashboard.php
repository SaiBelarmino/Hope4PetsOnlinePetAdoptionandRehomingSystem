<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();

if (class_exists('AdminSessionManager')) {
    AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
}

// The sidebar should be included within the main page structure, typically after the header.
// I will move this include to the correct place inside the body tag.

// Include the controller to fetch data
require_once __DIR__ . '/../../controllers/Admin/admin-dashboard-controllers.php';

// Fetch all dashboard data
$stats = AdminDashboardController::stats();
$petDistribution = AdminDashboardController::getPetTypeDistribution();
$recentActivities = AdminDashboardController::getRecentActivities(5); // Reduced for a cleaner look
$trafficOverviewData = AdminDashboardController::getTrafficOverviewData();

// Prepare data for charts
$petTypeLabels = json_encode(array_column($petDistribution, 'type'));
$petTypeData = json_encode(array_column($petDistribution, 'count'));
$trafficOverviewJson = json_encode($trafficOverviewData);

// Base URL for assets
$baseUrl = '/Hope4PetsOnlinePetAdoptionandRehomingSystem';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- Admin Theme CSS -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/admin-theme.css">
    
    <!-- Note: Bootstrap is usually part of the theme, but including it for safety -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="theme-light">

    <!-- Preloader -->
    <div class="h4p-preloader">
        <div class="h4p-preloader-inner">
            <div class="h4p-spinner">
                <div class="h4p-spinner-core"><i class="ti ti-paw"></i></div>
            </div>
            <div class="h4p-preloader-text">Loading Dashboard</div>
        </div>
    </div>

    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <?php include __DIR__ . '/../../sidebar.php'; ?>
        </aside>
        <!--  Sidebar End -->

        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            <header class="app-header">
                <?php include dirname(__DIR__, 2) . '/header.php'; ?>
            </header>
            <!--  Header End -->

            <div class="container-fluid">
                <h1 class="h2 mb-4 fw-semibold">Dashboard</h1>

                <!-- Stat Cards -->
                <div class="row metrics-row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary-subtle rounded-2"><i class="ti ti-users text-primary"></i></div>
                                <div>
                                    <p class="text-muted small mb-1">Registered Users</p>
                                    <h3 class="fw-semibold mb-0"><?php echo $stats['registered_users']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success-subtle rounded-2"><i class="ti ti-paw text-success"></i></div>
                                <div>
                                    <p class="text-muted small mb-1">Total Pets</p>
                                    <h3 class="fw-semibold mb-0"><?php echo $stats['total_pets']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning-subtle rounded-2"><i class="ti ti-hourglass text-warning"></i></div>
                                <div>
                                    <p class="text-muted small mb-1">Pending Adoptions</p>
                                    <h3 class="fw-semibold mb-0"><?php echo $stats['adoption_requests_pending']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info-subtle rounded-2"><i class="ti ti-check text-info"></i></div>
                                <div>
                                    <p class="text-muted small mb-1">Approved Adoptions</p>
                                    <h3 class="fw-semibold mb-0"><?php echo $stats['approved_adoptions']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-lg-8 d-flex align-items-stretch">
                        <div class="card w-100">
                            <div class="card-body">
                                <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                                    <div class="mb-3 mb-sm-0">
                                        <h5 class="card-title fw-semibold">Traffic Overview</h5>
                                        <p class="card-subtitle">New users and pets added in the last 7 days</p>
                                    </div>
                                </div>
                                <div id="traffic-overview"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold">Pet Distribution</h5>
                                <p class="card-subtitle mb-4">Breakdown of available pet types</p>
                                <div id="pet-type-chart" class="d-flex align-items-center justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title fw-semibold">Recent System Activity</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentActivities)): ?>
                                                <tr><td colspan="3" class="text-center text-muted">No recent activities.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($recentActivities as $activity): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-primary-subtle text-primary fw-semibold fs-2">
                                                                <?php echo str_replace('_', ' ', htmlspecialchars($activity['type'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                                        <td class="text-muted"><?php echo date('M d, H:i', strtotime($activity['activity_date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
        </div>
    </div>

    <!-- Libs JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Theme JS -->
    <script src="<?php echo $baseUrl; ?>/assets/js/theme.js"></script>

    <script>
    $(function () {
        // Data from PHP
        const trafficData = <?php echo $trafficOverviewJson; ?>;
        const petTypeLabels = <?php echo $petTypeLabels; ?>;
        const petTypeData = <?php echo $petTypeData; ?>;

        // =====================================
        // Traffic Overview Chart
        // =====================================
        var trafficOverviewChart = {
            series: trafficData.series,
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                toolbar: { show: false },
                fontFamily: "inherit",
                foreColor: "#adb0bb",
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '35%',
                    borderRadius: 6,
                },
            },
            colors: ["#5D87FF", "#49BEFF"],
            dataLabels: { enabled: false },
            legend: { show: true, position: 'top', horizontalAlign: 'right' },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            grid: {
                borderColor: "rgba(0,0,0,0.1)",
                strokeDashArray: 3,
            },
            xaxis: {
                categories: trafficData.labels,
                axisBorder: { show: false },
            },
            yaxis: {
                tickAmount: 4,
            },
            tooltip: {
                theme: "dark",
                fillSeriesColor: false,
            },
        };

        var chart = new ApexCharts(document.querySelector("#traffic-overview"), trafficOverviewChart);
        chart.render();

        // =====================================
        // Pet Type Distribution Chart
        // =====================================
        var petTypeChart = {
            series: petTypeData,
            labels: petTypeLabels,
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: "inherit",
                foreColor: '#adb0bb'
            },
            plotOptions: {
                pie: {
                    startAngle: 0,
                    endAngle: 360,
                    donut: {
                        size: '75%',
                    },
                },
            },
            stroke: { show: false },
            dataLabels: { enabled: false },
            legend: { show: false },
            colors: ["#5D87FF", "#49BEFF", "#13DEB9", "#FFAE1F", "#FA896B"],
            tooltip: {
                theme: "dark",
                fillSeriesColor: false,
            },
        };

        var chart = new ApexCharts(document.querySelector("#pet-type-chart"), petTypeChart);
        chart.render();
        
        // Hide preloader after a short delay to ensure rendering
        setTimeout(() => {
            $('.h4p-preloader').addClass('hidden');
        }, 300);
    });
    </script>

</body>
</html>
