<nav class="navbar navbar-expand-lg px-0 topbar">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center me-3" href="./index.php">
            <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets" height="32" />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navFeed"
            aria-controls="navFeed" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navFeed">
            <!-- Search -->
            <form class="search-wrapper d-none d-md-flex" role="search">
                <div class="search-box w-100">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent"><i class="ti ti-search"></i></span>
                        <input class="form-control border-0 bg-transparent shadow-none" type="search"
                            placeholder="Search pets, shelters, posts..." aria-label="Search" />
                        <button
                            class="btn btn-sm d-flex align-items-center justify-content-center px-2 bg-transparent border-0 shadow-none search-btn"
                            type="submit" aria-label="Search">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                    <ul class="list-unstyled mb-0 search-suggestions d-none"></ul>
                </div>
            </form>

            <!-- Center nav -->
            <ul class="navbar-nav center-nav d-none d-lg-flex mx-auto">
                <li class="nav-item"><a class="nav-link" href="./index.php" aria-label="Home"><i
                            class="ti ti-home fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./pets.php" aria-label="Pets"><i
                            class="ti ti-paw fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./shelters.php" aria-label="Shelters"><i
                            class="ti ti-building-community fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./messages.php" aria-label="Messages"><i
                            class="ti ti-message-circle fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./adoptions.php" aria-label="Adoptions"><i
                            class="ti ti-heart-handshake fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./favorites.php" aria-label="Favorites"><i
                            class="ti ti-star fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./events.php" aria-label="Events"><i
                            class="ti ti-calendar-event fs-5"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="./community.php" aria-label="Community"><i
                            class="ti ti-users fs-5"></i></a></li>
            </ul>

            <!-- Right -->
            <ul class="navbar-nav right-actions ms-auto align-items-center">
                <li class="nav-item d-none d-md-block">
                    <?php
                        $hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || (!empty($_SESSION['user']) && !empty($_SESSION['user']['shelter_id']));
                        $shelterCtaLabel = $hasShelter ? 'My Shelter Profile' : 'Register as Shelter';
                        $shelterCtaHref  = $hasShelter ? './my_shelter.php' : './register_shelter.php';
                    ?>
                    <a href="<?php echo htmlspecialchars($shelterCtaHref); ?>"
                        class="btn btn-primary btn-sm shelter-btn">
                        <i class="ti ti-building-community me-1"></i><?php echo htmlspecialchars($shelterCtaLabel); ?>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon-hover p-0" href="#" id="dropUser" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <img src="../../assets/images/profile/user-1.jpg" alt="" width="36" height="36"
                            class="rounded-circle" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="dropUser">
                        <div class="message-body">
                            <a href="./profile.php" class="d-flex align-items-center gap-2 dropdown-item">
                                <i class="ti ti-user fs-6"></i><span>My Profile</span>
                            </a>
                            <a href="./settings.php" class="d-flex align-items-center gap-2 dropdown-item">
                                <i class="ti ti-settings fs-6"></i><span>Settings</span>
                            </a>
                            <a href="../api/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
