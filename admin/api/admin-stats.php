<?php
// Simple JSON endpoint to return the admin dashboard stats.
// Protect the endpoint: require admin session.
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// Load controller
require_once __DIR__ . '/../controllers/Admin/admin-dashboard-controllers.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(AdminDashboardController::stats());