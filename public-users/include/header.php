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

    <!-- Preloader CSS -->
    <style>
    #preloader {
        position: fixed;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: #fff;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loader {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    </style>
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

    <?php if (empty($pageTitle) || strtolower($pageTitle) !== 'messages') { ?>
    <!-- Preloader Markup -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <?php } ?>