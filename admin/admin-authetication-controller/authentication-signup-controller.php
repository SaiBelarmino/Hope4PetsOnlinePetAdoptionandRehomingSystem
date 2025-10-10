<?php
session_start();

// Include your database connection file - FIXED PATH
require_once '../config/db_connection.php';

class AuthenticationSignupController {
    private $conn;
    
    public function __construct() {
        // Use the existing connection from db_connection.php
        global $conn;
        $this->conn = $conn;
        
        if (!$this->conn) {
            error_log("Database connection failed");
            $this->sendErrorResponse("Database connection failed. Please try again.");
        }
    }
    
    private function sendErrorResponse($message) {
        // FIXED PATH
        header('Location: ../admin-authentication/authentication-signup.php?error=' . urlencode($message));
        exit();
    }
    
    public function handleSignup() {
        // Only process POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // FIXED PATH
            header('Location: ../admin-authentication/authentication-signup.php?error=Invalid request method');
            exit();
        }
        
        // Get and validate form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            header('Location: ../admin-authentication/authentication-signup.php?error=All fields are required');
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ../admin-authentication/authentication-signup.php?error=Invalid email format');
            exit();
        }
        
        if (strlen($password) < 6) {
            header('Location: ../admin-authentication/authentication-signup.php?error=Password must be at least 6 characters long');
            exit();
        }
        
        if (strlen($name) < 2) {
            header('Location: ../admin-authentication/authentication-signup.php?error=Name must be at least 2 characters long');
            exit();
        }
        
        // Process registration
        $result = $this->processRegistration($name, $email, $password);
        
        if ($result['success']) {
            // Auto-login after successful registration
            $loginResult = $this->autoLoginAfterSignup($email, $password);
            
            if ($loginResult['success']) {
                // Redirect to admin dashboard - FIXED PATH
                header('Location: ../admin-dashboard.php');
            } else {
                // If auto-login fails, redirect to login page with success message - FIXED PATH
                header('Location: ../admin-authentication/authentication-login.php?success=' . urlencode($result['message']));
            }
        } else {
            header('Location: ../admin-authentication/authentication-signup.php?error=' . urlencode($result['message']));
        }
        exit();
    }
    
    private function processRegistration($name, $email, $password) {
        try {
            // Check if email already exists
            if ($this->emailExists($email)) {
                return [
                    'success' => false,
                    'message' => 'Email address is already registered.'
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Default role for new signups
            $role = 'admin';
            
            // Prepare and execute insert query
            $query = "INSERT INTO admins (full_name, email, password, role, status) 
                      VALUES (?, ?, ?, ?, 'active')";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                // Get the newly created admin ID
                $admin_id = $this->conn->insert_id;
                
                // Registration successful
                $this->logRegistrationActivity($admin_id, 'success');
                
                return [
                    'success' => true,
                    'message' => 'Registration successful! Welcome to Hope4Pets.',
                    'admin_id' => $admin_id,
                    'email' => $email
                ];
            } else {
                throw new Exception("Registration failed: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->logRegistrationActivity(null, 'failed', $email, $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
    
    private function autoLoginAfterSignup($email, $password) {
        try {
            // Prepare SQL statement to get the newly created admin
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
                
                // Verify password (should always match since we just created it)
                if (password_verify($password, $admin['password'])) {
                    // Set session variables
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                    
                    // Update last login timestamp
                    $this->updateLastLogin($admin['admin_id']);
                    
                    // Log the login activity
                    $this->logLoginActivity($admin['admin_id'], 'success');
                    
                    return [
                        'success' => true,
                        'message' => 'Auto-login successful!',
                        'redirect' => '../admin-dashboard.php'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Auto-login failed. Please login manually.'
            ];
            
        } catch (Exception $e) {
            error_log("Auto-login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Auto-login failed. Please login manually.'
            ];
        }
    }
    
    private function emailExists($email) {
        try {
            $query = "SELECT admin_id FROM admins WHERE email = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
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
    
    private function logRegistrationActivity($admin_id, $status, $email = null, $remarks = '') {
        try {
            $query = "INSERT INTO admin_registration_logs 
                      (admin_id, email, registration_time, status, ip_address, user_agent, remarks) 
                      VALUES 
                      (?, ?, NOW(), ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt->bind_param("isssss", $admin_id, $email, $status, $ip_address, $user_agent, $remarks);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Registration activity logging error: " . $e->getMessage());
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
    $signupController = new AuthenticationSignupController();
    $signupController->handleSignup();
} catch (Exception $e) {
    error_log("Signup controller initialization error: " . $e->getMessage());
    header('Location: ../admin-authentication/authentication-signup.php?error=System error. Please try again.');
    exit();
}
?>