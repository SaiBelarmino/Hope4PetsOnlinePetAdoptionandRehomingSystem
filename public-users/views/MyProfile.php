<?php
$pageTitle = 'My Profile';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';
?>

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
                        <a href="./my_adoptions.php" class="btn btn-sm btn-outline-secondary">My Adoptions</a>
                        <a href="./shelters.php" class="btn btn-sm btn-outline-secondary">Shelters</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Center Content -->
        <div class="col-12 col-lg-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
            </div>
            <?php if (!$user): ?>
            <div class="alert alert-danger">User not found or not logged in.</div>
            <?php else: ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo htmlspecialchars($user['profile_photo'] ?? '/default-avatar.png'); ?>"
                            alt="Profile" class="rounded-circle"
                            style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                    <h5 class="text-center mb-3"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Birthday:</strong> <?php echo htmlspecialchars($user['birthday'] ?? 'N/A'); ?>
                            </p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst($user['gender'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?>
                            </p>
                            <p><strong>Contact Number:</strong>
                                <?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></p>
                            <p><strong>Verified:</strong> <span
                                    class="badge bg-<?php echo $user['is_verified'] ? 'success' : 'warning'; ?>"><?php echo $user['is_verified'] ? 'Yes' : 'No'; ?></span>
                            </p>
                        </div>
                    </div>
                    <p class="small text-muted">Created:
                        <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?> | Updated:
                        <?php echo htmlspecialchars(date('M d, Y', strtotime($user['updated_at']))); ?></p>
                    <div class="text-center">
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"><i
                                class="ti ti-edit"></i> Edit Profile</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Right Sidebar -->
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Profile Summary</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Status:</strong>
                        <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?></p>
                    <p class="mb-0"><strong>Joined:</strong>
                        <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?></p>
                </div>
            </div>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../controllers/profile-controller.php" enctype="multipart/form-data">
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
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location"
                            value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number"
                            value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Photo (Upload new to replace)</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Update
                        Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>