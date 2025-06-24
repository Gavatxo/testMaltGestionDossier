// assets/js/app.js - JavaScript principal de l'application

/**
 * Configuration globale
 */
const App = {
  config: {
    searchDelay: 300,
    animationDuration: 300,
    maxSearchResults: 10,
    debounceDelay: 250,
  },

  cache: {
    searchResults: new Map(),
    lastSearchQuery: "",
    searchTimeout: null,
  },

  elements: {
    searchInput: null,
    searchResults: null,
    loadingModal: null,
    deleteModal: null,
  },
};

/**
 * Initialisation de l'application
 */
document.addEventListener("DOMContentLoaded", function () {
  initializeApp();
  initializeSearch();
  initializeModals();
  initializeForms();
  initializeStatistics();
  initializeTooltips();
  console.log("🚀 Application initialisée");
});

/**
 * Initialisation principale
 */
function initializeApp() {
  // Cache des éléments DOM
  App.elements.searchInput = document.getElementById("globalSearch");
  App.elements.searchResults = document.getElementById("searchResults");
  App.elements.loadingModal = document.getElementById("loadingModal");
  App.elements.deleteModal = document.getElementById("deleteModal");

  // Gestion des erreurs globales
  window.addEventListener("error", function (e) {
    console.error("Erreur JavaScript:", e.error);
    showNotification("Une erreur inattendue s'est produite", "error");
  });

  // Gestion des erreurs AJAX
  window.addEventListener("unhandledrejection", function (e) {
    console.error("Promise rejetée:", e.reason);
    showNotification("Erreur de communication avec le serveur", "error");
  });
}

/**
 * ========================================
 * SYSTÈME DE RECHERCHE GLOBALE
 * ========================================
 */

function initializeSearch() {
  if (!App.elements.searchInput) return;

  // Événements de recherche
  App.elements.searchInput.addEventListener(
    "input",
    debounce(handleSearchInput, App.config.debounceDelay)
  );
  App.elements.searchInput.addEventListener("focus", showSearchResults);
  App.elements.searchInput.addEventListener("keydown", handleSearchKeydown);

  // Cacher les résultats quand on clique ailleurs
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-container")) {
      hideSearchResults();
    }
  });
}

function handleSearchInput(e) {
  const query = e.target.value.trim();

  if (query.length === 0) {
    showRecentSearches();
    return;
  }

  if (query.length < 2) {
    hideSearchResults();
    return;
  }

  // Vérifier le cache
  if (App.cache.searchResults.has(query)) {
    displaySearchResults(App.cache.searchResults.get(query), query);
    return;
  }

  // Recherche via API
  performSearch(query);
}

function performSearch(query) {
  if (App.cache.searchTimeout) {
    clearTimeout(App.cache.searchTimeout);
  }

  App.cache.searchTimeout = setTimeout(() => {
    showSearchLoading();

    const searchUrl = `${window.baseUrl}search/advanced?q=${encodeURIComponent(
      query
    )}&limit=${App.config.maxSearchResults}`;

    fetch(searchUrl)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          App.cache.searchResults.set(query, data.results);
          displaySearchResults(data.results, query);
        } else {
          showSearchError(data.message || "Erreur de recherche");
        }
      })
      .catch((error) => {
        console.error("Erreur de recherche:", error);
        showSearchError("Erreur de communication");
      });
  }, App.config.searchDelay);
}

function displaySearchResults(results, query) {
  if (!App.elements.searchResults) return;

  App.elements.searchResults.style.display = "block";

  if (results.length === 0) {
    App.elements.searchResults.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="fas fa-search mb-2"></i>
                <div>Aucun résultat pour "${query}"</div>
            </div>
        `;
    return;
  }

  const groupedResults = groupSearchResults(results);
  let html = "";

  // Afficher les résultats par type
  Object.entries(groupedResults).forEach(([type, items]) => {
    if (items.length === 0) return;

    const typeInfo = getSearchTypeInfo(type);
    html += `
            <div class="search-section">
                <div class="search-section-header p-2 bg-light border-bottom">
                    <small class="fw-bold text-muted">
                        <i class="${typeInfo.icon} me-1"></i>
                        ${typeInfo.label} (${items.length})
                    </small>
                </div>
        `;

    items.forEach((item) => {
      html += createSearchResultItem(item, type);
    });

    html += "</div>";
  });

  App.elements.searchResults.innerHTML = html;
}

function groupSearchResults(results) {
  return results.reduce((groups, item) => {
    const type = item.search_type || "dossier";
    if (!groups[type]) groups[type] = [];
    groups[type].push(item);
    return groups;
  }, {});
}

function createSearchResultItem(item, type) {
  const typeInfo = getSearchTypeInfo(type);
  let title, subtitle, url;

  switch (type) {
    case "dossier":
      title = item.reference;
      subtitle = `${item.match_type} : ${item.match_detail}`;
      url = `${window.baseUrl}dossier/${item.id}`;
      break;
    case "tiers":
      title = item.denomination;
      subtitle = `${item.nb_contacts || 0} contact(s)`;
      url = `#`; // Pas de page dédiée pour les tiers
      break;
    case "contact":
      title = `${item.prenom} ${item.nom}`;
      subtitle = item.email;
      url = `#`; // Pas de page dédiée pour les contacts
      break;
    default:
      title = "Élément inconnu";
      subtitle = "";
      url = "#";
  }

  return `
        <div class="search-result-item" onclick="navigateToResult('${url}', '${type}')">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="${typeInfo.icon} ${typeInfo.color}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">${escapeHtml(title)}</div>
                    <div class="small text-muted">${escapeHtml(subtitle)}</div>
                </div>
                <div class="ms-2">
                    <i class="fas fa-chevron-right text-muted small"></i>
                </div>
            </div>
        </div>
    `;
}

function getSearchTypeInfo(type) {
  const types = {
    dossier: {
      icon: "fas fa-folder",
      color: "text-primary",
      label: "Dossiers",
    },
    tiers: { icon: "fas fa-building", color: "text-success", label: "Tiers" },
    contact: { icon: "fas fa-user", color: "text-info", label: "Contacts" },
  };
  return types[type] || types.dossier;
}

function navigateToResult(url, type) {
  hideSearchResults();

  if (url && url !== "#") {
    window.location.href = url;
  } else {
    showNotification(`Navigation vers ${type} non implémentée`, "info");
  }
}

function showRecentSearches() {
  if (!App.elements.searchResults) return;

  App.elements.searchResults.style.display = "block";
  App.elements.searchResults.innerHTML = `
        <div class="p-3 text-center text-muted">
            <i class="fas fa-clock mb-2"></i>
            <div class="small">Commencez à taper pour rechercher...</div>
        </div>
    `;
}

function showSearchLoading() {
  if (!App.elements.searchResults) return;

  App.elements.searchResults.style.display = "block";
  App.elements.searchResults.innerHTML = `
        <div class="p-3 text-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Recherche...</span>
            </div>
            <span class="text-muted">Recherche en cours...</span>
        </div>
    `;
}

function showSearchError(message) {
  if (!App.elements.searchResults) return;

  App.elements.searchResults.style.display = "block";
  App.elements.searchResults.innerHTML = `
        <div class="p-3 text-center text-danger">
            <i class="fas fa-exclamation-triangle mb-2"></i>
            <div class="small">${escapeHtml(message)}</div>
        </div>
    `;
}

function showSearchResults() {
  if (App.elements.searchInput.value.trim().length >= 2) {
    App.elements.searchResults.style.display = "block";
  }
}

function hideSearchResults() {
  if (App.elements.searchResults) {
    App.elements.searchResults.style.display = "none";
  }
}

function handleSearchKeydown(e) {
  if (e.key === "Escape") {
    hideSearchResults();
    App.elements.searchInput.blur();
  }
}

/**
 * ========================================
 * GESTION DES MODALES
 * ========================================
 */

function initializeModals() {
  // Modal de suppression
  if (App.elements.deleteModal) {
    App.elements.deleteModal.addEventListener("hidden.bs.modal", function () {
      document.getElementById("delete-message").textContent =
        "Êtes-vous sûr de vouloir supprimer cet élément ?";
      document.getElementById("confirm-delete").onclick = null;
    });
  }

  // Modales de création rapide depuis la navbar
  initializeQuickCreateModals();
}

function initializeQuickCreateModals() {
  // Modal création tiers
  const createTiersForm = document.getElementById("createTiersForm");
  if (createTiersForm) {
    createTiersForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleQuickCreateTiers(this);
    });
  }

  // Modal création contact
  const createContactForm = document.getElementById("createContactForm");
  if (createContactForm) {
    createContactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleQuickCreateContact(this);
    });
  }
}

function handleQuickCreateTiers(form) {
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  setLoading(submitBtn, true);

  fetch(`${window.baseUrl}tiers/create`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          `Tiers "${data.tiers.denomination}" créé avec succès`,
          "success"
        );
        form.reset();
        bootstrap.Modal.getInstance(
          document.getElementById("createTiersModal")
        ).hide();
        updateStatistics(); // Rafraîchir les stats
      } else {
        showNotification("Erreur : " + data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      showNotification("Erreur de communication", "error");
    })
    .finally(() => {
      setLoading(submitBtn, false);
    });
}

function handleQuickCreateContact(form) {
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  setLoading(submitBtn, true);

  fetch(`${window.baseUrl}contact/create`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          `Contact "${data.contact.prenom} ${data.contact.nom}" créé avec succès`,
          "success"
        );
        form.reset();
        bootstrap.Modal.getInstance(
          document.getElementById("createContactModal")
        ).hide();
        updateStatistics(); // Rafraîchir les stats
      } else {
        showNotification("Erreur : " + data.message, "error");

        // Afficher les erreurs de validation si disponibles
        if (data.errors) {
          Object.entries(data.errors).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
              input.classList.add("is-invalid");
              const feedback = input.nextElementSibling;
              if (feedback && feedback.classList.contains("invalid-feedback")) {
                feedback.textContent = message;
              }
            }
          });
        }
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      showNotification("Erreur de communication", "error");
    })
    .finally(() => {
      setLoading(submitBtn, false);
    });
}

/**
 * ========================================
 * GESTION DES FORMULAIRES
 * ========================================
 */

function initializeForms() {
  // Validation en temps réel
  document.querySelectorAll('input[type="email"]').forEach((input) => {
    input.addEventListener("blur", validateEmail);
  });

  // Nettoyage des erreurs de validation
  document.querySelectorAll(".form-control").forEach((input) => {
    input.addEventListener("input", function () {
      this.classList.remove("is-invalid");
    });
  });

  // Confirmation avant soumission des formulaires de suppression
  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", function (e) {
      const message = this.dataset.confirm || "Êtes-vous sûr ?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });
}

function validateEmail(e) {
  const email = e.target.value.trim();
  const input = e.target;

  if (email && !isValidEmail(email)) {
    input.classList.add("is-invalid");
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains("invalid-feedback")) {
      feedback.textContent = "Format d'email invalide";
    }
  } else {
    input.classList.remove("is-invalid");
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * ========================================
 * GESTION DES STATISTIQUES
 * ========================================
 */

function initializeStatistics() {
  updateStatistics();

  // Mettre à jour les statistiques toutes les 5 minutes
  setInterval(updateStatistics, 300000);
}

function updateStatistics() {
  const statsElements = {
    dossiers: document.getElementById("stats-dossiers"),
    tiers: document.getElementById("stats-tiers"),
    contacts: document.getElementById("stats-contacts"),
  };

  // Récupérer les statistiques via API
  Promise.all([
    fetch(`${window.baseUrl}dossier/api`).then((r) => r.json()),
    fetch(`${window.baseUrl}tiers/api`).then((r) => r.json()),
    fetch(`${window.baseUrl}contact/api`).then((r) => r.json()),
  ])
    .then(([dossiers, tiers, contacts]) => {
      if (statsElements.dossiers && dossiers.success) {
        statsElements.dossiers.innerHTML = `<i class="fas fa-folder me-1"></i>${dossiers.count}`;
      }
      if (statsElements.tiers && tiers.success) {
        statsElements.tiers.innerHTML = `<i class="fas fa-building me-1"></i>${tiers.count}`;
      }
      if (statsElements.contacts && contacts.success) {
        statsElements.contacts.innerHTML = `<i class="fas fa-users me-1"></i>${contacts.count}`;
      }
    })
    .catch((error) => {
      console.error("Erreur mise à jour statistiques:", error);
    });
}

/**
 * ========================================
 * SYSTÈME DE NOTIFICATIONS
 * ========================================
 */

function showNotification(message, type = "info", duration = 5000) {
  const notification = createNotificationElement(message, type);
  document.body.appendChild(notification);

  // Animation d'entrée
  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  // Auto-suppression
  setTimeout(() => {
    hideNotification(notification);
  }, duration);

  return notification;
}

function createNotificationElement(message, type) {
  const icons = {
    success: "fas fa-check-circle",
    error: "fas fa-exclamation-triangle",
    warning: "fas fa-exclamation-circle",
    info: "fas fa-info-circle",
  };

  const colors = {
    success: "alert-success",
    error: "alert-danger",
    warning: "alert-warning",
    info: "alert-info",
  };

  const notification = document.createElement("div");
  notification.className = `alert ${colors[type]} alert-dismissible notification-toast`;
  notification.innerHTML = `
        <i class="${icons[type]} me-2"></i>
        ${escapeHtml(message)}
        <button type="button" class="btn-close" onclick="hideNotification(this.parentElement)"></button>
    `;

  return notification;
}

function hideNotification(notification) {
  notification.classList.remove("show");
  setTimeout(() => {
    if (notification.parentElement) {
      notification.parentElement.removeChild(notification);
    }
  }, 300);
}

/**
 * ========================================
 * UTILITAIRES
 * ========================================
 */

function initializeTooltips() {
  // Initialiser les tooltips Bootstrap
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

function setLoading(button, loading) {
  if (!button) return;

  if (loading) {
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';
  } else {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || "Valider";
  }
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text.replace(/[&<>"']/g, function (m) {
    return map[m];
  });
}

function showLoading() {
  if (App.elements.loadingModal) {
    const modal = new bootstrap.Modal(App.elements.loadingModal);
    modal.show();
  }
}

function hideLoading() {
  if (App.elements.loadingModal) {
    const modal = bootstrap.Modal.getInstance(App.elements.loadingModal);
    if (modal) {
      modal.hide();
    }
  }
}

/**
 * ========================================
 * FONCTIONS GLOBALES ACCESSIBLES
 * ========================================
 */

// Fonctions accessibles depuis les vues PHP
window.showCreateTiersModal = function () {
  const modal = new bootstrap.Modal(
    document.getElementById("createTiersModal")
  );
  modal.show();
};

window.showCreateContactModal = function () {
  const modal = new bootstrap.Modal(
    document.getElementById("createContactModal")
  );
  modal.show();
};

window.confirmDelete = function (type, id, name) {
  const modal = new bootstrap.Modal(App.elements.deleteModal);
  const message = document.getElementById("delete-message");
  const confirmBtn = document.getElementById("confirm-delete");

  message.textContent = `Êtes-vous sûr de vouloir supprimer ${type} "${name}" ?`;

  confirmBtn.onclick = function () {
    window.location.href = `${window.baseUrl}${type}/delete/${id}`;
    modal.hide();
  };

  modal.show();
};

window.showNotification = showNotification;
window.updateStatistics = updateStatistics;

// CSS pour les notifications
const notificationStyles = `
<style>
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.notification-toast.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-toast .btn-close {
    position: relative;
    margin-left: auto;
}
</style>
`;

document.head.insertAdjacentHTML("beforeend", notificationStyles);

console.log("📱 JavaScript de l'application chargé");
