<?php
/**
 * Login Controller
 * 
 * Handles user authentication and session initialization.
 * Validates credentials and creates isolated session per user.
 */

require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Define the controller class FIRST
class PublicAuthenticationLoginController extends BaseController {
    /**
     * Attempt to authenticate a user by email/password.
     * Returns user data (without password_hash) or null.
     */
    public static function authenticate(string $email, string $password): ?array {
        $user = self::fetchOne(
            'SELECT id, full_name, email, password_hash, is_verified, profile_photo, birthday, gender, location, contact_number FROM users WHERE email = ? LIMIT 1',
            's',
            [$email]
        );
        
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'] ?? '')) return null;
        
        // Remove sensitive data
        unset($user['password_hash']);
        
        return $user;
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        SessionManager::setFlash('error', 'Please provide both email and password.');
        header('Location: ../user-authentication/authentication-login.php');
        exit;
    }
    
    // Authenticate user
    $user = PublicAuthenticationLoginController::authenticate($email, $password);
    
    if ($user) {
        // Login successful - create session
        SessionManager::login($user);
        SessionManager::setFlash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
        
        // Redirect to dashboard or requested page
        $redirect = $_GET['redirect'] ?? '../views/index.php';
        header('Location: ' . $redirect);
        exit;
    } else {
        // Login failed
        SessionManager::setFlash('error', 'Invalid email or password.');
        header('Location: ../user-authentication/authentication-login.php');
        exit;
    }
}
?>

		if (!$user || !password_verify($password, $user['password_hash'])) {
			self::redirect(['error' => 'Invalid credentials.']);
		}

		session_regenerate_id(true);
		$_SESSION['user_id'] = (int)$user['id'];
		$_SESSION['user_name'] = $user['full_name'];
		$_SESSION['user_email'] = $user['email'];
		$_SESSION['user_verified'] = (int)$user['is_verified'];

		header('Location: ../views/index.php');
		exit();
	}

	private static function redirect(array $params): void {
		$base = '../user-authentication/authentication-login.php';
		$query = http_build_query($params);
		header('Location: ' . $base . ($query ? ('?' . $query) : ''));
		exit();
	}
}

PublicLoginController::handle();

