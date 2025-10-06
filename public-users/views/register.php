<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: register.php
 * Table: users (create)
 */
$pageTitle = 'Register';
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <a href="./authentication-login.php" class="btn btn-outline-secondary btn-sm">Login</a>
  </div>
  <div class="card"><div class="card-body">
    <form action="../authentication-controllers/authentication-signup-controller.php" method="post" class="row g-3">
      <div class="col-12 col-md-6">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" minlength="8" required>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirm" class="form-control" minlength="8" required>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Birthday</label>
        <input type="date" name="birthday" class="form-control">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
          <?php foreach(['male','female','other','unspecified'] as $g): ?><option value="<?php echo $g; ?>"><?php echo ucfirst($g); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control">
      </div>
      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="terms" required>
          <label class="form-check-label" for="terms">I agree to the Terms & Privacy Policy</label>
        </div>
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary"><i class="ti ti-user-plus"></i> Create Account</button>
      </div>
    </form>
  </div></div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
