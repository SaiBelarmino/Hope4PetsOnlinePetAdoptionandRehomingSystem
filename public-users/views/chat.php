<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * View: chat.php (lightweight single conversation view)
 * Table: messages
 * Expected Variables:
 *  - $conversation => [ {'id','sender_id','recipient_id','body','is_read','created_at','is_outgoing'}, ... ]
 *  - $otherUser => ['id','full_name']
 */
$pageTitle = 'Chat';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="container-fluid py-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($otherUser['full_name'] ?? $pageTitle); ?></h3>
    <a href="./messages.php" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left"></i> All Messages</a>
  </div>
  <div class="card" style="min-height:60vh;">
    <div class="card-body d-flex flex-column">
      <div class="flex-grow-1 overflow-auto mb-3" id="chatScroll">
        <ul class="list-unstyled m-0">
          <?php if(empty($conversation)): ?><li class="text-muted small">No messages yet.</li><?php else: foreach($conversation as $msg): ?>
            <li class="mb-3 d-flex <?php echo !empty($msg['is_outgoing'])? 'flex-row-reverse text-end' : ''; ?>">
              <img src="../../assets/images/profile/user-placeholder.png" class="rounded-circle <?php echo !empty($msg['is_outgoing'])?'ms-2':'me-2'; ?>" width="40" height="40" alt="User">
              <div class="mw-100" style="max-width:70%;">
                <div class="small text-muted mb-1">
                  <?php echo !empty($msg['is_outgoing'])? 'You' : 'User #'.(int)$msg['sender_id']; ?> • <?php echo htmlspecialchars(date('H:i', strtotime($msg['created_at']))); ?>
                </div>
                <div class="p-2 rounded <?php echo !empty($msg['is_outgoing'])? 'bg-primary text-white':'bg-light'; ?>">
                  <?php echo nl2br(htmlspecialchars($msg['body'])); ?>
                </div>
              </div>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
      <form action="../controllers/chat-controller.php" method="post" class="d-flex gap-2">
        <input type="hidden" name="recipient_id" value="<?php echo (int)($otherUser['id'] ?? 0); ?>">
        <input type="text" name="body" class="form-control" placeholder="Type a message" autocomplete="off" required>
        <button class="btn btn-primary"><i class="ti ti-send"></i></button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
