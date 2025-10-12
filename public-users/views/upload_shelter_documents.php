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
// Load existing documents for this shelter so they show in Submitted Documents
$documents = [];
if ($hasShelter) {
    // Use direct DB connection instead of protected BaseController::fetchAll()
    $shelterId = $_SESSION['shelter_id'] ?? $_SESSION['user']['shelter_id'] ?? null;
    if ($shelterId) {
        require_once __DIR__ . '/../../config/db-connection/db_connection.php';
        if (isset($conn) && $conn instanceof mysqli) {
            $stmt = $conn->prepare("SELECT id, shelter_id, doc_type, file_path, status, uploaded_at, reviewed_at FROM shelter_documents WHERE shelter_id = ? ORDER BY uploaded_at DESC");
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $res = $stmt->get_result();
                $documents = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
                $stmt->close();
            }
        }
    }
}
?>

<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
    <div class="container-fluid py-3">
        <div class="row g-3">
            <!-- Left Sidebar -->
            <div class="col-12 col-lg-3">
                <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
            </div>
            <!-- Center Content -->
            <div class="col-12 col-lg-9">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
                    <a href="./shelter_verification_status.php" class="btn btn-outline-secondary btn-sm"><i
                            class="ti ti-arrow-left"></i> Status</a>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">Upload Document</h6>
                            </div>
                            <div class="card-body">
                                <form action="../controllers/upload-shelter-documents-controller.php" method="post"
                                    enctype="multipart/form-data">
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
                                            <option value="<?php echo htmlspecialchars($t); ?>">
                                                <?php echo strtoupper(str_replace('_',' ', $t)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted d-block mt-1">Select the type of Philippine business
                                            document you're uploading (business registrations, permits, clearances). Do
                                            not upload personal IDs here.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">File</label>
                                        <div class="input-group mb-2">
                                            <input type="file" name="documents[]" id="documentInput"
                                                class="form-control" required accept="image/*,application/pdf" multiple>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="showCameraModal()"><i class="ti ti-camera"></i> Take
                                                Photo</button>
                                        </div>
                                        <div id="selectedFilesInfo" class="mt-2 small text-muted">No files selected.
                                            (Select up to 3 files. Exactly 3 required to upload.)</div>
                                        <small class="text-muted">Max 5MB per file. JPG, PNG, PDF. Upload scanned copies
                                            or photos of Philippine business documents (DTI/BIR, Mayor's Permit,
                                            Barangay Clearance, etc.). You can also use your camera to take a
                                            photo.</small>
                                        <!-- Camera Modal -->
                                        <div id="cameraModal"
                                            style="display:none; position:fixed; z-index:1050; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
                                            <div
                                                style="background:#fff; padding:20px; border-radius:8px; max-width:95vw; max-height:95vh; position:relative;">
                                                <video id="cameraPreview" autoplay playsinline
                                                    style="width:100%; max-width:350px; border-radius:8px;"></video>
                                                <canvas id="cameraCanvas" style="display:none;"></canvas>
                                                <div class="mt-2 d-flex justify-content-between">
                                                    <button type="button" class="btn btn-primary"
                                                        onclick="capturePhoto()"><i class="ti ti-camera"></i>
                                                        Capture</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="closeCameraModal()">Cancel</button>
                                                </div>
                                            </div>
                                        </div>



                                        <!-- JS to handle file selection, preview, camera capture -->
                                        <script>
                                        let cameraStream = null;
                                        // Internal array to manage selected files (so we can append from camera)
                                        const maxFiles = 3;
                                        const maxSizeBytes = 5 * 1024 * 1024; // 5MB
                                        // selectedFiles will store objects: { file: File, docType: string }
                                        let selectedFiles = [];

                                        function updateInputFiles() {
                                            const dt = new DataTransfer();
                                            selectedFiles.forEach(item => dt.items.add(item.file));
                                            document.getElementById('documentInput').files = dt.files;
                                            // rebuild hidden inputs for per-file doc types
                                            const form = document.querySelector('form[action="../controllers/upload-shelter-documents-controller.php"]');
                                            if (form) {
                                                // remove existing doc_types[] hidden inputs
                                                Array.from(form.querySelectorAll('input[name="doc_types[]"]')).forEach(n => n.remove());
                                                selectedFiles.forEach(it => {
                                                    const h = document.createElement('input');
                                                    h.type = 'hidden';
                                                    h.name = 'doc_types[]';
                                                    h.value = it.docType || '';
                                                    form.appendChild(h);
                                                });
                                            }
                                            renderSelectedFiles();
                                        }

                                        function resetSelectionControls() {
                                            // reset doc_type select and file input so user can pick next file type easily
                                            const sel = document.querySelector('select[name="doc_type"]');
                                            const input = document.getElementById('documentInput');
                                            if (sel) sel.value = '';
                                            if (input) {
                                                try { input.value = null; } catch(e) { /* ignore */ }
                                            }
                                        }

                                        function renderSelectedFiles() {
                                            const preview = document.getElementById('selectedFilesPreview');
                                            const info = document.getElementById('selectedFilesInfo');
                                            const uploadBtn = document.getElementById('uploadBtn');
                                            if (!preview) return;
                                            preview.innerHTML = '';
                                            if (selectedFiles.length === 0) {
                                                preview.classList.add('text-muted');
                                                preview.textContent = 'No files selected.';
                                            } else {
                                                preview.classList.remove('text-muted');
                                                selectedFiles.forEach((item, idx) => {
                                                    const file = item.file;
                                                    const docType = item.docType || '';
                                                    const wrapper = document.createElement('div');
                                                    wrapper.className = 'd-flex flex-column align-items-center';
                                                    const div = document.createElement('div');
                                                    div.className = 'thumb';
                                                    if (file.type.startsWith('image/')) {
                                                        const img = document.createElement('img');
                                                        img.src = URL.createObjectURL(file);
                                                        img.alt = file.name;
                                                        div.appendChild(img);
                                                    } else {
                                                        div.innerHTML = '<div class="pdf-placeholder">PDF</div>';
                                                    }

                                                    const meta = document.createElement('div');
                                                    meta.className = 'text-center mt-2';
                                                    const nameLink = document.createElement('a');
                                                    nameLink.href = '#';
                                                    nameLink.className = 'small';
                                                    nameLink.textContent = file.name;
                                                    nameLink.onclick = function(e) {
                                                        e.preventDefault();
                                                        const url = URL.createObjectURL(file);
                                                        if (file.type.startsWith('image/')) {
                                                            // show in modal
                                                            const modal = document.getElementById('fileViewModal');
                                                            const content = document.getElementById('fileViewContent');
                                                            content.innerHTML = '<img src="' + url + '" alt="' + file.name + '" />';
                                                            modal.style.display = 'flex';
                                                        } else {
                                                            window.open(url, '_blank');
                                                        }
                                                    };

                                                    const dtype = document.createElement('div');
                                                    dtype.className = 'small text-muted';
                                                    dtype.textContent = 'Type: ' + (docType.replace(/_/g, ' ') || '—');

                                                    const removeBtn = document.createElement('button');
                                                    removeBtn.type = 'button';
                                                    removeBtn.className = 'btn btn-sm btn-outline-danger mt-2';
                                                    removeBtn.textContent = 'Remove';
                                                    removeBtn.onclick = function() {
                                                        selectedFiles.splice(idx, 1);
                                                        updateInputFiles();
                                                    };

                                                    meta.appendChild(nameLink);
                                                    meta.appendChild(dtype);
                                                    wrapper.appendChild(div);
                                                    wrapper.appendChild(meta);
                                                    wrapper.appendChild(removeBtn);
                                                    preview.appendChild(wrapper);
                                                });
                                            }
                                            info.textContent = selectedFiles.length +
                                                ' file(s) selected. (Exactly 3 required)';
                                            // enable upload only when exactly 3 files
                                            if (uploadBtn) uploadBtn.disabled = (selectedFiles.length !== maxFiles);
                                        }

                                        function showCameraModal() {
                                            const modal = document.getElementById('cameraModal');
                                            const video = document.getElementById('cameraPreview');
                                            modal.style.display = 'flex';
                                            navigator.mediaDevices.getUserMedia({
                                                    video: {
                                                        facingMode: 'environment'
                                                    }
                                                })
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
                                                if (!blob) return;
                                                const fileName = 'photo_' + Date.now() + '.jpg';
                                                const file = new File([blob], fileName, {
                                                    type: 'image/jpeg'
                                                });
                                                // validate
                                                if (file.size > maxSizeBytes) {
                                                    alert('Captured photo exceeds 5MB');
                                                    return;
                                                }
                                                if (selectedFiles.length >= maxFiles) {
                                                        alert('Maximum of 3 files allowed');
                                                        return;
                                                    }
                                                    const docType = (document.querySelector('select[name="doc_type"]').value || 'document').replace(/[^a-z0-9_\-]/gi,'_');
                                                    selectedFiles.push({ file: file, docType: docType });
                                                    updateInputFiles();
                                                    resetSelectionControls();
                                                closeCameraModal();
                                            }, 'image/jpeg', 0.95);
                                        }

                                        // handle file picker changes
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const input = document.getElementById('documentInput');
                                            const uploadBtn = document.getElementById('uploadBtn');
                                            // initialize selectedFiles from any prefilled input (unlikely)
                                            input.addEventListener('change', function(e) {
                                                const files = Array.from(e.target.files || []);
                                                // merge, but enforce maxFiles and validate
                                                const docType = (document.querySelector('select[name="doc_type"]').value || 'document').replace(/[^a-z0-9_\-]/gi,'_');
                                                files.forEach((f, i) => {
                                                    if (selectedFiles.length >= maxFiles) return;
                                                    if (f.size > maxSizeBytes) return;
                                                    // accept images and pdfs
                                                    if (!/^image\//.test(f.type) && f.type !== 'application/pdf') return;
                                                    // try to rename file to include docType where supported
                                                    try {
                                                        const ext = (f.name.split('.').pop() || (f.type.startsWith('image/') ? 'jpg' : 'pdf')).toLowerCase();
                                                        const newName = docType + '_' + Date.now() + '_' + i + '.' + ext;
                                                        const newFile = new File([f], newName, { type: f.type });
                                                        selectedFiles.push({ file: newFile, docType: docType });
                                                    } catch (err) {
                                                        selectedFiles.push({ file: f, docType: docType });
                                                    }
                                                });
                                                // ensure uniqueness by file.name+file.size (selectedFiles stores objects)
                                                selectedFiles = selectedFiles.filter((v, i, self) =>
                                                    i === self.findIndex(t => t.file.name === v.file.name && t.file.size === v.file.size));
                                                if (selectedFiles.length > maxFiles) selectedFiles =
                                                    selectedFiles.slice(0, maxFiles);
                                                updateInputFiles();
                                                resetSelectionControls();
                                            });
                                            // create preview container if not present
                                            if (!document.getElementById('selectedFilesPreview')) {
                                                const previewCard = document.createElement('div');
                                                previewCard.id = 'selectedFilesPreview';
                                                previewCard.className = 'file-preview text-muted';
                                                const parent = document.getElementById('documentInput').closest(
                                                    '.card-body');
                                                if (parent) parent.appendChild(previewCard);
                                            }
                                            // initial render
                                            renderSelectedFiles();
                                            // disable upload initially
                                            if (uploadBtn) uploadBtn.disabled = true;
                                            // guard form submit to ensure exactly maxFiles are selected
                                            const form = document.querySelector(
                                                'form[action="../controllers/upload-shelter-documents-controller.php"]'
                                            );
                                            if (form) {
                                                form.addEventListener('submit', function(e) {
                                                    if (selectedFiles.length !== maxFiles) {
                                                        e.preventDefault();
                                                        alert('You must select exactly ' + maxFiles +
                                                            ' files before uploading.');
                                                        return false;
                                                    }
                                                });
                                            }
                                        });
                                        </script>
                                        <!-- JS to handle file selection, preview, camera capture -->







                                    </div>
                                    <button id="uploadBtn" class="btn btn-primary w-100"><i class="ti ti-upload"></i>
                                        Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-white border-0 pb-0">
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
                                            <?php if(empty($documents)): ?><tr>
                                                <td colspan="5" class="text-center text-muted py-4">No documents
                                                    uploaded.</td>
                                            </tr><?php else: foreach($documents as $d): ?>
                                            <tr id="<?php echo htmlspecialchars($d['doc_type']); ?>">
                                                <td><?php echo strtoupper(str_replace('_',' ', htmlspecialchars($d['doc_type']))); ?>
                                                </td>
                                                <td><span
                                                        class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$d['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span>
                                                </td>
                                                <td><span
                                                        class="small text-muted"><?php echo htmlspecialchars(date('M d', strtotime($d['uploaded_at']))); ?></span>
                                                </td>
                                                <td><span
                                                        class="small text-muted"><?php echo $d['reviewed_at']? htmlspecialchars(date('M d', strtotime($d['reviewed_at']))):'—'; ?></span>
                                                </td>
                                                <td class="text-end"><a
                                                        href="<?php echo htmlspecialchars($d['file_path']); ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-outline-secondary">View</a></td>
                                            </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card mt-3">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">Selected Files Preview</h6>
                            </div>
                            <div class="card-body">
                                <style>
                                /* Keep thumbnails a reasonable, consistent size and crop images to fit */
                                .file-preview .thumb {
                                    width: 120px;
                                    height: 120px;
                                    border-radius: .5rem;
                                    overflow: hidden;
                                    background: #f8f9fa;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                }

                                .file-preview .thumb img {
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    display: block;
                                }

                                .file-preview .pdf-placeholder {
                                    font-weight: 600;
                                    color: #6c757d;
                                }

                                /* Slightly smaller on very small screens */
                                @media (max-width: 576px) {
                                    .file-preview .thumb {
                                        width: 90px;
                                        height: 90px;
                                    }
                                }
                                </style>

                                <div id="selectedFilesPreview"
                                    class="d-flex gap-3 flex-wrap align-items-start text-muted small file-preview">
                                    <div class="thumb">
                                        <div class="text-center small text-muted">No files selected.</div>
                                    </div>
                                </div>

                                <div class="mt-2 small text-muted">
                                    You must select exactly 3 files (JPG/PNG/PDF). Each file max 5MB. Use camera or file
                                    picker. Upload button is disabled until exactly 3 valid files are selected.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>