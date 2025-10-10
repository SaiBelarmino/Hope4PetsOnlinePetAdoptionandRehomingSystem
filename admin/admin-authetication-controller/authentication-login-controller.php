<?php
session_start();

// Include your actual database connection file
require_once '../../config/db_connection.php';

class AuthenticationLoginController {
    private $conn;
    
    public function __construct() {
        // Use the existing connection from db_connection.php
        global $conn;
        $this->conn = 
            error_log("Database connection failed");
            $this->sendErrorResponse("Database connection failed. Please try again.");

    }
    
    private function sendErrorResponse($message) {
        header('Location: ../../admin-authentication/authentication-login.php?error=' . urlencode($message));
        exit();
    }
    
    public function handleLogin() {
        // Only process POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../admin-authentication/authentication-login.php?error=Invalid request method');
            exit();
        }
        
        // Get and validate form data
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Basic validation
        if (empty($email) || empty($password)) {
            header('Location: ../../admin-authentication/authentication-login.php?error=Email and password are required');
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ../../admin-authentication/authentication-login.php?error=Invalid email format');
            exit();
        }
        
        // Process login
        $result = $this->processLogin($email, $password, $remember);
        
        if ($result['success']) {
            header('Location: ' . $result['redirect']);
        } else {
            header('Location: ../../admin-authentication/authentication-login.php?error=' . urlencode($result['message']));
        }
        exit();
    }
    
    private function processLogin($email, $password, $remember) {
        try {
            // Check for brute force attacks
            if ($this->checkBruteForce($email)) {
                return [
                    'success' => false,
                    'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
                ];
            }
            
            // Prepare and execute query using MySQLi
            $query = "SELECT admin_id, email, password, full_name, role, status 
                      FROM admins 
                      WHERE email = ? AND status = 'active' 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("s", $email);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            // Check if user exists
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Login successful
                    $this->setupUserSession($admin);
                    
                    if ($remember) {
                        $this->setRememberMeCookie($admin['admin_id']);
                    }
                    
                    $this->updateLastLogin($admin['admin_id']);
                    $this->logLoginActivity($admin['admin_id'], 'success');
                    
                    return [
                        'success' => true,
                        'message' => 'Login successful!',
                        'redirect' => '../../admin-dashboard.php'
                    ];
                }
            }
            
            // Login failed
            $this->logLoginActivity(null, 'failed', $email, 'Invalid credentials');
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }
    
    private function setupUserSession($admin) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    private function checkBruteForce($email) {
        try {
            $query = "SELECT COUNT(*) as attempt_count 
                      FROM admin_login_attempts 
                      WHERE email = ? 
                      AND status = 'failed' 
                      AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return ($row['attempt_count'] >= 5);
        } catch (Exception $e) {
            error_log("Brute force check error: " . $e->getMessage());
            return false;
        }
    }
    
    private function setRememberMeCookie($admin_id) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            setcookie('remember_me', $token, $expiry, '/', '', false, true);
            
            $query = "UPDATE admins SET remember_token = ? WHERE admin_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $token, $admin_id);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Remember me error: " . $e->getMessage());
        }
    }
    
    private function updateLastLogin($admin_id) {
        try {
            $query = "UPDATE admins SET last_login = NOW() WHERE admin_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    private function logLoginActivity($admin_id, $status, $email = null, $remarks = '') {
        try {
            $query = "INSERT INTO admin_login_attempts 
                      (admin_id, email, attempt_time, status, ip_address, user_agent, remarks) 
                      VALUES 
                      (?, ?, NOW(), ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt->bind_param("isssss", $admin_id, $email, $status, $ip_address, $user_agent, $remarks);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Login activity logging error: " . $e->getMessage());
        }
    }
}

// Check if script is accessed directly
try {
    $loginController = new AuthenticationLoginController();
    $loginController->handleLogin();
} catch (Exception $e) {
    error_log("Controller initialization error: " . $e->getMessage());
    header('Location: ../../admin-authentication/authentication-login.php?error=System error. Please try again.');
    exit();
}
?>