# BüA Schwerpunkt-Einwahl Webapp

Eine webbasierte Anwendung für die Einwahl von Schülern in Schwerpunktkurse der Schulform BüA (Berufsfachschule zum Übergang in Ausbildung).

## Features

### Schüler-Interface

- **Intuitive Einwahl**: Schüler können sich mit Vor- und Nachname, Klasse und optional E-Mail-Adresse einwählen
- **Dynamische Kursauswahl**: Zweitwunsch wird automatisch basierend auf dem Erstwunsch und den Kombinationsregeln gefiltert
- **Echtzeit-Kapazitätsanzeige**: Aktuelle Belegung der Kurse wird angezeigt (ohne Namensnennung)
- **Mehrfach-Einwahl-Schutz**: Verhindert mehrmalige Einwahlen derselben Person

### Admin-Interface

- **Dashboard mit Statistiken**: Übersicht über alle Einwahlen und Kursbelegungen
- **Flexible Kurskonfiguration**: Schwerpunkte und deren Kombinationen können dynamisch angepasst werden
- **Klassenverwaltung**: Klassenbezeichnungen können frei konfiguriert werden
- **Ein-/Ausschalten der Einwahl**: Einwahl kann jederzeit geöffnet oder geschlossen werden
- **CSV-Export**: Alle Einwahlen können als Excel-kompatible CSV-Datei exportiert werden

### Technische Features

- **Docker-basierte Bereitstellung**: Einfache Installation und Wartung
- **Concurrent-Access**: Mehrere Schüler können gleichzeitig einwählen
- **Responsive Design**: Funktioniert auf Desktop und mobilen Geräten
- **Sichere Admin-Authentifizierung**: Passwort-geschützter Admin-Bereich

## Installation

### Voraussetzungen

- Docker
- Docker Compose

### Setup

1. **Repository klonen oder Dateien erstellen**:

   ```bash
   mkdir buea-einwahl
   cd buea-einwahl
   ```

2. **Ordnerstruktur erstellen**:

   ```
   buea-einwahl/
   ├── docker-compose.yml
   ├── webapp/
   │   ├── index.php
   │   ├── includes/
   │   │   ├── Database.php
   │   │   └── EinwahlModel.php
   │   └── admin/
   │       └── index.php
   ├── sql/
   │   └── init.sql
   └── php-config/
       └── php.ini
   ```

3. **Docker Container starten**:

   ```bash
   docker-compose up -d
   ```

4. **Erste Einrichtung**:
   - Webapp ist unter `http://localhost` erreichbar
   - Admin-Bereich: `http://localhost/admin/`
   - Standard-Login: `admin` / `admin123`

## Konfiguration

### Schwerpunkt-Konfiguration

Im Admin-Bereich können Schwerpunkte über ein Textfeld konfiguriert werden. Format pro Zeile:

**Einzelne Schwerpunkte:**

```
10,Kraftfahrzeugtechnik
10,Informationstechnik
10,Handel
```

**Pflicht-Kombinationen:**

```
12,Metall;Elektrotechnik
12,Verwaltung;Ernährung
```

- Zahl vor dem Komma = maximale Teilnehmerzahl
- Semikolon trennt Kombinationspartner
- Eine Konfiguration pro Zeile

### Klassen-Konfiguration

Klassenbezeichnungen werden zeilenweise eingegeben:

```
BüA-1A
BüA-1B
BüA-1C
BüA-2A
```

### Standard-Einstellungen

Die Anwendung wird mit folgenden Standard-Schwerpunkten ausgeliefert:

- Kraftfahrzeugtechnik (10 Plätze)
- Informationstechnik (10 Plätze)
- Metall + Elektro (je 10 Plätze, nur in Kombination)
- Handel (10 Plätze)
- Verwaltung + Ernährung (je 10 Plätze, nur in Kombination)

Standard-Klassen: BüA-1A bis BüA-1D

## Kombinationsregeln

Das System unterstützt verschiedene Kombinationsregeln:

1. **Freie Kombination**: Informationstechnik, Kraftfahrzeugtechnik und Handel können beliebig kombiniert werden
2. **Pflicht-Kombinationen**: Bestimmte Schwerpunkte können nur zusammen gewählt werden (z.B. Metall + Elektro)
3. **Ausschluss**: Gleiche Schwerpunkte können nicht als Erst- und Zweitwunsch gewählt werden

## Datenbank-Schema

### Haupttabellen

- `schwerpunkte`: Verfügbare Schwerpunktkurse mit Kapazitäten und Kombinationsregeln
- `klassen`: Verfügbare Klassenbezeichnungen
- `einwahlen`: Schülereinwahlen mit Erst- und Zweitwunsch
- `admin_users`: Admin-Benutzer für Backend-Zugang
- `konfiguration`: Systemkonfiguration

## Sicherheit

- **Admin-Authentifizierung**: Passwort-geschützter Admin-Bereich
- **SQL-Injection-Schutz**: Prepared Statements
- **Session-Management**: Sichere Session-Verwaltung
- **Input-Validierung**: Alle Eingaben werden validiert und sanitized

## CSV-Export

Der CSV-Export enthält folgende Spalten:

- Vorname
- Nachname
- Klasse
- E-Mail
- Erstwunsch
- Zweitwunsch
- Einwahl-Zeitpunkt

Die Datei ist Excel-kompatibel (UTF-8 BOM, Semikolon-getrennt).

## Wartung

### Logs einsehen

```bash
docker-compose logs web
docker-compose logs db
```

### Datenbank-Backup

```bash
docker exec buea_db mysqldump -u buea_user -p buea_einwahl > backup.sql
```

### Updates

```bash
docker-compose down
docker-compose pull
docker-compose up -d
```

## Produktions-Deployment

Für den Produktionseinsatz sollten folgende Punkte beachtet werden:

1. **Passwörter ändern**: Alle Standard-Passwörter in der docker-compose.yml ändern
2. **Reverse Proxy**: Nginx oder Apache als Reverse Proxy mit SSL/TLS
3. **Backup-Strategie**: Regelmäßige Datenbank-Backups einrichten
4. **Monitoring**: Log-Monitoring und Health-Checks implementieren
5. **Resource-Limits**: Docker Container-Ressourcen begrenzen

### Beispiel Reverse Proxy (Nginx)

```nginx
server {
    listen 443 ssl;
    server_name ihre-domain.de;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## Troubleshooting

### Häufige Probleme

1. **Container startet nicht**:

   - Ports 80 und 3306 bereits belegt prüfen
   - Docker und Docker Compose Version prüfen

2. **Datenbank-Verbindung fehlgeschlagen**:

   - Container-Status prüfen: `docker-compose ps`
   - Logs einsehen: `docker-compose logs db`

3. **Admin-Login funktioniert nicht**:

   - Standard-Passwort: `admin123`
   - Datenbank-Initialisierung prüfen

4. **Einwahl-Validierung schlägt fehl**:
   - JavaScript-Fehler in Browser-Konsole prüfen
   - Schwerpunkt-Konfiguration validieren

## Support

Bei Problemen oder Fragen zur Anwendung:

1. Logs überprüfen
2. Datenbank-Konsistenz prüfen
3. Browser-Cache leeren
4. Konfiguration validieren

## Lizenz

Diese Anwendung wurde für Bildungseinrichtungen entwickelt und kann frei verwendet und angepasst werden.
