
<header class="app-header border-bottom bg-white sticky-top">
    <nav class="navbar navbar-expand-lg px-0">
        <div class="container-fluid px-0">
            <!-- Brand + search -->
            <a class="navbar-brand d-flex align-items-center" href="./index.php">
                <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets" height="28" class="me-2" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navFeed"
                aria-controls="navFeed" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navFeed">
                <form class="d-none d-md-flex ms-2 flex-grow-1" role="search">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ti ti-search"></i></span>
                        <input class="form-control border-0 bg-light" type="search" placeholder="Search pets, shelters, posts..." aria-label="Search" />
                    </div>
                </form>

                <!-- Center quick nav -->
                <ul class="navbar-nav mx-auto d-none d-lg-flex">
                    <li class="nav-item mx-2"><a class="nav-link" href="./index.php"><i class="ti ti-home fs-5"></i></a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="./pets.php"><i class="ti ti-paw fs-5"></i></a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="./shelters.php"><i class="ti ti-building-community fs-5"></i></a></li>
                    <li class="nav-item mx-2"><a class="nav-link" href="./messages.php"><i class="ti ti-message-circle fs-5"></i></a></li>
                </ul>

                <!-- Right actions -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2 d-none d-md-block">
                        <?php
                            $hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || (!empty($_SESSION['user']) && !empty($_SESSION['user']['shelter_id']));
                            $shelterCtaLabel = $hasShelter ? 'My Shelter Profile' : 'Register as Shelter';
                            $shelterCtaHref  = $hasShelter ? './my_shelter.php' : './register_shelter.php';
                        ?>
                        <a href="<?php echo htmlspecialchars($shelterCtaHref); ?>" class="btn btn-primary">
                            <i class="ti ti-building-community me-1"></i><?php echo htmlspecialchars($shelterCtaLabel); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-icon-hover position-relative" href="./messages.php">
                            <i class="ti ti-bell-ringing fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link nav-icon-hover" href="#" id="dropUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../../assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="dropUser">
                            <div class="message-body">
                                <a href="./profile.php" class="d-flex align-items-center gap-2 dropdown-item">
                                    <i class="ti ti-user fs-6"></i>
                                    <p class="mb-0 fs-3">My Profile</p>
                                </a>
                                <a href="./settings.php" class="d-flex align-items-center gap-2 dropdown-item">
                                    <i class="ti ti-settings fs-6"></i>
                                    <p class="mb-0 fs-3">Settings</p>
                                </a>
                                <a href="../api/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>