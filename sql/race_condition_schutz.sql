-- Race Condition Schutz für BüA-Einwahl
-- Führen Sie diese SQL-Befehle aus, um zusätzlichen Datenbankschutz zu aktivieren

-- 1. Index für bessere Performance bei Kapazitätsprüfungen
CREATE INDEX IF NOT EXISTS idx_einwahlen_schwerpunkte ON einwahlen (erstwunsch_id, zweitwunsch_id);

-- 2. Index für schnelle Duplikat-Prüfung
CREATE INDEX IF NOT EXISTS idx_einwahlen_student ON einwahlen (vorname, nachname, klasse_id);

-- 3. Trigger für automatische Kapazitätsprüfung (zusätzlicher Schutz)
DELIMITER / /

DROP TRIGGER IF EXISTS check_capacity_before_insert //

CREATE TRIGGER check_capacity_before_insert
    BEFORE INSERT ON einwahlen
    FOR EACH ROW
BEGIN
    DECLARE erst_current INT DEFAULT 0;
    DECLARE erst_max INT DEFAULT 0;
    DECLARE zweit_current INT DEFAULT 0;
    DECLARE zweit_max INT DEFAULT 0;
    
    -- Aktueller Stand und Maximum für Erstwunsch
    SELECT COUNT(*) INTO erst_current
    FROM einwahlen 
    WHERE erstwunsch_id = NEW.erstwunsch_id OR zweitwunsch_id = NEW.erstwunsch_id;
    
    SELECT max_teilnehmer INTO erst_max
    FROM schwerpunkte 
    WHERE id = NEW.erstwunsch_id;
    
    -- Prüfung Erstwunsch
    IF erst_current >= erst_max THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Kapazität für ersten Schwerpunkt überschritten';
    END IF;
    
    -- Falls Zweitwunsch vorhanden
    IF NEW.zweitwunsch_id IS NOT NULL THEN
        SELECT COUNT(*) INTO zweit_current
        FROM einwahlen 
        WHERE erstwunsch_id = NEW.zweitwunsch_id OR zweitwunsch_id = NEW.zweitwunsch_id;
        
        SELECT max_teilnehmer INTO zweit_max
        FROM schwerpunkte 
        WHERE id = NEW.zweitwunsch_id;
        
        -- Prüfung Zweitwunsch
        IF zweit_current >= zweit_max THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Kapazität für zweiten Schwerpunkt überschritten';
        END IF;
    END IF;
END //

DELIMITER;

-- 4. Unique Index für Doppel-Einwahl-Schutz (verstärkt)
CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_student_einwahl ON einwahlen (
    vorname (50),
    nachname (50),
    klasse_id
);

-- 5. Stored Procedure für atomare Einwahl (optional)
DELIMITER / /

DROP PROCEDURE IF EXISTS safe_einwahl_insert //

CREATE PROCEDURE safe_einwahl_insert(
    IN p_vorname VARCHAR(100),
    IN p_nachname VARCHAR(100), 
    IN p_klasse_id INT,
    IN p_email VARCHAR(255),
    IN p_erstwunsch_id INT,
    IN p_zweitwunsch_id INT,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Kapazitätsprüfung mit Locks
    SELECT id FROM schwerpunkte WHERE id = p_erstwunsch_id FOR UPDATE;
    IF p_zweitwunsch_id IS NOT NULL THEN
        SELECT id FROM schwerpunkte WHERE id = p_zweitwunsch_id FOR UPDATE;
    END IF;
    
    -- Einwahl durchführen
    INSERT INTO einwahlen (vorname, nachname, klasse_id, email, erstwunsch_id, zweitwunsch_id, ip_address, user_agent)
    VALUES (p_vorname, p_nachname, p_klasse_id, p_email, p_erstwunsch_id, p_zweitwunsch_id, p_ip_address, p_user_agent);
    
    COMMIT;
END //

DELIMITER;