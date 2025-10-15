<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/MessageController.php';

// Current user
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Recipient
$recipientId = (int)($_GET['recipient_id'] ?? $_GET['user'] ?? $_GET['user_id'] ?? $_GET['u'] ?? $_POST['recipient_id'] ?? 0);

$messages = [];
$conversationWith = '';

// Kukunin ang pangalan ng ka-chat (Recipient)
if ($recipientId > 0) {
    $other = PublicMessagesController::userSummary($recipientId);
    $conversationWith = $other['full_name'] ?? 'User';
}

if ($currentUserId > 0 && $recipientId > 0) {
    $raw = PublicMessagesController::conversation($currentUserId, $recipientId, 1000);
    $lastMessageId = 0; // Initialize last message ID
    if (!empty($raw) && is_array($raw)) {
        foreach ($raw as $r) {
            $senderId = (int)($r['sender_id'] ?? 0);
            $senderSummary = PublicMessagesController::userSummary($senderId);
            $senderName = $senderSummary['full_name'] ?? ($senderId === $currentUserId ? 'You' : 'User');
            $messages[] = [
                'sender_id'   => $senderId,
                'message'     => $r['body'] ?? $r['message'] ?? '',
                'created_at'  => $r['created_at'] ?? '',
                'sender_name' => $senderName,
                'id'          => $r['id'] ?? 0 // Kukunin ang ID para sa lastMessageId
            ];
            // Update last message ID
            if (isset($r['id']) && (int)$r['id'] > $lastMessageId) {
                $lastMessageId = (int)$r['id'];
            }
        }
    }
}
?>

<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<link rel="stylesheet" href="../assets/css/chatmessages.css">
<div class="container-fluid">
    <div class="row g-3 py-3">
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card mb-3">
                <?php if ($recipientId > 0): ?>
                <div class="card-header d-flex align-items-center">
                    <?php 
                        $profilePhoto = $other['profile_photo'] ?? null;
                        if ($profilePhoto) {
                            if (strpos($profilePhoto, 'http') === 0) {
                                $avatar = $profilePhoto;
                            } else {
                                $avatar = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/' . $profilePhoto;
                            }
                        } else {
                            $avatar = '/assets/img/default-avatar.png';
                        }
                    ?>
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture"
                        class="rounded-circle me-1" width="40" height="40">
                    <h5 class="mb-0 ms-2"><?php echo htmlspecialchars($conversationWith); ?></h5>
                </div>
                <?php endif; ?>
                <div class="card-body" id="chat-container"
                    style="max-height: calc(97vh - 220px); overflow-y: auto; background:#f8f9fb; position:relative; z-index:2;">
                    <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $m): ?>
                    <?php $isMine = $m['sender_id'] === $currentUserId; ?>
                    <div class="d-flex mb-3 <?php echo $isMine ? 'justify-content-end' : 'justify-content-start'; ?>">
                        <?php if (!$isMine): ?>
                        <?php
                            // Gamitin ang $other['profile_photo'] na nakuha na sa itaas
                            $recipientAvatar = $avatar;
                        ?>
                        <img src="<?php echo htmlspecialchars($recipientAvatar); ?>" alt="Profile Picture"
                            class="rounded-circle me-2" width="40" height="40">
                        <?php endif; ?>
                        <div class="p-2 rounded"
                            style="max-width:75%; background: <?php echo $isMine ? '#d1e7dd' : '#fff'; ?>;">
                            <div><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center text-muted">No messages yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <form id="chat-form" method="post" action="../controllers/SendMessagesController.php">
                <input type="hidden" name="recipient_id" value="<?php echo (int)$recipientId; ?>">
                <div class="d-flex mb-2">
                    <input type="text" class="form-control me-2" name="message" id="message-input"
                        placeholder="Write a message..." required maxlength="2000" autocomplete="off">
                    <button class="btn btn-primary" type="button" id="send-button" style="width: 120px;">
                        <i class="ti ti-send"></i> Send
                    </button>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <div class="small text-muted">Press Enter to send</div>
                    <div class="small text-muted"><span id="char-count">0</span>/2000</div>
                </div>
            </form>
        </div>

        <div class="col-12 col-lg-3">
            <?php
            $recentConvos = [];
            if ($currentUserId > 0) {
                if (method_exists('PublicMessagesController', 'recentConversations')) {
                    $recentConvos = PublicMessagesController::recentConversations($currentUserId, 20);
                } elseif (method_exists('PublicMessagesController', 'inbox')) {
                    $recentConvos = PublicMessagesController::inbox($currentUserId, 20);
                } elseif (method_exists('PublicMessagesController', 'getRecentContacts')) {
                    $recentConvos = PublicMessagesController::getRecentContacts($currentUserId, 20);
                }
            }
            
            // Check if the current recipient is missing and add them
            if ($recipientId > 0 && !empty($other)) {
                $found = false;
                $recipientData = ['user_id' => $recipientId, 'full_name' => $other['full_name'] ?? 'User', 'profile_photo' => $other['profile_photo'] ?? null, 'last_message' => ''];
                foreach ($recentConvos as $c) {
                    $uid = (int)($c['other_user_id'] ?? $c['user_id'] ?? $c['participant_id'] ?? $c['sender_id'] ?? $c['recipient_id'] ?? $c['id'] ?? 0);
                    if ($uid === $recipientId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                     array_unshift($recentConvos, $recipientData);
                }
            }
            ?>

            <div class="card">
                <div class="card-header">
                    Chat
                    <input type="text" id="chat-search" class="form-control form-control-sm mt-2"
                        placeholder="Search chats...">
                </div>
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($recentConvos) && is_array($recentConvos)): ?>
                    <?php foreach ($recentConvos as $c):
                        $uid = (int)($c['other_user_id'] ?? $c['user_id'] ?? $c['participant_id'] ?? $c['sender_id'] ?? $c['recipient_id'] ?? $c['id'] ?? 0);
                        if ($uid === 0) continue;
                        // Use $c['full_name'] if available, otherwise fetch summary
                        $name = $c['full_name'] ?? (PublicMessagesController::userSummary($uid)['full_name'] ?? 'User');
                        $profilePhoto = $c['profile_photo'] ?? PublicMessagesController::userSummary($uid)['profile_photo'] ?? null;
                        
                        // Set avatar URL
                        if ($profilePhoto) {
                            if (strpos($profilePhoto, 'http') === 0) {
                                $avatar = $profilePhoto;
                            } else {
                                $avatar = '/Hope4PetsOnlinePetAdoptionandRehomingSystem/' . $profilePhoto;
                            }
                        } else {
                            $avatar = '/assets/img/default-avatar.png';
                        }
                        $last = $c['last_message'] ?? $c['body'] ?? $c['message'] ?? '';
                        $time = $c['last_time'] ?? $c['updated_at'] ?? $c['created_at'] ?? '';
                    ?>
                    <a href="ChatMessages.php?recipient_id=<?php echo $uid; ?>"
                        class="list-group-item list-group-item-action d-flex align-items-start <?php echo $uid === $recipientId ? 'active' : ''; ?>">
                        <div class="me-2">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="" class="rounded-circle" width="40"
                                height="40">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-truncate"><?php echo htmlspecialchars($name); ?></h6>
                            <div class="small text-truncate"><?php echo htmlspecialchars($last); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-3 text-muted">No recent conversations.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
window.CURRENT_USER_ID = <?php echo (int)$currentUserId; ?>;
window.RECIPIENT_ID = <?php echo (int)$recipientId; ?>;
window.CURRENT_USER_AVATAR =
    '<?php echo htmlspecialchars($other['profile_photo'] ?? '/assets/img/default-avatar.png'); ?>'; // Assuming current user's avatar is known or can be passed
window.INITIAL_LAST_MESSAGE_ID = <?php echo $lastMessageId ?? 0; ?>;

// --- Dito mo idadagdag ang scroll script ---
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) {
        // I-scroll pababa sa pinakadulo
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
});
// ------------------------------------------
</script>
<script src="./assets/js/messages.js"></script>