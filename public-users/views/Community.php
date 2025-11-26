<?php
$pageTitle = 'Success Stories';
include __DIR__ . '/../include/header.php';
include __DIR__ . '/../include/topbar.php';

require_once __DIR__ . '/../controllers/CommunityController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

$session = new SessionManager();
$userId = $session->get('user_id');
$stories = CommunityController::getStories();
?>
<link rel="stylesheet" href="assets/css/community.css" />

<!-- HERO BANNER -->
<div class="hero-banner" style="
    background: url('assets/img/collage-pets-adopters.jpg') center center/cover no-repeat;
    min-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #fff;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    margin-bottom: 2rem;
">
    <h1 class="display-4 fw-bold mb-3 text-center">Success Stories: From Rescue to Forever Homes</h1>
    <a href="#share-story" class="btn btn-lg btn-primary shadow">Share Your Story</a>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <!-- POSTING BOX -->
            <div id="share-story" class="card shadow-sm mb-4">
                <div class="card-body">
                    <?php if ($userId): ?>
                    <form method="POST" action="../controllers/PostStoryController.php">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <h6 class="mb-3 fw-bold">
                            ✨ Share Your Success Story  
                            <div class="text-muted small">Testimonials from adopters & rescued animals</div>
                        </h6>
                        <input type="text" class="form-control mb-2" name="title" maxlength="100" placeholder="Story Title (e.g., 'From Stray to Family Member')" required>
                        <select name="category" class="form-select mb-2" required>
                            <option value="">Select Category</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Other">Other Animal</option>
                        </select>
                        <select name="theme" class="form-select mb-2" required>
                            <option value="">Select Theme</option>
                            <option value="Health Recovery">Health Recovery</option>
                            <option value="Family Bonding">Family Bonding</option>
                            <option value="Senior Pets">Senior Pets</option>
                        </select>
                        <textarea class="form-control mb-3" name="content" rows="4"
                            placeholder="Write your story here..." required></textarea>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-pencil"></i> Post Story
                        </button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Please log in to share your story.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- NO STORIES -->
            <?php if (empty($stories)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-notebook text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No stories yet. Be the first to share your experience!</p>
                </div>
            <?php else: ?>

            <!-- STORY FEED -->
            <?php foreach ($stories as $story): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <!-- User Name / Shelter Name -->
                    <div class="mb-1 text-primary fw-semibold small">
                        <?php echo htmlspecialchars($story['full_name']); ?>
                    </div>
                    <!-- Story Title -->
                    <h5 class="fw-bold mb-2">
                        <?php echo !empty($story['title']) ? htmlspecialchars($story['title']) : 'Success Story'; ?>
                    </h5>
                    <!-- Testimonial Text (excerpt, expandable) -->
                    <?php
                        $excerpt = mb_substr($story['content'], 0, 180);
                        $isLong = mb_strlen($story['content']) > 180;
                    ?>
                    <p class="fs-6 lh-base mb-2">
                        <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                        <?php if ($isLong): ?>
                            <span id="more-<?php echo $story['id']; ?>" class="collapse">
                                <?php echo nl2br(htmlspecialchars(mb_substr($story['content'], 180))); ?>
                            </span>
                            <a href="#!" class="text-primary" data-bs-toggle="collapse" data-bs-target="#more-<?php echo $story['id']; ?>">Read more</a>
                        <?php endif; ?>
                    </p>
                    <!-- Date Posted -->
                    <small class="text-muted">
                        <i class="ti ti-calendar"></i>
                        <?php echo date('M d, Y', strtotime($story['created_at'])); ?>
                    </small>
                    <!-- Buttons -->
                    <div class="mt-3 d-flex gap-3">
                        <button class="btn btn-sm btn-outline-primary">
                            👍 Like (<?php echo $story['reaction_count']; ?>)
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                            data-bs-target="#comments-<?php echo $story['id']; ?>">
                            💬 Comment (<?php echo $story['comment_count']; ?>)
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="navigator.clipboard.writeText(window.location.href)">
                            🔗 Share
                        </button>
                    </div>
                    <!-- Comments Section -->
                    <div class="collapse mt-3" id="comments-<?php echo $story['id']; ?>">
                        <?php foreach ($story['comments'] as $comment): ?>
                            <div class="mt-2 p-2 rounded bg-light">
                                <strong><?php echo htmlspecialchars($comment['user_name']); ?>:</strong>
                                <?php echo htmlspecialchars($comment['content']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../include/footer.php'; ?>
