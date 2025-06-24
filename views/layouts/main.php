<?php
$pageTitle = $title ?? 'Gestion de Dossiers';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Meta tags -->
    <meta name="description" content="Application de gestion de dossiers avec tiers et contacts">
    <meta name="author" content="Test Technique">
</head>
<body>
    <!-- Navigation -->
    <?php include 'navbar.php'; ?>
    
    <!-- Messages flash -->
    <?php if ($successMessage = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= e($successMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMessage = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= e($errorMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($warningMessage = flash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= e($warningMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($infoMessage = flash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= e($infoMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Contenu principal -->
    <main class="container-fluid mt-4">
      <?php
      // Inclure la vue spécifique
      $viewFile = "views/{$view}.php";
      if (file_exists($viewFile)) {
          include $viewFile;
      } else {
          echo "<div class='alert alert-danger'>";
          echo "<h4>Vue non trouvée</h4>";
          echo "<p>Le fichier <code>{$viewFile}</code> n'existe pas.</p>";
          echo "<p><strong>Vue demandée :</strong> {$view}</p>";
          echo "<p><a href='" . BASE_URL . "' class='btn btn-primary'>Retour à l'accueil</a></p>";
          echo "</div>";
      }
      ?>
  </main>
      
    <!-- Footer -->
    <?php include 'footer.php'; ?>
    
    <!-- Modales globales -->
    <div id="global-modals">
        <!-- Modal de confirmation de suppression -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="delete-message">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette action est irréversible.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-danger" id="confirm-delete">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de chargement -->
        <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mb-0">Traitement en cours...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé -->
    <script src="assets/js/app.js"></script>
    
    <!-- Token CSRF pour les requêtes AJAX -->
    <script>
        window.csrfToken = '<?= csrf_token() ?>';
        window.baseUrl = '<?= BASE_URL ?>';
    </script>
    
    <!-- Scripts additionnels de la page -->
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>