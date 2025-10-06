<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
/**
 * View: messages.php (full inbox + conversation)
 * Table: messages
 * Controller sets: $inbox, $conversation, $otherUser, $authUserId, $otherId, $sendError
 *  - $inbox => list of threads with: other_user_id, last_message, last_time, unread_count
 *  - $conversation => messages array
 */
// existing logic below retained
require_once __DIR__ . '/../controllers/messages-controller.php';

$authUserId = 0;
// Robustly extract user id from session (handles both array and flat session structures)
if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] > 0) {
    $authUserId = (int)$_SESSION['user']['id'];
} elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    $authUserId = (int)$_SESSION['user_id'];
} elseif (isset($_SESSION['user']) && is_numeric($_SESSION['user']) && $_SESSION['user'] > 0) {
    // In case $_SESSION['user'] is just the user id (legacy/flat)
    $authUserId = (int)$_SESSION['user'];
}
$otherId = isset($_GET['u']) ? (int)$_GET['u'] : null;
$sendError = '';
$inbox = [];
$conversation = [];
$otherUser = null;
$userNames = [];
$pageTitle = 'Messages';

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');
    
    if ($authUserId > 0 && $recipientId > 0 && $body !== '') {
        $result = PublicMessagesController::send($authUserId, $recipientId, $body);
        if (!$result) {
            $sendError = 'Failed to send message. Please try again.';
        } else {
            // Redirect to avoid form resubmission
            header("Location: ./messages.php?u={$recipientId}");
            exit;
        }
    } else {
        $sendError = 'Invalid message or recipient.';
        $sendError .= ' [authUserId=' . htmlspecialchars($authUserId) . ', recipientId=' . htmlspecialchars($recipientId) . ', body=' . (strlen($body) ? 'set' : 'empty') . ']';
    }
}

// Load inbox for current user and build userNames for all conversation partners
if ($authUserId > 0) {
    $inbox = PublicMessagesController::inbox($authUserId);
    foreach ($inbox as $row) {
        $uid = (int)$row['other_user_id'];
        if (!isset($userNames[$uid])) {
            $summary = PublicMessagesController::userSummary($uid);
            if ($summary && isset($summary['full_name']) && $summary['full_name']) {
                $userNames[$uid] = $summary['full_name'];
            }
        }
    }
}

// Load conversation if otherId is set
if ($otherId && $otherId > 0 && $authUserId > 0) {
    $conversation = PublicMessagesController::conversation($authUserId, $otherId);
    $otherUser = PublicMessagesController::userSummary($otherId);
    // Build a map of user_id => name for all participants in the conversation
    $userIds = [$authUserId, $otherId];
    foreach ($conversation as $msg) {
        if (!in_array($msg['sender_id'], $userIds)) {
            $userIds[] = $msg['sender_id'];
        }
    }
    foreach ($userIds as $uid) {
        $summary = PublicMessagesController::userSummary($uid);
        if ($summary && isset($summary['full_name']) && $summary['full_name']) {
            $userNames[$uid] = $summary['full_name'];
        }
    }
    // Mark messages as read
    PublicMessagesController::markAsRead($authUserId, $otherId);
}
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
            <h3 class="mb-3 d-none d-lg-block">
            <?php
                if ($otherUser && isset($otherUser['full_name']) && $otherUser['full_name']) {
                    echo 'Conversation with ' . htmlspecialchars($otherUser['full_name']);
                } else {
                    echo htmlspecialchars($pageTitle);
                }
            ?>
            </h3>
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
                                                    <strong>
                                                        <?php
                                                        $uid = (int)$row['other_user_id'];
                                                        if (isset($userNames[$uid]) && $userNames[$uid]) {
                                                            echo htmlspecialchars($userNames[$uid]);
                                                        } else {
                                                            echo 'User #' . $uid;
                                                        }
                                                        ?>
                                                    </strong>
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
                                                    <div class="small text-muted"><?php
                                                        if ($isOutgoing) {
                                                            echo 'You';
                                                        } else if (isset($userNames[$msg['sender_id']]) && $userNames[$msg['sender_id']]) {
                                                            echo htmlspecialchars($userNames[$msg['sender_id']]);
                                                        } else {
                                                            echo 'User #' . (int)$msg['sender_id'];
                                                        }
                                                        echo ' • ' . $time;
                                                    ?></div>
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
                                                                        <input type="hidden" name="recipient_id" id="recipient_id" value="<?php echo (int)$otherId; ?>" />
                                                                        <input type="text" name="body" class="form-control" placeholder="Type your message" aria-label="Message" autocomplete="off" <?php echo $otherId? '' : 'disabled'; ?>>
                                                                        <button class="btn btn-success" type="submit" <?php echo $otherId? '' : 'disabled'; ?>><i class="ti ti-send"></i><span class="d-none d-sm-inline ms-1">Send</span></button>
                                                                </form>
                                                                <script>
                                                                // Ensure recipient_id is always set from URL param if missing
                                                                (function() {
                                                                    var url = new URL(window.location.href);
                                                                    var u = url.searchParams.get('u');
                                                                    var rid = document.getElementById('recipient_id');
                                                                    if (u && rid && (!rid.value || rid.value === '0')) {
                                                                        rid.value = u;
                                                                    }
                                                                })();
                                                                </script>
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