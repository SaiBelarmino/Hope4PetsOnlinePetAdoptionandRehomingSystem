<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: settings.php
 * Table: users (preferences fields could be extended)
 * Expected Variables:
 *  - $user
 *  - $flash optional
 */
$pageTitle = 'Settings';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper"><div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    <a href="./profile.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> Back</a>
  </div>
  <?php if(!empty($flash['message'])): ?><div class="alert alert-<?php echo htmlspecialchars($flash['type']??'info'); ?>"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Account Security</h6></div>
        <div class="card-body">
          <form action="../controllers/settings-controller.php" method="post" class="mb-3">
            <input type="hidden" name="action" value="change_password">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <button class="btn btn-primary"><i class="ti ti-lock"></i> Update Password</button>
          </form>
          <hr>
          <form action="../controllers/settings-controller.php" method="post">
            <input type="hidden" name="action" value="toggle_2fa">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="twofa" name="enable_2fa" <?php if(!empty($user['twofa_enabled'])) echo 'checked'; ?>>
              <label for="twofa" class="form-check-label">Enable Two-Factor Authentication (placeholder)</label>
            </div>
            <button class="btn btn-outline-primary btn-sm">Save Security Settings</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white border-0 pb-0"><h6 class="mb-0">Preferences</h6></div>
        <div class="card-body">
          <form action="../controllers/settings-controller.php" method="post" class="mb-3">
            <input type="hidden" name="action" value="update_preferences">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" <?php if(!empty($user['email_notifications'])) echo 'checked'; ?>>
              <label for="email_notifications" class="form-check-label">Email Notifications</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="sms_notifications" id="sms_notifications" <?php if(!empty($user['sms_notifications'])) echo 'checked'; ?>>
              <label for="sms_notifications" class="form-check-label">SMS Notifications</label>
            </div>
            <div class="mb-3">
              <label class="form-label">Timezone</label>
              <select name="timezone" class="form-select">
                <option value="Asia/Manila">Asia/Manila (PH)</option>
              </select>
            </div>
            <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Save Preferences</button>
          </form>
          <hr>
          <form action="../controllers/settings-controller.php" method="post" onsubmit="return confirm('Deactivate your account?');">
            <input type="hidden" name="action" value="deactivate_account">
            <button class="btn btn-outline-danger btn-sm"><i class="ti ti-user-off"></i> Deactivate Account</button>
          </form>
        </div>
      </div>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
