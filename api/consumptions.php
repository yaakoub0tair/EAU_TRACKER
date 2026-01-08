<?php
// api/consumptions.php
require_once __DIR__ . '/../services/TrackerService.php';
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/../models/Alert.php';

$trackerService = new TrackerService();
$consumptionModel = new Consumption();
$alertModel = new Alert();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['user_id'])) {
                $userId = (int)$_GET['id'];
                
                // Statistiques du jour
                $dailyTotal = $consumptionModel->getDailyTotal($userId);
                $breakdown = $consumptionModel->getActivityBreakdown($userId);
                $alerts = $alertModel->getActiveAlerts($userId);
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'daily_total' => $dailyTotal,
                        'breakdown' => $breakdown,
                        'alerts' => $alerts
                    ]
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'user_id manquant']);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $trackerService->addConsumption($data);
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}