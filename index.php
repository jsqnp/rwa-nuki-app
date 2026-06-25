<?php
require_once 'config.php';

$isLoggedIn = isLoggedIn();
$hasPermission = hasPermission();
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? 'Mitglied';
$userGroup = $_SESSION['user_group'] ?? 'Gruppe';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuki Lock Control</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .subtitle {
            color: #999;
            font-size: 12px;
            margin-top: 6px;
        }

        .user-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .user-info p {
            margin: 8px 0;
            color: #555;
        }

        .badge {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin: 30px 0;
        }

        button {
            flex: 1;
            padding: 15px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .unlock-btn {
            background: #4CAF50;
            color: white;
        }

        .unlock-btn:hover:not(:disabled) {
            background: #45a049;
        }

        .lock-btn {
            background: #f44336;
            color: white;
        }

        .lock-btn:hover:not(:disabled) {
            background: #da190b;
        }

        .login-btn {
            background: #667eea;
            color: white;
            width: 100%;
        }

        .login-btn:hover {
            background: #5568d3;
        }

        .logout-link {
            text-align: center;
            margin-top: 20px;
        }

        .logout-link a,
        .debug-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-link a:hover,
        .debug-link:hover {
            text-decoration: underline;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #2e7d32;
        }

        .info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #1565c0;
        }

        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            font-weight: bold;
            margin: 10px 0;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #667eea;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .small-note {
            margin-top: 10px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        .debug-box {
            margin-top: 16px;
            padding: 12px;
            background: #fafafa;
            border: 1px dashed #bbb;
            border-radius: 8px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Nuki Lock Control</h1>
            <p>Schloss-Verwaltung für Pfadi-Abteilungen</p>
            <p class="subtitle">MiData / Hitobito OAuth2</p>
        </div>

        <?php if (!$isLoggedIn): ?>
            <div class="info">
                👋 Bitte melde dich mit deinem MiData-Account an.
            </div>

            <a href="auth.php?action=login" style="text-decoration: none;">
                <button class="login-btn">🔑 Mit MiData anmelden</button>
            </a>

        <?php else: ?>
            <div class="user-info">
                <p><strong>👤 Benutzer:</strong> <?php echo e($userName); ?></p>
                <p><strong>📧 Email:</strong> <?php echo e($userEmail); ?></p>

                <?php if ($hasPermission): ?>
                    <span class="badge">
                        ✓ <?php echo e($userRole); ?> in <?php echo e($userGroup); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!$hasPermission): ?>
                <div class="error">
                    ⛔ <strong>Keine Berechtigung</strong><br>
                    Keine passende Rolle für die konfigurierte Abteilung gefunden.
                </div>
            <?php else: ?>
                <div class="info">
                    Du kannst das Schloss jetzt öffnen oder schliessen.
                </div>

                <div class="button-group">
                    <button class="unlock-btn" onclick="unlockDoor()">🔓 Öffnen</button>
                    <button class="lock-btn" onclick="lockDoor()">🔒 Schliessen</button>
                </div>

                <div id="message"></div>
                <div class="loading" id="loading">
                    <span class="spinner"></span>Verarbeite...
                </div>

                <div class="small-note">
                    Es wird kein Live-Status vom Schloss angezeigt.
                </div>
            <?php endif; ?>

            <?php if (isDebugRolesEnabled()): ?>
                <div class="debug-box">
                    🧪 Debug aktiv – <a class="debug-link" href="debug-roles.php">Rollen-Debug öffnen</a>
                </div>
            <?php endif; ?>

            <div class="logout-link">
                <a href="auth.php?action=logout">↪️ Logout</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isLoggedIn && $hasPermission): ?>
    <script>
        function unlockDoor() {
            sendCommand('unlock', 'Öffnen ausgelöst');
        }

        function lockDoor() {
            sendCommand('lock', 'Schliessen ausgelöst');
        }

        function sendCommand(action, resultText) {
            const loading = document.getElementById('loading');
            const message = document.getElementById('message');
            const buttons = document.querySelectorAll('.unlock-btn, .lock-btn');

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
                    message.innerHTML = '<div class="success">✅ ' + escapeHtml(resultText) + '</div>';
                    return;
                }

                if (data.error) {
                    message.innerHTML = '<div class="error">❌ Fehler: ' + escapeHtml(data.error) + '</div>';
                    return;
                }

                message.innerHTML = '<div class="error">❌ Unbekannte Antwort vom Server</div>';
            })
            .catch(error => {
                message.innerHTML = '<div class="error">❌ Fehler: ' + escapeHtml(error.message) + '</div>';
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
