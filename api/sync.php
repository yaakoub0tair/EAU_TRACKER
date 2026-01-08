<?php
// api/sync.php
require_once __DIR__ . '/../services/TrackerService.php';

$trackerService = new TrackerService();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Seul POST est autorisé']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['consumptions']) || !is_array($data['consumptions'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Format de données invalide']);
        exit;
    }
    
    $result = $trackerService->syncOfflineData($data['consumptions']);
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
