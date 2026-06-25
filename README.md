# RWA Nuki Control

Eine kleine PHP-Webanwendung für Pfadi-Abteilungen, die ein Nuki-Schloss über MiData absichern möchten.

Die App erlaubt:

- Login über MiData OAuth2
- Berechtigungsprüfung über Rollen in einer konfigurierten Pfadi-Abteilung
- Prüfung von Untergruppen über die echte Hitobito-Gruppenhierarchie
- Öffnen und Schliessen eines Nuki Smart Locks

## Ziel

Diese App soll von einer beliebigen Pfadi-Abteilung verwendet werden können.

Dazu müssen nur folgende Dinge angepasst werden:

- OAuth Client ID und Client Secret
- Nuki API Token und Lock ID
- die eigene `layer_group_id` der Abteilung
- optional die erlaubten Rollen

## Funktionen

- Login über MiData OAuth2
- Rollenprüfung via `with_roles`
- Hierarchieprüfung via `groups`
- Unterstützung für Untergruppen einer Abteilung
- Einfache Weboberfläche für `Öffnen` und `Schliessen`
- Kein Live-Status des Schlosses, um API-Inkompatibilitäten im Shared-Hosting-Setup zu vermeiden

## Projektstruktur

```text
.
├── .gitignore
├── LICENSE
├── config.example.php
├── index.php
├── auth.php
├── lock-control.php
├── debug-roles.php
└── auth/
    └── midata/
        └── index.php
```

## Voraussetzungen

- PHP mit aktivierter cURL-Erweiterung
- Ein MiData OAuth2 Client
- Ein Nuki Web API Token
- Eine Nuki Smart Lock ID
- Webserver / Hosting mit PHP-Unterstützung

## Einrichtung

1. Repository auschecken
2. `config.example.php` nach `config.php` kopieren
3. In `config.php` die Platzhalter ersetzen
4. Die App auf dem Webserver bereitstellen

In `config.php` die Platzhalter ersetzen:

- `YOUR_HITOBITO_CLIENT_ID`
- `YOUR_HITOBITO_CLIENT_SECRET`
- `YOUR_NUKI_API_TOKEN`
- `YOUR_NUKI_LOCK_ID`
- `REDIRECT_URI` auf die eigene URL anpassen

### Beispiel für die Zugriffskonfiguration

```php
function getAccessRules() {
    return [
        [
            'name' => 'Meine Pfadi Abteilung',
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
            ],
        ],
    ];
}
```

### Bedeutung der Felder

- `name`: frei wählbarer Anzeigename für die Regel
- `layer_group_id`: ID der Abteilung / des Layers in MiData / Hitobito
- `include_subgroups`: wenn `true`, zählen auch Rollen in Untergruppen
- `allowed_roles`: erlaubte Rollennamen; ein leeres Array erlaubt alle Rollen in der passenden Gruppe

## Wie die Berechtigung funktioniert

1. Der Benutzer meldet sich via MiData an.
2. Die App liest die Rollen über `/oauth/profile` mit Scope `with_roles`.
3. Für jede Rollen-Gruppe lädt die App zusätzlich `/groups/{id}.json` mit Scope `groups`.
4. Über `links.hierarchies` wird geprüft, ob die Rolle zur konfigurierten Abteilung oder zu einer Untergruppe davon gehört.
5. Nur wenn Gruppe **und** Rolle passen, darf das Schloss bedient werden.

## Deployment

Die Dateien können direkt auf einen PHP-Webserver hochgeladen werden.

Mindestens benötigt:

```text
config.php
index.php
auth.php
lock-control.php
debug-roles.php
auth/midata/index.php
```

## Debug

Für Tests gibt es die Datei `debug-roles.php`.

Standardmässig ist Debug deaktiviert:

```php
define('DEBUG_ROLES_ENABLED', false);
```

Zum Troubleshooting kann es temporär aktiviert werden. Danach sollte es wieder deaktiviert werden.

## Checkliste für neue Pfadi-Abteilungen

- `config.example.php` nach `config.php` kopieren
- MiData OAuth2 Client erstellen oder bestehende Zugangsdaten eintragen
- `REDIRECT_URI` auf die eigene URL setzen
- `YOUR_NUKI_API_TOKEN` eintragen
- `YOUR_NUKI_LOCK_ID` eintragen
- richtige `layer_group_id` der Abteilung eintragen
- bei Bedarf `allowed_roles` anpassen
- Testlogin mit einer berechtigten Rolle durchführen
- Testlogin mit einer nicht berechtigten Rolle durchführen
- `DEBUG_ROLES_ENABLED` nach dem Test wieder auf `false` setzen

## Sicherheitshinweis

Dieses Repository enthält keine echten Secrets.

Die Datei `config.php` sollte nicht ins Repository committed werden. Dafür gibt es die Vorlage `config.example.php`.

## Lizenz

MIT License. Siehe `LICENSE`.
