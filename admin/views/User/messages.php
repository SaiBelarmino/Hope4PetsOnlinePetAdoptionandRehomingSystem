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
                <h5 class="card-title fw-semibold mb-4">User Messages</h5>
                <p class="mb-0">Live monitor of user message threads. Updates automatically.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="list-group" id="message-threads-container">
                    <!-- Message threads will be loaded here by JavaScript -->
                    <div class="text-center p-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include dirname(__DIR__, 2) . '/footer.php'; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const threadsContainer = document.getElementById('message-threads-container');
        const baseUrl = "<?php echo rtrim(dirname($_SERVER['PHP_SELF'], 4), '/'); ?>";

        const fetchThreads = async () => {
            try {
                const response = await fetch(`${baseUrl}/admin/api/get_messages.php`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const threads = await response.json();
                renderThreads(threads);
            } catch (error) {
                console.error("Could not fetch threads:", error);
                threadsContainer.innerHTML = `<div class="alert alert-danger" role="alert">
                    Failed to load messages. Please check the console for more details.
                </div>`;
            }
        };

        const renderThreads = (threads) => {
            if (!threads.length) {
                threadsContainer.innerHTML = '<p class="text-center p-4">No message threads found.</p>';
                return;
            }

            threadsContainer.innerHTML = ''; // Clear previous content

            threads.forEach(thread => {
                const participantsHtml = thread.participants.map(p => `
                    <img src="${baseUrl}/${p.profile_image || 'assets/images/profile/default-profile.png'}" alt="${p.full_name}" class="rounded-circle" width="40" height="40" title="${p.full_name}">
                `).join('');

                const threadElement = document.createElement('a');
                threadElement.href = '#'; // Add link to view thread details later
                threadElement.className = 'list-group-item list-group-item-action';
                threadElement.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${escapeHTML(thread.subject)}</h6>
                        <small class="text-muted">${new Date(thread.updated_at).toLocaleString()}</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <div class="d-flex align-items-center">
                            ${participantsHtml}
                        </div>
                        <small class="text-muted">Thread ID: ${thread.id}</small>
                    </div>
                `;
                threadsContainer.appendChild(threadElement);
            });
        };

        function escapeHTML(str) {
            var p = document.createElement("p");
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
        }

        // Fetch threads on initial load
        fetchThreads();

        // Set interval to fetch threads every 5 seconds for real-time updates
        setInterval(fetchThreads, 5000);
    });
</script>
