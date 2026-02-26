<?php
// admin/api/delete_permission.php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['id'])) {
  http_response_code(400);
  echo json_encode(['message' => 'ID de permissão inválido']);
  exit;
}

$permission_id = intval($data['id']);

try {
  $stmt = $pdo->prepare("DELETE FROM user_schedule WHERE id = :id");
  $stmt->execute(['id' => $permission_id]);
  echo json_encode(['message' => 'Permissão excluída com sucesso']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao excluir permissão: ' . $e->getMessage()]);
}
?>

