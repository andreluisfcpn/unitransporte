<?php
// admin/api/delete_bus.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Lê o corpo da requisição como JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se o método é POST e se o parâmetro 'id' foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'ID inválido']);
    exit;
}

$bus_id = intval($data['id']);
$override = isset($data['override']) ? intval($data['override']) : 0;

try {
    // Verifica se existem registros na tabela trips associados a esse ônibus
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE bus_id = :bus_id");
    $stmt->execute(['bus_id' => $bus_id]);
    $tripCount = $stmt->fetchColumn();

    // Verifica se existem registros na tabela user_schedule associados a esse ônibus
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_schedule WHERE bus_id = :bus_id");
    $stmt->execute(['bus_id' => $bus_id]);
    $scheduleCount = $stmt->fetchColumn();

    // Se existirem dependências e não foi enviado override, retorna aviso
    if (($tripCount > 0 || $scheduleCount > 0) && $override === 0) {
        http_response_code(400);
        echo json_encode([
            'message' => 'Este ônibus possui permissões e viagens associadas. Ao excluí-lo, todas as permissões serão revogadas e os dados de viagem serão perdidos. Deseja realmente excluir?',
            'requires_override' => true
        ]);
        exit;
    }

    // Inicia transação para exclusão atômica
    $pdo->beginTransaction();

    // Remove os registros associados na tabela trips
    $stmt = $pdo->prepare("DELETE FROM trips WHERE bus_id = :bus_id");
    $stmt->execute(['bus_id' => $bus_id]);

    // Remove os registros associados na tabela user_schedule
    $stmt = $pdo->prepare("DELETE FROM user_schedule WHERE bus_id = :bus_id");
    $stmt->execute(['bus_id' => $bus_id]);

    // Exclui o ônibus da tabela buses
    $stmt = $pdo->prepare("DELETE FROM buses WHERE id = :id");
    $stmt->execute(['id' => $bus_id]);

    $pdo->commit();
    echo json_encode(['message' => 'Ônibus excluído com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao excluir ônibus: ' . $e->getMessage()]);
}
?>

