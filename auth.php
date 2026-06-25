<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;

    $params = [
        'client_id' => HITOBITO_CLIENT_ID,
        'redirect_uri' => REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'with_roles groups',
        'state' => $state
    ];

    $authUrl = HITOBITO_BASE_URL . '/oauth/authorize?' . http_build_query($params);
    redirect($authUrl);
}

if ($action === 'logout') {
    session_unset();
    session_destroy();
    redirect('/index.php');
}

http_response_code(400);
echo '❌ Unbekannte Action';
?>
