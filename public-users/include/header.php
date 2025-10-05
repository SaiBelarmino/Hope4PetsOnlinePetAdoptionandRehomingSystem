<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hope4Pets</title>
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