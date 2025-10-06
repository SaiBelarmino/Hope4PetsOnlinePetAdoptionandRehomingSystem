<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: donation_receipt.php
 * Related Table: donations
 * Expected Variables:
 *  - $donation => ['transaction_id','amount','payment_method','status','created_at','donor_name','shelter_name']
 *  - $user (current)
 *  - $orgInfo (optional) => display organization header / logo
 * If $donation is missing, controller should redirect or set an error message.
 */
$pageTitle = 'Donation Receipt';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Navigation</h6>
          <div class="d-grid gap-2">
            <a href="./my_donations.php" class="btn btn-sm btn-light border">My Donations</a>
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate Again</a>
            <a href="./shelters.php" class="btn btn-sm btn-light border">Shelters</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <div>
          <a href="./donate.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
          <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="ti ti-printer"></i> Print</button>
        </div>
      </div>
      <div class="card shadow-sm">
        <div class="card-body">
      <?php if (empty($donation)): ?>
        <div class="alert alert-danger mb-0">Donation not found.</div>
      <?php else: ?>
        <div class="row mb-3">
          <div class="col-sm-6">
            <h5 class="mb-1">Hope4Pets Donation Receipt</h5>
            <small class="text-muted">Official Acknowledgment of Contribution</small>
          </div>
          <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <strong>Date:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($donation['created_at']))); ?><br>
            <strong>Txn ID:</strong> <?php echo htmlspecialchars($donation['transaction_id']); ?>
          </div>
        </div>
        <hr>
        <div class="row mb-4">
          <div class="col-md-6">
            <h6 class="text-uppercase text-muted">Donor</h6>
            <p class="mb-0">
              <?php echo htmlspecialchars($donation['donor_name'] ?: ($user['full_name'] ?? 'Anonymous')); ?><br>
              <span class="text-muted small"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
            </p>
          </div>
          <div class="col-md-6 mt-3 mt-md-0">
            <h6 class="text-uppercase text-muted">Beneficiary Shelter</h6>
            <p class="mb-0">
              <?php echo htmlspecialchars($donation['shelter_name'] ?? 'General Fund'); ?><br>
              <span class="text-muted small">Thank you for supporting animal welfare.</span>
            </p>
          </div>
        </div>
        <div class="table-responsive mb-4">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Description</th>
                <th class="text-end" style="width:180px;">Amount (PHP)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Donation via <?php echo htmlspecialchars(ucwords(str_replace('_',' ',$donation['payment_method']))); ?></td>
                <td class="text-end">₱<?php echo number_format((float)$donation['amount'],2); ?></td>
              </tr>
              <tr>
                <th class="text-end">Total</th>
                <th class="text-end">₱<?php echo number_format((float)$donation['amount'],2); ?></th>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="row mb-4">
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="p-3 bg-light rounded h-100">
              <div class="text-muted small">Payment Method</div>
              <strong><?php echo htmlspecialchars(ucwords(str_replace('_',' ',$donation['payment_method']))); ?></strong>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="p-3 bg-light rounded h-100">
              <div class="text-muted small">Status</div>
              <?php 
                $statusClass = [ 'pending'=>'warning','completed'=>'success','failed'=>'danger','refunded'=>'secondary'][$donation['status']] ?? 'light';
              ?>
              <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($donation['status'])); ?></span>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="p-3 bg-light rounded h-100">
              <div class="text-muted small">Reference</div>
              <code class="d-block small"><?php echo htmlspecialchars($donation['transaction_id']); ?></code>
            </div>
          </div>
        </div>
        <p class="small text-muted mb-0">This electronic receipt acknowledges your voluntary contribution. No goods or services were provided in exchange. Please retain for your records.</p>
      <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Next Steps</h6>
          <p class="small text-muted mb-2">Share your support and encourage others to donate.</p>
          <div class="d-grid gap-2">
            <a href="./my_donations.php" class="btn btn-sm btn-outline-primary">View History</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Reminder</h6>
          <p class="small text-muted mb-0">Keep this receipt for your personal records.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
