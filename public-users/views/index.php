<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Community Feed';
$displayName = !empty($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Share something';
?>

<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Left sidebar: shortcuts -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Explore</h6>
                    <div class="d-grid gap-2">
                        <a href="./shelters.php" class="btn btn-outline-primary"><i
                                class="ti ti-building-community me-1"></i> Find Shelters</a>
                        <a href="./pets.php" class="btn btn-outline-secondary"><i class="ti ti-search me-1"></i> Browse
                            Pets</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: composer and feed -->
        <div class="col-12 col-lg-6">
            <!-- Composer -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <img src="../../assets/images/profile/user-1.jpg" class="rounded-circle me-3" width="44"
                            height="44" alt="" />
                        <a href="./create_post.php" class="form-control text-start text-muted"
                            style="text-decoration:none;">
                            <i class="ti ti-edit me-2"></i><?php echo htmlspecialchars($displayName); ?>...
                        </a>
                    </div>
                    <div class="d-flex gap-2 mt-3 composer-actions btn-stack-sm">
                        <a href="./create_post.php" class="btn btn-light border"><i
                                class="ti ti-photo me-1 text-success"></i> Photo</a>
                        <a href="./create_post.php" class="btn btn-light border"><i
                                class="ti ti-video me-1 text-danger"></i> Video</a>
                        <a href="./create_post.php" class="btn btn-light border"><i
                                class="ti ti-article me-1 text-primary"></i> Update</a>
                    </div>
                </div>
            </div>

            <!-- Feed items (placeholders) -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img src="../../assets/images/profile/user-2.jpg" class="rounded-circle me-2" width="36"
                            height="36" alt="" />
                        <div>
                            <strong>@PawsRescuePH</strong>
                            <div class="text-muted small">2 hrs • Manila</div>
                        </div>
                    </div>
                    <p class="mb-3">Meet Coco! A gentle 2-year-old looking for a loving home. Vaccinated and
                        house-trained. 🐶💛</p>
                    <img src="../../assets/images/products/GRDog1.jpg" class="img-fluid rounded mb-3" alt="Pet" />
                    <div class="d-flex justify-content-between post-actions-sm mt-2">
                        <div class="action-group d-flex flex-wrap">
                            <a href="./post_view.php" class="btn btn-light border me-1 mb-1"><i
                                    class="ti ti-thumb-up"></i> <span class="d-none d-sm-inline">Like</span></a>
                            <a href="./post_view.php" class="btn btn-light border me-1 mb-1"><i
                                    class="ti ti-message-circle"></i> <span
                                    class="d-none d-sm-inline">Comment</span></a>
                            <a href="./post_view.php" class="btn btn-light border mb-1"><i class="ti ti-share"></i>
                                <span class="d-none d-sm-inline">Share</span></a>
                        </div>
                        <a href="./pets.php" class="btn btn-primary primary-action"><i class="ti ti-heart me-1"></i>
                            <span>Adopt</span></a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img src="../../assets/images/profile/user-3.jpg" class="rounded-circle me-2" width="36"
                            height="36" alt="" />
                        <div>
                            <strong>@CatHaven</strong>
                            <div class="text-muted small">Yesterday • Quezon City</div>
                        </div>
                    </div>
                    <p class="mb-3">Kittens rescued and now ready for pre-adoption screening. Visit our shelter profile
                        for details. 🐱</p>
                    <img src="../../assets/images/products/PCat3.jpg" class="img-fluid rounded mb-3" alt="Kittens" />
                    <div class="d-flex justify-content-between post-actions-sm mt-2">
                        <div class="action-group d-flex flex-wrap">
                            <a href="./post_view.php" class="btn btn-light border me-1 mb-1"><i
                                    class="ti ti-thumb-up"></i> <span class="d-none d-sm-inline">Like</span></a>
                            <a href="./post_view.php" class="btn btn-light border me-1 mb-1"><i
                                    class="ti ti-message-circle"></i> <span
                                    class="d-none d-sm-inline">Comment</span></a>
                            <a href="./post_view.php" class="btn btn-light border mb-1"><i class="ti ti-share"></i>
                                <span class="d-none d-sm-inline">Share</span></a>
                        </div>
                        <a href="./shelters.php" class="btn btn-outline-primary primary-action"><i
                                class="ti ti-building-community me-1"></i> <span>View Shelter</span></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right sidebar: suggestions -->
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Suggested Shelters</h6>
                        <a href="./shelters.php" class="small">See all</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=1">
                            <img src="../../assets/images/profile/user-4.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Paws Rescue PH</span>
                        </a>
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=2">
                            <img src="../../assets/images/profile/user-5.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Cat Haven QC</span>
                        </a>
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=3">
                            <img src="../../assets/images/profile/user-6.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Happy Tails</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Trending</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="./community.php" class="btn btn-sm btn-light border">#adoptDontShop</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#rescue</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#catsofph</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#dogsofph</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>