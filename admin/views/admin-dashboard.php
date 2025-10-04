<?php 
    // Use existing topbar includes
    include __DIR__ . '/../include/sidebar.php';
?>
<div class="body-wrapper">
    <?php
        // Use existing header includes
        include __DIR__ . '/../include/header.php';
     ?>
    <div class="container-fluid">
        <main class="py-2">
            <!-- Overview Stats -->
            <div class="row g-3 g-lg-4 mb-4 metrics-row">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Total Users</div>
                                <div class="h3 mb-0">1,284</div>
                                <div class="small mt-1 trend-up"><i class="ti ti-arrow-up-right"></i> 4.3% vs last month
                                </div>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="ti ti-users fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Active Pets Available</div>
                                <div class="h3 mb-0">96</div>
                                <div class="small mt-1 trend-up"><i class="ti ti-arrow-up-right"></i> 2.1% this week
                                </div>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="ti ti-paw fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Pending Applications</div>
                                <div class="h3 mb-0">18</div>
                                <div class="small mt-1 text-warning"><i class="ti ti-arrow-up-right"></i> 1 pending
                                    today</div>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="ti ti-hourglass fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small">Donations Received</div>
                                <div class="h3 mb-0">₱42,350</div>
                                <div class="small mt-1 trend-up"><i class="ti ti-arrow-up-right"></i> 7.8% MTD</div>
                            </div>
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="ti ti-currency-dollar fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-3 g-lg-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-white"><strong>Monthly Adoptions</strong></div>
                        <div class="card-body">
                            <div id="adoptionLineChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-white"><strong>Monthly Donations</strong></div>
                        <div class="card-body">
                            <div id="donationBarChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables + Notifications -->
            <div class="row g-3 g-lg-4">
                <div class="col-12 col-xl-8">
                    <div class="card h-100 mb-4 mb-xl-0">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <strong>Recent Adoption Applications</strong>
                            <a href="#" class="small">View all</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-auto-stack">
                                    <thead class="table-light">
                                        <tr>
                                            <th>User</th>
                                            <th>Pet</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Maria Santos</td>
                                            <td>Buddy (Dog)</td>
                                            <td><span class="badge bg-warning-subtle text-warning border">Pending</span>
                                            </td>
                                            <td>2025-10-01</td>
                                        </tr>
                                        <tr>
                                            <td>Jose Cruz</td>
                                            <td>Mittens (Cat)</td>
                                            <td><span
                                                    class="badge bg-success-subtle text-success border">Approved</span>
                                            </td>
                                            <td>2025-09-29</td>
                                        </tr>
                                        <tr>
                                            <td>Ana Dela Cruz</td>
                                            <td>Charlie (Dog)</td>
                                            <td><span class="badge bg-danger-subtle text-danger border">Rejected</span>
                                            </td>
                                            <td>2025-09-28</td>
                                        </tr>
                                        <tr>
                                            <td>Mark Reyes</td>
                                            <td>Luna (Cat)</td>
                                            <td><span class="badge bg-warning-subtle text-warning border">Pending</span>
                                            </td>
                                            <td>2025-09-28</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card h-100 mb-4">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <strong>Recent Reports</strong>
                            <a href="./reports.php" class="small">View all</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reporter</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jane D.</td>
                                            <td>Abuse</td>
                                            <td><span class="badge bg-danger-subtle text-danger border">Unread</span>
                                            </td>
                                            <td><a class="btn btn-outline-primary btn-sm" href="./reports.php">View</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Chris P.</td>
                                            <td>Missing</td>
                                            <td><span
                                                    class="badge bg-success-subtle text-success border">Resolved</span>
                                            </td>
                                            <td><a class="btn btn-outline-primary btn-sm" href="./reports.php">View</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Lea M.</td>
                                            <td>Stray</td>
                                            <td><span class="badge bg-warning-subtle text-warning border">In
                                                    Review</span></td>
                                            <td><a class="btn btn-outline-primary btn-sm" href="./reports.php">View</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Page-specific scripts: wait for footer assets to load (ApexCharts) -->
        <script>
        window.addEventListener('load', function() {
            if (typeof ApexCharts === 'undefined') return;
            function currentTheme(){ return document.body.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'; }
            function numberFmt(v){ return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g,','); }
            function baseCommon(){ const dark = currentTheme()==='dark'; return {
                tooltip: {
                    theme: currentTheme(),
                    y: {
                        formatter: function(val, opts){
                            if(opts.w.config.chart.type === 'bar') return '₱' + numberFmt(val);
                            return numberFmt(val);
                        }
                    },
                    custom: function({ series, seriesIndex, dataPointIndex, w }){
                        const val = series[seriesIndex][dataPointIndex];
                        const cat = w.globals.categoryLabels[dataPointIndex];
                        const isBar = w.config.chart.type === 'bar';
                        const color = w.config.colors[seriesIndex] || '#3b82f6';
                        const bg = dark ? '#1e293b' : '#ffffff';
                        const border = dark ? '#334155' : '#e2e8f0';
                        const txt = dark ? '#e2e8f0' : '#1e293b';
                        const sub = dark ? '#94a3b8' : '#64748b';
                        const displayVal = isBar ? '₱' + numberFmt(val) : numberFmt(val);
                        return `<div style="background:${bg};color:${txt};border:1px solid ${border};padding:8px 10px;border-radius:8px;min-width:140px;font-size:12px;box-shadow:0 4px 12px rgba(0,0,0,.15)">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:${color}"></span>
                                <strong style="font-size:12px;letter-spacing:.5px">${cat}</strong>
                            </div>
                            <div style="font-size:20px;font-weight:600;line-height:1;color:${txt}">${displayVal}</div>
                            <div style="margin-top:2px;font-size:11px;color:${sub}">${isBar? 'Donations':'Adoptions'} </div>
                        </div>`;
                    }
                },
                grid: { borderColor: dark ? '#334155' : '#e2e8f0' },
                xaxis: { labels: { style: { colors: dark ? '#cbd5e1' : '#475569' } } },
                yaxis: { labels: { style: { colors: dark ? '#cbd5e1' : '#475569' } } },
                markers: { strokeColors: dark ? '#0f172a' : '#ffffff' }
            }; }
            // Line Chart - Monthly Adoptions
            const adoptionOptions = Object.assign({
                chart: {
                    type: 'line',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Adoptions',
                    data: [12, 19, 14, 22, 28, 31, 26, 34, 30, 38, 40, 45]
                }],
                colors: ['#16a34a'],
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                        'Nov', 'Dec'
                    ]
                },
                yaxis: {
                    labels: {
                        formatter: (v) => Math.round(v)
                    }
                },
                grid: {
                    strokeDashArray: 4
                },
                markers: {
                    size: 3
                }
            }, baseCommon());
            const adoptionChart = new ApexCharts(document.querySelector('#adoptionLineChart'), adoptionOptions); adoptionChart.render();

            // Bar Chart - Monthly Donations
            const donationOptions = Object.assign({
                chart: {
                    type: 'bar',
                    height: 280,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Donations (₱k)',
                    data: [15, 12, 18, 22, 27, 30, 29, 35, 31, 40, 44, 48]
                }],
                colors: ['#3b82f6'],
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 4
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                        'Nov', 'Dec'
                    ]
                },
                grid: {
                    strokeDashArray: 4
                }
            }, baseCommon());
            const donationChart = new ApexCharts(document.querySelector('#donationBarChart'), donationOptions); donationChart.render();

            // Observe theme changes
            const observer = new MutationObserver(function(muts){
                for(const m of muts){
                    if(m.type==='attributes' && m.attributeName==='data-bs-theme'){
                        const common = baseCommon();
                        adoptionChart.updateOptions(common, false, true);
                        donationChart.updateOptions(common, false, true);
                    }
                }
            });
            observer.observe(document.body,{ attributes:true, attributeFilter:['data-bs-theme'] });
        });
        </script>
    </div>
    <?php include __DIR__ . '/../include/footer.php'; ?>
</div>