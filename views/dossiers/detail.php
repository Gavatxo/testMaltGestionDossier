<?php
// views/dossiers/detail.php - Détail d'un dossier
$view = 'dossiers/detail';
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
        <li class="breadcrumb-item active">
            <?= e($dossier['reference']) ?>
        </li>
    </ol>
</nav>

<!-- En-tête du dossier -->
<div class="row align-items-center mb-4">
    <div class="col">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                <i class="fas fa-folder text-primary fs-3"></i>
            </div>
            <div>
                <h1 class="h2 mb-1"><?= e($dossier['reference']) ?></h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar me-1"></i>
                    Créé le <?= formatDate($dossier['date_creation']) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success" onclick="showAddTiersModal()">
                <i class="fas fa-plus me-2"></i>
                Ajouter un tiers
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" onclick="exportDossier()">
                            <i class="fas fa-download me-2"></i>Exporter
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" onclick="confirmDeleteDossier()">
                            <i class="fas fa-trash me-2"></i>Supprimer le dossier
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques du dossier -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-primary mb-2">
                    <i class="fas fa-building fs-1"></i>
                </div>
                <h3 class="mb-1"><?= count($dossier['tiers'] ?? []) ?></h3>
                <p class="text-muted mb-0">Tiers associés</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-success mb-2">
                    <i class="fas fa-users fs-1"></i>
                </div>
                <h3 class="mb-1">
                    <?php
                    $totalContacts = 0;
                    foreach ($dossier['tiers'] ?? [] as $tier) {
                        $totalContacts += count($tier['contacts'] ?? []);
                    }
                    echo $totalContacts;
                    ?>
                </h3>
                <p class="text-muted mb-0">Contacts totaux</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="text-info mb-2">
                    <i class="fas fa-clock fs-1"></i>
                </div>
                <h3 class="mb-1">
                    <?php
                    $daysSince = floor((time() - strtotime($dossier['date_creation'])) / (60 * 60 * 24));
                    echo $daysSince;
                    ?>
                </h3>
                <p class="text-muted mb-0">Jours depuis création</p>
            </div>
        </div>
    </div>
</div>

<!-- Liste des tiers et contacts -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-sitemap me-2 text-muted"></i>
                Tiers et Contacts
            </h5>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="expandAllTiers()">
                <i class="fas fa-expand-arrows-alt me-2"></i>
                Développer tout
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($dossier['tiers'])): ?>
            <!-- État vide -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-building text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-muted mb-3">Aucun tiers associé</h6>
                <p class="text-muted mb-4">
                    Ajoutez des tiers à ce dossier pour commencer à organiser vos contacts.
                </p>
                <button type="button" class="btn btn-primary" onclick="showAddTiersModal()">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un tiers
                </button>
            </div>
        <?php else: ?>
            <!-- Liste des tiers -->
            <div class="accordion" id="tiersAccordion">
                <?php foreach ($dossier['tiers'] as $index => $tier): ?>
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="heading<?= $index ?>">
                            <button class="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= $index ?>">
                                <div class="d-flex align-items-center w-100">
                                    <div class="me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?= e($tier['tiers_denomination']) ?></div>
                                        <small class="text-muted">
                                            <?= count($tier['contacts'] ?? []) ?> contact(s)
                                        </small>
                                    </div>
                                    <div class="me-3">
                                        <span class="badge bg-success">
                                            <?= count($tier['contacts'] ?? []) ?>
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $index ?>" 
                             class="accordion-collapse collapse" 
                             data-bs-parent="#tiersAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <h6 class="mb-0">
                                            <i class="fas fa-users me-2 text-success"></i>
                                            Contacts de <?= e($tier['tiers_denomination']) ?>
                                        </h6>
                                    </div>
                                    <div class="col-auto">
                                        <div class="btn-group" role="group" size="sm">
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-sm"
                                                    onclick="showAddContactModal(<?= e($tier['tiers_id']) ?>)">
                                                <i class="fas fa-user-plus me-1"></i>
                                                Ajouter contact
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="removeTiersFromDossier(<?= e($tier['tiers_id']) ?>)">
                                                <i class="fas fa-times me-1"></i>
                                                Retirer tiers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (empty($tier['contacts'])): ?>
                                    <div class="text-center py-3 bg-light rounded">
                                        <i class="fas fa-user-slash text-muted mb-2"></i>
                                        <p class="text-muted mb-2">Aucun contact pour ce tiers</p>
                                        <button type="button" 
                                                class="btn btn-sm btn-success"
                                                onclick="showAddContactModal(<?= e($tier['tiers_id']) ?>)">
                                            <i class="fas fa-plus me-1"></i>
                                            Ajouter le premier contact
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($tier['contacts'] as $contact): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-0 bg-light h-100">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-start">
                                                            <div class="avatar-circle bg-info text-white me-3">
                                                                <?= strtoupper(substr($contact['contact_prenom'], 0, 1) . substr($contact['contact_nom'], 0, 1)) ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">
                                                                    <?= e($contact['contact_prenom']) ?> 
                                                                    <?= e($contact['contact_nom']) ?>
                                                                </h6>
                                                                <p class="text-muted mb-2 small">
                                                                    <i class="fas fa-envelope me-1"></i>
                                                                    <a href="mailto:<?= e($contact['contact_email']) ?>" 
                                                                       class="text-decoration-none">
                                                                        <?= e($contact['contact_email']) ?>
                                                                    </a>
                                                                </p>
                                                                <div class="small text-muted">
                                                                    Ajouté le <?= formatDateShort($contact['date_association']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown">
                                                                <button class="btn btn-outline-secondary btn-sm" 
                                                                        type="button" 
                                                                        data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a class="dropdown-item" 
                                                                           href="mailto:<?= e($contact['contact_email']) ?>">
                                                                            <i class="fas fa-envelope me-2"></i>
                                                                            Envoyer email
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item" 
                                                                           href="#" 
                                                                           onclick="editContact(<?= e($contact['contact_id']) ?>)">
                                                                            <i class="fas fa-edit me-2"></i>
                                                                            Modifier
                                                                        </a>
                                                                    </li>
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" 
                                                                           href="#" 
                                                                           onclick="removeContactFromTiers(<?= e($tier['tiers_id']) ?>, <?= e($contact['contact_id']) ?>)">
                                                                            <i class="fas fa-times me-2"></i>
                                                                            Retirer du tiers
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal d'ajout de tiers -->
<div class="modal fade" id="addTiersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-building text-success me-2"></i>
                    Ajouter un tiers au dossier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>
                            <i class="fas fa-list me-2"></i>
                            Tiers existants
                        </h6>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div id="available-tiers-list">
                                <!-- Chargé via AJAX -->
                                <div class="text-center p-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>
                            <i class="fas fa-plus me-2"></i>
                            Créer un nouveau tiers
                        </h6>
                        <form id="createNewTiersForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="mb-3">
                                <label for="new_tiers_denomination" class="form-label">
                                    Dénomination sociale <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="new_tiers_denomination" 
                                       name="denomination" 
                                       required 
                                       placeholder="Nom de l'entreprise">
                                <div class="invalid-feedback"></div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i>
                                Créer et ajouter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'ajout de contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus text-info me-2"></i>
                    Ajouter un contact au tiers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current-tiers-id">
                <div class="row">
                    <div class="col-md-6">
                        <h6>
                            <i class="fas fa-list me-2"></i>
                            Contacts existants
                        </h6>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <div id="available-contacts-list">
                                <!-- Chargé via AJAX -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>
                            <i class="fas fa-plus me-2"></i>
                            Créer un nouveau contact
                        </h6>
                        <form id="createNewContactForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="new_contact_nom" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="new_contact_nom" name="nom" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="new_contact_prenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="new_contact_prenom" name="prenom" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="new_contact_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="new_contact_email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-save me-2"></i>
                                Créer et ajouter
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    box-shadow: none;
}

.tiers-item, .contact-item {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.tiers-item:hover, .contact-item:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
}
</style>

<script>
const dossierId = <?= e($dossier['id']) ?>;

function showAddTiersModal() {
    const modal = new bootstrap.Modal(document.getElementById('addTiersModal'));
    loadAvailableTiers();
    modal.show();
}

function showAddContactModal(tiersId) {
    const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
    document.getElementById('current-tiers-id').value = tiersId;
    loadAvailableContacts(tiersId);
    modal.show();
}

function loadAvailableTiers() {
    fetch(`${window.baseUrl}tiers/available/${dossierId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('available-tiers-list');
            if (data.success && data.data.length > 0) {
                container.innerHTML = data.data.map(tiers => `
                    <div class="tiers-item" onclick="addExistingTiers(${tiers.id})">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-building text-primary me-2"></i>
                            <span>${tiers.denomination}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted text-center">Aucun tiers disponible</p>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('available-tiers-list').innerHTML = 
                '<p class="text-danger text-center">Erreur de chargement</p>';
        });
}

function loadAvailableContacts(tiersId) {
    fetch(`${window.baseUrl}contact/available/${tiersId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('available-contacts-list');
            if (data.success && data.data.length > 0) {
                container.innerHTML = data.data.map(contact => `
                    <div class="contact-item" onclick="addExistingContact(${tiersId}, ${contact.id})">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user text-info me-2"></i>
                            <div>
                                <div>${contact.prenom} ${contact.nom}</div>
                                <small class="text-muted">${contact.email}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted text-center">Aucun contact disponible</p>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('available-contacts-list').innerHTML = 
                '<p class="text-danger text-center">Erreur de chargement</p>';
        });
}

function addExistingTiers(tiersId) {
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    formData.append('dossier_id', dossierId);
    formData.append('tiers_id', tiersId);
    
    fetch(`${window.baseUrl}dossier/add-tiers`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

function addExistingContact(tiersId, contactId) {
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    formData.append('tiers_id', tiersId);
    formData.append('contact_id', contactId);
    
    fetch(`${window.baseUrl}tiers/add-contact`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

function removeTiersFromDossier(tiersId) {
    if (!confirm('Êtes-vous sûr de vouloir retirer ce tiers du dossier ?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    formData.append('dossier_id', dossierId);
    formData.append('tiers_id', tiersId);
    
    fetch(`${window.baseUrl}dossier/remove-tiers`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

function removeContactFromTiers(tiersId, contactId) {
    if (!confirm('Êtes-vous sûr de vouloir retirer ce contact du tiers ?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    formData.append('tiers_id', tiersId);
    formData.append('contact_id', contactId);
    
    fetch(`${window.baseUrl}tiers/remove-contact`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

function expandAllTiers() {
    const accordionButtons = document.querySelectorAll('.accordion-button.collapsed');
    accordionButtons.forEach(button => button.click());
}

function confirmDeleteDossier() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const message = document.getElementById('delete-message');
    const confirmBtn = document.getElementById('confirm-delete');
    
    message.textContent = `Êtes-vous sûr de vouloir supprimer le dossier "${document.querySelector('h1').textContent}" ?`;
    
    confirmBtn.onclick = function() {
        const formData = new FormData();
        formData.append('csrf_token', window.csrfToken);
        
        fetch(`${window.baseUrl}dossier/delete/${dossierId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = window.baseUrl;
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
        });
        
        modal.hide();
    };
    
    modal.show();
}

// Gestion des formulaires de création
document.getElementById('createNewTiersForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(`${window.baseUrl}tiers/create`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ajouter le nouveau tiers au dossier
            addExistingTiers(data.tiers.id);
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
});

document.getElementById('createNewContactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const tiersId = document.getElementById('current-tiers-id').value;
    
    fetch(`${window.baseUrl}contact/create`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ajouter le nouveau contact au tiers
            addExistingContact(tiersId, data.contact.id);
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
});
</script>