<?php
$pageTitle = 'My Profile';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';

require_once __DIR__ . '/../controllers/MyProfileController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

$session = new SessionManager();
$userId = $session->get('user_id');
$user = null;
if ($userId) {
    $user = ProfileController::get($userId);
}
?>
<link rel="stylesheet" href="assets/css/leaflet.css" />
<?php
$__cssFile = __DIR__ . '/assets/css/myprofile.css';
$__cssVer = file_exists($__cssFile) ? filemtime($__cssFile) : time();
?>
<link rel="stylesheet" href="assets/css/myprofile.css?v=<?php echo $__cssVer; ?>" />
<form id="photoForm" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data"
    style="display: none;">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*"
        onchange="document.getElementById('photoForm').submit();">
</form>
<div class="container-fluid">
    <div class="row g-3 py-4">
        <!-- Left Sidebar -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        <!-- Center Content -->
        <div class="col-12 col-lg-6"
            style="max-height:862px; overflow:auto; -webkit-overflow-scrolling:touch; scrollbar-width: none; -ms-overflow-style: none;"
            tabindex="0" aria-label="Main shelter content (scrollable)">
            <?php if (!$user): ?>
            <div class="alert alert-danger">User not found or not logged in.</div>
            <?php else: ?>
            <?php
            $posts = ProfileController::getPosts($userId);
            $stats = ProfileController::getStats($userId);
            $shelter = ProfileController::getShelter($userId);
            $location = $user['location'] ?? '';
            $location_parts = explode(', ', $location);
            $shelter_unit = '';
            $purok_subdivision = $location_parts[0] ?? '';
            $barangay = $location_parts[1] ?? '';
            $city = $location_parts[2] ?? '';
            $province = $location_parts[3] ?? '';
            $postal_code = $location_parts[4] ?? '';

            if (!function_exists('resolve_media_path')) {
                function resolve_media_path(?string $path): string {
                    if (empty($path)) return '../../assets/images/placeholder.png';
                    $p = trim($path);
                    if (preg_match('#^https?://#i', $p)) return $p;
                    $normalized = str_replace('\\', '/', $p);
                    $pos = stripos($normalized, 'storage/');
                    if ($pos !== false) {
                        $sub = substr($normalized, $pos);
                        return '../../' . ltrim($sub, '/');
                    }
                    $normalized = preg_replace('#^(\\.{1,2}/)+#', '', $normalized);
                    $normalized = ltrim($normalized, '/');
                    if (stripos($normalized, 'storage/') === 0) return '../../' . $normalized;
                    if (stripos($normalized, 'uploads/') === 0) return '../../storage/' . ltrim($normalized, '/');
                    return '../../' . $normalized;
                }
            }
            ?>
            <!-- Cover Photo -->
            <div class="card mb-3"
                style="background-image: url('/default-cover.jpg'); background-size: cover; background-position: center; min-height: 250px; border-radius: 10px;">
                <div class="card-body d-flex flex-column flex-md-row align-items-center align-items-md-end">
                    <div class="position-relative mb-3 mb-md-0 me-md-3">
                        <div class="rounded-circle border border-white border-4 d-flex align-items-center justify-content-center bg-light"
                            style="width: 100px; height: 100px;">
                            <i class="ti ti-user text-muted" style="font-size: 50px;"></i>
                        </div>
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/<?php echo htmlspecialchars($user['profile_photo'] ?? 'default-avatar.png'); ?>"
                            alt="Profile"
                            class="rounded-circle border border-white border-4 position-absolute top-0 start-0"
                            style="width: 100px; height: 100px; object-fit: cover; display: none;"
                            onload="this.style.display='block'; this.previousElementSibling.style.display='none';">
                        <button class="btn btn-light position-absolute rounded-circle p-1"
                            onclick="document.getElementById('profilePhotoInput').click();"
                            style="bottom: 5px; right: 5px; width: 30px; height: 30px;">
                            <i class="ti ti-camera" style="font-size: 14px;"></i>
                        </button>
                    </div>
                    <div class="text-black text-center text-md-start flex-grow-1 mb-3 mb-md-0">
                        <h4 class="mb-1 h5 h4-md"><?php echo htmlspecialchars($user['full_name']); ?>
                            <?php if ($user['is_verified']): ?><img
                                src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/svg-verified/verified.svg"
                                width="16" height="16" alt="Verified" class="ms-0" data-verified-badge><?php endif; ?>
                        </h4>
                        <p class="mb-1 small"><?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?> years old •
                            <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                        <p class="mb-0 small"><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="d-flex flex-row gap-2">
                        <?php if (empty($user['is_verified'])): ?>
                        <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#verifyIdModal"
                            data-verify-id-button><i class="ti ti-id"></i> Verify ID</button>
                        <?php endif; ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"><i
                                class="ti ti-edit"></i> Edit Profile</button>
                    </div>
                </div>
            </div>
            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-pills nav-fill" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about"
                                type="button" role="tab" aria-controls="about" aria-selected="true">About</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts"
                                type="button" role="tab" aria-controls="posts" aria-selected="false">Posts</button>
                        </li>
                        <?php if ($shelter): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shelter-tab" data-bs-toggle="tab" data-bs-target="#shelter"
                                type="button" role="tab" aria-controls="shelter" aria-selected="false">Shelter</button>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- About Tab -->
                        <div class="tab-pane fade show active" id="about" role="tabpanel" aria-labelledby="about-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Contact Information</h6>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p><strong>Contact Number:</strong>
                                        <?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></p>
                                    <p><strong>Location:</strong>
                                        <?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <p><strong>Birthday:</strong>
                                        <?php echo htmlspecialchars($user['birthday'] ?? 'N/A'); ?></p>
                                    <p><strong>Gender:</strong>
                                        <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                                </div>
                            </div>
                            <hr>
                            <p class="small text-muted">Joined:
                                <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?> | Last
                                Updated: <?php echo htmlspecialchars(date('M d, Y', strtotime($user['updated_at']))); ?>
                            </p>
                        </div>
                        <!-- Posts Tab -->
                        <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                            <?php if (empty($posts)): ?>
                            <div class="text-center py-5">
                                <i class="ti ti-photo-off text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No posts yet. Share your first post to get started!</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                            <div class="card mb-4 shadow-sm border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/<?php echo htmlspecialchars($user['profile_photo'] ?? 'default-avatar.png'); ?>"
                                            alt="Profile" class="rounded-circle me-2"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user['full_name']); ?>
                                            </h6>
                                            <small
                                                class="text-muted"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <?php if (!empty($post['content'])): ?>
                                    <div class="mb-3">
                                        <div class="post-caption clamp-3" data-clamp-lines="3">
                                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                        </div>
                                        <button type="button" class="btn btn-link p-0 mt-1 post-caption-toggle"
                                            aria-expanded="false" aria-label="Toggle full caption">See more</button>
                                    </div>
                                    <?php endif; ?>
                                    <?php 
                                    // Improved media handling
                                    $rawMedia = $post['media'] ?? [];
                                    if (is_string($rawMedia)) {
                                        $decoded = json_decode($rawMedia, true);
                                        if (json_last_error() === JSON_ERROR_NONE) $rawMedia = $decoded; else $rawMedia = [$rawMedia];
                                    }
                                    $mediaItems = [];
                                    foreach ((array)$rawMedia as $m) {
                                        if (is_string($m)) {
                                            $mediaItems[] = ['media_path' => $m];
                                        } elseif (is_array($m)) {
                                            // Accept various key names
                                            $candidate = $m['media_path'] ?? $m['path'] ?? $m['file_path'] ?? $m['file'] ?? $m['url'] ?? '';
                                            if ($candidate !== '') {
                                                $m['__resolved'] = $candidate;
                                                $mediaItems[] = $m;
                                            }
                                        }
                                    }
                                    if (!empty($mediaItems)): ?>
                                    <div class="row g-2">
                                        <?php 
                                            $displayMedia = array_slice($mediaItems, 0, 4);
                                            foreach ($displayMedia as $m):
                                                $mPath = $m['__resolved'] ?? $m['media_path'] ?? $m['path'] ?? '';
                                                $fullPath = resolve_media_path($mPath);
                                                // Normalize to absolute web path
                                                $webPath = $fullPath;
                                                if (strpos($webPath, '../../') === 0) {
                                                    $webPath = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/' . ltrim(substr($webPath, 6), '/');
                                                }
                                                $ext = strtolower(pathinfo(parse_url($mPath, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                                                $isPhoto = !in_array($ext, ['mp4','webm','ogg']);
                                                if (isset($m['media_type']) && strpos($m['media_type'], 'video') !== false) $isPhoto = false;
                                            ?>
                                        <div class="col-6">
                                            <a href="PostView.php?id=<?php echo $post['id']; ?>"
                                                class="d-block ratio ratio-1x1">
                                                <?php if ($isPhoto): ?>
                                                <img src="<?php echo htmlspecialchars($webPath); ?>" loading="lazy"
                                                    alt="Post Media" class="rounded w-100 h-100"
                                                    style="object-fit:cover;">
                                                <?php else: ?>
                                                <video src="<?php echo htmlspecialchars($webPath); ?>"
                                                    class="rounded w-100 h-100" style="object-fit:cover;" muted
                                                    playsinline></video>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <!-- Shelter Tab -->
                        <?php if ($shelter): ?>
                        <div class="tab-pane fade" id="shelter" role="tabpanel" aria-labelledby="shelter-tab">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <h6 class="mb-0 me-2">Shelter Information</h6>
                                        <?php if ($shelter['is_verified'] ?? false): ?>
                                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/svg-verified/verified.svg"
                                            width="20" height="20" alt="Verified Shelter" class="ms-1">
                                        <?php endif; ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Shelter Name:</strong>
                                                <?php echo htmlspecialchars($shelter['shelter_name']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Address:</strong>
                                                <?php echo htmlspecialchars($shelter['address']); ?></p>
                                        </div>
                                    </div>
                                    <!-- Add more professional details if available, e.g., contact, description -->
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Right Sidebar -->
        <div class="col-12 col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Keep your profile updated for better adoption chances. Upload a
                        clear photo.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 1060;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <?php if ($user['profile_photo'] && $user['profile_photo'] !== 'default-avatar.png'): ?>
                    <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/<?php echo htmlspecialchars($user['profile_photo']); ?>"
                        alt="Profile"
                        style="display: block; margin: 0 auto; width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff;">
                    <?php else: ?>
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-white border-4"
                        style="display: inline-block; width: 120px; height: 120px;">
                        <i class="ti ti-user text-muted" style="font-size: 60px;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($user['profile_photo'] && $user['profile_photo'] !== 'default-avatar.png'): ?>
                <div class="text-center mb-3">
                    <button type="button" class="btn btn-danger" onclick="deletePhoto()">Delete Picture</button>
                </div>
                <?php endif; ?>
                <form id="profileForm" method="post" action="../controllers/EditMyProfileController.php"
                    enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Full Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="full_name"
                                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Birthday</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="birthday"
                                value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Gender</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="gender">
                                <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Male
                                </option>
                                <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>
                                    Female
                                </option>
                                <option value="other" <?php echo ($user['gender'] == 'other') ? 'selected' : ''; ?>>
                                    Other
                                </option>
                                <option value="unspecified"
                                    <?php echo ($user['gender'] == 'unspecified') ? 'selected' : ''; ?>>Unspecified
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-2" name="shelter_unit"
                                        value="<?php echo htmlspecialchars($shelter_unit); ?>"
                                        placeholder="Shelter/Unit Name (e.g., 2nd Floor)">
                                    <input type="text" class="form-control mb-2" name="purok_subdivision"
                                        value="<?php echo htmlspecialchars($purok_subdivision); ?>"
                                        placeholder="Purok/Subdivision">
                                    <input type="text" class="form-control mb-2" name="barangay"
                                        value="<?php echo htmlspecialchars($barangay); ?>" placeholder="Barangay">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control mb-2" name="city"
                                        value="<?php echo htmlspecialchars($city); ?>" placeholder="City">
                                    <input type="text" class="form-control mb-2" name="province"
                                        value="<?php echo htmlspecialchars($province); ?>" placeholder="Province">
                                    <input type="text" class="form-control mb-2" name="postal_code"
                                        value="<?php echo htmlspecialchars($postal_code); ?>" placeholder="Postal Code">
                                </div>
                            </div>
                            <small class="text-muted">Location is used for accurate place name via geolocation.</small>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary" id="getLocationBtn">Get Current
                                    Location</button>
                            </div>
                            <div id="map" style="height: 300px; margin-top: 10px; display: none;"></div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Contact Number</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="contact_number"
                                value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="profileForm" class="btn btn-primary ms-auto"><i
                                class="ti ti-device-floppy"></i> Update Profile</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- Verify ID Modal -->
<div class="modal fade" id="verifyIdModal" tabindex="-1" aria-labelledby="verifyIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyIdModalLabel">Verify ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verifyIdForm" method="post" action="../controllers/EditMyProfileController.php"
                    enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <input type="hidden" name="action" value="verify_id">
                    <div class="mb-3">
                        <label for="docType" class="form-label">Select Document Type</label>
                        <select class="form-select" id="docType" name="doc_type" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="Philippine National ID (PhilSys/ePhilID)">Philippine National ID
                                (PhilSys/ePhilID)</option>
                            <option value="Passport">Passport</option>
                            <option value="Driver's License">Driver's License</option>
                            <option value="UMID Card (Unified Multi-Purpose ID)">UMID Card (Unified Multi-Purpose ID)
                            </option>
                            <option value="Professional Regulation Commission (PRC) ID">Professional Regulation
                                Commission (PRC) ID</option>
                            <option value="Social Security System (SSS) ID (with date of birth visible)">Social Security
                                System (SSS) ID (with date of birth visible)</option>
                            <option value="Voter's ID or Voter's Certification">Voter's ID or Voter's Certification
                            </option>
                            <option value="Postal ID">Postal ID</option>
                            <option value="Senior Citizen ID">Senior Citizen ID</option>
                            <option value="National Bureau of Investigation (NBI) Clearance">National Bureau of
                                Investigation (NBI) Clearance</option>
                            <option value="Barangay ID">Barangay ID</option>
                            <option value="PhilHealth ID">PhilHealth ID</option>
                            <option value="GSIS E-Card">GSIS E-Card</option>
                            <option value="Solo Parent ID">Solo Parent ID</option>
                            <option value="Philippine National Police (PNP) ID">Philippine National Police (PNP) ID
                            </option>
                            <option value="Integrated Bar of the Philippines (IBP) ID">Integrated Bar of the Philippines
                                (IBP) ID</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="openCameraBtn" disabled>Open Camera & Take
                            Picture</button>
                    </div>
                    <div id="cameraContainer" style="display: none;">
                        <p id="captureLabel">Capture Front of ID</p>
                        <video id="video" width="100%" height="300" autoplay></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <div id="capturedImages" style="display: none;">
                            <div class="d-flex flex-column align-items-center">
                                <div class="text-center mb-3">
                                    <p>Front</p>
                                    <img id="frontImg"
                                        style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
                                    <br>
                                    <button class="btn btn-danger btn-sm mt-2" id="removeFrontBtn"
                                        style="display: none;">Remove</button>
                                </div>
                                <div class="text-center" id="backContainer" style="display: none;">
                                    <p>Back</p>
                                    <img id="backImg"
                                        style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
                                    <br>
                                    <button class="btn btn-danger btn-sm mt-2" id="removeBackBtn"
                                        style="display: none;">Remove</button>
                                </div>
                                <button class="btn btn-danger btn-sm mt-2" id="removeAllBtn"
                                    style="display: none;">Remove All Photos</button>
                            </div>
                        </div>
                        <br>
                        <button type="button" class="btn btn-success" id="captureBtn">Capture</button>
                        <button type="button" class="btn btn-primary" id="nextBackBtn" style="display: none;">Next
                            Back</button>
                        <button type="button" class="btn btn-secondary" id="retakeBtn"
                            style="display: none;">Retake</button>
                    </div>
                    <input type="hidden" name="id_photo" id="idPhotoInput">
                    <input type="hidden" name="id_photo_back" id="idPhotoBackInput">
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="verifyIdForm" class="btn btn-primary" id="submitVerificationBtn"
                    disabled>Submit Verification</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script src="assets/js/leaflet.js"></script>
<script>
window.userId = <?php echo json_encode($user['id']); ?>;
window.posts = <?php echo json_encode($posts); ?>;
window.user = <?php echo json_encode($user); ?>;
// Disable caption init in myprofile.js to avoid conflicts; we use the inline initializer below
window.__DISABLE_POST_CAPTION_INIT = true;
</script>
<?php
$__jsFile = __DIR__ . '/assets/js/myprofile.js';
$__jsVer = file_exists($__jsFile) ? filemtime($__jsFile) : time();
?>
<script src="assets/js/myprofile.js?v=<?php echo $__jsVer; ?>"></script>
<script>
(function() {
    if (window.__CAPTION_INIT_SIMPLE_APPLIED) return; // guard
    window.__CAPTION_INIT_SIMPLE_APPLIED = true;

    function lineHeight(el) {
        var cs = window.getComputedStyle(el);
        var lh = parseFloat(cs.lineHeight);
        if (isNaN(lh)) lh = (parseFloat(cs.fontSize) || 16) * 1.2;
        return lh;
    }

    function clamp(caption) {
        caption.classList.remove('clamp-3', 'expanded');
        var lh = caption.dataset.lh ? parseFloat(caption.dataset.lh) : lineHeight(caption);
        caption.dataset.lh = lh;
        caption.style.maxHeight = (lh * 3) + 'px';
        caption.style.overflow = 'hidden';
    }

    function expand(caption) {
        caption.classList.remove('clamp-3');
        caption.classList.add('expanded');
        caption.style.maxHeight = 'none';
        caption.style.overflow = 'visible';
    }

    function needsToggle(caption) {
        // Temporarily remove maxHeight to measure full height accurately
        var prev = caption.style.maxHeight;
        caption.style.maxHeight = 'none';
        var full = caption.scrollHeight;
        var lh = caption.dataset.lh ? parseFloat(caption.dataset.lh) : lineHeight(caption);
        var need = full > (lh * 3 + 2);
        caption.style.maxHeight = prev;
        return need;
    }

    function setupOne(caption) {
        var toggle = caption.parentElement && caption.parentElement.querySelector('.post-caption-toggle');
        if (!toggle) {
            toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'btn btn-link p-0 mt-1 post-caption-toggle';
            toggle.textContent = 'See more';
            toggle.setAttribute('aria-expanded', 'false');
            caption.parentElement && caption.parentElement.appendChild(toggle);
        }
        // Respect current state
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        if (expanded) expand(caption);
        else clamp(caption);
        // Show toggle only if needed
        toggle.style.display = needsToggle(caption) ? 'inline' : 'none';
        if (!toggle._boundSimple) {
            toggle.addEventListener('click', function() {
                var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                if (isExpanded) {
                    clamp(caption);
                    toggle.textContent = 'See more';
                    toggle.setAttribute('aria-expanded', 'false');
                } else {
                    expand(caption);
                    toggle.textContent = 'See less';
                    toggle.setAttribute('aria-expanded', 'true');
                }
            });
            toggle._boundSimple = true;
        }
    }

    function init(scope) {
        (scope || document).querySelectorAll('.post-caption').forEach(setupOne);
    }

    function run() {
        init(document.getElementById('posts'));
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
    else run();
    var postsTab = document.getElementById('posts-tab');
    if (postsTab) postsTab.addEventListener('shown.bs.tab', run);
    window.addEventListener('load', run);
    window.addEventListener('resize', function() {
        (document.getElementById('posts') || document).querySelectorAll('.post-caption').forEach(function(
            caption) {
            var toggle = caption.parentElement && caption.parentElement.querySelector(
                '.post-caption-toggle');
            if (!toggle) return;
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            if (!isExpanded) clamp(caption); // recompute collapsed height
            // Re-evaluate if toggle is needed on resize
            toggle.style.display = needsToggle(caption) ? 'inline' : 'none';
        });
    });
})();
</script>