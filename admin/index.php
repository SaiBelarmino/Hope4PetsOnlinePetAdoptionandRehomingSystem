<?php
// admin/index.php
// Entry point for admin area. If admin is logged in, redirect to admin dashboard view.
// If not logged in, redirect to admin login with redirect back to attempted URL.

require_once __DIR__ . '/../config/SessionManager.php';

// Initialize session
SessionManager::init();

// If admin is logged in, send to admin dashboard. Otherwise redirect to login.
if (class_exists('AdminSessionManager') && AdminSessionManager::isAdminLoggedIn()) {
    // You may want to change to the actual dashboard path in your project
    header('Location: views/admin-dashboard.php');
    exit;
} else {
    // redirect to admin login and include requested URL to return after login
    $current = $_SERVER['REQUEST_URI'] ?? '/admin/';
    header('Location: admin-authentication/authentication-login.php?redirect=' . urlencode($current));
    exit;
}
