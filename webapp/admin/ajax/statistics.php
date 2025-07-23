<?php

/**
 * AJAX-Endpoint für Statistik-Updates
 */

session_start();

// Admin-Authentifizierung prüfen
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Nicht autorisiert']);
    exit;
}

require_once '../../includes/EinwahlModel.php';

try {
    $model = new EinwahlModel();

    // Aktuelle Statistiken abrufen
    $schwerpunkte = $model->getSchwerpunkte();
    $einwahlen = $model->getAlleEinwahlen();

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

    // Antwort zusammenstellen
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'statistiken' => $statistiken,
        'gesamt_einwahlen' => count($einwahlen),
        'einwahl_offen' => $model->istEinwahlOffen()
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Laden der Statistiken',
        'message' => $e->getMessage()
    ]);
}
