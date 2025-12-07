<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
require_once __DIR__ . '/../../controllers/Admin/profile-settings-controller.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$adminId = $_SESSION['admin_id'];
$passwordResult = null;
$basePath = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/';

// Handle AJAX request for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    $data = [
        'full_name' => $_POST['full_name'],
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'phone_number' => $_POST['phone_number']
    ];
    $result = ProfileSettingsController::updateProfile($adminId, $data, $_FILES);

    if ($result['success']) {
        // Fetch the updated data to send back to the client
        $updatedAdmin = ProfileSettingsController::get($adminId);
        $updatedAdmin['profile_picture_url'] = !empty($updatedAdmin['profile_picture']) ? $basePath . $updatedAdmin['profile_picture'] : $basePath . 'public/images/default-profile.png';
        echo json_encode(['success' => true, 'admin' => $updatedAdmin, 'message' => $result['message']]);
    } else {
        echo json_encode(['success' => false, 'errors' => $result['errors']]);
    }
    exit; // Stop further script execution
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This block now only handles the password change form submission
    if (isset($_POST['change_password'])) {
        $passwordResult = ProfileSettingsController::changePassword(
            $adminId,
            $_POST['current_password'],
            $_POST['new_password'],
            $_POST['confirm_password']
        );
    }
}

$admin = ProfileSettingsController::get($adminId);
// Correct the image path to be relative from the project root for the browser
$profilePicture = !empty($admin['profile_picture']) ? $basePath . $admin['profile_picture'] : $basePath . 'public/images/default-profile.png';
?>
<?php include dirname(__DIR__, 2) . '/sidebar.php'; ?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="mb-4">
            <h3 class="fw-semibold">Admin Profile Settings</h3>
            <p>Manage your account details and preferences</p>
        </div>
        <!-- Profile Details Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Profile Details</h5>
                    <button id="edit-profile-btn" class="btn btn-primary">Edit Profile</button>
                </div>

                <!-- Profile Display View -->
                <div id="profile-display">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="profile-display-img" src="<?= htmlspecialchars($profilePicture) ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="col-md-8">
                            <p><strong>Full Name:</strong> <span id="display-full-name"><?= htmlspecialchars($admin['full_name'] ?? 'N/A') ?></span></p>
                            <p><strong>Username:</strong> <span id="display-username"><?= htmlspecialchars($admin['username'] ?? 'N/A') ?></span></p>
                            <p><strong>Email Address:</strong> <span id="display-email"><?= htmlspecialchars($admin['email'] ?? 'N/A') ?></span></p>
                            <p><strong>Phone Number:</strong> <span id="display-phone"><?= htmlspecialchars($admin['phone_number'] ?? 'N/A') ?></span></p>
                        </div>
                    </div>
                </div>

                <!-- Profile Edit Form (Initially Hidden) -->
                <form id="profile-edit-form" method="POST" action="" enctype="multipart/form-data" class="mt-4" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="profile-edit-img" src="<?= htmlspecialchars($profilePicture) ?>" alt="Profile Picture" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Upload New Picture</label>
                                <input class="form-control" type="file" id="profile_picture" name="profile_picture">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($admin['phone_number'] ?? '') ?>">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            <button type="button" id="cancel-profile-edit-btn" class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Change Password</h5>
                 <?php if ($passwordResult && $passwordResult['success']): ?>
                    <div class="alert alert-success mt-3" role="alert"><?= htmlspecialchars($passwordResult['message']) ?></div>
                <?php elseif ($passwordResult && !$passwordResult['success']): ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?php foreach ($passwordResult['errors'] as $error): ?>
                            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="mt-4">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelProfileEditBtn = document.getElementById('cancel-profile-edit-btn');
    const profileDisplay = document.getElementById('profile-display');
    const profileEditForm = document.getElementById('profile-edit-form');
    const profileForm = document.getElementById('profile-edit-form');

    editProfileBtn.addEventListener('click', () => {
        profileDisplay.style.display = 'none';
        profileEditForm.style.display = 'block';
        editProfileBtn.style.display = 'none';
    });

    cancelProfileEditBtn.addEventListener('click', () => {
        profileDisplay.style.display = 'block';
        profileEditForm.style.display = 'none';
        editProfileBtn.style.display = 'block';
    });

    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const alertContainer = document.querySelector('.card-body'); // Container to show alerts

        // Remove old alerts
        const oldAlert = alertContainer.querySelector('.alert');
        if (oldAlert) {
            oldAlert.remove();
        }

        fetch('', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            let alertHtml = '';
            if (data.success) {
                // Update display view with new data
                document.getElementById('display-full-name').textContent = data.admin.full_name || 'N/A';
                document.getElementById('display-username').textContent = data.admin.username || 'N/A';
                document.getElementById('display-email').textContent = data.admin.email || 'N/A';
                document.getElementById('display-phone').textContent = data.admin.phone_number || 'N/A';
                
                // Update image sources
                const newImageUrl = data.admin.profile_picture_url;
                document.getElementById('profile-display-img').src = newImageUrl;
                document.getElementById('profile-edit-img').src = newImageUrl;
                
                // Update form input values as well
                document.getElementById('full_name').value = data.admin.full_name || '';
                document.getElementById('username').value = data.admin.username || '';
                document.getElementById('email').value = data.admin.email || '';
                document.getElementById('phone_number').value = data.admin.phone_number || '';


                // Show success message
                alertHtml = `<div class="alert alert-success" role="alert">${data.message}</div>`;

                // Switch back to display view
                cancelProfileEditBtn.click();

            } else {
                // Build error message list
                let errorList = data.errors.map(error => `<p class="mb-0">${error}</p>`).join('');
                alertHtml = `<div class="alert alert-danger" role="alert">${errorList}</div>`;
            }
            // Prepend the new alert
            profileDisplay.insertAdjacentHTML('beforebegin', alertHtml);
        })
        .catch(error => {
            console.error('Error:', error);
            const errorHtml = `<div class="alert alert-danger" role="alert">An unexpected error occurred. Please try again.</div>`;
            profileDisplay.insertAdjacentHTML('beforebegin', errorHtml);
        });
    });
});
</script>
