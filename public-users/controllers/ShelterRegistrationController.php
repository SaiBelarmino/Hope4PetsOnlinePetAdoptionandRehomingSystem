<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

if (class_exists('SessionManager') && method_exists('SessionManager','start')) {
    try { SessionManager::start(); } catch (Exception $e) { if (session_status() === PHP_SESSION_NONE) { session_start(); } }
} else {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

// Helper to set flash messages using SessionManager if available, else $_SESSION
function set_flash($message, $type = 'info') {
    if (class_exists('SessionManager') && method_exists('SessionManager','setFlash')) {
        try { SessionManager::setFlash($type, $message); return; } catch(Exception $e) { /* fallback */ }
    }
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Add controller class that uses BaseController's protected db() method
class ShelterRegistrationController extends BaseController {
    public static function insertShelter(int $userId, string $shelter_name, string $address, string $contact_number, string $created_at, string $status): array {
        // returns [bool $inserted, ?string $errorMessage, ?int $insertId]
        $inserted = false; $error = null; $insertId = null;
        try {
            $mysqli = self::db();
            $sql = 'INSERT INTO shelters (user_id, shelter_name, address, contact_number, created_at, status) VALUES (?, ?, ?, ?, ?, ?)';
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                $error = 'Prepare failed: ' . $mysqli->error;
                return [$inserted, $error, $insertId];
            }
            $stmt->bind_param('isssss', $userId, $shelter_name, $address, $contact_number, $created_at, $status);
            if ($stmt->execute()) {
                $insertId = $mysqli->insert_id;
                $inserted = true;
            } else {
                $error = 'Execute failed: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Throwable $t) {
            $error = $t->getMessage();
        }
        return [$inserted, $error, $insertId];
    }
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('Invalid request method.', 'warning');
    header('Location: ../views/ShelterRegistration.php');
    exit;
}

// Check authentication
$userId = $_SESSION['user']['id'] ?? null;
if (empty($userId)) {
    set_flash('You must be logged in to register a shelter.', 'danger');
    header('Location: ../views/ShelterRegistration.php');
    exit;
}

// Collect and validate input
$shelter_name = trim($_POST['shelter_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');

$errors = [];
if ($shelter_name === '') { $errors[] = 'Shelter name is required.'; }
if ($contact_number === '') { $errors[] = 'Contact number is required.'; }
if ($address === '') { $errors[] = 'Address is required.'; }
if (strlen($shelter_name) > 255) { $errors[] = 'Shelter name is too long.'; }
if (strlen($contact_number) > 50) { $errors[] = 'Contact number is too long.'; }
if (strlen($address) > 255) { $errors[] = 'Address is too long.'; }

if (!empty($errors)) {
    set_flash(implode(' ', $errors), 'danger');
    header('Location: ../views/ShelterRegistration.php');
    exit;
}

// Prepare data
$now = date('Y-m-d H:i:s');
$status = 'pending'; // default status - change as needed

// Use BaseController-backed insert method
$inserted = false;
$dbError = null;
$lastId = null;
try {
    list($inserted, $dbError, $lastId) = ShelterRegistrationController::insertShelter($userId, $shelter_name, $address, $contact_number, $now, $status);
    if ($inserted && $lastId) {
        $_SESSION['shelter_id'] = (int)$lastId;
        $_SESSION['has_shelter'] = true;
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

if ($inserted) {
    set_flash('Shelter registered successfully. Your shelter is pending verification.', 'success');
    // Optionally set session flags so view knows the user has a shelter
    $_SESSION['has_shelter'] = true;
    // Redirect back to registration/view page
    header('Location: ../views/ShelterRegistration.php');
    exit;
}

// If we reach here, DB insert failed. Save to session as fallback so user can continue working.
$pending = [
    'user_id' => $userId,
    'name' => $shelter_name,
    'contact_number' => $contact_number,
    'address' => $address,
    'status' => $status,
    'created_at' => $now,
    'db_error' => $dbError
];

// Store multiple pending registrations if needed
if (empty($_SESSION['pending_shelters'])) { $_SESSION['pending_shelters'] = []; }
$_SESSION['pending_shelters'][] = $pending;

$notice = 'Shelter registration saved locally (fallback).';
if ($dbError) { $notice .= ' DB error: ' . $dbError; }
set_flash($notice, 'warning');
header('Location: ../views/ShelterRegistration.php');
exit;
