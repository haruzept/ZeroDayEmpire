# ZeroDayEmpire 2 — Quellcode (modernisiert)

> **Version:** zde2src.2.0‑RC6 (15.08.2025)
>
> **Kompatibilität:** PHP 8.3+, MariaDB 10.x (oder kompatible MySQL‑Server), moderner Webserver (Apache/Nginx)

---

## 🔐 Lizenz

Creative Commons **BY‑NC‑SA 2.0 DE** (Namensnennung – nicht kommerziell – Weitergabe unter gleichen Bedingungen). Details in `license_by-nc-sa_2.0_de.txt`. Crystal‑Icons unter LGPL (siehe `static/lizenz.txt` und `static/lgpl.txt`).

> Nutzung auf eigene Gefahr; kein Anspruch auf Support.

---

## ✅ Systemvoraussetzungen

- PHP 8.3 oder höher (üblich: ext‑mysqli, ext‑json, ext‑mbstring)
- MariaDB 10.x
- Apache 2.4+ (empfohlen) oder Nginx
- Shell‑Zugriff für CLI‑Import (alternativ: phpMyAdmin)

---

## 🚀 Schnellstart (Kurzfassung)

1) **Quellcode** ins Webroot deployen (z. B. `/var/www/zde2`).  
2) **Dump importieren**: `DATABASE.DUMP.mariadb10.sql` importiert **Datenbank & Tabellen**.  
3) **DB‑Benutzer anlegen & berechtigen** (falls noch nicht vorhanden).  
4) **`config.php` setzen**: Host, Benutzername, Kennwort und DB‑Name (bzw. Prefix/Suffix).  
5) **Dateirechte** sicher setzen (kein `777`).  
6) Login mit Start‑Accounts, anschließend Passwörter ändern.

---

## 🧭 Schritt‑für‑Schritt‑Anleitung (empfohlen)

### 1. Dateien bereitstellen

Kopiere das Projekt in dein Webserver‑Verzeichnis, z. B.:

```bash
sudo mkdir -p /var/www/zde2
sudo rsync -a . /var/www/zde2/
```

Richte ggf. eine virtuelle Host‑Konfiguration ein (Apache/Nginx), sodass die Domain auf den Ordner zeigt.

### 2. Datenbank & Benutzer einrichten

> Der bereitgestellte Dump **erstellt die Datenbank automatisch** (`CREATE DATABASE IF NOT EXISTS …`). Du kannst ihn direkt als `root`/Admin importieren **oder** zuerst einen dedizierten DB‑Benutzer anlegen und dann mit diesem arbeiten.

**Variante A: Erst Benutzer anlegen, dann importieren**

```sql
-- in der MariaDB‑Shell (z. B. via: sudo mariadb)
CREATE USER IF NOT EXISTS 'zde_user'@'localhost' IDENTIFIED BY 'EinStarkesPasswort!';
CREATE DATABASE IF NOT EXISTS `zde_server1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `zde_server1`.* TO 'zde_user'@'localhost';
FLUSH PRIVILEGES;
```

Danach Import über CLI:

```bash
mysql -u zde_user -p zde_server1 < DATABASE.DUMP.mariadb10.sql
```

**Variante B: Dump als Admin importieren (erzeugt DB), dann Benutzer berechtigen**

```bash
# Import als root/Admin:
sudo mysql < DATABASE.DUMP.mariadb10.sql

# Danach Benutzer anlegen und berechtigen (in der MariaDB‑Shell):
CREATE USER IF NOT EXISTS 'zde_user'@'localhost' IDENTIFIED BY 'EinStarkesPasswort!';
GRANT ALL PRIVILEGES ON `zde_server1`.* TO 'zde_user'@'localhost';
FLUSH PRIVILEGES;
```

**Alternative: phpMyAdmin**

- Melde dich als Admin an, öffne **Import** und wähle `DATABASE.DUMP.mariadb10.sql` aus.  
- Falls noch kein Benutzer existiert, unter **Benutzerkonten** → **Benutzerkonto hinzufügen** → Berechtigungen für Datenbank `zde_server1` vergeben.

### 3. Anwendung konfigurieren (`config.php`)

Öffne `config.php` und setze mindestens die DB‑Parameter. Beispielkonfiguration:

```php
// Datenbankparameter aktiv nutzen
$db_use_this_values = true;

// Datenbank‑Zugangsdaten
$db_host = 'localhost';
$db_username = 'zde_user';
$db_password = 'EinStarkesPasswort!';

// Datenbankname wird i. d. R. aus Prefix + Suffix gebildet:
$database_prefix = 'zde_server';
$database_suffix = '1'; // ergibt 'zde_server1'
```

> Hinweis: Wenn du einen anderen DB‑Namen verwendest, passe `suffix` entsprechend an **oder** stelle sicher, dass die Anwendung auf die korrekte Datenbank zeigt.

### 4. Sichere Dateirechte setzen (kein 777)

Nur schreibpflichtige Verzeichnisse (z. B. `data/`) erhalten Schreibrechte für den Webserver‑User. Beispiel (Debian/Ubuntu mit `www-data`):

```bash
sudo chown -R www-data:www-data /var/www/zde2/data
# Verzeichnisse: 750 (rwx für Owner, rx für Gruppe)
find /var/www/zde2/data -type d -exec chmod 750 {} \;
# Dateien: 640 (rw für Owner, r für Gruppe)
find /var/www/zde2/data -type f -exec chmod 640 {} \;
```

Wenn mehrere Systemnutzer deployen, kannst du eine gemeinsame Gruppe verwenden und `770/660` wählen.

### 5. Erster Start & Login

Rufe die Site im Browser auf. Initial stehen Test‑Accounts zur Verfügung (z. B. *Administrator*, *Administrator2*, *TestUser*). **Passwörter sind leer** – bitte sofort ändern bzw. Testnutzer deaktivieren.

### 6. Cronjob für Punkteberechnung

Die Rangliste wird nicht mehr durch das Aufrufen einer Webseite aktualisiert. Stattdessen muss regelmäßig ein Cronjob den Punkteberechnungsskript ausführen:

```cron
0 */3 * * * /bin/sh /pfad/zum/zde2/cron/run_calc_points.sh
```

Der obige Eintrag berechnet alle drei Stunden die Punkte neu. `run_calc_points.sh` ruft intern `run_calc_points.php` im CLI-Modus auf.

---

## 🔒 Sicherheitsempfehlungen

- **Starke Passwörter** verwenden und sofortige Änderung der Standard‑Accounts.
- Webserver so konfigurieren, dass Verzeichnis‑Listings deaktiviert sind.
- Schreibrechte auf das **Minimum** begrenzen (nur dort, wo nötig).
- Regelmäßige Backups der Datenbank.

---

## 🧩 Fehlerbehebung (kurz)

- *„Access denied for user …“*: Berechtigungen prüfen (`GRANT`), Host (`localhost` vs. `%`), Passwort korrekt?  
- *„Unknown database …“*: Dump erneut importieren oder DB‑Name (Prefix/Suffix) in `config.php` anpassen.  
- *Umlaute/Encoding*: sicherstellen, dass `utf8mb4` als Standard gesetzt ist.

---

## 🗒️ Changelog

- **2.0‑RC6 (15.08.2025)**
  - PHP 8.3 / MariaDB 10.x Kompatibilität
  - Session‑Behandlung überarbeitet
  - SQL‑Injection‑Fixes
  - Entfernung veralteter Warnungen
- **2.0‑RC5 (09.09.2004)**
  - Bugfixes in .htaccess und login.php (kritischer Fehler behoben)
  - kleinere Darstellungsfehler in mehreren Dateien entfernt
- **2.0‑RC4 (04.09.2004)**
  - Diverse Bugfixes: cboard.php („s“-User gefixt), syndikat.php (\\n entfernt)
  - game.php: Anzeige des Hijack‑Levels in PC‑Übersicht hinzugefügt
  - mail.php: Bug mit \\s gefixt
  - user.php: Passwort ändern für Admins gefixt
  - Mini-\"Doku\" hinzugefügt
- **2.0‑RC3 (03.09.2004)**
  - Weitere Bugfixes
- **2.0‑RC2 (03.09.2004)**
  - Änderungen im Detail gegenüber RC1
- **2.0‑RC1 (02.09.2004)**
  - Erster Release Candidate

---

## 💡 Beiträge

Verbesserungen oder Fixes willkommen! Reiche Änderungen als Archiv (ZIP/TAR.GZ) an zde2code@ZeroDayEmpire.org ein, damit sie ggf. auf der offiziellen Seite veröffentlicht werden.

### Forschung & Entwicklung
- Migration `sql/2025-08-28_research_migration.sql` in die Datenbank importieren.
- Neue Seite `research.php` im Spiel aufrufen, um Forschungen zu verwalten.
- Laufende Forschungen werden durch den Aufruf von `research_process()` in `processupgrades()` automatisch verarbeitet.
