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
    ?>

    <div class="container-fluid">
        <!-- Top Summary Cards (Important Metrics) -->
        <div class="row mb-4">
            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Total Pets Listed</h6>
                        <h3 id="totalPets" class="fw-bold"><?php echo (int)($stats['total_pets'] ?? 0); ?></h3>
                        <small class="text-muted">All adoptable pets</small>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Adoption Requests</h6>
                        <h3 id="adoptionTotal" class="fw-bold"><?php echo (int)($stats['adoption_requests_total'] ?? 0); ?></h3>
                        <small class="text-muted">Pending: <span id="adoptionPending"><?php echo (int)($stats['adoption_requests_pending'] ?? 0); ?></span></small>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Rehoming Requests</h6>
                        <h3 id="rehomingRequests" class="fw-bold"><?php echo (int)($stats['rehoming_requests_total'] ?? 0); ?></h3>
                        <small class="text-muted">Users surrendering pets</small>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Approved Adoptions</h6>
                        <h3 id="approvedAdoptions" class="fw-bold"><?php echo (int)($stats['approved_adoptions'] ?? 0); ?></h3>
                        <small class="text-muted">Successful placements</small>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Registered Users</h6>
                        <h3 id="registeredUsers" class="fw-bold"><?php echo (int)($stats['registered_users'] ?? 0); ?></h3>
                        <small class="text-muted">Owners + adopters</small>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Total Shelters</h6>
                        <h3 id="totalShelters" class="fw-bold"><?php echo (int)($stats['total_shelters'] ?? 0); ?></h3>
                        <small class="text-muted">Active shelters</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Top Summary Cards -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2 mb-4">
                            Traffic Overview
                            <span>
                                <iconify-icon icon="solar:question-circle-bold" class="fs-7 d-flex text-muted"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="tooltip-success" data-bs-title="Traffic Overview">
                                </iconify-icon>
                            </span>
                        </h5>
                        <div id="traffic-overview"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/backgrounds/product-tip.png" alt="image" class="img-fluid" width="205">
                        <h4 class="mt-7">Productivity Tips!</h4>
                        <p class="card-subtitle mt-2 mb-3">Duis at orci justo nulla in libero id leo molestie sodales phasellus justo.</p>
                        <button class="btn btn-primary mb-3">View All Tips</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">View by page title and screen class</h5>
                        <div class="table-responsive">
                            <table class="table text-nowrap align-middle mb-0">
                                <thead>
                                    <tr class="border-2 border-bottom border-primary border-0">
                                        <th scope="col" class="ps-0">Page Title</th>
                                        <th scope="col">Link</th>
                                        <th scope="col" class="text-center">Pageviews</th>
                                        <th scope="col" class="text-center">Page Value</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider">
                                    <tr>
                                        <th scope="row" class="ps-0 fw-medium">
                                            <span class="table-link1 text-truncate d-block">Welcome to our website</span>
                                        </th>
                                        <td>
                                            <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">/index.php</a>
                                        </td>
                                        <td class="text-center fw-medium">$18,456</td>
                                        <td class="text-center fw-medium">$2.40</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="ps-0 fw-medium">
                                            <span class="table-link1 text-truncate d-block">Modern Admin Dashboard Template</span>
                                        </th>
                                        <td>
                                            <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">/dashboard</a>
                                        </td>
                                        <td class="text-center fw-medium">17,452</td>
                                        <td class="text-center fw-medium">$0.97</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="ps-0 fw-medium">
                                            <span class="table-link1 text-truncate d-block">Explore our product catalog</span>
                                        </th>
                                        <td>
                                            <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">/product-checkout</a>
                                        </td>
                                        <td class="text-center fw-medium">12,180</td>
                                        <td class="text-center fw-medium">$7,50</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="ps-0 fw-medium">
                                            <span class="table-link1 text-truncate d-block">Comprehensive User Guide</span>
                                        </th>
                                        <td>
                                            <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">/docs</a>
                                        </td>
                                        <td class="text-center fw-medium">800</td>
                                        <td class="text-center fw-medium">$5,50</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="ps-0 fw-medium border-0">
                                            <span class="table-link1 text-truncate d-block">Check out our services</span>
                                        </th>
                                        <td class="border-0">
                                            <a href="javascript:void(0)" class="link-primary text-dark fw-medium d-block">/services</a>
                                        </td>
                                        <td class="text-center fw-medium border-0">1300</td>
                                        <td class="text-center fw-medium border-0">$2,15</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2 mb-5 pb-3">Sessions by device<span>
                                <iconify-icon icon="solar:question-circle-bold" class="fs-7 d-flex text-muted"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" data-bs-title="Locations">
                                </iconify-icon>
                            </span>
                        </h5>
                        <div class="row">
                            <div class="col-4">
                                <iconify-icon icon="solar:laptop-minimalistic-line-duotone" class="fs-7 d-flex text-primary"></iconify-icon>
                                <span class="fs-11 mt-2 d-block text-nowrap">Computers</span>
                                <h4 class="mb-0 mt-1">87%</h4>
                            </div>
                            <div class="col-4">
                                <iconify-icon icon="solar:smartphone-line-duotone" class="fs-7 d-flex text-secondary"></iconify-icon>
                                <span class="fs-11 mt-2 d-block text-nowrap">Smartphone</span>
                                <h4 class="mb-0 mt-1">9.2%</h4>
                            </div>
                            <div class="col-4">
                                <iconify-icon icon="solar:tablet-line-duotone" class="fs-7 d-flex text-success"></iconify-icon>
                                <span class="fs-11 mt-2 d-block text-nowrap">Tablets</span>
                                <h4 class="mb-0 mt-1">3.1%</h4>
                            </div>
                        </div>
                        <div class="vstack gap-4 mt-7 pt-2">
                            <div>
                                <div class="hstack justify-content-between">
                                    <span class="fs-3 fw-medium">Computers</span>
                                    <h6 class="fs-3 fw-medium text-dark lh-base mb-0">87%</h6>
                                </div>
                                <div class="progress mt-6" role="progressbar" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="hstack justify-content-between">
                                    <span class="fs-3 fw-medium">Smartphones</span>
                                    <h6 class="fs-3 fw-medium text-dark lh-base mb-0">9.2%</h6>
                                </div>
                                <div class="progress mt-6" role="progressbar" aria-valuenow="9" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-secondary" style="width: 50%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="hstack justify-content-between">
                                    <span class="fs-3 fw-medium">Tablets</span>
                                    <h6 class="fs-3 fw-medium text-dark lh-base mb-0">3.1%</h6>
                                </div>
                                <div class="progress mt-6" role="progressbar" aria-valuenow="3" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success" style="width: 35%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card overflow-hidden hover-img">
                    <div class="position-relative">
                        <a href="javascript:void(0)">
                            <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/blog/blog-img1.jpg" class="card-img-top" alt="matdash-img">
                        </a>
                        <span class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2 min Read</span>
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/profile/user-3.jpg" alt="matdash-img" class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40" height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                    </div>
                    <div class="card-body p-4">
                        <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Social</span>
                        <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">As yen tumbles, gadget-loving Japan goes for secondhand iPhones</a>
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-eye text-dark fs-5"></i>9,125
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-message-2 text-dark fs-5"></i>3
                            </div>
                            <div class="d-flex align-items-center fs-2 ms-auto">
                                <i class="ti ti-point text-dark"></i>Mon, Dec 19
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card overflow-hidden hover-img">
                    <div class="position-relative">
                        <a href="javascript:void(0)">
                            <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/blog/blog-img2.jpg" class="card-img-top" alt="matdash-img">
                        </a>
                        <span class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2 min Read</span>
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/profile/user-2.jpg" alt="matdash-img" class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40" height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                    </div>
                    <div class="card-body p-4">
                        <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Gadget</span>
                        <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">Intel loses bid to revive antitrust case against patent foe Fortress</a>
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-eye text-dark fs-5"></i>4,150
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-message-2 text-dark fs-5"></i>38
                            </div>
                            <div class="d-flex align-items-center fs-2 ms-auto">
                                <i class="ti ti-point text-dark"></i>Sun, Dec 18
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card overflow-hidden hover-img">
                    <div class="position-relative">
                        <a href="javascript:void(0)">
                            <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/blog/blog-img3.jpg" class="card-img-top" alt="matdash-img">
                        </a>
                        <span class="badge text-bg-light text-dark fs-2 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">2 min Read</span>
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/profile/user-3.jpg" alt="matdash-img" class="img-fluid rounded-circle position-absolute bottom-0 start-0 mb-n9 ms-9" width="40" height="40" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Georgeanna Ramero">
                    </div>
                    <div class="card-body p-4">
                        <span class="badge text-bg-light fs-2 py-1 px-2 lh-sm  mt-3">Health</span>
                        <a class="d-block my-4 fs-5 text-dark fw-semibold link-primary" href="">COVID outbreak deepens as more lockdowns loom in China</a>
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-eye text-dark fs-5"></i>9,480
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-message-2 text-dark fs-5"></i>12
                            </div>
                            <div class="d-flex align-items-center fs-2 ms-auto">
                                <i class="ti ti-point text-dark"></i>Sat, Dec 17
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include dirname(__DIR__, 2) . '/footer.php';  ?>
    </div>
</div>
<script>
(function pollStats(){
  const url = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/api/admin-stats.php';
  fetch(url, {cache: 'no-store'})
    .then(r => r.ok ? r.json() : Promise.reject(r.statusText))
    .then(data => {
      if (!data) return;
      const set = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val ?? 0; };
      set('totalPets', data.total_pets);
      set('adoptionTotal', data.adoption_requests_total);
      set('adoptionPending', data.adoption_requests_pending);
      set('rehomingRequests', data.rehoming_requests_total);
      set('approvedAdoptions', data.approved_adoptions);
      set('registeredUsers', data.registered_users);
      set('totalShelters', data.total_shelters);
    })
    .catch(()=>{/* silent */})
    .finally(()=> setTimeout(pollStats, 10000)); // poll every 10s
})();
</script>