(function pollStats() {
    const url = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/admin/api/admin-stats.php';
    fetch(url, { cache: 'no-store' })
        .then(r => r.ok ? r.json() : Promise.reject(r.statusText))
        .then(data => {
            if (!data) return;
            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val ?? 0;
            };
            set('totalPets', data.total_pets);
            set('adoptionTotal', data.adoption_requests_total);
            set('adoptionPending', data.adoption_requests_pending);
            set('approvedAdoptions', data.approved_adoptions);
            set('registeredUsers', data.registered_users);
            set('totalShelters', data.total_shelters);
        })
        .catch(() => { /* silent */ })
        .finally(() => setTimeout(pollStats, 10000)); // poll every 10s
})();

document.addEventListener('DOMContentLoaded', function () {
    // Access data from the window object
    const adoptionTrendsData = window.dashboardData.adoptionTrends;
    const petDistributionData = window.dashboardData.petDistribution;
    const userRegistrationsData = window.dashboardData.userRegistrations;

    // Helper function to check if data is valid
    const isDataValid = (data) => data && Array.isArray(data) && data.length > 0;

    /**
     * --------------------------------------------------------------------------
     * 1. Monthly Adoption Trends (Line Chart)
     * --------------------------------------------------------------------------
     */
    if (typeof adoptionTrendsData !== 'undefined' && isDataValid(adoptionTrendsData)) {
        const adoptionTrendsOptions = {
            series: [{
                name: "Approved Adoptions",
                data: adoptionTrendsData.map(item => item.count)
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: false
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: adoptionTrendsData.map(item => item.month),
                title: {
                    text: 'Month'
                }
            },
            yaxis: {
                title: {
                    text: 'Number of Adoptions'
                },
                min: 0,
                forceNiceScale: true, // Ensure y-axis has nice, round numbers
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " adoptions"
                    }
                }
            },
            colors: ['#5D87FF']
        };

        const adoptionTrendsChart = new ApexCharts(document.querySelector("#adoption-trends-chart"), adoptionTrendsOptions);
        adoptionTrendsChart.render();
    } else {
        document.querySelector("#adoption-trends-chart").innerHTML = "<div class='text-center p-4'>No adoption trend data available.</div>";
    }


    /**
     * --------------------------------------------------------------------------
     * 2. Pet Types Distribution (Donut Chart)
     * --------------------------------------------------------------------------
     */
    if (typeof petDistributionData !== 'undefined' && isDataValid(petDistributionData)) {
        const petTypesOptions = {
            series: petDistributionData.map(item => item.count),
            chart: {
                type: 'donut',
                height: 350
            },
            labels: petDistributionData.map(item => item.type),
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " pets"
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex]
                },
            },
            legend: {
                position: 'bottom'
            }
        };

        const petTypesChart = new ApexCharts(document.querySelector("#pet-types-chart"), petTypesOptions);
        petTypesChart.render();
    } else {
        document.querySelector("#pet-types-chart").innerHTML = "<div class='text-center p-4'>No pet distribution data available.</div>";
    }


    /**
     * --------------------------------------------------------------------------
     * 3. New User Registrations (Line Chart)
     * --------------------------------------------------------------------------
     */
    if (typeof userRegistrationsData !== 'undefined' && isDataValid(userRegistrationsData)) {
        const userRegistrationsOptions = {
            series: [{
                name: "New Users",
                data: userRegistrationsData.map(item => item.count)
            }],
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: false
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                type: 'datetime',
                categories: userRegistrationsData.map(item => item.date),
                title: {
                    text: 'Date'
                },
                labels: {
                    format: 'MMM dd'
                }
            },
            yaxis: {
                title: {
                    text: 'Number of New Users'
                },
                min: 0,
                forceNiceScale: true,
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy'
                },
                y: {
                    formatter: function (val) {
                        return val + " users"
                    }
                }
            },
            colors: ['#13DEB9']
        };

        const userRegistrationsChart = new ApexCharts(document.querySelector("#user-registrations-chart"), userRegistrationsOptions);
        userRegistrationsChart.render();
    } else {
        document.querySelector("#user-registrations-chart").innerHTML = "<div class='text-center p-4'>No new user registration data available.</div>";
    }
});