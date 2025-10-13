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