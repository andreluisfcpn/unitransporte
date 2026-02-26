<?php
// admin/api/get_buses.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
  $stmt = $pdo->query("SELECT * FROM buses ORDER BY identificador ASC");
  $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($buses);
} catch(Exception $e) {
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao obter ônibus: ' . $e->getMessage()]);
}
?>

