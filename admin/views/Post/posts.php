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

// Get query parameters for filters/sorting
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$media = filter_input(INPUT_GET, 'media', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // images/videos/text
$engagement = filter_input(INPUT_GET, 'engagement', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'created_at';
$order = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'DESC';

$options = [
    'page' => $page,
    'limit' => 30,
    'search' => $search,
    'type' => $type,
    'status' => $status,
    'media' => $media,
    'engagement' => $engagement,
    'sort' => $sort,
    'order' => $order
];

$result = PostsController::list($options);
$posts = $result['posts'];
$totalPages = $result['total_pages'];
$currentPage = $result['current_page'];
$totalRecords = $result['total_records'];

// For header stats (adjust as needed)
$totalLive = 0;
$totalHidden = 0;
foreach ($posts as $p) {
    if (($p['status'] ?? '') === 'visible') $totalLive++;
    else $totalHidden++;
}
?>
<?php include dirname(__DIR__, 2) . '/sidebar.php'; ?>

<div class="body-wrapper">
    <?php include dirname(__DIR__, 2) . '/header.php'; ?>
    <div class="container-fluid">

        <!-- HEADER SUMMARY -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 fw-bold">
                Total: <?= $totalRecords ?> posts |
                <span class="text-success">Live: <?= $totalLive ?></span> |
                <span class="text-danger">Hidden: <?= $totalHidden ?></span>
            </h4>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshPosts()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>

        <!-- QUICK FILTERS & SORT -->
        <div class="row mb-3 g-2">
            <div class="col-auto">
                <div class="btn-group" role="group">
                    <a href="?media=all" class="btn btn-outline-primary<?= !$media || $media=='all' ? ' active' : '' ?>">All</a>
                    <a href="?media=image" class="btn btn-outline-primary<?= $media=='image' ? ' active' : '' ?>">With Images</a>
                    <a href="?media=text" class="btn btn-outline-primary<?= $media=='text' ? ' active' : '' ?>">Text Only</a>
                    <a href="?media=video" class="btn btn-outline-primary<?= $media=='video' ? ' active' : '' ?>">Videos</a>
                    <a href="?engagement=high" class="btn btn-outline-warning<?= $engagement=='high' ? ' active' : '' ?>">High Engagement</a>
                </div>
            </div>
            <div class="col-auto ms-auto">
                <select class="form-select" id="sortSelect" style="min-width:180px;">
                    <option value="created_at_DESC"<?= $sort=='created_at'&&$order=='DESC'?' selected':''; ?>>Newest</option>
                    <option value="likes_DESC"<?= $sort=='likes'&&$order=='DESC'?' selected':''; ?>>Most Likes</option>
                    <option value="comments_DESC"<?= $sort=='comments'&&$order=='DESC'?' selected':''; ?>>Most Comments</option>
                    <option value="shares_DESC"<?= $sort=='shares'&&$order=='DESC'?' selected':''; ?>>Most Shares</option>
                </select>
            </div>
        </div>

        <!-- POSTS GRID -->
        <div id="postsGrid" class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (empty($posts)): ?>
                <div class="col">
                    <div class="alert alert-info">No posts found.</div>
                </div>
            <?php else: foreach ($posts as $post): ?>
                <div class="col">
                    <div class="card shadow-sm h-100 border-<?= ($post['status'] ?? '') === 'visible' ? 'success' : 'danger' ?>">
                        <div class="card-body d-flex flex-column">
                            <!-- User Avatar -->
                            <div class="d-flex align-items-center mb-2">
                                <img src="<?= htmlspecialchars($post['user_avatar'] ?? '/assets/img/default-avatar.png') ?>"
                                     alt="Avatar" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                                <span class="fw-semibold"><?= htmlspecialchars($post['full_name'] ?? '–') ?></span>
                            </div>
                            <!-- Post Content (image/video/text) -->
                            <?php if (!empty($post['media_type']) && $post['media_type'] === 'image' && !empty($post['media_url'])): ?>
                                <img src="<?= htmlspecialchars($post['media_url']) ?>" class="img-fluid rounded mb-2" style="aspect-ratio:16/9;object-fit:cover;">
                            <?php elseif (!empty($post['media_type']) && $post['media_type'] === 'video' && !empty($post['media_url'])): ?>
                                <video controls class="w-100 rounded mb-2" style="aspect-ratio:16/9;">
                                    <source src="<?= htmlspecialchars($post['media_url']) ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <div class="mb-2" style="min-height:60px;">
                                    <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'] ?? '', 0, 120, '...'))) ?>
                                    <?php if (mb_strlen($post['content'] ?? '') > 120): ?>
                                        <a href="#" onclick="viewPost(<?= intval($post['id']) ?>);return false;">Read more</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <!-- Engagement Stats -->
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><i class="bi bi-heart-fill text-danger"></i> <?= intval($post['likes'] ?? 0) ?></span>
                                <span><i class="bi bi-chat-left-text-fill text-primary"></i> <?= intval($post['comments'] ?? 0) ?></span>
                                <span><i class="bi bi-share-fill text-success"></i> <?= intval($post['shares'] ?? 0) ?></span>
                            </div>
                            <!-- Time & Visibility -->
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="small"><?= time_elapsed_string($post['created_at']) ?></span>
                                <span class="badge bg-<?= ($post['status'] ?? '') === 'visible' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($post['status'] ?? 'hidden') ?>
                                </span>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div class="card-footer bg-white border-0 d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-secondary" title="Hide/Show" onclick="toggleVisibility(<?= intval($post['id']) ?>, this)">
                                <i class="bi bi-eye<?= ($post['status'] ?? '') === 'visible' ? '' : '-slash' ?>"></i>
                                <span class="ms-1"><?= ($post['status'] ?? '') === 'visible' ? 'Hide' : 'Show' ?></span>
                            </button>
                            <button class="btn btn-sm btn-outline-info" title="Boost" onclick="boostPost(<?= intval($post['id']) ?>)">
                                <i class="bi bi-rocket-takeoff"></i><span class="ms-1">Boost</span>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deletePost(<?= intval($post['id']) ?>, this)">
                                <i class="bi bi-trash"></i><span class="ms-1">Delete</span>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Flag/Report" onclick="flagPost(<?= intval($post['id']) ?>)">
                                <i class="bi bi-flag"></i><span class="ms-1">Flag</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Pagination (optional, if needed) -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item<?= $i == $currentPage ? ' active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(compact('search', 'status', 'type', 'media', 'engagement', 'sort', 'order')) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>

<!-- Post Modal (for "Read more" or View) -->
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
// Sort dropdown
document.getElementById('sortSelect').addEventListener('change', function() {
    const [sort, order] = this.value.split('_');
    const params = new URLSearchParams(window.location.search);
    params.set('sort', sort);
    params.set('order', order);
    window.location.search = params.toString();
});

// Auto-refresh every 60s (AJAX)
function refreshPosts() {
    fetch(window.location.pathname + '?' + window.location.search.substring(1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('postsGrid').innerHTML = doc.getElementById('postsGrid').innerHTML;
        });
}
setInterval(refreshPosts, 60000);

// Action handlers (implement AJAX endpoints as needed)
function handlePostAction(action, postId, options = {}) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('id', postId);

    return fetch('/admin/ajax/post-actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'An unknown error occurred.');
        }
        return data;
    });
}

function toggleVisibility(postId, button) {
    const isHiding = button.textContent.includes('Hide');
    const action = isHiding ? 'hide' : 'show';

    handlePostAction(action, postId)
        .then(data => {
            alert(data.message);
            refreshPosts(); // Refresh the grid to show changes
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function boostPost(postId) {
    if (!confirm('Are you sure you want to boost this post? This might incur costs.')) return;

    handlePostAction('boost', postId)
        .then(data => {
            alert(data.message);
            refreshPosts();
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function deletePost(postId, button) {
    if (!confirm('Are you sure you want to permanently delete this post? This cannot be undone.')) return;

    const card = button.closest('.col');
    handlePostAction('delete', postId)
        .then(data => {
            alert(data.message);
            if (card) {
                card.remove(); // Optimistically remove from UI
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function flagPost(postId) {
    if (!confirm('Are you sure you want to flag this post for review?')) return;

    handlePostAction('flag', postId)
        .then(data => {
            alert(data.message);
            refreshPosts();
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

// View post modal
function viewPost(postId) {
    const modal = new bootstrap.Modal(document.getElementById('postModal'));
    const body = document.getElementById('postModalBody');
    body.innerHTML = '<div class="text-center">Loading...</div>';
    modal.show();
    fetch(`/admin/ajax/get-post-details.php?id=${postId}`)
        .then(response => response.text())
        .then(data => { body.innerHTML = data; })
        .catch(() => { body.innerHTML = '<div class="alert alert-danger">Error loading post details.</div>'; });
}

// Helper: time ago
function time_elapsed_string(datetime) {
    // You may want to implement this in PHP for server-side rendering
    // This is just a placeholder
    return datetime;
}
</script>

<?php include dirname(__DIR__, 2) . '/footer.php'; ?>

<?php
// PHP helper for "X minutes ago"
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks and adjust days
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    $diffs = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    foreach ($string as $k => &$v) {
        if ($diffs[$k]) {
            $v = $diffs[$k] . ' ' . $v . ($diffs[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
