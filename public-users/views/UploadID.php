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
                <?php
                // Default document types for the Philippines. If $allowedTypes is provided by controller, use it instead.
                $ph_doc_types = [
                  'gov_id' => 'Government ID (Any)',
                  'philhealth' => 'PhilHealth ID',
                  'passport' => 'Passport',
                  'drivers_license' => 'Driver\'s License',
                  'postal_id' => 'Postal ID',
                  'sss_id' => 'SSS ID',
                  'gsis_id' => 'GSIS ID',
                  'tin_id' => 'TIN ID',
                  'prc_id' => 'PRC ID',
                  'voter_id' => 'Voter\'s ID',
                  'umid' => 'UMID',
                  'student_id' => 'Student ID',
                  'company_id' => 'Company/Office ID',
                  'barangay_id' => 'Barangay ID',
                  'birth_certificate' => 'Birth Certificate (PSA)',
                  'marriage_certificate' => 'Marriage Certificate (PSA)',
                  'proof_address' => 'Proof of Address (Utility Bill / Lease / Bank Statement)',
                  'police_clearance' => 'Police Clearance',
                  'national_id' => 'Philippine National ID',
                ];

                $use = [];
                if (!empty($allowedTypes) && is_array($allowedTypes)) {
                  // If controller provided allowedTypes, map keys to friendly labels when possible
                  foreach ($allowedTypes as $t) {
                    if (isset($ph_doc_types[$t])) $use[$t] = $ph_doc_types[$t];
                    else $use[$t] = ucwords(str_replace('_',' ', $t));
                  }
                } else {
                  $use = $ph_doc_types;
                }

                foreach ($use as $key => $label): ?>
                  <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">File</label>
              <div class="input-group">
                <!-- File input: keep existing name so backend stays the same. On mobile the capture attribute will open camera. -->
                <input type="file" id="documentInput" name="document" class="form-control" required accept="image/*,application/pdf" capture="environment">
                <button type="button" id="openCameraBtn" class="btn btn-outline-secondary" title="Take photo with camera"><i class="ti ti-camera"></i> Camera</button>
              </div>
              <small class="text-muted">Accepted: JPG, PNG, PDF. Max 5MB. You can take a live photo using your camera (mobile or desktop).</small>

              <div id="previewContainer" class="mt-2" style="display:none;">
                <img id="previewImage" src="" alt="Preview" class="img-fluid border" />
              </div>

              <!-- Simple camera UI (hidden until requested) -->
              <div id="cameraModal" class="mt-3" style="display:none;">
                <div class="card">
                  <div class="card-body">
                    <video id="cameraVideo" autoplay playsinline style="width:100%;height:auto;background:#000;border-radius:4px;"></video>
                    <div class="d-flex gap-2 mt-2">
                      <button type="button" id="captureBtn" class="btn btn-primary">Capture</button>
                      <button type="button" id="closeCameraBtn" class="btn btn-outline-secondary">Close</button>
                    </div>
                    <small class="d-block text-muted mt-2">Make sure your camera permission is allowed. If no camera is available, use the file chooser.</small>
                  </div>
                </div>
              </div>
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
<script>
// Camera capture support: opens camera on desktop via getUserMedia, or triggers mobile camera via input capture attribute.
;(function(){
  const openCameraBtn = document.getElementById('openCameraBtn');
  const cameraModal = document.getElementById('cameraModal');
  const cameraVideo = document.getElementById('cameraVideo');
  const captureBtn = document.getElementById('captureBtn');
  const closeCameraBtn = document.getElementById('closeCameraBtn');
  const documentInput = document.getElementById('documentInput');
  const previewContainer = document.getElementById('previewContainer');
  const previewImage = document.getElementById('previewImage');

  let stream = null;

  function stopStream(){
    if(stream){
      stream.getTracks().forEach(t=>t.stop());
      stream = null;
    }
  }

  // If browser doesn't support getUserMedia, hide the camera button (mobile will still open native camera via capture attr).
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    // still keep button so mobile users can use input capture, but disable desktop usage
    openCameraBtn.addEventListener('click', ()=> documentInput.click());
    return;
  }

  openCameraBtn.addEventListener('click', async function(){
    // Show camera UI and request camera
    cameraModal.style.display = 'block';
    try{
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
      cameraVideo.srcObject = stream;
      await cameraVideo.play();
    }catch(err){
      // fallback: open file chooser if permission denied or no camera
      console.error('Camera error', err);
      stopStream();
      cameraModal.style.display = 'none';
      documentInput.click();
    }
  });

  closeCameraBtn.addEventListener('click', function(){
    stopStream();
    cameraModal.style.display = 'none';
  });

  captureBtn.addEventListener('click', function(){
    if(!stream) return;
    const videoTrack = stream.getVideoTracks()[0];
    const settings = videoTrack.getSettings ? videoTrack.getSettings() : {};

    const w = cameraVideo.videoWidth || settings.width || 1280;
    const h = cameraVideo.videoHeight || settings.height || 720;
    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(cameraVideo, 0, 0, w, h);

    canvas.toBlob(function(blob){
      if(!blob) return;
      // Create a File from the Blob to attach to the file input
      const file = new File([blob], 'capture.jpg', { type: blob.type || 'image/jpeg' });

      // Create a DataTransfer to populate the input.files (works in modern browsers)
      const dt = new DataTransfer();
      dt.items.add(file);
      documentInput.files = dt.files;

      // Update preview
      const url = URL.createObjectURL(file);
      previewImage.src = url;
      previewContainer.style.display = 'block';

      // Close camera
      stopStream();
      cameraModal.style.display = 'none';
    }, 'image/jpeg', 0.92);
  });

  // When a user selects a file via input (including mobile camera), show preview
  documentInput.addEventListener('change', function(){
    const f = this.files && this.files[0];
    if(!f) return;
    if(f.type.startsWith('image/')){
      previewImage.src = URL.createObjectURL(f);
      previewContainer.style.display = 'block';
    } else {
      previewContainer.style.display = 'none';
    }
  });
})();
</script>
