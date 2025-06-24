<?php
// debug_routing.php - Pour diagnostiquer le problème de routage
require_once 'config/config.php';

echo "<h2>🔍 Debug du routage</h2>";

// Tester l'URL actuelle
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

echo "<strong>REQUEST_URI:</strong> " . htmlspecialchars($request) . "<br>";
echo "<strong>PATH:</strong> " . htmlspecialchars($path) . "<br>";

$basePath = '/test-gestion-dossiers/';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

echo "<strong>PATH après basePath:</strong> " . htmlspecialchars($path) . "<br>";

$segments = explode('/', trim($path, '/'));
echo "<strong>Segments:</strong> " . print_r($segments, true) . "<br>";

$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? '';

echo "<strong>Controller:</strong> '" . htmlspecialchars($controller) . "'<br>";
echo "<strong>Action:</strong> '" . htmlspecialchars($action) . "'<br>";
echo "<strong>Param:</strong> '" . htmlspecialchars($param) . "'<br><br>";

// Vérifier l'existence des fichiers
echo "<h3>📁 Vérification des fichiers :</h3>";

$files_to_check = [
    'controllers/DossierController.php',
    'views/dossiers/create.php',
    'views/layouts/main.php',
    'models/Dossier.php',
    'models/Tiers.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    echo "{$status} {$file}<br>";
}

echo "<br><h3>🧪 Test du contrôleur :</h3>";

try {
    require_once 'controllers/DossierController.php';
    echo "✅ DossierController chargé<br>";
    
    $controller = new DossierController();
    echo "✅ Instance DossierController créée<br>";
    
    if (method_exists($controller, 'create')) {
        echo "✅ Méthode create() existe<br>";
    } else {
        echo "❌ Méthode create() n'existe pas<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<br><h3>🔗 Liens de test :</h3>";
echo "<a href='index.php'>🏠 Accueil</a><br>";
echo "<a href='dossier'>📁 Liste dossiers</a><br>";
echo "<a href='dossier/create'>➕ Créer dossier</a><br>";
echo "<a href='test_models.php'>🧪 Test modèles</a><br>";
?>