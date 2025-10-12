<?php
// Prevent "Undefined variable" warnings when view is included directly
$shelter = $shelter ?? null;
$pets = $pets ?? [];
?>
<?php include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid">
    <?php if (!$shelter): ?>
        <div class="alert alert-warning">Shelter not found.</div>
    <?php else: ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0"><?= htmlspecialchars($shelter['shelter_name'] ?? 'Shelter') ?>
                <?php if (!empty($shelter['is_verified'])): ?>
                    <span class="badge bg-success">Verified</span>
                <?php endif; ?>
            </h3>
            <a href="shelters.php" class="btn btn-sm btn-outline-secondary">Back to list</a>
        </div>

        <div class="mb-3">
            <h5>Owner</h5>
            <p><?= htmlspecialchars($shelter['owner_name'] ?? '-') ?> — <?= htmlspecialchars($shelter['owner_email'] ?? '-') ?></p>
        </div>

        <div>
            <h5>Pets (<?= count($pets) ?>)</h5>
            <?php if (empty($pets)): ?>
                <p>No pets found for this shelter.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($pets as $pet): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($pet['name']) ?></h6>
                                    <p class="mb-1"><?= htmlspecialchars($pet['species'] ?? '') ?> — <?= htmlspecialchars($pet['breed'] ?? '') ?></p>
                                    <p class="mb-0">Age: <?= htmlspecialchars($pet['age'] ?? '') ?> · Status: <?= htmlspecialchars($pet['status'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
</div>