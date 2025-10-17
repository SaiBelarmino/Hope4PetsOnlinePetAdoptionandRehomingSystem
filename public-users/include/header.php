<?php
/**
 * Protected Header - Requires Authentication
 * Include auth-guard to ensure user is logged in
 */
require_once __DIR__ . '/auth-guard.php';
// Helpers
require_once __DIR__ . '/profile_helpers.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $baseTitle = 'Hope4Pets';
    // Avoid undefined variable notice and ensure non-empty value
    $pageTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : null;
    $titleTag = $baseTitle;
    if ($pageTitle) {
        $titleTag = $pageTitle . ' | ' . $baseTitle;
    }
    echo '<title>' . htmlspecialchars($titleTag, ENT_QUOTES, 'UTF-8') . '</title>';
    ?>
    <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo-icon.png" />
    <link rel="stylesheet" href="../../assets/css/styles.css" />

</head>

<?php
// Compute a safe body class from $pageTitle (e.g. 'Messages' -> 'messages-page')
$bodyClass = '';
if (!empty($pageTitle)) {
    // replace non-alnum with dash, lowercase, trim dashes
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $pageTitle);
    $slug = trim(strtolower($slug), '-');
    $bodyClass = ' class="' . htmlspecialchars($slug . '-page', ENT_QUOTES, 'UTF-8') . '"';
}
?>

<body<?php echo $bodyClass; ?>>
<style>
/* FIX: Stop page from shifting when Bootstrap modals open */
/* 1. Force the vertical scrollbar to always be present. */
/* This reserves the space, stopping content expansion/shift. */
body {
  overflow-y: scroll !important;
}

/* 2. Remove Bootstrap's default padding compensation. */
/* This prevents a slight push/shift to the left. */
.modal-open {
  padding-right: 0 !important;
}
</style>
<script>
// Keep window.currentUser in sync with server verification status.
window.refreshVerification = (function(){
  let polling = false;
  async function updateOnce(){
    if (!window.currentUser || !window.currentUser.id) return;
    try {
      const res = await fetch('/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/controllers/check_verification.php', {cache: 'no-store'});
      const d = await res.json();
      if (d && d.ok) {
        const isVerified = !!d.is_verified;
        // update global JS object
        window.currentUser.is_verified = isVerified ? 1 : 0;
        // update any verified badge images rendered with the known src path
        document.querySelectorAll('img[src$="assets/images/svg-verified/verified.svg"]').forEach(img => {
          img.style.display = isVerified ? '' : 'none';
        });
        // update inline badge that may be a separate element data-verified
        document.querySelectorAll('[data-verified-badge]').forEach(el => {
          el.style.display = isVerified ? '' : 'none';
        });
        // hide any 'Verify ID' buttons (those that open the verify modal) when verified
        // buttons that open the modal typically have data-bs-target="#verifyIdModal" or a custom attr data-verify-id-button
        document.querySelectorAll('[data-bs-target="#verifyIdModal"], [data-verify-id-button]').forEach(btn => {
          btn.style.display = isVerified ? 'none' : '';
        });
      }
    } catch (e) {
      console.error('verification refresh failed', e);
    }
  }
  return {
    start: function(intervalMs=15000){
      if (polling) return;
      polling = true;
      updateOnce();
      window._verifyInterval = setInterval(updateOnce, intervalMs);
    },
    stop: function(){
      polling = false;
      if (window._verifyInterval) clearInterval(window._verifyInterval);
    },
    once: updateOnce
  };
})();

// Start polling on pages where there's a logged in user
if (window.currentUser && window.currentUser.id) {
  // Run immediately and then every 15s
  window.refreshVerification.start(15000);
}
</script>