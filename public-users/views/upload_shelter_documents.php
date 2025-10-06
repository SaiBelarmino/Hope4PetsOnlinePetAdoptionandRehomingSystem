<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: upload_shelter_documents.php
 * Table: shelter_documents
 * Expected Variables:
 *  - $documents => existing docs [ {'id','doc_type','file_path','status','uploaded_at','reviewed_at'}, ... ]
 *  - $requiredTypes => list of required types
 */
$pageTitle = 'Upload Shelter Documents';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>

<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-9">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./shelter_verification_status.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Status</a>
      </div>
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card h-100">
            <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Upload Document</h6></div>
            <div class="card-body">
              <form action="../controllers/upload-shelter-documents-controller.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Document Type</label>
                  <select name="doc_type" class="form-select" required>
                    <option value="">Select</option>
                    <?php
                      // Default to common Philippine business documents (non-ID)
                      $defaultBusinessDocs = [
                        'dtiregistration',
                        'mayors_permit',
                        'bir_registration',
                        'business_permit',
                        'articles_of_incorporation',
                        'barangay_clearance',
                        'contract_of_lease',
                        'other_business_documents'
                      ];
                    ?>
                    <?php foreach(($requiredTypes ?? $defaultBusinessDocs) as $t): ?>
                      <option value="<?php echo htmlspecialchars($t); ?>"><?php echo strtoupper(str_replace('_',' ', $t)); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-muted d-block mt-1">Select the type of Philippine business document you're uploading (business registrations, permits, clearances). Do not upload personal IDs here.</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">File</label>
                  <div class="input-group mb-2">
                    <input type="file" name="document" id="documentInput" class="form-control" required accept="image/*,application/pdf">
                    <button type="button" class="btn btn-outline-secondary" onclick="showCameraModal()"><i class="ti ti-camera"></i> Take Photo</button>
                  </div>
                  <small class="text-muted">Max 5MB. JPG, PNG, PDF. Upload scanned copies or photos of Philippine business documents (DTI/BIR, Mayor's Permit, Barangay Clearance, etc.). You can also use your camera to take a photo.</small>
                  <!-- Camera Modal -->
                  <div id="cameraModal" style="display:none; position:fixed; z-index:1050; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:20px; border-radius:8px; max-width:95vw; max-height:95vh; position:relative;">
                      <video id="cameraPreview" autoplay playsinline style="width:100%; max-width:350px; border-radius:8px;"></video>
                      <canvas id="cameraCanvas" style="display:none;"></canvas>
                      <div class="mt-2 d-flex justify-content-between">
                        <button type="button" class="btn btn-primary" onclick="capturePhoto()"><i class="ti ti-camera"></i> Capture</button>
                        <button type="button" class="btn btn-secondary" onclick="closeCameraModal()">Cancel</button>
                      </div>
                    </div>
                  </div>
                  <script>
                  let cameraStream = null;
                  function showCameraModal() {
                    const modal = document.getElementById('cameraModal');
                    const video = document.getElementById('cameraPreview');
                    modal.style.display = 'flex';
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                      .then(function(stream) {
                        cameraStream = stream;
                        video.srcObject = stream;
                      })
                      .catch(function(err) {
                        alert('Unable to access camera: ' + err);
                        closeCameraModal();
                      });
                  }
                  function closeCameraModal() {
                    const modal = document.getElementById('cameraModal');
                    modal.style.display = 'none';
                    const video = document.getElementById('cameraPreview');
                    if (cameraStream) {
                      cameraStream.getTracks().forEach(track => track.stop());
                      cameraStream = null;
                    }
                    video.srcObject = null;
                  }
                  function capturePhoto() {
                    const video = document.getElementById('cameraPreview');
                    const canvas = document.getElementById('cameraCanvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    canvas.toBlob(function(blob) {
                      // Create a file from the blob and set it to the file input
                      const file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });
                      const dataTransfer = new DataTransfer();
                      dataTransfer.items.add(file);
                      document.getElementById('documentInput').files = dataTransfer.files;
                      closeCameraModal();
                    }, 'image/jpeg', 0.95);
                  }
                  </script>
                </div>
                <button class="btn btn-primary w-100"><i class="ti ti-upload"></i> Upload</button>
              </form>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card h-100">
            <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Submitted Documents</h6></div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light"><tr><th>Type</th><th>Status</th><th>Uploaded</th><th>Reviewed</th><th></th></tr></thead>
                  <tbody>
                    <?php if(empty($documents)): ?><tr><td colspan="5" class="text-center text-muted py-4">No documents uploaded.</td></tr><?php else: foreach($documents as $d): ?>
                      <tr id="<?php echo htmlspecialchars($d['doc_type']); ?>">
                        <td><?php echo strtoupper(str_replace('_',' ', htmlspecialchars($d['doc_type']))); ?></td>
                        <td><span class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$d['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span></td>
                        <td><span class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($d['uploaded_at']))); ?></span></td>
                        <td><span class="small text-muted"><?php echo $d['reviewed_at']? htmlspecialchars(date('M d', strtotime($d['reviewed_at']))):'—'; ?></span></td>
                        <td class="text-end"><a href="<?php echo htmlspecialchars($d['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">View</a></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
