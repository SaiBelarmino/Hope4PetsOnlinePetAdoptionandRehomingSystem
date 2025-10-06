<?php
/**
 * Registration Handler
 * 
 * Processes user registration form and creates new accounts.
 */

require_once __DIR__ . '/../authentication-controllers/authentication-signup-controller.php';
require_once __DIR__ . '/../../config/SessionManager.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
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
        // Attempt registration
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
            // Auto-login after successful registration
            require_once __DIR__ . '/../authentication-controllers/authentication-login-controller.php';
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
        header('Location: ../user-authentication/register.php');
        exit;
    }
}
?>
