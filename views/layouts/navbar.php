<?php
// views/layouts/navbar.php - Barre de navigation
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Logo/Brand -->
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
            <i class="fas fa-folder-open me-2"></i>
            <?= APP_NAME ?>
        </a>
        
        <!-- Bouton mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menu de navigation -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= empty($_GET) ? 'active' : '' ?>" href="<?= BASE_URL ?>">
                        <i class="fas fa-home me-1"></i>
                        Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>dossier">
                        <i class="fas fa-folder me-1"></i>
                        Dossiers
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus me-1"></i>
                        Créer
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>dossier/create">
                                <i class="fas fa-folder-plus me-2 text-primary"></i>
                                Nouveau dossier
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="showCreateTiersModal()">
                                <i class="fas fa-building me-2 text-success"></i>
                                Nouveau tiers
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="showCreateContactModal()">
                                <i class="fas fa-user-plus me-2 text-info"></i>
                                Nouveau contact
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <!-- Barre de recherche -->
            <div class="d-flex align-items-center">
                <div class="search-container position-relative me-3">
                    <input type="text" 
                           class="form-control search-input" 
                           id="globalSearch" 
                           placeholder="Rechercher..."
                           style="width: 250px;">
                    <i class="fas fa-search search-icon"></i>
                    
                    <!-- Résultats de recherche -->
                    <div class="search-results position-absolute w-100 bg-white border rounded shadow-lg" 
                         id="searchResults" 
                         style="top: 100%; z-index: 1050; display: none; max-height: 400px; overflow-y: auto;">
                    </div>
                </div>
                
                <!-- Statistiques rapides -->
                <div class="navbar-text text-light me-3 d-none d-lg-block">
                    <small>
                        <span class="badge bg-light text-dark me-1" id="stats-dossiers">
                            <i class="fas fa-folder me-1"></i>0
                        </span>
                        <span class="badge bg-light text-dark me-1" id="stats-tiers">
                            <i class="fas fa-building me-1"></i>0
                        </span>
                        <span class="badge bg-light text-dark" id="stats-contacts">
                            <i class="fas fa-users me-1"></i>0
                        </span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Modales pour création rapide -->
<!-- Modal création tiers -->
<div class="modal fade" id="createTiersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-building text-success me-2"></i>
                    Créer un nouveau tiers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTiersForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label for="tiers_denomination" class="form-label">
                            Dénomination sociale <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="tiers_denomination" 
                               name="denomination" 
                               required 
                               placeholder="Nom de l'entreprise">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Le tiers sera créé et pourra être associé aux dossiers.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Créer le tiers
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal création contact -->
<div class="modal fade" id="createContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus text-info me-2"></i>
                    Créer un nouveau contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createContactForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_nom" class="form-label">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contact_nom" 
                                   name="nom" 
                                   required 
                                   placeholder="Nom de famille">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_prenom" class="form-label">
                                Prénom <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contact_prenom" 
                                   name="prenom" 
                                   required 
                                   placeholder="Prénom">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="contact_email" 
                               name="email" 
                               required 
                               placeholder="email@exemple.com">
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            L'email doit être unique dans le système.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-2"></i>Créer le contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.search-container .search-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

.search-input {
    padding-right: 35px;
}

.search-results {
    max-height: 400px;
    overflow-y: auto;
}

.search-result-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-type {
    font-size: 0.8em;
    color: #6c757d;
}
</style>