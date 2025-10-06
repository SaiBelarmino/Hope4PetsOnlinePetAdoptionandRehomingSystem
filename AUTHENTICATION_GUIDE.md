# Authentication & Security Implementation Guide

## Overview
Complete authentication system with login-protected pages. All pages require authentication except login and registration.

---

## 🔐 Security Architecture

### Protected Pages Pattern
All pages in `public-users/views/` and `public-users/controllers/` are protected by the authentication guard.

```php
// Every protected page includes header.php which includes auth-guard.php
<?php include __DIR__ . '/../include/header.php'; ?>
```

### Public Pages (No Authentication Required)
1. `public-users/user-authentication/authentication-login.php` - Login page
2. `public-users/user-authentication/authentication-signup.php` - Registration page
3. Root `/index.php` - Redirects based on auth status

---

## 🚀 How It Works

### 1. User Accesses Any Page
```
User tries to access: /public-users/views/pets.php
```

### 2. Header.php Loads Auth Guard
```php
// header.php includes:
require_once __DIR__ . '/auth-guard.php';
```

### 3. Auth Guard Checks Login Status
```php
// auth-guard.php logic:
if (!SessionManager::isLoggedIn()) {
    // Store requested URL
    $requestedUrl = $_SERVER['REQUEST_URI'];
    
    // Redirect to login
    header('Location: ../user-authentication/authentication-login.php?redirect=' . urlencode($requestedUrl));
    exit;
}
```

### 4. Two Scenarios

#### **Scenario A: User NOT Logged In** ❌
1. Redirected to login page
2. Original URL saved in `?redirect=` parameter
3. After successful login → redirected back to original page

#### **Scenario B: User IS Logged In** ✅
1. Page loads normally
2. User data available via `SessionManager::getUser()`
3. Session activity timestamp updated

---

## 📋 File Structure

### Core Security Files

```
config/
  └── SessionManager.php          # Session management class

public-users/
  ├── include/
  │   ├── auth-guard.php          # Authentication checker (included in header.php)
  │   └── header.php              # Includes auth-guard automatically
  │
  ├── user-authentication/
  │   ├── authentication-login.php     # Login form (PUBLIC)
  │   ├── authentication-signup.php    # Registration form (PUBLIC)
  │   └── authentication-logout.php    # Logout handler
  │
  ├── controllers/
  │   ├── authentication-login-controller.php   # Handles login POST
  │   ├── register-handler.php                  # Handles registration POST
  │   └── [all other controllers require login]
  │
  └── views/
      └── [all pages require login via header.php]
```

---

## 🔑 Authentication Flow

### Login Process
```
1. User visits login page
   ↓
2. Enters email/password
   ↓
3. Form submits to authentication-login-controller.php
   ↓
4. Controller validates credentials
   ↓
5. If valid:
   - SessionManager::login($user) creates session
   - Redirect to dashboard or ?redirect URL
   ↓
6. If invalid:
   - Redirect back to login with error message
```

### Registration Process
```
1. User visits signup page
   ↓
2. Fills registration form
   ↓
3. Form submits to register-handler.php
   ↓
4. Controller validates and creates account
   ↓
5. If successful:
   - Auto-login via SessionManager::login()
   - Redirect to dashboard
   ↓
6. If failed:
   - Redirect back to signup with error message
```

### Logout Process
```
1. User clicks logout link
   ↓
2. Redirects to authentication-logout.php
   ↓
3. SessionManager::logout() destroys session
   ↓
4. Redirect to login page with success message
```

---

## 🛡️ Security Features

### 1. Session Fixation Prevention
```php
// SessionManager::login() regenerates session ID
session_regenerate_id(true);
```

### 2. Session Timeout
```php
// auth-guard.php checks inactivity
$sessionTimeout = 86400; // 24 hours
if ((time() - $lastActivity) > $sessionTimeout) {
    SessionManager::logout();
    redirect to login with "session_expired" error
}
```

### 3. Secure Session Settings
```php
// SessionManager::init()
ini_set('session.cookie_httponly', '1');  // Prevent JavaScript access
ini_set('session.use_only_cookies', '1'); // No session ID in URLs
ini_set('session.cookie_secure', '0');    // Set to '1' with HTTPS
```

### 4. Password Security
```php
// Registration: bcrypt hashing
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Login: secure verification
password_verify($password, $user['password_hash'])
```

### 5. Direct URL Access Protection
- All view pages require login
- Typing URL directly → redirected to login
- No data exposure without authentication

---

## 📝 Usage Examples

### In Any Protected View
```php
<?php
// header.php automatically protects this page
include __DIR__ . '/../include/header.php';

// Get current user data
$user = SessionManager::getUser();
$userId = SessionManager::getUserId();

// Display user-specific data
echo "Welcome, " . htmlspecialchars($user['full_name']);
?>
```

### Checking Authentication Status
```php
// Check if logged in
if (SessionManager::isLoggedIn()) {
    // User is authenticated
} else {
    // User is guest
}
```

### Flash Messages
```php
// Set message (in controller after action)
SessionManager::setFlash('success', 'Post created successfully!');
header('Location: ../views/index.php');

// Display message (in view)
$flash = SessionManager::getFlash();
if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
    </div>
<?php endif; ?>
```

### Logout Link
```php
<a href="../user-authentication/authentication-logout.php">Logout</a>
```

---

## 🧪 Testing Scenarios

### Test 1: Direct URL Access Without Login
```
1. Clear browser cookies/session
2. Type in address bar: http://localhost/Hope4Pets/public-users/views/pets.php
3. Expected: Redirected to login page
4. After login: Redirected back to pets.php
```

### Test 2: Session Timeout
```
1. Login successfully
2. Wait 24 hours (or modify $sessionTimeout to 60 seconds for testing)
3. Try to access any page
4. Expected: Redirected to login with "session expired" message
```

### Test 3: Multiple Accounts
```
1. Login as User A → Browse pages
2. Logout
3. Login as User B → Browse pages
4. Expected: Each user sees only their own data
5. User B cannot access User A's data
```

### Test 4: Already Logged In
```
1. Login successfully
2. Try to access login page again
3. Expected: Automatically redirected to dashboard
```

### Test 5: Root Index.php
```
1. Not logged in → Access http://localhost/Hope4Pets/
2. Expected: Redirected to login page

3. Login successfully
4. Access http://localhost/Hope4Pets/
5. Expected: Redirected to dashboard (public-users/views/index.php)
```

---

## ⚠️ Important Notes

### Session Management
- Sessions initialized automatically by auth-guard
- Last activity timestamp updated on every page load
- Inactive sessions automatically cleared after timeout

### Redirect After Login
- Original requested URL preserved in `?redirect=` parameter
- After login, user goes back to where they wanted to go
- Example: Tried to access `/pets.php` → redirected to login → after login → back to `/pets.php`

### Protected vs Public Routes
**Protected** (require login):
- All `/views/*.php` pages
- All `/controllers/*.php` files (except authentication)
- Any page that includes `header.php`

**Public** (no login required):
- `/user-authentication/authentication-login.php`
- `/user-authentication/authentication-signup.php`
- Root `/index.php` (but redirects based on status)

---

## 🔧 Configuration

### Session Timeout
Edit `public-users/include/auth-guard.php`:
```php
$sessionTimeout = 86400; // Change to desired seconds
// 3600 = 1 hour
// 86400 = 24 hours
// 604800 = 1 week
```

### HTTPS (Production)
Edit `config/SessionManager.php`:
```php
ini_set('session.cookie_secure', '1'); // Change from '0' to '1'
```

### Custom Login Redirect
Edit `config/SessionManager.php`:
```php
public static function requireLogin(): void {
    if (!self::isLoggedIn()) {
        // Change redirect URL here
        header('Location: ../user-authentication/authentication-login.php');
        exit;
    }
}
```

---

## 🐛 Troubleshooting

### Issue: "Headers already sent" error
**Cause**: Output before `header()` redirect
**Solution**: Ensure no whitespace or echo before `<?php` tag in auth-guard.php or header.php

### Issue: Login successful but redirected to login again
**Cause**: Session not persisting
**Solution**: 
1. Check if cookies are enabled in browser
2. Verify session directory is writable
3. Check `session.save_path` in php.ini

### Issue: Users can access pages without login
**Cause**: Auth guard not included
**Solution**: Ensure page includes `header.php` which includes `auth-guard.php`

### Issue: Infinite redirect loop
**Cause**: Login page protected by auth guard
**Solution**: Login/signup pages should NOT include `header.php`, they use standalone templates

---

## ✅ Implementation Checklist

- [x] SessionManager class created
- [x] auth-guard.php protection file
- [x] header.php includes auth-guard
- [x] Login page (public, redirects if logged in)
- [x] Registration page (public, redirects if logged in)
- [x] Logout handler
- [x] Login controller with session creation
- [x] Registration controller with auto-login
- [x] Root index.php redirects based on auth
- [x] Session timeout protection
- [x] Redirect after login to original URL
- [x] Flash message system
- [x] Password hashing with bcrypt
- [x] Session fixation prevention
- [x] Secure cookie settings

---

## 📚 Summary

**The system now requires login for ALL pages except:**
1. Login page (`authentication-login.php`)
2. Registration page (`authentication-signup.php`)

**Direct URL access protection:**
- Typing any view URL → redirected to login
- After login → redirected back to requested page
- Session-based authentication
- 24-hour session timeout
- Secure password handling
- Flash messages for user feedback

**Every user has isolated data:**
- Each session stores only current user's ID
- All queries filter by logged-in user
- No cross-user data visibility
- Facebook-style account separation

🎉 **Complete authentication system ready for production!**
