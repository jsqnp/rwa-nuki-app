<?php
require_once 'app.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Nur POST erlaubt'
    ]);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Nicht angemeldet'
    ]);
    exit;
}

if (!hasPermission()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Keine Berechtigung'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'unlock') {
    $raw = controlNukiLock(1);

    if (!empty($raw['success'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Öffnen ausgelöst',
            'raw' => $raw
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'error' => $raw['error'] ?? 'Nuki-Aktion fehlgeschlagen',
        'raw' => $raw
    ]);
    exit;
}

if ($action === 'lock') {
    $raw = controlNukiLock(2);

    if (!empty($raw['success'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Schliessen ausgelöst',
            'raw' => $raw
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'error' => $raw['error'] ?? 'Nuki-Aktion fehlgeschlagen',
        'raw' => $raw
    ]);
    exit;
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'error' => 'Unbekannte Action'
]);
?>
