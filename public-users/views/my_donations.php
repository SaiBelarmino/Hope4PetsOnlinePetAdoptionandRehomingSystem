<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: my_donations.php
 * Tables: donations, shelters
 * Expected Variables:
 *  - $donations => list of user's donations [ ['transaction_id','shelter_name','amount','payment_method','status','created_at'] , ...]
 *  - $user => current user
 *  - $summary (optional) => ['total_amount'=>float,'count'=>int]
 */
$pageTitle = 'My Donations';
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
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Pets</a>
            <a href="./shelters.php" class="btn btn-sm btn-outline-secondary">Shelters</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <div class="d-flex gap-2">
          <a href="./donate.php" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> New Donation</a>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <div class="row text-center g-3">
            <div class="col-6 col-md-4">
              <div class="p-2 bg-light rounded small">
                <div class="text-muted">Total (₱)</div>
                <h5 class="mb-0"><?php echo number_format((float)($summary['total_amount'] ?? 0),2); ?></h5>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="p-2 bg-light rounded small">
                <div class="text-muted">Transactions</div>
                <h5 class="mb-0"><?php echo (int)($summary['count'] ?? count($donations ?? [])); ?></h5>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="p-2 bg-light rounded small">
                <div class="text-muted">Completed</div>
                <h5 class="mb-0"><?php echo (int)($summary['completed'] ?? 0); ?></h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0 d-flex flex-wrap justify-content-between align-items-center">
          <h6 class="mb-0">Donation History</h6>
          <form class="d-flex align-items-center gap-2" method="get" action="">
            <input type="text" class="form-control form-control-sm" name="q" placeholder="Search txn or shelter" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" />
            <select name="status" class="form-select form-select-sm">
              <option value="">All Status</option>
              <?php foreach (['pending','completed','failed','refunded'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php if(($_GET['status'] ?? '')===$st) echo 'selected'; ?>><?php echo ucfirst($st); ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-outline-secondary"><i class="ti ti-filter"></i></button>
          </form>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover align-middle table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th>Txn ID</th>
                  <th>Shelter</th>
                  <th class="text-end">Amount</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($donations)): foreach ($donations as $d): ?>
                  <tr>
                    <td><a class="text-decoration-none" href="./donation_receipt.php?tx=<?php echo urlencode($d['transaction_id']); ?>"><?php echo htmlspecialchars(substr($d['transaction_id'],0,12)); ?></a></td>
                    <td><?php echo htmlspecialchars($d['shelter_name'] ?? '—'); ?></td>
                    <td class="text-end">₱<?php echo number_format((float)$d['amount'],2); ?></td>
                    <td><span class="badge bg-light text-dark text-uppercase"><?php echo htmlspecialchars($d['payment_method']); ?></span></td>
                    <td><?php $cls=['pending'=>'warning','completed'=>'success','failed'=>'danger','refunded'=>'secondary'][$d['status']]??'light'; ?>
                      <span class="badge bg-<?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span></td>
                    <td><span class="small text-muted"><?php echo htmlspecialchars(date('Y-m-d', strtotime($d['created_at']))); ?></span></td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No donations found.</td></tr>
                <?php endif; ?>
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
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate</a>
            <a href="./shelters.php" class="btn btn-sm btn-light border">Shelters</a>
            <a href="./pets.php" class="btn btn-sm btn-light border">Pets</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2">Tips</h6>
          <p class="small text-muted mb-0">Keep receipts for your records. Completed donations appear within minutes.</p>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
