<?php
/**
 * Login Page
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

// Check for session expired message
$errorMessage = '';
if (!empty($_GET['error'])) {
    if ($_GET['error'] === 'session_expired') {
        $errorMessage = 'Your session has expired. Please login again.';
    } else {
        $errorMessage = htmlspecialchars($_GET['error']);
    }
}
?>
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
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-start mb-2">
                                        <a href="../../index.php" class="btn btn-light btn-sm" aria-label="Back to landing">
                                            <i class="ti ti-arrow-left"></i> Back
                                        </a>
                                    </div>
                                    <div class="text-center">
                                        <a href="../../index.php" class="text-nowrap logo-img d-block py-2">
                                            <img src="../../assets/images/logos/HOPE4PETSlogo.png" alt="Hope4Pets Logo" class="img-fluid" style="width: 260px; max-width: 100%; height: auto;">
                                        </a>
                                    </div>
                                </div>
                                <p class="text-center">Welcome back</p>
                                
                                <?php if ($flash): ?>
                                    <div class="alert alert-<?php echo $flash['type']; ?>" role="alert">
                                        <?php echo $flash['message']; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($errorMessage): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $errorMessage; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($_GET['registered'])): ?>
                                    <div class="alert alert-success" role="alert">
                                        Account created successfully. Please log in.
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" action="../authentication-controllers/authentication-login-controller.php">
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
                                    <a href="../../api/google/google_oauth_start.php" class="btn btn-outline-secondary w-100 py-8 fs-4 mb-4">
                                        <span class="me-2">
                                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width:20px;height:20px">
                                        </span>
                                        Continue with Google
                                    </a>
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
