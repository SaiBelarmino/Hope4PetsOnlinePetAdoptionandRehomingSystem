<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: PetManagement.php
 * Expected Variables:
 *  - $pets => [ {'id','name','breed','age','status','photo','primary_photo','photos'=>[...],'species','gender','size','vaccine_status','health_status','location','description','created_at'}, ... ]
 */
$pageTitle = 'Pet Management';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);

if (!isset($pets)) {
    // try to load pets for current logged-in user
    require_once __DIR__ . '/../controllers/PetManagementController.php';
    $ownerId = (int)($_SESSION['user']['id'] ?? 0);
    if ($ownerId > 0) {
        // show owner's pets
        $pets = PetManagementController::getPetsByOwnerId($ownerId);
    } else {
        // not logged in: show public available pets
        $pets = PetManagementController::getAvailablePets(36, 0);
    }

    // attach photos and resolve primary
    foreach ($pets as &$pet) {
        // if primary_photo already provided by controller, use it; otherwise fetch photos
        if (empty($pet['primary_photo'])) {
            $photos = PetManagementController::getPetPhotos((int)$pet['id']);
        } else {
            // still fetch photos list for potential gallery
            $photos = PetManagementController::getPetPhotos((int)$pet['id']);
        }
        $pet['photos'] = $photos;
        if (!empty($pet['primary_photo'])) {
            $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $pet['primary_photo']);
        } elseif (!empty($photos[0]['photo_path'])) {
            $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $photos[0]['photo_path']);
        } else {
            $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, '/storage/uploads/images/default.png');
        }
    }
    unset($pet);
}
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
          <h6 class="text-muted mb-2">Navigate</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-outline-secondary">Browse Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-outline-primary">Donate</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Center Content -->
    <div class="col-12 col-lg-6">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
        <div class="d-flex gap-2">
          <a href="./pets.php" class="btn btn-sm btn-outline-primary"><i class="ti ti-search"></i> Find Pets</a>
          <?php if ($hasShelter): ?><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPetModal">Add Pet</button><?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
              <div class="input-group">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input id="pet-search" type="search" class="form-control" placeholder="Search by name, breed or species">
              </div>
            </div>
            <div class="col-6 col-md-3">
              <select id="pet-filter-status" class="form-select">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="adopted">Adopted</option>
                <option value="pending">Pending</option>
              </select>
            </div>
            <div class="col-6 col-md-3 text-end d-none d-md-block">
              <small class="text-muted">Showing <span id="pet-count"><?php echo (int)count($pets ?? []); ?></span> pets</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Scrollable pets area: only the pet cards will scroll -->
      <div class="pet-grid-scroll" style="max-height:60vh; overflow-y:auto; overscroll-behavior:y-contain; padding-right:8px;">
        <div class="row g-3" id="pet-grid">
          <?php if (empty($pets)): ?>
        <div class="col-12">
          <div class="card"><div class="card-body text-center text-muted py-5">No pets found.</div></div>
        </div>
          <?php else: foreach ($pets as $p):
        // The controller now provides the full URL in $p['photo']
        $status = $p['status'] ?? 'available';
        $photoFullUrl = $p['photo'] ?? '/storage/uploads/images/default.png';
        
        // Set $p_data for use with the View Modal button
        $p_data = $p;
        $p_data['photo'] = $photoFullUrl; // Ensure p_data uses the correct URL
          ?>
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 pet-card" data-name="<?php echo htmlspecialchars(strtolower($p['name'] ?? '')); ?>" data-breed="<?php echo htmlspecialchars(strtolower($p['breed'] ?? '')); ?>" data-species="<?php echo htmlspecialchars(strtolower($p['species'] ?? '')); ?>" data-status="<?php echo htmlspecialchars($status); ?>">
            <div class="ratio ratio-4x3 overflow-hidden">
          <img src="<?php echo htmlspecialchars($photoFullUrl); ?>" alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>" class="card-img-top object-fit-cover">
            </div>
            <?php if (!empty($_GET['debug'])): ?>
          <div class="small text-muted mt-1">
            Raw: <?php echo htmlspecialchars($p['photo_raw'] ?? ($p['pet_photos'] ?? '')); ?><br>
            URL: <?php echo htmlspecialchars($photoFullUrl); ?>
          </div>
            <?php endif; ?>
            <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h6 class="mb-0 fw-semibold"><a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($p['name'] ?? 'Unnamed'); ?></a></h6>
              <div class="small text-muted"><?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?> · <?php echo htmlspecialchars($p['age'] ?? ''); ?></div>
            </div>
            <div class="text-end">
              <span class="badge bg-<?php echo ($status==='available')? 'success' : (($status==='adopted')? 'secondary' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
            </div>
          </div>

          <!-- Additional details -->
          <div class="mb-2 small">
            <span class="me-2"><strong>Species:</strong> <?php echo htmlspecialchars(ucfirst($p['species'] ?? 'Other')); ?></span>
            <span class="me-2"><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($p['gender'] ?? 'Unknown')); ?></span>
            <span><strong>Size:</strong> <?php echo htmlspecialchars(ucfirst($p['size'] ?? 'Medium')); ?></span>
          </div>
          <div class="mb-2 small text-truncate"><strong>Vaccine:</strong> <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?></div>
          <div class="mb-2 small text-truncate"><strong>Health:</strong> <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?></div>
          <div class="mb-2 small text-truncate text-muted"><i class="ti ti-map-pin"></i> <?php echo htmlspecialchars($p['location'] ?? 'Unknown'); ?></div>

          <p class="small text-truncate mb-2"><?php echo htmlspecialchars($p['description'] ?? 'No description'); ?></p>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary flex-grow-1 btn-view" data-pet='<?php $p_data['photo'] = htmlspecialchars($photoFullUrl); echo json_encode($p_data, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' type="button">View</button>
            <?php if ($hasShelter): ?>
              <button class="btn btn-sm btn-outline-secondary btn-edit" data-pet='<?php echo json_encode($p, JSON_HEX_APOS|JSON_HEX_QUOT); ?>' data-bs-toggle="modal" data-bs-target="#editPetModal" type="button">Edit</button>
              <button class="btn btn-sm btn-outline-danger" onclick="deletePet(<?php echo (int)$p['id']; ?>, this)" type="button">Delete</button>
            <?php endif; ?>
          </div>
            </div>
          </div>
        </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Pagination placeholder (server-side preferred) -->
      <div class="mt-3 text-center">
        <!-- implement server-side pagination as needed -->
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="card mb-3">
        <div class="card-body">
          <h6 class="mb-2">Help & Tips</h6>
          <p class="small text-muted mb-2">Click a pet's View button to see full details. Use filters to narrow results.</p>
          <a href="./messages.php" class="btn btn-sm btn-outline-primary w-100">Message Support</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted mb-2">Shortcuts</h6>
          <div class="d-grid gap-2">
            <a href="./pets.php" class="btn btn-sm btn-light border">Find Pets</a>
            <a href="./donate.php" class="btn btn-sm btn-light border">Donate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>
</div>

<!-- Add Pet Modal -->
<div class="modal fade" id="addPetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form action="../controllers/AddPetManagementController.php" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Add Pet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="mb-2">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Species</label>
                <select name="species" class="form-select">
                  <option value="dog">Dog</option>
                  <option value="cat">Cat</option>
                  <option value="bird">Bird</option>
                  <option value="rabbit">Rabbit</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Breed</label>
                <input name="breed" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Age</label>
                <input name="age" class="form-control">
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="mb-2">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                  <option value="unknown">Unknown</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Size</label>
                <select name="size" class="form-select">
                  <option value="small">Small</option>
                  <option value="medium">Medium</option>
                  <option value="large">Large</option>
                  <option value="extra-large">Extra-large</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Vaccine Status</label>
                <input name="vaccine_status" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Health Status</label>
                <input name="health_status" class="form-control">
              </div>
            </div>

            <div class="col-12">
              <div class="mb-2">
                <label class="form-label">Location</label>
                <input name="location" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
              </div>
              <div class="mb-2">
                <label class="form-label">Photos (you can select multiple) - first image will be primary</label>
                <input name="photos[]" type="file" class="form-control" accept="image/*" multiple id="addPetPhotos">
                <div id="addPetPhotoPreview" class="d-flex gap-2 mt-2 flex-wrap"></div>
              </div>
            </div>

            <!-- include shelter_id if available -->
            <?php if (!empty($_SESSION['shelter_id'])): ?>
              <input type="hidden" name="shelter_id" value="<?php echo (int)$_SESSION['shelter_id']; ?>">
            <?php endif; ?>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Pet</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Pet Modal -->
<div class="modal fade" id="editPetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form action="../controllers/EditPetManagementController.php" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Edit Pet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="mb-2">
                <label class="form-label">Name</label>
                <input name="name" id="editPetName" class="form-control" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Species</label>
                <select name="species" id="editPetSpecies" class="form-select">
                  <option value="dog">Dog</option>
                  <option value="cat">Cat</option>
                  <option value="bird">Bird</option>
                  <option value="rabbit">Rabbit</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Breed</label>
                <input name="breed" id="editPetBreed" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Age</label>
                <input name="age" id="editPetAge" class="form-control">
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="mb-2">
                <label class="form-label">Gender</label>
                <select name="gender" id="editPetGender" class="form-select">
                  <option value="unknown">Unknown</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Size</label>
                <select name="size" id="editPetSize" class="form-select">
                  <option value="small">Small</option>
                  <option value="medium">Medium</option>
                  <option value="large">Large</option>
                  <option value="extra-large">Extra-large</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label">Vaccine Status</label>
                <input name="vaccine_status" id="editPetVaccine" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Health Status</label>
                <input name="health_status" id="editPetHealth" class="form-control">
              </div>
            </div>

            <div class="col-12">
              <div class="mb-2">
                <label class="form-label">Location</label>
                <input name="location" id="editPetLocation" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea name="description" id="editPetDescription" class="form-control" rows="3"></textarea>
              </div>
              <div class="mb-2">
                <label class="form-label">Photos (optional - you can select multiple to replace existing)</label>
                <input name="photos[]" type="file" class="form-control" accept="image/*" multiple id="editPetPhotos">
                <div id="editPetPhotoPreview" class="d-flex gap-2 mt-2 flex-wrap"></div>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-2">
                <label class="form-label">Status</label>
                <div>
                  <input type="radio" name="status" value="available" id="editStatusAvailable"> <label for="editStatusAvailable">Available</label>
                  <input type="radio" name="status" value="adopted" id="editStatusAdopted" style="margin-left: 20px;"> <label for="editStatusAdopted">Adopted</label>
                </div>
              </div>
            </div>

            <!-- hidden pet_id -->
            <input type="hidden" name="pet_id" id="editPetId">

            <!-- include shelter_id if available -->
            <?php if (!empty($_SESSION['shelter_id'])): ?>
              <input type="hidden" name="shelter_id" value="<?php echo (int)$_SESSION['shelter_id']; ?>">
            <?php endif; ?>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Pet</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Pet Detail Modal -->
<div class="modal fade" id="petDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="petDetailTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <img id="petDetailImg" src="" alt="" class="img-fluid rounded">
          </div>
          <div class="col-12 col-md-6">
            <dl class="row">
              <dt class="col-4">Breed</dt><dd class="col-8" id="petDetailBreed"></dd>
              <dt class="col-4">Age</dt><dd class="col-8" id="petDetailAge"></dd>
              <dt class="col-4">Species</dt><dd class="col-8" id="petDetailSpecies"></dd>
              <dt class="col-4">Gender</dt><dd class="col-8" id="petDetailGender"></dd>
              <dt class="col-4">Size</dt><dd class="col-8" id="petDetailSize"></dd>
              <dt class="col-4">Status</dt><dd class="col-8"><span id="petDetailStatus" class="badge bg-success"></span></dd>
              <dt class="col-4">Vaccine</dt><dd class="col-8" id="petDetailVaccine"></dd>
              <dt class="col-4">Health</dt><dd class="col-8" id="petDetailHealth"></dd>
              <dt class="col-4">Location</dt><dd class="col-8" id="petDetailLocation"></dd>
              <dt class="col-4">Added</dt><dd class="col-8" id="petDetailAdded"></dd>
            </dl>
            <p id="petDetailDesc" class="small text-muted"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="#" id="petDetailLink" class="btn btn-primary">Open Profile</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script src="assets/js/pet-management.js"></script>

