<?php
// config/config.php
// Configuration générale de l'application

define('APP_NAME', 'Gestion de Dossiers');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost:8888/test-gestion-dossiers/');

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Paris');

// Autoloader simple pour charger automatiquement les classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fonctions utilitaires globales
 */

// Redirection
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

// Messages flash
function flash($key, $message = null) {
    if ($message === null) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    $_SESSION['flash'][$key] = $message;
}

// Conserver les anciennes valeurs de formulaire
function old($key, $default = '') {
    $value = $_SESSION['old'][$key] ?? $default;
    unset($_SESSION['old'][$key]);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Sauvegarder les valeurs de formulaire pour réaffichage
function flashInputs($data) {
    $_SESSION['old'] = $data;
}

// Protection CSRF
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Échapper les données pour l'affichage
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Formater une date
function formatDate($date, $format = 'd/m/Y H:i') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

// Formater une date courte
function formatDateShort($date) {
    return formatDate($date, 'd/m/Y');
}

// Pagination simple
function paginate($currentPage, $totalItems, $itemsPerPage = 10) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Valider un email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Nettoyer une chaîne
function cleanString($string) {
    return trim(strip_tags($string));
}

// Debug rapide (à supprimer en production)
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

// Log simple pour le debug
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// Vérifier si on est en requête AJAX
function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Envoyer une réponse JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}