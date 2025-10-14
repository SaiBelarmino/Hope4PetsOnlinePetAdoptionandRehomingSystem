<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid">
    <div class="row g-3">
        <!-- Left Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center Content -->
        <div class="col-12 col-lg-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
                <a href="./pet_view.php?id=<?php echo (int)($pet['id'] ?? 0); ?>" class="btn btn-outline-secondary"><i
                        class="ti ti-arrow-left"></i> Back to Pet</a>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-2">Application Form</h5>
                    <?php if(!empty($existingApplication)): ?>
                    <div class="alert alert-info">You already applied on
                        <?php echo htmlspecialchars(date('M d, Y', strtotime($existingApplication['created_at']))); ?>.
                        Current status:
                        <strong><?php echo htmlspecialchars(ucfirst($existingApplication['status'])); ?></strong></div>
                    <?php else: ?>
                    <form method="post" action="../controllers/adopt-controller.php">
                        <input type="hidden" name="pet_id" value="<?php echo (int)($pet['id'] ?? 0); ?>">
                        <div class="mb-3">
                            <label class="form-label">Why do you want to adopt this pet?</label>
                            <textarea class="form-control" name="reason" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Do you have other pets?</label>
                            <textarea class="form-control" name="other_pets" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Home Environment</label>
                            <textarea class="form-control" name="home_env" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agree" required>
                            <label for="agree" class="form-check-label small">I confirm the information provided is
                                accurate and I consent to a possible home visit.</label>
                        </div>
                        <button class="btn btn-primary"><i class="ti ti-heart"></i> Submit Application</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Right Sidebar -->
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Pet Summary</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-1"><strong><?php echo htmlspecialchars($pet['name'] ?? ''); ?></strong></p>
                    <p class="text-muted mb-2">
                        <?php echo htmlspecialchars(($pet['species'] ?? '').' • '.($pet['breed'] ?? '')); ?></p>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <span
                            class="badge bg-light text-dark"><?php echo htmlspecialchars($pet['age'] ?? '?'); ?></span>
                        <span
                            class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($pet['gender'] ?? '')); ?></span>
                        <span
                            class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($pet['size'] ?? '')); ?></span>
                    </div>
                    <p class="small mb-1"><strong>Location:</strong>
                        <?php echo htmlspecialchars($pet['location'] ?? '—'); ?></p>
                    <p class="small mb-1"><strong>Health:</strong>
                        <?php echo htmlspecialchars($pet['health_status'] ?? '—'); ?></p>
                    <p class="small mb-1"><strong>Vaccine:</strong>
                        <?php echo htmlspecialchars($pet['vaccine_status'] ?? '—'); ?></p>
                    <p class="small mb-0"><strong>Status:</strong> <span
                            class="badge bg-<?php echo ['available'=>'success','pending'=>'warning','adopted'=>'secondary','removed'=>'dark'][$pet['status'] ?? ''] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($pet['status'] ?? '')); ?></span>
                    </p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Provide clear answers. Honest details speed up approval.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>