<?php
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/users-controller.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!$action) {
    echo json_encode(['success'=>false,'message'=>'No action specified']); exit;
}

// Simple auth guard: ensure admin session exists (project may have different auth)
// Use project's SessionManager to check login state
require_once __DIR__ . '/../../config/SessionManager.php';
\SessionManager::init();
if (!\SessionManager::isLoggedIn()) {
    echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit;
}

try {
    switch ($action) {
        case 'get':
            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $row = UsersController::getById($id);
            if (!$row) throw new Exception('User not found');
            echo json_encode(['success'=>true,'data'=>$row]);
            break;
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $data = [];
            foreach (['full_name','email','gender','location','contact_number','birthday','is_verified'] as $k) {
                if (isset($_POST[$k])) $data[$k] = $_POST[$k];
            }
            $ok = UsersController::update($id, $data);
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Invalid id');
            $ok = UsersController::bulk('delete', [$id]);
            echo json_encode(['success'=>($ok>0),'affected'=>$ok]);
            break;
        case 'bulk':
            $bulkAction = $_POST['bulk_action'] ?? '';
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids)) $ids = explode(',', (string)$ids);
            $affected = UsersController::bulk($bulkAction, $ids);
            echo json_encode(['success'=>($affected>0),'affected'=>$affected]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}


?>