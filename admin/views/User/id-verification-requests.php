<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h3 class="mb-4">ID Verification Requests</h3>
                <p class="mb-4">Queue of user ID verifications pending approval.</p>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>ID Type</th>
                                <th>ID Image</th>
                                <th>Uploaded At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($verification_requests) && !empty($verification_requests)) {
                                foreach ($verification_requests as $request) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                                        <td><?php echo htmlspecialchars($request['id_type']); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($request['id_image_path']); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($request['id_image_path']); ?>" 
                                                     alt="ID Image" class="img-thumbnail" style="max-width: 100px;">
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['uploaded_at']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $request['status'] == 'pending' ? 'bg-warning' : 
                                                ($request['status'] == 'approved' ? 'bg-success' : 'bg-danger'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] == 'pending') : ?>
                                                <button class="btn btn-success btn-sm approve-btn" 
                                                        data-user-id="<?php echo htmlspecialchars($request['user_id']); ?>"
                                                        onclick="approveVerification(<?php echo htmlspecialchars($request['user_id']); ?>)">
                                                    Approve
                                                </button>
                                                <button class="btn btn-danger btn-sm reject-btn" 
                                                        data-user-id="<?php echo htmlspecialchars($request['user_id']); ?>"
                                                        onclick="rejectVerification(<?php echo htmlspecialchars($request['user_id']); ?>)">
                                                    Reject
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">No verification requests found.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<!-- Add this before the closing body tag -->
<script>
function approveVerification(userId) {
    if (confirm('Are you sure you want to approve this verification request?')) {
        $.ajax({
            url: '../controllers/User/verify-id-controller.php',
            type: 'POST',
            data: {
                user_id: userId,
                action: 'approve'
            },
            success: function(response) {
                const result = JSON.parse(response);
                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Verification request approved successfully.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message || 'Failed to approve verification request.',
                        icon: 'error'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing the request.',
                    icon: 'error'
                });
            }
        });
    }
}

function rejectVerification(userId) {
    Swal.fire({
        title: 'Reject Verification',
        text: 'Please provide a reason for rejection:',
        input: 'text',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for rejection!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controllers/User/verify-id-controller.php',
                type: 'POST',
                data: {
                    user_id: userId,
                    action: 'reject',
                    reason: result.value
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Verification request rejected successfully.',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to reject verification request.',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while processing the request.',
                        icon: 'error'
                    });
                }
            });
        }
    });
}
</script>

<?php
include dirname(__DIR__, 2) . '/controllers/User/id-verification-requests-controller.php';
?>
