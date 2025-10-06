<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: shelters.php
 * Tables: shelters, shelter_documents, users (owner), donations (aggregate optional)
 * Expected Variables:
 *  - $filters => ['q'=>string]
 *  - $shelters => [ {'id','shelter_name','address','contact_number','verified_badge','pet_count','total_donations'}, ... ]
 *  - $pagination => ['page','per_page','total']
 */
$pageTitle = 'Shelters';
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
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Pets</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <a href="./register_shelter.php" class="btn btn-sm btn-primary"><i class="ti ti-building-community"></i> Register Shelter</a>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
              <label class="form-label">Search</label>
              <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="Shelter name or address">
            </div>
            <div class="col-6 col-md-4 d-grid">
              <button class="btn btn-primary"><i class="ti ti-search"></i></button>
            </div>
          </form>
        </div>
      </div>
      <div class="row g-3">
    <?php if (empty($shelters)): ?>
      <div class="col-12"><div class="text-center text-muted py-5">No shelters found.</div></div>
    <?php else: foreach ($shelters as $s): ?>
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="mb-0"><a href="./shelter_view.php?id=<?php echo (int)$s['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($s['shelter_name']); ?></a> <?php if(!empty($s['verified_badge'])): ?><span class="badge bg-primary">✔</span><?php endif; ?></h6>
              <span class="badge bg-light text-dark">Pets: <?php echo (int)($s['pet_count'] ?? 0); ?></span>
            </div>
            <p class="small text-muted mb-2"><?php echo htmlspecialchars($s['address'] ?: 'No address'); ?></p>
            <p class="small mb-2"><strong>Contact:</strong> <?php echo htmlspecialchars($s['contact_number'] ?: '—'); ?></p>
            <div class="mt-auto d-flex justify-content-between align-items-center">
              <small class="text-muted">Donations: ₱<?php echo number_format((float)($s['total_donations'] ?? 0),2); ?></small>
              <a href="./shelter_view.php?id=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
      </div>
      <?php if(!empty($pagination) && $pagination['total']>$pagination['per_page']): $tp = (int)ceil($pagination['total']/$pagination['per_page']); $cur=(int)$pagination['page']; ?>
        <nav class="mt-4" aria-label="Shelter pagination">
          <ul class="pagination pagination-sm">
            <?php for($i=1;$i<=$tp;$i++): ?>
              <li class="page-item <?php if($i===$cur) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Tips</h6>
          <p class="small text-muted mb-2">Look for the verified badge to support established shelters.</p>
          <a href="./donate.php" class="btn btn-sm btn-outline-primary w-100">Donate</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-light border">Browse Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
