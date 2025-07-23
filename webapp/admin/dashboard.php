<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BüA Einwahl</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="../assets/js/admin.js" defer></script>
</head>

<body class="dashboard-page">
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="header-actions">
            <span>Willkommen, <?= htmlspecialchars($_SESSION['admin_user']) ?></span>
            <a href="?logout=1" class="btn btn-secondary">Abmelden</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Einwahl-Status -->
            <div class="card">
                <h3>
                    Einwahl-Status
                    <span class="status-indicator <?= $einwahl_offen ? 'status-open' : 'status-closed' ?>">
                        <?= $einwahl_offen ? 'OFFEN' : 'GESCHLOSSEN' ?>
                    </span>
                </h3>

                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="toggle_einwahl">
                    <button type="submit" class="btn <?= $einwahl_offen ? 'btn-danger' : 'btn-success' ?>">
                        <?= $einwahl_offen ? 'Einwahl schließen' : 'Einwahl öffnen' ?>
                    </button>
                </form>

                <p style="margin-top: 1rem; color: #6c757d; font-size: 0.9rem;">
                    Gesamt: <?= count($einwahlen) ?> Einwahlen
                </p>
            </div>

            <!-- Statistiken -->
            <div class="card">
                <h3>Schwerpunkt-Statistiken</h3>
                <ul class="stats-list">
                    <?php foreach ($statistiken as $stat): ?>
                        <li class="stats-item">
                            <span class="stats-item-name"><?= htmlspecialchars($stat['name']) ?></span>
                            <div class="stats-item-data">
                                <span class="stats-count">
                                    <?= $stat['anzahl'] ?>/<?= $stat['max'] ?>
                                </span>
                                <div class="progress-bar">
                                    <div class="progress-fill <?= $stat['prozent'] >= 100 ? 'progress-full' : '' ?>"
                                        style="width: <?= min($stat['prozent'], 100) ?>%"></div>
                                </div>
                                <span class="progress-percentage">
                                    <?= $stat['prozent'] ?>%
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Export -->
            <div class="card">
                <h3>Daten-Export</h3>
                <form method="post">
                    <input type="hidden" name="action" value="csv_export">
                    <button type="submit" class="btn btn-primary">
                        📊 CSV-Export herunterladen
                    </button>
                </form>
                <p style="margin-top: 1rem; color: #6c757d; font-size: 0.9rem;">
                    Exportiert alle Einwahlen als CSV-Datei für Excel.
                </p>
            </div>

            <!-- Passwort ändern -->
            <div class="card">
                <h3>Passwort ändern</h3>
                <form method="post" id="passwordForm">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Aktuelles Passwort</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Neues Passwort</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Passwort bestätigen</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-warning">
                        🔒 Passwort ändern
                    </button>
                </form>
            </div>
        </div>

        <!-- Konfiguration -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3>Hinweistext-Konfiguration</h3>
            <div class="config-help">
                <h4>Anleitung:</h4>
                <p>Dieser Text wird über dem Einwahlformular angezeigt. Verwenden Sie:</p>
                <ul>
                    <li><code>• Text</code> für Aufzählungspunkte</li>
                    <li>Zeilenumbrüche für neue Absätze</li>
                    <li><strong>**Text**</strong> wird automatisch fett dargestellt</li>
                </ul>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="update_hinweistext">
                <div class="form-group">
                    <label for="hinweistext">Hinweistext für Schüler:</label>
                    <textarea id="hinweistext" name="hinweistext" style="min-height: 120px;" placeholder="• Erster Hinweis&#10;• Zweiter Hinweis&#10;&#10;Weitere Informationen..."><?= htmlspecialchars($hinweistext) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Hinweistext übernehmen</button>
            </form>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <h3>Klassen-Konfiguration</h3>
            <div class="config-help">
                <h4>Anleitung:</h4>
                <p>Geben Sie jede Klassenbezeichnung in eine separate Zeile ein. Beispiel:</p>
                <code>BüA-1A<br>BüA-1B<br>BüA-2A</code>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="update_klassen">
                <div class="form-group">
                    <label for="klassen_config">Klassenbezeichnungen (eine pro Zeile):</label>
                    <textarea id="klassen_config" name="klassen_config"><?= htmlspecialchars($klassen_config) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Klassen übernehmen</button>
            </form>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <h3>Schwerpunkt-Konfiguration</h3>
            <div class="config-help">
                <h4>Anleitung:</h4>
                <p><strong>Einzelne Schwerpunkte:</strong> <code>12,Informationstechnik</code></p>
                <p><strong>Pflicht-Kombinationen:</strong> <code>12,Metall;Elektrotechnik</code></p>
                <ul style="margin-top: 0.5rem;">
                    <li>Zahl vor dem Komma = maximale Teilnehmerzahl</li>
                    <li>Semikolon trennt Kombinationspartner</li>
                    <li>Eine Konfiguration pro Zeile</li>
                </ul>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="update_schwerpunkte">
                <div class="form-group">
                    <label for="schwerpunkte_config">Schwerpunkt-Konfiguration:</label>
                    <textarea id="schwerpunkte_config" name="schwerpunkte_config" placeholder="10,Kraftfahrzeugtechnik&#10;10,Informationstechnik&#10;10,Handel&#10;12,Metall;Elektrotechnik&#10;12,Verwaltung;Ernährung"><?= htmlspecialchars($schwerpunkte_config) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Schwerpunkte übernehmen</button>
            </form>
        </div>

        <!-- Einwahlen-Tabelle -->
        <div class="table-container">
            <h3 style="margin-bottom: 1rem;">Alle Einwahlen (<?= count($einwahlen) ?>)</h3>

            <?php if (empty($einwahlen)): ?>
                <div class="empty-state">
                    Noch keine Einwahlen vorhanden.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Klasse</th>
                            <th>E-Mail</th>
                            <th>Erstwunsch</th>
                            <th>Zweitwunsch</th>
                            <th>Einwahl am</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($einwahlen as $einwahl): ?>
                            <tr>
                                <td><?= htmlspecialchars($einwahl['vorname'] . ' ' . $einwahl['nachname']) ?></td>
                                <td><?= htmlspecialchars($einwahl['klasse']) ?></td>
                                <td><?= htmlspecialchars($einwahl['email'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($einwahl['erstwunsch']) ?></td>
                                <td><?= htmlspecialchars($einwahl['zweitwunsch'] ?: '-') ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($einwahl['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>