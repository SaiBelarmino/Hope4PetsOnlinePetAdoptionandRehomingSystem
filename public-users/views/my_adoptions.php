<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: my_adoptions.php
 * Table: adoptions, pets
 * Expected Variables:
 *  - $applications => [ {'id','pet_id','pet_name','status','created_at','updated_at'}, ... ]
 *  - $summary => optional counts per status ['applied'=>n,'approved'=>n,'completed'=>n]
 */
$pageTitle = 'My Adoptions';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./pets.php" class="btn btn-sm btn-outline-primary"><i class="ti ti-search"></i> Find Pets</a>
      </div>
      <div class="row g-3 mb-3">
        <?php $statuses = ['applied','approved','completed','denied','cancelled']; foreach($statuses as $st): $count = (int)($summary[$st] ?? 0); ?>
          <div class="col-6 col-sm-4 col-md-4 col-xl-2">
            <div class="card h-100">
              <div class="card-body p-2 text-center">
                <div class="small text-muted text-uppercase mb-1"><?php echo htmlspecialchars($st); ?></div>
                <h5 class="mb-0 fw-semibold"><?php echo $count; ?></h5>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap">
          <h6 class="mb-0">Applications</h6>
          <form method="get" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select form-select-sm">
              <option value="">All Status</option>
              <?php foreach ($statuses as $st): ?>
                <option value="<?php echo $st; ?>" <?php if(($_GET['status'] ?? '')===$st) echo 'selected'; ?>><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-outline-secondary"><i class="ti ti-filter"></i></button>
          </form>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Pet</th>
                  <th>Status</th>
                  <th>Applied</th>
                  <th>Updated</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($applications)): ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No adoption applications.</td></tr>
                <?php else: foreach ($applications as $a): ?>
                  <tr>
                    <td><a href="./pet_view.php?id=<?php echo (int)$a['pet_id']; ?>" class="text-decoration-none fw-semibold"><?php echo htmlspecialchars($a['pet_name']); ?></a></td>
                    <td><span class="badge bg-<?php echo ['applied'=>'info','approved'=>'success','denied'=>'danger','completed'=>'secondary','cancelled'=>'dark'][$a['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($a['status'])); ?></span></td>
                    <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($a['created_at']))); ?></span></td>
                    <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($a['updated_at'] ?? $a['created_at']))); ?></span></td>
                    <td class="text-end"><a href="./adoption_status.php?id=<?php echo (int)$a['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Need Help?</h6>
          <p class="small text-muted mb-2">Check each application's status regularly. Approved? Complete adoption promptly.</p>
          <a href="./messages.php" class="btn btn-sm btn-outline-primary w-100">Message Support</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-light border">Find Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
