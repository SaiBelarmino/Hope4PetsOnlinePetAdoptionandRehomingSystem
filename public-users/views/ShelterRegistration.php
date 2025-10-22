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
<link rel="stylesheet" href="assets/css/leaflet.css" />
<?php
// Get flash message (if any) and load shelter using the controller
require_once __DIR__ . '/../controllers/ShelterManagementController.php';

// Ensure the class exists before using it
if (!class_exists('ShelterManagementController')) {
    require_once __DIR__ . '/../controllers/ShelterManagementController.php';
}

// Try to use SessionManager if available for flash; otherwise use $_SESSION fallback
$flash = [];
if (class_exists('SessionManager') && method_exists('SessionManager','getFlash')) {
    try { $flash = SessionManager::getFlash() ?? []; } catch(Exception $e) { $flash = []; }
}
if (empty($flash) && !empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Load shelter for current user (if logged in)
$userId = $_SESSION['user']['id'] ?? null;
$shelter = null;
$authHasShelter = false;

if ($userId) {
    // Prefer the explicit controller method if available
    if (class_exists('ShelterManagementController') && method_exists('ShelterManagementController', 'getShelterByUser')) {
        try {
            $shelter = call_user_func(['ShelterManagementController', 'getShelterByUser'], $userId);
        } catch (Throwable $e) {
            // If the method exists but throws, fall back to null
            $shelter = null;
        }
    } else {
        // Attempt a graceful fallback if the controller exposes a differently named method
        if (class_exists('ShelterManagementController') && method_exists('ShelterManagementController', 'getByUserId')) {
            try {
                $shelter = call_user_func(['ShelterManagementController', 'getByUserId'], $userId);
            } catch (Throwable $e) {
                $shelter = null;
            }
        } else {
            // No suitable controller method available; leave $shelter as null
            $shelter = null;
        }
    }

    $authHasShelter = !empty($shelter);
}
?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
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
          <?php if (!empty($flash['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type'] ?? 'info'); ?> alert-dismissible fade show" id="autoHideAlert">
              <?php echo htmlspecialchars($flash['message']); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>
              // Auto-hide after 4s
              setTimeout(function(){ var a = document.getElementById('autoHideAlert'); if(a){ a.classList.remove('show'); a.classList.add('hide'); } }, 4000);
            </script>
          <?php endif; ?>

          <?php if ($authHasShelter): ?>
            <div class="alert alert-info">You already have a registered shelter. You can manage it from your profile or upload verification documents.</div>
            <div class="d-flex justify-content-end">
              <a href="./upload_shelter_documents.php" class="btn btn-secondary btn-sm me-2">Upload Documents</a>
              <a href="./profile.php" class="btn btn-outline-secondary btn-sm">Go to Profile</a>
            </div>
          <?php else: ?>
            <form action="../controllers/ShelterRegistrationController.php" method="post" class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Shelter Name</label>
                <input type="text" name="shelter_name" class="form-control" required maxlength="255" placeholder="e.g. Happy Paws Shelter">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" required maxlength="50" placeholder="e.g. +63 912 345 6789">
              </div>
              <div class="col-12">
                <label class="form-label">Address</label>
                <input type="hidden" name="address" id="address">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control mb-2" name="shelter_unit" placeholder="Shelter/Unit Name (e.g., 2nd Floor)">
                        <input type="text" class="form-control mb-2" name="purok_subdivision" placeholder="Purok/Subdivision">
                        <input type="text" class="form-control mb-2" name="barangay" placeholder="Barangay" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control mb-2" name="city" placeholder="City" required>
                        <input type="text" class="form-control mb-2" name="province" placeholder="Province" required>
                        <input type="text" class="form-control mb-2" name="postal_code" placeholder="Postal Code">
                    </div>
                </div>
                <small class="text-muted">Location is used for accurate place name via geolocation.</small>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" id="getLocationBtn">Get Current Location</button>
                </div>
                <div id="locationError" class="alert alert-warning mt-2" style="display: none;"></div>
                <div id="map" style="height: 300px; margin-top: 10px; display: none;"></div>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="agree" required>
                  <label class="form-check-label" for="agree">I confirm the information provided is accurate.</label>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="ti ti-building-community"></i> Register Shelter</button>
              </div>
            </form>
          <?php endif; ?>
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
 </div>
</div>
<script src="assets/js/leaflet.js"></script>
<script src="assets/js/geolocation.js"></script>
<script>
document.querySelector('form').addEventListener('submit', function() {
    const fields = ['shelter_unit', 'purok_subdivision', 'barangay', 'city', 'province', 'postal_code'];
    const addressParts = fields.map(id => document.querySelector(`[name="${id}"]`).value.trim()).filter(val => val);
    document.getElementById('address').value = addressParts.join(', ');
});
</script>
<?php include __DIR__ . '/../include/footer.php'; ?>
