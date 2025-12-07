<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

include_once dirname(__DIR__, 3) . '/admin/controllers/Adoption/donations-by-shelter-controller.php';
$donationsByShelter = DonationsByShelterController::totals();
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="card-title fw-semibold">Donations by Shelter</h5>
                        <p class="mb-0">Breakdown of total donations received by each shelter.</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="shelterSearch" class="form-control" placeholder="Search for shelters...">
                            <button class="btn btn-outline-secondary" type="button" id="refreshSearchBtn" title="Refresh list">
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Shelter Name</h6>
                                </th>
                                <th class="border-bottom-0 text-center">
                                    <h6 class="fw-semibold mb-0">Total Donations</h6>
                                </th>
                                <th class="border-bottom-0 text-center">
                                    <h6 class="fw-semibold mb-0">Donation Count</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Actions</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="shelterDonationsTable">
                            <?php if (empty($donationsByShelter)) : ?>
                                <tr>
                                    <td colspan="4" class="text-center">No donation data found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($donationsByShelter as $item) : ?>
                                    <tr>
                                        <td class="border-bottom-0">
                                            <h6 class="fw-semibold mb-0"><?php echo htmlspecialchars($item['shelter_name']); ?></h6>
                                        </td>
                                        <td class="border-bottom-0 text-center">
                                            <p class="mb-0 fw-normal">₱<?php echo number_format($item['total_amount'], 2); ?></p>
                                        </td>
                                        <td class="border-bottom-0 text-center">
                                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-3 fw-semibold"><?php echo $item['total_donations']; ?></span>
                                        </td>
                                        <td class="border-bottom-0">
                                            <button type="button" class="btn btn-outline-primary btn-sm view-details-btn" data-bs-toggle="modal" data-bs-target="#donationDetailsModal" data-shelter-id="<?php echo $item['shelter_id']; ?>" data-shelter-name="<?php echo htmlspecialchars($item['shelter_name']); ?>">
                                                View Details
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
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<!-- Donation Details Modal -->
<div class="modal fade" id="donationDetailsModal" tabindex="-1" aria-labelledby="donationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="donationDetailsModalLabel">Donation Details for <span id="modalShelterName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="donationDetailsTbody">
                            <!-- Donation details will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('shelterSearch');
    const tableBody = document.getElementById('shelterDonationsTable');
    const tableRows = tableBody.getElementsByTagName('tr');
    const refreshBtn = document.getElementById('refreshSearchBtn');

    function filterTable() {
        const filter = searchInput.value.toLowerCase();
        let hasVisibleRows = false;
        for (let i = 0; i < tableRows.length; i++) {
            let td = tableRows[i].getElementsByTagName('td')[0]; // Shelter Name column
            if (td) {
                if (td.colSpan === 4) { // This is the "No data" row
                    continue;
                }
                let txtValue = td.textContent || td.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tableRows[i].style.display = "";
                    hasVisibleRows = true;
                } else {
                    tableRows[i].style.display = "none";
                }
            }
        }
    }

    searchInput.addEventListener('keyup', filterTable);

    refreshBtn.addEventListener('click', function() {
        searchInput.value = '';
        for (let i = 0; i < tableRows.length; i++) {
            tableRows[i].style.display = "";
        }
    });

    const donationDetailsModal = document.getElementById('donationDetailsModal');
    donationDetailsModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const shelterId = button.getAttribute('data-shelter-id');
        const shelterName = button.getAttribute('data-shelter-name');

        const modalTitle = donationDetailsModal.querySelector('#modalShelterName');
        const modalBodyTbody = donationDetailsModal.querySelector('#donationDetailsTbody');

        modalTitle.textContent = shelterName;
        modalBodyTbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

        fetch(`../api/get-shelter-donations.php?shelter_id=${shelterId}`)
            .then(response => response.json())
            .then(data => {
                modalBodyTbody.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(donation => {
                        const row = `<tr>
                            <td>${escapeHtml(donation.donor)}</td>
                            <td>₱${parseFloat(donation.amount).toFixed(2)}</td>
                            <td>${escapeHtml(donation.payment_method)}</td>
                            <td><span class="badge bg-success-subtle text-success-emphasis rounded-3 fw-semibold">${escapeHtml(donation.status)}</span></td>
                            <td>${new Date(donation.created_at).toLocaleDateString()}</td>
                        </tr>`;
                        modalBodyTbody.innerHTML += row;
                    });
                } else {
                    modalBodyTbody.innerHTML = '<tr><td colspan="5" class="text-center">No donations found for this shelter.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error fetching donation details:', error);
                modalBodyTbody.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load donation details.</td></tr>';
            });
    });

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>
