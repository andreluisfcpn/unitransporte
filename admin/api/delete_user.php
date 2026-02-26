<?php
// admin/api/delete_user.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Tenta decodificar o JSON recebido
$data = json_decode(file_get_contents("php://input"), true);

// Se não houver dados via JSON, tenta usar $_POST
if (!$data) {
    $data = $_POST;
}

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'ID inválido']);
    exit;
}

$user_id = intval($data['id']);
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'ID inválido']);
    exit;
}

try {
    // Inicia transação para exclusão atômica
    $pdo->beginTransaction();

    // Exclui as viagens relacionadas ao usuário
    $stmt = $pdo->prepare("DELETE FROM trips WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Exclui os logs relacionados ao usuário
    $stmt = $pdo->prepare("DELETE FROM logs WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Exclui os agendamentos relacionados ao usuário
    $stmt = $pdo->prepare("DELETE FROM user_schedule WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Exclui o próprio usuário
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $pdo->commit();
    echo json_encode(['message' => 'Usuário e todos os dados relacionados foram excluídos com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
}
?>

