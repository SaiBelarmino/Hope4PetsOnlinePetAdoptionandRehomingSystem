<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: upload_id.php
 * Table: user_documents
 * Expected Variables:
 *  - $documents => [ {'id','doc_type','file_path','status','uploaded_at','reviewed_at'}, ... ]
 *  - $allowedTypes => ['gov_id','proof_address','selfie'] (example)
 */
$pageTitle = 'Upload ID';
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
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./profile.php" class="btn btn-sm btn-outline-secondary">Profile</a>
            <a href="./settings.php" class="btn btn-sm btn-outline-secondary">Settings</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./profile.php" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
      </div>
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Submit Document</h6></div>
        <div class="card-body">
          <form action="../controllers/upload-id-controller.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Document Type</label>
              <select name="doc_type" class="form-select" required>
                <option value="">Select type</option>
                <?php foreach (($allowedTypes ?? ['gov_id','proof_address','selfie']) as $t): ?>
                  <option value="<?php echo htmlspecialchars($t); ?>"><?php echo strtoupper(str_replace('_',' ', $t)); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">File</label>
              <input type="file" name="document" class="form-control" required accept="image/*,application/pdf">
              <small class="text-muted">Accepted: JPG, PNG, PDF. Max 5MB.</small>
            </div>
            <button class="btn btn-primary w-100"><i class="ti ti-upload"></i> Upload</button>
            <small class="d-block mt-2 text-muted">Ensure details are clear and readable.</small>
          </form>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Submitted Documents</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Uploaded</th>
                  <th>Reviewed</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($documents)): ?>
                  <tr><td colspan="5" class="text-center text-muted py-4">No documents submitted.</td></tr>
                <?php else: foreach ($documents as $d): ?>
                  <tr>
                    <td><?php echo strtoupper(str_replace('_',' ', htmlspecialchars($d['doc_type']))); ?></td>
                    <td><span class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$d['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span></td>
                    <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($d['uploaded_at']))); ?></span></td>
                    <td><span class="small text-muted"><?php echo !empty($d['reviewed_at'])? htmlspecialchars(date('M d', strtotime($d['reviewed_at']))):'—'; ?></span></td>
                    <td class="text-end"><a href="<?php echo htmlspecialchars($d['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a></td>
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
          <h6 class="mb-2">Tips</h6>
          <p class="small text-muted mb-0">Upload clear scans. Blurry or cropped documents may be rejected.</p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./profile.php" class="btn btn-sm btn-light border">Profile</a>
            <a href="./settings.php" class="btn btn-sm btn-light border">Settings</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
