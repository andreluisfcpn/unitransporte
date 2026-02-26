<?php
// admin/api/get_users.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
  $stmt = $pdo->query("SELECT * FROM users WHERE role = 'usuario' ORDER BY id DESC");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($users as &$user) {
    // Obter schedule com o campo id
    $stmtSched = $pdo->prepare("SELECT us.id, us.dia_semana, us.bus_id, b.identificador as busIdentificador 
                                FROM user_schedule us 
                                JOIN buses b ON us.bus_id = b.id 
                                WHERE us.user_id = :user_id");
    $stmtSched->execute(['user_id' => $user['id']]);
    $user['schedule'] = $stmtSched->fetchAll(PDO::FETCH_ASSOC);
    // Concatena o caminho da foto se existir
    if (!empty($user['foto'])) {
      $user['foto'] = '/assets/uploads/' . $user['foto'];
    }
  }
  echo json_encode($users);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao obter usuários: ' . $e->getMessage()]);
}
?>

