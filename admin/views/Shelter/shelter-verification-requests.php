<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// Define BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$project_root = preg_replace('/\/admin\/views\/Shelter\/[^\/]+$/', '', $script_name);
define('BASE_URL', $protocol . $host . $project_root);

require_once dirname(__DIR__, 2) . '/controllers/Shelter/shelter-verification-requests-controller.php';

$documents = ShelterVerificationRequestsController::fetchDocuments();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['doc_id'])) {
    $docId = (int)$_POST['doc_id'];
    $action = $_POST['action'];
    $adminId = AdminSessionManager::getAdminId();
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    $result = ShelterVerificationRequestsController::updateDocumentStatus($docId, $status, $adminId);

    if ($result) {
        $document = ShelterVerificationRequestsController::getDocumentById($docId);
        if ($document) {
            $shelterId = $document['shelter_id'];
            if ($status === 'approved') {
                // If approved, check if all documents for the shelter are now approved
                if (ShelterVerificationRequestsController::areAllDocumentsApproved($shelterId)) {
                    // If all are approved, verify the shelter
                    ShelterVerificationRequestsController::verifyShelter($shelterId, $adminId);
                }
            } elseif ($status === 'rejected') {
                // If any document is rejected, reject the shelter verification
                ShelterVerificationRequestsController::rejectShelter($shelterId, $adminId);
            }
        }

        // Redirect to the same page to see the changes
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle error, maybe set a session message
        $_SESSION['error_message'] = "Failed to update document status.";
    }
}

?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>

<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Shelter Verification Requests</h5>
                <p class="mb-0">Review and approve or reject shelter verification documents.</p>

                <div class="table-responsive mt-4">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Shelter Name</th>
                                <th scope="col">Document Type</th>
                                <th scope="col">Document</th>
                                <th scope="col">Submission Date</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documents)) : ?>
                                <tr>
                                    <td colspan="5" class="text-center">No pending documents found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($documents as $doc) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doc['shelter_name']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['doc_type']))); ?></td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="<?php echo BASE_URL . '/' . htmlspecialchars($doc['file_path']); ?>">
                                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($doc['file_path']); ?>" alt="Document Thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Document View</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid" alt="Document Image">
                </div>
            </div>
        </div>
    </div>

    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var imageModal = document.getElementById('imageModal');
        imageModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var imgSrc = button.getAttribute('data-img-src');
            var modalImage = imageModal.querySelector('#modalImage');
            modalImage.src = imgSrc;
        });
    });
</script>
