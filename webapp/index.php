<?php
session_start();
require_once 'includes/EinwahlModel.php';

$model = new EinwahlModel();
$message = '';
$error = '';

// Formular verarbeiten
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'einwahl') {
    try {
        $daten = [
            'vorname' => trim($_POST['vorname']),
            'nachname' => trim($_POST['nachname']),
            'klasse_id' => intval($_POST['klasse_id']),
            'email' => trim($_POST['email']),
            'erstwunsch_id' => intval($_POST['erstwunsch_id']),
            'zweitwunsch_id' => intval($_POST['zweitwunsch_id']) ?: null
        ];

        // Validierung
        if (empty($daten['vorname']) || empty($daten['nachname']) || !$daten['klasse_id'] || !$daten['erstwunsch_id']) {
            throw new Exception("Bitte füllen Sie alle Pflichtfelder aus.");
        }

        $model->speichereEinwahl($daten);
        $message = "Einwahl erfolgreich gespeichert!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$schwerpunkte = $model->getSchwerpunkte();
$klassen = $model->getKlassen();
$einwahl_offen = $model->istEinwahlOffen();
$hinweistext = $model->getKonfiguration('hinweistext');

// Teilnehmeranzahl für jeden Schwerpunkt ermitteln
$teilnehmer_anzahl = [];
foreach ($schwerpunkte as $sp) {
    $teilnehmer_anzahl[$sp['id']] = $model->getTeilnehmeranzahl($sp['id']);
}

// Daten für JavaScript vorbereiten
$js_schwerpunkte = json_encode($schwerpunkte);
$js_teilnehmer_anzahl = json_encode($teilnehmer_anzahl);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BüA Schwerpunkt-Einwahl</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>
    <div class="container">
        <h1>BüA Schwerpunkt-Einwahl</h1>

        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$einwahl_offen): ?>
            <div class="closed-notice">
                <h2>Die Einwahl ist derzeit geschlossen</h2>
                <p>Bitte wenden Sie sich an Ihre Lehrkraft.</p>
            </div>
        <?php else: ?>
            <?php if ($hinweistext): ?>
                <div class="combination-info">
                    <h3>Hinweise zur Kurswahl:</h3>
                    <?php
                    // Hinweistext formatieren - Zeilenumbrüche und Bullet Points
                    $formatted_text = nl2br(htmlspecialchars($hinweistext));
                    $formatted_text = preg_replace('/^•\s*/m', '<li>', $formatted_text);
                    $formatted_text = preg_replace('/(<li>.*?)(<br \/>|$)/s', '$1</li>', $formatted_text);

                    if (strpos($formatted_text, '<li>') !== false) {
                        echo '<ul>' . $formatted_text . '</ul>';
                    } else {
                        echo '<p>' . $formatted_text . '</p>';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" id="einwahlForm">
                <input type="hidden" name="action" value="einwahl">

                <div class="form-group">
                    <label for="vorname">Vorname <span class="required">*</span></label>
                    <input type="text" id="vorname" name="vorname" required>
                </div>

                <div class="form-group">
                    <label for="nachname">Nachname <span class="required">*</span></label>
                    <input type="text" id="nachname" name="nachname" required>
                </div>

                <div class="form-group">
                    <label for="klasse_id">Klasse <span class="required">*</span></label>
                    <select id="klasse_id" name="klasse_id" required>
                        <option value="">Bitte wählen...</option>
                        <?php foreach ($klassen as $klasse): ?>
                            <option value="<?= $klasse['id'] ?>"><?= htmlspecialchars($klasse['bezeichnung']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">E-Mail (optional)</label>
                    <input type="email" id="email" name="email">
                </div>

                <div class="form-group">
                    <label for="erstwunsch_id">Schwerpunkt 1 <span class="required">*</span></label>
                    <select id="erstwunsch_id" name="erstwunsch_id" required>
                        <option value="">Bitte wählen...</option>
                        <?php foreach ($schwerpunkte as $sp): ?>
                            <?php
                            $aktuell = $teilnehmer_anzahl[$sp['id']];
                            $max = $sp['max_teilnehmer'];
                            $ist_voll = $aktuell >= $max;
                            ?>
                            <option value="<?= $sp['id'] ?>" <?= $ist_voll ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($sp['name']) ?> (<?= $aktuell ?>/<?= $max ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="course-info">
                        Wählen Sie Ihren ersten Schwerpunkt. Rote Kurse sind bereits voll.
                    </div>
                </div>

                <div class="form-group">
                    <label for="zweitwunsch_id">Schwerpunkt 2 <span class="required">*</span></label>
                    <select id="zweitwunsch_id" name="zweitwunsch_id" required disabled>
                        <option value="">Erst Schwerpunkt 1 wählen...</option>
                    </select>
                    <div class="course-info">
                        Wird automatisch basierend auf Ihrem ersten Schwerpunkt gefiltert.
                    </div>
                </div>

                <button type="submit">Einwahl speichern</button>
            </form>
        <?php endif; ?>

        <div class="admin-link">
            <a href="admin/">Admin-Bereich</a>
        </div>
    </div>

    <script>
        // Daten für JavaScript
        window.schwerpunkte = <?= $js_schwerpunkte ?>;
        window.teilnehmerAnzahl = <?= $js_teilnehmer_anzahl ?>;
    </script>
    <script src="assets/js/einwahl.js"></script>
</body>

</html>