<?php
require_once 'config/config.php';

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

$basePath = '/test-gestion-dossiers/';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

if (empty($path) || $path === '/') {
    $path = '';
}

$segments = explode('/', trim($path, '/'));

$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? '';

try {
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
                if (is_numeric($action)) {
                    $dossierController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
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
                if (is_numeric($action)) {
                    $tierController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
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
                if (is_numeric($action)) {
                    $contactController->show($action);
                } else {
                    throw new Exception("Action non trouvée : {$action}");
                }
                break;
        }
    }
    
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
    
    elseif ($controller === 'dashboard') {
        require_once 'controllers/DossierController.php';
        $dossierController = new DossierController();
        $dossierController->index(); 
    }
    
    elseif ($controller === 'test' && $action === 'models') {
        include 'test_models.php';
        exit;
    }
    
    elseif ($controller === 'test' && $action === 'connection') {
        include 'test_connection.php';
        exit;
    }
    
    else {
        throw new Exception("Contrôleur non trouvé : {$controller}");
    }
    
} catch (Exception $e) {
    if (isAjax()) {
        jsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'error_type' => 'routing_error'
        ], 404);
    } else {
        $errorMessage = $e->getMessage();
        $errorCode = 404;
        
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
?>
