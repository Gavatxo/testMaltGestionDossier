<?php
?>
<footer class="bg-light mt-5 py-4 border-top">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary">
                    <i class="fas fa-folder-open me-2"></i>
                    <?= APP_NAME ?>
                </h6>
                <p class="text-muted small mb-2">
                    Application de gestion de dossiers avec système de relations entre dossiers, tiers et contacts.
                </p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-code me-1"></i>
                    Développé avec PHP, MySQL et Bootstrap 5
                </p>
            </div>
            
            <div class="col-md-3">
                <h6 class="fw-bold">Liens rapides</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1">
                        <a href="<?= BASE_URL ?>" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="<?= BASE_URL ?>dossier" class="text-decoration-none">
                            <i class="fas fa-folder me-1"></i>Dossiers
                        </a>
                    </li>
                    <li class="mb-1">
                        <a href="<?= BASE_URL ?>dossier/create" class="text-decoration-none">
                            <i class="fas fa-plus me-1"></i>Nouveau dossier
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h6 class="fw-bold">Informations</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1">
                        <i class="fas fa-calendar me-1 text-muted"></i>
                        <span class="text-muted">Aujourd'hui : <?= formatDateShort(date('Y-m-d')) ?></span>
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-clock me-1 text-muted"></i>
                        <span class="text-muted" id="current-time"></span>
                    </li>
                    <li class="mb-1">
                        <i class="fas fa-server me-1 text-muted"></i>
                        <span class="text-muted">PHP <?= phpversion() ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="my-3">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted small mb-0">
                    &copy; <?= date('Y') ?> Test Technique - Gestion de Dossiers
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted small mb-0">
                    Version <?= APP_VERSION ?>
                    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
                        <span class="badge bg-warning text-dark ms-2">
                            <i class="fas fa-bug me-1"></i>DEBUG
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bouton de retour en haut -->
<button type="button" 
        class="btn btn-primary btn-floating btn-lg" 
        id="btn-back-to-top"
        style="position: fixed; bottom: 20px; right: 20px; display: none; z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Horloge en temps réel
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR');
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Mettre à jour l'heure toutes les secondes
updateTime();
setInterval(updateTime, 1000);

// Bouton retour en haut
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.getElementById('btn-back-to-top');
    
    if (backToTopButton) {
        // Afficher/masquer le bouton selon le scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        // Action du bouton
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
</script>

<style>
.btn-floating {
    border-radius: 50%;
    width: 56px;
    height: 56px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.btn-floating:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

footer a {
    color: #6c757d;
    transition: color 0.2s ease;
}

footer a:hover {
    color: #0d6efd;
}
</style>