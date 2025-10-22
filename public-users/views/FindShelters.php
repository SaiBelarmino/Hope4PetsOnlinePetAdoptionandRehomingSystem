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
                                <option value="name">Sort: Name</option>
                                <option value="pets">Sort: Most Pets</option>
                                <option value="verified">Sort: Verified</option>
                            </select>
                        </div>
                    </div>

                    <?php if (isset($data['shelters']) && is_array($data['shelters']) && count($data['shelters']) > 0) : ?>
                    <ul id="shelterList" class="list-group list-group-flush">
                        <?php foreach ($data['shelters'] as $shelter) : ?>
                        <li class="list-group-item py-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <?php $img = $shelter['photo'] ?? $shelter['image'] ?? ''; ?>
                                    <div class="avatar rounded bg-light" style="width:64px;height:64px;overflow:hidden;">
                                        <?php if (!empty($img)): ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($shelter['shelter_name']); ?>" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted small">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($shelter['shelter_name']); ?>
                                                <?php if (isset($shelter['is_verified']) && intval($shelter['is_verified'])===1): ?>
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

                        <!-- Modal for shelter (professional layout) -->
                        <div class="modal fade" id="shelterModal-<?php echo (int)$shelter['id']; ?>" tabindex="-1" aria-labelledby="shelterModalLabel-<?php echo (int)$shelter['id']; ?>" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="shelterModalLabel-<?php echo (int)$shelter['id']; ?>"><?php echo htmlspecialchars($shelter['shelter_name'] ?? ''); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-5 mb-3 mb-md-0">
                                        <?php if (!empty($img)): ?>
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($shelter['shelter_name']); ?>" class="img-fluid rounded">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:200px;">No image available</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($shelter['address'] ?? '—'); ?></p>
                                        <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($shelter['contact_number'] ?? '—'); ?></p>
                                        <p class="mb-1"><strong>Owner:</strong> <?php echo htmlspecialchars($shelter['owner_name'] ?? '—'); ?></p>
                                        <p class="mb-1"><strong>Verified:</strong> <?php echo (isset($shelter['is_verified']) && intval($shelter['is_verified'])===1) ? 'Yes' : 'No'; ?></p>
                                        <p class="mb-1"><strong>Pets Available:</strong> <?php echo (int)($shelter['pet_count'] ?? 0); ?></p>
                                        <?php if (!empty($shelter['description'] ?? '') || !empty($shelter['notes'] ?? '')): ?>
                                        <hr>
                                        <p class="mb-0 small text-muted"><?php echo nl2br(htmlspecialchars($shelter['description'] ?? $shelter['notes'] ?? '—')); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <?php if (!empty($shelter['contact_number'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($shelter['contact_number']); ?>" class="btn btn-success">Call</a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>

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