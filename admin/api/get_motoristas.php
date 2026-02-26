<?php
// /admin/api/get_motoristas.php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'motorista' ORDER BY id DESC");
    $motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($motoristas as &$m) {
      if(!empty($m['foto'])){
         // Se a foto não tem o caminho, ajusta
         if(substr($m['foto'], 0, 1) !== '/'){
             $m['foto'] = '/assets/uploads/' . $m['foto'];
         }
      }
    }
    echo json_encode($motoristas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao obter motoristas: ' . $e->getMessage()]);
}
?>

