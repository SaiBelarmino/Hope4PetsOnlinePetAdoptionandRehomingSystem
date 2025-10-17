<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/AdoptManagementController.php';
require_once __DIR__ . '/../controllers/PetController.php';

$petId = (int)($_GET['pet_id'] ?? 0);
$pet = null;
if ($petId > 0) {
    // Try to fetch pet details - using PetController list and match, or AdoptController.details if available
    $p = AdoptController::details($petId);
    if ($p) {
        $pet = $p;
    } else {
        // fallback: try PetController to find in available list
        $list = PetController::fetchAvailablePets(1, 0);
        foreach ($list as $item) {
            if ((int)$item['id'] === $petId) { $pet = $item; break; }
        }
    }
}

$pageTitle = 'Adoption Process';

// Require login and verification before continuing (do this before any output)
if (empty($_SESSION['user']['id'])) {
    if (!function_exists('set_flash')) {
        function set_flash($type, $message) { $_SESSION['flash'] = ['type'=>$type,'message'=>$message]; }
    }
    set_flash('error', 'You must be logged in to start the adoption process.');
    header('Location: ./authentication-login.php');
    exit;
}

if (empty($_SESSION['user']['is_verified']) || !$_SESSION['user']['is_verified']) {
    if (!function_exists('set_flash')) {
        function set_flash($type, $message) { $_SESSION['flash'] = ['type'=>$type,'message'=>$message]; }
    }
    set_flash('error', 'Your account must be verified before you can adopt a pet. Please upload your ID on your profile.');
    header('Location: ./MyProfile.php');
    exit;
}

?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Adoption Process</h4>
                    <?php if (!$pet): ?>
                        <div class="alert alert-warning">Pet not found or no longer available.</div>
                        <a href="./pets.php" class="btn btn-secondary">Back to Browse</a>
                    <?php else: ?>
                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <div class="ratio ratio-4x3 overflow-hidden">
                                    <?php $photo = htmlspecialchars($pet['photo'] ?? ($pet['photo_raw'] ?? '/storage/uploads/images/default.png')); ?>
                                    <img src="<?php echo $photo; ?>" class="card-img-top object-fit-cover" alt="<?php echo htmlspecialchars($pet['name'] ?? 'Pet'); ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <h5><?php echo htmlspecialchars($pet['name'] ?? 'Unnamed'); ?> <small class="text-muted"><?php echo htmlspecialchars($pet['breed'] ?? ''); ?></small></h5>
                                <div class="small text-muted mb-2">Species: <?php echo htmlspecialchars($pet['species'] ?? ''); ?> · Age: <?php echo htmlspecialchars($pet['age'] ?? ''); ?></div>
                                <p><?php echo nl2br(htmlspecialchars($pet['description'] ?? 'No description')); ?></p>

                                <form method="post" action="../controllers/AdoptManagementController.php">
                                    <input type="hidden" name="action" value="request">
                                    <input type="hidden" name="pet_id" value="<?php echo (int)$petId; ?>">

                                    <div class="mb-2">
                                        <label class="form-label">Your full name</label>
                                        <input type="text" name="applicant_name" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Phone number</label>
                                        <input type="text" name="applicant_phone" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="applicant_address" class="form-control" required value="<?php echo htmlspecialchars($_SESSION['user']['address'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message / Why you want to adopt</label>
                                        <textarea name="applicant_message" class="form-control" rows="3"><?php echo htmlspecialchars(''); ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success">Confirm Adoption Request</button>
                                    <a href="./pets.php" class="btn btn-secondary">Cancel</a>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
