<?php
// =====================================================================
// RWA NUKI CONTROL - KONFIGURATION
// =====================================================================

// ---------------------------------------------------------------------
// App / Branding
// ---------------------------------------------------------------------
define('APP_NAME', 'Chutze RWA');
define('APP_SUBTITLE', 'MiData Nuki Smart Lock Control');
define('APP_VERSION', '2026');
define('APP_AUTHOR', 'Woody');
define('APP_LOGIN_TEXT', 'Bitte melde dich mit deinem MiData-Account an.');
define('APP_ACCESS_TEXT', 'Du kannst das Schloss vom Leitenden-Raum jetzt öffnen oder schliessen.');
define('APP_WARNING_TEXT', 'Hinweis: Verwende die App nur, wenn du persönlich vor Ort bist.');
define('APP_NO_ACCESS_BADGE', 'no-Access');
define('APP_NO_ACCESS_TEXT', 'Keine passende Rolle für die konfigurierte Abteilung gefunden.');
define('APP_STATUS_NOTE', 'Es wird kein Live-Status vom Schloss angezeigt.');
define('APP_LOGIN_BUTTON_TEXT', 'Mit MiData anmelden');
define('APP_UNLOCK_BUTTON_TEXT', 'Öffnen');
define('APP_LOCK_BUTTON_TEXT', 'Schliessen');
define('APP_LOGOUT_BUTTON_TEXT', 'Logout');
define('APP_DEBUG_TEXT', 'Debug aktiv');
define('APP_DEBUG_LINK_TEXT', 'Rollen-Debug öffnen');
define('APP_USER_SECTION_TITLE', 'Benutzer');
define('APP_SCOUT_NAME_LABEL', 'Pfadiname');
define('APP_USERNAME_LABEL', 'Benutzername');
define('APP_EMAIL_LABEL', 'E-Mail');

// ---------------------------------------------------------------------
// Midata / Hitobito OAuth2
// ---------------------------------------------------------------------
define('HITOBITO_CLIENT_ID', 'YOUR_HITOBITO_CLIENT_ID');
define('HITOBITO_CLIENT_SECRET', 'YOUR_HITOBITO_CLIENT_SECRET');
define('HITOBITO_BASE_URL', 'https://pbs.puzzle.ch');
define('REDIRECT_URI', 'https://example.org/auth/midata');
define('HITOBITO_DEFAULT_LANGUAGE', 'de');

// ---------------------------------------------------------------------
// Nuki Web API
// ---------------------------------------------------------------------
define('NUKI_API_TOKEN', 'YOUR_NUKI_API_TOKEN');
define('NUKI_LOCK_ID', 'YOUR_NUKI_LOCK_ID');

// ---------------------------------------------------------------------
// Debug
// Setze für den Normalbetrieb auf false.
// ---------------------------------------------------------------------
define('DEBUG_ROLES_ENABLED', false);

// ---------------------------------------------------------------------
// Zugriffskonfiguration
// ---------------------------------------------------------------------
define('ACCESS_RULES', [
    [
        'name' => 'Example Pfadi Abteilung',
        'layer_group_id' => 12345,
        'include_subgroups' => true,
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
    ],
]);
