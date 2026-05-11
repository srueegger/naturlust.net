# Naturlust.net

Redesign der Naturlust-Website ([naturlust.net](https://naturlust.net)) als
lokale Entwicklungsumgebung mit DDEV und WordPress. Ziel des Projekts ist
ein eigenständiges, responsives WordPress-Block-Theme im skizzenhaften
Naturkarten-Stil, das die bestehenden Inhalte über die WordPress-REST-API
der Live-Site übernimmt.

---

## Inhaltsverzeichnis

1. [Projektüberblick](#projektüberblick)
2. [Technischer Stack](#technischer-stack)
3. [Verzeichnisstruktur](#verzeichnisstruktur)
4. [Lokale Einrichtung](#lokale-einrichtung)
5. [Tägliche Arbeitsabläufe](#tägliche-arbeitsabläufe)
6. [Inhalte aus der Live-Site übernehmen](#inhalte-aus-der-live-site-übernehmen)
7. [Design-Vorgaben](#design-vorgaben)
8. [Deployment](#deployment)
9. [Lizenz und Rechte](#lizenz-und-rechte)

---

## Projektüberblick

| Eigenschaft       | Wert                                                       |
|-------------------|------------------------------------------------------------|
| Auftraggeber      | Stephan, Betreiber von naturlust.net                       |
| Zielgruppe        | Naturinteressierte zwischen 35 und 65 Jahren               |
| Live-Site         | <https://naturlust.net>                                    |
| Lokale URL        | <https://naturlust.ddev.site>                              |
| Inhaltliche Säulen | Wandern, Radfahren, Fotografieren, Waldbaden              |
| Status            | Theme produktiv, Demo-Inhalte aktiv, Live-Import pausiert  |

Eine ausführliche Beschreibung der Designideen, Skizzen und Renderings des
Auftraggebers liegt unter [`ASSETS/`](ASSETS/).

## Technischer Stack

- DDEV (lokale Container-Umgebung)
- PHP 8.3 (nginx-fpm)
- MariaDB 11.8 (DDEV-Default)
- WordPress 6.9.4 in deutscher Lokalisierung (`de_DE`)
- Eigenes Custom Block-Theme (FSE) im Verzeichnis
  [`public/wp-content/themes/naturlust/`](public/wp-content/themes/naturlust/)
- WP-CLI (über `ddev wp …`)

## Verzeichnisstruktur

```text
.
├── .ddev/                       # DDEV-Konfiguration (versioniert)
├── ASSETS/                      # Original-Skizzen, Renderings und Bilder
│                                # vom Auftraggeber (Stephan)
├── public/                      # WordPress-Webroot
│   └── wp-content/
│       ├── themes/
│       │   └── naturlust/       # Projekt-Theme (versioniert)
│       │       ├── style.css
│       │       ├── theme.json
│       │       ├── functions.php
│       │       ├── templates/   # Block-Templates
│       │       ├── parts/       # Header- und Footer-Parts
│       │       ├── patterns/    # PHP-Block-Patterns
│       │       ├── inc/         # PHP-Setup
│       │       └── assets/      # CSS und web-optimierte Bilder
│       └── plugins/
│           └── naturlust-*/     # eigene Plugins, falls nötig
├── CLAUDE.md                    # Anleitung für Claude/Claude-Code
└── README.md                    # diese Datei
```

WordPress-Core, Drittanbieter-Plugins und Uploads werden bewusst nicht
versioniert. Details siehe [`.gitignore`](.gitignore).

## Lokale Einrichtung

### Voraussetzungen

- Docker (oder OrbStack/Colima)
- DDEV ≥ 1.25
- Optionale Werkzeuge: `git`, `wp-cli` lokal (nicht erforderlich, da über
  DDEV verfügbar)

### Einrichtung aus einem frischen Klon

```bash
git clone <repo-url> naturlust.net
cd naturlust.net

# 1. DDEV starten
ddev start

# 2. WordPress-Core herunterladen
ddev wp core download --locale=de_DE

# 3. Installation durchführen
ddev wp core install \
  --url="https://naturlust.ddev.site" \
  --title="Naturlust.net" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email="dev@example.com" \
  --skip-email

# 4. Theme aktivieren (sobald vorhanden)
ddev wp theme activate naturlust
```

Anschließend ist die Site unter <https://naturlust.ddev.site> erreichbar,
das Backend unter <https://naturlust.ddev.site/wp-admin>.

### Zugangsdaten (lokal)

- Backend-URL: <https://naturlust.ddev.site/wp-admin>
- Benutzer: `admin`
- Passwort: `admin`

Diese Zugangsdaten gelten ausschliesslich lokal und niemals produktiv.

## Tägliche Arbeitsabläufe

| Aufgabe                           | Befehl                                       |
|-----------------------------------|----------------------------------------------|
| Umgebung starten                  | `ddev start`                                 |
| Umgebung stoppen                  | `ddev stop`                                  |
| Status & URLs anzeigen            | `ddev describe`                              |
| WP-CLI ausführen                  | `ddev wp <befehl>`                           |
| MySQL-Konsole                     | `ddev mysql`                                 |
| Browser öffnen (Front-/Backend)   | `ddev launch` / `ddev launch -p`             |
| Logs des Webcontainers            | `ddev logs -s web`                           |

## Inhalte aus der Live-Site übernehmen

Die bestehende Seite läuft selbst auf WordPress und stellt die REST-API
unter <https://naturlust.net/wp-json/> bereit. Beiträge, Seiten,
Kategorien, Tags und Medien werden über das eigene Plugin
[`naturlust-importer`](public/wp-content/plugins/naturlust-importer/)
in die lokale Installation gespiegelt. Der Import läuft idempotent über
Origin-IDs (`_naturlust_origin_id`).

```bash
# Plugin aktivieren (einmalig)
ddev wp plugin activate naturlust-importer

# Komplett-Import von der Standardquelle
ddev wp naturlust import

# Oder phasenweise (empfohlen wegen Speicher beim Medien-Sideload)
ddev wp naturlust import --only=terms
ddev wp naturlust import --only=media
ddev wp naturlust import --only=posts
ddev wp naturlust import --only=pages

# Bestand prüfen
ddev wp naturlust status
```

Relevante REST-Endpunkte:

- `/wp/v2/posts`
- `/wp/v2/pages`
- `/wp/v2/categories`
- `/wp/v2/tags`
- `/wp/v2/media`

**Aktueller Stand:** Der Live-Hoster blockiert Anfragen aus dem
Entwicklungsnetz (`Connection refused` auf 185.30.32.111:443). Lokal
arbeiten wir deshalb mit Demo-Beiträgen und Demo-Seiten, das Plugin
bleibt installiert, aber deaktiviert. Sobald die Verbindung wieder
freigegeben ist, lässt sich der reale Import wie oben beschrieben
starten.

## Design-Vorgaben

Die folgenden Vorgaben stammen aus Skizzen und Schriftverkehr mit dem
Auftraggeber:

- Hauptkategorien als vier runde Kategorie-Buttons auf der Startseite:
  Wandern, Radfahren, Fotografieren, Waldbaden
- Hamburger-Menü mit den Einträgen: Über uns, Videos, Wallpapers,
  Veranstaltungen, Kontaktformular, Datenschutzerklärung, Impressum
- Hintergrund im Stil einer skizzenhaften Landkarte des Naturraums
- Beitragsbilder im Skizzen-/Aquarell-Look (statt fotorealistisch)
- Einheitlicher Footer auf allen Seiten und Beiträgen mit dynamischer
  Jahreszahl sowie Icons für Instagram und Facebook
- Volle Bedienbarkeit auf Smartphone (Hoch- und Querformat), Tablet und
  Desktop

Originaldateien zur Inspiration liegen in [`ASSETS/`](ASSETS/).

## Deployment

Ein produktives Deployment ist noch nicht eingerichtet. Die Live-Site
läuft aktuell unverändert unter <https://naturlust.net>.

## Lizenz und Rechte

Sämtliche Bilder, Skizzen und Inhalte im Verzeichnis `ASSETS/` sowie auf
der Live-Site sind Eigentum des Auftraggebers und dürfen nur im Rahmen
dieses Projekts verwendet werden. Der Quellcode dieses Repositories ist
nicht öffentlich lizenziert.
