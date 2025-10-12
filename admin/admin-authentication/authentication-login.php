<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Portal</title>
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
                        <div class="card mb-0 shadow-lg border-0">
                            <div class="card-body py-4">
                                <h4 class="text-center mb-2">Admin Portal</h4>
                                <?php if (!empty($_GET['error'])): ?>
                                  <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                  </div>
                                <?php endif; ?>
                                <form method="post" action="../admin-authetication-controller/authentication-login-controller.php" aria-label="Admin sign in form">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email or Username</label>
                                        <input type="text" class="form-control form-control-lg" id="email" name="email" required autofocus>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                    </div>
                                    <div class="mb-4 text-end">
                                        <a href="../admin-authentication/authentication-forgot.php" class="small text-muted">Forgot password?</a>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100 py-3 fs-5 mb-3">Sign In</button>
                                    <div class="text-center">
                                        <a href="../admin-authentication/authentication-signup.php" class="btn btn-outline-primary w-100 py-3 fs-5">
                                            Create Account
                                        </a>
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
