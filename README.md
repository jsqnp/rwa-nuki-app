# RWA Nuki Control

Eine kleine PHP-Webanwendung für:

- Midata / Hitobito OAuth2 Login
- Berechtigungsprüfung über eine Midata-Gruppe
- Nuki Smart Lock öffnen und schliessen

## Funktionen

- Login über Midata / Hitobito OAuth2
- Zugriff nur für Mitglieder der Gruppe `Chutze Pfadi` (`group_id = 506`)
- Nuki-Schloss per Weboberfläche öffnen
- Nuki-Schloss per Weboberfläche schliessen
- Kein Live-Status des Schlosses, um API-Inkompatibilitäten im Shared-Hosting-Setup zu vermeiden

## Projektstruktur

```text
.
├── config.php
├── index.php
├── auth.php
├── lock-control.php
└── auth/
    └── midata/
        └── index.php
```

## Voraussetzungen

- PHP mit aktivierter cURL-Erweiterung
- Ein Midata / Hitobito OAuth2 Client
- Ein Nuki Web API Token
- Eine Nuki Smart Lock ID
- Webserver / Hosting mit PHP-Unterstützung

## Konfiguration

In `config.php` folgende Platzhalter ersetzen:

- `DEIN_CLIENT_ID`
- `DEIN_CLIENT_SECRET`
- `DEIN_NUKI_API_TOKEN`
- `DEINE_NUKI_LOCK_ID`

Zusätzlich prüfen:

- `REDIRECT_URI` muss exakt mit der bei Midata / Hitobito hinterlegten Redirect-URI übereinstimmen
- In diesem Projekt ist absichtlich **kein abschliessender Slash** enthalten:
  - `https://rwa.chutze.ch/auth/midata`

## Berechtigung

Der Zugriff wird anhand der Midata-Rollen geprüft.
Zugelassen ist aktuell nur:

- `ALLOWED_GROUP_ID = 506`

Das entspricht der Gruppe:

- `Chutze Pfadi`

## Deployment

Dateien auf den Webserver hochladen, z. B. so:

```text
config.php
index.php
auth.php
lock-control.php
auth/midata/index.php
```

## Sicherheitshinweis

Dieses Repository enthält **keine echten Secrets**.
Bitte niemals produktive Tokens oder OAuth-Secrets direkt in ein öffentliches Repository committen.

## Lizenz

Keine Lizenz definiert.
