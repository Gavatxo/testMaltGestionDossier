<?php
require_once 'config/config.php';

// Récupérer l'URL demandée
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// DEBUG: Afficher l'URL demandée
// echo "<h3>Debug du routage</h3>";
// echo "<p><strong>REQUEST_URI:</strong> " . $request . "</p>";
// echo "<p><strong>PATH:</strong> " . $path . "</p>";


// Enlever le chemin de base si l'application est dans un sous-dossier
$basePath = '/test-gestion-dossiers/';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Si path est vide après suppression du basePath, on est à la racine
if (empty($path) || $path === '/') {
    $path = '';
}

// Séparer le chemin en segments
$segments = explode('/', trim($path, '/'));

// Premier segment = contrôleur, deuxième = action, troisième = paramètre
$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? '';

// DEBUG: Ajoutez ces lignes temporairement pour voir ce qui se passe
// echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
// echo "Path: " . $path . "<br>";
// echo "Controller: " . $controller . "<br>";
// echo "Action: " . $action . "<br>";
// die();

try {
    // Routes pour les dossiers
    if (empty($controller) || $controller === 'dossier') {
        require_once 'controllers/DossierController.php';
        $dossierController = new DossierController();
        
        switch ($action) {
            case '':
            case 'index':
                $dossierController->index();
                break;
                
            case 'create':
                $dossierController->create();
                break;
                
            case 'store':
                $dossierController->store();
                break;
                
            case 'add-tiers':
                $dossierController->addTiers();
                break;
                
            case 'remove-tiers':
                $dossierController->removeTiers();
                break;
                
            case 'delete':
                $dossierController->delete($param);
                break;
                
            case 'search':
                $dossierController->search();
                break;
                
            case 'api':
                $dossierController->api();
                break;
                
            default:
                // Si l'action est un nombre, c'est probablement un ID pour show
                if (is_numeric($action)) {
                    $dossierController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
    // Routes pour les tiers
    elseif ($controller === 'tiers') {
        require_once 'controllers/TierController.php';
        $tierController = new TierController();
        
        switch ($action) {
            case 'create':
                $tierController->create();
                break;
                
            case 'update':
                $tierController->update();
                break;
                
            case 'delete':
                $tierController->delete();
                break;
                
            case 'add-contact':
                $tierController->addContact();
                break;
                
            case 'remove-contact':
                $tierController->removeContact();
                break;
                
            case 'search':
                $tierController->search();
                break;
                
            case 'api':
                $tierController->api();
                break;
                
            case 'available':
                $tierController->available($param);
                break;
                
            default:
                // Si l'action est un nombre, c'est un ID pour show
                if (is_numeric($action)) {
                    $tierController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
    // Routes pour les contacts
    elseif ($controller === 'contact') {
        require_once 'controllers/ContactController.php';
        $contactController = new ContactController();
        
        switch ($action) {
            case 'create':
                $contactController->create();
                break;
                
            case 'update':
                $contactController->update();
                break;
                
            case 'delete':
                $contactController->delete();
                break;
                
            case 'search':
                $contactController->search();
                break;
                
            case 'api':
                $contactController->api();
                break;
                
            case 'available':
                $contactController->available($param);
                break;
                
            case 'validate-email':
                $contactController->validateEmail();
                break;
                
            case 'by-letter':
                $contactController->byLetter($param);
                break;
                
            default:
                // Si l'action est un nombre, c'est un ID pour show
                if (is_numeric($action)) {
                    $contactController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
    // Route pour la recherche globale
    elseif ($controller === 'search') {
        require_once 'controllers/SearchController.php';
        $searchController = new SearchController();
        
        switch ($action) {
            case 'global':
                $searchController->global();
                break;
                
            case 'advanced':
                $searchController->advanced();
                break;
                
            case 'suggestions':
                $searchController->suggestions();
                break;
                
            case 'recent':
                $searchController->recent();
                break;
                
            case 'stats':
                $searchController->stats();
                break;
                
            default:
                $searchController->global();
                break;
        }
    }
    
    // Route pour les statistiques/dashboard
    elseif ($controller === 'dashboard') {
        require_once 'controllers/DossierController.php';
        $dossierController = new DossierController();
        $dossierController->index(); // Afficher le dashboard principal
    }
    
    // Route pour les tests (en développement)
    elseif ($controller === 'test' && $action === 'models') {
        include 'test_models.php';
        exit;
    }
    
    elseif ($controller === 'test' && $action === 'connection') {
        include 'test_connection.php';
        exit;
    }
    
    // Route inconnue
    else {
        throw new Exception("Contrôleur non trouvé : {$controller}");
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
    if (isAjax()) {
        // Réponse JSON pour les requêtes AJAX
        jsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'error_type' => 'routing_error'
        ], 404);
    } else {
        // Affichage d'une page d'erreur pour les requêtes normales
        $errorMessage = $e->getMessage();
        $errorCode = 404;
        
        // En développement, afficher plus de détails
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $errorDetails = [
                'Controller' => $controller,
                'Action' => $action,
                'Param' => $param,
                'Path' => $path,
                'Segments' => $segments
            ];
        }
        
        include 'views/layouts/error.php';
    }
}

// Fonctions utilitaires pour le routage

/**
 * Vérifier si un contrôleur existe
 */
function controllerExists($controller) {
    $controllerFile = "controllers/" . ucfirst($controller) . "Controller.php";
    return file_exists($controllerFile);
}

/**
 * Nettoyer et valider les paramètres d'URL
 */
function sanitizeUrlParam($param) {
    return preg_replace('/[^a-zA-Z0-9\-_]/', '', $param);
}

/**
 * Redirection avec code de statut
 */
function redirectWithStatus($url, $status = 302) {
    http_response_code($status);
    redirect($url);
}

/**
 * Générer une URL pour une route
 */
function route($controller, $action = '', $param = '') {
    $url = BASE_URL;
    
    if (!empty($controller)) {
        $url .= $controller;
        
        if (!empty($action)) {
            $url .= '/' . $action;
            
            if (!empty($param)) {
                $url .= '/' . $param;
            }
        }
    }
    
    return $url;
}

/**
 * Créer un lien HTML avec la route
 */
function linkTo($controller, $action = '', $param = '', $text = '', $class = '') {
    $url = route($controller, $action, $param);
    $text = $text ?: ucfirst($controller);
    $class = $class ? " class=\"{$class}\"" : '';
    
    return "<a href=\"{$url}\"{$class}>{$text}</a>";
}
?>