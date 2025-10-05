<?php
// Google OAuth start script.

session_start();

$clientId = trim(getenv('GOOGLE_CLIENT_ID') ?: '375118041490-9gh5vfl3u6k3ql4c3a7v09f6fn2tbu78.apps.googleusercontent.com');
$redirectUri = trim(getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/Hope4PetsOnlinePetAdoptionandRehomingSystem/api/google/google_oauth_callback.php');
if ($redirectUri === '') {
    // Fallback construct (must EXACTLY match one registered in Google Console)
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $redirectUri = $scheme . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google_oauth_callback.php';
}
$scopeList = trim(getenv('GOOGLE_OAUTH_SCOPES') ?: 'openid email profile');
$scope = urlencode($scopeList);

if ($clientId === '') {
    if (isset($_GET['debug'])) {
        header('Content-Type: text/plain');
        echo "Missing GOOGLE_CLIENT_ID env var.\n";
        echo "Computed redirect_uri: $redirectUri\n";
        exit;
    }
    exit('OAuth not configured (GOOGLE_CLIENT_ID missing). Append ?debug=1 for details.');
}

// Allow debug view without redirect
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain');
    echo "Google OAuth Debug Info\n";
    echo "client_id: $clientId\n";
    echo "redirect_uri: $redirectUri\n";
    echo "scopes: $scopeList\n";
    echo "state preview: will generate random 16-byte hex\n";
    echo "To fix redirect_uri_mismatch: Copy EXACT redirect_uri above into Google Cloud Console > Credentials > OAuth 2.0 Client IDs > Authorized redirect URIs.\n";
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth' .
    '?response_type=code' .
    '&client_id=' . urlencode($clientId) .
    '&redirect_uri=' . urlencode($redirectUri) .
    '&scope=' . $scope .
    '&state=' . $state .
    '&access_type=offline' .
    '&prompt=select_account';

header('Location: ' . $authUrl);
exit;
