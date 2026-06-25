<?php
require_once 'config.php';

if (!isDebugRolesEnabled()) {
    http_response_code(404);
    exit('Debug deaktiviert');
}

if (!isLoggedIn()) {
    http_response_code(401);
    exit('Nicht angemeldet');
}

$debugData = getRoleDebugData();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Roles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f6f8fa;
            color: #222;
        }

        h1, h2 {
            margin-top: 0;
        }

        .box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
            background: #0d1117;
            color: #e6edf3;
            padding: 16px;
            border-radius: 8px;
            overflow: auto;
        }

        a {
            color: #0969da;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Rollen-Debug</h1>
        <p>Diese Seite zeigt die gelieferten Midata-/Hitobito-Rollen und die aktuell gematchte Zugriffsregel.</p>
        <p><a href="index.php">← Zurück zur Startseite</a></p>
    </div>

    <div class="box">
        <h2>Aktuelle Auswertung</h2>
        <pre><?php echo e(json_encode($debugData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
    </div>
</body>
</html>
