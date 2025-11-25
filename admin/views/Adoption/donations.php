<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
require_once dirname(__DIR__, 2) . '/controllers/Adoption/donations-controller.php';

SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// Fetch data
$shelters = DonationsController::getAllShelters();
$summary = DonationsController::getDonationsSummary();

// Filters and Pagination
$search = $_GET['search'] ?? '';
$filter_shelter = $_GET['shelter'] ?? '';
$filter_status = $_GET['status'] ?? '';
$sort_by = $_GET['sort'] ?? 'd.created_at DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;

$filters = [
    'search' => $search,
    'shelter_id' => $filter_shelter,
    'status' => $filter_status,
];

$donations = DonationsController::getDonations($filters, $sort_by, $page, $limit);
$totalDonations = DonationsController::countDonations($filters);
$totalPages = ceil($totalDonations / $limit);

$donation_statuses = ['pending', 'completed', 'failed', 'refunded'];
$payment_methods = ['credit_card', 'paypal', 'gcash', 'paymaya', 'bank_transfer', 'other'];

?>
<?php include dirname(__DIR__, 2) . '/sidebar.php'; ?>
<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Admin Portal > Donations Monitoring</h5>
                </div>

                <!-- Search and Filters -->
                <form method="GET" action="">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search User, Shelter..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="shelter" class="form-select">
                                <option value="">Filter by Shelter</option>
                                <?php foreach ($shelters as $shelter) : ?>
                                    <option value="<?= $shelter['id'] ?>" <?= $filter_shelter == $shelter['id'] ? 'selected' : '' ?>><?= htmlspecialchars($shelter['shelter_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Filter by Status</option>
                                <?php foreach ($donation_statuses as $status) : ?>
                                    <option value="<?= $status ?>" <?= $filter_status == $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort" class="form-select">
                                <option value="d.created_at DESC" <?= $sort_by == 'd.created_at DESC' ? 'selected' : '' ?>>Sort by Date (Newest)</option>
                                <option value="d.created_at ASC" <?= $sort_by == 'd.created_at ASC' ? 'selected' : '' ?>>Sort by Date (Oldest)</option>
                                <option value="d.amount DESC" <?= $sort_by == 'd.amount DESC' ? 'selected' : '' ?>>Sort by Amount (High)</option>
                                <option value="d.amount ASC" <?= $sort_by == 'd.amount ASC' ? 'selected' : '' ?>>Sort by Amount (Low)</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-start">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Apply</button>
                            <a href="donations.php" class="btn btn-outline-secondary ms-2"><i class="ti ti-refresh me-1"></i>Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Donations Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User / Donor</th>
                                <th>Shelter</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($donations)) : ?>
                                <tr>
                                    <td colspan="7" class="text-center">No donations found.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($donations as $donation) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($donation['donor']) ?></td>
                                        <td><?= htmlspecialchars($donation['shelter_name'] ?? 'N/A') ?></td>
                                        <td>₱<?= number_format($donation['amount'], 2) ?></td>
                                        <td><?= date('m/d/Y', strtotime($donation['created_at'])) ?></td>
                                        <td><span class="badge bg-light-<?= strtolower($donation['status']) == 'completed' ? 'success' : 'warning' ?> text-dark-emphasis"><?= ucfirst(htmlspecialchars($donation['status'])) ?></span></td>
                                        <td><?= ucwords(str_replace('_', ' ', htmlspecialchars($donation['payment_method']))) ?></td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Donation Actions">
                                                <a href="#" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i> View</a>
                                                <a href="#" class="btn btn-sm btn-outline-danger"><i class="ti ti-receipt-refund"></i> Refund</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary and Pagination -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <p><strong>Total Donations (Completed):</strong> ₱<?= number_format($summary['total_donations'] ?? 0, 2) ?></p>
                        <p><strong>Total Unique Donors:</strong> <?= $summary['total_donors'] ?? 0 ?></p>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1) : ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>">Previous</a></li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a></li>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages) : ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <div class="btn-group" role="group" aria-label="Page Actions">
                        <button class="btn btn-success"><i class="ti ti-file-spreadsheet me-1"></i>Export CSV</button>
                        <button class="btn btn-secondary"><i class="ti ti-file-report me-1"></i>Generate Report</button>
                        <a href="donations.php" class="btn btn-info"><i class="ti ti-refresh me-1"></i>Refresh</a>
                    </div>
                </div>

                <!-- Donation Statistics Charts -->
                <div class="row mt-5">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Donations per Shelter</h5>
                                <div id="donationByShelterChartContainer" style="position: relative; height:400px; width:100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Donations by Payment Method</h5>
                                <div id="donationByCategoryChartContainer" style="position: relative; height:400px; width:100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<!-- React and Chart.js scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.7.1/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/react-chartjs-2@3.3.0/dist/react-chartjs-2.min.js"></script>

<!-- Babel for JSX Transformation -->
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<!-- Chart Components -->
<script type="text/babel">
  const { useState, useEffect } = React;
  const { Pie, Bar } = window.ReactChartJs2;

  // Register Chart.js components
  Chart.register(Chart.ArcElement, Chart.CategoryScale, Chart.LinearScale, Chart.BarElement, Chart.Tooltip, Chart.Legend, Chart.Title);

  // Generic Chart Component
  const ChartComponent = ({ chartType, endpoint, title, labelField, dataField }) => {
    const [chartData, setChartData] = useState(null); // Start with null
    const [error, setError] = useState(null);

    useEffect(() => {
      let isMounted = true; // Flag to prevent state update on unmounted component
      
      const fetchData = async () => {
        try {
          const response = await fetch(endpoint);
          if (!response.ok) {
            throw new Error(`Network response was not ok (${response.status})`);
          }
          const apiResponse = await response.json();

          if (isMounted) {
            if (apiResponse.success && Array.isArray(apiResponse.data)) {
              const labels = apiResponse.data.map(item => item[labelField] || 'Unnamed');
              const data = apiResponse.data.map(item => item[dataField]);
              
              setChartData({
                labels,
                datasets: [{
                  label: 'Total Donations (PHP)',
                  data,
                  backgroundColor: [
                    'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'
                  ],
                  borderColor: '#fff',
                  borderWidth: 1,
                }],
              });
            } else {
              throw new Error(apiResponse.message || 'Invalid data format from API');
            }
          }
        } catch (err) {
          if (isMounted) {
            setError(err.message);
          }
        }
      };

      fetchData();

      return () => {
        isMounted = false; // Cleanup function to set flag on unmount
      };
    }, [endpoint, labelField, dataField]);

    const options = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: chartType === 'Pie' ? 'right' : 'top' },
        title: { display: false }, // Title is handled by card-title
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.label || '';
              const value = chartType === 'Pie' ? context.parsed : context.parsed.y;
              if (value === null || value === undefined) return label;
              return `${label}: ${new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value)}`;
            }
          }
        }
      },
      scales: chartType === 'Bar' ? { y: { beginAtZero: true } } : undefined,
    };

    if (error) return <div className="alert alert-danger p-2 m-2">Error: {error}</div>;
    if (!chartData) return <div className="text-center p-5">Loading Chart...</div>;
    
    const ChartElement = chartType === 'Pie' ? Pie : Bar;
    return <ChartElement data={chartData} options={options} />;
  };

  // Render Bar Chart (Donations by Shelter)
  ReactDOM.render(
    <ChartComponent 
      chartType="Bar"
      endpoint="/Hope4PetsOnlinePetAdoptionandRehomingSystem/api/donations/by-shelter.php"
      title="Donations per Shelter"
      labelField="shelter_name"
      dataField="total_donations"
    />,
    document.getElementById('donationByShelterChartContainer')
  );

  // Render Pie Chart (Donations by Payment Method)
  ReactDOM.render(
    <ChartComponent 
      chartType="Pie"
      endpoint="/Hope4PetsOnlinePetAdoptionandRehomingSystem/api/donations/by-payment-method.php"
      title="Donations by Payment Method"
      labelField="payment_method"
      dataField="total_donations"
    />,
    document.getElementById('donationByCategoryChartContainer')
  );
</script>
