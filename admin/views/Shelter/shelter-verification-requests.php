<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<?php
include dirname(__DIR__, 2) . '/controllers/Shelter/shelter-verification-requests-controller.php';
$docs = ShelterVerificationRequestsController::fetchDocuments(null, 200);
?>

<div class="body-wrapper">
<?php include dirname(__DIR__, 2) . '/header.php'; ?>
<div class="container-fluid">
	<div class="d-flex align-items-center justify-content-between mb-3">
		<h3 class="mb-0">Pending Shelter Verification Documents</h3>
	</div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Shelter</th>
                            <th>Document Type</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($docs as $doc): ?>
                        <tr>
                            <td><?= htmlspecialchars($doc['shelter_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($doc['doc_type'] ?? '') ?></td>
                            <td><?= htmlspecialchars($doc['uploaded_at'] ?? '') ?></td>
                            <td>
                                <a class="btn btn-primary btn-sm"
                                    href="/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/serve-shelter-document.php?id=<?= $doc['id'] ?>"
                                    target="_blank">
                                    Open Document
                                </a>
                                <form method="post" action="/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/review-shelter-document-action.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this document?');">Approve</button>
                                </form>
                                <form method="post" action="/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/controllers/review-shelter-document-action.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this document?');">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>

<!-- Document Viewer Modal -->
<div id="docViewerModal" style="display:none; position:fixed; z-index:2000; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:#fff; width:90vw; height:90vh; max-width:1100px; max-height:800px; border-radius:8px; position:relative; overflow:hidden; display:flex; flex-direction:column;">
        <div style="padding:10px 12px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:bold;">Document Viewer</span>
            <button onclick="closeModal()" style="border:none; background:none; font-size:1.5em;">&times;</button>
        </div>
        <div id="docViewerContent" style="flex:1; display:flex; align-items:center; justify-content:center; background:#fafafa;">
            <!-- Content loaded by JS -->
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('docViewerModal').style.display = 'none';
    document.getElementById('docViewerContent').innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('docViewerModal');
    const content = document.getElementById('docViewerContent');
    document.querySelectorAll('.js-view-doc').forEach(function(btn){
        btn.addEventListener('click', function(){
            const url = btn.getAttribute('data-file');
            const ext = url.split('.').pop().toLowerCase();
            modal.style.display = 'flex';
            content.innerHTML = '<div style="color:#888;">Loading...</div>';
            // Use fetch to get the file as blob and display
            fetch(url, { credentials: 'same-origin', cache: 'no-store' })
                .then(resp => {
                    if (!resp.ok) throw new Error('Failed to load document: ' + resp.status + ' ' + resp.statusText);
                    return resp.blob();
                })
                .then(blob => {
                    let el;
                    if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                        el = document.createElement('img');
                        el.src = URL.createObjectURL(blob);
                        el.style.maxWidth = '100%';
                        el.style.maxHeight = '100%';
                        el.style.objectFit = 'contain';
                    } else if (ext === 'pdf') {
                        el = document.createElement('iframe');
                        el.src = URL.createObjectURL(blob);
                        el.style.width = '100%';
                        el.style.height = '100%';
                    } else {
                        el = document.createElement('a');
                        el.href = url;
                        el.textContent = 'Open document';
                        el.target = '_blank';
                    }
                    content.innerHTML = '';
                    content.appendChild(el);
                })
                .catch(err => {
                    content.innerHTML = '<div style="color:red;">Error loading document: ' + err.message + '</div>';
                });
        });
    });
    modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
});
</script>
