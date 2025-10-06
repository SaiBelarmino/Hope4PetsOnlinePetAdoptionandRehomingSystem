
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: my_shelter.php
 * Tables: shelters, pets, donations (aggregate), shelter_documents
 * Expected Variables:
 *  - $shelter, $stats => ['pets','donations','pending_docs']
 */
$pageTitle = 'My Shelter Profile';
$hasShelter = !empty($_SESSION['shelter_id']);
if (!$hasShelter) {
  header('Location: register_shelter.php');
  exit;
}
?>

<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-9">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($shelter['shelter_name'] ?? $pageTitle); ?> <?php if(!empty($shelter['verified_badge'])): ?><span class="badge bg-primary">✔</span><?php endif; ?></h3>
        <div class="d-flex gap-2">
          <a href="./upload_shelter_documents.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-file-upload"></i> Documents</a>
          <a href="./edit_shelter.php" class="btn btn-sm btn-primary"><i class="ti ti-edit"></i> Edit</a>
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-4"><div class="card h-100"><div class="card-body text-center p-3"><div class="small text-muted">Pets</div><h4 class="mb-0"><?php echo (int)($stats['pets'] ?? 0); ?></h4></div></div></div>
        <div class="col-4"><div class="card h-100"><div class="card-body text-center p-3"><div class="small text-muted">Donations</div><h4 class="mb-0">₱<?php echo number_format((float)($stats['donations'] ?? 0),2); ?></h4></div></div></div>
        <div class="col-4"><div class="card h-100"><div class="card-body text-center p-3"><div class="small text-muted">Pending Docs</div><h4 class="mb-0"><?php echo (int)($stats['pending_docs'] ?? 0); ?></h4></div></div></div>
      </div>
      <div class="card mb-3"><div class="card-body">
        <p class="small mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($shelter['address'] ?? '—'); ?></p>
        <p class="small mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($shelter['contact_number'] ?? '—'); ?></p>
        <p class="small mb-0"><strong>Since:</strong> <?php echo !empty($shelter['created_at'])? htmlspecialchars(date('M Y', strtotime($shelter['created_at']))):'—'; ?></p>
      </div></div>
      <div class="card"><div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Quick Actions</h6></div><div class="card-body d-flex flex-wrap gap-2">
        <a href="./pets.php?mine=1" class="btn btn-light border"><i class="ti ti-paw"></i> Manage Pets</a>
        <a href="./donate.php?shelter_id=<?php echo (int)($shelter['id'] ?? 0); ?>" class="btn btn-light border"><i class="ti ti-heart-handshake"></i> View Donations</a>
        <a href="./shelter_verification_status.php" class="btn btn-light border"><i class="ti ti-shield-check"></i> Verification</a>
      </div></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
