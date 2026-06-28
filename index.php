<?php
require_once 'config.php';

$isLoggedIn = isLoggedIn();
$hasPermission = hasPermission();
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userScoutName = trim((string)($_SESSION['user_info']['nickname'] ?? $_SESSION['user_info']['scout_name'] ?? ''));
$matchedRoles = getMatchedAccessRoles();
$appVersion = defined('APP_VERSION') ? APP_VERSION : '2026';
$appAuthor = defined('APP_AUTHOR') ? APP_AUTHOR : 'Woody';
$appName = defined('APP_NAME') ? APP_NAME : 'Chutze RWA';
$appSubtitle = defined('APP_SUBTITLE') ? APP_SUBTITLE : 'MiData Nuki Smart Lock Control';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($appName); ?></title>
    <style>
        :root {
            --bg: #f7f2eb;
            --card: #ffffff;
            --text: #2e2a26;
            --muted: #7a6f66;
            --line: #dfcdb8;
            --primary: #c77f1a;
            --primary-hover: #b06f13;
            --primary-soft: #fbf2e7;
            --success: #3b7f4a;
            --success-soft: #edf7ef;
            --error: #b2412d;
            --error-soft: #fbeceb;
            --info: #8a5a16;
            --info-soft: #fcf4ea;
            --warning: #9a5a00;
            --warning-soft: #fff4e5;
            --shadow: 0 10px 30px rgba(70, 40, 10, 0.08);
            --radius-xl: 22px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 999px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top, rgba(199, 127, 26, 0.08), transparent 35%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 32px 16px;
        }

        .page {
            max-width: 820px;
            margin: 0 auto;
        }

        .title {
            text-align: center;
            margin-bottom: 24px;
        }

        .title-icon {
            width: 32px;
            height: 32px;
            color: var(--primary);
            margin: 0 auto 10px auto;
            display: block;
        }

        .title h1 {
            font-size: 2.15rem;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .title p {
            color: var(--muted);
            font-size: 0.98rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .user-card {
            background: #fffdfb;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 18px;
            margin-bottom: 18px;
        }

        .user-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .section-icon {
            width: 20px;
            height: 20px;
            color: var(--primary);
            flex: 0 0 auto;
        }

        .user-details {
            display: grid;
            gap: 14px;
        }

        .user-item-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 3px;
        }

        .user-item-value {
            font-size: 1rem;
            color: var(--text);
            word-break: break-word;
        }

        .user-email {
            color: var(--muted);
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--success-soft);
            border: 1px solid #bfd8c4;
            color: var(--success);
            border-radius: var(--radius-sm);
            padding: 8px 14px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .badge svg {
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
        }

        .badge.no-access {
            background: var(--error-soft);
            border-color: #efc5bd;
            color: var(--error);
        }

        .message {
            border-radius: var(--radius-md);
            padding: 14px 16px;
            border: 1px solid transparent;
            font-size: 0.95rem;
            line-height: 1.45;
            margin-top: 14px;
        }

        .message.info {
            background: var(--info-soft);
            border-color: #edd4b1;
            color: var(--info);
        }

        .message.error {
            background: var(--error-soft);
            border-color: #efc5bd;
            color: var(--error);
        }

        .message.success {
            background: var(--success-soft);
            border-color: #c7ddcb;
            color: var(--success);
        }

        .message.warning {
            background: var(--warning-soft);
            border-color: #f0cf9c;
            color: var(--warning);
        }

        .button-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 14px;
            margin-top: 20px;
        }

        button,
        .button-link {
            appearance: none;
            border: none;
            border-radius: var(--radius-md);
            padding: 15px 18px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.18s ease;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        button:hover:not(:disabled),
        .button-link:hover {
            transform: translateY(-1px);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-secondary:hover:not(:disabled),
        .btn-secondary:hover {
            background: var(--primary-soft);
        }

        .btn-login {
            width: 100%;
            margin-top: 14px;
        }

        .btn-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .small-note {
            text-align: center;
            margin-top: 14px;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 14px;
            color: var(--primary);
            font-weight: 700;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--primary);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .debug-box {
            margin-top: 16px;
            padding: 13px 14px;
            background: #faf6f0;
            border: 1px dashed #d4b893;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
        }

        .debug-box a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .debug-box a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            color: #b5a89c;
            font-size: 0.78rem;
            margin-top: 16px;
        }

        @media (max-width: 700px) {
            .button-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .title h1 {
                font-size: 1.8rem;
            }

            .card {
                padding: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="title">
            <svg class="title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
            </svg>
            <h1><?php echo e($appName); ?></h1>
            <p><?php echo e($appSubtitle); ?></p>
        </div>

        <div class="card">
            <?php if (!$isLoggedIn): ?>
                <div class="message info">
                    <?php echo e(defined('APP_LOGIN_TEXT') ? APP_LOGIN_TEXT : 'Bitte melde dich mit deinem MiData-Account an.'); ?>
                </div>

                <a href="auth.php?action=login" style="text-decoration: none;">
                    <button class="btn-primary btn-login">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <path d="M10 17l5-5-5-5"></path>
                            <path d="M15 12H3"></path>
                        </svg>
                        <span><?php echo e(defined('APP_LOGIN_BUTTON_TEXT') ? APP_LOGIN_BUTTON_TEXT : 'Mit MiData anmelden'); ?></span>
                    </button>
                </a>

            <?php else: ?>
                <div class="user-card">
                    <div class="user-section-title">
                        <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span><?php echo e(defined('APP_USER_SECTION_TITLE') ? APP_USER_SECTION_TITLE : 'Benutzer'); ?></span>
                    </div>

                    <div class="user-details">
                        <?php if ($userScoutName !== ''): ?>
                            <div>
                                <div class="user-item-title"><?php echo e(defined('APP_SCOUT_NAME_LABEL') ? APP_SCOUT_NAME_LABEL : 'Pfadiname'); ?></div>
                                <div class="user-item-value"><?php echo e($userScoutName); ?></div>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div class="user-item-title"><?php echo e(defined('APP_USERNAME_LABEL') ? APP_USERNAME_LABEL : 'Benutzername'); ?></div>
                            <div class="user-item-value"><?php echo e($userName); ?></div>
                        </div>

                        <div>
                            <div class="user-item-title"><?php echo e(defined('APP_EMAIL_LABEL') ? APP_EMAIL_LABEL : 'E-Mail'); ?></div>
                            <div class="user-item-value user-email"><?php echo e($userEmail); ?></div>
                        </div>
                    </div>

                    <?php if ($hasPermission && !empty($matchedRoles)): ?>
                        <div class="badge-list">
                            <?php foreach ($matchedRoles as $matchedRole): ?>
                                <span class="badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                    <?php echo e($matchedRole['role_name'] ?? 'Mitglied'); ?> in <?php echo e($matchedRole['group_name'] ?? 'Gruppe'); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="badge-list">
                            <span class="badge no-access"><?php echo e(defined('APP_NO_ACCESS_BADGE') ? APP_NO_ACCESS_BADGE : 'no-Access'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$hasPermission): ?>
                    <div class="message error">
                        <?php echo e(defined('APP_NO_ACCESS_TEXT') ? APP_NO_ACCESS_TEXT : 'Keine passende Rolle für die konfigurierte Abteilung gefunden.'); ?>
                    </div>
                <?php else: ?>
                    <div class="message info">
                        <?php echo e(defined('APP_ACCESS_TEXT') ? APP_ACCESS_TEXT : 'Du kannst das Schloss vom Leitenden-Raum jetzt öffnen oder schliessen.'); ?>
                    </div>

                    <div class="message warning">
                        <?php echo e(defined('APP_WARNING_TEXT') ? APP_WARNING_TEXT : 'Hinweis: Verwende die App nur, wenn du persönlich vor Ort bist.'); ?>
                    </div>

                    <div class="button-row">
                        <button class="btn-primary" onclick="unlockDoor()">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                <path d="M8 11V8a4 4 0 0 1 7.2-2.4"></path>
                            </svg>
                            <span><?php echo e(defined('APP_UNLOCK_BUTTON_TEXT') ? APP_UNLOCK_BUTTON_TEXT : 'Öffnen'); ?></span>
                        </button>

                        <button class="btn-secondary" onclick="lockDoor()">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
                            </svg>
                            <span><?php echo e(defined('APP_LOCK_BUTTON_TEXT') ? APP_LOCK_BUTTON_TEXT : 'Schliessen'); ?></span>
                        </button>

                        <a class="button-link btn-secondary" href="auth.php?action=logout">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <path d="M16 17l5-5-5-5"></path>
                                <path d="M21 12H9"></path>
                            </svg>
                            <span><?php echo e(defined('APP_LOGOUT_BUTTON_TEXT') ? APP_LOGOUT_BUTTON_TEXT : 'Logout'); ?></span>
                        </a>
                    </div>

                    <div id="message"></div>
                    <div class="loading" id="loading">
                        <span class="spinner"></span>Verarbeite...
                    </div>

                    <div class="small-note">
                        <?php echo e(defined('APP_STATUS_NOTE') ? APP_STATUS_NOTE : 'Es wird kein Live-Status vom Schloss angezeigt.'); ?>
                    </div>
                <?php endif; ?>

                <?php if (isDebugRolesEnabled()): ?>
                    <div class="debug-box">
                        <?php echo e(defined('APP_DEBUG_TEXT') ? APP_DEBUG_TEXT : 'Debug aktiv'); ?> – <a href="debug-roles.php"><?php echo e(defined('APP_DEBUG_LINK_TEXT') ? APP_DEBUG_LINK_TEXT : 'Rollen-Debug öffnen'); ?></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="footer">
            Erstellt durch <?php echo e($appAuthor); ?> – <?php echo e($appVersion); ?>
        </div>
    </div>

    <?php if ($isLoggedIn && $hasPermission): ?>
    <script>
        function unlockDoor() {
            sendCommand('unlock', <?php echo json_encode(defined('APP_UNLOCK_BUTTON_TEXT') ? APP_UNLOCK_BUTTON_TEXT . ' ausgelöst' : 'Öffnen ausgelöst'); ?>);
        }

        function lockDoor() {
            sendCommand('lock', <?php echo json_encode(defined('APP_LOCK_BUTTON_TEXT') ? APP_LOCK_BUTTON_TEXT . ' ausgelöst' : 'Schliessen ausgelöst'); ?>);
        }

        function sendCommand(action, resultText) {
            const loading = document.getElementById('loading');
            const message = document.getElementById('message');
            const buttons = document.querySelectorAll('button');

            loading.style.display = 'block';
            message.innerHTML = '';
            buttons.forEach(button => {
                button.disabled = true;
            });

            const formData = new FormData();
            formData.append('action', action);

            fetch('lock-control.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.status === 401 || response.status === 403) {
                    window.location.reload();
                    throw new Error('Nicht autorisiert');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    message.innerHTML = '<div class="message success">' + escapeHtml(resultText) + '</div>';
                    return;
                }

                if (data.error) {
                    message.innerHTML = '<div class="message error">Fehler: ' + escapeHtml(data.error) + '</div>';
                    return;
                }

                message.innerHTML = '<div class="message error">Unbekannte Antwort vom Server</div>';
            })
            .catch(error => {
                message.innerHTML = '<div class="message error">Fehler: ' + escapeHtml(error.message) + '</div>';
            })
            .finally(() => {
                loading.style.display = 'none';
                buttons.forEach(button => {
                    button.disabled = false;
                });
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    <?php endif; ?>
</body>
</html>
