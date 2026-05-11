# CLAUDE.md

Diese Datei wird automatisch von Claude Code in jeder Session gelesen.
Sie ist die kompakte, handlungsorientierte Anleitung für die Arbeit am
Projekt **naturlust.net**. Die ausführliche, menschenlesbare Übersicht
liegt in [`README.md`](README.md) – wenn etwas detaillierter erklärt
werden muss, dorthin verlinken, nicht hier duplizieren.

> Pflegehinweis: README.md und CLAUDE.md sind dauerhaft aktuell zu
> halten. Wer hier eine Konvention, einen Befehl oder eine
> Verzeichnisstruktur ändert, aktualisiert beide Dateien im selben
> Arbeitsschritt. Bei strukturellen Änderungen am Code zuerst hier den
> Stand nachziehen, dann committen.

---

## Projekt in einem Satz

Greenfield-Redesign der WordPress-Seite naturlust.net als Custom-Block-
Theme (FSE) in einer DDEV-Umgebung; Inhalte werden über die REST-API
der bestehenden Live-Site übernommen.

## Stack-Eckdaten

- Lokale Umgebung: DDEV, PHP 8.3, nginx-fpm, MariaDB 11.8
- WordPress 6.9.4, Locale `de_DE`, Permalinks `/%postname%/`
- Lokale URL: <https://naturlust.ddev.site>
- Theme im Aufbau: [`public/wp-content/themes/naturlust/`](public/wp-content/themes/naturlust/)
- Webroot: `public/`

## Verzeichnis-Landkarte

- `ASSETS/` – Original-Skizzen, Renderings, Logos und Buttons vom
  Auftraggeber. **Nur lesen, nichts verändern.** Web-optimierte
  Varianten liegen unter `…/themes/naturlust/assets/images/`.
- `public/` – WordPress-Docroot.
- `public/wp-content/themes/naturlust/` – Projekt-Theme.
  - `style.css`, `theme.json`, `functions.php`
  - `templates/` – Block-Templates (index, front-page, single, page,
    archive, search, 404)
  - `parts/` – Template-Parts (header, footer)
  - `patterns/` – PHP-Patterns (front-hero, category-tiles,
    footer-copy)
  - `inc/` – PHP-Setup (setup, assets, shortcodes)
  - `assets/css/theme.css` – globales Frontend-CSS
  - `assets/images/` – web-optimierte Theme-Bilder
- `public/wp-content/plugins/naturlust-*/` – ggf. eigene Plugins
  (versioniert, sobald angelegt).
- `.ddev/` – DDEV-Konfiguration (versioniert, ohne Cache-Verzeichnisse).
- Alles andere unter `public/` ist **nicht** versioniert (siehe
  `.gitignore`).

## Wichtige Befehle

Bevorzugt WP-CLI über DDEV verwenden, nie händisch in der Datenbank
arbeiten.

| Zweck                                  | Befehl                                                  |
|----------------------------------------|---------------------------------------------------------|
| Stack starten / stoppen                | `ddev start`, `ddev stop`                               |
| Status, URLs, Ports                    | `ddev describe`                                         |
| WP-CLI                                 | `ddev wp <befehl>`                                      |
| WordPress-Core nachziehen              | `ddev wp core download --locale=de_DE`                  |
| Plugin/Theme installieren              | `ddev wp plugin install <slug> --activate`              |
| Datenbank-Konsole                      | `ddev mysql`                                            |
| Logs Web-Container                     | `ddev logs -s web`                                      |
| Browser öffnen                         | `ddev launch` (Frontend), `ddev launch -p` (Admin)      |

## Konventionen

### Theme

- Das Theme ist ein **Block-Theme (Full Site Editing)**, kein Classic-
  Theme. Layouts liegen in `templates/` und `parts/` als HTML-Block-
  Markup; Design-Tokens kommen aus `theme.json`.
- Farben, Typografie, Spacing **nur** über `theme.json` definieren –
  keine festen Farb-/Pixelwerte direkt in CSS oder Templates.
- Eigene Blocks/Variations werden unter `assets/` (CSS/JS) bzw.
  `inc/` (PHP-Registrierung) abgelegt, sobald sie gebraucht werden.
- `functions.php` bleibt schlank: lediglich Theme-Setup und Includes
  aus `inc/`.
- Dynamische Inhalte (Jahreszahl, Kategorie-Kacheln) werden als
  PHP-Patterns unter `patterns/` umgesetzt und über
  `<!-- wp:pattern {"slug":"naturlust/…"} /-->` eingebunden.
- Pattern-Cache: WordPress cached Theme-Patterns pro Stylesheet-
  Version. Bei Änderungen an einer Datei in `patterns/` im laufenden
  Frontend bitte einmal flushen:
  `ddev wp eval 'wp_get_theme()->delete_pattern_cache();'`.
  Im Backend (`/wp-admin`) wird der Cache bei aktivem `WP_DEBUG`
  automatisch verworfen.

### Inhalte aus der Live-Site

- Quelle ist die öffentliche REST-API: <https://naturlust.net/wp-json/>.
- Beiträge, Seiten, Kategorien, Tags und Medien werden importiert,
  inklusive Beitragsbilder. Medien landen in der lokalen Mediathek
  (`wp-content/uploads/`, nicht versioniert).
- Beim Import gilt: IDs nicht erzwingen, stattdessen über Slug
  abgleichen, damit Mehrfach-Importe idempotent sind.

### Codequalität

- WordPress-Coding-Standards für PHP und JavaScript befolgen.
- Strings, die der Endnutzer sieht, durch `__()` / `_e()` mit der
  Text-Domain `naturlust` laufen lassen.
- Externe Skripte/Styles werden via `wp_enqueue_*` registriert, niemals
  inline.

### Git

- Branch-Namen kurz und beschreibend (`feature/…`, `fix/…`).
- Commit-Messages auf Deutsch oder Englisch, aber konsistent in einer
  Sprache. Aktive Form, max. 72 Zeichen Betreff.
- Vor dem Commit prüfen, dass keine WordPress-Core-Dateien
  eingeschlichen werden (siehe `.gitignore`).

## Was nicht ohne Rückfrage tun

- Inhalte unter `ASSETS/` verändern oder löschen.
- Drittanbieter-Plugins in `public/wp-content/plugins/` einchecken.
- Live-Site naturlust.net schreibend ansprechen (POST/PUT/DELETE über
  REST-API).
- `wp-config.php` versionieren oder darin Secrets ablegen.

## Designvorgaben (Kurzfassung)

Ausführlich in [`README.md`](README.md#design-vorgaben). Wichtigste
Eckpunkte:

- Startseite: Hero-Bereich mit skizzenhaftem Naturmotiv, darunter vier
  runde Kategorie-Buttons (Wandern, Radfahren, Fotografieren,
  Waldbaden), umgeben von einer Landkarten-Hintergrundgrafik.
- Hamburger-Menü oben links für: Über uns, Videos, Wallpapers,
  Veranstaltungen, Kontaktformular, Datenschutzerklärung, Impressum.
- Footer global gleich, mit dynamischer Jahreszahl und Social-Icons
  (Instagram, Facebook).
- Voll responsiv: Smartphone (hoch und quer), Tablet, Desktop.

## Offene Fragen / nächste Schritte

Aktueller Arbeitsstand und nächste Schritte werden in der
Todo-/Plan-Ebene der jeweiligen Session geführt, nicht hier. Diese
Datei beschreibt nur den dauerhaft gültigen Rahmen.
