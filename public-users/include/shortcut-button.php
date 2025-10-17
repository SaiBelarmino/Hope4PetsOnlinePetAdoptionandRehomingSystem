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
<div class="col-12 col-lg-4 shortcut-list">
    <div class="card mb-3 bg-transparent shadow-none">
        <div class="card-body py-2" style="font-size:0.95rem;">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./MyProfile.php">
                    <span><i class="ti ti-user me-2 text-secondary" style="font-size:1.15rem;"></i>My Profile</span>
                </a>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./BrowsePet.php">
                    <span><i class="ti ti-paw me-2 text-success" style="font-size:1.15rem;"></i>Browse Pets</span>
                </a>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./shelters.php">
                    <span><i class="ti ti-building-community me-2 text-info" style="font-size:1.15rem;"></i>Find
                        Shelters</span>
                </a>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./AdoptionManagement.php">
                    <span><i class="ti ti-file-like me-2 text-warning" style="font-size:1.15rem;"></i>My
                        Adoptions</span>
                </a>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./my_donations.php">
                    <span><i class="ti ti-currency-dollar me-2 text-success" style="font-size:1.15rem;"></i>My
                        Donations</span>
                </a>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./my_posts.php">
                    <span><i class="ti ti-news me-2 text-secondary" style="font-size:1.15rem;"></i>My Posts</span>
                </a>
                <?php if(!$hasShelter): ?>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./ShelterRegistration.php">
                    <span><i class="ti ti-building-arch me-2 text-info" style="font-size:1.15rem;"></i>Register Shelter</span>
                </a>
                <?php else: ?>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center border-0"
                    href="./ShelterManagement.php">
                    <span><i class="ti ti-home-heart me-2 text-info" style="font-size:1.15rem;"></i>My Shelter</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>