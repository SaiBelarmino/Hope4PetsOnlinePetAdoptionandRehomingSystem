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
<link rel="stylesheet" href="assets/css/myprofile.css" />
<form id="photoForm" method="post" action="../controllers/EditMyProfileController.php" enctype="multipart/form-data"
    style="display: none;">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*"
        onchange="document.getElementById('photoForm').submit();">
</form>
<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
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
                                width="16" height="16" alt="Verified" class="ms-0"><?php endif; ?></h4>
                        <p class="mb-1 small"><?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?> years old •
                            <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                        <p class="mb-0 small"><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></p>
                    </div>
            <div class="d-flex flex-row gap-2">
            <?php if (empty($user['is_verified'])): ?>
            <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#verifyIdModal"><i
                class="ti ti-id"></i> Verify ID</button>
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
                                    <!-- Post Content -->
                                    <div class="mb-3">
                                        <p class="mb-3 fs-6 lh-base"><?php echo htmlspecialchars($post['content']); ?>
                                        </p>
                                    </div>

                                    <!-- Media Section -->
                                    <?php if (!empty($post['media'])): ?>
                                    <?php $mediaCount = count($post['media']); ?>
                                    <div class="mb-3">
                                        <?php if ($mediaCount === 1): ?>
                                        <div class="position-relative">
                                            <?php $media = $post['media'][0]; ?>
                                            <?php if ($media['type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($media['url']); ?>"
                                                class="img-fluid rounded" alt="Post media"
                                                style="width: 100%; height: auto; max-height: 400px; object-fit: cover; cursor: pointer;"
                                                onclick="openMediaModal(<?php echo $post['id']; ?>, 0, 'image')">
                                            <?php elseif ($media['type'] === 'video'): ?>
                                            <video class="w-100 rounded"
                                                style="max-height: 400px; object-fit: cover; cursor: pointer;"
                                                onclick="openMediaModal(<?php echo $post['id']; ?>, 0, 'video')"
                                                controls>
                                                <source src="<?php echo htmlspecialchars($media['url']); ?>"
                                                    type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                            <?php endif; ?>
                                        </div>
                                        <?php elseif ($mediaCount === 2): ?>
                                        <div class="row g-2">
                                            <?php foreach ($post['media'] as $index => $media): ?>
                                            <div class="col-6">
                                                <?php if ($media['type'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($media['url']); ?>"
                                                    class="img-fluid rounded" alt="Post media"
                                                    style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $index; ?>, 'image')">
                                                <?php elseif ($media['type'] === 'video'): ?>
                                                <video class="w-100 rounded"
                                                    style="height: 200px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $index; ?>, 'video')"
                                                    controls>
                                                    <source src="<?php echo htmlspecialchars($media['url']); ?>"
                                                        type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php elseif ($mediaCount === 3): ?>
                                        <div class="row g-2">
                                            <div class="col-8">
                                                <?php $media = $post['media'][0]; ?>
                                                <?php if ($media['type'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($media['url']); ?>"
                                                    class="img-fluid rounded" alt="Post media"
                                                    style="width: 100%; height: 300px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, 0, 'image')">
                                                <?php elseif ($media['type'] === 'video'): ?>
                                                <video class="w-100 rounded"
                                                    style="height: 300px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, 0, 'video')"
                                                    controls>
                                                    <source src="<?php echo htmlspecialchars($media['url']); ?>"
                                                        type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-4 d-flex flex-column g-2">
                                                <?php for ($i = 1; $i < 3; $i++): ?>
                                                <div class="flex-fill">
                                                    <?php $media = $post['media'][$i]; ?>
                                                    <?php if ($media['type'] === 'image'): ?>
                                                    <img src="<?php echo htmlspecialchars($media['url']); ?>"
                                                        class="img-fluid rounded mb-2" alt="Post media"
                                                        style="width: 100%; height: 145px; object-fit: cover; cursor: pointer;"
                                                        onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $i; ?>, 'image')">
                                                    <?php elseif ($media['type'] === 'video'): ?>
                                                    <video class="w-100 rounded mb-2"
                                                        style="height: 145px; object-fit: cover; cursor: pointer;"
                                                        onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $i; ?>, 'video')"
                                                        controls>
                                                        <source src="<?php echo htmlspecialchars($media['url']); ?>"
                                                            type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="row g-2">
                                            <?php $mediaIndex = 0; foreach ($post['media'] as $media): ?>
                                            <div class="col-6 col-md-4">
                                                <?php if ($media['type'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($media['url']); ?>"
                                                    class="img-fluid rounded" alt="Post media"
                                                    style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $mediaIndex; ?>, 'image')">
                                                <?php elseif ($media['type'] === 'video'): ?>
                                                <video class="w-100 rounded"
                                                    style="height: 150px; object-fit: cover; cursor: pointer;"
                                                    onclick="openMediaModal(<?php echo $post['id']; ?>, <?php echo $mediaIndex; ?>, 'video')"
                                                    controls>
                                                    <source src="<?php echo htmlspecialchars($media['url']); ?>"
                                                        type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <?php endif; ?>
                                            </div>
                                            <?php $mediaIndex++; endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Post Footer -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i
                                                class="ti ti-calendar me-1"></i><?php echo htmlspecialchars(date('M d, Y', strtotime($post['created_at']))); ?>
                                            <i class="ti ti-heart me-1 ms-3"></i><?php echo $post['reaction_count']; ?>
                                            reactions
                                            <i class="ti ti-message me-1 ms-3"></i><?php echo $post['comment_count']; ?>
                                            comments
                                        </small>
                                    </div>

                                    <!-- Comments Section -->
                                    <?php if ($post['comment_count'] > 0): ?>
                                    <div class="mt-4">
                                        <?php if (!empty($post['comments'])): ?>
                                        <div class="d-flex mb-3">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="ti ti-user text-muted" style="font-size: 16px;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="bg-light rounded p-2">
                                                    <strong><?php echo htmlspecialchars($post['comments'][0]['user_name']); ?>:</strong>
                                                    <?php echo htmlspecialchars($post['comments'][0]['content']); ?>
                                                </div>
                                                <small
                                                    class="text-muted ms-2"><?php echo htmlspecialchars(date('M d, Y', strtotime($post['comments'][0]['created_at']))); ?></small>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($post['comment_count'] > 1): ?>
                                        <button class="btn btn-outline-primary btn-sm" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#comments-<?php echo $post['id']; ?>" aria-expanded="false"
                                            aria-controls="comments-<?php echo $post['id']; ?>">
                                            <i class="ti ti-chevron-down me-1"></i>View all comments
                                            (<?php echo $post['comment_count']; ?>)
                                        </button>
                                        <div class="collapse mt-3" id="comments-<?php echo $post['id']; ?>">
                                            <?php for ($i = 1; $i < count($post['comments']); $i++): ?>
                                            <div class="d-flex mb-2">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;">
                                                        <i class="ti ti-user text-muted" style="font-size: 16px;"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="bg-light rounded p-2">
                                                        <strong><?php echo htmlspecialchars($post['comments'][$i]['user_name']); ?>:</strong>
                                                        <?php echo htmlspecialchars($post['comments'][$i]['content']); ?>
                                                    </div>
                                                    <small
                                                        class="text-muted ms-2"><?php echo htmlspecialchars(date('M d, Y', strtotime($post['comments'][$i]['created_at']))); ?></small>
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                        </div>
                                        <?php endif; ?>
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
        <div class="col-12 col-lg-3">
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

<!-- Image Modal -->
<div class="modal fade modal-fullscreen" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <button class="btn-close position-absolute top-0 end-0 m-2 text-white" data-bs-dismiss="modal"
                aria-label="Close"></button>
            <div class="modal-body p-0">
                <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner" id="carousel-inner"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
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
var postMedia = {};
<?php foreach ($posts as $post): ?>
postMedia[<?php echo $post['id']; ?>] = <?php echo json_encode($post['media']); ?>;
<?php endforeach; ?>
</script>
<script src="assets/js/myprofile.js"></script>