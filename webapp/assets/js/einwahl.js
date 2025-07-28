/**
 * BüA Einwahl - JavaScript für dynamische Schwerpunkt-Filterung
 */

class EinwahlManager {
  constructor() {
    this.schwerpunkte = window.schwerpunkte || [];
    this.teilnehmerAnzahl = window.teilnehmerAnzahl || {};
    this.suffixHalbierer = window.suffixHalbierer;
    this.erstwunschSelect = document.getElementById("erstwunsch_id");
    this.zweitwunschSelect = document.getElementById("zweitwunsch_id");
    this.form = document.getElementById("einwahlForm");

    this.init();
  }

  init() {
    if (!this.erstwunschSelect || !this.zweitwunschSelect) {
      console.warn("Einwahl-Selects nicht gefunden");
      return;
    }

    // Event Listeners
    this.erstwunschSelect.addEventListener("change", (e) => {
      this.updateZweitwunschOptions(parseInt(e.target.value));
    });

    // Form-Validierung
    if (this.form) {
      this.form.addEventListener("submit", (e) => {
        this.handleFormSubmit(e);
      });
    }

    // Initial state
    this.updateZweitwunschOptions(null);

    console.log("EinwahlManager initialisiert");
  }

  /**
   * Aktualisiert die Zweitwunsch-Optionen basierend auf dem Erstwunsch
   */
  updateZweitwunschOptions(erstwunschId) {
    // Reset Zweitwunsch
    this.zweitwunschSelect.innerHTML =
      '<option value="">Bitte wählen...</option>';

    if (!erstwunschId) {
      this.zweitwunschSelect.disabled = true;
      this.updateSelectInfo(
        this.zweitwunschSelect,
        "Erst Erstwunsch wählen..."
      );
      return;
    }

    this.zweitwunschSelect.disabled = false;

    // Finde den gewählten Erstwunsch
    const erstwunsch = this.schwerpunkte.find((s) => s.id === erstwunschId);
    if (!erstwunsch) {
      console.error("Erstwunsch nicht gefunden:", erstwunschId);
      return;
    }

    let verfügbareOptionen = 0;

    // Alle möglichen Zweitwünsche durchgehen
    this.schwerpunkte.forEach((sp) => {
      if (sp.id === erstwunschId) return; // Gleicher Schwerpunkt nicht wählbar

      const aktuell = this.teilnehmerAnzahl[sp.id] || 0;
      const max = parseInt(sp.max_teilnehmer);
      const istVoll = aktuell >= max;

      // Prüfung auf Kombinierbarkeit
      const kombinierbar = this.sindKombinierbar(erstwunsch, sp);

      if (kombinierbar && !istVoll) {
        const option = document.createElement("option");
        option.value = sp.id;
        // Suffix anzeigen, falls vorhanden
        option.textContent = sp.suffix
          ? `${sp.name} (${sp.suffix}) (${aktuell}/${max})`
          : `${sp.name} (${aktuell}/${max})`;
        //   option.textContent = `${sp.name} (${sp.suffix}) (${aktuell}/${max})`;

        this.zweitwunschSelect.appendChild(option);
        verfügbareOptionen++;
      }
    });

    // Wenn keine Optionen verfügbar sind
    if (verfügbareOptionen === 0) {
      const option = document.createElement("option");
      option.value = "";
      option.textContent = "Keine kombinierbaren Kurse verfügbar";
      option.disabled = true;
      this.zweitwunschSelect.appendChild(option);

      this.updateSelectInfo(
        this.zweitwunschSelect,
        "Für diesen ersten Schwerpunkt sind keine weiteren Kurse verfügbar.",
        "error"
      );
    } else {
      this.updateSelectInfo(
        this.zweitwunschSelect,
        `${verfügbareOptionen} kombinierbare${
          verfügbareOptionen === 1 ? "r" : ""
        } Kurs${verfügbareOptionen === 1 ? "" : "e"} verfügbar.`
      );
    }
  }

  /**
   * Prüft ob zwei Schwerpunkte kombinierbar sind
   */
  sindKombinierbar(schwerpunkt1, schwerpunkt2) {
    // Prüfung auf Pflicht-Kombinationen

    // Wenn Schwerpunkt1 eine Pflicht-Kombination hat
    if (
      schwerpunkt1.kombination_mit &&
      schwerpunkt1.kombination_mit != schwerpunkt2.id
    ) {
      return false;
    }

    // Wenn Schwerpunkt2 eine Pflicht-Kombination hat
    if (
      schwerpunkt2.kombination_mit &&
      schwerpunkt2.kombination_mit != schwerpunkt1.id
    ) {
      return false;
    }
    var maxKombis = Math.min(
      parseInt(schwerpunkt1.max_teilnehmer),
      parseInt(schwerpunkt2.max_teilnehmer)
    );

    if (this.suffixHalbierer == 1) {
      maxKombis = maxKombis / 2;
    }
    if (
      schwerpunkt1.suffix &&
      schwerpunkt2.suffix &&
      einwahlKombinationen[schwerpunkt1.id + "-" + schwerpunkt2.id] >= maxKombis
    ) {
      return false;
    }

    if (
      schwerpunkt1.suffix &&
      (schwerpunkt1.suffix === schwerpunkt2.suffix ||
        schwerpunkt1.name === schwerpunkt2.name)
    ) {
      return false; // Gleiche Suffixe sind nicht erlaubt
    }
    return true;
  }

  /**
   * Aktualisiert die Info-Nachricht unter einem Select-Element
   */
  updateSelectInfo(selectElement, message, type = "info") {
    const parent = selectElement.parentElement;
    let infoElement = parent.querySelector(".course-info");

    if (!infoElement) {
      infoElement = document.createElement("div");
      infoElement.className = "course-info";
      parent.appendChild(infoElement);
    }

    infoElement.textContent = message;
    infoElement.className = `course-info ${
      type === "error" ? "course-full" : ""
    }`;
  }

  /**
   * Behandelt das Absenden des Formulars
   */
  handleFormSubmit(event) {
    const formData = new FormData(this.form);

    // Grundvalidierung
    const vorname = formData.get("vorname")?.trim();
    const nachname = formData.get("nachname")?.trim();
    const klasse_id = formData.get("klasse_id");
    const erstwunsch_id = formData.get("erstwunsch_id");
    const zweitwunsch_id = formData.get("zweitwunsch_id");

    if (
      !vorname ||
      !nachname ||
      !klasse_id ||
      !erstwunsch_id ||
      !zweitwunsch_id
    ) {
      event.preventDefault();
      this.showValidationError("Bitte füllen Sie alle Pflichtfelder aus.");
      return;
    }

    if (erstwunsch_id === zweitwunsch_id) {
      event.preventDefault();
      this.showValidationError(
        "Die beiden Schwerpunkte müssen unterschiedlich sein."
      );
      return;
    }

    // Kombinierbarkeit prüfen
    const erstwunsch = this.schwerpunkte.find((s) => s.id == erstwunsch_id);
    const zweitwunsch = this.schwerpunkte.find((s) => s.id == zweitwunsch_id);

    if (!this.sindKombinierbar(erstwunsch, zweitwunsch)) {
      event.preventDefault();
      this.showValidationError(
        "Diese Schwerpunkt-Kombination ist nicht erlaubt."
      );
      return;
    }

    // Kapazität prüfen
    const erstwunschAktuell = this.teilnehmerAnzahl[erstwunsch_id] || 0;
    const zweitwunschAktuell = this.teilnehmerAnzahl[zweitwunsch_id] || 0;

    if (erstwunschAktuell >= erstwunsch.max_teilnehmer) {
      event.preventDefault();
      this.showValidationError("Der erste Schwerpunkt-Kurs ist bereits voll.");
      return;
    }

    if (zweitwunschAktuell >= zweitwunsch.max_teilnehmer) {
      event.preventDefault();
      this.showValidationError("Der zweite Schwerpunkt-Kurs ist bereits voll.");
      return;
    }

    // Loading-State aktivieren
    this.setLoadingState(true);
  }

  /**
   * Zeigt Validierungsfehler an
   */
  showValidationError(message) {
    this.removeExistingErrors();

    const errorDiv = document.createElement("div");
    errorDiv.className = "message error";
    errorDiv.textContent = message;

    const container = document.querySelector(".container");
    const title = container.querySelector("h1");
    title.insertAdjacentElement("afterend", errorDiv);

    // Smooth scroll to error
    errorDiv.scrollIntoView({ behavior: "smooth", block: "center" });

    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.remove();
      }
    }, 5000);
  }

  /**
   * Entfernt bestehende Fehlermeldungen
   */
  removeExistingErrors() {
    const existingErrors = document.querySelectorAll(".message.error");
    existingErrors.forEach((error) => error.remove());
  }

  /**
   * Setzt Loading-State für das Formular
   */
  setLoadingState(loading) {
    const container = document.querySelector(".container");
    const submitButton = this.form.querySelector('button[type="submit"]');

    if (loading) {
      container.classList.add("loading");
      submitButton.textContent = "Speichere Einwahl...";
      submitButton.disabled = true;
    } else {
      container.classList.remove("loading");
      submitButton.textContent = "Einwahl speichern";
      submitButton.disabled = false;
    }
  }

  /**
   * Debug-Funktion für Entwicklung
   */
  debug() {
    console.log("Schwerpunkte:", this.schwerpunkte);
    console.log("Teilnehmer-Anzahl:", this.teilnehmerAnzahl);
    console.log("Erstwunsch-Select:", this.erstwunschSelect);
    console.log("Zweitwunsch-Select:", this.zweitwunschSelect);
  }
}

// Initialisierung nach DOM-Load
document.addEventListener("DOMContentLoaded", () => {
  new EinwahlManager();
});

// Export für mögliche Tests
if (typeof module !== "undefined" && module.exports) {
  module.exports = EinwahlManager;
}
