<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: register_shelter.php
 * Table: shelters (create)
 */
$pageTitle = 'Register as Shelter';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Why Register?</h6>
          <p class="small text-muted mb-0">Gain visibility, receive donations, and manage pets easily.</p>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./shelters.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> All Shelters</a>
      </div>
      <div class="card">
        <div class="card-body">
          <form action="../controllers/register-shelter-controller.php" method="post" class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Shelter Name</label>
              <input type="text" name="shelter_name" class="form-control" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact_number" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control" required>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agree" required>
                <label class="form-check-label" for="agree">I confirm the information provided is accurate.</label>
              </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary"><i class="ti ti-building-community"></i> Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">After Registration</h6>
          <p class="small text-muted mb-0">Upload required documents to get verified faster.</p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./shelters.php" class="btn btn-sm btn-light border">Shelters</a>
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
