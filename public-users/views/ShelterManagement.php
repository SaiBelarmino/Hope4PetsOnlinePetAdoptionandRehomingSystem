<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<script>
function openModal(id, extra) {
    if (id === 'uploadDocumentsModal' && extra) {
        try {
            const el = document.querySelector('#uploadDocumentsModal select[name="doc_type"]');
            if (el) el.value = decodeURIComponent(extra);
        } catch (e) {}
    }
    const m = document.getElementById(id);
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
// removed modal opener; uploads are now inline in the Documents card
// single camera modal handler reused by page (if implemented)
function showCameraModal(prefix) {
    // find target input
    let input = null;
    try {
        if (prefix === 'optional_document') {
            input = document.querySelector('input[name="optional_document"]');
        } else {
            // required_docs[<prefix>]
            input = document.querySelector('input[name="required_docs[' + prefix + ']"]');
        }
    } catch (e) {
        input = null;
    }
    if (!input) {
        alert('File input not found for: ' + prefix);
        return;
    }
    // open camera modal
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const captureBtn = document.getElementById('cameraCaptureBtn');
    const switchBtn = document.getElementById('cameraSwitchBtn');
    modal.dataset.targetInput = prefix;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // constraints prefer environment camera
    const constraints = {
        video: {
            facingMode: {
                ideal: 'environment'
            }
        },
        audio: false
    };
    // attempt to get media
    navigator.mediaDevices.getUserMedia(constraints).then(s => {
        modal._stream = s;
        video.srcObject = s;
        video.play();
    }).catch(err => {
        console.error('Camera error', err);
        $.notify('Cannot access camera: ' + (err && err.message ? err.message : err), {
            align: 'center',
            verticalAlign: 'top'
        });
        closeCameraModal();
    });
}

function closeCameraModal() {
    const modal = document.getElementById('cameraModal');
    if (!modal) return;
    const video = document.getElementById('cameraVideo');
    // stop stream
    if (modal._stream) {
        modal._stream.getTracks().forEach(t => t.stop());
        modal._stream = null;
    }
    video.pause();
    video.srcObject = null;
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function captureFromCamera() {
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const prefix = modal.dataset.targetInput;
    if (!modal._stream) {
        $.notify('Camera not ready', {
            align: 'center',
            verticalAlign: 'top'
        });
        return;
    }
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 1280;
    canvas.height = video.videoHeight || 720;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(function(blob) {
        if (!blob) {
            $.notify('Capture failed', {
                align: 'center',
                verticalAlign: 'top'
            });
            return;
        }
        const filename = (prefix || 'capture') + '_' + Date.now() + '.jpg';
        try {
            const file = new File([blob], filename, {
                type: 'image/jpeg'
            });
            const dt = new DataTransfer();
            // find target input again
            let input;
            if (prefix === 'optional_document') input = document.querySelector(
                'input[name="optional_document"]');
            else input = document.querySelector('input[name="required_docs[' + prefix + ']"]');
            if (!input) {
                $.notify('Target input not found for: ' + prefix, {
                    align: 'center',
                    verticalAlign: 'top'
                });
                closeCameraModal();
                return;
            }
            dt.items.add(file);
            input.files = dt.files;
            // update selected files info if exists
            const infoEl = document.getElementById('inlineSelectedFilesInfo');
            if (infoEl) infoEl.textContent = '1 file selected: ' + filename;
            closeCameraModal();
        } catch (e) {
            console.error('Assign file error', e);
            $.notify('Cannot assign captured photo to input in this browser.', {
                align: 'center',
                verticalAlign: 'top'
            });
        }
    }, 'image/jpeg', 0.9);
}
</script>

<?php
// compute application base path (strip off /public-users and deeper) so URLs like /storage/... map to project root
$appBase = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $parts = explode('/public-users', $_SERVER['SCRIPT_NAME']);
    $appBase = $parts[0] ?? '';
}
?>

<script>
var APP_BASE = '<?php echo addslashes($appBase); ?>';
</script>

<!-- Main content: My Shelter summary and quick actions -->
<div class="container-fluid py-3">
    <!-- alert container for upload messages -->
    <div id="uploadAlertContainer" class="mb-3"></div>
    <div class="row g-3">
        <!-- Left Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center Content -->
        <div class="col-12 col-lg-9 center-scroll"
            style="max-height:calc(100vh - 140px); overflow:auto; -webkit-overflow-scrolling:touch;" tabindex="0"
            aria-label="Main shelter content (scrollable)">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="mb-0 d-flex align-items-center gap-2">
                    <?php echo htmlspecialchars($shelter['shelter_name'] ?? ''); ?>
                    <?php if (!empty($shelter['is_verified'])): ?>
                    <span
                        class="badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-success text-white shadow-sm"
                        title="Verified">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 6L9 17l-5-5" />
                        </svg>
                        <small class="ms-1">Verified<?php if(!empty($shelter['verified_at'])): ?> •
                            <?php echo htmlspecialchars(date('M d, Y', strtotime($shelter['verified_at']))); ?><?php endif; ?></small>
                    </span>
                    <?php else: ?>
                    <span
                        class="badge rounded-pill d-inline-flex align-items-center px-2 py-1 border text-muted bg-white"
                        title="Unverified">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <small class="ms-1">Unverified</small>
                    </span>
                    <?php endif; ?>
                </h3>
                <div class="d-flex gap-1 align-items-center">
                    <a href="PetManagement.php" class="btn btn-sm btn-primary"><i class="ti ti-paw"></i> Manage Pets</a>
                    <button id="editShelterBtn" type="button" class="btn btn-sm btn-outline-secondary"><i
                            class="ti ti-edit"></i> Edit</button>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-3">
                            <div class="small text-muted">Pets</div>
                            <h4 class="mb-0"><?php echo (int)($stats['pets'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-3">
                            <div class="small text-muted">Donations</div>
                            <h4 class="mb-0">₱<?php echo number_format((float)($stats['donations'] ?? 0),2); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-3">
                            <div class="small text-muted">Pending Docs</div>
                            <h4 class="mb-0"><?php echo (int)($stats['pending_docs'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="small text-muted">Shelter Name</div>
                            <div id="shelterNameDisplay" class="fw-semibold text-truncate"
                                title="<?php echo htmlspecialchars($shelter['shelter_name'] ?? '—'); ?>">
                                <?php echo htmlspecialchars($shelter['shelter_name'] ?? '—'); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="small text-muted">Address</div>
                            <div id="shelterAddressDisplay" class="text-truncate"
                                title="<?php echo htmlspecialchars($shelter['address'] ?? '—'); ?>">
                                <?php echo htmlspecialchars($shelter['address'] ?? '—'); ?>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="small text-muted">Contact</div>
                            <div id="shelterContactDisplay">
                                <?php echo htmlspecialchars($shelter['contact_number'] ?? '—'); ?></div>
                        </div>

                        <div class="col-6 col-sm-6 col-lg-2">
                            <div class="small text-muted">Since</div>
                            <div id="shelterSinceDisplay">
                                <?php echo !empty($shelter['created_at']) ? htmlspecialchars(date('M Y', strtotime($shelter['created_at']))) : '—'; ?>
                            </div>
                        </div>

                        <?php
              // Determine overall documents status: any rejected -> Rejected, else any pending -> Pending, else any approved -> Approved, else No documents
              $overallStatus = 'No documents';
              $badgeType = 'light';
              if (!empty($documents) && is_array($documents)) {
                $hasPending = $hasApproved = $hasRejected = false;
                foreach ($documents as $d) {
                  $st = strtolower($d['status'] ?? '');
                  if ($st === 'rejected') { $hasRejected = true; break; }
                  if ($st === 'pending') $hasPending = true;
                  if ($st === 'approved') $hasApproved = true;
                }
                if ($hasRejected) {
                  $overallStatus = 'Rejected';
                  $badgeType = 'danger';
                } elseif ($hasPending) {
                  $overallStatus = 'Pending';
                  $badgeType = 'warning';
                } elseif ($hasApproved) {
                  $overallStatus = 'Approved';
                  $badgeType = 'success';
                } else {
                  $overallStatus = 'Unknown';
                  $badgeType = 'light';
                }
              }
              ?>
                        <div class="col-6 col-sm-6 col-lg-1 text-lg-end">
                            <div class="small text-muted">Document Status</div>
                            <div>
                                <span
                                    class="badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-<?php echo $badgeType; ?> text-dark shadow-sm">
                                    <?php echo htmlspecialchars($overallStatus); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Documents Card with inline upload form -->
            <div class="card">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Documents</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-white border-0 pb-0">
                                    <h6 class="mb-0">Upload Document</h6>
                                </div>
                                <div class="card-body">
                                    <form id="uploadDocumentsFormInline"
                                        action="../controllers/UploadDocumentsController.php" method="post"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="shelter_id"
                                            value="<?php echo htmlspecialchars($shelter['id'] ?? ''); ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Required Documents</label>
                                            <div class="mb-2">
                                                <label class="form-label small">Business Permit</label>
                                                <div class="input-group">
                                                    <input type="file" name="required_docs[business_permit]"
                                                        class="form-control" required accept="image/*,application/pdf">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="showCameraModal('business_permit')"><i
                                                            class="ti ti-camera"></i> Take Photo</button>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Mayor's Permit</label>
                                                <div class="input-group">
                                                    <input type="file" name="required_docs[mayors_permit]"
                                                        class="form-control" required accept="image/*,application/pdf">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="showCameraModal('mayors_permit')"><i
                                                            class="ti ti-camera"></i> Take Photo</button>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">BIR Registration</label>
                                                <div class="input-group">
                                                    <input type="file" name="required_docs[bir_registration]"
                                                        class="form-control" required accept="image/*,application/pdf">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="showCameraModal('bir_registration')"><i
                                                            class="ti ti-camera"></i> Take Photo</button>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Barangay Clearance</label>
                                                <div class="input-group">
                                                    <input type="file" name="required_docs[barangay_clearance]"
                                                        class="form-control" required accept="image/*,application/pdf">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        onclick="showCameraModal('barangay_clearance')"><i
                                                            class="ti ti-camera"></i> Take Photo</button>
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-1">You can upload one file per required
                                                document. Max 5MB each. JPG, PNG, PDF.</small>
                                        </div>

                                        <hr />

                                        <div class="mb-3">
                                            <label class="form-label">Optional Document</label>
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <select name="optional_doc_type" class="form-select">
                                                        <option value="">Select (optional)</option>
                                                        <?php
                                $allDocs = ['dtiregistration','mayors_permit','bir_registration','business_permit','articles_of_incorporation','barangay_clearance','contract_of_lease','other_business_documents'];
                                $requiredDocs = ['business_permit','mayors_permit','bir_registration','barangay_clearance'];
                                $optional = array_values(array_filter($allDocs, function($v) use ($requiredDocs){ return !in_array($v, $requiredDocs); }));
                                foreach($optional as $t): ?>
                                                        <option value="<?php echo htmlspecialchars($t); ?>">
                                                            <?php echo ucwords(str_replace('_',' ', $t)); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-8">
                                                    <div class="input-group">
                                                        <input type="file" name="optional_document" class="form-control"
                                                            accept="image/*,application/pdf">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            onclick="showCameraModal('optional_document')"><i
                                                                class="ti ti-camera"></i> Take Photo</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted">Upload one optional document if needed.</small>
                                        </div>

                                        <button id="inlineUploadBtn" class="btn btn-primary w-100" type="submit"><i
                                                class="ti ti-upload"></i> Upload</button>
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
                                            <tbody id="submittedDocumentsTbody">
                                                <?php if(empty($documents)): ?><tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No documents
                                                        uploaded.</td>
                                                </tr><?php else: foreach($documents as $d): ?>
                                                <tr id="<?php echo htmlspecialchars($d['doc_type']); ?>">
                                                    <td><?php echo strtoupper(str_replace('_',' ',htmlspecialchars($d['doc_type']))); ?>
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
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            onclick="openDocumentModal('<?php echo addslashes($d['file_path']); ?>')">View</button>
                                                        <?php if (isset($d['status']) && $d['status'] === 'rejected'): ?>
                                                        <button type="button" class="btn btn-sm btn-danger ms-2"
                                                            onclick="deleteDocument(<?php echo (int)$d['id']; ?>, this)">Remove</button>
                                                        <?php endif; ?>
                                                    </td>
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
        </div>
    </div>
</div>

<?php // Document preview modal ?>
<style>
/* Ensure preview content fits nicely in modal */
#documentPreviewModal>div {
    max-width: 900px;
    width: 95%;
    max-height: 90vh;
    border-radius: 8px;
    overflow: hidden;
}

#documentPreviewHeader {
    padding: 10px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#documentPreviewContent {
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    position: relative;
}

.preview-toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
}

.preview-media {
    cursor: grab;
    max-width: 100%;
    max-height: 78vh;
    object-fit: contain;
    display: block;
}

/* transform container to allow panning */
.preview-wrap {
    overflow: hidden;
    width: 100%;
    height: 78vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-wrap>* {
    transform-origin: center center;
}

#documentPreviewContent iframe {
    width: 100%;
    height: 78vh;
    border: 0;
}
</style>

<div id="documentPreviewModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:1050;">
    <div
        style="background:#fff;max-width:900px;width:95%;max-height:90vh;border-radius:8px;overflow:hidden;position:relative;">
        <div id="documentPreviewHeader">
            <div class="fw-semibold">Document Preview</div>
            <div class="preview-toolbar">
                <button id="zoomOutBtn" class="btn btn-sm btn-outline-secondary" title="Zoom out">−</button>
                <button id="zoomResetBtn" class="btn btn-sm btn-outline-secondary" title="Reset">Reset</button>
                <button id="zoomInBtn" class="btn btn-sm btn-outline-secondary" title="Zoom in">+</button>
                <button id="closePreviewBtn" class="btn btn-sm btn-outline-secondary"
                    onclick="closeDocumentModal()">Close</button>
            </div>
        </div>
        <div id="documentPreviewContent">
            <div class="preview-wrap" id="previewWrap"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var APP_BASE_LOCAL = typeof APP_BASE !== 'undefined' ? APP_BASE : '';
    var modal = document.getElementById('documentPreviewModal');
    var wrap = document.getElementById('previewWrap');
    var currentEl = null; // img or iframe
    var scale = 1;
    var minScale = 0.25;
    var maxScale = 5;
    var translate = {
        x: 0,
        y: 0
    };
    var dragging = false;
    var dragStart = {
        x: 0,
        y: 0
    };

    function clearPreview() {
        wrap.innerHTML = '';
        currentEl = null;
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    }

    function updateTransform() {
        if (!currentEl) return;
        currentEl.style.transform = 'translate(' + translate.x + 'px,' + translate.y + 'px) scale(' + scale + ')';
    }

    function loadPreview(finalUrl) {
        clearPreview();
        var ext = (finalUrl.split('.').pop() || '').toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
            var img = document.createElement('img');
            img.className = 'preview-media';
            img.onload = function() {
                centerFit();
            };
            img.src = finalUrl;
            wrap.appendChild(img);
            currentEl = img;
            attachPanHandlers(img);
        } else if (ext === 'pdf') {
            var iframe = document.createElement('iframe');
            iframe.src = finalUrl;
            iframe.className = 'preview-media';
            wrap.appendChild(iframe);
            currentEl = iframe;
            // panning/zoom on iframe will use CSS transform too
            attachPanHandlers(iframe);
        } else {
            wrap.innerHTML = '<p class="small text-muted">Cannot preview this file type. <a href="' + finalUrl +
                '" target="_blank">Open in new tab</a></p>';
        }
    }

    function centerFit() {
        // reset position and scale
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    }

    function zoomBy(delta) {
        var old = scale;
        scale = Math.min(maxScale, Math.max(minScale, scale + delta));
        if (scale === old) return;
        updateTransform();
    }

    // mouse wheel zoom
    function onWheel(e) {
        if (!currentEl) return;
        e.preventDefault();
        var delta = (e.deltaY > 0) ? -0.1 : 0.1;
        zoomBy(delta);
    }

    // pan handlers
    function attachPanHandlers(el) {
        el.style.cursor = 'grab';
        el.addEventListener('mousedown', function(e) {
            dragging = true;
            dragStart.x = e.clientX - translate.x;
            dragStart.y = e.clientY - translate.y;
            el.style.cursor = 'grabbing';
            e.preventDefault();
        });
        window.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            translate.x = e.clientX - dragStart.x;
            translate.y = e.clientY - dragStart.y;
            updateTransform();
        });
        window.addEventListener('mouseup', function(e) {
            if (dragging) {
                dragging = false;
                if (currentEl) currentEl.style.cursor = 'grab';
            }
        });

        // touch
        var touchStartPos = null;
        var lastDist = null;
        el.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                touchStartPos = {
                    x: e.touches[0].clientX - translate.x,
                    y: e.touches[0].clientY - translate.y
                };
            } else if (e.touches.length === 2) {
                lastDist = distance(e.touches[0], e.touches[1]);
            }
        }, {
            passive: false
        });
        el.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (e.touches.length === 1 && touchStartPos) {
                translate.x = e.touches[0].clientX - touchStartPos.x;
                translate.y = e.touches[0].clientY - touchStartPos.y;
                updateTransform();
            } else if (e.touches.length === 2) {
                var d = distance(e.touches[0], e.touches[1]);
                if (lastDist) {
                    var dd = (d - lastDist) / 200;
                    zoomBy(dd);
                }
                lastDist = d;
            }
        }, {
            passive: false
        });
        el.addEventListener('touchend', function(e) {
            touchStartPos = null;
            lastDist = null;
        });

        // wheel on wrap
        wrap.addEventListener('wheel', onWheel, {
            passive: false
        });
    }

    function distance(a, b) {
        var dx = a.clientX - b.clientX;
        var dy = a.clientY - b.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // toolbar buttons
    document.getElementById('zoomInBtn').addEventListener('click', function() {
        zoomBy(0.25);
    });
    document.getElementById('zoomOutBtn').addEventListener('click', function() {
        zoomBy(-0.25);
    });
    document.getElementById('zoomResetBtn').addEventListener('click', function() {
        scale = 1;
        translate = {
            x: 0,
            y: 0
        };
        updateTransform();
    });

    // open function exposed globally
    window.openDocumentModal = function(url) {
        try {
            var finalUrl = url;
            try {
                if (typeof url === 'string' && url.indexOf('/storage/') === 0 && APP_BASE_LOCAL) finalUrl =
                    APP_BASE_LOCAL + url;
            } catch (e) {}
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            loadPreview(finalUrl);
        } catch (e) {
            console.error(e);
            window.open(url, '_blank');
        }
    };

    window.closeDocumentModal = function() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            clearPreview();
        }
    };
})();
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>


<!-- Camera modal for capturing photos -->
<div id="cameraModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);align-items:center;justify-content:center;z-index:1100;">
    <div
        style="background:#111;max-width:600px;width:90%;padding:0;border-radius:8px;overflow:hidden;position:relative;">
        <video id="cameraVideo" style="width:100%;height:auto;background:#000;" autoplay muted></video>
        <div style="padding:10px;display:flex;align-items:center;justify-content:space-between;background:#222;">
            <div style="color:#fff;font-weight:500;">Capture Photo</div>
            <button onclick="closeCameraModal()" class="btn btn-sm btn-outline-secondary">Close</button>
        </div>
        <div style="padding:10px;display:flex;gap:10px;background:#333;">
            <button id="cameraCaptureBtn" type="button" class="btn btn-primary flex-fill" onclick="captureFromCamera()">
                <i class="ti ti-camera"></i> Capture
            </button>
            <button id="cameraSwitchBtn" type="button" class="btn btn-outline-light flex-fill" style="display:none;">
                <i class="ti ti-camera-off"></i> Switch to Front
            </button>
        </div>
    </div>
</div>

<script>
// helper to build controller URLs reliably from APP_BASE
function controllerUrl(name) {
    var base = (typeof APP_BASE !== 'undefined' && APP_BASE) ? APP_BASE : '';
    // ensure leading slash
    if (base && base.indexOf('/') !== 0) base = '/' + base;
    return base + '/public-users/controllers/' + name;
}

// Auto-fetch data from controller API to populate page dynamically
(function() {
    var api = '../controllers/ShelterManagementController.php';

    function textOrDash(v) {
        return v || '—';
    }

    function setText(selector, val) {
        var el = document.querySelector(selector);
        if (el) el.textContent = val;
    }

    function clearChildren(el) {
        while (el.firstChild) el.removeChild(el.firstChild);
    }

    function buildDocumentsTable(docs) {
        var tbody = document.querySelector('#submittedDocumentsTbody');
        if (!tbody) return;
        clearChildren(tbody);
        if (!docs || docs.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'text-center text-muted py-4';
            td.textContent = 'No documents uploaded.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }
        docs.forEach(function(d) {
            var tr = document.createElement('tr');
            tr.id = d.doc_type ? String(d.doc_type) : '';
            var tdType = document.createElement('td');
            tdType.textContent = (d.doc_type ? d.doc_type.replace(/_/g, ' ').toUpperCase() : '');
            tr.appendChild(tdType);
            var tdStatus = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'badge bg-' + ({
                'pending': 'warning',
                'approved': 'success',
                'rejected': 'danger'
            } [d.status] || 'light');
            span.textContent = (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) :
                'Unknown');
            tdStatus.appendChild(span);
            tr.appendChild(tdStatus);
            var tdUploaded = document.createElement('td');
            tdUploaded.innerHTML = '<span class="small text-muted">' + (d.uploaded_at ? new Date(d
                .uploaded_at).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric'
            }) : '—') + '</span>';
            tr.appendChild(tdUploaded);
            var tdReviewed = document.createElement('td');
            tdReviewed.innerHTML = '<span class="small text-muted">' + (d.reviewed_at ? new Date(d
                .reviewed_at).toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric'
            }) : '—') + '</span>';
            tr.appendChild(tdReviewed);
            var tdAction = document.createElement('td');
            tdAction.className = 'text-end';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-secondary';
            btn.textContent = 'View';
            btn.onclick = function() {
                openDocumentModal(d.file_path);
            };
            tdAction.appendChild(btn);
            // add Remove button for rejected documents
            if (d.status && String(d.status).toLowerCase() === 'rejected') {
                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger ms-2';
                removeBtn.textContent = 'Remove';
                removeBtn.onclick = function() {
                    deleteDocument(d.id, removeBtn);
                };
                tdAction.appendChild(removeBtn);
            }
            tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    }

    function populate(data) {
        if (!data) return;
        // fill header
        var shelter = data.shelter || {};
        var stats = data.stats || {};
        // set both header and the detail fields
        setText('h3.mb-0.d-flex', shelter.shelter_name || 'My Shelter');
        setText('#shelterNameDisplay', shelter.shelter_name || '—');
        setText('#shelterAddressDisplay', shelter.address || '—');
        setText('#shelterContactDisplay', shelter.contact_number || '—');
        setText('#shelterSinceDisplay', shelter.created_at ? new Date(shelter.created_at).toLocaleDateString(
            undefined, {
                month: 'short',
                year: 'numeric'
            }) : '—');
        // simple placements: update counts
        setText('.card .card-body .small.text-muted + h4', String(stats.pets || 0));
        // better update using IDs: ensure elements have IDs — fallback to query selectors
        var donationEls = document.querySelectorAll('.card-body h4');
        // update specific stats by walking the 3 cards
        var statCards = document.querySelectorAll('.row.g-3.mb-3 .card .card-body h4');
        if (statCards && statCards.length >= 3) {
            statCards[0].textContent = String(stats.pets || 0);
            statCards[1].textContent = '₱' + (typeof stats.donations !== 'undefined' ? Number(stats.donations)
                .toFixed(2) : '0.00');
            statCards[2].textContent = String(stats.pending_docs || 0);
        }

        // shelter details
        var shelterNameEl = document.querySelector('[title="' + (shelter.shelter_name || '—') + '"]');
        // update address/contact/since more reliably
        var addressEls = document.querySelectorAll('div[title]');

        // set upload form shelter_id
        var hid = document.querySelector('input[name="shelter_id"]');
        if (hid) hid.value = shelter.id || '';

        buildDocumentsTable(data.documents || []);

        // overall document status badge
        var badge = document.querySelector('.badge.rounded-pill');
        if (badge && data.documents) {
            var overall = 'No documents';
            var type = 'light';
            var hasPending = false,
                hasApproved = false,
                hasRejected = false;
            data.documents.forEach(function(d) {
                var st = (d.status || '').toLowerCase();
                if (st === 'rejected') hasRejected = true;
                if (st === 'pending') hasPending = true;
                if (st === 'approved') hasApproved = true;
            });
            if (hasRejected) {
                overall = 'Rejected';
                type = 'danger';
            } else if (hasPending) {
                overall = 'Pending';
                type = 'warning';
            } else if (hasApproved) {
                overall = 'Approved';
                type = 'success';
            }
            badge.textContent = overall;
            badge.className = 'badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-' + type +
                ' text-dark shadow-sm';
        }
    }

    // fetch data
    // resolve api via helper
    try {
        api = controllerUrl('ShelterManagementController.php');
    } catch (e) {}
    fetch(api, {
        credentials: 'same-origin'
    }).then(function(res) {
        if (!res.ok) throw new Error('Network response not ok');
        return res.json();
    }).then(function(json) {
        try {
            populate(json);
        } catch (e) {
            console.error(e);
        }
    }).catch(function(err) {
        console.warn('Could not fetch remote data, falling back to server-side render if present.', err);
    });
})();
</script>


<script>
// helper to show notifications (optional wrapper — uses $.notify)
function notifyMessage(msg) {
    try {
        $.notify(String(msg || ''), {
            align: 'center',
            verticalAlign: 'top'
        });
    } catch (e) {
        try {
            alert(msg);
        } catch (e) {}
    }
}
</script>

<script>
// replace alert usage in delete/upload/edit flows with $.notify via notifyMessage
(function() {
    document.getElementById('saveEditShelterBtn').addEventListener('click', function() {
        const form = document.getElementById('editShelterForm');
        const formData = new FormData(form);
        const data = {
            shelter_id: formData.get('shelter_id'),
            shelter_name: formData.get('shelter_name'),
            address: formData.get('address'),
            contact_number: formData.get('contact_number')
        };

        fetch(controllerUrl('EditShelterController.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Update the page with new data
                    document.querySelector('h3.mb-0.d-flex').textContent = data.shelter_name ||
                        'My Shelter';
                    document.getElementById('shelterNameDisplay').textContent = data.shelter_name ||
                        '—';
                    document.getElementById('shelterAddressDisplay').textContent = data.address || '—';
                    document.getElementById('shelterContactDisplay').textContent = data
                        .contact_number || '—';
                    // Close modal if exists
                    try {
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'editShelterModal'));
                        if (modal) modal.hide();
                    } catch (e) {}
                    notifyMessage('Shelter updated successfully.');
                } else {
                    notifyMessage(result.error || 'Failed to update shelter.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notifyMessage('An error occurred while updating the shelter.');
            });
    });
})();
</script>
<!-- Include SweetAlert2 -->

<script>
// Modern, minimalist delete confirmation using SweetAlert2
window.deleteDocument = function(docId, btnEl) {
    if (!docId) {
        notifyMessage('Document id missing');
        return;
    }

    Swal.fire({
        title: 'Delete document?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        width: 350, // smaller width
        scrollbarPadding: false, // prevent page jump
        focusConfirm: false, // prevent auto scroll
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(docId, btnEl);
        }
    });
};

// performDelete: sends request and removes row on success
window.performDelete = function(docId, btnEl) {
    if (!docId) return;
    try {
        if (btnEl) btnEl.disabled = true;
    } catch (e) {}
    var origText = btnEl && btnEl.textContent || 'Remove';
    if (btnEl) btnEl.textContent = 'Removing...';

    var fd = new FormData();
    fd.append('document_id', String(docId));

    fetch(controllerUrl('DeleteDocumentController.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: fd
    }).then(function(res) {
        if (!res.ok) return res.text().then(function(text) {
            throw new Error('Server returned ' + res.status + '. ' + text);
        });
        var ct = res.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) return res.text().then(function(text) {
            throw new Error('Expected JSON response but got: ' + text);
        });
        return res.json();
    }).then(function(json) {
        if (json && json.success) {
            var tr = btnEl && btnEl.closest && btnEl.closest('tr');
            if (tr) tr.parentNode.removeChild(tr);

            // Minimalist success toast
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                timer: 1200,
                showConfirmButton: false,
                width: 300,
                scrollbarPadding: false,
                focusConfirm: false
            });
        } else {
            notifyMessage((json && json.error) ? json.error : 'Could not remove document');
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.textContent = origText;
            }
        }
    }).catch(function(err) {
        console.error('Delete error', err);
        notifyMessage('Request failed: ' + (err && err.message ? err.message : 'unknown'));
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.textContent = origText;
        }
    });
};
</script>


<!-- Edit shelter modal using SweetAlert2 for a modern look -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('editShelterBtn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        var nameEl = document.getElementById('shelterNameDisplay');
        var addrEl = document.getElementById('shelterAddressDisplay');
        var contactEl = document.getElementById('shelterContactDisplay');
        var hid = document.querySelector('input[name="shelter_id"]');
        var shelterId = hid ? hid.value : '';
        var curName = nameEl ? nameEl.textContent.trim() : '';
        var curAddr = addrEl ? addrEl.textContent.trim() : '';
        var curContact = contactEl ? contactEl.textContent.trim() : '';

        Swal.fire({
            title: 'Edit Shelter',
            html: `
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <input id="swName" class="swal2-input" placeholder="Shelter name">
                    <input id="swAddress" class="swal2-input" placeholder="Address">
                    <input id="swContact" class="swal2-input" placeholder="Contact number">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: 'Cancel',
            width: 600, // wider for horizontal feel
            padding: '1.5em',
            scrollbarPadding: false,
            focusConfirm: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function() {
                try {
                    document.getElementById('swName').value = curName;
                    document.getElementById('swAddress').value = curAddr;
                    document.getElementById('swContact').value = curContact;
                    document.getElementById('swName').focus();
                } catch (e) {}
            },
            preConfirm: function() {
                var newName = (document.getElementById('swName') || {}).value || '';
                var newAddr = (document.getElementById('swAddress') || {}).value || '';
                var newContact = (document.getElementById('swContact') || {}).value || '';
                if (!newName.trim()) {
                    Swal.showValidationMessage('Shelter name is required');
                    return false;
                }
                return {
                    shelter_id: shelterId,
                    shelter_name: newName.trim(),
                    address: newAddr.trim(),
                    contact_number: newContact.trim()
                };
            }
        }).then(function(result) {
            if (!result || !result.value) return;
            var data = result.value;

            fetch(controllerUrl('EditShelterController.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            }).then(function(res) {
                return res.json();
            }).then(function(json) {
                if (json && json.success) {
                    try {
                        if (nameEl) nameEl.textContent = data.shelter_name || '—';
                        if (addrEl) addrEl.textContent = data.address || '—';
                        if (contactEl) contactEl.textContent = data.contact_number ||
                            '—';
                    } catch (e) {}

                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        timer: 1000,
                        showConfirmButton: false,
                        width: 250,
                        scrollbarPadding: false,
                        focusConfirm: false
                    });
                } else {
                    notifyMessage((json && (json.error || json.message)) ? (json
                        .error || json.message) : 'Failed to update shelter');
                }
            }).catch(function(err) {
                console.error('Edit shelter error', err);
                notifyMessage('An error occurred while updating the shelter.');
            });
        });
    });
});
</script>