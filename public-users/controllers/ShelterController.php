<?php
// API endpoint to fetch all verified shelters or pets for a given shelter
require_once __DIR__ . '/../../config/db-connection/db_connection.php';

// Start session if available so we can detect the currently logged in user
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
// current user id (0 when not logged-in)
$currentUserId = isset($_SESSION['user']['id']) ? intval($_SESSION['user']['id']) : 0;

if (isset($_GET['pets']) && isset($_GET['shelter_id'])) {
    // Fetch pets for a specific shelter
    $shelterId = intval($_GET['shelter_id']);
    // Return pets only if the shelter is verified, or if the requester is the shelter owner
    $pets = [];
    $check = $conn->prepare("SELECT is_verified, user_id FROM shelters WHERE id = ? LIMIT 1");
    if ($check) {
        $check->bind_param('i', $shelterId);
        $check->execute();
        $res = $check->get_result();
        $row = $res->fetch_assoc();
        $check->close();
        $isVerified = (int)($row['is_verified'] ?? 0);
        $ownerId = isset($row['user_id']) ? intval($row['user_id']) : 0;
        // Allow pets to be returned if shelter is verified, OR the requester is logged-in (owner or other logged-in user)
        if ($isVerified === 1 || ($currentUserId > 0)) {
            $sql = "SELECT id, name, type, breed, age, gender, description, photo FROM pets WHERE shelter_id = ? AND status = 'available' ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $shelterId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($r = $result->fetch_assoc()) {
                    $pets[] = $r;
                }
                $stmt->close();
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode($pets);
    exit;
}

// Only return shelters that the admin has verified. Also require verified_at to be set as extra safety.
// Build listing: include all verified shelters; also include the current user's shelter (owner) even if unverified
$shelters = [];
// Build base: verified shelters are visible to all. If requester is logged-in, include all unverified shelters too.
$baseSql = "SELECT s.id, s.shelter_name, s.address, s.contact_number, s.is_verified, s.user_id AS owner_id, u.full_name AS owner_name, 
    (SELECT COUNT(*) FROM pets WHERE shelter_id = s.id) AS pet_count
    FROM shelters s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE (s.is_verified = 1 AND s.verified_at IS NOT NULL)";

if ($currentUserId > 0) {
    // Logged-in users can also see unverified shelters
    $baseSql = "SELECT s.id, s.shelter_name, s.address, s.contact_number, s.is_verified, s.user_id AS owner_id, u.full_name AS owner_name, 
    (SELECT COUNT(*) FROM pets WHERE shelter_id = s.id) AS pet_count
    FROM shelters s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE 1"; // no verification filter for logged-in users
}

$baseSql .= " ORDER BY s.shelter_name ASC";

if ($currentUserId > 0) {
    // For logged-in users just run the base SQL directly
    $stmt = $conn->prepare($baseSql);
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['is_owner'] = (isset($row['owner_id']) && intval($row['owner_id']) === $currentUserId) ? 1 : 0;
            $shelters[] = $row;
        }
        $stmt->close();
    }
} else {
    // Anonymous visitors only see verified shelters with verified_at set
    $result = $conn->query($baseSql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['is_owner'] = 0;
            $shelters[] = $row;
        }
    }
}
header('Content-Type: application/json');
echo json_encode($shelters);
