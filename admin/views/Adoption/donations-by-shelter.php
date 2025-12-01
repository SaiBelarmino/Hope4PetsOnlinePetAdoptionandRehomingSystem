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
                        <input type="text" id="shelterSearch" class="form-control" placeholder="Search for shelters...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 align-middle">
                        <thead class="text-dark fs-4">
                            <tr>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Shelter Name</h6>
                                </th>
                                <th class="border-bottom-0">
                                    <h6 class="fw-semibold mb-0">Total Donations</h6>
                                </th>
                                <th class="border-bottom-0">
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
                                        <td class="border-bottom-0">
                                            <p class="mb-0 fw-normal">$<?php echo number_format($item['total_amount'], 2); ?></p>
                                        </td>
                                        <td class="border-bottom-0">
                                            <span class="badge bg-primary rounded-3 fw-semibold"><?php echo $item['total_donations']; ?></span>
                                        </td>
                                        <td class="border-bottom-0">
                                            <a href="view-shelter-donations.php?shelter_id=<?php echo $item['shelter_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('shelterSearch');
    const tableBody = document.getElementById('shelterDonationsTable');
    const tableRows = tableBody.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < tableRows.length; i++) {
            let td = tableRows[i].getElementsByTagName('td')[0]; // Shelter Name column
            if (td) {
                let txtValue = td.textContent || td.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tableRows[i].style.display = "";
                } else {
                    tableRows[i].style.display = "none";
                }
            }
        }
    });
});
</script>
