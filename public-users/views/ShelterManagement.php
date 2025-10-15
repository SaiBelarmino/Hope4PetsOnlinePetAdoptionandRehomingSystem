<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<link rel="stylesheet" href="assets/css/documentmodal.css">
<link rel="stylesheet" href="assets/css/leaflet.css" />

<?php
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
    <div id="uploadAlertContainer" class="mb-3"></div>
    <div class="row g-3">
        <!-- Left Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center Content -->
        <div class="col-12 col-lg-9 center-scroll"
            style="max-height:862px; overflow:auto; -webkit-overflow-scrolling:touch;" tabindex="0"
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
<div class="modal fade" id="editShelterModal" tabindex="-1" aria-labelledby="editShelterModalLabel" aria-hidden="true">
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
                                    value="<?php echo htmlspecialchars($shelter_barangay); ?>" placeholder="Barangay">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control mb-2" name="city"
                                    value="<?php echo htmlspecialchars($shelter_city); ?>" placeholder="City">
                                <input type="text" class="form-control mb-2" name="province"
                                    value="<?php echo htmlspecialchars($shelter_province); ?>" placeholder="Province">
                                <input type="text" class="form-control mb-2" name="postal_code"
                                    value="<?php echo htmlspecialchars($shelter_postal); ?>" placeholder="Postal Code">
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
var APP_BASE = '<?php echo addslashes($appBase); ?>';
</script>

<script src="assets/js/leaflet.js"></script>
<script src="assets/js/shelterGeolocation.js" defer></script>
<script src="assets/js/documentPreviewModal.js" defer></script>
<script src="assets/js/uploadDocumentsModal.js" defer></script>
<script src="assets/js/shelterManagement.js"></script>
<?php include __DIR__ . '/../include/footer.php'; ?>