<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../controllers/AdoptionManagementController.php';

// ensure variables are defined to avoid warnings when view is accessed directly
$shelterId = $_SESSION['shelter_id'] ?? 0;
$requests = AdoptController::getRequests($shelterId);
$pageTitle = $pageTitle ?? 'Adoption Requests';
?>
<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left Sidebar -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        <!-- Center Content -->
        <div class="col-12 col-lg-6">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
            </div>

            <?php if (empty($requests)): ?>
            <div class="card">
                <div class="card-body text-center text-muted py-5">No adoption requests yet.</div>
            </div>
            <?php else: ?>
            <?php foreach ($requests as $r): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <?php
                                    $ownerId = $r['owner_id'] ?? '';
                                    $petPhotos = $r['pet_photo'] ?? '';

                                    // Normalize URL candidate
                                    if (empty($petPhotos)) {
                                        $relPath = '/storage/uploads/images/default.png';
                                    } elseif (strpos($petPhotos, '/storage/uploads') === 0 || preg_match('#^https?://#i', $petPhotos)) {
                                        // pet_photo already contains a storage path or full URL
                                        $relPath = $petPhotos;
                                    } else {
                                        // stored as filename only
                                        $relPath = '/storage/uploads/images/' . $ownerId . '/' . $petPhotos;
                                    }

                                    $candidates = [];
                                    $candidates[] = $relPath;
                                    // also try with project folder prefix
                                    $projectPrefix = '/Hope4PetsOnlinePetAdoptionandRehomingSystem';
                                    $candidates[] = $projectPrefix . $relPath;

                                    // find first that exists on filesystem (try both docroot+url and docroot+projectPrefix+url)
                                    $petImgPath = '/storage/uploads/images/default.png';
                                    foreach ($candidates as $url) {
                                        // possible filesystem paths to check
                                        $fs1 = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $url;
                                        $fs2 = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $projectPrefix . $url;
                                        if (file_exists($fs1)) {
                                            $petImgPath = $url;
                                            break;
                                        }
                                        if (file_exists($fs2)) {
                                            // when fs2 exists, use project-prefixed URL for browser
                                            $petImgPath = $projectPrefix . $url;
                                            break;
                                        }
                                    }
                                ?>
                        <img src="<?php echo htmlspecialchars($petImgPath); ?>" alt="pet" class="rounded-circle"
                            style="width:96px; height:96px; object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-1">
                                        <?php echo htmlspecialchars($r['pet_name'] ?? ('Pet ID: ' . ($r['pet_id'] ?? ''))); ?>
                                    </h5>
                                    <div class="small text-muted">Requested on
                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($r['created_at'] ?? ''))); ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="badge bg-<?php echo ($r['status']==='applied')? 'warning' : (($r['status']==='approved')? 'success' : 'secondary'); ?>"><?php echo htmlspecialchars(ucfirst($r['status'] ?? '')); ?></span>
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="mt-3 d-flex gap-2">
                                <?php if (($r['status'] ?? '') === 'applied'): ?>
                                <form method="post" action="../controllers/AdoptionActionController.php"
                                    class="d-inline">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="adoption_id" value="<?php echo (int)$r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="post" action="../controllers/AdoptionActionController.php"
                                    class="d-inline">
                                    <input type="hidden" name="action" value="deny">
                                    <input type="hidden" name="adoption_id" value="<?php echo (int)$r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Deny</button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled>Manage</button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#applicantModal"
                                    data-name="<?php echo htmlspecialchars($r['applicant_name'] ?? ''); ?>"
                                    data-phone="<?php echo htmlspecialchars($r['applicant_phone'] ?? ''); ?>"
                                    data-address="<?php echo htmlspecialchars($r['applicant_address'] ?? ''); ?>"
                                    data-message="<?php echo htmlspecialchars($r['applicant_message'] ?? ''); ?>"
                                    data-created="<?php echo htmlspecialchars($r['created_at'] ?? ''); ?>">View
                                    Applicant</button>
                                <!-- redundant 'Message Applicant' button hidden -->
                                <a href="./ChatMessages.php?user_id=<?php echo (int)($r['applicant_id'] ?? 0); ?>"
                                    class="btn btn-sm btn-outline-secondary d-none">Message Applicant</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <!-- Right Sidebar -->
        <div class="col-12 col-lg-2">
            <div class="card mb-3">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Total requests:</strong> <?php echo count($requests); ?></p>
                    <p class="mb-1"><strong>Pending:</strong>
                        <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='applied'; })); ?>
                    </p>
                    <p class="mb-1"><strong>Approved:</strong>
                        <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='approved'; })); ?>
                    </p>
                    <p class="mb-0"><strong>Denied:</strong>
                        <?php echo count(array_filter($requests, function($x){ return ($x['status'] ?? '')==='denied'; })); ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Tips</h6>
                    <p class="small text-muted mb-0">Approve only after contacting the applicant and verifying details.
                        Use messages to coordinate meetups.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applicant Modal -->
<div class="modal fade" id="applicantModal" tabindex="-1" aria-labelledby="applicantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicantModalLabel">Applicant Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="app-name"></span></p>
                <p><strong>Phone:</strong> <span id="app-phone"></span></p>
                <p><strong>Address:</strong> <span id="app-address"></span></p>
                <p><strong>Message:</strong> <span id="app-message"></span></p>
                <p class="text-muted small"><strong>Applied on:</strong> <span id="app-created"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="href=" ./ChatMessages.php?user_id=<?php echo (int)($r['applicant_id'] ?? 0); ?>"
                    id="app-message-link" class="btn btn-primary">Message Applicant</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var applicantModal = document.getElementById('applicantModal');
    applicantModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var name = button.getAttribute('data-name') || '';
        var phone = button.getAttribute('data-phone') || '';
        var address = button.getAttribute('data-address') || '';
        var message = button.getAttribute('data-message') || '';
        var created = button.getAttribute('data-created') || '';
        document.getElementById('app-name').textContent = name;
        document.getElementById('app-phone').textContent = phone;
        document.getElementById('app-address').textContent = address;
        document.getElementById('app-message').textContent = message;
        document.getElementById('app-created').textContent = created ? new Date(created)
            .toLocaleString() : '';
        // set message link to open chat with applicant id if available
        var applicantId = button.closest('.card').querySelector('a[href^="./ChatMessages.php"]')
            .getAttribute('href');
        var link = document.getElementById('app-message-link');
        link.setAttribute('href', applicantId || '#');
    });
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>