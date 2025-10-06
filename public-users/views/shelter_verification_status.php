<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: shelter_verification_status.php
 * Tables: shelter_documents, shelters
 * Expected Variables:
 *  - $requirements => [ {'key','label','status','uploaded_at','reviewed_at'}, ... ]
 *  - $shelter => basic info
 */
$pageTitle = 'Shelter Verification Status';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <a href="./my_shelter.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Back</a>
  </div>
  <div class="card mb-3"><div class="card-body">
    <p class="small mb-1"><strong>Shelter:</strong> <?php echo htmlspecialchars($shelter['shelter_name'] ?? ''); ?></p>
    <p class="small mb-0"><strong>Verified:</strong> <?php echo !empty($shelter['verified_badge'])? 'Yes' : 'No'; ?></p>
  </div></div>
  <div class="card"><div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Requirements</h6></div><div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Requirement</th><th>Status</th><th>Uploaded</th><th>Reviewed</th><th></th></tr></thead>
        <tbody>
          <?php if(empty($requirements)): ?><tr><td colspan="5" class="text-center text-muted py-4">No documents.</td></tr><?php else: foreach($requirements as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['label']); ?></td>
              <td><span class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$r['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($r['status'])); ?></span></td>
              <td><span class="small text-muted"><?php echo $r['uploaded_at']? htmlspecialchars(date('M d', strtotime($r['uploaded_at']))):'—'; ?></span></td>
              <td><span class="small text-muted"><?php echo $r['reviewed_at']? htmlspecialchars(date('M d', strtotime($r['reviewed_at']))):'—'; ?></span></td>
              <td class="text-end"><a href="./upload_shelter_documents.php#<?php echo urlencode($r['key']); ?>" class="btn btn-sm btn-outline-secondary">Update</a></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div></div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
