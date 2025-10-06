
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Hope4Pets</title>
    <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo-icon.png" />
    <link rel="stylesheet" href="../../assets/css/styles.min.css" />
    <link rel="stylesheet" href="../../assets/css/icons/tabler-icons/tabler-icons.css" />
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="mb-2">
                                     <a href="../../index.php" class="text-decoration-none" aria-label="Back to Home" title="Back to Home">
                                        <i class="ti ti-arrow-left"></i>
                                    </a>
                                </div>
                                <a href="./admin-dashboard.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="">
                                </a>
                                <p class="text-center">Welcome back Admin</p>
                                <?php if (!empty($_GET['error'])): ?>
                                  <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                  </div>
                                <?php endif; ?>
                                <form method="post" action="../api/login.php">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input primary" type="checkbox" value="1" id="remember">
                                            <label class="form-check-label text-dark" for="remember">
                                                Remember this device
                                            </label>
                                        </div>
                                        <span class="text-muted">&nbsp;</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-3">Sign In</button>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <p class="fs-4 mb-0 fw-bold">New to Hope4Pets?</p>
                                        <a class="text-primary fw-bold ms-2" href="./authentication-signup.php">Create an account</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
