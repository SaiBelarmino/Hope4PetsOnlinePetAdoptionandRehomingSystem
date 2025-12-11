<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user'])) {
    header('Location: ../user-authentication/authentication-login.php');
    exit;
}
require_once __DIR__ . '/../controllers/PetController.php';

// --- Fetch species and breeds for dropdowns ---
$speciesList = PetController::getSpeciesList();
$breedList = [];
if (!empty($_GET['species'])) {
    $breedList = PetController::getBreedList($_GET['species']);
}

$pageTitle = 'Browse Pets';

// --- Build filter array from GET ---
$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'species' => $_GET['species'] ?? '',
    'breed' => $_GET['breed'] ?? '',
    'age' => $_GET['age'] ?? [],
    'size' => $_GET['size'] ?? [],
    'gender' => $_GET['gender'] ?? '',
    'activity_level' => $_GET['activity_level'] ?? '',
    'vaccine_status' => $_GET['vaccine_status'] ?? '',
    'availability' => $_GET['availability'] ?? '',
];

$pets = PetController::filterAvailablePets($filters);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid">
    <?php
    // show flash via $.notify if any
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $flash = $_SESSION['flash'] ?? null;
    if ($flash) {
        $msg = $flash['message'] ?? '';
        // map bootstrap 'danger' to notify-friendly 'error'
        $type = (($flash['type'] ?? 'success') === 'success') ? 'success' : 'error';
        // use json_encode to safely embed the message and type into JS
        echo '<script>
        document.addEventListener("DOMContentLoaded", function(){ 
            if (typeof $ !== "undefined" && typeof $.notify === "function") {
                $.notify(' . json_encode($msg) . ', ' . json_encode($type) . ');
            } else {
                console.warn("$.notify is not available.");
            }
        });
        </script>';
        unset($_SESSION['flash']);
    }
    ?>
    <div class="row g-3 py-4">
        <!-- Shortcut Buttons -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>

        <div class="col-12 col-lg-6"
            style="max-height:862px; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; -ms-overflow-style:none;">
            <div class="row g-3">
                <!-- Search Bar Start -->
                    <div class="col-12 mb-2">
                        <form method="get" action="" class="input-group input-group-sm" style="max-width:100%;">
                            <input type="text" class="form-control" name="search" placeholder="Search pets..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                    </div>
                        <!-- Search Bar End -->

                <!-- Filter By: Start -->
                <form method="get" action="" class="card mb-2" style="max-width: 100%; border: none; background: transparent;">
                    <div class="card-body py-2 px-0">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span class="fw-bold me-2">Filter By:</span>
                            
                            <!-- Species -->
                            <div class="d-flex align-items-center gap-1">
                                <span title="Dog"><i class="ti ti-dog"></i></span>
                                <span title="Cat"><i class="ti ti-cat"></i></span>
                                <select class="form-select form-select-sm" style="width:110px;" name="species" id="filter-species">
                                    <option value="">Species</option>
                                    <?php foreach ($speciesList as $sp): ?>
                                        <option value="<?php echo htmlspecialchars($sp); ?>" <?php echo ($filters['species'] == $sp) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($sp); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Breed -->
                            <select class="form-select form-select-sm" style="width:120px;" name="breed" id="filter-breed" <?php echo empty($filters['species']) ? 'disabled' : ''; ?>>
                                <option value="">Breed</option>
                                <?php foreach ($breedList as $br): ?>
                                    <option value="<?php echo htmlspecialchars($br); ?>" <?php echo ($filters['breed'] == $br) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($br); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Gender (dropdown, beside breed) -->
                            <select class="form-select form-select-sm" style="width:100px;" name="gender" id="filter-gender">
                                <option value="">Gender</option>
                                <option value="male" <?php echo ($filters['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($filters['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                            </select>

                            <!-- Age -->
                            <div class="d-flex align-items-center gap-1">
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="age[]" value="puppy" <?php echo in_array('puppy', $filters['age']) ? 'checked' : ''; ?>> Puppy/Kitten</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="age[]" value="young" <?php echo in_array('young', $filters['age']) ? 'checked' : ''; ?>> Young (1-3y)</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="age[]" value="adult" <?php echo in_array('adult', $filters['age']) ? 'checked' : ''; ?>> Adult (3-8y)</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="age[]" value="senior" <?php echo in_array('senior', $filters['age']) ? 'checked' : ''; ?>> Senior (8+y)</label>
                            </div>

                            <!-- Size -->
                            <div class="d-flex align-items-center gap-1">
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="size[]" value="small" <?php echo in_array('small', $filters['size']) ? 'checked' : ''; ?>> Small</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="size[]" value="medium" <?php echo in_array('medium', $filters['size']) ? 'checked' : ''; ?>> Medium</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="size[]" value="large" <?php echo in_array('large', $filters['size']) ? 'checked' : ''; ?>> Large</label>
                                <label class="form-check-label small"><input type="checkbox" class="form-check-input" name="size[]" value="xlarge" <?php echo in_array('xlarge', $filters['size']) ? 'checked' : ''; ?>> X-Large</label>
                            </div>

                            <!-- Advanced Filters Toggle -->
                            <button type="button" class="btn btn-link btn-sm px-2" id="toggle-advanced-filters">Show Advanced Filters</button>
                        </div>

                        <!-- Advanced Filters (hidden by default) -->
                        <div id="advanced-filters" class="mt-2 d-none">
                            <div class="d-flex flex-wrap gap-2">
                                <!-- Vaccination Status Dropdown -->
                                <select class="form-select form-select-sm" style="width:170px;" name="vaccine_status">
                                    <option value="">Vaccination Status: Any</option>
                                    <option value="up-to-date" <?php echo ($filters['vaccine_status'] ?? '') === 'up-to-date' ? 'selected' : ''; ?>>Up-to-date</option>
                                    <option value="partially" <?php echo ($filters['vaccine_status'] ?? '') === 'partially' ? 'selected' : ''; ?>>Partially</option>
                                    <option value="unknown" <?php echo ($filters['vaccine_status'] ?? '') === 'unknown' ? 'selected' : ''; ?>>Unknown</option>
                                </select>
                                <!-- Availability Dropdown -->
                                <select class="form-select form-select-sm" style="width:170px;" name="availability">
                                    <option value="">Availability: Any</option>
                                    <option value="available" <?php echo ($filters['availability'] ?? '') === 'available' ? 'selected' : ''; ?>>Available Now</option>
                                    <option value="pending" <?php echo ($filters['availability'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending Adoption</option>
                                    <option value="hold" <?php echo ($filters['availability'] ?? '') === 'hold' ? 'selected' : ''; ?>>On Hold</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="mt-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                            <button type="reset" class="btn btn-outline-secondary btn-sm" onclick="window.location='BrowsePet.php'">Clear All</button>
                        </div>
                    </div>
                </form>
                <!-- Filter By: End -->

                <?php if (empty($pets)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">No pets available right now.</div>
                    </div>
                </div>
                <?php else: foreach ($pets as $p):
								$photo = $p['photo'] ?? '/storage/uploads/images/default.png';
								$ownerId = (int)($p['owner_id'] ?? 0);
								$ownerName = htmlspecialchars($p['owner_name'] ?? ($p['full_name'] ?? 'Owner'));
								$shelterName = htmlspecialchars($p['shelter_name'] ?? '');
						?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card h-100">
                        <div class="ratio ratio-4x3 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($photo); ?>" class="card-img-top object-fit-cover"
                                alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>">
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>"
                                            class="text-decoration-none"><?php echo htmlspecialchars($p['name'] ?? 'Unnamed'); ?></a>
                                    </h6>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?> ·
                                        <?php echo htmlspecialchars($p['age'] ?? ''); ?></div>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="badge bg-<?php echo (($p['status'] ?? '')==='available')? 'success' : ((($p['status'] ?? '')==='adopted')? 'secondary' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($p['status'] ?? '')); ?></span>
                                </div>
                            </div>

                            <div class="small mb-2">
                                <div><strong>Species:</strong> <?php echo htmlspecialchars($p['species'] ?? 'Other'); ?>
                                </div>
                                <div><strong>Gender:</strong> <?php echo htmlspecialchars($p['gender'] ?? 'Unknown'); ?>
                                </div>
                                <div><strong>Size:</strong> <?php echo htmlspecialchars($p['size'] ?? 'Medium'); ?>
                                </div>
                                <div><strong>Vaccine:</strong>
                                    <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?></div>
                                <div><strong>Health:</strong>
                                    <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?></div>
                                <div><strong>Location:</strong>
                                    <?php echo htmlspecialchars($p['location'] ?? 'Unknown'); ?></div>
                                <?php if (!empty($shelterName)): ?><div><strong>Shelter:</strong>
                                    <?php echo $shelterName; ?></div><?php endif; ?>
                                <div class="mt-1"><small class="text-muted">Owner: <?php echo $ownerName; ?></small>
                                </div>
                            </div>

                            <?php
								$descRaw = $p['description'] ?? 'No description';
								$plain = trim(strip_tags($descRaw));
								$pid = (int)$p['id'];
							?>
                            <p class="small mb-2">
                                <span id="desc-short-<?php echo $pid; ?>" class="one-line-truncate"
                                    title="<?php echo htmlspecialchars($plain); ?>">
                                    <?php echo htmlspecialchars($plain); ?>
                                </span>
                                <span id="desc-full-<?php echo $pid; ?>" class="d-none">
                                    <?php echo nl2br(htmlspecialchars($descRaw)); ?>
                                </span>
                            </p>
                            <a href="#" class="small d-none" id="desc-toggle-<?php echo $pid; ?>"
                                onclick="event.preventDefault(); togglePetDescription(<?php echo $pid; ?>);">
                                See more
                            </a>

                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal"
                                    data-bs-target="#viewModal-<?php echo (int)$p['id']; ?>">View Profile</a>

                                <!-- View Profile Modal -->
                                <div class="modal fade" id="viewModal-<?php echo (int)$p['id']; ?>" tabindex="-1"
                                    aria-labelledby="viewModalLabel-<?php echo (int)$p['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="viewModalLabel-<?php echo (int)$p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['name'] ?? 'Pet Profile'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-12 col-md-5">
                                                        <img src="<?php echo htmlspecialchars($photo); ?>"
                                                            class="img-fluid rounded w-100 object-fit-cover"
                                                            alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>">
                                                    </div>
                                                    <div class="col-12 col-md-7">
                                                        <p><strong>Breed:</strong>
                                                            <?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?>
                                                        </p>
                                                        <p><strong>Age:</strong>
                                                            <?php echo htmlspecialchars($p['age'] ?? ''); ?></p>
                                                        <p><strong>Species:</strong>
                                                            <?php echo htmlspecialchars($p['species'] ?? 'Other'); ?>
                                                        </p>
                                                        <p><strong>Gender:</strong>
                                                            <?php echo htmlspecialchars($p['gender'] ?? 'Unknown'); ?>
                                                        </p>
                                                        <p><strong>Size:</strong>
                                                            <?php echo htmlspecialchars($p['size'] ?? 'Medium'); ?></p>
                                                        <p><strong>Vaccine:</strong>
                                                            <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?>
                                                        </p>
                                                        <p><strong>Health:</strong>
                                                            <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?>
                                                        </p>
                                                        <p><strong>Owner:</strong> <?php echo $ownerName; ?></p>
                                                        <?php if (!empty($shelterName) || !empty($p['location']) || !empty($p['shelter_address'])): ?>
                                                        <hr>
                                                        <h6>Shelter / Location</h6>
                                                        <?php if (!empty($shelterName)): ?><p><strong>Shelter:</strong>
                                                            <?php echo $shelterName; ?></p><?php endif; ?>
                                                        <?php if (!empty($p['shelter_address'])): ?><p>
                                                            <strong>Address:</strong>
                                                            <?php echo htmlspecialchars($p['shelter_address']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($p['location'])): ?>
                                                        <p><strong>Location:</strong>
                                                            <?php echo htmlspecialchars($p['location']); ?></p>
                                                        <p><a href="https://www.google.com/maps/search/<?php echo urlencode($p['location']); ?>"
                                                                target="_blank" rel="noopener noreferrer"
                                                                class="btn btn-sm btn-outline-secondary">View on Google
                                                                Maps</a></p>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-12">
                                                        <hr>
                                                        <h6>Description</h6>
                                                        <div><?php echo nl2br(htmlspecialchars($descRaw)); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <?php if (($p['status'] ?? '') === 'available'): ?>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#adoptModal-<?php echo (int)$p['id']; ?>"
                                                    data-bs-dismiss="modal">Adopt</button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adopt button replaced by modal trigger -->
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#adoptModal-<?php echo (int)$p['id']; ?>"
                                    <?php echo (($p['status'] ?? '') !== 'available') ? 'disabled' : ''; ?>>Adopt</button>

                                <!-- Adoption Modal -->
                                <div class="modal fade" id="adoptModal-<?php echo (int)$p['id']; ?>" tabindex="-1"
                                    aria-labelledby="adoptModalLabel-<?php echo (int)$p['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="adoptModalLabel-<?php echo (int)$p['id']; ?>">Adoption Request -
                                                    <?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form method="post" action="../controllers/AdoptPetProcessController.php">
                                                <input type="hidden" name="action" value="request">
                                                <input type="hidden" name="pet_id" value="<?php echo (int)$p['id']; ?>">
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="form-label">Your full name</label>
                                                        <input type="text" name="applicant_name" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Phone number</label>
                                                        <input type="text" name="applicant_phone" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Address</label>
                                                        <input type="text" name="applicant_address" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['address'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Message / Why you want to
                                                            adopt</label>
                                                        <textarea name="applicant_message" class="form-control"
                                                            rows="3"><?php echo htmlspecialchars(''); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">Confirm Adoption
                                                        Request</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message button: open chat with owner (ChatMessages.php?user_id=OWNER_ID) -->
                                <a href="./ChatMessages.php?user_id=<?php echo $ownerId; ?>"
                                    class="btn btn-sm btn-outline-secondary"><i class="ti ti-message"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="col-12 col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Filters</h6>
                    <p class="small text-muted">Use search and filters on the left to narrow results.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- include page-specific JS -->
<script src="./assets/js/BrowsePet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const species = document.getElementById('filter-species');
    const breed = document.getElementById('filter-breed');
    species.addEventListener('change', function() {
        // Submit form to reload breeds for selected species
        this.form.submit();
    });

    document.getElementById('toggle-advanced-filters').addEventListener('click', function() {
        document.getElementById('advanced-filters').classList.toggle('d-none');
        this.textContent = document.getElementById('advanced-filters').classList.contains('d-none')
            ? 'Show Advanced Filters'
            : 'Hide Advanced Filters';
    });
});
</script>
<?php include __DIR__ . '/../include/footer.php'; ?>