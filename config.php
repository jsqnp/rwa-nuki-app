<?php
// =====================================================================
// RWA NUKI CONTROL - KONFIGURATION
// =====================================================================

// Midata / Hitobito OAuth2
define('HITOBITO_CLIENT_ID', 'DEIN_CLIENT_ID');
define('HITOBITO_CLIENT_SECRET', 'DEIN_CLIENT_SECRET');
define('HITOBITO_BASE_URL', 'https://pbs.puzzle.ch');
define('REDIRECT_URI', 'https://rwa.chutze.ch/auth/midata');

// Nuki
define('NUKI_API_TOKEN', 'DEIN_NUKI_API_TOKEN');
define('NUKI_LOCK_ID', 'DEINE_NUKI_LOCK_ID');

// Debug-Ausgabe für Rollen aktivieren/deaktivieren
define('DEBUG_ROLES_ENABLED', true);

session_start();

function getAccessRules() {
    return [
        [
            'group_id' => 375,
            'allowed_roles' => [
                'Einheitsleiter*in',
                'Mitleiter*in',
                'Adressverwalter*in',
                'Abteilungsleiter*in',
                'Abteilungsleiter*in Stv',
                'Sekretariat',
                'PowerUser',
                'Präsident*in',
                'Vize Präsident*in',
                'Präsident*in APV',
                'Präsident*in Elternrat',
                'Materialwart*in',
                'Heimverwalter*in',
                'Stufenleiter*in Biber',
                'Stufenleiter*in Wölfe',
                'Stufenleiter*in Pfadi',
                'Stufenleiter*in Pio',
                'Stufenleiter*in Rover',
                'Stufenleiter*in PTA',
                'Kassier*in',
                'Rechnungen',
            ],
            'include_subgroups' => true,
        ],
    ];
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function clearPermissionCache() {
    unset(
        $_SESSION['user_role'],
        $_SESSION['user_group'],
        $_SESSION['matched_access_rule'],
        $_SESSION['matched_access_role']
    );
}

function normalizeRoleName($roleName) {
    return mb_strtolower(trim((string)$roleName));
}

function isRoleNameAllowed($roleName, $allowedRoles) {
    if (empty($allowedRoles) || !is_array($allowedRoles)) {
        return true;
    }

    $normalizedRoleName = normalizeRoleName($roleName);

    foreach ($allowedRoles as $allowedRole) {
        if ($normalizedRoleName === normalizeRoleName($allowedRole)) {
            return true;
        }
    }

    return false;
}

function roleMatchesGroupRule($role, $rule) {
    if (!is_array($role) || !is_array($rule)) {
        return false;
    }

    $targetGroupId = isset($rule['group_id']) ? (int)$rule['group_id'] : 0;
    if ($targetGroupId <= 0) {
        return false;
    }

    $roleGroupId = isset($role['group_id']) ? (int)$role['group_id'] : 0;
    $roleLayerGroupId = isset($role['layer_group_id']) ? (int)$role['layer_group_id'] : 0;
    $includeSubgroups = !empty($rule['include_subgroups']);

    if ($roleGroupId === $targetGroupId) {
        return true;
    }

    if ($includeSubgroups && $roleLayerGroupId === $targetGroupId) {
        return true;
    }

    return false;
}

function getMatchingAccessEntry() {
    clearPermissionCache();

    if (!isLoggedIn()) {
        return null;
    }

    $userInfo = $_SESSION['user_info'] ?? [];
    $roles = $userInfo['roles'] ?? [];
    $rules = getAccessRules();

    if (empty($roles) || !is_array($roles) || empty($rules) || !is_array($rules)) {
        return null;
    }

    foreach ($roles as $role) {
        foreach ($rules as $rule) {
            if (!roleMatchesGroupRule($role, $rule)) {
                continue;
            }

            $roleName = $role['role_name'] ?? '';
            if (!isRoleNameAllowed($roleName, $rule['allowed_roles'] ?? [])) {
                continue;
            }

            $_SESSION['user_role'] = $roleName !== '' ? $roleName : 'Mitglied';
            $_SESSION['user_group'] = $role['group_name'] ?? 'Gruppe';
            $_SESSION['matched_access_rule'] = $rule;
            $_SESSION['matched_access_role'] = $role;

            return [
                'role' => $role,
                'rule' => $rule,
            ];
        }
    }

    return null;
}

function isInAllowedGroup() {
    return getMatchingAccessEntry() !== null;
}

function hasPermission() {
    return isInAllowedGroup();
}

function isDebugRolesEnabled() {
    return defined('DEBUG_ROLES_ENABLED') && DEBUG_ROLES_ENABLED;
}

function getRoleDebugData() {
    $roles = $_SESSION['user_info']['roles'] ?? [];

    return [
        'logged_in' => isLoggedIn(),
        'has_permission' => hasPermission(),
        'access_rules' => getAccessRules(),
        'matched_access_rule' => $_SESSION['matched_access_rule'] ?? null,
        'matched_access_role' => $_SESSION['matched_access_role'] ?? null,
        'roles' => is_array($roles) ? $roles : [],
    ];
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
