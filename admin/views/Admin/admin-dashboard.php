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
        <!-- Top Summary Cards (Important Metrics) -->
        <div class="row mb-4 justify-content-center g-3">

            <!-- Total Pets Listed -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="summary-card text-center">
                    <h6>Total Pets Listed</h6>
                    <div class="summary-number">
                        <?php echo (int)($stats['total_pets'] ?? 0); ?>
                    </div>
                    <small>All adoptable pets</small>
                </div>
            </div>

            <!-- Adoption Requests -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="summary-card text-center">
                    <h6>Adoption Requests</h6>
                    <div class="summary-number">
                        <?php echo (int)($stats['adoption_requests_total'] ?? 0); ?>
                    </div>
                    <small>Pending:
                        <?php echo (int)($stats['adoption_requests_pending'] ?? 0); ?>
                    </small>
                </div>
            </div>

            <!-- Approved Adoptions -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="summary-card text-center">
                    <h6>Approved Adoptions</h6>
                    <div class="summary-number">
                        <?php echo (int)($stats['approved_adoptions'] ?? 0); ?>
                    </div>
                    <small>Successful placements</small>
                </div>
            </div>

            <!-- Registered Users -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="summary-card text-center">
                    <h6>Registered Users</h6>
                    <div class="summary-number">
                        <?php echo (int)($stats['registered_users'] ?? 0); ?>
                    </div>
                    <small>Owners + adopters</small>
                </div>
            </div>

            <!-- Total Shelters -->
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="summary-card text-center">
                    <h6>Total Shelters</h6>
                    <div class="summary-number">
                        <?php echo (int)($stats['total_shelters'] ?? 0); ?>
                    </div>
                    <small>Active shelters</small>
                </div>
            </div>

        </div>
        <!-- End Top Summary Cards -->

        <div class="row">
            <!-- Monthly Adoption Trends -->
            <div class="col-lg-8 d-flex align-items-strech">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                            <div class="mb-3 mb-sm-0">
                                <h5 class="card-title fw-semibold">Monthly Adoption Trends</h5>
                                <p class="card-subtitle">Number of approved adoptions per month</p>
                            </div>
                        </div>
                        <div id="adoption-trends-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Pet Types Distribution -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold">Pet Types Distribution</h5>
                        <p class="card-subtitle">Dogs, Cats, Birds, Rabbits, Others</p>
                        <div id="pet-types-chart" class="d-flex align-items-center justify-content-center"></div>
                    </div>
                </div>
            </div>

            <!-- New User Registrations -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold">New User Registrations</h5>
                        <p class="card-subtitle">New users over the last 30 days</p>
                        <div id="user-registrations-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold">Recent Activity Feed</h5>
                        <p class="card-subtitle">Latest actions from users and admins</p>
                        <div class="mt-4">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($recentActivities)) : ?>
                                    <li class="list-group-item">No recent activity.</li>
                                <?php else : ?>
                                    <?php foreach ($recentActivities as $activity) : ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <?php
                                                $icon = 'ti-info-circle'; // Default
                                                if ($activity['type'] === 'adoption_request') $icon = 'ti-file-text';
                                                if ($activity['type'] === 'user_registered') $icon = 'ti-user-plus';
                                                if ($activity['type'] === 'pet_added') $icon = 'ti-paw';
                                                if ($activity['type'] === 'adoption_approved') $icon = 'ti-circle-check';
                                                ?>
                                                <i class="ti <?php echo $icon; ?> me-2"></i>
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo date('M d, H:i', strtotime($activity['activity_date'])); ?>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include dirname(__DIR__, 2) . '/footer.php';  ?>
    </div>
</div>

<!-- Libs -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Chart Logic -->
<script>
    // Pass PHP data to JavaScript by attaching it to the window object
    window.dashboardData = {
        adoptionTrends: <?php echo json_encode($adoptionTrends); ?>,
        petDistribution: <?php echo json_encode($petDistribution); ?>,
        userRegistrations: <?php echo json_encode($userRegistrations); ?>
    };
</script>
<script src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/js/admin-dashboard.js"></script>