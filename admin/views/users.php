<?php
// Bootstrap: auto load data if not pre-set by a controller script.
if (!isset($users) || !isset($stats)) {
  $controllerPath = __DIR__ . '/../controllers/users-controller.php';
  if (!file_exists($controllerPath)) {
    die('Users controller not found at: ' . htmlspecialchars($controllerPath));
  }
  require_once $controllerPath;
  // Build filters from GET
  $filters = [];
  if (!empty($_GET['q'])) $filters['q'] = trim($_GET['q']);
  if (!empty($_GET['from'])) $filters['from'] = $_GET['from'];
  if (!empty($_GET['to'])) $filters['to'] = $_GET['to'];
  // Map status to verified / banned (banned only if column exists; controller ignores if absent)
  if (!empty($_GET['status'])) {
    switch ($_GET['status']) {
      case 'active': $filters['verified'] = 1; break;
      case 'pending': $filters['verified'] = 0; break;
      case 'banned': $filters['banned'] = 1; break;
    }
  }
  // Pagination basic (optional)
  if (!empty($_GET['page'])) $filters['page'] = (int)$_GET['page'];
  if (!empty($_GET['limit'])) $filters['limit'] = (int)$_GET['limit'];

  // CSV export trigger
  if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $csv = UsersController::exportCsv($filters);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users-export.csv"');
    header('Content-Length: ' . strlen($csv));
    echo $csv; exit;
  }

  $list = UsersController::list($filters);
  $users = $list['data'];
  $stats = UsersController::stats();
}
include __DIR__ . '/../include/sidebar.php'; ?>
<div class="body-wrapper">
<?php include __DIR__ . '/../include/header.php'; ?>
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">User Monitoring</h3>
    <div>
  <a class="btn btn-sm btn-primary" id="btn-export-users" href="?export=csv"><i class="ti ti-download"></i> Export CSV</a>
    </div>
  </div>

  <?php 
    // Expected: $stats = ['total'=>0,'active'=>0,'pending'=>0,'banned'=>0]; Provided by controller.
    $stats = $stats ?? ['total'=>0,'active'=>0,'pending'=>0,'banned'=>0];
  ?>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100 border-0 bg-light-subtle">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted text-uppercase fw-semibold">Total</small>
              <div class="h4 mb-0"><?= (int)$stats['total']; ?></div>
            </div>
            <span class="avatar bg-primary-subtle text-primary"><i class="ti ti-users"></i></span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100 border-0 bg-light-subtle">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted text-uppercase fw-semibold">Active</small>
              <div class="h4 mb-0 text-success"><?= (int)$stats['active']; ?></div>
            </div>
            <span class="avatar bg-success-subtle text-success"><i class="ti ti-user-check"></i></span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100 border-0 bg-light-subtle">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted text-uppercase fw-semibold">Pending</small>
              <div class="h4 mb-0 text-warning"><?= (int)$stats['pending']; ?></div>
            </div>
            <span class="avatar bg-warning-subtle text-warning"><i class="ti ti-user-plus"></i></span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100 border-0 bg-light-subtle">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted text-uppercase fw-semibold">Banned</small>
              <div class="h4 mb-0 text-danger"><?= (int)$stats['banned']; ?></div>
            </div>
            <span class="avatar bg-danger-subtle text-danger"><i class="ti ti-user-off"></i></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <form id="user-filters" class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Search</label>
          <input type="text" name="q" id="filter-search" class="form-control form-control-sm" placeholder="Name, email or ID">
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Role</label>
          <select name="role" id="filter-role" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="user">User</option>
            <option value="shelter">Shelter</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Status</label>
          <select name="status" id="filter-status" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="banned">Banned</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Created From</label>
          <input type="date" name="from" id="filter-from" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">To</label>
            <input type="date" name="to" id="filter-to" class="form-control form-control-sm">
        </div>
        <div class="col-md-1 d-grid">
          <button type="submit" class="btn btn-sm btn-secondary"><i class="ti ti-filter"></i></button>
        </div>
      </div>
      <div class="mt-2 d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-filters">Reset</button>
        <div class="ms-auto d-flex gap-2">
          <select id="bulk-action" class="form-select form-select-sm" style="min-width:140px;">
            <option value="">Bulk Action</option>
            <option value="activate">Activate</option>
            <option value="ban">Ban</option>
            <option value="delete">Delete</option>
          </select>
          <button type="button" id="apply-bulk" class="btn btn-sm btn-primary" disabled>Apply</button>
        </div>
      </div>
    </div>
  </form>

  <?php 
    // Expected from controller: $users = [...]; Each item: ['id'=>, 'name'=>, 'email'=>, 'role'=>, 'status'=>, 'avatar'=>, 'created_at'=>, 'last_active'=>]
    $users = $users ?? [];
  ?>
  <div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive" data-simplebar style="max-height:70vh;">
      <table class="table table-sm align-middle mb-0" id="users-table">
        <thead class="table-light position-sticky top-0">
          <tr>
            <th style="width:28px;"><input type="checkbox" id="select-all"></th>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Active</th>
            <th>Created</th>
            <th style="width:80px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($users)): ?>
            <tr><td colspan="9" class="text-center text-muted py-5">No users found.</td></tr>
          <?php else: ?>
            <?php foreach($users as $u): 
              $id = (int)($u['id'] ?? 0);
              $name = htmlspecialchars($u['name'] ?? '');
              $email = htmlspecialchars($u['email'] ?? '');
              $role = htmlspecialchars($u['role'] ?? 'user');
              $status = htmlspecialchars($u['status'] ?? 'pending');
              $avatar = !empty($u['avatar']) ? htmlspecialchars($u['avatar']) : '/assets/images/profile/default.png';
              $created = htmlspecialchars($u['created_at'] ?? '');
              $lastActive = htmlspecialchars($u['last_active'] ?? '—');
            ?>
            <tr data-role="<?= strtolower($role); ?>" data-status="<?= strtolower($status); ?>">
              <td><input type="checkbox" class="row-check" value="<?= $id; ?>"></td>
              <td class="text-muted small fw-semibold">#<?= $id; ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= $avatar; ?>" class="rounded-circle" width="32" height="32" alt="avatar">
                  <div class="d-flex flex-column">
                    <span class="fw-semibold small"><?= $name; ?></span>
                    <span class="text-muted xsmall"><?= $email; ?></span>
                  </div>
                </div>
              </td>
              <td class="small"><?= $email; ?></td>
              <td><span class="badge bg-primary-subtle text-primary text-capitalize"><?= $role; ?></span></td>
              <td>
                <?php 
                  $badgeClass = 'bg-secondary-subtle text-secondary';
                  if($status === 'active') $badgeClass = 'bg-success-subtle text-success';
                  elseif($status === 'pending') $badgeClass = 'bg-warning-subtle text-warning';
                  elseif($status === 'banned') $badgeClass = 'bg-danger-subtle text-danger';
                ?>
                <span class="badge <?= $badgeClass; ?> text-capitalize"><?= $status; ?></span>
              </td>
              <td class="small text-muted"><?= $lastActive; ?></td>
              <td class="small text-muted"><?= $created; ?></td>
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-outline-secondary" title="View" data-id="<?= $id; ?>"><i class="ti ti-eye"></i></button>
                  <button type="button" class="btn btn-outline-secondary" title="Edit" data-id="<?= $id; ?>"><i class="ti ti-edit"></i></button>
                  <button type="button" class="btn btn-outline-danger" title="Delete" data-id="<?= $id; ?>"><i class="ti ti-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer py-2 d-flex align-items-center gap-3 flex-wrap">
      <div class="small text-muted" id="table-count-info"></div>
      <nav class="ms-auto">
        <!-- Pagination placeholder -->
        <ul class="pagination pagination-sm mb-0" id="pagination">
          <!-- JS can inject pages -->
        </ul>
      </nav>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</div>

<script>
(function(){
  const table = document.getElementById('users-table');
  if(!table) return;
  const searchInput = document.getElementById('filter-search');
  const roleSelect = document.getElementById('filter-role');
  const statusSelect = document.getElementById('filter-status');
  const selectAll = document.getElementById('select-all');
  const bulkAction = document.getElementById('bulk-action');
  const applyBulk = document.getElementById('apply-bulk');
  const resetBtn = document.getElementById('btn-reset-filters');
  const countInfo = document.getElementById('table-count-info');

  function normalize(v){return (v||'').toString().toLowerCase();}

  function update(){
    const q = normalize(searchInput.value);
    const r = normalize(roleSelect.value);
    const s = normalize(statusSelect.value);
    let visible = 0; let total = 0;
    table.querySelectorAll('tbody tr').forEach(tr=>{
      total++;
      if(tr.querySelector('td')?.getAttribute('colspan')) return; // skip empty row message
      const rowRole = tr.getAttribute('data-role');
      const rowStatus = tr.getAttribute('data-status');
      const text = normalize(tr.innerText);
      const matchQ = !q || text.includes(q);
      const matchR = !r || rowRole === r;
      const matchS = !s || rowStatus === s;
      const show = matchQ && matchR && matchS;
      tr.style.display = show ? '' : 'none';
      if(show) visible++;
    });
    countInfo.textContent = visible + ' of ' + (total) + ' displayed';
  }

  function updateBulkState(){
    const checked = table.querySelectorAll('tbody .row-check:checked');
    applyBulk.disabled = checked.length === 0 || !bulkAction.value;
  }

  searchInput?.addEventListener('input', update);
  roleSelect?.addEventListener('change', update);
  statusSelect?.addEventListener('change', update);
  bulkAction?.addEventListener('change', updateBulkState);
  resetBtn?.addEventListener('click', ()=>{
    searchInput.value=''; roleSelect.value=''; statusSelect.value=''; update();
  });

  selectAll?.addEventListener('change', ()=>{
    const checked = selectAll.checked;
    table.querySelectorAll('tbody .row-check').forEach(cb=>{ if(cb.closest('tr').style.display !== 'none'){ cb.checked = checked; }});
    updateBulkState();
  });
  table.addEventListener('change', e=>{ if(e.target.classList.contains('row-check')) updateBulkState(); });

  applyBulk?.addEventListener('click', ()=>{
    const action = bulkAction.value;
    if(!action) return;
    const ids = Array.from(table.querySelectorAll('tbody .row-check:checked')).map(cb=>cb.value);
    if(ids.length===0) return;
    if(!confirm('Apply "'+action+'" to '+ids.length+' selected user(s)?')) return;
    // Placeholder: send AJAX / form submission
    console.log('Bulk action', action, ids);
  });

  update();
})();
</script>
