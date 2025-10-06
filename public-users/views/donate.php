<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: donate.php
 * Related DB Tables: donations, shelters, users
 * Expected Variables (set by controller before include):
 *  - $shelters: array of shelters => [ ['id'=>int,'shelter_name'=>string,'verified_badge'=>int] , ... ]
 *  - $user (current session user) => ['id'=>int,'full_name'=>string,'email'=>string]
 *  - $recentDonations (optional) => [ ['transaction_id'=>string,'shelter_name'=>string|null,'amount'=>float,'payment_method'=>string,'status'=>string,'created_at'=>string ] , ... ]
 *  - $flash (optional) => ['type'=>'success|danger|warning','message'=>string]
 * Notes:
 *  donations.status ENUM('pending','completed','failed','refunded')
 */
 $pageTitle = 'Donate';
 $hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar: Shortcuts -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Explore</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-search me-1"></i> Browse Pets</a>
            <a href="./shelters.php" class="btn btn-outline-primary btn-sm"><i class="ti ti-building-community me-1"></i> Find Shelters</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
      </div>
      <?php if (!empty($flash['message'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?>">
          <?php echo htmlspecialchars($flash['message']); ?>
        </div>
      <?php endif; ?>
      <div class="row g-3">
        <div class="col-12 col-xl-5">
          <div class="card h-100">
            <div class="card-header bg-white pb-0 border-0">
              <h6 class="card-title mb-0">Make a Donation</h6>
            </div>
            <div class="card-body">
              <form method="post" action="../controllers/donate-controller.php" id="donationForm">
            <div class="mb-3">
              <label for="shelter_id" class="form-label">Shelter (optional)</label>
              <select class="form-select" name="shelter_id" id="shelter_id">
                <option value="">-- General Fund / Any Shelter --</option>
                <?php if (!empty($shelters)): foreach ($shelters as $s): ?>
                  <option value="<?php echo (int)$s['id']; ?>">
                    <?php echo htmlspecialchars($s['shelter_name']); ?><?php if (!empty($s['verified_badge'])) echo ' ✔'; ?>
                  </option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="amount" class="form-label">Amount (PHP)</label>
              <input type="number" step="0.01" min="1" class="form-control" name="amount" id="amount" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <div class="d-flex flex-wrap gap-2">
                <?php $methods = ['gcash'=>'GCash','paymaya'=>'Maya','credit_card'=>'Credit Card','paypal'=>'PayPal','bank_transfer'=>'Bank Transfer']; ?>
                <?php foreach ($methods as $val=>$label): ?>
                  <div class="form-check me-3">
                    <input class="form-check-input" type="radio" name="payment_method" id="pm_<?php echo $val; ?>" value="<?php echo $val; ?>" required>
                    <label for="pm_<?php echo $val; ?>" class="form-check-label"><?php echo htmlspecialchars($label); ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="mb-3">
              <label for="donor_name" class="form-label">Display Name (optional)</label>
              <input type="text" class="form-control" name="donor_name" id="donor_name" placeholder="Anonymous or your name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary"><i class="ti ti-heart-handshake me-1"></i> Donate</button>
            </div>
            <small class="text-muted d-block mt-2">By donating you agree funds are used to support partner shelters and rescue operations.</small>
              </form>
            </div>
          </div>
        </div>
        <div class="col-12 col-xl-7">
          <div class="card h-100">
            <div class="card-header bg-white pb-0 border-0 d-flex justify-content-between align-items-center">
              <h6 class="card-title mb-0">Recent Donations</h6>
              <a href="./my_donations.php" class="small">View all</a>
            </div>
            <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col">Txn ID</th>
                  <th scope="col">Shelter</th>
                  <th scope="col" class="text-end">Amount</th>
                  <th scope="col">Method</th>
                  <th scope="col">Status</th>
                  <th scope="col">Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($recentDonations)): foreach ($recentDonations as $d): ?>
                  <tr>
                    <td><a href="./donation_receipt.php?tx=<?php echo urlencode($d['transaction_id']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($d['transaction_id']); ?></a></td>
                    <td><?php echo htmlspecialchars($d['shelter_name'] ?? '—'); ?></td>
                    <td class="text-end">₱<?php echo number_format((float)$d['amount'],2); ?></td>
                    <td><span class="badge bg-light text-dark text-uppercase"><?php echo htmlspecialchars($d['payment_method']); ?></span></td>
                    <td><?php
                      $statusClass = [
                        'pending'=>'warning',
                        'completed'=>'success',
                        'failed'=>'danger',
                        'refunded'=>'secondary'
                      ][$d['status']] ?? 'light';
                      ?>
                      <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span></td>
                    <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d, Y', strtotime($d['created_at']))); ?></span></td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No donations yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="text-muted mb-2">Trending</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="./community.php" class="btn btn-sm btn-light border">#adoptDontShop</a>
            <a href="./community.php" class="btn btn-sm btn-light border">#rescue</a>
            <a href="./community.php" class="btn btn-sm btn-light border">#pets</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="mb-2">Need Help?</h6>
          <p class="small text-muted mb-2">Contact support for donation issues.</p>
          <a href="./messages.php" class="btn btn-sm btn-outline-primary w-100">Message Support</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
