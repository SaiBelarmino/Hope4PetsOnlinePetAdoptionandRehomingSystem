<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// AdoptPetProcess.php - handles adoption requests from public users
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $action = $_POST['action'] ?? '';
    if ($action !== 'request') {
        throw new Exception('Invalid action.');
    }

    // Require user to be logged in
    $user = $_SESSION['user'] ?? null;
    if (empty($user) || empty($user['id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You must be logged in to submit an adoption request.'];
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    $applicantId = (int)$user['id'];
    $petId = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
    $applicantName = trim($_POST['applicant_name'] ?? '');
    $applicantPhone = trim($_POST['applicant_phone'] ?? '');
    $applicantAddress = trim($_POST['applicant_address'] ?? '');
    $applicantMessage = trim($_POST['applicant_message'] ?? '');

    if ($petId <= 0 || $applicantName === '' || $applicantPhone === '' || $applicantAddress === '') {
        throw new Exception('Missing required fields.');
    }

    // Attempt to load DB config from common locations, fall back to defaults
    $dbHost = '127.0.0.1';
    $dbName = 'hope4pets';
    $dbUser = 'root';
    $dbPass = '';
    if (file_exists(__DIR__ . '/../config/database.php')) {
        // expected to set $dbHost, $dbName, $dbUser, $dbPass
        require __DIR__ . '/../config/database.php';
    } elseif (file_exists(__DIR__ . '/../config.php')) {
        require __DIR__ . '/../config.php';
    }

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Check pet exists and is available
    $stmt = $pdo->prepare('SELECT id, status, shelter_id FROM pets WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $petId]);
    $pet = $stmt->fetch();
    if (!$pet) {
        throw new Exception('Pet not found.');
    }

    if (($pet['status'] ?? '') !== 'available') {
        throw new Exception('This pet is not currently available for adoption.');
    }

    // Prevent duplicate active application by same user
    $check = $pdo->prepare("SELECT COUNT(*) as cnt FROM adoptions WHERE pet_id = :pet_id AND applicant_id = :applicant_id AND status IN ('applied','approved')");
    $check->execute([':pet_id' => $petId, ':applicant_id' => $applicantId]);
    $exists = (int)$check->fetchColumn();
    if ($exists > 0) {
        throw new Exception('You have already submitted an adoption request for this pet.');
    }

    // Insert adoption record
    $insert = $pdo->prepare('INSERT INTO adoptions (pet_id, applicant_id, shelter_id, status, created_at) VALUES (:pet_id, :applicant_id, :shelter_id, :status, NOW())');
    $insert->execute([
        ':pet_id' => $petId,
        ':applicant_id' => $applicantId,
        ':shelter_id' => $pet['shelter_id'] ?? null,
        ':status' => 'applied',
    ]);

    $adoptionId = (int)$pdo->lastInsertId();

    // Optionally store application details in a separate table or as metadata.
    // For now we will store applicant info into a simple table 'adoption_applicants' if it exists.
    if (true) {
        try {
            $pdo->prepare("CREATE TABLE IF NOT EXISTS adoption_applicants (
                id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                adoption_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255),
                phone VARCHAR(100),
                address TEXT,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;")
            ->execute();

            $stmtApp = $pdo->prepare('INSERT INTO adoption_applicants (adoption_id, name, phone, address, message) VALUES (:adoption_id, :name, :phone, :address, :message)');
            $stmtApp->execute([
                ':adoption_id' => $adoptionId,
                ':name' => $applicantName,
                ':phone' => $applicantPhone,
                ':address' => $applicantAddress,
                ':message' => $applicantMessage,
            ]);
        } catch (Throwable $e) {
            // If creation/insertion of additional info fails, ignore — adoption record exists.
        }
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Adoption request submitted successfully.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;

} catch (Exception $ex) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => $ex->getMessage()];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}