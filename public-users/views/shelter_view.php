<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: shelter_view.php
 * Tables: shelters, pets, donations (aggregate), shelter_documents (status)
 * Expected Variables:
 *  - $shelter => ['id','shelter_name','address','contact_number','verified_badge','created_at']
 *  - $pets => subset latest pets
 *  - $totals => ['donations'=>float,'pets'=>int]
 *  - $documents (optional) => list with statuses
 */
$pageTitle = 'Shelter Details';
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
            <a href="./shelters.php" class="btn btn-sm btn-outline-secondary">All Shelters</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Pets</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($shelter['shelter_name'] ?? $pageTitle); ?> <?php if(!empty($shelter['verified_badge'])): ?><span class="badge bg-primary">✔</span><?php endif; ?></h3>
        <div class="d-flex gap-2">
          <a href="./shelters.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Back</a>
          <a href="./donate.php?shelter_id=<?php echo (int)($shelter['id'] ?? 0); ?>" class="btn btn-sm btn-primary"><i class="ti ti-heart-handshake"></i> Donate</a>
        </div>
      </div>
      <div class="card h-100 mb-3">
        <div class="card-body">
          <p class="small mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($shelter['address'] ?? '—'); ?></p>
          <p class="small mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($shelter['contact_number'] ?? '—'); ?></p>
          <p class="small mb-1"><strong>Since:</strong> <?php echo !empty($shelter['created_at'])? htmlspecialchars(date('M Y', strtotime($shelter['created_at']))):'—'; ?></p>
          <hr>
          <div class="row g-2 text-center small">
            <div class="col-6">
              <div class="p-2 bg-light rounded">
                <div class="text-muted">Pets</div><strong><?php echo (int)($totals['pets'] ?? 0); ?></strong>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2 bg-light rounded">
                <div class="text-muted">Donations</div><strong>₱<?php echo number_format((float)($totals['donations'] ?? 0),2); ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php if(!empty($documents)): ?>
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Documents</h6></div>
        <div class="card-body small">
          <ul class="list-unstyled mb-0">
            <?php foreach($documents as $d): ?>
              <li class="mb-1 d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($d['doc_type']); ?></span>
                <span class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$d['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Recent Pets</h6></div>
        <div class="card-body">
          <div class="row g-2">
            <?php if(empty($pets)): ?>
              <div class="col-12 text-muted small">No pets listed.</div>
            <?php else: foreach($pets as $p): ?>
              <div class="col-6">
                <a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-4x3 rounded bg-light overflow-hidden mb-1">
                    <?php if(!empty($p['primary_photo'])): ?><img src="<?php echo htmlspecialchars($p['primary_photo']); ?>" class="object-fit-cover" alt="Pet"><?php endif; ?>
                  </div>
                  <div class="small fw-semibold text-truncate"><?php echo htmlspecialchars($p['name']); ?></div>
                </a>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./donate.php?shelter_id=<?php echo (int)($shelter['id'] ?? 0); ?>" class="btn btn-sm btn-light border">Donate</a>
            <a href="./shelters.php" class="btn btn-sm btn-light border">All Shelters</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div></div>
      <div class="card h-100">
        <div class="card-body">
          <p class="small mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($shelter['address'] ?? '—'); ?></p>
          <p class="small mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($shelter['contact_number'] ?? '—'); ?></p>
          <p class="small mb-1"><strong>Since:</strong> <?php echo !empty($shelter['created_at'])? htmlspecialchars(date('M Y', strtotime($shelter['created_at']))):'—'; ?></p>
          <hr>
          <div class="row g-2 text-center small">
            <div class="col-6">
              <div class="p-2 bg-light rounded">
                <div class="text-muted">Pets</div><strong><?php echo (int)($totals['pets'] ?? 0); ?></strong>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2 bg-light rounded">
                <div class="text-muted">Donations</div><strong>₱<?php echo number_format((float)($totals['donations'] ?? 0),2); ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8">
      <div class="card mb-3">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Recent Pets</h6></div>
        <div class="card-body">
          <div class="row g-2">
            <?php if(empty($pets)): ?>
              <div class="col-12 text-muted small">No pets listed.</div>
            <?php else: foreach($pets as $p): ?>
              <div class="col-6 col-md-4">
                <a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none">
                  <div class="ratio ratio-4x3 rounded bg-light overflow-hidden mb-1">
                    <?php if(!empty($p['primary_photo'])): ?><img src="<?php echo htmlspecialchars($p['primary_photo']); ?>" class="object-fit-cover" alt="Pet"><?php endif; ?>
                  </div>
                  <div class="small fw-semibold text-truncate"><?php echo htmlspecialchars($p['name']); ?></div>
                  <div class="small text-muted text-truncate"><?php echo htmlspecialchars(ucfirst($p['species'])); ?></div>
                </a>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
      <?php if(!empty($documents)): ?>
      <div class="card">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Documents</h6></div>
        <div class="card-body small">
          <ul class="list-unstyled mb-0">
            <?php foreach($documents as $d): ?>
              <li class="mb-1 d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($d['doc_type']); ?></span>
                <span class="badge bg-<?php echo ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$d['status']] ?? 'light'; ?>"><?php echo htmlspecialchars(ucfirst($d['status'])); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
