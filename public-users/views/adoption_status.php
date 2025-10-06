<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: adoption_status.php
 * Table: adoptions, pets
 * Expected Variables:
 *  - $adoption => ['id','status','created_at','reviewed_by','reviewed_at','pet_name','pet_id']
 *  - $timeline => [ ['label','time','active'=>bool,'done'=>bool], ... ] (optional pre-computed)
 */
$pageTitle = 'Adoption Status';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./my_adoptions.php" class="btn btn-sm btn-outline-secondary">My Adoptions</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./my_adoptions.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Back</a>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <?php if (empty($adoption)): ?>
            <div class="alert alert-danger mb-0">Adoption request not found.</div>
          <?php else: ?>
            <h5 class="mb-2">Pet: <a href="./pet_view.php?id=<?php echo (int)$adoption['pet_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($adoption['pet_name']); ?></a></h5>
            <p class="small mb-1"><strong>Application Date:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($adoption['created_at']))); ?></p>
            <p class="small mb-1"><strong>Current Status:</strong> <span class="badge bg-<?php echo ['applied'=>'info','approved'=>'success','denied'=>'danger','completed'=>'secondary','cancelled'=>'dark'][$adoption['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($adoption['status'])); ?></span></p>
            <?php if (!empty($adoption['reviewed_by'])): ?>
              <p class="small mb-1"><strong>Reviewed:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($adoption['reviewed_at']))); ?> by Admin #<?php echo (int)$adoption['reviewed_by']; ?></p>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Progress</h6></div>
        <div class="card-body">
      <div class="card mb-3">
        <div class="card-body">
          <?php if (empty($adoption)): ?>
            <div class="alert alert-danger mb-0">Adoption request not found.</div>
          <?php else: ?>
            <h5 class="mb-2">Pet: <a href="./pet_view.php?id=<?php echo (int)$adoption['pet_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($adoption['pet_name']); ?></a></h5>
            <p class="small mb-1"><strong>Application Date:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($adoption['created_at']))); ?></p>
            <p class="small mb-1"><strong>Current Status:</strong> <span class="badge bg-<?php echo ['applied'=>'info','approved'=>'success','denied'=>'danger','completed'=>'secondary','cancelled'=>'dark'][$adoption['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($adoption['status'])); ?></span></p>
            <?php if (!empty($adoption['reviewed_by'])): ?>
              <p class="small mb-1"><strong>Reviewed:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($adoption['reviewed_at']))); ?> by Admin #<?php echo (int)$adoption['reviewed_by']; ?></p>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
          <?php 
          if (empty($timeline)) {
            $status = $adoption['status'] ?? 'applied';
            $flow = ['applied','approved','completed'];
            if ($status==='denied') $flow = ['applied','denied'];
            if ($status==='cancelled') $flow = ['applied','cancelled'];
            $timeline = [];
            foreach($flow as $st){
              $timeline[] = [
                'label'=>ucfirst($st),
                'time'=>($st===$status)?($adoption['reviewed_at']??$adoption['created_at']??null):null,
                'done'=> array_search($st,$flow) <= array_search($status,$flow),
                'active'=> $st===$status
              ];
            }
          }
          ?>
          <ol class="list-unstyled d-flex flex-wrap gap-3 m-0 adoption-progress">
            <?php foreach ($timeline as $step): ?>
              <li class="d-flex flex-column align-items-start" style="min-width:120px;">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge rounded-circle <?php echo $step['done']?'bg-success':'bg-light text-dark'; ?>" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                    <?php echo $step['done'] ? '<i class=\'ti ti-check\'></i>' : '<i class=\'ti ti-dots\'></i>'; ?>
                  </span>
                  <strong class="small mb-0 <?php echo $step['active']?'text-primary':''; ?>"><?php echo htmlspecialchars($step['label']); ?></strong>
                </div>
                <span class="small text-muted ms-5"><?php echo $step['time']? htmlspecialchars(date('M d', strtotime($step['time']))) : '—'; ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Actions</h6></div>
        <div class="card-body small">
          <?php if (!empty($adoption) && in_array($adoption['status'], ['applied','approved'])): ?>
            <form action="../controllers/adoption-status-controller.php" method="post" class="d-flex gap-2 flex-wrap mb-3">
              <input type="hidden" name="adoption_id" value="<?php echo (int)$adoption['id']; ?>">
              <?php if ($adoption['status']==='applied'): ?>
                <button name="action" value="cancel" class="btn btn-sm btn-outline-danger">Cancel</button>
              <?php elseif ($adoption['status']==='approved'): ?>
                <button name="action" value="confirm_completion" class="btn btn-sm btn-success">Confirm Completion</button>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <p class="text-muted mb-0">No available actions.</p>
          <?php endif; ?>
          <p class="small text-muted mb-0">Need help? Contact support.</p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-light border">Find Pets</a>
            <a href="./my_adoptions.php" class="btn btn-sm btn-light border">My Adoptions</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
