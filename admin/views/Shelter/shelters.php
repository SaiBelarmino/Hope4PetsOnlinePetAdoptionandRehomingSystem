<?php
// 1. SETUP & DATA FETCHING (HANDLER LOGIC)
// ==================================================
require_once __DIR__ . '/../../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/Shelter/shelters-controller.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// Get filter parameters from URL
$search = htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES);
$status = htmlspecialchars($_GET['status'] ?? '', ENT_QUOTES);
$registeredDate = htmlspecialchars($_GET['registered_date'] ?? '', ENT_QUOTES);

// Fetch all shelter data using the controller method with filters
$shelters = SheltersController::listAllSheltersWithDetails($search, $status, $registeredDate);
$shelterStats = SheltersController::getShelterStats();
?>

<?php
// 2. HTML RENDERING (VIEW LOGIC)
// ==================================================
include dirname(__DIR__, 2) . '/sidebar.php';
?>

<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Shelters</h3>
        </div>

        <!-- Shelter Stats Cards -->
        <div class="row">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h6 class="mb-0">Total Shelters</h6>
                            <span class="fs-4 fw-bold"><?= $shelterStats['total'] ?></span>
                        </div>
                        <div class="ms-auto">
                            <div class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light-primary text-primary">
                                <i class="ti ti-building-community fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h6 class="mb-0">Verified</h6>
                            <span class="fs-4 fw-bold text-success"><?= $shelterStats['verified'] ?></span>
                        </div>
                        <div class="ms-auto">
                            <div class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light-success text-success">
                                <i class="ti ti-circle-check fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <h6 class="mb-0">Unverified</h6>
                            <span class="fs-4 fw-bold text-warning"><?= $shelterStats['unverified'] ?></span>
                        </div>
                        <div class="ms-auto">
                            <div class="d-flex align-items-center justify-content-center w-35px h-35px rounded-circle bg-light-warning text-warning">
                                <i class="ti ti-clock-hour-4 fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="shelters.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Shelter Name" value="<?= $search ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="" <?= $status === '' ? 'selected' : '' ?>>All</option>
                            <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
                            <option value="unverified" <?= $status === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="registered_date" class="form-label">Registered Date</label>
                        <input type="date" class="form-control" id="registered_date" name="registered_date" value="<?= $registeredDate ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="shelters.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Shelter Name</th>
                                <th>Address</th>
                                <th>Contact Person</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Date Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($shelters)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No shelters found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($shelters as $s): ?>
                                    <tr>
                                        <td style="min-width: 150px; white-space: normal;"><?= htmlspecialchars($s['shelter_name'] ?? '–') ?></td>
                                        <td style="min-width: 100px; white-space: normal;"><?= htmlspecialchars($s['address'] ?? '–') ?></td>
                                        <td style="min-width: 100px; white-space: normal;"><?= htmlspecialchars($s['owner_name'] ?? '–') ?></td>
                                        <td style="min-width: 100px; white-space: normal;"><?= htmlspecialchars($s['contact_number'] ?? '–') ?></td>
                                        <td style="min-width: 100px; white-space: normal;"><?= htmlspecialchars($s['owner_email'] ?? '–') ?></td>
                                        <td>
                                            <?php if (!empty($s['is_verified'])): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Unverified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars((new DateTime($s['created_at']))->format('M d, Y') ?? '–') ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Shelter Actions">
                                                <a href="view-shelter.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="delete-shelter.php?id=<?= $s['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this shelter?');">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </div>
                                        </td>
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

<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
