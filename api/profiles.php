<?php
// api/profiles.php
require_once __DIR__ . '/../models/UserProfile.php';

$profileModel = new UserProfile();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Récupérer un profil spécifique
                $profile = $profileModel->findById((int)$_GET['id']);
                if ($profile) {
                    echo json_encode(['success' => true, 'data' => $profile]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Profil non trouvé']);
                }
            } else {
                // Récupérer tous les profils
                $profiles = $profileModel->findAll();
                echo json_encode(['success' => true, 'data' => $profiles]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $errors = $profileModel->validate($data);
            
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => $errors]);
                break;
            }
            
            $id = $profileModel->create($data);
            echo json_encode([
                'success' => true,
                'message' => 'Profil créé avec succès',
                'id' => $id
            ]);
            break;
            
        case 'PUT':
            parse_str(file_get_contents('php://input'), $data);
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                break;
            }
            
            $id = (int)$data['id'];
            unset($data['id']);
            
            $success = $profileModel->update($id, $data);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Profil mis à jour' : 'Erreur lors de la mise à jour'
            ]);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID manquant']);
                break;
            }
            
            $success = $profileModel->delete((int)$_GET['id']);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Profil supprimé' : 'Erreur lors de la suppression'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}