<?php
// Protect this admin view from direct URL access
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

//
// Bootstrap: auto load data if not pre-set by a controller script.
if (!isset($users) || !isset($stats)) {
  include __DIR__ . '/../../controllers/User/users-controller.php';
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
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
  <?php include dirname(__DIR__, 2) . '/header.php'; ?>
  <div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
      <h3 class="mb-0">User Monitoring</h3>
      <div>
        <a class="btn btn-sm btn-outline-primary" id="btn-export-users" href="?export=csv"><i class="ti ti-download"></i> Export CSV</a>
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
                  <button type="button" class="btn btn-outline-secondary action-btn" data-action="view" title="View" data-id="<?= $id; ?>"><i class="ti ti-eye"></i></button>
                  <button type="button" class="btn btn-outline-secondary action-btn" data-action="edit" title="Edit" data-id="<?= $id; ?>"><i class="ti ti-edit"></i></button>
                  <button type="button" class="btn btn-outline-danger action-btn" data-action="delete" title="Delete" data-id="<?= $id; ?>"><i class="ti ti-trash"></i></button>
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
<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
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
    // send AJAX to perform bulk action
    fetch('../../controllers/User/users-action.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({action:'bulk', bulk_action: action, ids: ids.join(',')})
    }).then(async r=>{
      const txt = await r.text();
      try { const res = JSON.parse(txt); if(res.success){ alert('Bulk action applied. Affected: '+(res.affected||0)); location.reload(); } else alert('Failed: '+(res.message||txt)); }
      catch(e){ alert('Sorry, something went wrong. Please try again or contact support.'); }
    }).catch(e=>alert('Request failed: '+e.message));
  });

  update();
})();
</script>


<!-- User Profile View Modal -->
<div class="modal" id="userProfileModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Profile</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column align-items-center mb-3">
          <img id="profile-avatar" src="/assets/images/profile/default.png" class="rounded-circle border mb-2" width="90" height="90" alt="avatar">
          <h5 id="profile-full_name" class="mb-0"></h5>
          <div class="text-muted" id="profile-email"></div>
        </div>
        <div class="row g-2">
          <div class="col-6"><strong>Gender:</strong> <span id="profile-gender"></span></div>
          <div class="col-6"><strong>Birthday:</strong> <span id="profile-birthday"></span></div>
          <div class="col-12"><strong>Location:</strong> <span id="profile-location"></span></div>
          <div class="col-12"><strong>Contact:</strong> <span id="profile-contact_number"></span></div>
          <div class="col-6"><strong>Verified:</strong> <span id="profile-is_verified"></span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- User Edit Modal -->
<div class="modal" id="userEditModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-center mb-3">
          <img id="u-avatar" src="/assets/images/profile/default.png" class="rounded-circle border" width="80" height="80" alt="avatar">
        </div>
        <form id="user-form">
          <input type="hidden" name="id" id="u-id">
          <div class="mb-2"><label class="form-label small">Full name</label><input class="form-control form-control-sm" name="full_name" id="u-full_name"></div>
          <div class="mb-2"><label class="form-label small">Email</label><input class="form-control form-control-sm" name="email" id="u-email"></div>
          <div class="mb-2"><label class="form-label small">Gender</label><input class="form-control form-control-sm" name="gender" id="u-gender"></div>
          <div class="mb-2"><label class="form-label small">Location</label><input class="form-control form-control-sm" name="location" id="u-location"></div>
          <div class="mb-2"><label class="form-label small">Contact</label><input class="form-control form-control-sm" name="contact_number" id="u-contact_number"></div>
          <div class="mb-2"><label class="form-label small">Birthday</label><input type="date" class="form-control form-control-sm" name="birthday" id="u-birthday"></div>
          <div class="mb-2"><label class="form-label small">Verified</label>
            <select class="form-select form-select-sm" name="is_verified" id="u-is_verified"><option value="1">Yes</option><option value="0">No</option></select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" id="save-user">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Modal helpers (minimal, no dependency on bootstrap JS)
  function showModal(id){
    const m = document.getElementById(id); if(!m) return; m.style.display='block';
  }
  function hideModal(id){
    const m = document.getElementById(id); if(!m) return; m.style.display='none';
  }

  const table = document.getElementById('users-table');
  if(!table) return;
  table.addEventListener('click', e=>{
    const btn = e.target.closest('.action-btn');
    if(!btn) return;
    const action = btn.getAttribute('data-action');
    const id = btn.getAttribute('data-id');
    if(!action || !id) return;
    if(action === 'view'){
        fetch('../../controllers/User/users-action.php?action=get&id='+encodeURIComponent(id), {credentials: 'same-origin'})
          .then(async r=>{
            const txt = await r.text();
            try {
              const res = JSON.parse(txt);
              if(!res.success){ alert(res.message||'Failed to load'); return; }
              const d = res.data;
              // Fill profile modal fields
              document.getElementById('profile-avatar').src = d.avatar || '/assets/images/profile/default.png';
              document.getElementById('profile-full_name').textContent = d.full_name || '';
              document.getElementById('profile-email').textContent = d.email || '';
              document.getElementById('profile-gender').textContent = d.gender || '';
              document.getElementById('profile-location').textContent = d.location || '';
              document.getElementById('profile-contact_number').textContent = d.contact_number || '';
              document.getElementById('profile-birthday').textContent = d.birthday || '';
              document.getElementById('profile-is_verified').textContent = d.is_verified ? 'Yes' : 'No';
              showModal('userProfileModal');
            } catch(e) {
              alert('Unexpected response: '+txt);
            }
          }).catch(e=>alert('Request failed: '+e.message));
    } else if(action === 'edit'){
        fetch('../../controllers/User/users-action.php?action=get&id='+encodeURIComponent(id), {credentials: 'same-origin'})
          .then(async r=>{
            const txt = await r.text();
            try {
              const res = JSON.parse(txt);
              if(!res.success){ alert(res.message||'Failed to load'); return; }
              const d = res.data;
              document.getElementById('u-id').value = d.id;
              document.getElementById('u-full_name').value = d.full_name || '';
              document.getElementById('u-email').value = d.email || '';
              document.getElementById('u-gender').value = d.gender || '';
              document.getElementById('u-location').value = d.location || '';
              document.getElementById('u-contact_number').value = d.contact_number || '';
              document.getElementById('u-birthday').value = d.birthday || '';
              document.getElementById('u-is_verified').value = d.is_verified ? '1' : '0';
              var avatar = d.avatar || '/assets/images/profile/default.png';
              document.getElementById('u-avatar').src = avatar;
              showModal('userEditModal');
            } catch(e) {
              alert('Unexpected response: '+txt);
            }
          }).catch(e=>alert('Request failed: '+e.message));
    } else if(action === 'delete'){
      if(!confirm('Delete user #'+id+'?')) return;
      fetch('../../controllers/User/users-action.php', {
        method:'POST', credentials: 'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'delete', id: id})
      }).then(async r=>{
        const txt = await r.text();
        try {
          const res = JSON.parse(txt);
          if(res.success){
            alert('Deleted');
            // Remove the row from the table without reloading
            const row = btn.closest('tr');
            if(row) row.remove();
            // Optionally update the count info
            if(typeof update === 'function') update();
          } else {
            alert('Failed: '+(res.message||txt));
          }
        } catch(e){
          // Show backend response for debugging
          alert('Sorry, something went wrong.\nResponse: '+txt);
        }
      }).catch(e=>alert('Request failed: '+e.message));
    }
  });


  document.getElementById('save-user')?.addEventListener('click', ()=>{
    const form = document.getElementById('user-form');
    const data = new FormData(form);
    data.append('action','update');
    fetch('../../controllers/User/users-action.php', {
      method:'POST', credentials: 'same-origin', body: data
    }).then(async r=>{
      const txt = await r.text();
      try { const res = JSON.parse(txt); if(res.success){ alert('Saved'); hideModal('userEditModal'); location.reload(); } else alert('Failed to save: '+(res.message||txt)); } catch(e){ alert('Sorry, something went wrong. Please try again or contact support.'); }
    }).catch(e=>alert('Request failed: '+e.message));
  });

  // Close modal when clicking close buttons
  document.querySelectorAll('[data-dismiss="modal"]').forEach(btn=>btn.addEventListener('click', ()=>{
    hideModal('userProfileModal');
    hideModal('userEditModal');
  }));
})();
</script>
