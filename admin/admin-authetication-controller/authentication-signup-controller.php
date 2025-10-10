<?php
/**
 * Admin Registration Controller
 * 
 * Handles new admin registration and automatic login.
 * Creates new admin account with hashed password.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';

// Define the controller class FIRST
class AdminAuthenticationSignupController extends BaseController {
    /**
     * Register a new admin according to admins table schema.
     */
    public static function register(array $data, array &$errors = []): bool {
        $mysqli = self::db();

        // Basic validation
    // Database admins table stores `username` (not full_name)
    $username = trim($data['username'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
    // admins table does not include is_verified flag
    if ($username === '') $errors[] = 'Username is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

        if ($errors) return false;

        // Duplicate email check
    $existing = self::fetchValue('SELECT id FROM admins WHERE email = ? OR username = ? LIMIT 1', 'ss', [$email, $username]);
        if ($existing) {
            $errors[] = 'Email already registered.';
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert using admins schema (username, email, password_hash)
        $sql = 'INSERT INTO admins (username, email, password_hash, created_at) VALUES (?,?,?,NOW())';
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $errors[] = 'Failed to prepare statement.';
            return false;
        }

        $stmt->bind_param('sss', $username, $email, $hash);
        $ok = $stmt->execute();

        if (!$ok) {
            if ($mysqli->errno === 1062) {
                $errors[] = 'Email already registered.';
            } else {
                $errors[] = 'Database error: ' . $mysqli->error;
            }
        }
        $stmt->close();
        return $ok;
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form fields: username expected for admins
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? $password; // fallback if no confirm field
    // admins table does not include is_verified

    $errors = [];

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Register admin
        $registrationErrors = [];
        $success = AdminAuthenticationSignupController::register([
            'username' => $username,
            'email' => strtolower($email),
            'password' => $password
        ], $registrationErrors);

        if ($success) {
            // Auto-login after registration (use normalized email)
            require_once __DIR__ . '/authentication-login-controller.php';
            // Auto-login by email; login controller will accept email or username
            $admin = AdminAuthenticationLoginController::authenticate(strtolower($email), $password);

            if ($admin) {
                SessionManager::login($admin, true);
                SessionManager::setFlash('success', 'Registration successful! Welcome to the admin panel!');
                header('Location: ../views/index.php');
                exit;
            } else {
                // Account created but auto-login failed; send user to login page with a notice
                SessionManager::setFlash('warning', 'Account created. Please sign in to continue.');
                header('Location: ../admin-authentication/authentication-login.php');
                exit;
            }
        } else {
            foreach ($registrationErrors as $error) {
                $errors[] = $error;
            }
        }
    }
    // Redirect back with errors
    $errorQuery = http_build_query(['error' => implode(' ', $errors)]);
    header('Location: ../admin-authentication/authentication-signup.php?' . $errorQuery);
    exit;
} else {
    // Invalid request method
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Method Not Allowed';
    exit;
}
