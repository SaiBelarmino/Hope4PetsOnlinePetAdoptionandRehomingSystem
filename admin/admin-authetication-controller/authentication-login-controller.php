<?php
/**
 * Admin Authentication Login Controller
 * 
 * Handles admin login.
 * Validates credentials and logs in the admin.
 * Creates session on successful login.
 * Redirects to admin dashboard.
 * On failure, redirects back with error message.
 * 
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
// Ensure SessionManager is available
require_once __DIR__ . '/../../config/SessionManager.php';

class AdminAuthenticationLoginController extends BaseController {
    /**
     * Authenticate admin by email or username and password.
     */
    public static function authenticate(string $identity, string $password) {
        $mysqli = self::db();

        // Try to find admin by email or username
        $sql = 'SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1';
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param('ss', $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            unset($admin['password_hash']); // Don't expose hash
            return $admin;
        }
        return null;
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The login form uses name="email" but it accepts either email or username.
    // Accept either posted 'identity' or 'email' or 'username' for compatibility.
    $identity = trim(
        $_POST['identity'] ??
        $_POST['email'] ??
        $_POST['username'] ??
        ''
    ); // email or username
    $password = $_POST['password'] ?? '';

    $errors = [];

    // Validation
    if (empty($identity)) $errors[] = 'Email or username is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        $admin = AdminAuthenticationLoginController::authenticate($identity, $password);

        if ($admin) {
            // Log the admin in and redirect to admin dashboard or requested page.
            AdminSessionManager::loginAdmin($admin);
            SessionManager::setFlash('success', 'Login successful! Welcome to the admin panel!');
            // Respect a redirect parameter (POST or GET)
            $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? null;
            if ($redirect) {
                header('Location: ' . $redirect);
            } else {
                header('Location: ../views/admin-dashboard.php');
            }
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
    // Redirect back with errors
    $errorQuery = http_build_query(['error' => implode(' ', $errors)]);
    header('Location: ../admin-authentication/authentication-login.php?' . $errorQuery);
    exit;
}
?>
