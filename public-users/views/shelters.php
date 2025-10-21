<?php 
// Set page title for header include
$pageTitle = 'Find Shelters';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';
?>
<div class="container-fluid py-4">
  <div class="row g-3">
	<?php include __DIR__ . '/../include/shortcut-button.php'; ?>
	<div class="col-12 col-lg-8">
	  <div class="card mb-3">
		<div class="card-body">
		  <h2 class="h4 mb-3"><i class="ti ti-building-community text-info me-2"></i>Find Animal Shelters</h2>
		  <div class="row mb-3">
			<div class="col-md-8">
			  <input type="text" id="shelterSearch" class="form-control" placeholder="Search shelters by name or city...">
			</div>
		  </div>
		  <div id="shelterList" class="row g-3"></div>
		  <div id="noResults" class="alert alert-info d-none">No shelters found.</div>
		</div>
	  </div>
	</div>
  </div>
</div>

<!-- Shelter Details Modal -->
<div class="modal fade" id="shelterModal" tabindex="-1" aria-labelledby="shelterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="shelterModalLabel">Shelter Details</h5>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body" id="shelterModalBody">
		<div class="text-center">Loading...</div>
	  </div>
	</div>
  </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script>
let shelters = [];
let filteredShelters = [];
function renderShelters(list) {
	const container = document.getElementById('shelterList');
	const noResults = document.getElementById('noResults');
	container.innerHTML = '';
	if (!list.length) {
		noResults.classList.remove('d-none');
		return;
	}
	noResults.classList.add('d-none');
	list.forEach(s => {
		const card = document.createElement('div');
		card.className = 'col-md-6';
		card.innerHTML = `
			<div class="card h-100 shadow-sm">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-start">
						<div>
							<h5 class="card-title mb-1">${s.shelter_name ? escapeHtml(s.shelter_name) : '–'}</h5>
							${s.is_verified == 1 ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-secondary">Unverified</span>'}
							${(s.is_verified != 1 && s.is_owner == 1) ? `
								<div class="mt-2">
									<div class="small text-muted">This is your shelter <strong>(pending verification)</strong></div>
									<div class="mt-2">
										<a href="/public-users/views/ShelterManagement.php" class="btn btn-sm btn-primary">Manage shelter</a>
										<a href="/public-users/views/ShelterRegistration.php" class="btn btn-sm btn-outline-secondary ms-2">Edit details</a>
									</div>
								</div>
							` : ''}
						</div>
						<div class="text-end">
							<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewShelter(${s.id})">View Details</button>
						</div>
					</div>
					<p class="mb-1 mt-2"><strong>Owner:</strong> ${escapeHtml(s.owner_name || '-') }</p>
					<p class="mb-1"><strong>Address:</strong> ${escapeHtml(s.address || '-') }</p>
					<p class="mb-0"><strong>Pets:</strong> ${parseInt(s.pet_count || 0)}</p>
				</div>
			</div>
		`;
		container.appendChild(card);
	});
}
function escapeHtml(text) {
	return text ? text.replace(/[&<>"']/g, function(m) {
		return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
	}) : '';
}
function viewShelter(id) {
	const shelter = shelters.find(s => s.id == id);
	const modal = new bootstrap.Modal(document.getElementById('shelterModal'));
	const body = document.getElementById('shelterModalBody');
	if (!shelter) {
		body.innerHTML = '<div class="alert alert-danger">Shelter not found.</div>';
		modal.show();
		return;
	}
	body.innerHTML = `
		<h4>${escapeHtml(shelter.shelter_name)}</h4>
		<p><strong>Owner:</strong> ${escapeHtml(shelter.owner_name || '-') }</p>
		<p><strong>Address:</strong> ${escapeHtml(shelter.address || '-') }</p>
		<p><strong>Contact:</strong> ${escapeHtml(shelter.contact_number || '-') }</p>
		<p><strong>Verified:</strong> ${shelter.is_verified == 1 ? 'Yes' : 'No'}</p>
		<p><strong>Number of Pets:</strong> ${parseInt(shelter.pet_count || 0)}</p>
		<hr />
		<div id="shelterPets">
			<div class="text-center text-muted"><span class="spinner-border spinner-border-sm"></span> Loading pets...</div>
		</div>
	`;
	modal.show();
	// Fetch pets for this shelter (use relative path and include credentials so cookies/session are sent)
	fetch('../controllers/ShelterController.php?pets=1&shelter_id=' + encodeURIComponent(id), { credentials: 'same-origin' })
		.then(r => r.json())
		.then(pets => {
			const petsDiv = document.getElementById('shelterPets');
			if (!pets.length) {
				petsDiv.innerHTML = '<div class="alert alert-info">No available pets at this shelter.</div>';
				return;
			}
			let html = '<div class="row g-2">';
			pets.forEach(pet => {
				html += `
					<div class="col-12 col-md-6">
						<div class="card mb-2 h-100">
							<div class="row g-0 align-items-center">
								<div class="col-4">
									<img src="${pet.photo ? escapeHtml(pet.photo) : '../../assets/images/placeholder.png'}" alt="Pet photo" class="img-fluid rounded-start" style="object-fit:cover;min-height:70px;max-height:90px;width:100%;background:#f6f6f6;">
								</div>
								<div class="col-8">
									<div class="card-body py-2 px-2">
										<h6 class="card-title mb-1">${escapeHtml(pet.name || 'Unnamed')}</h6>
										<div class="small text-muted mb-1">${escapeHtml(pet.type || '')} ${pet.breed ? '• ' + escapeHtml(pet.breed) : ''}</div>
										<div class="small">${pet.age ? 'Age: ' + escapeHtml(pet.age) : ''} ${pet.gender ? '• ' + escapeHtml(pet.gender) : ''}</div>
										<div class="small text-truncate">${pet.description ? escapeHtml(pet.description) : ''}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				`;
			});
			html += '</div>';
			petsDiv.innerHTML = html;
		})
		.catch(() => {
			const petsDiv = document.getElementById('shelterPets');
			petsDiv.innerHTML = '<div class="alert alert-danger">Failed to load pets for this shelter.</div>';
		});
}
function filterShelters() {
	const q = document.getElementById('shelterSearch').value.trim().toLowerCase();
	if (!q) {
		filteredShelters = shelters;
	} else {
		filteredShelters = shelters.filter(s =>
			(s.shelter_name && s.shelter_name.toLowerCase().includes(q)) ||
			(s.address && s.address.toLowerCase().includes(q))
		);
	}
	renderShelters(filteredShelters);
}
document.getElementById('shelterSearch').addEventListener('input', filterShelters);
// Load shelters (use relative path and include credentials so server can recognize current user)
fetch('../controllers/ShelterController.php', { credentials: 'same-origin' })
	.then(r => r.json())
	.then(data => {
		shelters = data;
		filteredShelters = data;
		renderShelters(data);
	})
	.catch(() => {
		document.getElementById('shelterList').innerHTML = '<div class="alert alert-danger">Failed to load shelters.</div>';
	});
</script>
