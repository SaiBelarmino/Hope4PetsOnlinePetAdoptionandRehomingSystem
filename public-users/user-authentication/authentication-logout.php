<?php
// Secure logout for public users
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate ID to mitigate fixation (optional defensive step pre-destroy)
if (function_exists('session_regenerate_id')) {
    @session_regenerate_id(true);
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Optional: prevent caching of the now logged-out page if accessed via back button
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Redirect to login page (adjust path if moved)
header('Location: ./authentication-login.php?logged_out=1');
exit;