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

if ($recipientId > 0) {
    $other = PublicMessagesController::userSummary($recipientId);
    $conversationWith = $other['full_name'] ?? 'User';
}

if ($currentUserId > 0 && $recipientId > 0) {
    $raw = PublicMessagesController::conversation($currentUserId, $recipientId, 1000);
    if (!empty($raw) && is_array($raw)) {
        foreach ($raw as $r) {
            $senderId = (int)($r['sender_id'] ?? 0);
            $senderSummary = PublicMessagesController::userSummary($senderId);
            $senderName = $senderSummary['full_name'] ?? ($senderId === $currentUserId ? 'You' : 'User');
            $messages[] = [
                'sender_id'   => $senderId,
                'message'     => $r['body'] ?? $r['message'] ?? '',
                'created_at'  => $r['created_at'] ?? '',
                'sender_name' => $senderName
            ];
        }
    }
}
?>

<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>

<div class="container-fluid">
    <div class="row g-3 py-3">
        <!-- Sidebar -->
        <div class="col-12 col-lg-3">
            <?php include __DIR__ . '/../include/shortcut-button.php'; ?>
        </div>

        <!-- Main Chat -->
        <div class="col-12 col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Chat with <?php echo htmlspecialchars($conversationWith); ?></h3>
                <a href="./pet_view.php?id=<?php echo (int)($pet['id'] ?? 0); ?>" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back
                </a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div id="chat-container" class="border rounded p-3 mb-3" 
                         style="height:400px; overflow:auto; background:#f8f9fb;">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $m): ?>
                                <?php $isMine = $m['sender_id'] === $currentUserId; ?>
                                <div class="d-flex mb-3 <?php echo $isMine ? 'justify-content-end' : 'justify-content-start'; ?>">
                                    <div class="p-2 rounded" 
                                         style="max-width:75%; background: <?php echo $isMine ? '#d1e7dd' : '#fff'; ?>;">
                                        <div class="small text-muted mb-1">
                                            <?php echo htmlspecialchars($m['sender_name']); ?>
                                        </div>
                                        <div><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                                        <div class="small text-muted mt-1 text-end">
                                            <?php echo htmlspecialchars(date('M d, Y H:i', strtotime($m['created_at']))); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">No messages yet.</div>
                        <?php endif; ?>
                    </div>

                    <form id="chat-form" method="post" action="../controllers/SendMessagesController.php">
                        <input type="hidden" name="recipient_id" value="<?php echo (int)$recipientId; ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="message" id="message-input" 
                                   placeholder="Write a message..." required maxlength="2000" autocomplete="off">
                            <button class="btn btn-primary" type="button" id="send-button">
                                <i class="ti ti-send"></i> Send
                            </button>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <div class="small text-muted">Press Enter to send</div>
                            <div class="small text-muted"><span id="char-count">0</span>/2000</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-12 col-lg-3">
            <?php
            // Load recent conversations for the right sidebar (with graceful fallbacks).
            $recentConvos = [];
            if ($currentUserId > 0) {
                if (method_exists('PublicMessagesController', 'recentConversations')) {
                    $recentConvos = PublicMessagesController::recentConversations($currentUserId, 20);
                } elseif (method_exists('PublicMessagesController', 'conversations')) {
                    $recentConvos = PublicMessagesController::conversations($currentUserId, 20);
                } elseif (method_exists('PublicMessagesController', 'inbox')) {
                    $recentConvos = PublicMessagesController::inbox($currentUserId, 20);
                }
            }
            ?>

            <div class="card">
                <div class="card-header">
                    Recent Conversations
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!empty($recentConvos) && is_array($recentConvos)): ?>
                        <?php foreach ($recentConvos as $c):
                            $uid = (int)($c['user_id'] ?? $c['participant_id'] ?? $c['sender_id'] ?? $c['recipient_id'] ?? $c['id'] ?? 0);
                            if ($uid === 0) continue;
                            $summary = PublicMessagesController::userSummary($uid);
                            $name = $summary['full_name'] ?? 'User';
                            $last = $c['last_message'] ?? $c['body'] ?? $c['message'] ?? '';
                            $time = $c['updated_at'] ?? $c['created_at'] ?? '';
                        ?>
                        <a href="ChatMessages.php?recipient_id=<?php echo $uid; ?>" class="list-group-item list-group-item-action d-flex align-items-start">
                            <div class="me-2">
                                <img src="<?php echo htmlspecialchars($summary['avatar'] ?? '/assets/img/default-avatar.png'); ?>" alt="" class="rounded-circle" width="40" height="40">
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($name); ?></strong>
                                    <small class="text-muted"><?php echo $time ? htmlspecialchars(date('M d, H:i', strtotime($time))) : ''; ?></small>
                                </div>
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
</script>
<script src="../assets/js/messages.js"></script>
