<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Messages';
$displayName = !empty($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Type a message';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid py-3">
    <div class="row g-3">
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

        <!-- Center: messages interface -->
        <div class="col-12 col-lg-6">
            <h3 class="mb-3 d-none d-lg-block"><?php echo htmlspecialchars($pageTitle); ?></h3>
            <div class="card mb-3" style="height:calc(100vh - 180px);">
                <div class="card-body p-0 d-flex flex-column h-100">
                    <div class="row g-0 flex-grow-1 align-items-stretch overflow-hidden">
                        <!-- Conversations list -->
                        <div class="col-12 col-md-5 border-end d-flex flex-column h-100">
                            <div class="p-3 border-bottom fw-semibold small text-uppercase text-muted">Conversations
                            </div>
                            <ul class="list-unstyled mb-0 friend-list overflow-auto flex-grow-1">
                                <!-- Existing static members retained -->
                                <li class="active">
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-1.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Hope Animal Shelter</strong>
                                                <small class="text-muted">2d</small>
                                            </div>
                                            <div class="small text-muted">Thanks — we received your adoption inquiry
                                                about Luna.</div>
                                        </div>
                                        <span class="badge bg-danger ms-2 align-self-start">2</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-2.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Maria Santos</strong>
                                                <small class="text-muted">5h</small>
                                            </div>
                                            <div class="small text-muted">I'm available to visit the shelter this
                                                Saturday.</div>
                                        </div>
                                        <i class="fa fa-check text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-3.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Volunteer Coordinator</strong>
                                                <small class="text-muted">1d</small>
                                            </div>
                                            <div class="small text-muted">Can you help with weekend transport for
                                                rescued dogs?</div>
                                        </div>
                                        <i class="fa fa-reply text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-1.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Admin Team</strong>
                                                <small class="text-muted">3d</small>
                                            </div>
                                            <div class="small text-muted">Your ID verification was approved.</div>
                                        </div>
                                        <i class="fa fa-check text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-2.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Green Paws Rescue</strong>
                                                <small class="text-muted">4d</small>
                                            </div>
                                            <div class="small text-muted">Do you have photos of the dog available for
                                                fostering?</div>
                                        </div>
                                        <i class="fa fa-reply text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-3.jpg" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Foster Volunteer</strong>
                                                <small class="text-muted">6d</small>
                                            </div>
                                            <div class="small text-muted">I can foster for two weeks starting next
                                                Monday.</div>
                                        </div>
                                        <i class="fa fa-reply text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                        <img src="../../assets/images/profile/user-placeholder.png" alt=""
                                            class="rounded-circle me-2" width="44" height="44">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>Dr. Reyes</strong>
                                                <small class="text-muted">1w</small>
                                            </div>
                                            <div class="small text-muted">Vaccinations for adopted pets are scheduled
                                                next week.</div>
                                        </div>
                                        <i class="fa fa-reply text-muted ms-2 mt-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Active chat -->
                        <div class="col-12 col-md-7 d-flex flex-column h-100">
                            <div class="flex-grow-1 p-3 overflow-auto">
                                <ul class="list-unstyled chat mb-0">
                                    <li class="mb-3 d-flex">
                                        <img src="https://bootdey.com/img/Content/user_3.jpg"
                                            class="rounded-circle me-2" width="40" height="40" alt="User">
                                        <div>
                                            <div class="small text-muted">John Doe • 12 mins ago</div>
                                            <div class="p-2 bg-light rounded">Lorem ipsum dolor sit amet, consectetur
                                                adipiscing elit.</div>
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex flex-row-reverse text-end">
                                        <img src="https://bootdey.com/img/Content/user_1.jpg"
                                            class="rounded-circle ms-2" width="40" height="40" alt="User">
                                        <div>
                                            <div class="small text-muted">You • 13 mins ago</div>
                                            <div class="p-2 bg-primary text-white rounded">Lorem ipsum dolor sit amet,
                                                consectetur adipiscing elit. Curabitur bibendum ornare dolor.</div>
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex">
                                        <img src="https://bootdey.com/img/Content/user_3.jpg"
                                            class="rounded-circle me-2" width="40" height="40" alt="User">
                                        <div>
                                            <div class="small text-muted">John Doe • 10 mins ago</div>
                                            <div class="p-2 bg-light rounded">Lorem ipsum dolor sit amet, consectetur
                                                adipiscing elit.</div>
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex flex-row-reverse text-end">
                                        <img src="https://bootdey.com/img/Content/user_1.jpg"
                                            class="rounded-circle ms-2" width="40" height="40" alt="User">
                                        <div>
                                            <div class="small text-muted">You • 9 mins ago</div>
                                            <div class="p-2 bg-primary text-white rounded">Lorem ipsum dolor sit amet,
                                                consectetur adipiscing elit.</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="border-top p-2">
                                <form class="d-flex gap-2">
                                    <input type="text" class="form-control" placeholder="Type your message here"
                                        aria-label="Message">
                                    <button class="btn btn-success" type="button"><i class="ti ti-send"></i><span
                                            class="d-none d-sm-inline ms-1">Send</span></button>
                                </form>
                            </div>
                        </div>
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