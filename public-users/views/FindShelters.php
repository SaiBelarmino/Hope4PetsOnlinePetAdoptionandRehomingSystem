<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<?php
$pageTitle = 'Find Shelters';

// If the controller didn't provide $data (view accessed directly), load data from controller
if (!isset($data) || !is_array($data)) {
    require_once __DIR__ . '/../controllers/FindShelterController.php';
    $fetched = FindShelterController::fetchData();
    $data = ['shelters' => is_array($fetched) ? $fetched : []];

    // Determine selected shelter from query string (?id=) or default to first
    $selected = null;
    if (isset($_GET['id'])) {
        $sid = intval($_GET['id']);
        foreach ($data['shelters'] as $s) {
            if (isset($s['id']) && intval($s['id']) === $sid) {
                $selected = $s;
                break;
            }
        }
    }
    if (!$selected && count($data['shelters']) > 0) {
        $selected = $data['shelters'][0];
    }
    if ($selected) {
        $data['selectedShelter'] = $selected;
    }
}

// Choose a selected shelter if provided, otherwise use the first shelter for summary
$selectedShelter = $data['selectedShelter'] ?? ($data['shelters'][0] ?? null);
?>
<div class="container-fluid">
    <div class="row g-4 py-4">
        <!-- Left Sidebar -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>

        <!-- Center Content: searchable list -->
        <div class="col-12 col-lg-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h3 class="mb-0 h5"><?php echo htmlspecialchars($pageTitle); ?></h3>
                            <small class="text-muted">Browse shelters near you and view details.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input id="shelterSearch" type="search" class="form-control" placeholder="Search shelters...">
                            </div>
                            <select id="shelterSort" class="form-select form-select-sm">
                                <option value="all">Sort: All</option>
                                <option value="pets">Sort: Most Pets</option>
                                <option value="verified">Sort: Verified</option>
                            </select>
                        </div>
                    </div>

                    <?php if (!empty($data['shelters'])) : ?>
                    <ul id="shelterList" class="list-group list-group-flush">
                        <?php foreach ($data['shelters'] as $shelter) : ?>
                        <li class="list-group-item py-3">
                            <div class="d-flex">
                    
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($shelter['shelter_name']); ?>
                                                <?php if (!empty($shelter['is_verified'])): ?>
                                                <span class="badge bg-success ms-2">Verified</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-1 text-muted small"><?php echo htmlspecialchars($shelter['address']); ?></p>
                                            <p class="mb-0 small text-muted">Owner: <?php echo htmlspecialchars($shelter['owner_name'] ?? '—'); ?></p>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-1 small"><strong><?php echo (int)($shelter['pet_count'] ?? 0); ?></strong> pets</p>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shelterModal-<?php echo (int)$shelter['id']; ?>">View Details</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>


                        <?php endforeach; ?>
                    </ul>
                    <?php else : ?>
                    <div class="text-center py-5">
                        <p class="mb-2">No shelters found.</p>
                        <a href="/" class="btn btn-outline-secondary btn-sm">Back to Home</a>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Right Sidebar: Purpose panel -->
        <div class="col-12 col-lg-2">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Purpose & How to Use</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">This area explains the purpose of the shelter list and guides users how to proceed.</p>
                    <ol class="small mb-2">
                        <li>Browse shelters to find nearby adoption and rehoming centers.</li>
                        <li>Click "View Details" to see owner information, contact, and services.</li>
                        <li>Call the shelter directly from the details modal to arrange visits or ask questions.</li>
                    </ol>
                    <p class="small text-muted mb-2"><strong>Need help?</strong></p>
                    <p class="small text-muted mb-2">If you need assistance locating a shelter or arranging an adoption visit, contact our support team.</p>
                    <div class="d-grid gap-2">
                        <a href="mailto:support@hope4pets.local" class="btn btn-primary btn-sm">Contact Support</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="reportIssueBtn">Report an Issue</button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Before visiting, contact the shelter for visiting hours and adoption requirements. Bring valid ID and proof of address if required.</p>
                </div>
            </div>
        </div>

    </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('shelterSearch');
    const sortSelect = document.getElementById('shelterSort');
    const list = document.getElementById('shelterList');
    if (!list) return;

    // Cache original shelter data
    const originalShelters = Array.from(list.children).map(li => {
        return {
            element: li,
            name: li.querySelector('h6').textContent.trim().toLowerCase(),
            pets: parseInt(li.querySelector('.text-end strong').textContent.trim(), 10) || 0,
            verified: li.querySelector('.badge.bg-success') ? 1 : 0,
            fullName: li.querySelector('h6').textContent.trim()
        };
    });

    // Helper: sort names alphabetically, then by number if present
    function alphaNumSort(a, b) {
        function splitAlphaNum(str) {
            const match = str.match(/^([a-zA-Z\s]+)(\d+)?/);
            return match ? [match[1].trim(), match[2] ? parseInt(match[2], 10) : null] : [str, null];
        }
        const [aAlpha, aNum] = splitAlphaNum(a.fullName);
        const [bAlpha, bNum] = splitAlphaNum(b.fullName);

        const cmp = aAlpha.localeCompare(bAlpha, undefined, { sensitivity: 'base' });
        if (cmp !== 0) return cmp;
        if (aNum !== null && bNum !== null) return aNum - bNum;
        if (aNum !== null) return 1;
        if (bNum !== null) return -1;
        return 0;
    }

    function renderShelters(shelters, emptyMsg = "No shelters found.") {
        list.innerHTML = '';
        if (shelters.length === 0) {
            list.innerHTML = `<li class="list-group-item text-center py-5">${emptyMsg}</li>`;
        } else {
            shelters.forEach(s => {
                list.appendChild(s.element);
            });
        }
    }

    function filterAndSort() {
        const search = searchInput.value.trim().toLowerCase();
        let filtered = originalShelters;

        if (search.length > 0) {
            filtered = originalShelters.filter(s => s.name.startsWith(search));
        }

        const sortVal = sortSelect.value;
        if (sortVal === 'all') {
            renderShelters(filtered);
        } else if (sortVal === 'pets') {
            // Only show shelters with at least 1 pet
            const withPets = filtered.filter(s => s.pets > 0);
            if (withPets.length === 0) {
                renderShelters([], "No shelter managed a pet.");
            } else {
                withPets.sort((a, b) => b.pets - a.pets);
                renderShelters(withPets);
            }
        } else if (sortVal === 'verified') {
            const verifiedShelters = filtered.filter(s => s.verified === 1);
            if (verifiedShelters.length === 0) {
                renderShelters([], "No verified shelters found.");
            } else {
                verifiedShelters.sort(alphaNumSort);
                renderShelters(verifiedShelters);
            }
        }
    }

    searchInput.addEventListener('input', filterAndSort);
    sortSelect.addEventListener('change', filterAndSort);

    // Automatically trigger sorting when page loads and whenever sort option changes
    filterAndSort();
});
</script>