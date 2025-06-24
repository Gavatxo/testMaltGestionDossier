<?php
$title = 'Erreur ' . ($errorCode ?? '500');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - <?= APP_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
        }
        .error-code {
            font-size: 2.5rem;
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="error-card">
                        <div class="card-body text-center p-5">
                            <!-- Icône d'erreur -->
                            <div class="error-icon mb-4">
                                <?php
                                $errorCode = $errorCode ?? 500;
                                switch ($errorCode) {
                                    case 404:
                                        echo '<i class="fas fa-search"></i>';
                                        break;
                                    case 403:
                                        echo '<i class="fas fa-lock"></i>';
                                        break;
                                    case 500:
                                    default:
                                        echo '<i class="fas fa-exclamation-triangle"></i>';
                                        break;
                                }
                                ?>
                            </div>
                            
                            <!-- Code d'erreur -->
                            <div class="error-code mb-3">
                                <?= e($errorCode) ?>
                            </div>
                            
                            <!-- Titre d'erreur -->
                            <h2 class="h4 mb-3">
                                <?php
                                switch ($errorCode) {
                                    case 404:
                                        echo 'Page non trouvée';
                                        break;
                                    case 403:
                                        echo 'Accès interdit';
                                        break;
                                    case 500:
                                    default:
                                        echo 'Erreur interne';
                                        break;
                                }
                                ?>
                            </h2>
                            
                            <!-- Message d'erreur -->
                            <p class="text-muted mb-4">
                                <?php if (isset($errorMessage)): ?>
                                    <?= e($errorMessage) ?>
                                <?php else: ?>
                                    <?php
                                    switch ($errorCode) {
                                        case 404:
                                            echo 'La page que vous recherchez n\'existe pas ou a été déplacée.';
                                            break;
                                        case 403:
                                            echo 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.';
                                            break;
                                        case 500:
                                        default:
                                            echo 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.';
                                            break;
                                    }
                                    ?>
                                <?php endif; ?>
                            </p>
                            
                            <!-- Détails d'erreur en mode debug -->
                            <?php if (isset($errorDetails) && !empty($errorDetails)): ?>
                                <div class="alert alert-warning text-start mt-4">
                                    <h6><i class="fas fa-bug me-2"></i>Détails de debug :</h6>
                                    <ul class="list-unstyled small mb-0">
                                        <?php foreach ($errorDetails as $key => $value): ?>
                                            <li><strong><?= e($key) ?>:</strong> <?= e($value) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="d-grid gap-2 d-md-block">
                                <button type="button" class="btn btn-secondary" onclick="history.back()">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour
                                </button>
                                <a href="<?= BASE_URL ?>" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>
                                    Accueil
                                </a>
                            </div>
                            
                            <!-- Aide -->
                            <div class="mt-4 pt-3 border-top">
                                <p class="small text-muted mb-0">
                                    Si le problème persiste, contactez l'administrateur.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
