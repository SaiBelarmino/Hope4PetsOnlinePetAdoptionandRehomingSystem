<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

require_once dirname(__DIR__, 2) . '/controllers/Adoption/adoption-requests-controller.php';
$requests = AdoptionRequestsController::getAdoptionRequests();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Adoption Requests</h5>
                <p>Manage adoption requests.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Pet Name</th>
                                <th>Applicant</th>
                                <th>Shelter</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center">No adoption requests found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($requests as $request) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['pet_name']) ?></td>
                                        <td><?= htmlspecialchars($request['applicant_name']) ?></td>
                                        <td><?= htmlspecialchars($request['shelter_name'] ?? 'N/A') ?></td>
                                        <td><span class="badge bg-light-info text-dark-emphasis"><?= ucfirst(htmlspecialchars($request['status'])) ?></span></td>
                                        <td><?= date('m/d/Y', strtotime($request['created_at'])) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary view-adoption-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewAdoptionModal"
                                                    data-pet-name="<?= htmlspecialchars($request['pet_name']) ?>"
                                                    data-applicant-name="<?= htmlspecialchars($request['adoption_applicant_name']) ?>"
                                                    data-applicant-phone="<?= htmlspecialchars($request['adoption_applicant_phone']) ?>"
                                                    data-applicant-address="<?= htmlspecialchars($request['adoption_applicant_address']) ?>"
                                                    data-shelter-name="<?= htmlspecialchars($request['shelter_name'] ?? 'N/A') ?>"
                                                    data-status="<?= htmlspecialchars($request['status']) ?>"
                                                    data-date="<?= date('F j, Y, g:i a', strtotime($request['created_at'])) ?>">
                                                View Adoption
                                            </button>
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

    <!-- View Adoption Modal -->
    <div class="modal fade" id="viewAdoptionModal" tabindex="-1" aria-labelledby="viewAdoptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAdoptionModalLabel">Adoption Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Pet Name:</strong><br><span id="modalPetName"></span></p>
                            <p><strong>Shelter:</strong><br><span id="modalShelterName"></span></p>
                            <p><strong>Status:</strong><br><span id="modalStatus"></span></p>
                            <p><strong>Date Applied:</strong><br><span id="modalDate"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Applicant Information</h5>
                            <p><strong>Name:</strong><br><span id="modalApplicantName"></span></p>
                            <p><strong>Phone:</strong><br><span id="modalApplicantPhone"></span></p>
                            <p><strong>Address:</strong><br><span id="modalApplicantAddress"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var viewAdoptionModal = document.getElementById('viewAdoptionModal');
            viewAdoptionModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                
                var petName = button.getAttribute('data-pet-name');
                var applicantName = button.getAttribute('data-applicant-name');
                var applicantPhone = button.getAttribute('data-applicant-phone');
                var applicantAddress = button.getAttribute('data-applicant-address');
                var shelterName = button.getAttribute('data-shelter-name');
                var status = button.getAttribute('data-status');
                var date = button.getAttribute('data-date');

                var modalPetName = viewAdoptionModal.querySelector('#modalPetName');
                var modalApplicantName = viewAdoptionModal.querySelector('#modalApplicantName');
                var modalApplicantPhone = viewAdoptionModal.querySelector('#modalApplicantPhone');
                var modalApplicantAddress = viewAdoptionModal.querySelector('#modalApplicantAddress');
                var modalShelterName = viewAdoptionModal.querySelector('#modalShelterName');
                var modalStatus = viewAdoptionModal.querySelector('#modalStatus');
                var modalDate = viewAdoptionModal.querySelector('#modalDate');

                modalPetName.textContent = petName;
                modalApplicantName.textContent = applicantName;
                modalApplicantPhone.textContent = applicantPhone;
                modalApplicantAddress.textContent = applicantAddress;
                modalShelterName.textContent = shelterName;
                modalStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                modalDate.textContent = date;
            });
        });
    </script>
</div>
