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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<form id="photoForm" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data" style="display: none;">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" onchange="document.getElementById('photoForm').submit();">
</form>
<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <!-- Center Content -->
        <div class="col-12 col-lg-6">
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
            ?>
            <!-- Cover Photo -->
            <div class="card mb-3" style="background-image: url('/default-cover.jpg'); background-size: cover; background-position: center; height: 250px; border-radius: 10px;">
                <div class="card-body d-flex align-items-end">
                    <div class="position-relative me-3">
                        <div class="rounded-circle border border-white border-4 d-flex align-items-center justify-content-center bg-light"
                            style="width: 120px; height: 120px;">
                            <i class="ti ti-user text-muted" style="font-size: 60px;"></i>
                        </div>
                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/<?php echo htmlspecialchars($user['profile_photo'] ?? 'default-avatar.png'); ?>"
                            alt="Profile" class="rounded-circle border border-white border-4 position-absolute top-0 start-0"
                            style="width: 120px; height: 120px; object-fit: cover; display: none;"
                            onload="this.style.display='block'; this.previousElementSibling.style.display='none';">
                        <button class="btn btn-light position-absolute rounded-circle p-1" onclick="document.getElementById('profilePhotoInput').click();" style="bottom: 5px; right: 5px; width: 30px; height: 30px;">
                            <i class="ti ti-camera" style="font-size: 14px;"></i>
                        </button>
                    </div>
                    <div class="text-black flex-grow-1">
                        <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?> <?php if ($user['is_verified']): ?><img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/svg-verified/verified.svg" width="16" height="16" alt="Verified" class="ms-0"><?php endif; ?></h4>
                        <p class="mb-1"><?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?> years old • <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#verifyIdModal"><i class="ti ti-id"></i> Verify ID</button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal"><i class="ti ti-edit"></i> Edit Profile</button>
                    </div>
                </div>
            </div>
            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-pills nav-fill" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="true">About</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts" type="button" role="tab" aria-controls="posts" aria-selected="false">Posts</button>
                        </li>
                        <?php if ($shelter): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shelter-tab" data-bs-toggle="tab" data-bs-target="#shelter" type="button" role="tab" aria-controls="shelter" aria-selected="false">Shelter</button>
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
                                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <p><strong>Birthday:</strong> <?php echo htmlspecialchars($user['birthday'] ?? 'N/A'); ?></p>
                                    <p><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                                </div>
                            </div>
                            <hr>
                            <p class="small text-muted">Joined: <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?> | Last Updated: <?php echo htmlspecialchars(date('M d, Y', strtotime($user['updated_at']))); ?></p>
                        </div>
                        <!-- Posts Tab -->
                        <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                            <?php if (empty($posts)): ?>
                            <p class="text-muted">No posts yet.</p>
                            <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                                    <small class="text-muted">Posted on <?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?> • <?php echo $post['reaction_count']; ?> reactions • <?php echo $post['comment_count']; ?> comments</small>
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
                                        <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/assets/images/svg-verified/verified.svg" width="20" height="20" alt="Verified Shelter" class="ms-1">
                                        <?php endif; ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Shelter Name:</strong> <?php echo htmlspecialchars($shelter['shelter_name']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($shelter['address']); ?></p>
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
        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Keep your profile updated for better adoption chances. Upload a clear photo.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="z-index: 1060;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <?php if ($user['profile_photo'] && $user['profile_photo'] !== 'default-avatar.png'): ?>
                    <img src="/Hope4PetsOnlinePetAdoptionandRehomingSystem/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile" style="display: block; margin: 0 auto; width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff;">
                    <?php else: ?>
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-white border-4" style="display: inline-block; width: 120px; height: 120px;">
                        <i class="ti ti-user text-muted" style="font-size: 60px;"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($user['profile_photo'] && $user['profile_photo'] !== 'default-avatar.png'): ?>
                <div class="text-center mb-3">
                    <button type="button" class="btn btn-danger" onclick="deletePhoto()">Delete Picture</button>
                </div>
                <?php endif; ?>
                <form id="profileForm" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name"
                            value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Birthday</label>
                        <input type="date" class="form-control" name="birthday"
                            value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-control" name="gender">
                            <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Male
                            </option>
                            <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>Female
                            </option>
                            <option value="other" <?php echo ($user['gender'] == 'other') ? 'selected' : ''; ?>>Other
                            </option>
                            <option value="unspecified"
                                <?php echo ($user['gender'] == 'unspecified') ? 'selected' : ''; ?>>Unspecified</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control mb-2" name="shelter_unit" value="<?php echo htmlspecialchars($shelter_unit); ?>" placeholder="Shelter/Unit Name (e.g., 2nd Floor)">
                                <input type="text" class="form-control mb-2" name="purok_subdivision" value="<?php echo htmlspecialchars($purok_subdivision); ?>" placeholder="Purok/Subdivision">
                                <input type="text" class="form-control mb-2" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>" placeholder="Barangay">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control mb-2" name="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="City">
                                <input type="text" class="form-control mb-2" name="province" value="<?php echo htmlspecialchars($province); ?>" placeholder="Province">
                                <input type="text" class="form-control mb-2" name="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>" placeholder="Postal Code">
                            </div>
                        </div>
                        <small class="text-muted">Location is used for accurate place name via geolocation.</small>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" id="getLocationBtn">Get Current Location</button>
                        </div>
                        <div id="map" style="height: 300px; margin-top: 10px; display: none;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number"
                            value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="profileForm" class="btn btn-primary ms-auto"><i class="ti ti-device-floppy"></i> Update Profile</button>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyIdModalLabel">Verify ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verifyIdForm" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <input type="hidden" name="action" value="verify_id">
                    <div class="mb-3">
                        <label for="docType" class="form-label">Select Document Type</label>
                        <select class="form-select" id="docType" name="doc_type" required>
                            <option value="" selected disabled>Choose...</option>
                            <option value="Philippine National ID (PhilSys/ePhilID)">Philippine National ID (PhilSys/ePhilID)</option>
                            <option value="Passport">Passport</option>
                            <option value="Driver's License">Driver's License</option>
                            <option value="UMID Card (Unified Multi-Purpose ID)">UMID Card (Unified Multi-Purpose ID)</option>
                            <option value="Professional Regulation Commission (PRC) ID">Professional Regulation Commission (PRC) ID</option>
                            <option value="Social Security System (SSS) ID (with date of birth visible)">Social Security System (SSS) ID (with date of birth visible)</option>
                            <option value="Voter's ID or Voter's Certification">Voter's ID or Voter's Certification</option>
                            <option value="Postal ID">Postal ID</option>
                            <option value="Senior Citizen ID">Senior Citizen ID</option>
                            <option value="National Bureau of Investigation (NBI) Clearance">National Bureau of Investigation (NBI) Clearance</option>
                            <option value="Barangay ID">Barangay ID</option>
                            <option value="PhilHealth ID">PhilHealth ID</option>
                            <option value="GSIS E-Card">GSIS E-Card</option>
                            <option value="Solo Parent ID">Solo Parent ID</option>
                            <option value="Philippine National Police (PNP) ID">Philippine National Police (PNP) ID</option>
                            <option value="Integrated Bar of the Philippines (IBP) ID">Integrated Bar of the Philippines (IBP) ID</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="openCameraBtn" disabled>Open Camera & Take Picture</button>
                    </div>
                    <div id="cameraContainer" style="display: none;">
                        <p id="captureLabel">Capture Front of ID</p>
                        <video id="video" width="100%" height="300" autoplay></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <div id="capturedImages" style="display: none;">
                            <div class="d-flex flex-column align-items-center">
                                <div class="text-center mb-3">
                                    <p>Front</p>
                                    <img id="frontImg" style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
                                    <br>
                                    <button class="btn btn-danger btn-sm mt-2" id="removeFrontBtn" style="display: none;">Remove</button>
                                </div>
                                <div class="text-center" id="backContainer" style="display: none;">
                                    <p>Back</p>
                                    <img id="backImg" style="width: 200px; height: 120px; object-fit: cover; border: 1px solid #ccc;">
                                    <br>
                                    <button class="btn btn-danger btn-sm mt-2" id="removeBackBtn" style="display: none;">Remove</button>
                                </div>
                                <button class="btn btn-danger btn-sm mt-2" id="removeAllBtn" style="display: none;">Remove All Photos</button>
                            </div>
                        </div>
                        <br>
                        <button type="button" class="btn btn-success" id="captureBtn">Capture</button>
                        <button type="button" class="btn btn-primary" id="nextBackBtn" style="display: none;">Next Back</button>
                        <button type="button" class="btn btn-secondary" id="retakeBtn" style="display: none;">Retake</button>
                    </div>
                    <input type="hidden" name="id_photo" id="idPhotoInput">
                    <input type="hidden" name="id_photo_back" id="idPhotoBackInput">
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="verifyIdForm" class="btn btn-primary">Submit Verification</button>
            </div>
        </div>
    </div>
</div>

<script>
var map;
var marker;
document.getElementById('getLocationBtn').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            // Reverse geocode using BigDataCloud
            fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
                .then(response => response.json())
                .then(data => {
                    // Fill the address fields
                    const admin = data.localityInfo?.administrative || [];
                    document.querySelector('input[name="province"]').value = admin[2]?.name || data.countryName || '';
                    document.querySelector('input[name="city"]').value = admin[3]?.name || data.city || '';
                    document.querySelector('input[name="barangay"]').value = admin[4]?.name || '';
                    document.querySelector('input[name="purok_subdivision"]').value = data.locality || '';
                    document.querySelector('input[name="postal_code"]').value = data.postcode || '';
                    document.querySelector('input[name="shelter_unit"]').value = ''; // Leave empty
                    // Show map with satellite tiles
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 18);
                        L.tileLayer('https://mt0.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                            attribution: '© Google'
                        }).addTo(map);
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${pos.lat}&longitude=${pos.lng}&localityLanguage=en`)
                                .then(response => response.json())
                                .then(data => {
                                    // Fill the fields with new location
                                    const admin = data.localityInfo?.administrative || [];
                                    document.querySelector('input[name="province"]').value = admin[2]?.name || data.countryName || '';
                                    document.querySelector('input[name="city"]').value = admin[3]?.name || data.city || '';
                                    document.querySelector('input[name="barangay"]').value = admin[4]?.name || '';
                                    document.querySelector('input[name="purok_subdivision"]').value = data.locality || '';
                                    document.querySelector('input[name="postal_code"]').value = data.postcode || '';
                                    document.querySelector('input[name="shelter_unit"]').value = '';
                                });
                        });
                    } else {
                        map.setView([lat, lng], 18);
                        marker.setLatLng([lat, lng]);
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                    // Still show map
                    document.getElementById('map').style.display = 'block';
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 18);
                        L.tileLayer('https://mt0.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                            attribution: '© Google'
                        }).addTo(map);
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            // No alert
                        });
                    } else {
                        map.setView([lat, lng], 18);
                        marker.setLatLng([lat, lng]);
                    }
                });
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
});

function deletePhoto() {
    if (confirm('Are you sure you want to delete your profile photo?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '../controllers/EditMyProfileController.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_photo';
        input.value = '1';
        form.appendChild(input);
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = '<?php echo $user['id']; ?>';
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Camera functionality for ID verification
const openCameraBtn = document.getElementById('openCameraBtn');
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const idPhotoInput = document.getElementById('idPhotoInput');
const idPhotoBackInput = document.getElementById('idPhotoBackInput');
let isFront = true;
let retakeFront = false;

openCameraBtn.addEventListener('click', function() {
    // Disable the button to prevent multiple clicks
    openCameraBtn.disabled = true;

    // Reset flags
    isFront = true;
    retakeFront = false;

    // Show the camera container
    document.getElementById('cameraContainer').style.display = 'block';

    // Start the video stream
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error('Error accessing camera: ' + err);
            openCameraBtn.disabled = false; // Re-enable the button
        });
});

document.getElementById('captureBtn').addEventListener('click', function() {
    if (isFront) {
        // Capture front
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        idPhotoInput.value = canvas.toDataURL('image/png');

        // Stop stream
        const stream = video.srcObject;
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach(function(track) {
                track.stop();
            });
        }
        video.srcObject = null;

        // Hide video, show captured images with front
        video.style.display = 'none';
        document.getElementById('capturedImages').style.display = 'block';
        document.getElementById('frontImg').src = idPhotoInput.value;

        // Change label
        document.getElementById('captureLabel').textContent = 'ID Photos Captured';
        captureBtn.style.display = 'none';
        isFront = false;
        retakeFront = true;

        // Show next back button and retake
        document.getElementById('nextBackBtn').style.display = 'inline-block';
        retakeBtn.style.display = 'inline-block';
        retakeBtn.textContent = 'Retake Front';
        document.getElementById('removeFrontBtn').style.display = 'inline-block';
    } else {
        // Capture back
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                video.onloadedmetadata = function() {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    idPhotoBackInput.value = canvas.toDataURL('image/png');

                    // Stop stream
                    const stream2 = video.srcObject;
                    if (stream2) {
                        const tracks = stream2.getTracks();
                        tracks.forEach(function(track) {
                            track.stop();
                        });
                    }
                    video.srcObject = null;

                    // Hide video, show captured images with both
                    video.style.display = 'none';
                    document.getElementById('capturedImages').style.display = 'block';
                    document.getElementById('frontImg').src = idPhotoInput.value;
                    document.getElementById('backImg').src = idPhotoBackInput.value;
                    document.getElementById('backContainer').style.display = 'block';

                    // Change label
                    document.getElementById('captureLabel').textContent = 'ID Photos Captured';
                    captureBtn.style.display = 'none';
                    retakeBtn.textContent = 'Retake Back';
                    retakeFront = false;
                    document.getElementById('removeFrontBtn').style.display = 'none';
                    document.getElementById('removeBackBtn').style.display = 'none';
                    document.getElementById('removeAllBtn').style.display = 'inline-block';
                };
            })
            .catch(function(err) {
                console.error('Error accessing camera: ' + err);
            });
    }
});

document.getElementById('nextBackBtn').addEventListener('click', function() {
    // Hide captured images, show video for back
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';

    // Start stream
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error('Error accessing camera: ' + err);
        });

    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Back of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture Back';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
});

document.getElementById('retakeBtn').addEventListener('click', function() {
    // Hide captured images
    document.getElementById('capturedImages').style.display = 'none';

    if (retakeFront) {
        // Retake front
        video.style.display = 'block';
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                // Change label
                document.getElementById('captureLabel').textContent = 'Capture Front of ID';
                captureBtn.style.display = 'inline-block';
                captureBtn.textContent = 'Capture';
                document.getElementById('nextBackBtn').style.display = 'none';
                retakeBtn.style.display = 'none';
                isFront = true;
            });
    } else {
        // Retake back
        video.style.display = 'block';
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                // Change label
                document.getElementById('captureLabel').textContent = 'Capture Back of ID';
                captureBtn.style.display = 'inline-block';
                captureBtn.textContent = 'Capture Back';
                document.getElementById('nextBackBtn').style.display = 'none';
                retakeBtn.style.display = 'none';
            });
    }
});

// Enable camera button when document type is selected
document.getElementById('docType').addEventListener('change', function() {
    const openCameraBtn = document.getElementById('openCameraBtn');
    if (this.value) {
        openCameraBtn.disabled = false;
    } else {
        openCameraBtn.disabled = true;
    }
});

document.getElementById('removeFrontBtn').addEventListener('click', function() {
    // Clear front image
    document.getElementById('frontImg').src = '';
    idPhotoInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for front
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Front of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    isFront = true;
    retakeFront = false;
});

document.getElementById('removeBackBtn').addEventListener('click', function() {
    // Clear back image
    document.getElementById('backImg').src = '';
    idPhotoBackInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for back
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Back of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture Back';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    isFront = false;
    retakeFront = false;
});

document.getElementById('removeAllBtn').addEventListener('click', function() {
    // Clear both images
    document.getElementById('frontImg').src = '';
    document.getElementById('backImg').src = '';
    idPhotoInput.value = '';
    idPhotoBackInput.value = '';
    // Hide back container
    document.getElementById('backContainer').style.display = 'none';
    // Show video for front
    document.getElementById('capturedImages').style.display = 'none';
    video.style.display = 'block';
    // Start stream
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        });
    // Change label
    document.getElementById('captureLabel').textContent = 'Capture Front of ID';
    captureBtn.style.display = 'inline-block';
    captureBtn.textContent = 'Capture';
    document.getElementById('nextBackBtn').style.display = 'none';
    retakeBtn.style.display = 'none';
    document.getElementById('removeAllBtn').style.display = 'none';
    isFront = true;
    retakeFront = false;
});
</script>
<style>
  #map {
    height: 300px;
  }
  #video {
    border: 2px dashed #28a745;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
  }
</style>

<?php include __DIR__ . '/../include/footer.php'; ?>