<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: forgot_password.php
 * Table: users (password reset flow)
 */
$pageTitle = 'Forgot Password';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <a href="./authentication-login.php" class="btn btn-outline-secondary btn-sm">Login</a>
  </div>
  <div class="card"><div class="card-body">
    <form action="../controllers/forgot-password-controller.php" method="post" class="row g-3">
      <div class="col-12">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Send Reset Link</button>
      </div>
    </form>
    <hr>
    <p class="small text-muted mb-0">Enter your registered email to receive a password reset link. Check spam folder if not received within a few minutes.</p>
  </div></div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
