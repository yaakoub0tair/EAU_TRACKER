<?php
// api/index.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Router simple
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Routes disponibles
$routes = [
    'profiles' => __DIR__ . '/profiles.php',
    'consumptions' => __DIR__ . '/consumptions.php',
    'stats' => __DIR__ . '/stats.php',
    'sync' => __DIR__ . '/sync.php'
];

// Obtenir le endpoint demandé
$endpoint = $uri[count($uri) - 1] ?? 'index';

if (isset($routes[$endpoint])) {
    require_once $routes[$endpoint];
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint non trouvé']);
}