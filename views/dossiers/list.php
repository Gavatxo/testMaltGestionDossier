<?php
// views/dossiers/list.php - Liste des dossiers
$view = 'dossiers/list'; // Pour le layout principal
?>

<!-- En-tête de page -->
<div class="row align-items-center mb-4">
    <div class="col">
        <h1 class="h2 mb-0">
            <i class="fas fa-folder text-primary me-2"></i>
            Gestion des Dossiers
        </h1>
        <p class="text-muted mb-0">Visualisez et gérez tous vos dossiers</p>
    </div>
    <div class="col-auto">
        <a href="<?= BASE_URL ?>dossier/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nouveau dossier
        </a>
    </div>
</div>

<!-- Statistiques -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-folder text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1"><?= e($stats['total_dossiers'] ?? 0) ?></h3>
                        <p class="text-muted small mb-0">Dossiers totaux</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-building text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1"><?= e($stats['total_tiers'] ?? 0) ?></h3>
                        <p class="text-muted small mb-0">Tiers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-users text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1"><?= e($stats['total_contacts'] ?? 0) ?></h3>
                        <p class="text-muted small mb-0">Contacts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-calendar text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-1"><?= e($stats['dossiers_ce_mois'] ?? 0) ?></h3>
                        <p class="text-muted small mb-0">Ce mois</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtres et recherche -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-6">
                <label for="searchDossiers" class="form-label small fw-bold">
                    <i class="fas fa-search me-1"></i>Recherche rapide
                </label>
                <input type="text" 
                       class="form-control" 
                       id="searchDossiers" 
                       placeholder="Rechercher par référence, tiers ou contact...">
            </div>
            <div class="col-md-3">
                <label for="sortBy" class="form-label small fw-bold">
                    <i class="fas fa-sort me-1"></i>Trier par
                </label>
                <select class="form-select" id="sortBy">
                    <option value="date_desc">Plus récents</option>
                    <option value="date_asc">Plus anciens</option>
                    <option value="reference_asc">Référence A-Z</option>
                    <option value="reference_desc">Référence Z-A</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-eraser me-2"></i>Effacer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Liste des dossiers -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-muted"></i>
                Liste des dossiers
                <span class="badge bg-primary ms-2" id="dossiers-count">
                    <?= count($dossiers ?? []) ?>
                </span>
            </h5>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm active" id="view-cards">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="view-table">
                    <i class="fas fa-table"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body" id="dossiers-container">
        <?php if (empty($dossiers)): ?>
            <!-- État vide -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-folder-open text-muted" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-muted">Aucun dossier trouvé</h5>
                <p class="text-muted mb-4">
                    Commencez par créer votre premier dossier pour organiser vos tiers et contacts.
                </p>
                <a href="<?= BASE_URL ?>dossier/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Créer un dossier
                </a>
            </div>
        <?php else: ?>
            <!-- Vue en cartes (par défaut) -->
            <div id="cards-view">
                <div class="row" id="dossiers-cards">
                    <?php foreach ($dossiers as $dossier): ?>
                        <div class="col-lg-4 col-md-6 mb-4 dossier-card" 
                             data-reference="<?= e(strtolower($dossier['reference'])) ?>"
                             data-date="<?= e($dossier['date_creation']) ?>">
                            <div class="card h-100 border-0 shadow-sm hover-shadow">
                                <div class="card-header bg-gradient-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-folder me-2"></i>
                                            <?= e($dossier['reference']) ?>
                                        </h6>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm dropdown-toggle" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="<?= BASE_URL ?>dossier/<?= e($dossier['id']) ?>">
                                                        <i class="fas fa-eye me-2"></i>Voir détail
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="#" 
                                                       onclick="confirmDelete('dossier', <?= e($dossier['id']) ?>, '<?= e($dossier['reference']) ?>')">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="small text-muted">Tiers</div>
                                            <div class="h5 text-primary mb-0">
                                                <?= e($dossier['nb_tiers'] ?? 0) ?>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Contacts</div>
                                            <div class="h5 text-success mb-0">
                                                <?= e($dossier['nb_contacts'] ?? 0) ?>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Statut</div>
                                            <div class="small">
                                                <span class="badge bg-success">Actif</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="small text-muted mb-3">
                                        <i class="fas fa-calendar me-1"></i>
                                        Créé le <?= formatDateShort($dossier['date_creation']) ?>
                                    </div>
                                    
                                    <?php if (!empty($dossier['date_modification']) && $dossier['date_modification'] !== $dossier['date_creation']): ?>
                                        <div class="small text-muted mb-3">
                                            <i class="fas fa-edit me-1"></i>
                                            Modifié le <?= formatDateShort($dossier['date_modification']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="<?= BASE_URL ?>dossier/<?= e($dossier['id']) ?>" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-arrow-right me-2"></i>
                                        Voir le dossier
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Vue en tableau (masquée par défaut) -->
            <div id="table-view" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Date création</th>
                                <th class="text-center">Tiers</th>
                                <th class="text-center">Contacts</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="dossiers-table-body">
                            <?php foreach ($dossiers as $dossier): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-folder text-primary me-2"></i>
                                            <div>
                                                <div class="fw-bold"><?= e($dossier['reference']) ?></div>
                                                <div class="small text-muted">
                                                    <span class="badge bg-success">Actif</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= formatDateShort($dossier['date_creation']) ?></div>
                                        <div class="small text-muted"><?= formatDate($dossier['date_creation'], 'H:i') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= e($dossier['nb_tiers'] ?? 0) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= e($dossier['nb_contacts'] ?? 0) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>dossier/<?= e($dossier['id']) ?>" 
                                               class="btn btn-outline-primary btn-sm"
                                               title="Voir détail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    title="Supprimer"
                                                    onclick="confirmDelete('dossier', <?= e($dossier['id']) ?>, '<?= e($dossier['reference']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.dossier-card {
    transition: all 0.3s ease;
}

.table th {
    font-weight: 600;
    color: #495057;
    border-top: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basculer entre les vues
    const viewCards = document.getElementById('view-cards');
    const viewTable = document.getElementById('view-table');
    const cardsView = document.getElementById('cards-view');
    const tableView = document.getElementById('table-view');
    
    viewCards.addEventListener('click', function() {
        viewCards.classList.add('active');
        viewTable.classList.remove('active');
        cardsView.style.display = 'block';
        tableView.style.display = 'none';
    });
    
    viewTable.addEventListener('click', function() {
        viewTable.classList.add('active');
        viewCards.classList.remove('active');
        cardsView.style.display = 'none';
        tableView.style.display = 'block';
    });
    
    // Recherche en temps réel
    const searchInput = document.getElementById('searchDossiers');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterDossiers();
        });
    }
    
    // Tri
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortDossiers(this.value);
        });
    }
});

function filterDossiers() {
    const searchTerm = document.getElementById('searchDossiers').value.toLowerCase();
    const cards = document.querySelectorAll('.dossier-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const reference = card.dataset.reference || '';
        const isVisible = reference.includes(searchTerm);
        
        card.style.display = isVisible ? 'block' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Mettre à jour le compteur
    document.getElementById('dossiers-count').textContent = visibleCount;
}

function sortDossiers(sortBy) {
    const container = document.getElementById('dossiers-cards');
    const cards = Array.from(container.querySelectorAll('.dossier-card'));
    
    cards.sort((a, b) => {
        switch (sortBy) {
            case 'date_asc':
                return new Date(a.dataset.date) - new Date(b.dataset.date);
            case 'date_desc':
                return new Date(b.dataset.date) - new Date(a.dataset.date);
            case 'reference_asc':
                return a.dataset.reference.localeCompare(b.dataset.reference);
            case 'reference_desc':
                return b.dataset.reference.localeCompare(a.dataset.reference);
            default:
                return 0;
        }
    });
    
    // Réorganiser les cartes
    cards.forEach(card => container.appendChild(card));
}

function clearFilters() {
    document.getElementById('searchDossiers').value = '';
    document.getElementById('sortBy').value = 'date_desc';
    filterDossiers();
    sortDossiers('date_desc');
}

function confirmDelete(type, id, name) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const message = document.getElementById('delete-message');
    const confirmBtn = document.getElementById('confirm-delete');
    
    message.textContent = `Êtes-vous sûr de vouloir supprimer le dossier "${name}" ?`;
    
    confirmBtn.onclick = function() {
        deleteDossier(id);
        modal.hide();
    };
    
    modal.show();
}

function deleteDossier(id) {
    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    
    fetch(`${window.baseUrl}dossier/delete/${id}`, {
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
</script>