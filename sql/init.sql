-- Datenbank-Initialisierung für BüA-Einwahl

CREATE TABLE IF NOT EXISTS schwerpunkte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    max_teilnehmer INT NOT NULL DEFAULT 10,
    kombination_mit INT NULL,
    aktiv BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    suffix VARCHAR(3) NULL
);

CREATE TABLE IF NOT EXISTS klassen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bezeichnung VARCHAR(50) NOT NULL UNIQUE,
    aktiv BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS einwahlen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vorname VARCHAR(100) NOT NULL,
    nachname VARCHAR(100) NOT NULL,
    klasse_id INT NOT NULL,
    email VARCHAR(255) NULL,
    erstwunsch_id INT NOT NULL,
    zweitwunsch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (klasse_id) REFERENCES klassen (id),
    FOREIGN KEY (erstwunsch_id) REFERENCES schwerpunkte (id),
    FOREIGN KEY (zweitwunsch_id) REFERENCES schwerpunkte (id),
    UNIQUE KEY unique_student (vorname, nachname, klasse_id)
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS konfiguration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schluessel VARCHAR(100) NOT NULL UNIQUE,
    wert TEXT,
    beschreibung TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Standard-Schwerpunkte einfügen
INSERT INTO
    schwerpunkte (
        name,
        max_teilnehmer,
        kombination_mit
    )
VALUES (
        'Kraftfahrzeugtechnik',
        10,
        NULL
    ),
    (
        'Informationstechnik',
        10,
        NULL
    ),
    ('Metall', 10, 4),
    ('Elektro', 10, 3),
    ('Handel', 10, NULL),
    ('Verwaltung', 10, 7),
    ('Ernährung', 10, 6);

-- Standard-Klassen einfügen
INSERT INTO
    klassen (bezeichnung)
VALUES ('BüA-1A'),
    ('BüA-1B'),
    ('BüA-1C'),
    ('BüA-1D');

-- Standard-Admin-User (Passwort: admin123)
INSERT INTO
    admin_users (username, password_hash)
VALUES (
        'admin',
        '$2y$10$3q1uc33uJPWGAFvZwDEjqendUOxipXo5wDrJnSQMOdy/02.mfrPZ2'
    );

-- Konfiguration für einwahl_offen
INSERT INTO
    konfiguration (
        schluessel,
        wert,
        beschreibung
    )
VALUES (
        'einwahl_offen',
        '1',
        'Bestimmt ob die Einwahl für Schüler geöffnet ist (1=offen, 0=geschlossen)'
    ),
    (
        'schwerpunkte_config',
        '',
        'Konfiguration der Schwerpunkte im Format: max_teilnehmer,Name oder max_teilnehmer,Name1;Name2'
    ),
    (
        'klassen_config',
        'BüA-1A\nBüA-1B\nBüA-1C\nBüA-1D',
        'Liste der verfügbaren Klassen, eine pro Zeile'
    ),
    (
        'hinweistext',
        'Wichtige Hinweise zur Kurswahl:\n• Metall und Elektro können nur in Kombination gewählt werden\n• Verwaltung und Ernährung können nur in Kombination gewählt werden\n• Informationstechnik, Kraftfahrzeugtechnik und Handel können frei kombiniert werden\n• Sie müssen zwei verschiedene Schwerpunkte wählen',
        'Hinweistext der über dem Einwahlformular angezeigt wird'
    ),
    (
        'form_ueberschrift',
        'BüA Schwerpunkt-Einwahl',
        'Überschrift des Einwahlformulars'
    ),
    (
        'suffix_halbierer',
        '0',
        'Aktiviert die Suffix-Funktion für gleiche Schwerpunkte (1=aktiviert, 0=deaktiviert)'
    );

-- Änderiungen für Branch flexcombi
-- Neue Spalte für flexible Kombinationen
ALTER TABLE schwerpunkte ADD COLUMN kombinationen TEXT;

ALTER TABLE schwerpunkte
ADD COLUMN kombination_typ ENUM('einzeln', 'fest', 'flexibel') DEFAULT 'einzeln';

-- Update bestehende Daten
UPDATE schwerpunkte
SET
    kombination_typ = 'fest'
WHERE
    kombination_mit IS NOT NULL;