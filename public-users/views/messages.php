<?php
// Simple messaging UI wired to PublicMessagesController WITHOUT using PHP sessions.
// TEMP auth: pass ?uid=<yourUserId> in the query string. DO NOT use this in production.
require_once __DIR__ . '/../controllers/messages-controller.php';

$pageTitle = 'Messages';
$authUserId = isset($_GET['uid']) ? (int)$_GET['uid'] : 0; // temporary user identification
$otherId = isset($_GET['u']) ? (int)$_GET['u'] : 0;       // conversation partner

// If no uid supplied, we'll keep UI disabled.
$authReady = $authUserId > 0;

// Handle send POST
$sendError = '';
if ($authReady && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='send') {
    $recipient = (int)($_POST['recipient_id'] ?? 0); // trust only from form for temp
    $body = trim($_POST['body'] ?? '');
    if ($recipient <=0 || $body==='') {
        $sendError = 'Message cannot be empty.';
    } else {
        $sent = PublicMessagesController::send($authUserId, $recipient, $body);
        if ($sent) {
            header('Location: ./messages.php?uid=' . $authUserId . '&u=' . $recipient);
            exit;
        } else {
            $sendError = 'Failed to send.';
        }
    }
}

// Load inbox & conversation only if temp auth provided
$inbox = $authReady ? PublicMessagesController::inbox($authUserId, 50) : [];
$conversation = ($authReady && $otherId) ? PublicMessagesController::conversation($authUserId, $otherId, 300) : [];
if ($authReady && $otherId) { PublicMessagesController::markAsRead($authUserId, $otherId); }
$otherUser = ($authReady && $otherId) ? PublicMessagesController::userSummary($otherId) : null;
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- Left sidebar: shortcuts -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Explore</h6>
                    <div class="d-grid gap-2">
                        <a href="./shelters.php" class="btn btn-outline-primary"><i
                                class="ti ti-building-community me-1"></i> Find Shelters</a>
                        <a href="./pets.php" class="btn btn-outline-secondary"><i class="ti ti-search me-1"></i> Browse
                            Pets</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: messages interface -->
        <div class="col-12 col-lg-6">
            <h3 class="mb-3 d-none d-lg-block"><?php echo htmlspecialchars($pageTitle); ?></h3>
            <div class="card mb-3" style="height:calc(100vh - 180px);">
                <div class="card-body p-0 d-flex flex-column h-100">
                    <div class="row g-0 flex-grow-1 align-items-stretch overflow-hidden">
                        <!-- Conversations list -->
                        <div class="col-12 col-md-5 border-end d-flex flex-column h-100">
                            <div class="p-3 border-bottom fw-semibold small text-uppercase text-muted">Conversations
                            </div>
                            <ul class="list-unstyled mb-0 friend-list overflow-auto flex-grow-1">
                                <?php if (empty($inbox)): ?>
                                    <li class="px-3 py-2 text-muted small">No conversations yet.</li>
                                <?php else: foreach ($inbox as $row): 
                                    $active = ($otherId === (int)$row['other_user_id']);
                                    $preview = htmlspecialchars(mb_strimwidth($row['last_message'] ?? '', 0, 60, '…'));
                                    $unread = (int)$row['unread_count'];
                                    $time = htmlspecialchars(date('M d H:i', strtotime($row['last_time'])));
                                ?>
                                    <li class="<?php echo $active ? 'active' : ''; ?>">
                                        <a href="./messages.php?u=<?php echo (int)$row['other_user_id']; ?>" class="d-flex align-items-start px-3 py-2 text-decoration-none">
                                            <img src="../../assets/images/profile/user-placeholder.png" alt="" class="rounded-circle me-2" width="44" height="44">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong>User #<?php echo (int)$row['other_user_id']; ?></strong>
                                                    <small class="text-muted"><?php echo $time; ?></small>
                                                </div>
                                                <div class="small text-muted"><?php echo $preview ?: 'No text'; ?></div>
                                            </div>
                                            <?php if ($unread>0): ?><span class="badge bg-danger ms-2 align-self-start"><?php echo $unread; ?></span><?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; endif; ?>
                            </ul>
                        </div>
                        <!-- Active chat -->
                        <div class="col-12 col-md-7 d-flex flex-column h-100">
                            <div class="flex-grow-1 p-3 overflow-auto">
                                <?php if (!$otherId): ?>
                                    <div class="text-muted small">Select a conversation or start a new one below.</div>
                                <?php else: ?>
                                    <ul class="list-unstyled chat mb-0">
                                        <?php if (empty($conversation)): ?>
                                            <li class="text-muted small">No messages yet. Say hello!</li>
                                        <?php else: foreach ($conversation as $msg):
                                            $isOutgoing = (int)$msg['is_outgoing'] === 1;
                                            $time = htmlspecialchars(date('M d H:i', strtotime($msg['created_at'])));
                                            $bodyHtml = nl2br(htmlspecialchars($msg['body']));
                                        ?>
                                            <li class="mb-3 d-flex <?php echo $isOutgoing ? 'flex-row-reverse text-end' : ''; ?>">
                                                <img src="https://bootdey.com/img/Content/user_<?php echo $isOutgoing ? '1' : '3'; ?>.jpg" class="rounded-circle <?php echo $isOutgoing ? 'ms-2' : 'me-2'; ?>" width="40" height="40" alt="User">
                                                <div>
                                                    <div class="small text-muted"><?php echo $isOutgoing ? 'You' : 'User #' . (int)$msg['sender_id']; ?> • <?php echo $time; ?></div>
                                                    <div class="p-2 rounded <?php echo $isOutgoing ? 'bg-primary text-white' : 'bg-light'; ?>"><?php echo $bodyHtml; ?></div>
                                                </div>
                                            </li>
                                        <?php endforeach; endif; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div class="border-top p-2">
                                <form class="d-flex gap-2" method="post" action="./messages.php?u=<?php echo (int)$otherId; ?>">
                                    <input type="hidden" name="action" value="send" />
                                    <input type="hidden" name="recipient_id" value="<?php echo (int)$otherId; ?>" />
                                    <input type="text" name="body" class="form-control" placeholder="Type your message" aria-label="Message" autocomplete="off" <?php echo $otherId? '' : 'disabled'; ?>>
                                    <button class="btn btn-success" type="submit" <?php echo $otherId? '' : 'disabled'; ?>><i class="ti ti-send"></i><span class="d-none d-sm-inline ms-1">Send</span></button>
                                </form>
                                <?php if ($sendError): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($sendError); ?></div><?php endif; ?>
                                <?php if (!$otherId): ?>
                                    <div class="small text-muted mt-1">Choose a user from the left to start chatting.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right sidebar: suggestions -->
        <div class="col-12 col-lg-3">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Suggested Shelters</h6>
                        <a href="./shelters.php" class="small">See all</a>
                    </div>
                    <div class="list-group list-group-flush">
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=1">
                            <img src="../../assets/images/profile/user-4.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Paws Rescue PH</span>
                        </a>
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=2">
                            <img src="../../assets/images/profile/user-5.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Cat Haven QC</span>
                        </a>
                        <a class="list-group-item px-0 d-flex align-items-center" href="./shelter_view.php?id=3">
                            <img src="../../assets/images/profile/user-6.jpg" class="rounded-circle me-2" width="28"
                                height="28" alt="" />
                            <span>Happy Tails</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Trending</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="./community.php" class="btn btn-sm btn-light border">#adoptDontShop</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#rescue</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#catsofph</a>
                        <a href="./community.php" class="btn btn-sm btn-light border">#dogsofph</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>