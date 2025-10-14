<?php
// Shortcut buttons include.
// You can set (optionally) from controller:
//   $pendingDocsCount (int)   -> number of pending user or shelter documents
//   $unreadMessagesCount (int)-> unread messages badge
//   $hasShelter (bool)        -> whether to show shelter-specific shortcuts
// These are just hints; code will degrade gracefully if not set.
if (!isset($hasShelter)) { $hasShelter = !empty($_SESSION['shelter_id']); }
$pendingDocsCount = isset($pendingDocsCount)? (int)$pendingDocsCount : 0;
$unreadMessagesCount = isset($unreadMessagesCount)? (int)$unreadMessagesCount : 0;
?>
<div class="card mb-3">
  <div class="card-body">
    <h6 class="text-uppercase text-muted small mb-3"><i class="ti ti-flash me-1"></i> Quick Actions</h6>
    <div class="list-group list-group-flush">
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#createPostModal">
        <span><i class="ti ti-square-plus me-2 text-primary"></i>Create Post</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./pets.php">
        <span><i class="ti ti-paw me-2 text-success"></i>Browse Pets</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./shelters.php">
        <span><i class="ti ti-building-community me-2 text-info"></i>Find Shelters</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./donate.php">
        <span><i class="ti ti-heart-handshake me-2 text-danger"></i>Donate</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./my_adoptions.php">
        <span><i class="ti ti-file-like me-2 text-warning"></i>My Adoptions</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./my_donations.php">
        <span><i class="ti ti-currency-dollar me-2 text-success"></i>My Donations</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./my_posts.php">
        <span><i class="ti ti-news me-2 text-secondary"></i>My Posts</span>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./messages.php">
        <span><i class="ti ti-message-circle me-2 text-primary"></i>Messages</span>
        <?php if($unreadMessagesCount>0): ?><span class="badge bg-danger ms-2"><?php echo $unreadMessagesCount; ?></span><?php endif; ?>
      </a>
      <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./upload_id.php">
        <span><i class="ti ti-id me-2 text-dark"></i>Verification ID</span>
        <?php if($pendingDocsCount>0): ?><span class="badge bg-warning text-dark ms-2"><?php echo $pendingDocsCount; ?></span><?php endif; ?>
      </a>
      <?php if(!$hasShelter): ?>
        <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./ShelterRegistration.php">
          <span><i class="ti ti-building-arch me-2 text-info"></i>Register Shelter</span>
        </a>
      <?php else: ?>
        <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center" href="./ShelterManagement.php">
          <span><i class="ti ti-home-heart me-2 text-info"></i>My Shelter</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
