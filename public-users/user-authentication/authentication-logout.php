<?php
/**
 * Logout Handler
 * Destroys user session and redirects to login page
 */

require_once __DIR__ . '/../../config/SessionManager.php';

// Logout user (destroys session)
SessionManager::logout();

// Set success message
SessionManager::init();
SessionManager::setFlash('success', 'You have been logged out successfully.');

// Redirect to login page
header('Location: ./authentication-login.php');
exit;
?>

// Destroy the session
session_destroy();

// Optional: prevent caching of the now logged-out page if accessed via back button
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Redirect to public user login page
header('Location: ../user-authentication/authentication-login.php?logged_out=1');
exit;