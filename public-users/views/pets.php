<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: pets.php
 * Tables: pets, pet_photos, shelters, users (owner)
 * Expected Variables:
 *  - $filters => ['q'=>string,'species'=>string,'size'=>string,'gender'=>string,'status'=>string]
 *  - $pets => [ { 'id','name','species','breed','age','gender','size','status','location','primary_photo','shelter_name','owner_name' }, ... ]
 *  - $pagination => ['page'=>int,'per_page'=>int,'total'=>int]
 */
$pageTitle = 'Browse Pets';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Left Sidebar -->
    <div class="col-12 col-lg-3">
      <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
      <div class="card mt-3 d-none d-lg-block">
        <div class="card-body">
          <h6 class="text-muted mb-2">Quick Links</h6>
          <div class="d-grid gap-2">
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
            <a href="./shelters.php" class="btn btn-sm btn-outline-secondary">Shelters</a>
            <a href="./my_adoptions.php" class="btn btn-sm btn-outline-secondary">My Adoptions</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./create_post.php?type=pet" class="btn btn-sm btn-primary"><i class="ti ti-plus"></i> Add Pet</a>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
              <label class="form-label">Search</label>
              <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="Name or breed">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Species</label>
              <select name="species" class="form-select">
                <option value="">All</option>
                <?php foreach (['dog','cat','bird','rabbit','other'] as $s): ?>
                  <option value="<?php echo $s; ?>" <?php if(($filters['species']??'')===$s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">All</option>
                <?php foreach (['male','female','unknown'] as $g): ?>
                  <option value="<?php echo $g; ?>" <?php if(($filters['gender']??'')===$g) echo 'selected'; ?>><?php echo ucfirst($g); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label">Size</label>
              <select name="size" class="form-select">
                <option value="">All</option>
                <?php foreach (['small','medium','large','extra-large'] as $sz): ?>
                  <option value="<?php echo $sz; ?>" <?php if(($filters['size']??'')===$sz) echo 'selected'; ?>><?php echo ucfirst($sz); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <?php foreach (['available','pending','adopted'] as $st): ?>
                  <option value="<?php echo $st; ?>" <?php if(($filters['status']??'')===$st) echo 'selected'; ?>><?php echo ucfirst($st); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
              <button class="btn btn-primary"><i class="ti ti-filter"></i></button>
            </div>
          </form>
        </div>
      </div>
      <div class="row g-3">
        <?php if (empty($pets)): ?>
          <div class="col-12"><div class="text-center text-muted py-5">No pets found.</div></div>
        <?php else: foreach ($pets as $p):
          $badgeClass = ['available'=>'success','pending'=>'warning','adopted'=>'secondary','removed'=>'dark'][$p['status']] ?? 'light';
        ?>
          <div class="col-6 col-md-6">
            <div class="card h-100 pet-card">
              <div class="ratio ratio-4x3 bg-light rounded-top overflow-hidden">
                <?php if(!empty($p['primary_photo'])): ?>
                  <img src="<?php echo htmlspecialchars($p['primary_photo']); ?>" class="object-fit-cover" alt="<?php echo htmlspecialchars($p['name']); ?>">
                <?php else: ?>
                  <div class="d-flex align-items-center justify-content-center text-muted">No Photo</div>
                <?php endif; ?>
              </div>
              <div class="card-body pb-2">
                <h6 class="mb-1"><a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($p['name']); ?></a></h6>
                <div class="small text-muted mb-1"><?php echo htmlspecialchars(ucfirst($p['species'])); ?> • <?php echo htmlspecialchars($p['breed'] ?: 'Mixed'); ?></div>
                <div class="d-flex flex-wrap gap-1 small mb-2">
                  <span class="badge bg-light text-dark"><?php echo htmlspecialchars($p['age'] ?: '?'); ?></span>
                  <span class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($p['gender'])); ?></span>
                  <span class="badge bg-light text-dark"><?php echo htmlspecialchars(ucfirst($p['size'])); ?></span>
                </div>
                <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($p['status'])); ?></span>
              </div>
              <div class="card-footer bg-white border-0 pt-0 d-flex justify-content-between align-items-center">
                <small class="text-muted text-truncate" style="max-width:70%;">
                  <?php echo htmlspecialchars($p['shelter_name'] ?? $p['owner_name'] ?? ''); ?>
                </small>
                <a href="./adopt.php?pet_id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-heart"></i></a>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
      <?php if (!empty($pagination) && $pagination['total'] > $pagination['per_page']):
        $totalPages = (int)ceil($pagination['total'] / $pagination['per_page']);
        $current = (int)$pagination['page'];
      ?>
        <nav class="mt-4" aria-label="Pet pagination">
          <ul class="pagination pagination-sm">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
              <li class="page-item <?php if($i===$current) echo 'active'; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Trending Tags</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="./community.php" class="btn btn-sm btn-light border">#adoptDontShop</a>
            <a href="./community.php" class="btn btn-sm btn-light border">#rescue</a>
            <a href="./community.php" class="btn btn-sm btn-light border">#pets</a>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Tips</h6>
          <p class="small text-muted mb-0">Filter to narrow results. Click a pet card for details and adoption.</p>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
