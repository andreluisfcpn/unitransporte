<?php
// admin/api/add_permission.php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['user_id']) || !isset($data['dia']) || !isset($data['bus_id'])) {
  http_response_code(400);
  echo json_encode(['message' => 'Dados insuficientes']);
  exit;
}

$user_id = intval($data['user_id']);
$dia = trim($data['dia']);
$bus_id = intval($data['bus_id']);

try {
  // Verifica se já existe permissão para este dia para este usuário
  //$stmt = $pdo->prepare("SELECT * FROM user_schedule WHERE user_id = :user_id AND dia_semana = :dia");
  //$stmt->execute(['user_id' => $user_id, 'dia' => $dia]);
  //if ($stmt->fetch()) {
    //http_response_code(400);
    //echo json_encode(['message' => 'Permissão já existe para este dia']);
    //exit;
  //}
  $stmt = $pdo->prepare("INSERT INTO user_schedule (user_id, dia_semana, bus_id) VALUES (:user_id, :dia, :bus_id)");
  $stmt->execute(['user_id' => $user_id, 'dia' => $dia, 'bus_id' => $bus_id]);
  echo json_encode(['message' => 'Permissão adicionada com sucesso']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao adicionar permissão: ' . $e->getMessage()]);
}
?>

