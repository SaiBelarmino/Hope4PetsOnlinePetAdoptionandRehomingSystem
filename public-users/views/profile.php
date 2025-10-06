<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: profile.php
 * Table: users, pets (counts), shelters (if user has), donations/adoptions aggregates optional
 * Expected Variables:
 *  - $user => ['id','full_name','birthday','gender','email','profile_photo','location','contact_number','is_verified','created_at']
 *  - $stats => ['pets'=>int,'adoptions'=>int,'donations'=>int]
 */
$pageTitle = 'Profile';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <div class="d-flex gap-2">
      <a href="./edit_profile.php" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i> Edit</a>
      <a href="./settings.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-settings"></i> Settings</a>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <div class="mb-3">
            <div class="ratio ratio-1x1 rounded-circle overflow-hidden bg-light mx-auto" style="width:140px;">
              <?php if(!empty($user['profile_photo'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" class="object-fit-cover" alt="Profile">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-muted">No Photo</div>
              <?php endif; ?>
            </div>
          </div>
          <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?> <?php if(!empty($user['is_verified'])): ?><span class="badge bg-primary">✔</span><?php endif; ?></h5>
          <p class="text-muted small mb-2">Member since <?php echo htmlspecialchars(date('M Y', strtotime($user['created_at'] ?? 'now'))); ?></p>
          <div class="d-flex flex-wrap justify-content-center gap-2 small mb-3">
            <span class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($user['gender'] ?? '')); ?></span>
            <?php if(!empty($user['location'])): ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($user['location']); ?></span><?php endif; ?>
          </div>
          <p class="small mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
          <p class="small mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact_number'] ?? '—'); ?></p>
          <p class="small mb-0"><strong>Birthday:</strong> <?php echo !empty($user['birthday'])? htmlspecialchars(date('M d, Y', strtotime($user['birthday']))):'—'; ?></p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8">
      <div class="row g-3 mb-1">
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <div class="small text-muted text-uppercase">Pets</div>
            <h4 class="mb-0"><?php echo (int)($stats['pets'] ?? 0); ?></h4>
          </div></div>
        </div>
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <div class="small text-muted text-uppercase">Adoptions</div>
            <h4 class="mb-0"><?php echo (int)($stats['adoptions'] ?? 0); ?></h4>
          </div></div>
        </div>
        <div class="col-4">
          <div class="card h-100"><div class="card-body text-center p-3">
            <div class="small text-muted text-uppercase">Donations</div>
            <h4 class="mb-0">₱<?php echo number_format((float)($stats['donations'] ?? 0),2); ?></h4>
          </div></div>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Activity Overview</h6></div>
        <div class="card-body">
          <p class="small text-muted mb-0">Recent actions, adoption progress, donation highlights could be summarized here.</p>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
