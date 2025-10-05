<?php
// Google OAuth callback: exchanges code for tokens and logs user in / creates account.

session_start();
require_once __DIR__ . '/../../config/db-connection/db_connection.php';
require_once __DIR__ . '/../../controllers/BaseController.php';

// Load credentials from environment (preferred). If missing, fallback to provided static values (NOT recommended for production).
$clientId = trim((string) getenv('GOOGLE_CLIENT_ID'));
$clientSecret = trim((string) getenv('GOOGLE_CLIENT_SECRET'));
$redirectUri = trim((string) getenv('GOOGLE_REDIRECT_URI'));

if ($clientId === '' && $clientSecret === '') {
    // Fallback injected per user request. REMOVE before deploying.
    $clientId = '375118041490-9gh5vfl3u6k3ql4c3a7v09f6fn2tbu78.apps.googleusercontent.com';
    $clientSecret = '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';
    if ($redirectUri === '') {
        $redirectUri = 'http://localhost/Hope4PetsOnlinePetAdoptionandRehomingSystem/api/google/google_oauth_callback.php';
    }
}

// Auto-build redirect URI only if not provided by env.
if ($redirectUri === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $redirectUri = $scheme . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/google_oauth_callback.php';
}

if ($clientId === '' || $clientSecret === '') {
    http_response_code(500);
    exit('Google OAuth not configured. Define GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.');
}

if (!isset($_GET['state']) || !hash_equals($_SESSION['google_oauth_state'] ?? '', $_GET['state'])) {
    exit('Invalid state parameter.');
}
if (!isset($_GET['code'])) {
    exit('Missing authorization code.');
}
$code = $_GET['code'];

$tokenResponse = curl_post_json('https://oauth2.googleapis.com/token', [
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
]);
if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
    exit('Failed to get access token');
}

$userInfo = curl_get_json('https://www.googleapis.com/oauth2/v3/userinfo', $tokenResponse['access_token']);
if (!$userInfo || !isset($userInfo['email'])) {
    exit('Failed to fetch user info');
}

$email = strtolower($userInfo['email']);
$fullName = $userInfo['name'] ?? ($userInfo['given_name'] ?? 'Google User');
$picture = $userInfo['picture'] ?? null;

class GoogleAuthTemp extends BaseController { public static function findUser($email){ return self::fetchOne('SELECT id, full_name, email, is_verified, profile_photo FROM users WHERE email = ? LIMIT 1', 's', [$email]); } }

$user = GoogleAuthTemp::findUser($email);
if (!$user) {
    $passwordHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
    $stmt = $conn->prepare('INSERT INTO users (full_name, email, password_hash, profile_photo, is_verified, created_at, updated_at) VALUES (?,?,?,?,1,NOW(),NOW())');
    $stmt->bind_param('ssss', $fullName, $email, $passwordHash, $picture);
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        $user = ['id'=>$userId,'full_name'=>$fullName,'email'=>$email,'is_verified'=>1,'profile_photo'=>$picture];
    } else {
        $stmt->close();
        exit('Failed to create user.');
    }
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['full_name'];

// Redirect to dashboard (correct relative path was two levels up)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
header('Location: ' . $scheme . $host . '/Hope4PetsOnlinePetAdoptionandRehomingSystem/public-users/views/index.php');
exit;

function curl_post_json($url, $params) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return null; }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    return json_decode($response, true);
}
function curl_get_json($url, $accessToken) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return null; }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) return null;
    return json_decode($response, true);
}