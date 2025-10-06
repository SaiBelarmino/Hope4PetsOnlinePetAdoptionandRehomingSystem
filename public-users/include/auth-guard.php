<?php
/**
 * Authentication Guard
 * 
 * Include this file at the top of every protected page.
 * Redirects to login if user is not authenticated.
 * Prevents direct URL access without login.
 */

require_once __DIR__ . '/../../config/SessionManager.php';

// Initialize session
SessionManager::init();

// Check if user is logged in
if (!SessionManager::isLoggedIn()) {
    // Store the requested URL to redirect back after login
    $requestedUrl = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header('Location: ../user-authentication/authentication-login.php?redirect=' . urlencode($requestedUrl));
    exit;
}

// Refresh user data from session
$currentUser = SessionManager::getUser();
$userId = SessionManager::getUserId();

// Optional: Check if session is expired (e.g., after 24 hours of inactivity)
$sessionTimeout = 86400; // 24 hours in seconds
$lastActivity = SessionManager::get('last_activity', time());

if ((time() - $lastActivity) > $sessionTimeout) {
    SessionManager::logout();
    header('Location: ../user-authentication/login.php?error=session_expired');
    exit;
}

// Update last activity timestamp
SessionManager::update('last_activity', time());
