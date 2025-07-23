-- Datenbank-Initialisierung für BüA-Einwahl

CREATE TABLE IF NOT EXISTS schwerpunkte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    max_teilnehmer INT NOT NULL DEFAULT 10,
    kombination_mit INT NULL,
    aktiv BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
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
    );