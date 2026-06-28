<?php
require_once 'app.php';

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (empty($state) || $state !== ($_SESSION['oauth_state'] ?? '')) {
    die('❌ Sicherheitsfehler: State ungültig. Bitte Login erneut starten.');
}

unset($_SESSION['oauth_state']);

if (empty($code)) {
    die('❌ Authorization code fehlt.');
}

$tokenUrl = HITOBITO_BASE_URL . '/oauth/token';
$tokenData = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => REDIRECT_URI,
    'client_id' => HITOBITO_CLIENT_ID,
    'client_secret' => HITOBITO_CLIENT_SECRET
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $tokenUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($tokenData),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'RWA-Nuki-App/1.0'
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    die('❌ Token-Request fehlgeschlagen: ' . e($curlError));
}

$tokenResponse = json_decode($response, true);

if (!is_array($tokenResponse) || !isset($tokenResponse['access_token'])) {
    die('❌ Token-Fehler (' . (int)$httpCode . '): ' . e($response));
}

$accessToken = $tokenResponse['access_token'];

$userInfoUrl = HITOBITO_BASE_URL . '/oauth/profile';
$headers = [
    'Authorization: Bearer ' . $accessToken,
    'Accept: application/json',
    'X-Scope: with_roles'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $userInfoUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'RWA-Nuki-App/1.0'
]);

$userResponse = curl_exec($ch);
$userCurlError = curl_error($ch);
$userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userResponse === false) {
    die('❌ User-Info Request fehlgeschlagen: ' . e($userCurlError));
}

$userInfo = json_decode($userResponse, true);

if (!is_array($userInfo) || !isset($userInfo['id'])) {
    die('❌ User-Info Fehler (' . (int)$userHttpCode . '): ' . e($userResponse));
}

$_SESSION['user_id'] = $userInfo['id'];
$_SESSION['user_name'] = trim(($userInfo['first_name'] ?? '') . ' ' . ($userInfo['last_name'] ?? ''));
$_SESSION['user_email'] = $userInfo['email'] ?? '';
$_SESSION['access_token'] = $accessToken;
$_SESSION['user_info'] = $userInfo;

hasPermission();

redirect('/index.php');
?>
