<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$controllerPath = dirname(__DIR__, 2) . '/controllers/Shelter/shelters-controller.php';
if (file_exists($controllerPath)) {
    include $controllerPath;
} else {
    throw new RuntimeException('Shelter controller not found: ' . $controllerPath);
}
$shelters = ShelterVerificationRequestsController::listSheltersWithOwnerAndCount();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>

<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Shelters</h3>
        </div>

    <?php if (empty($shelters)): ?>
        <div class="alert alert-info">No shelters found.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($shelters as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($s['shelter_name'] ?? '–') ?></h5>
                                    <?php if (!empty($s['is_verified'])): ?>
                                        <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unverified</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewProfile(<?= intval($s['id']) ?>)">View Profile</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewPets(<?= intval($s['id']) ?>)">View Pets</button>
                                </div>
                            </div>
                            <p class="mb-1 mt-2"><strong>Owner:</strong> <?= htmlspecialchars($s['owner_name'] ?? '-') ?></p>
                            <p class="mb-0"><strong>Pets:</strong> <?= intval($s['pet_count'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Shelter Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="profileModalBody">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>

<!-- Pets Modal -->
<div class="modal fade" id="petsModal" tabindex="-1" aria-labelledby="petsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="petsModalLabel">Shelter Pets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="petsModalBody">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
function viewProfile(shelterId) {
    const modal = new bootstrap.Modal(document.getElementById('profileModal'));
    const body = document.getElementById('profileModalBody');
    body.innerHTML = '<div class="text-center">Loading...</div>';
    modal.show();

    fetch(`/admin/ajax/get-shelter-profile.php?id=${shelterId}`)  // Adjust URL to your AJAX endpoint
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            body.innerHTML = `
                <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                <p><strong>Owner:</strong> ${data.owner || 'N/A'}</p>
                <p><strong>Verified:</strong> ${data.verified ? 'Yes' : 'No'}</p>
                <!-- Add more fields as needed -->
            `;
        })
        .catch(error => {
            body.innerHTML = '<div class="alert alert-danger">Error loading profile. Please try again.</div>';
            console.error('Error:', error);
        });
}

function viewPets(shelterId) {
    const modal = new bootstrap.Modal(document.getElementById('petsModal'));
    const body = document.getElementById('petsModalBody');
    body.innerHTML = '<div class="text-center">Loading...</div>';
    modal.show();

    fetch(`/admin/ajax/get-shelter-pets.php?id=${shelterId}`)  // Adjust URL to your AJAX endpoint
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.length === 0) {
                body.innerHTML = '<p>No pets found.</p>';
            } else {
                let html = '<ul class="list-group">';
                data.forEach(pet => {
                    html += `<li class="list-group-item">${pet.name || 'Unnamed'} - ${pet.type || 'Unknown'}</li>`;
                });
                html += '</ul>';
                body.innerHTML = html;
            }
        })
        .catch(error => {
            body.innerHTML = '<div class="alert alert-danger">Error loading pets. Please try again.</div>';
            console.error('Error:', error);
        });
}
</script>

<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
