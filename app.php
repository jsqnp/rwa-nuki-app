<?php
require_once 'config.php';

session_start();

function getAccessRules() {
    return defined('ACCESS_RULES') && is_array(ACCESS_RULES) ? ACCESS_RULES : [];
}

function getGroupHierarchyCacheTtlSeconds() {
    return 7 * 24 * 60 * 60;
}

function getGroupHierarchyCacheDirectory() {
    return __DIR__ . '/cache';
}

function getGroupHierarchyCacheFilePath() {
    return getGroupHierarchyCacheDirectory() . '/group-hierarchy-cache.json';
}

function ensureGroupHierarchyCacheDirectoryExists() {
    $directory = getGroupHierarchyCacheDirectory();
    if (is_dir($directory)) {
        return true;
    }

    return @mkdir($directory, 0775, true);
}

function getPersistentGroupHierarchyCache() {
    $filePath = getGroupHierarchyCacheFilePath();

    if (!is_file($filePath)) {
        return [];
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function savePersistentGroupHierarchyCache($cache) {
    if (!is_array($cache)) {
        return false;
    }

    if (!ensureGroupHierarchyCacheDirectoryExists()) {
        return false;
    }

    $filePath = getGroupHierarchyCacheFilePath();
    $payload = json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        return false;
    }

    return @file_put_contents($filePath, $payload, LOCK_EX) !== false;
}

function getPersistentGroupHierarchyCacheEntry($groupId) {
    $groupId = (int)$groupId;
    if ($groupId <= 0) {
        return null;
    }

    $cache = getPersistentGroupHierarchyCache();
    $entry = $cache[(string)$groupId] ?? null;
    if (!is_array($entry)) {
        return null;
    }

    $cachedAt = isset($entry['cached_at']) ? (int)$entry['cached_at'] : 0;
    if ($cachedAt <= 0) {
        return null;
    }

    if ((time() - $cachedAt) > getGroupHierarchyCacheTtlSeconds()) {
        return null;
    }

    return $entry;
}

function setPersistentGroupHierarchyCacheEntry($groupId, $data) {
    $groupId = (int)$groupId;
    if ($groupId <= 0 || !is_array($data)) {
        return false;
    }

    $cache = getPersistentGroupHierarchyCache();
    $data['cached_at'] = time();
    $cache[(string)$groupId] = $data;

    return savePersistentGroupHierarchyCache($cache);
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
        $_SESSION['matched_access_role'],
        $_SESSION['matched_access_entries'],
        $_SESSION['matched_access_roles'],
        $_SESSION['group_hierarchy_cache'],
        $_SESSION['last_hierarchy_debug']
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

function getHitobitoLanguage() {
    $allowedLanguages = ['de', 'fr', 'it'];
    $language = $_SESSION['user_info']['correspondence_language'] ?? HITOBITO_DEFAULT_LANGUAGE;
    $language = mb_strtolower(trim((string)$language));

    if (!in_array($language, $allowedLanguages, true)) {
        return HITOBITO_DEFAULT_LANGUAGE;
    }

    return $language;
}

function getGroupHierarchyCache() {
    if (!isset($_SESSION['group_hierarchy_cache']) || !is_array($_SESSION['group_hierarchy_cache'])) {
        $_SESSION['group_hierarchy_cache'] = [];
    }

    return $_SESSION['group_hierarchy_cache'];
}

function setGroupHierarchyCacheEntry($groupId, $data) {
    $cache = getGroupHierarchyCache();
    $cache[(string)$groupId] = $data;
    $_SESSION['group_hierarchy_cache'] = $cache;
}

function getGroupHierarchyData($groupId) {
    $groupId = (int)$groupId;
    if ($groupId <= 0) {
        return [
            'success' => false,
            'error' => 'Ungültige Gruppen-ID',
            'group_id' => $groupId,
        ];
    }

    $cache = getGroupHierarchyCache();
    if (isset($cache[(string)$groupId])) {
        return $cache[(string)$groupId];
    }

    $persistentEntry = getPersistentGroupHierarchyCacheEntry($groupId);
    if (is_array($persistentEntry)) {
        setGroupHierarchyCacheEntry($groupId, $persistentEntry);
        return $persistentEntry;
    }

    $accessToken = $_SESSION['access_token'] ?? '';
    if ($accessToken === '') {
        $result = [
            'success' => false,
            'error' => 'Kein Access Token vorhanden',
            'group_id' => $groupId,
        ];
        setGroupHierarchyCacheEntry($groupId, $result);
        return $result;
    }

    $language = getHitobitoLanguage();
    $url = HITOBITO_BASE_URL . '/' . $language . '/groups/' . $groupId . '.json';
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json',
        'X-Scope: groups',
    ];

    $response = getJSON($url, $headers);

    $hierarchyIds = [];
    if (!empty($response['success']) && isset($response['groups']) && is_array($response['groups']) && count($response['groups']) === 1) {
        $groupDetails = $response['groups'][0];
        if (isset($groupDetails['links']['hierarchies']) && is_array($groupDetails['links']['hierarchies'])) {
            foreach ($groupDetails['links']['hierarchies'] as $hierarchyGroupId) {
                $parsedId = (int)$hierarchyGroupId;
                if ($parsedId > 0) {
                    $hierarchyIds[] = $parsedId;
                }
            }
        }
    }

    $hierarchyIds = array_values(array_unique($hierarchyIds));
    rsort($hierarchyIds);

    $result = [
        'success' => !empty($response['success']),
        'group_id' => $groupId,
        'url' => $url,
        'language' => $language,
        'http_code' => $response['http_code'] ?? null,
        'hierarchy_ids' => $hierarchyIds,
        'raw' => $response,
    ];

    if (empty($response['success'])) {
        $result['error'] = $response['error'] ?? 'Hierarchie konnte nicht geladen werden';
    }

    setGroupHierarchyCacheEntry($groupId, $result);
    setPersistentGroupHierarchyCacheEntry($groupId, $result);
    return $result;
}

function roleMatchesGroupRule($role, $rule) {
    if (!is_array($role) || !is_array($rule)) {
        return false;
    }

    $targetGroupId = isset($rule['layer_group_id']) ? (int)$rule['layer_group_id'] : 0;
    if ($targetGroupId <= 0) {
        return false;
    }

    $roleGroupId = isset($role['group_id']) ? (int)$role['group_id'] : 0;
    $includeSubgroups = !empty($rule['include_subgroups']);

    if ($roleGroupId === $targetGroupId) {
        $_SESSION['last_hierarchy_debug'] = [
            'match_type' => 'direct_group_match',
            'target_group_id' => $targetGroupId,
            'role_group_id' => $roleGroupId,
        ];
        return true;
    }

    if (!$includeSubgroups || $roleGroupId <= 0) {
        $_SESSION['last_hierarchy_debug'] = [
            'match_type' => 'no_subgroup_check',
            'target_group_id' => $targetGroupId,
            'role_group_id' => $roleGroupId,
        ];
        return false;
    }

    $hierarchyData = getGroupHierarchyData($roleGroupId);
    $_SESSION['last_hierarchy_debug'] = $hierarchyData;

    if (empty($hierarchyData['success'])) {
        return false;
    }

    return in_array($targetGroupId, $hierarchyData['hierarchy_ids'] ?? [], true);
}

function getMatchingAccessEntries() {
    clearPermissionCache();

    if (!isLoggedIn()) {
        return [];
    }

    $userInfo = $_SESSION['user_info'] ?? [];
    $roles = $userInfo['roles'] ?? [];
    $rules = getAccessRules();

    if (empty($roles) || !is_array($roles) || empty($rules) || !is_array($rules)) {
        return [];
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

            $match = [
                'role' => $role,
                'rule' => $rule,
            ];

            $_SESSION['user_role'] = $roleName !== '' ? $roleName : 'Mitglied';
            $_SESSION['user_group'] = $role['group_name'] ?? 'Gruppe';
            $_SESSION['matched_access_rule'] = $rule;
            $_SESSION['matched_access_role'] = $role;
            $_SESSION['matched_access_entries'] = [$match];
            $_SESSION['matched_access_roles'] = [[
                'role_name' => $role['role_name'] ?? 'Mitglied',
                'group_name' => $role['group_name'] ?? 'Gruppe',
                'group_id' => $role['group_id'] ?? null,
                'rule_name' => $rule['name'] ?? null,
                'layer_group_id' => $rule['layer_group_id'] ?? null,
            ]];

            return [$match];
        }
    }

    return [];
}

function getMatchingAccessEntry() {
    $matches = getMatchingAccessEntries();
    return $matches[0] ?? null;
}

function isInAllowedGroup() {
    return count(getMatchingAccessEntries()) > 0;
}

function hasPermission() {
    return isInAllowedGroup();
}

function isDebugRolesEnabled() {
    return defined('DEBUG_ROLES_ENABLED') && DEBUG_ROLES_ENABLED;
}

function getMatchedAccessRoles() {
    getMatchingAccessEntries();
    $matchedRoles = $_SESSION['matched_access_roles'] ?? [];
    return is_array($matchedRoles) ? $matchedRoles : [];
}

function getRoleDebugData() {
    $roles = $_SESSION['user_info']['roles'] ?? [];
    $hierarchyChecks = [];
    $matches = getMatchingAccessEntries();

    if (is_array($roles)) {
        foreach ($roles as $role) {
            $groupId = isset($role['group_id']) ? (int)$role['group_id'] : 0;
            if ($groupId > 0) {
                $hierarchyChecks[(string)$groupId] = getGroupHierarchyData($groupId);
            }
        }
    }

    return [
        'logged_in' => isLoggedIn(),
        'has_permission' => count($matches) > 0,
        'access_rules' => getAccessRules(),
        'matched_access_rule' => $_SESSION['matched_access_rule'] ?? null,
        'matched_access_role' => $_SESSION['matched_access_role'] ?? null,
        'matched_access_entries' => $matches,
        'matched_access_roles' => getMatchedAccessRoles(),
        'last_hierarchy_debug' => $_SESSION['last_hierarchy_debug'] ?? null,
        'hierarchy_checks' => $hierarchyChecks,
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
