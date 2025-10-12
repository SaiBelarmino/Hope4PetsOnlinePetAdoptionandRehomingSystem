<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

require_once __DIR__ . '/../controllers/shelter-verification-requests-controller.php';
$shelters = ShelterVerificationRequestsController::listSheltersWithOwnerAndCount();
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Shelters</h3>
    </div>

    <?php if (empty($shelters)): ?>
        <div class="alert alert-info">No shelters found.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($shelters as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($s['shelter_name'] ?? '–') ?></h5>
                                    <?php if (!empty($s['is_verified'])): ?>
                                        <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unverified</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <a href="shelters-view.php?id=<?= intval($s['id']) ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                </div>
                            </div>

                            <p class="mb-1 mt-2"><strong>Owner:</strong> <?= htmlspecialchars($s['owner_name'] ?? '-') ?></p>
                            <p class="mb-0"><strong>Pets:</strong> <?= intval($s['pet_count'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
</div>
