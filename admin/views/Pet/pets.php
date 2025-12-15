<?php

require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
<?php include dirname(__DIR__, 2) . '/header.php'; ?>
<div class="container-fluid">

    <?php
    require_once dirname(__DIR__, 2) . '/controllers/Pet/pets-controller.php';
    $allSpecies = PetsController::getDistinctSpecies();

    // Get filter values from GET request
    $nameSearch = $_GET['name'] ?? '';
    $speciesFilter = $_GET['species'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    $minAgeFilter = $_GET['min_age'] ?? '';
    $maxAgeFilter = $_GET['max_age'] ?? '';

    $filters = [
        'name' => $nameSearch,
        'species' => $speciesFilter,
        'status' => $statusFilter,
        'min_age' => $minAgeFilter,
        'max_age' => $maxAgeFilter,
    ];

    $pets = PetsController::listAll($filters);
    ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Search & Filter</h5>
            <form action="pets.php" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="name" class="form-label">Search by Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter pet name" value="<?= htmlspecialchars($nameSearch) ?>">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="species" class="form-label">Filter by Species</label>
                        <select class="form-select" id="species" name="species">
                            <option value="">All Species</option>
                            <?php foreach ($allSpecies as $spec) : ?>
                                <option value="<?= htmlspecialchars($spec['species']) ?>" <?= $speciesFilter == $spec['species'] ? 'selected' : '' ?>><?= htmlspecialchars($spec['species']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Available" <?= $statusFilter == 'Available' ? 'selected' : '' ?>>Available</option>
                            <option value="Adopted" <?= $statusFilter == 'Adopted' ? 'selected' : '' ?>>Adopted</option>
                            <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Rehomed" <?= $statusFilter == 'Rehomed' ? 'selected' : '' ?>>Rehomed</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label">Age Range</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="min_age" placeholder="Min" value="<?= htmlspecialchars($minAgeFilter) ?>">
                            <input type="number" class="form-control" name="max_age" placeholder="Max" value="<?= htmlspecialchars($maxAgeFilter) ?>">
                        </div>
                    </div>
                    <div class="col-lg-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Age</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pets)) : ?>
                            <tr>
                                <td colspan="8" class="text-center">No pets found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($pets as $pet) : ?>
                                <?php
                                $status = htmlspecialchars($pet['status']);
                                $badgeClass = 'bg-primary'; // Default class
                                switch (strtolower($status)) {
                                    case 'available':
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 'adopted':
                                        $badgeClass = 'bg-secondary';
                                        break;
                                    case 'pending':
                                        $badgeClass = 'bg-warning';
                                        break;
                                    case 'rehomed':
                                        $badgeClass = 'bg-info';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($pet['id']) ?></td>
                                    <td><?= htmlspecialchars($pet['name']) ?></td>
                                    <td><?= htmlspecialchars($pet['species']) ?></td>
                                    <td><?= htmlspecialchars($pet['breed']) ?></td>
                                    <td><?= htmlspecialchars($pet['age']) ?></td>
                                    <td><span class="badge <?= $badgeClass ?> rounded-3 fw-semibold"><?= $status ?></span></td>
                                    <td><?= htmlspecialchars((new DateTime($pet['created_at']))->format('F j, Y')) ?></td>
                                    <td>
                                        <a href="edit-pet.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <a href="view-pet.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-outline-info">View</a>
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
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>
