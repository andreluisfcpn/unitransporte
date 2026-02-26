<?php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

// Decodifica o JSON recebido
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['identificador']) || !isset($data['ponto_partida']) || !isset($data['horario_ida']) || !isset($data['horario_volta'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Dados insuficientes para atualização']);
    exit;
}

$id = intval($data['id']);
$identificador = trim($data['identificador']);
$ponto_partida = trim($data['ponto_partida']);

// Converte os horários do formato ISO para o formato TIME
$horario_ida = date('H:i:s', strtotime($data['horario_ida']));
$horario_volta = date('H:i:s', strtotime($data['horario_volta']));
$itinerario = isset($data['itinerario']) ? trim($data['itinerario']) : '';

try {
    $stmt = $pdo->prepare("UPDATE buses SET identificador = :identificador, ponto_partida = :ponto_partida, horario_ida = :horario_ida, horario_volta = :horario_volta, itinerario = :itinerario WHERE id = :id");
    $stmt->execute([
        'identificador' => $identificador,
        'ponto_partida' => $ponto_partida,
        'horario_ida'   => $horario_ida,
        'horario_volta' => $horario_volta,
        'itinerario'    => $itinerario,
        'id'            => $id
    ]);
    echo json_encode(['message' => 'Ônibus atualizado com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao atualizar ônibus: ' . $e->getMessage()]);
}
?>

