/**
 * BüA Admin Dashboard - JavaScript für erweiterte Funktionalität
 */

class AdminDashboard {
  constructor() {
    this.init();
  }

  init() {
    this.initTabNavigation();
    this.initFormValidation();
    this.initConfirmDialogs();
    this.initAutoRefresh();
    this.initKeyboardShortcuts();
    this.initTableSorting();
    this.initPasswordForm();
    this.initStudentSearch();

    console.log("Admin Dashboard initialisiert");
  }

  /**
   * Tab-Navigation initialisieren
   */
  initTabNavigation() {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const targetTab = button.dataset.tab;

        // Alle Tabs deaktivieren
        tabButtons.forEach((btn) => btn.classList.remove("active"));
        tabContents.forEach((content) => content.classList.remove("active"));

        // Aktiven Tab aktivieren
        button.classList.add("active");
        document.getElementById(`tab-${targetTab}`).classList.add("active");

        // URL-Hash aktualisieren
        window.history.replaceState(null, null, `#${targetTab}`);
      });
    });

    // Tab aus URL-Hash laden
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(`tab-${hash}`)) {
      document.querySelector(`[data-tab="${hash}"]`).click();
    }
  }

  /**
   * Schüler-Suche initialisieren
   */
  initStudentSearch() {
    const searchInput = document.getElementById("student-search");
    const studentsTable = document.getElementById("students-table");

    if (!searchInput || !studentsTable) return;

    searchInput.addEventListener("input", (e) => {
      const searchTerm = e.target.value.toLowerCase();
      const rows = studentsTable.querySelectorAll("tbody tr");

      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    });

    // Event-Listener für Lösch-Buttons
    const deleteButtons = document.querySelectorAll(".delete-student-btn");
    deleteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const id = button.dataset.id;
        const name = button.dataset.name;
        this.deleteStudent(id, name);
      });
    });

    // Event-Listener für "Alle löschen" Button
    const deleteAllBtn = document.getElementById("deleteAllBtn");
    if (deleteAllBtn) {
      deleteAllBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.showDeleteAllModal();
      });
    }
  }

  /**
   * Modal für "Alle löschen" anzeigen
   */
  showDeleteAllModal() {
    console.log("showDeleteAllModal aufgerufen"); // Debug
    const modal = document.getElementById("deleteAllModal");
    if (modal) {
      modal.classList.add("show");

      // Focus auf Eingabefeld setzen
      setTimeout(() => {
        const input = document.getElementById("confirm_delete");
        if (input) {
          input.value = ""; // Feld leeren
          input.focus();
        }
      }, 100);
    }
  }

  /**
   * Modal für "Alle löschen" schließen
   */
  closeDeleteAllModal() {
    console.log("closeDeleteAllModal aufgerufen"); // Debug
    const modal = document.getElementById("deleteAllModal");
    if (modal) {
      modal.classList.remove("show");

      // Formular zurücksetzen
      const form = document.getElementById("deleteAllForm");
      if (form) form.reset();
    }
  }

  /**
   * Einzelnen Schüler löschen
   */
  deleteStudent(id, name) {
    if (
      confirm(
        `Möchten Sie die Einwahl von "${name}" wirklich löschen?\n\nDiese Aktion kann nicht rückgängig gemacht werden.`
      )
    ) {
      const form = document.getElementById("deleteStudentForm");
      const idInput = document.getElementById("delete_einwahl_id");

      if (form && idInput) {
        idInput.value = id;
        form.submit();
      }
    }
  }

  /**
   * Formular-Validierung für Konfiguration
   */
  initFormValidation() {
    // Schwerpunkte-Konfiguration validieren
    const schwerpunkteForm = document.querySelector(
      'form[action*="update_schwerpunkte"]'
    );
    const schwerpunkteTextarea = document.getElementById("schwerpunkte_config");

    if (schwerpunkteForm && schwerpunkteTextarea) {
      schwerpunkteForm.addEventListener("submit", (e) => {
        if (!this.validateSchwerpunkteConfig(schwerpunkteTextarea.value)) {
          e.preventDefault();
          this.showError(
            "Ungültige Schwerpunkt-Konfiguration. Bitte prüfen Sie das Format."
          );
        }
      });

      // Live-Validierung
      schwerpunkteTextarea.addEventListener("input", () => {
        this.validateSchwerpunkteConfigLive(schwerpunkteTextarea);
      });
    }

    // Klassen-Konfiguration validieren
    const klassenForm = document.querySelector(
      'form[action*="update_klassen"]'
    );
    const klassenTextarea = document.getElementById("klassen_config");

    if (klassenForm && klassenTextarea) {
      klassenForm.addEventListener("submit", (e) => {
        if (!this.validateKlassenConfig(klassenTextarea.value)) {
          e.preventDefault();
          this.showError(
            "Ungültige Klassen-Konfiguration. Mindestens eine Klasse muss angegeben werden."
          );
        }
      });
    }
    // Überschrift-Validierung
    const ueberschriftInput = document.getElementById("form_ueberschrift");
    if (ueberschriftInput) {
      ueberschriftInput.addEventListener("input", (e) => {
        if (e.target.value.length > 100) {
          this.showValidationError(
            e.target,
            "Überschrift zu lang (max. 100 Zeichen)"
          );
        } else {
          this.removeValidationError(e.target);
        }
      });
    }
  }

  /**
   * Validiert die Schwerpunkt-Konfiguration
   */
  validateSchwerpunkteConfig(config) {
    const lines = config
      .trim()
      .split("\n")
      .filter((line) => line.trim());

    if (lines.length === 0) {
      return false;
    }

    for (const line of lines) {
      const trimmedLine = line.trim();
      if (!trimmedLine) continue;

      // Format: Zahl,Name oder Zahl,Name1;Name2
      const parts = trimmedLine.split(",");
      if (parts.length !== 2) return false;

      const maxTeilnehmer = parseInt(parts[0].trim());
      if (isNaN(maxTeilnehmer) || maxTeilnehmer < 1 || maxTeilnehmer > 100) {
        return false;
      }

      const schwerpunktName = parts[1].trim();
      if (!schwerpunktName || schwerpunktName.length < 2) {
        return false;
      }
    }

    return true;
  }

  /**
   * Live-Validierung für Schwerpunkt-Konfiguration
   */
  validateSchwerpunkteConfigLive(textarea) {
    const isValid = this.validateSchwerpunkteConfig(textarea.value);

    if (isValid) {
      textarea.style.borderColor = "#28a745";
      this.removeValidationError(textarea);
    } else {
      textarea.style.borderColor = "#dc3545";
      this.showValidationError(
        textarea,
        "Format: max_teilnehmer,Name oder max_teilnehmer,Name1;Name2"
      );
    }
  }

  /**
   * Validiert die Klassen-Konfiguration
   */
  validateKlassenConfig(config) {
    const lines = config
      .trim()
      .split("\n")
      .filter((line) => line.trim());
    return lines.length > 0;
  }

  /**
   * Zeigt Validierungsfehler an
   */
  showValidationError(element, message) {
    const parent = element.parentElement;
    let errorElement = parent.querySelector(".validation-error");

    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "validation-error";
      errorElement.style.cssText = `
                color: #dc3545;
                font-size: 0.8rem;
                margin-top: 0.25rem;
                font-style: italic;
            `;
      parent.appendChild(errorElement);
    }

    errorElement.textContent = message;
  }

  /**
   * Entfernt Validierungsfehler
   */
  removeValidationError(element) {
    const parent = element.parentElement;
    const errorElement = parent.querySelector(".validation-error");
    if (errorElement) {
      errorElement.remove();
    }
  }

  /**
   * Bestätigungsdialoge für kritische Aktionen
   */
  initConfirmDialogs() {
    // Einwahl schließen/öffnen
    const toggleButton = document.querySelector(
      'button[name="action"][value="toggle_einwahl"]'
    );
    if (toggleButton) {
      toggleButton.addEventListener("click", (e) => {
        const isOpen = document.querySelector(".status-open") !== null;
        const action = isOpen ? "schließen" : "öffnen";

        if (!confirm(`Möchten Sie die Einwahl wirklich ${action}?`)) {
          e.preventDefault();
        }
      });
    }

    // CSV-Export
    const exportButton = document.querySelector(
      'button[name="action"][value="csv_export"]'
    );
    if (exportButton) {
      exportButton.addEventListener("click", (e) => {
        if (
          !confirm(
            "CSV-Export starten? Dies kann bei vielen Einträgen etwas dauern."
          )
        ) {
          e.preventDefault();
        }
      });
    }

    // Passwort ändern
    const passwordButton = document.querySelector('button[type="submit"]');
    const passwordForm = document.getElementById("passwordForm");
    if (
      passwordButton &&
      passwordForm &&
      passwordForm.contains(passwordButton)
    ) {
      // Wird bereits in initPasswordForm() behandelt
    }

    // Alle löschen Button
    const deleteAllButton = document.querySelector(
      'button[onclick="showDeleteAllModal()"]'
    );
    if (deleteAllButton) {
      deleteAllButton.addEventListener("click", (e) => {
        e.preventDefault();
        if (
          !confirm(
            "Sind Sie sicher, dass Sie alle Einwahlen löschen möchten? Dies öffnet ein Bestätigungsfenster."
          )
        ) {
          return;
        }
        showDeleteAllModal();
      });
    }
  }

  /**
   * Auto-Refresh für Statistiken
   */
  initAutoRefresh() {
    // Alle 30 Sekunden Statistiken aktualisieren
    setInterval(() => {
      this.refreshStatistics();
    }, 30000);
  }

  /**
   * Aktualisiert die Statistiken per AJAX
   */
  async refreshStatistics() {
    try {
      const response = await fetch("ajax/statistics.php");
      if (response.ok) {
        const data = await response.json();
        this.updateStatisticsDisplay(data);
      }
    } catch (error) {
      console.warn("Statistiken konnten nicht aktualisiert werden:", error);
    }
  }

  /**
   * Aktualisiert die Statistik-Anzeige
   */
  updateStatisticsDisplay(data) {
    const statsItems = document.querySelectorAll(".stats-item");

    statsItems.forEach((item, index) => {
      if (data.statistiken && data.statistiken[index]) {
        const stat = data.statistiken[index];
        const countElement = item.querySelector(".stats-count");
        const progressFill = item.querySelector(".progress-fill");
        const percentageElement = item.querySelector(".progress-percentage");

        if (countElement) {
          countElement.textContent = `${stat.anzahl}/${stat.max}`;
        }

        if (progressFill) {
          progressFill.style.width = `${Math.min(stat.prozent, 100)}%`;
          progressFill.className = `progress-fill ${
            stat.prozent >= 100 ? "progress-full" : ""
          }`;
        }

        if (percentageElement) {
          percentageElement.textContent = `${stat.prozent}%`;
        }
      }
    });

    // Gesamt-Einwahlen aktualisieren
    if (data.gesamt_einwahlen) {
      const gesamtElement = document.querySelector(".card p");
      if (gesamtElement && gesamtElement.textContent.includes("Gesamt:")) {
        gesamtElement.textContent = `Gesamt: ${data.gesamt_einwahlen} Einwahlen`;
      }
    }
  }

  /**
   * Keyboard-Shortcuts
   */
  initKeyboardShortcuts() {
    document.addEventListener("keydown", (e) => {
      // Ctrl/Cmd + E: Einwahl toggle
      if ((e.ctrlKey || e.metaKey) && e.key === "e") {
        e.preventDefault();
        const toggleButton = document.querySelector(
          'button[name="action"][value="toggle_einwahl"]'
        );
        if (toggleButton) {
          toggleButton.click();
        }
      }

      // Ctrl/Cmd + D: CSV-Export
      if ((e.ctrlKey || e.metaKey) && e.key === "d") {
        e.preventDefault();
        const exportButton = document.querySelector(
          'button[name="action"][value="csv_export"]'
        );
        if (exportButton) {
          exportButton.click();
        }
      }

      // Escape: Schließt Modals/Dialoge
      if (e.key === "Escape") {
        this.closeModals();
      }
    });
  }

  /**
   * Tabellen-Sortierung
   */
  initTableSorting() {
    const table = document.querySelector("table");
    if (!table) return;

    const headers = table.querySelectorAll("th");
    headers.forEach((header, index) => {
      header.style.cursor = "pointer";
      header.title = "Klicken zum Sortieren";

      header.addEventListener("click", () => {
        this.sortTable(table, index);
      });
    });
  }

  /**
   * Sortiert Tabelle nach Spalte
   */
  sortTable(table, columnIndex) {
    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll("tr"));
    const isNumeric = this.isNumericColumn(rows, columnIndex);

    // Aktuelle Sortierrichtung ermitteln
    const header = table.querySelectorAll("th")[columnIndex];
    const currentDirection = header.dataset.sortDirection || "asc";
    const newDirection = currentDirection === "asc" ? "desc" : "asc";

    // Alle Header-Sortierungen zurücksetzen
    table.querySelectorAll("th").forEach((th) => {
      delete th.dataset.sortDirection;
      th.classList.remove("sort-asc", "sort-desc");
    });

    // Neue Sortierung setzen
    header.dataset.sortDirection = newDirection;
    header.classList.add(`sort-${newDirection}`);

    // Zeilen sortieren
    rows.sort((a, b) => {
      const aValue = this.getCellValue(a, columnIndex);
      const bValue = this.getCellValue(b, columnIndex);

      let comparison;
      if (isNumeric) {
        comparison = parseFloat(aValue) - parseFloat(bValue);
      } else {
        comparison = aValue.localeCompare(bValue, "de");
      }

      return newDirection === "desc" ? -comparison : comparison;
    });

    // Sortierte Zeilen wieder einfügen
    rows.forEach((row) => tbody.appendChild(row));
  }

  /**
   * Prüft ob Spalte numerische Werte enthält
   */
  isNumericColumn(rows, columnIndex) {
    if (rows.length === 0) return false;

    const sampleValue = this.getCellValue(rows[0], columnIndex);
    return !isNaN(parseFloat(sampleValue));
  }

  /**
   * Holt Zellenwert für Sortierung
   */
  getCellValue(row, columnIndex) {
    const cell = row.cells[columnIndex];
    return cell ? cell.textContent.trim() : "";
  }

  /**
   * Zeigt Fehlermeldung an
   */
  showError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "message error";
    errorDiv.textContent = message;

    const container = document.querySelector(".container");
    container.insertBefore(errorDiv, container.firstChild);

    // Auto-remove nach 5 Sekunden
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.remove();
      }
    }, 5000);
  }

  /**
   * Zeigt Erfolgsmeldung an
   */
  showSuccess(message) {
    const successDiv = document.createElement("div");
    successDiv.className = "message success";
    successDiv.textContent = message;

    const container = document.querySelector(".container");
    container.insertBefore(successDiv, container.firstChild);

    // Auto-remove nach 3 Sekunden
    setTimeout(() => {
      if (successDiv.parentNode) {
        successDiv.remove();
      }
    }, 3000);
  }

  /**
   * Schließt alle modalen Dialoge
   */
  closeModals() {
    // Hier können Modal-Dialoge geschlossen werden
    // Aktuell nicht implementiert, aber vorbereitet für zukünftige Erweiterungen
  }

  /**
   * Utility: Formatiert Zahlen für Anzeige
   */
  formatNumber(num) {
    return new Intl.NumberFormat("de-DE").format(num);
  }

  /**
   * Utility: Formatiert Datum für Anzeige
   */
  formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("de-DE", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    }).format(date);
  }

  /**
   * Passwort-Formular Validierung
   */
  initPasswordForm() {
    const passwordForm = document.getElementById("passwordForm");
    if (!passwordForm) return;

    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById("confirm_password");

    // Passwort-Bestätigung live validieren
    const validatePasswordMatch = () => {
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (confirmPassword && newPassword !== confirmPassword) {
        confirmPasswordInput.setCustomValidity(
          "Passwörter stimmen nicht überein"
        );
        this.showValidationError(
          confirmPasswordInput,
          "Passwörter stimmen nicht überein"
        );
      } else {
        confirmPasswordInput.setCustomValidity("");
        this.removeValidationError(confirmPasswordInput);
      }
    };

    newPasswordInput.addEventListener("input", validatePasswordMatch);
    confirmPasswordInput.addEventListener("input", validatePasswordMatch);

    // Passwort-Stärke anzeigen
    newPasswordInput.addEventListener("input", () => {
      this.showPasswordStrength(newPasswordInput);
    });

    // Form-Submit validieren
    passwordForm.addEventListener("submit", (e) => {
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (newPassword !== confirmPassword) {
        e.preventDefault();
        this.showError("Die neuen Passwörter stimmen nicht überein.");
        return;
      }

      if (newPassword.length < 6) {
        e.preventDefault();
        this.showError(
          "Das neue Passwort muss mindestens 6 Zeichen lang sein."
        );
        return;
      }

      // Bestätigung anfordern
      if (!confirm("Möchten Sie Ihr Passwort wirklich ändern?")) {
        e.preventDefault();
        return;
      }
    });
  }

  /**
   * Zeigt Passwort-Stärke an
   */
  showPasswordStrength(passwordInput) {
    const password = passwordInput.value;
    const parent = passwordInput.parentElement;

    // Entferne bestehende Stärke-Anzeige
    const existingStrength = parent.querySelector(".password-strength");
    if (existingStrength) {
      existingStrength.remove();
    }

    if (!password) return;

    // Berechne Passwort-Stärke
    let strength = 0;
    let strengthText = "";
    let strengthClass = "";

    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (strength <= 2) {
      strengthText = "Schwach";
      strengthClass = "weak";
    } else if (strength <= 4) {
      strengthText = "Mittel";
      strengthClass = "medium";
    } else {
      strengthText = "Stark";
      strengthClass = "strong";
    }

    // Stärke-Anzeige erstellen
    const strengthDiv = document.createElement("div");
    strengthDiv.className = `password-strength ${strengthClass}`;
    strengthDiv.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill" style="width: ${
                  (strength / 6) * 100
                }%"></div>
            </div>
            <span class="strength-text">Passwort-Stärke: ${strengthText}</span>
        `;

    parent.appendChild(strengthDiv);

    // CSS für Stärke-Anzeige hinzufügen (falls noch nicht vorhanden)
    if (!document.querySelector("#password-strength-css")) {
      const css = `
                .password-strength {
                    margin-top: 0.5rem;
                    font-size: 0.8rem;
                }
                
                .strength-bar {
                    width: 100%;
                    height: 4px;
                    background: #e9ecef;
                    border-radius: 2px;
                    overflow: hidden;
                    margin-bottom: 0.25rem;
                }
                
                .strength-fill {
                    height: 100%;
                    transition: width 0.3s ease;
                }
                
                .password-strength.weak .strength-fill {
                    background: #dc3545;
                }
                
                .password-strength.medium .strength-fill {
                    background: #ffc107;
                }
                
                .password-strength.strong .strength-fill {
                    background: #28a745;
                }
                
                .strength-text {
                    font-weight: 500;
                }
                
                .password-strength.weak .strength-text {
                    color: #dc3545;
                }
                
                .password-strength.medium .strength-text {
                    color: #856404;
                }
                
                .password-strength.strong .strength-text {
                    color: #155724;
                }
            `;

      const style = document.createElement("style");
      style.id = "password-strength-css";
      style.textContent = css;
      document.head.appendChild(style);
    }
  }
}

// CSS für Tabellen-Sortierung hinzufügen
const sortingCSS = `
    th.sort-asc::after {
        content: ' ↑';
        color: #667eea;
        font-weight: bold;
    }
    
    th.sort-desc::after {
        content: ' ↓';
        color: #667eea;
        font-weight: bold;
    }
    
    th:hover {
        background-color: #e9ecef !important;
    }
    
    .validation-error {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;

// CSS dynamisch hinzufügen
const styleElement = document.createElement("style");
styleElement.textContent = sortingCSS;
document.head.appendChild(styleElement);

// Admin Dashboard initialisieren
document.addEventListener("DOMContentLoaded", () => {
  // Nur initialisieren wenn wir im Admin-Bereich sind
  if (document.querySelector(".header")) {
    console.log("Admin Dashboard wird initialisiert..."); // Debug

    // Sicherstellen, dass Modal initial versteckt ist
    const modal = document.getElementById("deleteAllModal");
    if (modal) {
      modal.classList.remove("show");
      console.log("Modal initial versteckt"); // Debug
    }

    const dashboard = new AdminDashboard();

    // Globale Event-Listener für Modal
    if (modal) {
      // Modal schließen mit Close-Button
      const closeBtn = modal.querySelector(".modal-close");
      if (closeBtn) {
        closeBtn.addEventListener("click", (e) => {
          e.preventDefault();
          dashboard.closeDeleteAllModal();
        });
      }

      // Modal schließen mit Abbrechen-Button
      const cancelBtn = modal.querySelector(".modal-cancel");
      if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
          e.preventDefault();
          dashboard.closeDeleteAllModal();
        });
      }

      // Modal schließen bei Klick außerhalb
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          dashboard.closeDeleteAllModal();
        }
      });
    }

    // ESC-Taste für Modal schließen
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        dashboard.closeDeleteAllModal();
      }
    });

    console.log("Admin Dashboard initialisiert"); // Debug
  }
});

// Export für mögliche Tests
if (typeof module !== "undefined" && module.exports) {
  module.exports = AdminDashboard;
}
