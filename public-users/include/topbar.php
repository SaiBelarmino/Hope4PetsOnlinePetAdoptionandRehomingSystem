<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm py-2 py-lg-3">
    <div class="container-fluid">

        <div class="d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center me-3" href="./index.php">
                <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets" height="42" />
            </a>
            
            <form class="d-none d-md-block me-3" role="search" method="get" action="./search.php">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search icon-huge-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search..." aria-label="Search" />
                </div>
            </form>

            <ul class="navbar-nav flex-row align-items-center d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link px-2" href="./index.php" title="Home"><i class="ti ti-home icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./community.php" title="Community"><i class="ti ti-users icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./pets.php" title="Pets"><i class="ti ti-paw icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./shelters.php" title="Shelters"><i class="ti ti-building-community icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./adoptions.php" title="Adoptions"><i class="ti ti-heart-handshake icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./events.php" title="Events"><i class="ti ti-calendar-event icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./favorites.php" title="Favorites"><i class="ti ti-star icon-huge"></i></a></li>
                <li class="nav-item"><a class="nav-link px-2" href="./messages.php" title="Messages"><i class="ti ti-message-circle icon-huge"></i></a></li>
            </ul>
        </div>
        
        <div class="d-flex align-items-center">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#communityNav" aria-controls="communityNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <ul class="navbar-nav flex-row ms-auto align-items-center">
                <li class="nav-item me-2 d-none d-md-block">
                    <?php
                        $hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || (!empty($_SESSION['user']) && !empty($_SESSION['user']['shelter_id']));
                        $shelterCtaLabel = $hasShelter ? 'My Shelter' : 'Be a Shelter';
                        $shelterCtaHref  = $hasShelter ? './my_shelter.php' : './register_shelter.php';
                    ?>
                    <a href="<?php echo htmlspecialchars($shelterCtaHref); ?>" class="btn btn-primary btn-sm"><i class="ti ti-building-community me-1" style="font-size: 1.25rem;"></i><?php echo htmlspecialchars($shelterCtaLabel); ?></a>
                </li>
                
                <li class="nav-item me-2">
                    <a class="nav-link nav-icon-hover position-relative" href="./notifications.php" title="Notifications">
                        <i class="ti ti-bell-ringing icon-huge"></i>
                        <div class="notification bg-danger rounded-circle position-absolute top-0 start-100 translate-middle" style="width: 8px; height: 8px;"></div>
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link p-0" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../../assets/images/profile/user-1.jpg" alt="Profile" width="36" height="36" class="rounded-circle" />
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="./profile.php"><i class="ti ti-user me-1"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="./messages.php"><i class="ti ti-message-circle me-1"></i> Messages</a></li>
                        <li><a class="dropdown-item" href="./settings.php"><i class="ti ti-settings me-1"></i> Settings</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="../api/logout.php"><i class="ti ti-logout me-1"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="collapse navbar-collapse w-100" id="communityNav">
            <form class="w-100 mt-3 d-md-none" role="search" method="get" action="./search.php">
                <div class="input-group input-group-lg mb-2 mb-lg-0">
                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search icon-huge-search"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="Search..." aria-label="Search" />
                </div>
            </form>
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-lg-none">
                <li class="nav-item"><a class="dropdown-item" href="./index.php">Home</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./community.php">Community</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./pets.php">Pets</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./shelters.php">Shelters</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./adoptions.php">Adoptions</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./events.php">Events</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./favorites.php">Favorites</a></li>
                <li class="nav-item"><a class="dropdown-item" href="./messages.php">Messages</a></li>
                <li class="nav-item mt-3 mb-2">
                    <a href="<?php echo htmlspecialchars($shelterCtaHref); ?>" class="btn btn-primary w-100"><?php echo htmlspecialchars($shelterCtaLabel); ?></a>
                </li>
            </ul>
        </div>
    </div>
</nav>