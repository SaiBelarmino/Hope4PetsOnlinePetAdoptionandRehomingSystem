<?php /* Landing page for Hope4Pets */ ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Hope4Pets — Adopt, Rehome, Support</title>
    <meta name="description"
        content="Hope4Pets helps you adopt and rehome pets with trusted shelters and caring communities." />
    <link rel="shortcut icon" type="image/png" href="assets/images/logos/seodashlogo.png" />
    <!-- Local theme/Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css" />
    <!-- Tabler Icons (local) -->
    <link rel="stylesheet" href="assets/css/icons/tabler-icons/tabler-icons.css" />
    <!-- Landing overrides -->
    <link rel="stylesheet" href="assets/css/landing.css" />
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container py-2">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logos/logo-light.svg" alt="Hope4Pets" height="36" class="me-2" />
                <span class="fw-semibold">Hope4Pets</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#adopt">Adopt</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">How it works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#stories">Stories</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ms-lg-3 mb-2 mb-lg-0"><a class="btn btn-outline-primary w-100"
                            href="public-users/views/authentication-login.php">Log in</a></li>
                    <li class="nav-item ms-lg-2 mb-2 mb-lg-0"><a class="btn btn-primary w-100"
                            href="public-users/views/authentication-signup.php">Sign up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="landing-hero position-relative overflow-hidden d-flex align-items-center"
        style="min-height: 80vh; color: rgba(255,255,255,.85);">
        <video aria-hidden="true" class="position-absolute top-0 start-0 w-100 h-100"
            style="object-fit: cover; z-index: 0;" autoplay loop muted playsinline preload="auto">
            <source src="assets/images/videos/film.mp4" type="video/mp4" />
        </video>
        <div class="position-absolute top-0 start-0 w-100 h-100"
            style="background: linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.45)); z-index: 1;"></div>
        <div class="container position-relative py-5" style="z-index: 2;">
            <div class="row align-items-center py-4 gy-4">
                <div class="col-lg-6">
                    <span class="badge bg-primary-subtle text-primary mb-3">Find your new best friend</span>
                    <h1 class="display-5 fw-bold mb-3 text-warning">Adopt a pet with love and confidence.</h1>
                    <p class="lead text-white-50 mb-4">Browse verified shelters, meet amazing pets, and start a
                        life-changing friendship today.</p>
                    <div class="card shadow-sm border-0 p-2 bg-white text-dark">
                        <form class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="ti ti-paw"></i></span>
                                    <select class="form-select border-0" aria-label="Pet type">
                                        <option selected>Any type</option>
                                        <option>Dogs</option>
                                        <option>Cats</option>
                                        <option>Small Pets</option>
                                        <option>Fish</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0"><i
                                            class="ti ti-map-pin"></i></span>
                                    <input type="text" class="form-control border-0" placeholder="City or ZIP" />
                                </div>
                            </div>
                            <div class="col-12 col-md-3 d-grid">
                                <a href="public-users/views/authentication-login.php" class="btn btn-primary"><i
                                        class="ti ti-search me-1"></i> Search</a>
                            </div>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-4 mt-4 text-white-50 small">
                        <span><i class="ti ti-shield-check text-success me-1"></i>Verified shelters</span>
                        <span><i class="ti ti-heart text-danger me-1"></i>Safe adoptions</span>
                        <span><i class="ti ti-message-circle-2 text-primary me-1"></i>Guided support</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <h3 class="fw-bold mb-0">2k+</h3>
                    <p class="text-muted mb-0">Successful adoptions</p>
                </div>
                <div class="col-6 col-md-3">
                    <h3 class="fw-bold mb-0">150+</h3>
                    <p class="text-muted mb-0">Partner shelters</p>
                </div>
                <div class="col-6 col-md-3">
                    <h3 class="fw-bold mb-0">4.9/5</h3>
                    <p class="text-muted mb-0">Community rating</p>
                </div>
                <div class="col-6 col-md-3">
                    <h3 class="fw-bold mb-0">24/7</h3>
                    <p class="text-muted mb-0">Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section id="adopt" class="py-6">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between mb-4">
                <div>
                    <h2 class="h1 fw-bold mb-1">Popular categories</h2>
                    <p class="text-muted mb-0">Start browsing by the type of pet you love.</p>
                </div>
                <a href="public-users/views/authentication-login.php" class="btn btn-outline-primary">Browse pets</a>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <a class="text-decoration-none" href="public-users/views/authentication-login.php">
                        <div class="card category-card border-0 shadow-sm h-100">
                            <img src="assets/images/products/GRDog1.jpg" class="card-img-top" alt="Dogs" />
                            <div class="card-body">
                                <h5 class="card-title mb-1"><i class="ti ti-bone me-2 text-primary"></i>Dogs</h5>
                                <p class="card-text text-muted">Loyal companions for every home.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a class="text-decoration-none" href="public-users/views/authentication-login.php">
                        <div class="card category-card border-0 shadow-sm h-100">
                            <img src="assets/images/products/PCat3.jpg" class="card-img-top" alt="Cats" />
                            <div class="card-body">
                                <h5 class="card-title mb-1"><i class="ti ti-cat me-2 text-primary"></i>Cats</h5>
                                <p class="card-text text-muted">Graceful, playful, and independent.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a class="text-decoration-none" href="public-users/views/authentication-login.php">
                        <div class="card category-card border-0 shadow-sm h-100">
                            <img src="assets/images/products/rabbit2.jpg" class="card-img-top" alt="Small pets" />
                            <div class="card-body">
                                <h5 class="card-title mb-1"><i class="ti ti-carrot me-2 text-primary"></i>Small Pets
                                </h5>
                                <p class="card-text text-muted">Rabbits, hamsters, and more.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <a class="text-decoration-none" href="public-users/views/authentication-login.php">
                        <div class="card category-card border-0 shadow-sm h-100">
                            <img src="assets/images/products/fish1.jpg" class="card-img-top" alt="Fish" />
                            <div class="card-body">
                                <h5 class="card-title mb-1"><i class="ti ti-fish me-2 text-primary"></i>Fish</h5>
                                <p class="card-text text-muted">Calming aquatic friends.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section id="how" class="py-6 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h1 fw-bold">How it works</h2>
                <p class="text-muted">We make adoption simple and trusted.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-primary-subtle text-primary mb-3"><i class="ti ti-search"></i>
                            </div>
                            <h5 class="fw-semibold">Browse pets</h5>
                            <p class="text-muted mb-0">Explore profiles from verified shelters near you.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-primary-subtle text-primary mb-3"><i
                                    class="ti ti-message-heart"></i></div>
                            <h5 class="fw-semibold">Connect & apply</h5>
                            <p class="text-muted mb-0">Chat with shelters and submit a quick application.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-primary-subtle text-primary mb-3"><i
                                    class="ti ti-home-heart"></i></div>
                            <h5 class="fw-semibold">Welcome home</h5>
                            <p class="text-muted mb-0">Schedule a meet, finalize, and bring them home.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stories -->
    <section id="stories" class="py-6">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h1 fw-bold">Happy stories</h2>
                <p class="text-muted">Real adoptions from our community.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/images/profile/user-1.jpg" class="card-img-top object-fit-cover"
                            style="height: 220px" alt="" />
                        <div class="card-body">
                            <h5 class="card-title">Buddy found a home</h5>
                            <p class="card-text text-muted mb-0">“The process was smooth and the team guided us at every
                                step.”</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/images/profile/user-2.jpg" class="card-img-top object-fit-cover"
                            style="height: 220px" alt="" />
                        <div class="card-body">
                            <h5 class="card-title">Mia the cat</h5>
                            <p class="card-text text-muted mb-0">“We met Mia through Hope4Pets and it was love at first
                                sight.”</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="assets/images/products/PCat1.jpg" class="card-img-top object-fit-cover"
                            style="height: 220px" alt="" />
                        <div class="card-body">
                            <h5 class="card-title">Luna’s big adventure</h5>
                            <p class="card-text text-muted mb-0">“The shelter was kind, transparent, and supportive
                                throughout.”</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA for shelters -->
    <section class="py-6 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-1">Are you a shelter or rescue org?</h2>
                    <p class="mb-0 opacity-85">Reach more adopters, manage applications, and share your pets with our
                        community.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-grid d-lg-block">
                    <a href="organization-shelter/views/authentication-signup.php"
                        class="btn btn-light text-primary fw-semibold">Join as a Shelter</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="py-6 bg-dark text-white-50">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="assets/images/logos/logo-light.svg" alt="Hope4Pets" height="32" class="me-2" />
                        <span class="fw-semibold text-white">Hope4Pets</span>
                    </div>
                    <p class="mb-3">Connecting loving homes with pets in need through trusted shelters.</p>
                    <div class="d-flex gap-3 fs-5">
                        <a class="link-light" href="#" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                        <a class="link-light" href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
                        <a class="link-light" href="#" aria-label="Twitter"><i class="ti ti-brand-x"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white">Explore</h6>
                    <ul class="list-unstyled">
                        <li><a class="link-light" href="#adopt">Adopt</a></li>
                        <li><a class="link-light" href="#how">How it works</a></li>
                        <li><a class="link-light" href="#stories">Stories</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">For shelters</h6>
                    <ul class="list-unstyled">
                        <li><a class="link-light" href="organization-shelter/views/authentication-signup.php">Create
                                account</a></li>
                        <li><a class="link-light" href="organization-shelter/views/authentication-login.php">Shelter
                                login</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white">Get in touch</h6>
                    <ul class="list-unstyled small mb-0">
                        <li><i class="ti ti-mail me-2"></i>hello@hope4pets.local</li>
                        <li><i class="ti ti-map-pin me-2"></i>Anywhere, PH</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4" />
            <div class="d-flex flex-column flex-md-row justify-content-between small">
                <p class="mb-2 mb-md-0">© <?php echo date('Y'); ?> Hope4Pets. All rights reserved.</p>
                <p class="mb-0">Design base by AdminMart / ThemeWagon</p>
            </div>
        </div>
    </footer>

    <!-- Local JS (Bootstrap bundle includes Popper) -->
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Optional: theme scripts if needed on landing -->
    <script src="assets/js/app.min.js"></script>
</body>

</html>