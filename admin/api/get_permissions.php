<?php
// admin/api/get_permissions.php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'User ID is required']);
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    $stmt = $pdo->prepare("SELECT us.id, us.dia_semana, us.bus_id, b.identificador AS busIdentificador 
                           FROM user_schedule us 
                           LEFT JOIN buses b ON us.bus_id = b.id 
                           WHERE us.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($permissions);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao obter permissões: ' . $e->getMessage()]);
}
?>

