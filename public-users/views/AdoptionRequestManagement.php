<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
// ensure variables are defined to avoid warnings when view is accessed directly
$requests = $requests ?? [];
$pageTitle = $pageTitle ?? 'Adoption Requests';
?>
<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left Sidebar -->
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        <!-- Center Content -->
        <div class="col-12 col-lg-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
            </div>

            <?php if (empty($requests)): ?>
            <div class="card">
                <div class="card-body text-center text-muted py-5">No adoption requests yet.</div>
            </div>
            <?php else: ?>
                <?php foreach ($requests as $r): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <img src="<?php echo htmlspecialchars($r['pet_photo'] ?? '/storage/uploads/images/default.png'); ?>" alt="pet"
                                    class="rounded" style="width:96px; height:96px; object-fit:cover;">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($r['pet_name'] ?? 'Pet'); ?></h5>
                                            <div class="small text-muted">Requested on <?php echo htmlspecialchars(date('M d, Y', strtotime($r['created_at'] ?? ''))); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-<?php echo ($r['status']==='applied')? 'warning' : (($r['status']==='approved')? 'success' : 'secondary'); ?>"><?php echo htmlspecialchars(ucfirst($r['status'] ?? '')); ?></span>
                                        </div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="small mb-2">
                                        <p class="mb-1"><strong>Applicant:</strong> <?php echo htmlspecialchars($r['applicant_name'] ?? ''); ?></p>
                                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($r['applicant_phone'] ?? ''); ?></p>
                                        <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($r['applicant_address'] ?? ''); ?></p>
                                        <p class="mb-0"><strong>Message:</strong> <?php echo htmlspecialchars($r['applicant_message'] ?? ''); ?></p>
                                    </div>

                                    <div class="mt-3 d-flex gap-2">
                                        <?php if (($r['status'] ?? '') === 'applied'): ?>
                                            <form method="post" action="../controllers/AdoptionActionController.php" class="d-inline">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="adoption_id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="post" action="../controllers/AdoptionActionController.php" class="d-inline">
                                                <input type="hidden" name="action" value="deny">
                                                <input type="hidden" name="adoption_id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Deny</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>Manage</button>
                                        <?php endif; ?>

                                        <a href="./pet_view.php?id=<?php echo (int)($r['pet_id'] ?? 0); ?>" class="btn btn-sm btn-outline-primary">View Pet</a>
                                        <a href="./ChatMessages.php?user_id=<?php echo (int)($r['applicant_id'] ?? 0); ?>" class="btn btn-sm btn-outline-secondary">Message Applicant</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <!-- Right Sidebar -->
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Total requests:</strong> <?php echo count($requests); ?></p>
                    <p class="mb-1"><strong>Pending:</strong> <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='applied'; })); ?></p>
                    <p class="mb-1"><strong>Approved:</strong> <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='approved'; })); ?></p>
                    <p class="mb-0"><strong>Denied:</strong> <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='denied'; })); ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Approve only after contacting the applicant and verifying details. Use messages to coordinate meetups.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>