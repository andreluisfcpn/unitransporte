<?php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

// Obtém os dados corretamente (se for enviado como JSON ou via POST normal)
$data = json_decode(file_get_contents("php://input"), true);
$user_id = isset($data['id']) ? intval($data['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($user_id <= 0) {
  http_response_code(400);
  echo json_encode(['message' => 'Erro: ID do motorista inválido ou não informado.']);
  exit;
}

try {
    $pdo->beginTransaction();

    // Exclui todas as dependências do motorista antes de removê-lo
    $stmt = $pdo->prepare("DELETE FROM trips WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $stmt = $pdo->prepare("DELETE FROM logs WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $stmt = $pdo->prepare("DELETE FROM user_schedule WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Exclui o motorista
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'motorista'");
    $stmt->execute(['id' => $user_id]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['message' => 'Erro: Motorista não encontrado ou já excluído.']);
        exit;
    }

    $pdo->commit();
    echo json_encode(['message' => 'Motorista e todos os dados relacionados foram excluídos com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao excluir motorista: ' . $e->getMessage()]);
}
?>

