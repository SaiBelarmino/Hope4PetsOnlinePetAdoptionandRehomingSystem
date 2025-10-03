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
                <li class="nav-item">
                    <a class="nav-link nav-icon-hover position-relative" href="./messages.php"
                        aria-label="Notifications">
                        <i class="ti ti-bell-ringing fs-5"></i>
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

<style>
.topbar {
    --gap-sm: .5rem;
    --gap: 1rem;
    --gap-lg: 1.25rem;
}

.topbar .center-nav {
    display: flex;
    gap: var(--gap-lg);
}

.topbar .center-nav .nav-link {
    padding: .35rem .25rem;
    line-height: 1;
}

.topbar .right-actions {
    display: flex;
    gap: var(--gap);
}

.topbar .shelter-btn {
    padding: .45rem .9rem;
}

.search-wrapper {
    flex: 0 0 460px;
    max-width: 460px;
    margin-right: var(--gap);
}

@media (max-width:1400px) {
    .search-wrapper {
        flex: 0 0 420px;
        max-width: 420px;
    }
}

@media (max-width:1200px) {
    .search-wrapper {
        flex: 0 0 360px;
        max-width: 360px;
    }
}

@media (max-width:992px) {
    .search-wrapper {
        flex: 1 1 auto;
        max-width: 100%;
        margin: .75rem 0 1rem;
    }
}

.search-box {
    background: #fff;
    border: 1px solid #e4e6eb;
    border-radius: 14px;
    padding: 4px 10px;
    box-shadow: 0 2px 4px rgba(31, 41, 55, .06);
    position: relative;
    transition: box-shadow .2s, border-color .2s;
}

.search-box:focus-within {
    border-color: #6c5ce7;
    box-shadow: 0 4px 10px rgba(108, 92, 231, .18);
}

.search-box .input-group-text {
    color: #6c757d;
}

.search-box input::placeholder {
    color: #9aa0ac;
}

.search-box .btn {
    border-radius: 10px;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e4e6eb;
    border-top: none;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
    padding: 6px 0;
    z-index: 20;
    box-shadow: 0 8px 18px -4px rgba(31, 41, 55, .15);
}

.search-suggestions li a {
    display: block;
    padding: 6px 14px;
    font-size: 14px;
    text-decoration: none;
    color: #34495e;
}

.search-suggestions li a:hover {
    background: #f5f7fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('.search-box input');
    const sug = document.querySelector('.search-suggestions');
    if (!input || !sug) return;
    const mock = ['Dog', 'Cat', 'Shelter Manila', 'Adopt senior dog', 'Lost pet help', 'Vaccination',
        'Events near me'
    ];
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        if (!q) {
            sug.classList.add('d-none');
            return;
        }
        const filtered = mock.filter(i => i.toLowerCase().includes(q)).slice(0, 6);
        if (!filtered.length) {
            sug.classList.add('d-none');
            return;
        }
        sug.innerHTML = filtered.map(v =>
            `<li><a href="./search.php?q=${encodeURIComponent(v)}">${v}</a></li>`).join('');
        sug.classList.remove('d-none');
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-box')) sug.classList.add('d-none');
    });
});
</script>