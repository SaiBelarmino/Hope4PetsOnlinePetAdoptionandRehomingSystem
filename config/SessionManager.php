<?php
/**
 * SessionManager
 * 
 * Handles user session management with data isolation per account.
 * Similar to Facebook's session system - each logged-in user sees only their own data.
 * 
 * Key Features:
 * - Secure session initialization
 * - User authentication state management
 * - Role-based access control (user/admin)
 * - Session data isolation per account
 * - CSRF protection
 */

class SessionManager {
    
    /**
     * Initialize session with security settings
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_secure', '0'); // Set to '1' in production with HTTPS
            session_start();
        }
    }
    
    /**
     * Login user and store session data
     * @param array $user User data from database
     */
    public static function login(array $user): void {
        self::init();
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        // Store user data in session (flat)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_verified'] = $user['is_verified'] ?? 0;
        $_SESSION['profile_photo'] = $user['profile_photo'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        // Store user data in $_SESSION['user'] array for compatibility
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'is_verified' => $user['is_verified'] ?? 0,
            'profile_photo' => $user['profile_photo'] ?? null
        ];
        // Check if user has a shelter
        self::refreshShelterStatus();
    }
    
    /**
     * Refresh shelter status for current user
     */
    public static function refreshShelterStatus(): void {
        if (!self::isLoggedIn()) return;
        
        require_once __DIR__ . '/db-connection/db_connection.php';
        global $conn;
        
        $userId = self::getUserId();
        $stmt = $conn->prepare("SELECT id, shelter_name, verified_badge FROM shelters WHERE user_id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $shelter = $result->fetch_assoc();
        $stmt->close();
        
        if ($shelter) {
            $_SESSION['has_shelter'] = true;
            $_SESSION['shelter_id'] = $shelter['id'];
            $_SESSION['shelter_name'] = $shelter['shelter_name'];
            $_SESSION['shelter_verified'] = $shelter['verified_badge'];
        } else {
            $_SESSION['has_shelter'] = false;
            $_SESSION['shelter_id'] = null;
            $_SESSION['shelter_name'] = null;
            $_SESSION['shelter_verified'] = null;
        }
    }
    
    /**
     * Logout user and destroy session
     */
    public static function logout(): void {
        self::init();
        
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        self::init();
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Require user to be logged in (redirect if not)
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ../user-authentication/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId(): ?int {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user data
     */
    public static function getUser(): array {
        self::init();
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'is_verified' => $_SESSION['is_verified'] ?? 0,
            'profile_photo' => $_SESSION['profile_photo'] ?? null,
            'has_shelter' => $_SESSION['has_shelter'] ?? false,
            'shelter_id' => $_SESSION['shelter_id'] ?? null,
            'shelter_name' => $_SESSION['shelter_name'] ?? null,
            'shelter_verified' => $_SESSION['shelter_verified'] ?? null,
        ];
    }
    
    /**
     * Check if user has a shelter
     */
    public static function hasShelter(): bool {
        self::init();
        return !empty($_SESSION['has_shelter']);
    }
    
    /**
     * Get current user's shelter ID
     */
    public static function getShelterId(): ?int {
        self::init();
        return $_SESSION['shelter_id'] ?? null;
    }
    
    /**
     * Set flash message
     */
    public static function setFlash(string $type, string $message): void {
        self::init();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlash(): ?array {
        self::init();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken(): string {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken(string $token): bool {
        self::init();
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Update session data
     */
    public static function update(string $key, $value): void {
        self::init();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session data
     */
    public static function get(string $key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }
}



/**
 * Admin SessionManager Extension
 * Adds admin session management and access control.
 */
class AdminSessionManager extends SessionManager {

    /**
     * Login admin and store session data
     * @param array $admin Admin data from database
     */
    public static function loginAdmin(array $admin): void {
        self::init();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        // Optionally store admin role
        $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
        // Store admin array for easier access and isolation
        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'name' => $admin['name'] ?? '',
            'email' => $admin['email'] ?? '',
            'role' => $admin['role'] ?? 'admin'
        ];
    }

    /**
     * Check if admin is logged in
     */
    public static function isAdminLoggedIn(): bool {
        self::init();
        return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Require admin to be logged in (redirect if not)
     */
    public static function requireAdminLogin(string $redirect = null): void {
        if (!self::isAdminLoggedIn()) {
            $redirectUrl = $redirect ?? ($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: ../admin-authentication/login.php?redirect=' . urlencode($redirectUrl));
            exit;
        }
    }

    /**
     * Get current admin ID
     */
    public static function getAdminId(): ?int {
        self::init();
        return $_SESSION['admin_id'] ?? null;
    }

    /**
     * Get current admin data
     */
    public static function getAdmin(): array {
        self::init();
        return [
            'id' => $_SESSION['admin_id'] ?? ($_SESSION['admin']['id'] ?? null),
            'name' => $_SESSION['admin_name'] ?? ($_SESSION['admin']['name'] ?? ''),
            'email' => $_SESSION['admin_email'] ?? ($_SESSION['admin']['email'] ?? ''),
            'role' => $_SESSION['admin_role'] ?? ($_SESSION['admin']['role'] ?? 'admin'),
        ];
    }

    /**
     * Logout admin and destroy session
     */
    public static function logoutAdmin(): void {
        self::init();
        // Unset admin-related session keys while keeping user session intact
        $keys = ['admin_id','admin_name','admin_email','admin_logged_in','admin_login_time','admin_role','admin'];
        foreach ($keys as $k) {
            if (isset($_SESSION[$k])) unset($_SESSION[$k]);
        }
        // Regenerate session id after logout to prevent fixation
        session_regenerate_id(true);
    }

    /**
     * Check if current admin has a specific role
     */
    public static function hasAdminRole(string $role): bool {
        self::init();
        $current = $_SESSION['admin_role'] ?? ($_SESSION['admin']['role'] ?? null);
        return $current === $role;
    }

    /**
     * Update admin session data
     */
    public static function updateAdmin(array $data): void {
        self::init();
        if (!self::isAdminLoggedIn()) return;
        // Merge into admin array
        $_SESSION['admin'] = array_merge($_SESSION['admin'] ?? [], $data);
        if (isset($data['id'])) $_SESSION['admin_id'] = $data['id'];
        if (isset($data['name'])) $_SESSION['admin_name'] = $data['name'];
        if (isset($data['email'])) $_SESSION['admin_email'] = $data['email'];
        if (isset($data['role'])) $_SESSION['admin_role'] = $data['role'];
    }
}
