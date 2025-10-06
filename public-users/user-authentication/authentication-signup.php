<?php
/**
 * Registration Page
 * Public page - no authentication required
 */
require_once __DIR__ . '/../../config/SessionManager.php';

// If already logged in, redirect to dashboard
SessionManager::init();
if (SessionManager::isLoggedIn()) {
    header('Location: ../views/index.php');
    exit;
}

// Get flash messages
$flash = SessionManager::getFlash();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Hope4Pets</title>
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
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-start mb-2">
                                        <a href="../../index.php" class="btn btn-light btn-sm p-2" aria-label="Back to landing" title="Back">
                                            <i class="ti ti-arrow-left"></i>
                                        </a>
                                    </div>
                                    <div class="text-center">
                                        <a href="../../index.php" class="text-nowrap logo-img d-block py-2">
                                            <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets Logo" class="img-fluid" style="width: 260px; max-width: 100%; height: auto;">
                                        </a>
                                    </div>
                                </div>
                                <p class="text-center">Create your account</p>
                                
                                <?php if ($flash): ?>
                                    <div class="alert alert-<?php echo $flash['type']; ?>" role="alert">
                                        <?php echo $flash['message']; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($_GET['error'])): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo htmlspecialchars($_GET['error']); ?>
                                    </div>
                                <?php elseif (!empty($_GET['success'])): ?>
                                    <div class="alert alert-success" role="alert">
                                        Account created successfully. You can now <a href="./authentication-login.php" class="alert-link">sign in</a>.
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" action="../authentication-controllers/authentication-signup-controller.php">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-3">Sign Up</button>
                                    <a href="../../api/google/google_oauth_start.php" class="btn btn-outline-secondary w-100 py-8 fs-4 mb-4">
                                        <span class="me-2"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="" style="width:20px;height:20px"></span>
                                        Sign up with Google
                                    </a>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <p class="fs-4 mb-0 fw-bold">Already have an Account?</p>
                                        <a class="text-primary fw-bold ms-2" href="./authentication-login.php">Sign In</a>
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
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>
