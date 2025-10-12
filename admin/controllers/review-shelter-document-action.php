<?php
require_once __DIR__ . '/../../config/SessionManager.php';
SessionManager::init();
AdminSessionManager::requireAdminLogin($_SERVER['REQUEST_URI'] ?? null);

// simple POST handler for approve/reject
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = $_POST['action'] ?? '';
$status = ($action === 'approve') ? 'approved' : (($action === 'reject') ? 'rejected' : '');

if ($id <= 0 || !$status) {
    header('Location: ../views/shelter-verification-requests.php');
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;

// try to load DB connection config (optional)
$mysqli = null;
$dbConfig = __DIR__ . '/../../config/db-connection/db_connection.php';
if (file_exists($dbConfig)) {
    require $dbConfig; // may provide $conn or $mysqli
    if (isset($conn) && $conn instanceof mysqli) $mysqli = $conn;
    if (isset($mysqli) && $mysqli instanceof mysqli) $mysqli = $mysqli;
}

// fallback connection (adjust DB name if different)
if (!$mysqli) {
    $mysqli = new mysqli('localhost', 'root', '', 'hope4pets');
}

if ($mysqli->connect_errno) {
    header('Location: ../views/shelter-verification-requests.php');
    exit;
}

// update document status
$updateSql = "UPDATE shelter_documents SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
if ($stmt = $mysqli->prepare($updateSql)) {
    if ($adminId === null) {
        // set reviewed_by = NULL
        $tmp = $mysqli->prepare("UPDATE shelter_documents SET status = ?, reviewed_by = NULL, reviewed_at = NOW() WHERE id = ?");
        $tmp->bind_param('si', $status, $id);
        $tmp->execute();
        $tmp->close();
    } else {
        $aId = intval($adminId);
        $stmt->bind_param('sii', $status, $aId, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// get shelter_id for this document
$shelterId = null;
if ($q = $mysqli->prepare("SELECT shelter_id FROM shelter_documents WHERE id = ?")) {
    $q->bind_param('i', $id);
    $q->execute();
    $q->bind_result($shelterId);
    $q->fetch();
    $q->close();
}

if ($shelterId) {
    // count approved docs for this shelter
    $approvedCount = 0;
    if ($q2 = $mysqli->prepare("SELECT COUNT(*) FROM shelter_documents WHERE shelter_id = ? AND status = 'approved'")) {
        $q2->bind_param('i', $shelterId);
        $q2->execute();
        $q2->bind_result($approvedCount);
        $q2->fetch();
        $q2->close();
    }

    if (intval($approvedCount) >= 3) {
        // mark shelter verified if not already
        if ($adminId === null) {
            $u = $mysqli->prepare("UPDATE shelters SET is_verified = 1, verified_at = NOW(), verified_by = NULL WHERE id = ?");
            $u->bind_param('i', $shelterId);
        } else {
            $aId = intval($adminId);
            $u = $mysqli->prepare("UPDATE shelters SET is_verified = 1, verified_at = NOW(), verified_by = ? WHERE id = ?");
            $u->bind_param('ii', $aId, $shelterId);
        }
        if (isset($u) && $u) {
            $u->execute();
            $u->close();
        }
    } elseif ($status === 'rejected') {
        // optional: if a shelter had been verified but later docs are rejected,
        // you may want custom logic. This implementation does not unverify.
    }
}

// redirect back
header('Location: ../views/shelter-verification-requests.php');
exit;