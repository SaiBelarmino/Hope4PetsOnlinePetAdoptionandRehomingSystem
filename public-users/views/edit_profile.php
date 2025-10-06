<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: edit_profile.php
 * Table: users
 * Expected Variables:
 *  - $user (same fields as profile)
 *  - $flash optional
 */
$pageTitle = 'Edit Profile';
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
  <div class="card">
    <div class="card-body">
      <form action="../controllers/edit-profile-controller.php" method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Birthday</label>
          <input type="date" name="birthday" class="form-control" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select">
            <?php foreach(['male','female','other','unspecified'] as $g): ?>
              <option value="<?php echo $g; ?>" <?php if(($user['gender']??'')===$g) echo 'selected'; ?>><?php echo ucfirst($g); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Profile Photo</label>
          <input type="file" name="profile_photo" class="form-control" accept="image/*">
        </div>
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_email" id="show_email" <?php if(!empty($user['show_email'])) echo 'checked'; ?>>
            <label class="form-check-label" for="show_email">Show my email on profile</label>
          </div>
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="./profile.php" class="btn btn-light border">Cancel</a>
          <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>
 </div></div>
<?php include __DIR__ . '/../include/footer.php'; ?>
