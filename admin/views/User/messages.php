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

        <!-- Search and Filter Bar -->
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by user name, message content...">
                    </div>
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-select">
                            <option value="all" selected>All Statuses</option>
                            <option value="unread">Unread</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                         <button class="btn btn-primary" onclick="applyFilters()">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Participants</th>
                                <th scope="col">Last Message</th>
                                <th scope="col">Last Activity</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="threads-table-body">
                            <!-- Threads will be loaded here -->
                            <tr>
                                <td colspan="5" class="text-center p-4">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
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
        const tableBody = document.getElementById('threads-table-body');
        const baseUrl = "<?php echo rtrim(dirname($_SERVER['PHP_SELF'], 4), '/'); ?>";
        let allThreads = []; // To store all fetched threads for client-side filtering

        const fetchThreads = async () => {
            try {
                const response = await fetch(`${baseUrl}/admin/api/get_message_threads.php`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                allThreads = await response.json();
                renderThreads(allThreads);
            } catch (error) {
                console.error("Could not fetch threads:", error);
                tableBody.innerHTML = `<tr><td colspan="5"><div class="alert alert-danger" role="alert">
                    Failed to load messages. Please check the console for more details.
                </div></td></tr>`;
            }
        };

        const renderThreads = (threads) => {
            if (!threads.length) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">No message threads found.</td></tr>';
                return;
            }

            tableBody.innerHTML = ''; // Clear previous content

            threads.forEach(thread => {
                const user1 = thread.user1;
                const user2 = thread.user2;

                // Function to determine the correct image source
                const getAvatarSrc = (profilePhoto) => {
                    if (!profilePhoto) {
                        return `${baseUrl}/assets/images/profile/default-profile.png`;
                    }
                    // Check if it's a full external URL
                    if (profilePhoto.startsWith('http://') || profilePhoto.startsWith('https://')) {
                        return profilePhoto;
                    }
                    // Handle root-relative paths (e.g., /storage/...)
                    if (profilePhoto.startsWith('/')) {
                        return `${baseUrl}${profilePhoto}`;
                    }
                    // Handle other relative paths
                    return `${baseUrl}/${profilePhoto}`;
                };

                const participantsHtml = `
                    <div class="d-flex align-items-center">
                        <img src="${getAvatarSrc(user1?.profile_photo)}" alt="${user1?.full_name}" class="rounded-circle" width="40" height="40" title="${user1?.full_name} (ID: ${user1?.id})">
                        <img src="${getAvatarSrc(user2?.profile_photo)}" alt="${user2?.full_name}" class="rounded-circle ms-n2" width="40" height="40" title="${user2?.full_name} (ID: ${user2?.id})">
                        <div class="ms-2">
                            <div class="fw-bold">${escapeHTML(user1?.full_name || 'N/A')}</div>
                            <div class="text-muted">${escapeHTML(user2?.full_name || 'N/A')}</div>
                        </div>
                    </div>
                `;

                const statusBadge = thread.unread_count > 0 
                    ? `<span class="badge bg-danger">${thread.unread_count} Unread</span>`
                    : `<span class="badge bg-success">Read</span>`;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${participantsHtml}</td>
                    <td>${escapeHTML(thread.last_message.substring(0, 50))}${thread.last_message.length > 50 ? '...' : ''}</td>
                    <td>${new Date(thread.updated_at).toLocaleString()}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-info" title="View Details">View</button>
                        <button class="btn btn-sm btn-warning" title="Archive">Archive</button>
                        <button class="btn btn-sm btn-danger" title="Delete">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        };
        
        window.applyFilters = () => {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            const filteredThreads = allThreads.filter(thread => {
                const user1Name = thread.user1?.full_name.toLowerCase() || '';
                const user2Name = thread.user2?.full_name.toLowerCase() || '';
                const lastMessage = thread.last_message.toLowerCase();

                const matchesSearch = user1Name.includes(searchTerm) || user2Name.includes(searchTerm) || lastMessage.includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || (statusFilter === 'unread' && thread.unread_count > 0);

                return matchesSearch && matchesStatus;
            });

            renderThreads(filteredThreads);
        };

        function escapeHTML(str) {
            if (str === null || str === undefined) return '';
            var p = document.createElement("p");
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
        }

        fetchThreads();
        setInterval(fetchThreads, 15000); // Refresh every 15 seconds
    });
</script>
