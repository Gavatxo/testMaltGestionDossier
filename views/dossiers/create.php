<?php
// views/dossiers/create.php - Formulaire de création de dossier
$view = 'dossiers/create';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>">
                <i class="fas fa-home"></i> Accueil
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>dossier">Dossiers</a>
        </li>
        <li class="breadcrumb-item active">Nouveau dossier</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                <i class="fas fa-folder-plus text-primary fs-3"></i>
            </div>
            <div>
                <h1 class="h2 mb-1">Créer un nouveau dossier</h1>
                <p class="text-muted mb-0">
                    La référence sera générée automatiquement
                </p>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <a href="<?= BASE_URL ?>dossier" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Retour à la liste
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Formulaire principal -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2 text-muted"></i>
                    Informations du dossier
                </h5>
            </div>
            <div class="card-body">
                <form id="createDossierForm" method="POST" action="<?= BASE_URL ?>dossier/store">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <!-- Référence (auto-générée) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-hashtag me-1"></i>
                            Référence du dossier
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-folder text-primary"></i>
                            </span>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   value="DOS-<?= date('Y') ?>-XXX (générée automatiquement)" 
                                   readonly>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            La référence unique sera générée automatiquement au format DOS-YYYY-XXX
                        </div>
                    </div>
                    
                    <!-- Date de création -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar me-1"></i>
                            Date de création
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-clock text-success"></i>
                            </span>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   value="<?= formatDate(date('Y-m-d H:i:s')) ?>" 
                                   readonly>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Date et heure actuelles
                        </div>
                    </div>
                    
                    <!-- Tiers à associer (optionnel) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-building me-1"></i>
                            Tiers à associer <span class="text-muted">(optionnel)</span>
                        </label>
                        
                        <?php if (!empty($tiers)): ?>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                <div class="form-text mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Sélectionnez les tiers que vous souhaitez associer à ce dossier
                                </div>
                                
                                <div class="row">
                                    <?php foreach ($tiers as $tier): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="tiers_ids[]" 
                                                       value="<?= e($tier['id']) ?>" 
                                                       id="tiers_<?= e($tier['id']) ?>">
                                                <label class="form-check-label" for="tiers_<?= e($tier['id']) ?>">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-building text-primary me-2"></i>
                                                        <div>
                                                            <div class="fw-bold"><?= e($tier['denomination']) ?></div>
                                                            <small class="text-muted">
                                                                <?= e($tier['nb_contacts'] ?? 0) ?> contact(s)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Aucun tiers disponible</strong><br>
                                Vous pourrez associer des tiers au dossier après sa création.
                                <a href="#" class="alert-link" onclick="showCreateTiersModal()">
                                    Créer un nouveau tiers maintenant
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>dossier" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Créer le dossier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Aide et informations -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Aide
                </h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Comment ça marche ?</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Un dossier est créé avec une référence unique
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Vous pouvez y associer des tiers existants
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Chaque tiers peut avoir plusieurs contacts
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        Tout peut être modifié après création
                    </li>
                </ul>
                
                <hr>
                
                <h6 class="fw-bold">Actions rapides</h6>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="showCreateTiersModal()">
                        <i class="fas fa-building me-2"></i>
                        Créer un tiers
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="showCreateContactModal()">
                        <i class="fas fa-user-plus me-2"></i>
                        Créer un contact
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2 text-muted"></i>
                    Statistiques
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1"><?= count($tiers ?? []) ?></h4>
                            <small class="text-muted">Tiers disponibles</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">
                            <?php
                            $totalContacts = 0;
                            foreach ($tiers ?? [] as $tier) {
                                $totalContacts += $tier['nb_contacts'] ?? 0;
                            }
                            echo $totalContacts;
                            ?>
                        </h4>
                        <small class="text-muted">Contacts totaux</small>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Vous pourrez ajouter plus de tiers et contacts après la création du dossier.
                </div>
            </div>
        </div>
        
        <!-- Derniers dossiers créés -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2 text-muted"></i>
                    Derniers dossiers créés
                </h6>
            </div>
            <div class="card-body">
                <div id="recent-dossiers">
                    <div class="text-center p-3">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check:hover {
    background-color: #f8f9fa;
    border-radius: 5px;
    transition: background-color 0.2s;
}

.form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.btn-outline-success:hover,
.btn-outline-info:hover {
    transform: translateY(-1px);
    transition: transform 0.2s;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Charger les derniers dossiers
    loadRecentDossiers();
    
    // Gestion du formulaire
    const form = document.getElementById('createDossierForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createDossier();
    });
    
    // Sélection/désélection de tous les tiers
    addSelectAllTiersButton();
});

function createDossier() {
    const form = document.getElementById('createDossierForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Désactiver le bouton et afficher le loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Création en cours...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // Redirection vers le dossier créé
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success === false) {
            throw new Error(data.message || 'Erreur lors de la création');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la création du dossier : ' + error.message);
        
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Créer le dossier';
    });
}

function loadRecentDossiers() {
    fetch(`${window.baseUrl}dossier/api`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recent-dossiers');
            
            if (data.success && data.data.length > 0) {
                const recentDossiers = data.data.slice(0, 3); // 3 derniers
                container.innerHTML = recentDossiers.map(dossier => `
                    <div class="d-flex align-items-center mb-3 p-2 border rounded hover-item">
                        <i class="fas fa-folder text-primary me-3"></i>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">${dossier.reference}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                ${formatDateShort(dossier.date_creation)}
                            </div>
                        </div>
                        <a href="${window.baseUrl}dossier/${dossier.id}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted p-3">
                        <i class="fas fa-folder-open mb-2"></i>
                        <div class="small">Aucun dossier existant</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('recent-dossiers').innerHTML = `
                <div class="text-center text-danger p-3">
                    <i class="fas fa-exclamation-triangle mb-2"></i>
                    <div class="small">Erreur de chargement</div>
                </div>
            `;
        });
}

function addSelectAllTiersButton() {
    const tiersContainer = document.querySelector('.border.rounded.p-3');
    if (tiersContainer && document.querySelectorAll('input[name="tiers_ids[]"]').length > 0) {
        const selectAllDiv = document.createElement('div');
        selectAllDiv.className = 'mb-3 pb-3 border-bottom';
        selectAllDiv.innerHTML = `
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllTiers(true)">
                    <i class="fas fa-check-square me-1"></i>Tout sélectionner
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAllTiers(false)">
                    <i class="fas fa-square me-1"></i>Tout désélectionner
                </button>
            </div>
        `;
        
        tiersContainer.insertBefore(selectAllDiv, tiersContainer.querySelector('.form-text').nextSibling);
    }
}

function selectAllTiers(select) {
    const checkboxes = document.querySelectorAll('input[name="tiers_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = select;
    });
}

function formatDateShort(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Gestion des modales de création rapide (réutilisation du code de navbar.php)
function showCreateTiersModal() {
    const modal = new bootstrap.Modal(document.getElementById('createTiersModal'));
    modal.show();
}

function showCreateContactModal() {
    const modal = new bootstrap.Modal(document.getElementById('createContactModal'));
    modal.show();
}

// Styles dynamiques
const style = document.createElement('style');
style.textContent = `
    .hover-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
        transition: all 0.2s ease;
    }
    
    .form-check-label:hover {
        cursor: pointer;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
        transition: transform 0.2s ease;
    }
`;
document.head.appendChild(style);
</script>