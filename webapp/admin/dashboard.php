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

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="overview">Übersicht</button>
            <button class="tab-btn" data-tab="students">Schüler-Verwaltung</button>
            <button class="tab-btn" data-tab="config">Konfiguration</button>
            <button class="tab-btn" data-tab="account">Account</button>
        </div>

        <!-- Tab: Übersicht -->
        <div class="tab-content active" id="tab-overview">
            <div class="dashboard-grid">
                <!-- Einwahl-Status -->
                <div class="card status-card">
                    <div class="card-header">
                        <h3>Einwahl-Status</h3>
                        <span class="status-indicator <?= $einwahl_offen ? 'status-open' : 'status-closed' ?>">
                            <?= $einwahl_offen ? 'OFFEN' : 'GESCHLOSSEN' ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="status-info">
                            <div class="status-metric">
                                <span class="metric-number"><?= count($einwahlen) ?></span>
                                <span class="metric-label">Einwahlen gesamt</span>
                            </div>
                        </div>

                        <form method="post" class="status-form">
                            <input type="hidden" name="action" value="toggle_einwahl">
                            <button type="submit" class="btn btn-lg <?= $einwahl_offen ? 'btn-danger' : 'btn-success' ?>">
                                <?= $einwahl_offen ? '🔒 Einwahl schließen' : '🔓 Einwahl öffnen' ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Statistiken -->
                <div class="card stats-card">
                    <div class="card-header">
                        <h3>Schwerpunkt-Statistiken</h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <?php foreach ($statistiken as $stat): ?>
                                <div class="stat-item">
                                    <div class="stat-header">
                                        <span class="stat-name"><?= htmlspecialchars($stat['name']) ?></span>
                                        <span class="stat-count"><?= $stat['anzahl'] ?>/<?= $stat['max'] ?></span>
                                    </div>
                                    <div class="stat-progress">
                                        <div class="progress-bar-modern">
                                            <div class="progress-fill-modern <?= $stat['prozent'] >= 100 ? 'progress-full' : '' ?>"
                                                style="width: <?= min($stat['prozent'], 100) ?>%"></div>
                                        </div>
                                        <span class="stat-percentage"><?= $stat['prozent'] ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card actions-card">
                    <div class="card-header">
                        <h3>Schnellaktionen</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <form method="post" class="action-item">
                                <input type="hidden" name="action" value="csv_export">
                                <button type="submit" class="btn btn-primary btn-block">
                                    📊 CSV-Export
                                </button>
                                <small>Alle Einwahlen exportieren</small>
                            </form>

                            <div class="action-item danger-zone">
                                <button type="button" class="btn btn-danger btn-block" id="deleteAllBtn">
                                    🗑️ Alle Einwahlen löschen
                                </button>
                                <small>⚠️ Vorsicht: Nicht rückgängig machbar!</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Schüler-Verwaltung -->
        <div class="tab-content" id="tab-students">
            <div class="card table-card">
                <div class="card-header">
                    <h3>Alle Einwahlen (<?= count($einwahlen) ?>)</h3>
                    <div class="table-actions">
                        <input type="text" id="student-search" placeholder="Schüler suchen..." class="search-input">
                    </div>
                </div>

                <div class="card-body">
                    <?php if (empty($einwahlen)): ?>
                        <div class="empty-state">
                            Noch keine Einwahlen vorhanden.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="students-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Klasse</th>
                                        <th>E-Mail</th>
                                        <th>Schwerpunkt 1</th>
                                        <th>Schwerpunkt 2</th>
                                        <th>Einwahl am</th>
                                        <th width="100">Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($einwahlen as $einwahl): ?>
                                        <tr data-id="<?= $einwahl['id'] ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($einwahl['vorname'] . ' ' . $einwahl['nachname']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?= htmlspecialchars($einwahl['klasse']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($einwahl['email'] ?: '-') ?></td>
                                            <td>
                                                <span class="badge badge-primary"><?= htmlspecialchars($einwahl['erstwunsch']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($einwahl['zweitwunsch']): ?>
                                                    <span class="badge badge-primary"><?= htmlspecialchars($einwahl['zweitwunsch']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('d.m.Y H:i', strtotime($einwahl['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger delete-student-btn"
                                                    data-id="<?= $einwahl['id'] ?>"
                                                    data-name="<?= htmlspecialchars($einwahl['vorname'] . ' ' . $einwahl['nachname']) ?>">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Konfiguration -->
        <div class="tab-content" id="tab-config">
            <div class="config-grid">
                <!-- Hinweistext -->
                <div class="card">
                    <div class="card-header">
                        <h3>Hinweistext für Schüler</h3>
                    </div>
                    <div class="card-body">
                        <div class="config-help">
                            <p>Dieser Text wird über dem Einwahlformular angezeigt.</p>
                            <p><strong>Formatierung:</strong> <code>• Text</code> für Aufzählungen</p>
                        </div>

                        <form method="post">
                            <input type="hidden" name="action" value="update_hinweistext">
                            <div class="form-group">
                                <textarea id="hinweistext" name="hinweistext" rows="6"
                                    placeholder="• Erster Hinweis&#10;• Zweiter Hinweis"><?= htmlspecialchars($hinweistext) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Speichern</button>
                        </form>
                    </div>
                </div>

                <!-- Klassen -->
                <div class="card">
                    <div class="card-header">
                        <h3>Klassen-Konfiguration</h3>
                    </div>
                    <div class="card-body">
                        <div class="config-help">
                            <p>Eine Klassenbezeichnung pro Zeile eingeben.</p>
                        </div>

                        <form method="post">
                            <input type="hidden" name="action" value="update_klassen">
                            <div class="form-group">
                                <textarea id="klassen_config" name="klassen_config" rows="6"><?= htmlspecialchars($klassen_config) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Speichern</button>
                        </form>
                    </div>
                </div>

                <!-- Schwerpunkte -->
                <div class="card config-card-full">
                    <div class="card-header">
                        <h3>Schwerpunkt-Konfiguration</h3>
                    </div>
                    <div class="card-body">
                        <div class="config-help">
                            <p><strong>Format:</strong></p>
                            <ul>
                                <li><code>12,Informationstechnik</code> für einzelne Schwerpunkte</li>
                                <li><code>12,Metall;Elektrotechnik</code> für Pflicht-Kombinationen</li>
                            </ul>
                        </div>

                        <form method="post">
                            <input type="hidden" name="action" value="update_schwerpunkte">
                            <div class="form-group">
                                <textarea id="schwerpunkte_config" name="schwerpunkte_config" rows="8"
                                    placeholder="10,Kraftfahrzeugtechnik&#10;10,Informationstechnik&#10;12,Metall;Elektrotechnik"><?= htmlspecialchars($schwerpunkte_config) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Speichern</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Account -->
        <div class="tab-content" id="tab-account">
            <div class="account-grid">
                <div class="card">
                    <div class="card-header">
                        <h3>Passwort ändern</h3>
                    </div>
                    <div class="card-body">
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

                            <button type="submit" class="btn btn-primary">
                                🔒 Passwort ändern
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal für "Alle löschen" -->
    <div id="deleteAllModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>⚠️ Alle Einwahlen löschen</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Diese Aktion kann nicht rückgängig gemacht werden!</strong></p>
                <p>Alle <?= count($einwahlen) ?> Einwahlen werden unwiderruflich gelöscht.</p>

                <form method="post" id="deleteAllForm">
                    <input type="hidden" name="action" value="delete_all_einwahlen">
                    <div class="form-group">
                        <label for="confirm_delete">Geben Sie <code>ALLE_LOESCHEN</code> ein:</label>
                        <input type="text" id="confirm_delete" name="confirm_delete" required
                            placeholder="ALLE_LOESCHEN" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Abbrechen</button>
                <button type="submit" form="deleteAllForm" class="btn btn-danger">Alle löschen</button>
            </div>
        </div>
    </div>

    <!-- Form für einzelne Löschung (versteckt) -->
    <form id="deleteStudentForm" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_einzelne_einwahl">
        <input type="hidden" name="einwahl_id" id="delete_einwahl_id">
    </form>
</body>

</html>