<?php
// Protect this admin view from direct URL access
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<!--  Main wrapper -->
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>

    <?php
    // server-side initial stats for first render
    require_once dirname(__DIR__, 2) . '/controllers/Admin/admin-dashboard-controllers.php';
    $stats = AdminDashboardController::stats();
    $adoptionTrends = AdminDashboardController::getMonthlyAdoptionTrends();
    $petDistribution = AdminDashboardController::getPetTypeDistribution();
    $userRegistrations = AdminDashboardController::getNewUserRegistrations();
    $recentActivities = AdminDashboardController::getRecentActivities();
    ?>

   <div class="container-fluid">

  <!-- Section Title -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Dashboard Overview</h4>
    <button class="btn btn-primary btn-sm">Generate Report</button>
  </div>

  <!-- Stats Cards -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card p-3">
        <h6 class="text-muted mb-1">Total Users</h6>
        <h3 class="fw-bold">1,245</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3">
        <h6 class="text-muted mb-1">New Users</h6>
        <h3 class="fw-bold">312</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3">
        <h6 class="text-muted mb-1">Active Today</h6>
        <h3 class="fw-bold">560</h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3">
        <h6 class="text-muted mb-1">Bounce Rate</h6>
        <h3 class="fw-bold">34%</h3>
      </div>
    </div>
  </div>

  <!-- Chart Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-1">Traffic Overview</h5>
      <small class="text-muted">Weekly insights</small>
    </div>
    <div class="card-body">
      <div id="traffic-overview"></div>
    </div>
  </div>

</div>


<script>
  $(function () {
    var chartOptions = {
      series: [
        { name: "New Users", data: [5, 1, 17, 6, 15, 9, 6] },
        { name: "Users", data: [7, 11, 4, 16, 10, 14, 10] }
      ],
      chart: {
        type: "line",
        height: 320,
        toolbar: { show: false },
        fontFamily: "inherit",
        foreColor: "#adb0bb"
      },
      colors: ["var(--bs-gray-300)", "var(--bs-primary)"],
      dataLabels: { enabled: false },
      stroke: {
        width: 2,
        curve: "smooth",
        dashArray: [8, 0]
      },
      grid: {
        borderColor: "rgba(0,0,0,0.1)",
        strokeDashArray: 3,
      },
      xaxis: {
        categories: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: { tickAmount: 4 },
      markers: {
        strokeWidth: 2,
        strokeColor: ["var(--bs-gray-300)", "var(--bs-primary)"],
      },
      tooltip: { theme: "dark" }
    };

    var chart = new ApexCharts(
      document.querySelector("#traffic-overview"),
      chartOptions
    );
    chart.render();
  });
</script>
<script src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/js/admin-dashboard.js"></script>