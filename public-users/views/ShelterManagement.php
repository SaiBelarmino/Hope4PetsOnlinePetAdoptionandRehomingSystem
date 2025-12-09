<!-- Container for fallback confirmation messages -->
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<body>
<link rel="stylesheet" href="assets/css/documentmodal.css">
<link rel="stylesheet" href="assets/css/leaflet.css" />

<?php
require_once __DIR__ . '/../controllers/PetManagementController.php';
// compute application base path (strip off /public-users and deeper) so URLs like /storage/... map to project root
$appBase = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $parts = explode('/public-users', $_SERVER['SCRIPT_NAME']);
    $appBase = $parts[0] ?? '';
}
?>

<!-- Main content: My Shelter summary and quick actions -->
<div class="container-fluid">
    <!-- alert container for upload messages -->
    <div id="uploadAlertContainer">
        <div class="row g-3 py-4">
            <!-- Left Sidebar -->
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
            <!-- Center Content -->
            <div class="col-12 col-lg-8 center-scroll"
                style="max-height:862px; overflow-y:auto; overflow-x:hidden; -webkit-overflow-scrolling:touch;" tabindex="0"
                aria-label="Main shelter content (scrollable)">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h3 class="mb-0 d-flex align-items-center gap-2">
                        <?php echo htmlspecialchars($shelter['shelter_name'] ?? ''); ?>
                        <?php if (!empty($shelter['is_verified'])): ?>
                        <span
                            class="badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-success text-white shadow-sm"
                            title="Verified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M20 6L9 17l-5-5" />
                            </svg>
                            <small class="ms-1">Verified<?php if(!empty($shelter['verified_at'])): ?> •
                                <?php echo htmlspecialchars(date('M d, Y', strtotime($shelter['verified_at']))); ?><?php endif; ?></small>
                        </span>
                        <?php else: ?>
                        <span
                            class="badge rounded-pill d-inline-flex align-items-center px-2 py-1 border text-muted bg-white"
                            title="Unverified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <small class="ms-1">Unverified</small>
                        </span>
                        <?php endif; ?>
                    </h3>
                    <div class="d-flex gap-1 align-items-center">
                        <a href="PetManagement.php" class="btn btn-sm btn-primary" id="managePetsBtn" rel="noopener">
                            <i class="ti ti-paw"></i> Manage Pets
                        </a>
                        <button id="editShelterBtn" type="button" class="btn btn-sm btn-outline-secondary"><i class="ti ti-edit"></i> Edit</button>
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
                <?php
                // Show upload documents form if not verified or has pending/rejected documents
                $showUploadForm = empty($shelter['is_verified']) || ($overallStatus === 'Pending' || $overallStatus === 'Rejected');
                if ($showUploadForm): ?>
                <div class="card mb-3" id="uploadDocumentsFormInline">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="mb-0">Upload Shelter Verification Documents</h6>
                    </div>
                    <div class="card-body">
                        <!--
                            After document upload, the backend should redirect to ShelterManagement.php.
                            This ensures the latest document statuses are fetched and displayed immediately.
                            The document status badge and count will update automatically based on the new DB state.
                        -->
                        <?php
                        // Map document types to field names and labels
                        $docFields = [
                            'barangay_permit' => 'Barangay Permit',
                            'barangay_clearance' => 'Barangay Clearance',
                            'bir_permit' => 'BIR Permit',
                            'bai_permit' => 'Bureau of Animal Industry Permit'
                        ];
                        // Build a lookup for document status by type
                        $docStatus = [];
                        if (!empty($documents) && is_array($documents)) {
                            foreach ($documents as $d) {
                                $docStatus[strtolower(str_replace(' ', '_', $d['doc_type']))] = strtolower($d['status']);
                            }
                        }
                        ?>
                        <form id="uploadDocumentsForm" enctype="multipart/form-data" class="row g-3">
                            <input type="hidden" name="shelter_id" value="<?php echo htmlspecialchars($shelter['id'] ?? ''); ?>">
                            <?php foreach ($docFields as $field => $label):
                                $status = $docStatus[$field] ?? 'no document';
                                $disabled = ($status === 'approved' || $status === 'pending') ? 'disabled' : '';
                                $badgeColor = ($status === 'approved') ? 'success' : (($status === 'pending') ? 'warning' : (($status === 'declined') ? 'danger' : 'secondary'));
                            ?>
                            <div class="col-12 col-md-6">
                                <label class="form-label"><?php echo $label; ?> (image or PDF)</label>
                                <input type="file" name="<?php echo $field; ?>" id="<?php echo $field; ?>Input" class="form-control" accept="image/*,.pdf" <?php echo $disabled; ?> >
                            </div>
                            <?php endforeach; ?>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" id="uploadDocsBtn" class="btn btn-primary"><i class="ti ti-upload"></i> Upload Documents</button>
                                <span id="uploadSpinner" class="spinner-border spinner-border-sm ms-2" style="display:none;" role="status" aria-hidden="true"></span>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($shelter['is_verified'])): ?>
                        <!-- Notify user that shelter is approved -->
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="ti ti-check-circle me-2"></i>
                            <div>Your shelter has been <strong>approved</strong> by the admin. You can now manage and display your pets.</div>
                        </div>
                        <!-- Show pets grid if shelter is verified -->
                        <?php
                        $shelterPets = [];
                        if (!empty($shelter['id'])) {
                            // Fetch pets for this shelter
                            $shelterId = (int)$shelter['id'];
                            $shelterPets = PetManagementController::getPetsByShelterId($shelterId);
                            // Attach photo URLs
                            foreach ($shelterPets as &$pet) {
                                $photos = PetManagementController::getPetPhotos((int)$pet['id']);
                                $pet['photos'] = $photos;
                                $source = '';
                                if (!empty($pet['primary_photo'])) $source = $pet['primary_photo'];
                                elseif (!empty($photos[0]['photo_path'])) $source = $photos[0]['photo_path'];
                                elseif (!empty($pet['pet_photos'])) $source = $pet['pet_photos'];
                                $pet['photo'] = PetManagementController::getPhotoUrl((int)($pet['owner_id'] ?? 0), $source ?: '/storage/uploads/images/default.png');
                                $pet['photo_raw'] = $source;
                            }
                            unset($pet);
                        }
                        ?>
                        <div class="card mb-3">
                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-0">Shelter Pets</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3" id="pet-grid">
                                    <?php if (empty($shelterPets)): ?>
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body text-center text-muted py-5">No pets found for this shelter.</div>
                                            </div>
                                        </div>
                                    <?php else: foreach ($shelterPets as $p):
                                        $status = $p['status'] ?? 'available';
                                        $photoFullUrl = $p['photo'] ?? '/storage/uploads/images/default.png';
                                    ?>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="card h-100 pet-card">
                                            <div class="ratio ratio-4x3 overflow-hidden">
                                                <img src="<?php echo htmlspecialchars($photoFullUrl); ?>"
                                                    alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>"
                                                    class="card-img-top object-fit-cover">
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">
                                                            <?php echo htmlspecialchars($p['name'] ?? 'Unnamed'); ?>
                                                        </h6>
                                                        <div class="small text-muted">
                                                            <?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?> ·
                                                            <?php echo htmlspecialchars($p['age'] ?? ''); ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-<?php echo ($status==='available')? 'success' : (($status==='adopted')? 'secondary' : 'warning'); ?>">
                                                            <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="mb-2 small">
                                                    <span class="me-2"><strong>Species:</strong> <?php echo htmlspecialchars(ucfirst($p['species'] ?? 'Other')); ?></span>
                                                    <span class="me-2"><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($p['gender'] ?? 'Unknown')); ?></span>
                                                    <span><strong>Size:</strong> <?php echo htmlspecialchars(ucfirst($p['size'] ?? 'Medium')); ?></span>
                                                </div>
                                                <div class="mb-2 small text-truncate"><strong>Vaccine:</strong> <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?></div>
                                                <div class="mb-2 small text-truncate"><strong>Health:</strong> <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?></div>
                                                <div class="mb-2 small text-truncate text-muted"><i class="ti ti-map-pin"></i> <?php echo htmlspecialchars($p['location'] ?? 'Unknown'); ?></div>
                                                <p class="small text-truncate mb-2"> <?php echo htmlspecialchars($p['description'] ?? 'No description'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="ti ti-lock me-2"></i>
                            <div>Your shelter must be <strong>verified</strong> before you can manage or add pets.</div>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>
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

    <!-- Edit Shelter Modal -->
    <div class="modal fade" id="editShelterModal" tabindex="-1" aria-labelledby="editShelterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="z-index: 1060;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editShelterModalLabel">Edit Shelter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editShelterForm" method="post" action="../controllers/EditShelterController.php">
                        <input type="hidden" name="shelter_id"
                            value="<?php echo htmlspecialchars($shelter['id'] ?? ''); ?>">
                        <div class="mb-3">
                            <label class="form-label">Shelter Name</label>
                            <input type="text" class="form-control" name="shelter_name"
                                value="<?php echo htmlspecialchars($shelter['shelter_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact_number"
                                value="<?php echo htmlspecialchars($shelter['contact_number'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-2" name="shelter_unit"
                                        value="<?php echo htmlspecialchars($shelter_unit); ?>"
                                        placeholder="Shelter/Unit Name (e.g., 2nd Floor)">
                                    <input type="text" class="form-control mb-2" name="purok_subdivision"
                                        value="<?php echo htmlspecialchars($shelter_purok); ?>"
                                        placeholder="Purok/Subdivision">
                                    <input type="text" class="form-control mb-2" name="barangay"
                                        value="<?php echo htmlspecialchars($shelter_barangay); ?>"
                                        placeholder="Barangay">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-2" name="city"
                                        value="<?php echo htmlspecialchars($shelter_city); ?>" placeholder="City">
                                    <input type="text" class="form-control mb-2" name="province"
                                        value="<?php echo htmlspecialchars($shelter_province); ?>"
                                        placeholder="Province">
                                    <input type="text" class="form-control mb-2" name="postal_code"
                                        value="<?php echo htmlspecialchars($shelter_postal); ?>"
                                        placeholder="Postal Code">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">Location is used for accurate place name via
                                geolocation.</small>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary" id="getShelterLocationBtn">Get Current
                                    Location</button>
                            </div>
                            <div id="shelterLocationError" class="alert alert-danger mt-2" style="display: none;"></div>
                            <div id="shelterMap"
                                style="height: 300px; margin-top: 10px; display: none; background: #f0f0f0; border: 1px solid #ccc; position: relative;">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Update
                                Shelter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
    var APP_BASE = '<?php echo addslashes($appBase); ?>';

    // AJAX Upload Documents button handler
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('uploadDocumentsForm');
        var btn = document.getElementById('uploadDocsBtn');
        var spinner = document.getElementById('uploadSpinner');
        if (form && btn) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                // Validate required files
                var requiredFields = ['barangayPermitInput', 'barangayClearanceInput', 'birPermitInput', 'baiPermitInput'];
                // Remove required document check and alert
                btn.disabled = true;
                if (spinner) spinner.style.display = 'inline-block';
                var formData = new FormData(form);
                fetch('../controllers/UploadDocumentsController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    if (spinner) spinner.style.display = 'none';
                        console.log('AJAX response received:', data);
                    if (data.success) {
                        // Show confirmation message instead of alert
                        var container = document.getElementById('uploadAlertContainer') || document.body;
                        var msg = document.createElement('div');
                        msg.className = 'alert alert-success mt-3';
                        msg.innerHTML = 'Your documents are submitted to admin.<br>Please wait up to <strong>3 days</strong> for review.';
                        // Remove previous confirmation if present
                        var oldMsg = container.querySelector('.alert-success');
                        if (oldMsg) oldMsg.remove();
                        container.prepend(msg);
                        // Fetch latest document status and update badge/count
                        fetchDocumentStatus();
                    } // No else: do not show any alert for failed upload
                })
                .catch(() => {
                    btn.disabled = false;
                    if (spinner) spinner.style.display = 'none';
                    // Silent failure: do not show any alert
                });
            });
        }
    });

    // Fetch latest document status and update badge/count
    function fetchDocumentStatus() {
        fetch('getShelterDocumentStatus.php?shelter_id=<?php echo htmlspecialchars($shelter['id'] ?? ''); ?>')
            .then(response => response.json())
            .then(data => {
                if (data && data.status && data.badgeType) {
                    var badge = document.querySelector('.badge.rounded-pill.bg-<?php echo $badgeType; ?>');
                    if (badge) {
                        badge.className = 'badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-' + data.badgeType + ' text-dark shadow-sm';
                        badge.textContent = data.status;
                    }
                }
                if (data && typeof data.pending_docs !== 'undefined') {
                    var pendingDocsEl = document.querySelector('.card-body h4.mb-0');
                    if (pendingDocsEl) pendingDocsEl.textContent = data.pending_docs;
                }
            });
    }

    function updateShelterStatusUI(isVerified) {
        // Badge
        var badge = document.getElementById('shelterStatusBadge');
        if (badge) {
            if (isVerified) {
                badge.className = 'badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-success text-white shadow-sm';
                badge.innerHTML = 'Verified';
            } else {
                badge.className = 'badge rounded-pill d-inline-flex align-items-center px-2 py-1 bg-warning text-dark shadow-sm';
                badge.innerHTML = 'Unverified';
            }
        }
        // Manage Pets button
        var manageBtn = document.getElementById('managePetsBtn');
        if (manageBtn) {
            manageBtn.disabled = !isVerified;
            if (isVerified) {
                manageBtn.className = 'btn btn-sm btn-primary';
            } else {
                manageBtn.className = 'btn btn-sm btn-secondary';
            }
        }
    }

    // Poll shelter status every 10 seconds
    setInterval(function() {
        fetch('ShelterManagementController.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.shelter) {
                    updateShelterStatusUI(data.shelter.is_verified == 1);
                }
            });
    }, 10000);
    </script>

    <script>
    function openDocumentsSection() {
        // close modal if bootstrap available
        var modalEl = document.getElementById('verifyRequiredModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            var m = bootstrap.Modal.getInstance(modalEl);
            if (m) m.hide();
        }
        // scroll to documents upload area
        var el = document.getElementById('uploadDocumentsFormInline');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var firstInput = el.querySelector('input[type="file"], select, textarea, input[type="text"], button');
            if (firstInput) firstInput.focus();
        } else {
            window.location.hash = '#'; // fallback
        }
    }
    </script>

    <script src="assets/js/leaflet.js"></script>
    <script src="assets/js/shelterGeolocation.js" defer></script>
    <script src="assets/js/documentPreviewModal.js" defer></script>
    <script src="assets/js/uploadDocumentsModal.js" defer></script>
    <script src="assets/js/shelterManagement.js"></script>
    <?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>