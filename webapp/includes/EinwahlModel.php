<?php

require_once 'Database.php';

class EinwahlModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getSchwerpunkte()
    {
        return $this->db->fetchAll("SELECT * FROM schwerpunkte WHERE aktiv = 1 ORDER BY name");
    }

    public function getKlassen()
    {
        return $this->db->fetchAll("SELECT * FROM klassen WHERE aktiv = 1 ORDER BY bezeichnung");
    }

    public function getTeilnehmeranzahl($schwerpunkt_id)
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as anzahl FROM einwahlen 
             WHERE (erstwunsch_id = ? OR zweitwunsch_id = ?)",
            [$schwerpunkt_id, $schwerpunkt_id]
        );
        return $result['anzahl'] ?? 0;
    }

    public function istEinwahlOffen()
    {
        $config = $this->db->fetchOne(
            "SELECT wert FROM konfiguration WHERE schluessel = 'einwahl_offen'"
        );
        return ($config['wert'] ?? '0') === '1';
    }

    public function studentExistiert($vorname, $nachname, $klasse_id)
    {
        $result = $this->db->fetchOne(
            "SELECT id FROM einwahlen WHERE vorname = ? AND nachname = ? AND klasse_id = ?",
            [$vorname, $nachname, $klasse_id]
        );
        return !empty($result);
    }

    public function kannEinwählen($schwerpunkt_id)
    {
        $schwerpunkt = $this->db->fetchOne("SELECT * FROM schwerpunkte WHERE id = ?", [$schwerpunkt_id]);
        if (!$schwerpunkt) return false;

        $aktuelle_anzahl = $this->getTeilnehmeranzahl($schwerpunkt_id);
        return $aktuelle_anzahl < $schwerpunkt['max_teilnehmer'];
    }

    public function sindKombinierbar($schwerpunkt1_id, $schwerpunkt2_id)
    {
        $schwerpunkt1 = $this->db->fetchOne("SELECT * FROM schwerpunkte WHERE id = ?", [$schwerpunkt1_id]);
        $schwerpunkt2 = $this->db->fetchOne("SELECT * FROM schwerpunkte WHERE id = ?", [$schwerpunkt2_id]);

        if (!$schwerpunkt1 || !$schwerpunkt2) return false;

        // Gleiche Schwerpunkte nicht kombinierbar
        if ($schwerpunkt1_id == $schwerpunkt2_id) return false;

        // Prüfung auf Pflicht-Kombinationen
        if ($schwerpunkt1['kombination_mit'] && $schwerpunkt1['kombination_mit'] != $schwerpunkt2_id) {
            return false;
        }

        if ($schwerpunkt2['kombination_mit'] && $schwerpunkt2['kombination_mit'] != $schwerpunkt1_id) {
            return false;
        }

        return true;
    }

    public function speichereEinwahl($daten)
    {
        // Validierung
        if (!$this->istEinwahlOffen()) {
            throw new Exception("Die Einwahl ist derzeit geschlossen.");
        }

        if ($this->studentExistiert($daten['vorname'], $daten['nachname'], $daten['klasse_id'])) {
            throw new Exception("Dieser Schüler ist bereits eingewählt.");
        }

        if (!$this->kannEinwählen($daten['erstwunsch_id'])) {
            throw new Exception("Der Erstwunsch-Kurs ist bereits voll.");
        }

        if ($daten['zweitwunsch_id'] && !$this->kannEinwählen($daten['zweitwunsch_id'])) {
            throw new Exception("Der Zweitwunsch-Kurs ist bereits voll.");
        }

        if ($daten['zweitwunsch_id'] && !$this->sindKombinierbar($daten['erstwunsch_id'], $daten['zweitwunsch_id'])) {
            throw new Exception("Diese Schwerpunkt-Kombination ist nicht erlaubt.");
        }

        // Einwahl speichern
        $sql = "INSERT INTO einwahlen (vorname, nachname, klasse_id, email, erstwunsch_id, zweitwunsch_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $daten['vorname'],
            $daten['nachname'],
            $daten['klasse_id'],
            $daten['email'] ?: null,
            $daten['erstwunsch_id'],
            $daten['zweitwunsch_id'] ?: null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        return true;
    }

    public function getAlleEinwahlen()
    {
        return $this->db->fetchAll("
            SELECT e.*, k.bezeichnung as klasse, 
                   s1.name as erstwunsch, s2.name as zweitwunsch
            FROM einwahlen e 
            JOIN klassen k ON e.klasse_id = k.id
            JOIN schwerpunkte s1 ON e.erstwunsch_id = s1.id
            LEFT JOIN schwerpunkte s2 ON e.zweitwunsch_id = s2.id
            ORDER BY e.created_at DESC
        ");
    }

    public function updateSchwerpunkteConfig($config_text)
    {
        // Zuerst alle Schwerpunkte deaktivieren
        $this->db->query("UPDATE schwerpunkte SET aktiv = 0");

        $lines = explode("\n", trim($config_text));
        $schwerpunkt_map = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Format: max_teilnehmer,Name oder max_teilnehmer,Name1;Name2
            $parts = explode(',', $line, 2);
            if (count($parts) != 2) continue;

            $max_teilnehmer = intval(trim($parts[0]));
            $schwerpunkte_str = trim($parts[1]);

            if (strpos($schwerpunkte_str, ';') !== false) {
                // Kombination
                $kombination = explode(';', $schwerpunkte_str);
                $name1 = trim($kombination[0]);
                $name2 = trim($kombination[1]);

                // Ersten Schwerpunkt erstellen/aktualisieren
                $id1 = $this->createOrUpdateSchwerpunkt($name1, $max_teilnehmer, null);
                $id2 = $this->createOrUpdateSchwerpunkt($name2, $max_teilnehmer, null);

                // Kombination setzen
                $this->db->query("UPDATE schwerpunkte SET kombination_mit = ? WHERE id = ?", [$id2, $id1]);
                $this->db->query("UPDATE schwerpunkte SET kombination_mit = ? WHERE id = ?", [$id1, $id2]);
            } else {
                // Einzelner Schwerpunkt
                $this->createOrUpdateSchwerpunkt($schwerpunkte_str, $max_teilnehmer, null);
            }
        }

        // Konfiguration in DB speichern
        $this->db->query(
            "UPDATE konfiguration SET wert = ? WHERE schluessel = 'schwerpunkte_config'",
            [$config_text]
        );
    }

    private function createOrUpdateSchwerpunkt($name, $max_teilnehmer, $kombination_mit)
    {
        // Prüfen ob Schwerpunkt bereits existiert
        $existing = $this->db->fetchOne("SELECT id FROM schwerpunkte WHERE name = ?", [$name]);

        if ($existing) {
            // Aktualisieren
            $this->db->query(
                "UPDATE schwerpunkte SET max_teilnehmer = ?, kombination_mit = ?, aktiv = 1 WHERE id = ?",
                [$max_teilnehmer, $kombination_mit, $existing['id']]
            );
            return $existing['id'];
        } else {
            // Neu erstellen
            $this->db->query(
                "INSERT INTO schwerpunkte (name, max_teilnehmer, kombination_mit, aktiv) VALUES (?, ?, ?, 1)",
                [$name, $max_teilnehmer, $kombination_mit]
            );
            return $this->db->lastInsertId();
        }
    }

    public function updateKlassenConfig($config_text)
    {
        // Zuerst alle Klassen deaktivieren
        $this->db->query("UPDATE klassen SET aktiv = 0");

        $lines = explode("\n", trim($config_text));

        foreach ($lines as $line) {
            $bezeichnung = trim($line);
            if (empty($bezeichnung)) continue;

            // Prüfen ob Klasse bereits existiert
            $existing = $this->db->fetchOne("SELECT id FROM klassen WHERE bezeichnung = ?", [$bezeichnung]);

            if ($existing) {
                // Aktivieren
                $this->db->query("UPDATE klassen SET aktiv = 1 WHERE id = ?", [$existing['id']]);
            } else {
                // Neu erstellen
                $this->db->query("INSERT INTO klassen (bezeichnung, aktiv) VALUES (?, 1)", [$bezeichnung]);
            }
        }

        // Konfiguration in DB speichern
        $this->db->query(
            "UPDATE konfiguration SET wert = ? WHERE schluessel = 'klassen_config'",
            [$config_text]
        );
    }

    public function getKonfiguration($schluessel)
    {
        $result = $this->db->fetchOne("SELECT wert FROM konfiguration WHERE schluessel = ?", [$schluessel]);
        return $result['wert'] ?? '';
    }

    public function setKonfiguration($schluessel, $wert)
    {
        $this->db->query(
            "UPDATE konfiguration SET wert = ? WHERE schluessel = ?",
            [$wert, $schluessel]
        );
    }
}
