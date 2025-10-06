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
