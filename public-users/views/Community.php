<?php
// Community page styled with system layout
// --- POST/Redirect/GET and session must be handled before any output ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/CommunityController.php';
$controller = new CommunityController();
$error = '';
$success = '';


// Handle delete post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_story']) && isset($_POST['story_index'])) {
	$userId = $_SESSION['user_id'] ?? null;
	$index = (int)$_POST['story_index'];
	$stories = $controller->getStories();
	if (isset($stories[$index]) && $stories[$index]['user_id'] == $userId) {
		array_splice($stories, $index, 1);
		// Save updated stories
		file_put_contents(__DIR__ . '/../storage/community_stories.json', json_encode($stories, JSON_PRETTY_PRINT));
		$_SESSION['community_success'] = 'Your story has been deleted.';
	} else {
		$_SESSION['community_error'] = 'Unable to delete this story.';
	}
	header('Location: ' . $_SERVER['REQUEST_URI']);
	exit;
}

// Handle add post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['story'])) {
	$userId = $_SESSION['user_id'] ?? null;
	$story = trim($_POST['story']);
	if ($userId && $story !== '') {
		if ($controller->addStory($userId, $story)) {
			// Use session to pass success message and redirect (Post/Redirect/Get)
			$_SESSION['community_success'] = 'Your adoption story has been posted!';
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		} else {
			$_SESSION['community_error'] = 'Failed to post your story. Please try again.';
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		}
	} else {
		$_SESSION['community_error'] = 'Please enter your story.';
		header('Location: ' . $_SERVER['REQUEST_URI']);
		exit;
	}
}

// Show and clear flash messages
if (!empty($_SESSION['community_success'])) {
    $success = $_SESSION['community_success'];
    unset($_SESSION['community_success']);
}
if (!empty($_SESSION['community_error'])) {
    $error = $_SESSION['community_error'];
    unset($_SESSION['community_error']);
}

$stories = $controller->getStories();
// --- END: POST/Redirect/GET and session logic ---

// Page title for header
$pageTitle = 'Community';
require_once __DIR__ . '/../include/header.php';
require_once __DIR__ . '/../include/topbar.php';

?>
    <div class="row g-3 py-4">
        <!-- Shortcut Buttons -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>

       <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 mb-3 text-primary text-center"><i class="ti ti-users me-2"></i>Community - Adoption Success Stories</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2"> <?= htmlspecialchars($error) ?> </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success py-2"> <?= htmlspecialchars($success) ?> </div>
                        <?php endif; ?>
                        <form method="post" class="mb-4">
                            <div class="mb-3">
                                <label for="story" class="form-label">Share your successful adoption story:</label>
                                <textarea name="story" id="story" rows="4" class="form-control" required maxlength="1000" placeholder="Write your story here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-send"></i> Post Story</button>
                        </form>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="h5 mb-3 text-secondary text-center"><i class="ti ti-star me-2"></i>Recent Stories</h3>
                        <?php if (empty($stories)): ?>
                            <div class="text-muted text-center">No stories yet. Be the first to share your adoption success!</div>
                        <?php else: ?>
                            <?php 
                            $userId = $_SESSION['user_id'] ?? null;
                            $storiesReversed = array_reverse($stories);
                            $total = count($storiesReversed);
                            foreach ($storiesReversed as $i => $s): 
                                // Calculate the original index in the file for deletion
                                $originalIndex = $total - $i - 1;
                            ?>
                                <div class="border rounded p-3 mb-3 bg-light position-relative">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <strong class="text-primary">User #<?= htmlspecialchars($s['user_id']) ?>:</strong>
                                        <?php if ($userId && $userId == $s['user_id']): ?>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this story?');">
                                                <input type="hidden" name="story_index" value="<?= $originalIndex ?>">
                                                <button type="submit" name="delete_story" class="btn btn-sm btn-outline-danger" title="Delete Story"><i class="ti ti-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-1" style="white-space:pre-line;">
                                        <?= nl2br(htmlspecialchars($s['story'])) ?>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Posted on <?= htmlspecialchars($s['date']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../include/footer.php'; ?>
