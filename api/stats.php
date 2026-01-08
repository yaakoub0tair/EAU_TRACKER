<?php
// api/stats.php
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Badge.php';

$consumptionModel = new Consumption();
$profileModel = new UserProfile();
$badgeModel = new Badge();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Seul GET est autorisé']);
    exit;
}

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id manquant']);
    exit;
}

$userId = (int)$_GET['user_id'];

try {
    // Statistiques générales
    $dailyTotal = $consumptionModel->getDailyTotal($userId);
    $quotaTotal = $profileModel->calculateTotalQuota($userId);
    $wasteScore = $profileModel->getWasteScore($userId);
    $badges = $badgeModel->getUserBadges($userId);
    $badgeCount = $badgeModel->getUserBadgeCount($userId);
    
    // Consommation par activité
    $breakdown = $consumptionModel->getActivityBreakdown($userId);
    
    // Calcul du pourcentage
    $percentage = $quotaTotal > 0 ? ($dailyTotal / $quotaTotal) * 100 : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'daily_total' => round($dailyTotal, 2),
            'quota_total' => round($quotaTotal, 2),
            'percentage' => round($percentage, 1),
            'waste_score' => $wasteScore,
            'badges' => $badges,
            'badge_count' => $badgeCount,
            'breakdown' => $breakdown
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}