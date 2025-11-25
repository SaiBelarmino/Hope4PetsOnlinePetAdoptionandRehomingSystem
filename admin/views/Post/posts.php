<?php
require_once __DIR__ . '/../../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

$controllerPath = dirname(__DIR__, 2) . '/controllers/Post/posts-controller.php';
if (file_exists($controllerPath)) {
    include $controllerPath;
} else {
    throw new RuntimeException('Posts controller not found: ' . $controllerPath);
}

// Get query parameters
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?: 'created_at';
$order = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_STRING) ?: 'DESC';
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

$sort_order = $sort . '_' . $order;

$options = [
    'page' => $page,
    'limit' => 10,
    'search' => $search,
    'sort' => 'p.' . $sort,
    'order' => $order,
    'status' => $status,
    'type' => $type
];

$result = PostsController::list($options);
$posts = $result['posts'];
$totalPages = $result['total_pages'];
$currentPage = $result['current_page'];

$postTypes = ['Educational', 'Event', 'Article', 'News']; // Example post types
$postStatuses = ['pending', 'approved', 'rejected']; // Existing statuses
?>
<?php
include dirname(__DIR__, 2) . '/sidebar.php';
?>

<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">All Users Posts</h3>
        </div>

        <!-- Controls -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="search" name="search" class="form-control" placeholder="Search by title or author..." value="<?= htmlspecialchars($search ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">Filter by Type</option>
                                <?php foreach ($postTypes as $postType): ?>
                                    <option value="<?= strtolower($postType) ?>" <?= ($type ?? '') === strtolower($postType) ? 'selected' : '' ?>><?= $postType ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                             <select name="status" class="form-select">
                                <option value="">Filter by Status</option>
                                <?php foreach ($postStatuses as $postStatus): ?>
                                    <option value="<?= $postStatus ?>" <?= ($status ?? '') === $postStatus ? 'selected' : '' ?>><?= ucfirst($postStatus) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort_order" class="form-select" onchange="this.form.submit()">
                                <option value="created_at_DESC" <?= $sort_order === 'created_at_DESC' ? 'selected' : '' ?>>Sort by Newest</option>
                                <option value="created_at_ASC" <?= $sort_order === 'created_at_ASC' ? 'selected' : '' ?>>Sort by Oldest</option>
                                <option value="title_ASC" <?= $sort_order === 'title_ASC' ? 'selected' : '' ?>>Sort by Title (A-Z)</option>
                                <option value="title_DESC" <?= $sort_order === 'title_DESC' ? 'selected' : '' ?>>Sort by Title (Z-A)</option>
                            </select>
                            <!-- Hidden fields for sort and order -->
                            <input type="hidden" name="sort" value="<?= explode('_', $sort_order)[0] ?>">
                            <input type="hidden" name="order" value="<?= explode('_', $sort_order)[1] ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Go</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">No posts found matching your criteria.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Visibility</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title'] ?? '–') ?></td>
                            <td><?= htmlspecialchars(ucfirst($post['type'] ?? 'N/A')) ?></td>
                            <td>
                                <?php
                                $status = htmlspecialchars($post['status'] ?? 'unknown');
                                $badgeClass = 'bg-secondary';
                                if ($status === 'approved') {
                                    $badgeClass = 'bg-success';
                                } elseif ($status === 'rejected') {
                                    $badgeClass = 'bg-danger';
                                } elseif ($status === 'pending') {
                                    $badgeClass = 'bg-warning';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= htmlspecialchars(ucfirst($post['visibility'] ?? 'N/A')) ?></td>
                            <td><?= htmlspecialchars((new DateTime($post['created_at']))->format('m/d/Y')) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewPost(<?= intval($post['id']) ?>)">View</button>
                                    <a href="/admin/posts/edit/<?= intval($post['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="/admin/posts/delete/<?= intval($post['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage - 1 ?>&<?= http_build_query(compact('search', 'status', 'type', 'sort', 'order')) ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(compact('search', 'status', 'type', 'sort', 'order')) ?>"><?= $i ?></a></li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage + 1 ?>&<?= http_build_query(compact('search', 'status', 'type', 'sort', 'order')) ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalLabel">Post Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="postModalBody">
                <div class="text-center">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
function viewPost(postId) {
    const modal = new bootstrap.Modal(document.getElementById('postModal'));
    const body = document.getElementById('postModalBody');
    body.innerHTML = '<div class="text-center">Loading...</div>';
    modal.show();

    fetch(`/admin/ajax/get-post-details.php?id=${postId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(data => {
            body.innerHTML = data;
        })
        .catch(error => {
            body.innerHTML = '<div class="alert alert-danger">Error loading post details. Please try again.</div>';
            console.error('Error:', error);
        });
}

// Handle sort dropdown change to submit form
document.addEventListener('DOMContentLoaded', function() {
    const sortOrderSelect = document.querySelector('select[name="sort_order"]');
    if (sortOrderSelect) {
        sortOrderSelect.addEventListener('change', function() {
            const [sort, order] = this.value.split('_');
            document.querySelector('input[name="sort"]').value = sort;
            document.querySelector('input[name="order"]').value = order;
            this.form.submit();
        });
    }
});
</script>

<?php include dirname(__DIR__, 2) . '/footer.php'; ?>
