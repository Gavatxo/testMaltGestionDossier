<?php
// debug_route.php
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

echo "<h3>Debug du routage</h3>";
echo "<p><strong>REQUEST_URI:</strong> " . $request . "</p>";
echo "<p><strong>PATH:</strong> " . $path . "</p>";

$basePath = '/test-gestion-dossiers/';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

echo "<p><strong>PATH après basePath:</strong> " . $path . "</p>";

$segments = explode('/', trim($path, '/'));
echo "<p><strong>Segments:</strong> " . print_r($segments, true) . "</p>";

$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? '';

echo "<p><strong>Controller:</strong> '$controller'</p>";
echo "<p><strong>Action:</strong> '$action'</p>";
echo "<p><strong>Param:</strong> '$param'</p>";
?>