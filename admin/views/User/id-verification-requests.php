<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
// Load controller to populate $verification_requests before rendering the view
include dirname(__DIR__, 2) . '/controllers/User/id-verification-requests-controller.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h3 class="mb-4">ID Verification Requests</h3>
                <p class="mb-4">Queue of user ID verifications pending approval.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>ID Type</th>
                                <th>ID Image</th>
                                <th>Uploaded At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($verification_requests) && !empty($verification_requests)) {
                                foreach ($verification_requests as $idx => $request) {
                                    ?>
                                    <tr>
                                        <td><?php echo (int)($idx + 1); ?></td>
                                        <td style="width:80px;">
                                            <img src="<?php echo htmlspecialchars($request['profile_photo'] ?? '/assets/images/default-avatar.png'); ?>" alt="Profile" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                                        </td>
                                        <td><?php echo htmlspecialchars($request['full_name'] ?? ''); ?>
                                            <div class="small text-muted">User ID: <?php echo (int)($request['user_id'] ?? 0); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars(strtoupper(str_replace('_',' ', $request['doc_type'] ?? ''))); ?></td>
                                        <td>
                                            <?php if (!empty($request['file_path'])): ?>
                                                <?php
                                                // Normalize file path to be web-accessible. Many stored paths are relative like 'storage/uploads/...'
                                                $fp = $request['file_path'];
                                                $fp = trim($fp);
                                                if ($fp !== '' && stripos($fp, 'http') !== 0 && strpos($fp, '/') !== 0) {
                                                    // Prepend project folder so URL resolves from web root
                                                    $fp = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/' . ltrim($fp, '/');
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($fp); ?>" alt="ID Image" class="img-thumbnail id-thumb" style="max-width:120px;cursor:pointer;" data-doc-id="<?php echo (int)$request['doc_id']; ?>" data-file-path="<?php echo htmlspecialchars($fp); ?>">
                                            <?php else: ?>
                                                <span class="text-muted">No file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['uploaded_at'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($request['status'] ?? '') == 'pending' ? 'bg-warning' : (($request['status'] ?? '') == 'approved' ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($request['status'] ?? '')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (($request['status'] ?? '') == 'pending') : ?>
                                                <button class="btn btn-success btn-sm me-1" onclick="approveVerification(<?php echo (int)$request['user_id']; ?>)">Approve</button>
                                                <button class="btn btn-danger btn-sm" onclick="rejectVerification(<?php echo (int)$request['user_id']; ?>)">Reject</button>
                                                <div class="small text-muted mt-1">Or click the thumbnail to act on this specific document.</div>
                                            <?php else: ?>
                                                <span class="text-muted">No actions</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="9" class="text-center">No verification requests found.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<!-- Add this before the closing body tag -->
<script>
function approveVerification(userId) {
    if (confirm('Are you sure you want to approve this verification request?')) {
        $.ajax({
            url: '/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/User/verify-id-controller.php',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                action: 'approve'
            },
            success: function(result) {
                if (result && result.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Verification request approved successfully.',
                        icon: 'success'
                    }).then(() => {
                        // Update UI: change any rows for this user to approved
                        document.querySelectorAll('tr').forEach(tr => {
                            if (tr.querySelector('.small.text-muted') && tr.querySelector('.small.text-muted').textContent.includes('User ID: ' + userId)) {
                                const badge = tr.querySelector('span.badge');
                                if (badge) { badge.textContent = 'Approved'; badge.className = 'badge bg-success'; }
                                const buttons = tr.querySelectorAll('button'); buttons.forEach(b=>b.remove());
                            }
                        });
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message || 'Failed to approve verification request.',
                        icon: 'error'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing the request.',
                    icon: 'error'
                });
            }
        });
    }
}

function rejectVerification(userId) {
    Swal.fire({
        title: 'Reject Verification',
        text: 'Please provide a reason for rejection:',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for rejection!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/User/verify-id-controller.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    user_id: userId,
                    action: 'reject',
                    reason: result.value
                },
                success: function(result) {
                    if (result && result.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Verification request rejected successfully.',
                            icon: 'success'
                        }).then(() => {
                            // Update UI: mark rows for this user as rejected
                            document.querySelectorAll('tr').forEach(tr => {
                                if (tr.querySelector('.small.text-muted') && tr.querySelector('.small.text-muted').textContent.includes('User ID: ' + userId)) {
                                    const badge = tr.querySelector('span.badge');
                                    if (badge) { badge.textContent = 'Rejected'; badge.className = 'badge bg-danger'; }
                                    const buttons = tr.querySelectorAll('button'); buttons.forEach(b=>b.remove());
                                }
                            });
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to reject verification request.',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while processing the request.',
                        icon: 'error'
                    });
                }
            });
        }
    });
}
</script>

<!-- Image preview modal for document review -->
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-labelledby="docPreviewLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="docPreviewLabel">ID Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="docPreviewImg" src="" alt="Document" style="max-width:100%; height:auto;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="modalRejectBtn">Reject</button>
        <button type="button" class="btn btn-success" id="modalApproveBtn">Approve</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// When clicking a thumbnail, open modal and set doc id
document.querySelectorAll('.id-thumb').forEach(function(img){
    img.addEventListener('click', function(){
        const file = this.dataset.filePath;
        const docId = this.dataset.docId;
        document.getElementById('docPreviewImg').src = file;
        // store doc id on modal buttons
        document.getElementById('modalApproveBtn').dataset.docId = docId;
        document.getElementById('modalRejectBtn').dataset.docId = docId;
        var modal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
        modal.show();
    });
});

function updateRowStatusByDoc(docId, status) {
    // find the thumbnail with this docId and then find its row
    const img = document.querySelector('.id-thumb[data-doc-id="'+docId+'"]');
    if (!img) return;
    const tr = img.closest('tr');
    if (!tr) return;
    const badge = tr.querySelector('span.badge');
    if (badge) {
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        badge.className = 'badge ' + (status === 'pending' ? 'bg-warning' : (status === 'approved' ? 'bg-success' : 'bg-danger'));
    }
    // remove action buttons
    const actions = tr.querySelectorAll('button');
    actions.forEach(b => b.remove());
}

document.getElementById('modalApproveBtn').addEventListener('click', function(){
    const docId = this.dataset.docId;
    if (!docId) return;
    if (!confirm('Approve this document?')) return;
    // POST doc_id
    fetch('/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/User/verify-id-controller.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=approve&doc_id=' + encodeURIComponent(docId)
    }).then(r => r.json()).then(d => {
        if (d.status === 'success') {
            updateRowStatusByDoc(docId, 'approved');
            // close modal
            var m = bootstrap.Modal.getInstance(document.getElementById('docPreviewModal'));
            if (m) m.hide();
            Swal.fire('Approved','Document approved.','success');
        } else {
            Swal.fire('Error', d.message || 'Failed to approve','error');
        }
    }).catch(e => { Swal.fire('Error','Network error','error'); });
});

document.getElementById('modalRejectBtn').addEventListener('click', function(){
    const docId = this.dataset.docId;
    if (!docId) return;
    Swal.fire({
        title: 'Reject Verification',
        input: 'text',
        inputLabel: 'Reason (optional)',
        showCancelButton: true,
        confirmButtonText: 'Reject'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/User/verify-id-controller.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'action=reject&doc_id=' + encodeURIComponent(docId) + '&reason=' + encodeURIComponent(result.value || '')
            }).then(r => r.json()).then(d => {
                if (d.status === 'success') {
                    updateRowStatusByDoc(docId, 'rejected');
                    var m = bootstrap.Modal.getInstance(document.getElementById('docPreviewModal'));
                    if (m) m.hide();
                    Swal.fire('Rejected','Document rejected.','success');
                } else {
                    Swal.fire('Error', d.message || 'Failed to reject','error');
                }
            }).catch(e => { Swal.fire('Error','Network error','error'); });
        }
    });
});
</script>

<?php // controller already included above ?>
