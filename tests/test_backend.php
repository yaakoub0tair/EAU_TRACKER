<?php
// tests/test_backend.php
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/../services/TrackerService.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/Consumption.php';
require_once __DIR__ . '/../models/ActivityReference.php';  // ← AJOUTE CETTE LIGNE
require_once __DIR__ . '/../models/Alert.php';              // ← ET CELLE-CI

echo "🧪 Tests Backend EauTrack Rural\n";
echo str_repeat("=", 50) . "\n\n";

// Test 0: Vérifier la connexion
echo "Test 0: Vérification connexion DB... ";
try {
    $db = Database::getInstance()->getConnection();
    echo "✅ OK\n\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "\n💡 Solution: Vérifie que MySQL tourne et que la DB existe\n";
    exit(1);
}

// Test 1: Lister les profils existants
echo "Test 1: Liste des profils existants... \n";
$profileModel = new UserProfile();
try {
    $profiles = $profileModel->findAll();
    echo "✅ " . count($profiles) . " profil(s) trouvé(s)\n";
    foreach ($profiles as $p) {
        echo "   - ID: {$p['id']}, Nom: {$p['nom']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// Test 2: Créer un nouveau profil
echo "Test 2: Création d'un nouveau profil... ";
try {
    $profileId = $profileModel->create([
        'nom' => 'Test Automatique ' . date('H:i:s'),
        'nb_personnes' => 3,
        'type_habitation' => 'maison',
        'quota_jour' => 150,
        'village' => 'Nador'
    ]);
    echo "✅ OK (ID: $profileId)\n\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Vérifier les activités
echo "Test 3: Vérification activités de référence... ";
$activityModel = new ActivityReference();
try {
    $activities = $activityModel->findAll();
    echo "✅ " . count($activities) . " activité(s) disponible(s)\n";
    if (count($activities) > 0) {
        echo "   Première activité: {$activities[0]['name']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// Test 4: Ajouter une consommation
echo "Test 4: Ajout d'une consommation... ";
$trackerService = new TrackerService();
try {
    $result = $trackerService->addConsumption([
        'user_id' => $profileId,
        'activity_id' => 1,
        'volume' => 45.5
    ]);
    
    if ($result['success']) {
        echo "✅ OK (ID: {$result['consumption_id']})\n\n";
    } else {
        echo "❌ ERREUR: {$result['error']}\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// Test 5: Vérifier le quota
echo "Test 5: Calcul du quota total... ";
try {
    $quota = $profileModel->calculateTotalQuota($profileId);
    echo "✅ OK (Quota: {$quota}L pour 3 personnes)\n\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// Test 6: Obtenir les stats du jour
echo "Test 6: Récupération des statistiques... ";
$consumptionModel = new Consumption();
try {
    $dailyTotal = $consumptionModel->getDailyTotal($profileId);
    $breakdown = $consumptionModel->getActivityBreakdown($profileId);
    
    echo "✅ OK\n";
    echo "   Total du jour: {$dailyTotal}L\n";
    echo "   Activités: " . count($breakdown) . "\n\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

// Test 7: Ajouter une grosse consommation pour tester les alertes
echo "Test 7: Test du système d'alertes (consommation élevée)... ";
try {
    $result = $trackerService->addConsumption([
        'user_id' => $profileId,
        'activity_id' => 1,
        'volume' => 400
    ]);
    
    echo "✅ OK\n";
    
    // Vérifier les alertes
    $alertModel = new Alert();
    $alerts = $alertModel->getActiveAlerts($profileId);
    echo "   Alertes générées: " . count($alerts) . "\n";
    foreach ($alerts as $alert) {
        echo "   - Niveau {$alert['level']}: {$alert['message']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
}

echo str_repeat("=", 50) . "\n";
echo "✨ Tests terminés avec succès!\n";