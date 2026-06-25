<?php
// =====================================================================
// RWA NUKI CONTROL - KONFIGURATION
// =====================================================================

// Midata / Hitobito OAuth2
define('HITOBITO_CLIENT_ID', 'DEIN_CLIENT_ID');
define('HITOBITO_CLIENT_SECRET', 'DEIN_CLIENT_SECRET');
define('HITOBITO_BASE_URL', 'https://pbs.puzzle.ch');
define('REDIRECT_URI', 'https://rwa.chutze.ch/auth/midata');

// Erlaubte Gruppe
define('ALLOWED_GROUP_ID', 506); // Chutze Pfadi

// Nuki
define('NUKI_API_TOKEN', 'DEIN_NUKI_API_TOKEN');
define('NUKI_LOCK_ID', 'DEINE_NUKI_LOCK_ID');

session_start();

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function clearPermissionCache() {
    unset($_SESSION['user_role'], $_SESSION['user_group']);
}

function isInAllowedGroup() {
    clearPermissionCache();

    if (!isLoggedIn()) {
        return false;
    }

    $userInfo = $_SESSION['user_info'] ?? [];

    if (empty($userInfo['roles']) || !is_array($userInfo['roles'])) {
        return false;
    }

    foreach ($userInfo['roles'] as $role) {
        if (
            isset($role['group_id']) &&
            (int)$role['group_id'] === (int)ALLOWED_GROUP_ID
        ) {
            $_SESSION['user_role'] = $role['role_name'] ?? 'Mitglied';
            $_SESSION['user_group'] = $role['group_name'] ?? 'Gruppe';
            return true;
        }
    }

    return false;
}

function hasPermission() {
    return isInAllowedGroup();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getJSON($url, $headers = []) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'RWA-Nuki-App/1.0'
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        return [
            'success' => false,
            'error' => 'cURL-Fehler: ' . $curlError,
            'http_code' => $httpCode
        ];
    }

    $decoded = json_decode($response, true);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Ungültige JSON-Antwort',
            'http_code' => $httpCode,
            'raw' => $response
        ];
    }

    if (!isset($decoded['success'])) {
        $decoded['success'] = ($httpCode >= 200 && $httpCode < 300);
    }

    $decoded['http_code'] = $httpCode;

    return $decoded;
}

function postJSON($url, $data = null, $headers = []) {
    $ch = curl_init();

    $defaultHeaders = ['Accept: application/json'];

    if ($data !== null) {
        $payload = json_encode($data);
        $defaultHeaders[] = 'Content-Type: application/json';
    } else {
        $payload = '';
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'RWA-Nuki-App/1.0'
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        return [
            'success' => false,
            'error' => 'cURL-Fehler: ' . $curlError,
            'http_code' => $httpCode
        ];
    }

    if ($httpCode === 204 || $response === '') {
        return [
            'success' => true,
            'http_code' => $httpCode,
            'empty_body' => true
        ];
    }

    $decoded = json_decode($response, true);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Ungültige JSON-Antwort',
            'http_code' => $httpCode,
            'raw' => $response
        ];
    }

    if (!isset($decoded['success'])) {
        $decoded['success'] = ($httpCode >= 200 && $httpCode < 300);
    }

    $decoded['http_code'] = $httpCode;

    return $decoded;
}

function controlNukiLock($action) {
    $headers = [
        'Authorization: Bearer ' . NUKI_API_TOKEN
    ];

    if ($action === 1) {
        $url = 'https://api.nuki.io/smartlock/' . NUKI_LOCK_ID . '/action/unlock';
        return postJSON($url, null, $headers);
    }

    if ($action === 2) {
        $url = 'https://api.nuki.io/smartlock/' . NUKI_LOCK_ID . '/action/lock';
        return postJSON($url, null, $headers);
    }

    return [
        'success' => false,
        'error' => 'Ungültige Action'
    ];
}
?>
