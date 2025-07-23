# BüA Schwerpunkt-Einwahl Webapp

Eine moderne, webbasierte Anwendung für die Einwahl von Schülern in Schwerpunktkurse der Schulform BüA (Berufsfachschule zum Übergang in Ausbildung).

## 🚀 Features

### **Schüler-Interface**

- ✅ **Intuitive Einwahl**: Schüler können sich mit Vor-/Nachname, Klasse und optionaler E-Mail einwählen
- ✅ **Intelligente Kursauswahl**: Schwerpunkt 2 wird automatisch basierend auf Schwerpunkt 1 und Kombinationsregeln gefiltert
- ✅ **Echtzeit-Kapazitätsanzeige**: Aktuelle Kursbelegung wird live angezeigt (ohne Namenslisten)
- ✅ **Mehrfach-Einwahl-Schutz**: Verhindert doppelte Einwahlen derselben Person
- ✅ **Responsive Design**: Funktioniert perfekt auf Desktop, Tablet und Smartphone
- ✅ **Konfigurierbarer Hinweistext**: Admin kann Informationen für Schüler anpassen

### **Admin-Interface**

- ✅ **Modernes Tab-Dashboard**: Übersichtliche Aufteilung in Übersicht, Schüler-Verwaltung, Konfiguration und Account
- ✅ **Live-Statistiken**: Echtzeit-Übersicht über Kursbelegungen mit visuellen Fortschrittsbalken
- ✅ **Einwahl-Steuerung**: Ein-/Ausschalten der Schülereinwahl mit einem Klick
- ✅ **Schüler-Verwaltung**: Vollständige Übersicht aller Einwahlen mit Suchfunktion
- ✅ **Einzellöschung**: Gezieltes Löschen einzelner Schülereinwahlen
- ✅ **System-Reset**: Sicheres Löschen aller Einwahlen mit Bestätigungsschutz
- ✅ **CSV-Export**: Excel-kompatible Datenexporte mit korrekter Zeichenkodierung
- ✅ **Passwort-Management**: Sichere Passwort-Änderung mit Stärke-Anzeige

### **Flexible Konfiguration**

- ✅ **Dynamische Schwerpunkte**: Kurse und deren Kombinationen können zur Laufzeit angepasst werden
- ✅ **Variable Kapazitäten**: Maximale Teilnehmerzahl pro Kurs individuell einstellbar
- ✅ **Klassenmanagement**: Klassenbezeichnungen frei konfigurierbar
- ✅ **Hinweistext-Editor**: Individuelle Informationen für Schüler mit Formatierungsoptionen

### **Technische Excellence**

- ✅ **Docker-Deployment**: Containerisierte Bereitstellung für einfache Installation
- ✅ **Concurrent Access**: Mehrere Benutzer können gleichzeitig ohne Konflikte arbeiten
- ✅ **MVC-Architektur**: Saubere Trennung von Controller, View und Model
- ✅ **Sichere Authentifizierung**: Bcrypt-Passwort-Hashing und Session-Management
- ✅ **AJAX-Updates**: Live-Statistiken ohne Seitenneuladen

## 📋 Schwerpunkt-Kombinationsregeln

Das System unterstützt verschiedene Kombinationslogiken:

1. **Freie Kombination**: Informationstechnik, Kraftfahrzeugtechnik und Handel können beliebig miteinander kombiniert werden
2. **Pflicht-Kombinationen**: Bestimmte Schwerpunkte können nur zusammen gewählt werden:
   - Metall ↔ Elektro (nur in Kombination)
   - Verwaltung ↔ Ernährung (nur in Kombination)
3. **Kapazitätsprüfung**: Automatische Überprüfung der verfügbaren Plätze
4. **Duplikatschutz**: Verhindert die Wahl desselben Schwerpunkts als Schwerpunkt 1 und 2

## 🐳 Installation & Deployment

### Voraussetzungen

- Docker & Docker Compose
- Mindestens 2GB RAM
- Port 80 und 3306 verfügbar

### Schnellstart

1. **Repository Setup**:

   ```bash
   mkdir buea-einwahl && cd buea-einwahl
   ```

2. **Verzeichnisstruktur erstellen**:

   ```
   buea-einwahl/
   ├── docker-compose.yml
   ├── webapp/
   │   ├── .htaccess
   │   ├── index.php
   │   ├── includes/
   │   │   ├── Database.php
   │   │   └── EinwahlModel.php
   │   ├── admin/
   │   │   ├── index.php
   │   │   ├── login.php
   │   │   ├── dashboard.php
   │   │   └── ajax/
   │   │       └── statistics.php
   │   └── assets/
   │       ├── css/
   │       │   ├── main.css
   │       │   └── admin.css
   │       └── js/
   │           ├── einwahl.js
   │           └── admin.js
   ├── sql/
   │   └── init.sql
   └── php-config/
       └── php.ini
   ```

3. **Container starten**:

   ```bash
   docker-compose up -d
   ```

4. **Zugriff**:
   - **Schüler-Interface**: `http://localhost`
   - **Admin-Dashboard**: `http://localhost/admin/`
   - **Login**: `admin` / `admin123`

### Produktions-Deployment

Für den Produktionseinsatz empfiehlt sich:

1. **Sichere Passwörter**: Alle Standard-Credentials in `docker-compose.yml` ändern
2. **Reverse Proxy**: Nginx/Apache mit SSL/TLS vor die Container schalten
3. **Backup-Strategie**: Automatisierte Datenbank-Backups einrichten
4. **Monitoring**: Log-Aggregation und Health-Checks implementieren

## ⚙️ Konfiguration

### Schwerpunkt-Konfiguration

Im Admin-Dashboard können Schwerpunkte über ein Textfeld konfiguriert werden:

**Format pro Zeile:**

```
maximale_teilnehmer,Schwerpunkt-Name
```

**Beispiele:**

```
10,Kraftfahrzeugtechnik
12,Informationstechnik
15,Handel
12,Metall;Elektrotechnik
10,Verwaltung;Ernährung
```

**Erklärung:**

- `10,Kraftfahrzeugtechnik` = Einzelkurs mit 10 Plätzen
- `12,Metall;Elektrotechnik` = Pflicht-Kombination mit je 12 Plätzen

### Klassen-Konfiguration

Eine Klassenbezeichnung pro Zeile:

```
BüA-1A
BüA-1B
BüA-2A
BüA-2B
```

### Hinweistext-Konfiguration

Formatierungsoptionen für Schüler-Hinweise:

```
• Erster wichtiger Hinweis
• Zweiter Hinweis mit **fetter Schrift**

Weitere Informationen in neuen Absätzen...
```

## 🗄️ Datenbank-Schema

### Haupttabellen

- **`schwerpunkte`**: Verfügbare Kurse mit Kapazitäten und Kombinationsregeln
- **`klassen`**: Konfigurierbare Klassenbezeichnungen
- **`einwahlen`**: Schülereinwahlen mit Audit-Trail (IP, User-Agent, Timestamp)
- **`admin_users`**: Admin-Benutzer mit sicherer Passwort-Speicherung
- **`konfiguration`**: Flexible Systemeinstellungen (Hinweistext, Einwahl-Status, etc.)

### Datenintegrität

- **Foreign Keys**: Referentielle Integrität zwischen allen Tabellen
- **Unique Constraints**: Verhindert Doppel-Einwahlen pro Schüler
- **Audit Trail**: Vollständige Nachverfolgbarkeit aller Aktionen

## 📊 CSV-Export

Der Export enthält alle relevanten Daten:

- Vorname, Nachname, Klasse
- E-Mail-Adresse (falls angegeben)
- Schwerpunkt 1 und Schwerpunkt 2
- Einwahl-Zeitpunkt

**Features:**

- Excel-kompatibel (UTF-8 BOM, Semikolon-Trennzeichen)
- Automatische Dateinamen mit Timestamp
- Vollständige Datenexportierung

## 🔧 Administration

### Schüler-Verwaltung

- **Live-Suche**: Sofortiges Filtern nach Namen, Klassen oder Kursen
- **Sortierbare Tabellen**: Klick auf Spaltenheader zum Sortieren
- **Schnell-Aktionen**: Ein-Klick-Löschung mit Sicherheitsabfrage
- **Batch-Operationen**: Alle Einwahlen auf einmal zurücksetzen

### System-Verwaltung

- **Einwahl-Toggle**: Schnelles Ein-/Ausschalten der Schülerregistrierung
- **Live-Statistiken**: Auto-refresh alle 30 Sekunden
- **Konfigurations-Validierung**: Syntax-Prüfung bei Eingaben
- **Passwort-Sicherheit**: Stärke-Indikator und sichere Hash-Algorithmen

## 🔒 Sicherheit

### Authentifizierung & Autorisierung

- **Session-basierte Authentifizierung** mit sicheren Cookies
- **Bcrypt-Passwort-Hashing** mit Salt
- **CSRF-Schutz** durch Session-Tokens
- **Input-Sanitization** gegen XSS und SQL-Injection

### Daten-Schutz

- **Prepared Statements** für alle Datenbankzugriffe
- **IP-Logging** für Audit-Trails
- **Sichere Headers** (.htaccess Konfiguration)
- **Minimale Berechtigungen** für Docker-Container

## 🚨 Troubleshooting

### Häufige Probleme

**Container starten nicht:**

```bash
# Ports prüfen
netstat -tulpn | grep :80
netstat -tulpn | grep :3306

# Logs einsehen
docker-compose logs web
docker-compose logs db
```

**Datenbank-Verbindung fehlgeschlagen:**

```bash
# Container-Status prüfen
docker-compose ps

# Datenbank-Logs
docker-compose logs db

# Container neu starten
docker-compose restart db
```

**Admin-Login funktioniert nicht:**

```bash
# In Container einloggen und Passwort zurücksetzen
docker-compose exec db mysql -u buea_user -p buea_einwahl

# Neuen Hash generieren (PHP)
docker-compose exec web php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
```

**Einwahl-Validierung schlägt fehl:**

- Browser-Cache leeren (Strg+F5)
- JavaScript-Konsole auf Fehler prüfen
- Schwerpunkt-Konfiguration validieren

### Performance-Optimierung

**Für größere Installationen:**

- Datenbankindizes für häufige Abfragen
- PHP OpCache aktivieren
- Nginx als Reverse Proxy mit Caching
- Container-Ressourcen entsprechend skalieren

## 🎯 Roadmap & Erweiterungen

### Geplante Features

- **E-Mail-Benachrichtigungen** bei erfolgreicher Einwahl
- **Wartelisten-Funktionalität** für überfüllte Kurse
- **Bulk-Import** von Schülerdaten via CSV
- **Erweiterte Reporting-Funktionen** mit Grafiken
- **Multi-Tenancy** für mehrere Schulen
- **API-Endpoints** für Dritt-System-Integration

### Anpassungsmöglichkeiten

- **Theming**: CSS-Variablen für Corporate Design
- **Mehrsprachigkeit**: Vorbereitete Lokalisierung
- **Custom Fields**: Erweiterbare Schülerdaten
- **Workflow-Engine**: Konfigurierbare Genehmigungsprozesse

## 📄 Lizenz

Diese Anwendung wurde speziell für Bildungseinrichtungen entwickelt und kann frei verwendet, angepasst und weiterentwickelt werden. Der Quellcode steht unter einer offenen Lizenz zur Verfügung.

## 🤝 Support & Community

Bei Fragen, Problemen oder Verbesserungsvorschlägen:

1. **Dokumentation prüfen**: Alle wichtigen Informationen sind hier dokumentiert
2. **Logs analysieren**: `docker-compose logs` gibt meist Aufschluss über Probleme
3. **Issues erstellen**: Detaillierte Problembeschreibung mit System-Informationen
4. **Community beitragen**: Pull Requests für Verbesserungen sind willkommen

---

**Entwickelt mit ❤️ für moderne Bildungseinrichtungen**
