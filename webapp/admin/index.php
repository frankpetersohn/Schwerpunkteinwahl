<?php
session_start();
require_once '../includes/Database.php';

// Einfache Admin-Authentifizierung
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
        $db = new Database();
        $admin = $db->fetchOne(
            "SELECT * FROM admin_users WHERE username = ?",
            [$_POST['username']]
        );

        if ($admin && password_verify($_POST['password'], $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $admin['username'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = "Ungültige Anmeldedaten";
        }
    }

    // Login-Formular anzeigen
    include 'login.php';
    exit;
}

// Admin ist eingeloggt - Dashboard anzeigen
require_once '../includes/EinwahlModel.php';
$model = new EinwahlModel();

$message = '';
$error = '';

// Aktionen verarbeiten
if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'toggle_einwahl':
                    $aktueller_status = $model->getKonfiguration('einwahl_offen');
                    $neuer_status = $aktueller_status === '1' ? '0' : '1';
                    $model->setKonfiguration('einwahl_offen', $neuer_status);
                    $message = 'Einwahl-Status wurde geändert.';
                    break;

                case 'update_schwerpunkte':
                    $model->updateSchwerpunkteConfig($_POST['schwerpunkte_config']);
                    $message = 'Schwerpunkte wurden aktualisiert.';
                    break;

                case 'update_klassen':
                    $model->updateKlassenConfig($_POST['klassen_config']);
                    $message = 'Klassen wurden übernommen.';
                    break;

                case 'update_hinweistext':
                    $model->setKonfiguration('hinweistext', $_POST['hinweistext']);
                    $message = 'Hinweistext wurde aktualisiert.';
                    break;

                case 'csv_export':
                    // CSV-Export
                    $einwahlen = $model->getAlleEinwahlen();

                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="buea_einwahlen_' . date('Y-m-d_H-i-s') . '.csv"');

                    $output = fopen('php://output', 'w');

                    // BOM für korrekte Umlaute in Excel
                    fwrite($output, "\xEF\xBB\xBF");

                    // Header
                    fputcsv($output, [
                        'Vorname',
                        'Nachname',
                        'Klasse',
                        'Email',
                        'Erstwunsch',
                        'Zweitwunsch',
                        'Einwahl am'
                    ], ';');

                    // Daten
                    foreach ($einwahlen as $einwahl) {
                        fputcsv($output, [
                            $einwahl['vorname'],
                            $einwahl['nachname'],
                            $einwahl['klasse'],
                            $einwahl['email'] ?: '',
                            $einwahl['erstwunsch'],
                            $einwahl['zweitwunsch'] ?: '',
                            $einwahl['created_at']
                        ], ';');
                    }

                    fclose($output);
                    exit;

                case 'change_password':
                    // Passwort ändern
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];

                    // Validierung
                    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                        throw new Exception("Bitte füllen Sie alle Felder aus.");
                    }

                    if ($new_password !== $confirm_password) {
                        throw new Exception("Die neuen Passwörter stimmen nicht überein.");
                    }

                    if (strlen($new_password) < 6) {
                        throw new Exception("Das neue Passwort muss mindestens 6 Zeichen lang sein.");
                    }

                    // Aktuelles Passwort prüfen
                    $db = new Database();
                    $admin = $db->fetchOne(
                        "SELECT password_hash FROM admin_users WHERE username = ?",
                        [$_SESSION['admin_user']]
                    );

                    if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
                        throw new Exception("Das aktuelle Passwort ist nicht korrekt.");
                    }

                    // Neues Passwort speichern
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->query(
                        "UPDATE admin_users SET password_hash = ? WHERE username = ?",
                        [$new_password_hash, $_SESSION['admin_user']]
                    );

                    $message = 'Passwort wurde erfolgreich geändert.';
                    break;

                case 'delete_all_einwahlen':
                    // Alle Einwahlen löschen
                    if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] !== 'ALLE_LOESCHEN') {
                        throw new Exception("Bestätigung fehlt. Geben Sie 'ALLE_LOESCHEN' ein.");
                    }

                    $db = new Database();
                    $deleted_count = $db->fetchOne("SELECT COUNT(*) as count FROM einwahlen")['count'];
                    $db->query("DELETE FROM einwahlen");

                    $message = "Alle {$deleted_count} Einwahlen wurden gelöscht.";
                    break;

                case 'delete_einzelne_einwahl':
                    // Einzelne Einwahl löschen
                    $einwahl_id = intval($_POST['einwahl_id']);
                    if (!$einwahl_id) {
                        throw new Exception("Ungültige Einwahl-ID.");
                    }

                    $db = new Database();
                    $einwahl = $db->fetchOne(
                        "SELECT vorname, nachname FROM einwahlen WHERE id = ?",
                        [$einwahl_id]
                    );

                    if (!$einwahl) {
                        throw new Exception("Einwahl nicht gefunden.");
                    }

                    $db->query("DELETE FROM einwahlen WHERE id = ?", [$einwahl_id]);

                    $message = "Einwahl von {$einwahl['vorname']} {$einwahl['nachname']} wurde gelöscht.";
                    break;

                case 'update_ueberschrift':
                    $model->setKonfiguration('form_ueberschrift', $_POST['form_ueberschrift']);
                    $message = 'Überschrift wurde aktualisiert.';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../');
    exit;
}

$einwahl_offen = $model->istEinwahlOffen();
$einwahlen = $model->getAlleEinwahlen();
$schwerpunkte = $model->getSchwerpunkte();
$schwerpunkte_config = $model->getKonfiguration('schwerpunkte_config');
$klassen_config = $model->getKonfiguration('klassen_config');
$hinweistext = $model->getKonfiguration('hinweistext');
$form_ueberschrift = $model->getKonfiguration('form_ueberschrift');

// Statistiken berechnen
$statistiken = [];
foreach ($schwerpunkte as $sp) {
    $anzahl = $model->getTeilnehmeranzahl($sp['id']);
    $statistiken[] = [
        'name' => $sp['name'],
        'anzahl' => $anzahl,
        'max' => $sp['max_teilnehmer'],
        'prozent' => $sp['max_teilnehmer'] > 0 ? round(($anzahl / $sp['max_teilnehmer']) * 100, 1) : 0
    ];
}

// Dashboard anzeigen
include 'dashboard.php';
