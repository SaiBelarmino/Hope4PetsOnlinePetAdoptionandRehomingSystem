<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../controllers/PetController.php';

$pageTitle = 'Browse Pets';

$pets = PetController::fetchAvailablePets(36, 0);

?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid">
    <?php
    // show flash via $.notify if any
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $flash = $_SESSION['flash'] ?? null;
    if ($flash) {
        $msg = $flash['message'] ?? '';
        // map bootstrap 'danger' to notify-friendly 'error'
        $type = (($flash['type'] ?? 'success') === 'success') ? 'success' : 'error';
        // use json_encode to safely embed the message and type into JS
        echo '<script>
        document.addEventListener("DOMContentLoaded", function(){ 
            if (typeof $ !== "undefined" && typeof $.notify === "function") {
                $.notify(' . json_encode($msg) . ', ' . json_encode($type) . ');
            } else {
                console.warn("$.notify is not available.");
            }
        });
        </script>';
        unset($_SESSION['flash']);
    }
    ?>
    <div class="row g-3 py-4">
        <!-- Shortcut Buttons -->
        <?php include __DIR__ . '/../include/shortcut-button.php'; ?>

        <div class="col-12 col-lg-6"
            style="max-height:862px; overflow-y:auto; overflow-x:hidden; scrollbar-width:none; -ms-overflow-style:none;">
            <div class="row g-3">
                <?php if (empty($pets)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">No pets available right now.</div>
                    </div>
                </div>
                <?php else: foreach ($pets as $p):
								$photo = $p['photo'] ?? '/storage/uploads/images/default.png';
								$ownerId = (int)($p['owner_id'] ?? 0);
								$ownerName = htmlspecialchars($p['owner_name'] ?? ($p['full_name'] ?? 'Owner'));
								$shelterName = htmlspecialchars($p['shelter_name'] ?? '');
						?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card h-100">
                        <div class="ratio ratio-4x3 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($photo); ?>" class="card-img-top object-fit-cover"
                                alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>">
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><a href="./pet_view.php?id=<?php echo (int)$p['id']; ?>"
                                            class="text-decoration-none"><?php echo htmlspecialchars($p['name'] ?? 'Unnamed'); ?></a>
                                    </h6>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?> ·
                                        <?php echo htmlspecialchars($p['age'] ?? ''); ?></div>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="badge bg-<?php echo (($p['status'] ?? '')==='available')? 'success' : ((($p['status'] ?? '')==='adopted')? 'secondary' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($p['status'] ?? '')); ?></span>
                                </div>
                            </div>

                            <div class="small mb-2">
                                <div><strong>Species:</strong> <?php echo htmlspecialchars($p['species'] ?? 'Other'); ?>
                                </div>
                                <div><strong>Gender:</strong> <?php echo htmlspecialchars($p['gender'] ?? 'Unknown'); ?>
                                </div>
                                <div><strong>Size:</strong> <?php echo htmlspecialchars($p['size'] ?? 'Medium'); ?>
                                </div>
                                <div><strong>Vaccine:</strong>
                                    <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?></div>
                                <div><strong>Health:</strong>
                                    <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?></div>
                                <div><strong>Location:</strong>
                                    <?php echo htmlspecialchars($p['location'] ?? 'Unknown'); ?></div>
                                <?php if (!empty($shelterName)): ?><div><strong>Shelter:</strong>
                                    <?php echo $shelterName; ?></div><?php endif; ?>
                                <div class="mt-1"><small class="text-muted">Owner: <?php echo $ownerName; ?></small>
                                </div>
                            </div>

                            <?php
								$descRaw = $p['description'] ?? 'No description';
								$plain = trim(strip_tags($descRaw));
								$pid = (int)$p['id'];
							?>
                            <p class="small mb-2">
                                <span id="desc-short-<?php echo $pid; ?>" class="one-line-truncate"
                                    title="<?php echo htmlspecialchars($plain); ?>">
                                    <?php echo htmlspecialchars($plain); ?>
                                </span>
                                <span id="desc-full-<?php echo $pid; ?>" class="d-none">
                                    <?php echo nl2br(htmlspecialchars($descRaw)); ?>
                                </span>
                            </p>
                            <a href="#" class="small d-none" id="desc-toggle-<?php echo $pid; ?>"
                                onclick="event.preventDefault(); togglePetDescription(<?php echo $pid; ?>);">
                                See more
                            </a>

                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal"
                                    data-bs-target="#viewModal-<?php echo (int)$p['id']; ?>">View Profile</a>

                                <!-- View Profile Modal -->
                                <div class="modal fade" id="viewModal-<?php echo (int)$p['id']; ?>" tabindex="-1"
                                    aria-labelledby="viewModalLabel-<?php echo (int)$p['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="viewModalLabel-<?php echo (int)$p['id']; ?>">
                                                    <?php echo htmlspecialchars($p['name'] ?? 'Pet Profile'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-12 col-md-5">
                                                        <img src="<?php echo htmlspecialchars($photo); ?>"
                                                            class="img-fluid rounded w-100 object-fit-cover"
                                                            alt="<?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?>">
                                                    </div>
                                                    <div class="col-12 col-md-7">
                                                        <p><strong>Breed:</strong>
                                                            <?php echo htmlspecialchars($p['breed'] ?? 'Unknown'); ?>
                                                        </p>
                                                        <p><strong>Age:</strong>
                                                            <?php echo htmlspecialchars($p['age'] ?? ''); ?></p>
                                                        <p><strong>Species:</strong>
                                                            <?php echo htmlspecialchars($p['species'] ?? 'Other'); ?>
                                                        </p>
                                                        <p><strong>Gender:</strong>
                                                            <?php echo htmlspecialchars($p['gender'] ?? 'Unknown'); ?>
                                                        </p>
                                                        <p><strong>Size:</strong>
                                                            <?php echo htmlspecialchars($p['size'] ?? 'Medium'); ?></p>
                                                        <p><strong>Vaccine:</strong>
                                                            <?php echo htmlspecialchars($p['vaccine_status'] ?? 'N/A'); ?>
                                                        </p>
                                                        <p><strong>Health:</strong>
                                                            <?php echo htmlspecialchars($p['health_status'] ?? 'N/A'); ?>
                                                        </p>
                                                        <p><strong>Owner:</strong> <?php echo $ownerName; ?></p>
                                                        <?php if (!empty($shelterName) || !empty($p['location']) || !empty($p['shelter_address'])): ?>
                                                        <hr>
                                                        <h6>Shelter / Location</h6>
                                                        <?php if (!empty($shelterName)): ?><p><strong>Shelter:</strong>
                                                            <?php echo $shelterName; ?></p><?php endif; ?>
                                                        <?php if (!empty($p['shelter_address'])): ?><p>
                                                            <strong>Address:</strong>
                                                            <?php echo htmlspecialchars($p['shelter_address']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($p['location'])): ?>
                                                        <p><strong>Location:</strong>
                                                            <?php echo htmlspecialchars($p['location']); ?></p>
                                                        <p><a href="https://www.google.com/maps/search/<?php echo urlencode($p['location']); ?>"
                                                                target="_blank" rel="noopener noreferrer"
                                                                class="btn btn-sm btn-outline-secondary">View on Google
                                                                Maps</a></p>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-12">
                                                        <hr>
                                                        <h6>Description</h6>
                                                        <div><?php echo nl2br(htmlspecialchars($descRaw)); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <?php if (($p['status'] ?? '') === 'available'): ?>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#adoptModal-<?php echo (int)$p['id']; ?>"
                                                    data-bs-dismiss="modal">Adopt</button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adopt button replaced by modal trigger -->
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#adoptModal-<?php echo (int)$p['id']; ?>"
                                    <?php echo (($p['status'] ?? '') !== 'available') ? 'disabled' : ''; ?>>Adopt</button>

                                <!-- Adoption Modal -->
                                <div class="modal fade" id="adoptModal-<?php echo (int)$p['id']; ?>" tabindex="-1"
                                    aria-labelledby="adoptModalLabel-<?php echo (int)$p['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="adoptModalLabel-<?php echo (int)$p['id']; ?>">Adoption Request -
                                                    <?php echo htmlspecialchars($p['name'] ?? 'Pet'); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form method="post" action="../controllers/AdoptPetProcessController.php">
                                                <input type="hidden" name="action" value="request">
                                                <input type="hidden" name="pet_id" value="<?php echo (int)$p['id']; ?>">
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="form-label">Your full name</label>
                                                        <input type="text" name="applicant_name" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Phone number</label>
                                                        <input type="text" name="applicant_phone" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Address</label>
                                                        <input type="text" name="applicant_address" class="form-control"
                                                            required
                                                            value="<?php echo htmlspecialchars($_SESSION['user']['address'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Message / Why you want to
                                                            adopt</label>
                                                        <textarea name="applicant_message" class="form-control"
                                                            rows="3"><?php echo htmlspecialchars(''); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-success">Confirm Adoption
                                                        Request</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message button: open chat with owner (ChatMessages.php?user_id=OWNER_ID) -->
                                <a href="./ChatMessages.php?user_id=<?php echo $ownerId; ?>"
                                    class="btn btn-sm btn-outline-secondary"><i class="ti ti-message"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="col-12 col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Filters</h6>
                    <p class="small text-muted">Use search and filters on the left to narrow results.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- include page-specific JS -->
<script src="./assets/js/BrowsePet.js"></script>
<?php include __DIR__ . '/../include/footer.php'; ?>