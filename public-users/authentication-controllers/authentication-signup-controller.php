<?php
/**
 * Registration Controller
 * 
 * Handles new user registration and automatic login.
 * Creates new user account with hashed password.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Define the controller class FIRST
class PublicAuthenticationSignupController extends BaseController {
    /**
     * Register a new user according to users table schema.
     */
    public static function register(array $data, array &$errors = []): bool {
        $mysqli = self::db();

        // Basic validation
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $birthday = $data['birthday'] ?? null;
        $gender = $data['gender'] ?? 'unspecified';
        $contactNumber = $data['contact_number'] ?? null;

        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        
        if ($errors) return false;

        // Duplicate email check
        $existing = self::fetchValue('SELECT id FROM users WHERE email = ? LIMIT 1', 's', [$email]);
        if ($existing) {
            $errors[] = 'Email already registered.';
            return false;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = 'INSERT INTO users (full_name, birthday, gender, email, password_hash, contact_number, created_at, updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())';
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            $errors[] = 'Failed to prepare statement.';
            return false;
        }
        
        $stmt->bind_param('ssssss', $fullName, $birthday, $gender, $email, $hash, $contactNumber);
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
    $fullName = trim($_POST['full_name'] ?? $_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? $password; // fallback if no confirm field
    $birthday = $_POST['birthday'] ?? null;
    $gender = $_POST['gender'] ?? 'unspecified';
    $contactNumber = trim($_POST['contact_number'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($fullName)) {
        $errors[] = 'Full name is required.';
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
        // Register user
        $registrationErrors = [];
        $success = PublicAuthenticationSignupController::register([
            'full_name' => $fullName,
            'email' => strtolower($email),
            'password' => $password,
            'birthday' => $birthday,
            'gender' => $gender,
            'contact_number' => $contactNumber
        ], $registrationErrors);
        
        if ($success) {
            // Auto-login after registration
            require_once __DIR__ . '/authentication-login-controller.php';
            $user = PublicAuthenticationLoginController::authenticate($email, $password);
            
            if ($user) {
                SessionManager::login($user);
                SessionManager::setFlash('success', 'Registration successful! Welcome to Hope4Pets!');
                header('Location: ../views/index.php');
                exit;
            }
        } else {
            foreach ($registrationErrors as $error) {
                $errors[] = $error;
            }
        }
    }
    
    // Store errors in session and redirect back
    if (!empty($errors)) {
        SessionManager::setFlash('error', implode('<br>', $errors));
        header('Location: ../user-authentication/authentication-signup.php');
        exit;
    }
}
