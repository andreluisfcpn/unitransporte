<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'motorista') {
  http_response_code(403);
  echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
  exit;
}

if (!isset($_POST['qrData'])) {
  echo json_encode(['status' => 'erro', 'mensagem' => 'QR Data não fornecido.']);
  exit;
}

$qrDataEncoded = trim($_POST['qrData']);
$qrDataDecoded = rawurldecode($qrDataEncoded);
$userData = json_decode($qrDataDecoded, true);

if (!$userData || !isset($userData['id'])) {
  echo json_encode(['status' => 'erro', 'mensagem' => 'QR Code inválido.']);
  exit;
}

$userId = intval($userData['id']);

// Log para verificar o ID do usuário recebido
error_log("Buscando usuário com ID: $userId");

$stmt = $pdo->prepare("SELECT id, nome, email, turno, cpf, foto FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Log para depuração
error_log("Consulta SQL para buscar usuário: SELECT id, nome, email, turno, cpf, foto FROM users WHERE id = $userId LIMIT 1");

// Verificar a consulta SQL direta para diagnóstico
if (!$user) {
  error_log("Usuário com ID $userId não encontrado no banco de dados");
  echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado.']);
  exit;
} else {
  // Log dos dados do usuário, incluindo a foto
  error_log("Dados do usuário encontrado: " . json_encode($user));
  
  // Busque os agendamentos (schedule) do usuário no formato original
  $stmtSchedOriginal = $pdo->prepare("SELECT dia_semana, bus_id FROM user_schedule WHERE user_id = :id");
  $stmtSchedOriginal->execute(['id' => $userId]);
  $scheduleOriginal = $stmtSchedOriginal->fetchAll(PDO::FETCH_ASSOC);
  
  // Busque os agendamentos (schedule) do usuário com detalhes do ônibus
  $stmtSched = $pdo->prepare("
    SELECT us.dia_semana, us.bus_id, b.identificador as bus_nome 
    FROM user_schedule us
    JOIN buses b ON us.bus_id = b.id
    WHERE us.user_id = :id
  ");
  $stmtSched->execute(['id' => $userId]);
  $scheduleFull = $stmtSched->fetchAll(PDO::FETCH_ASSOC);
  
  // Organizar os agendamentos por dia da semana
  $scheduleMultiple = [];
  foreach ($scheduleFull as $item) {
    if (!isset($scheduleMultiple[$item['dia_semana']])) {
      $scheduleMultiple[$item['dia_semana']] = [];
    }
    $scheduleMultiple[$item['dia_semana']][] = [
      'bus_id' => $item['bus_id'],
      'bus_nome' => $item['bus_nome']
    ];
  }
  
  $user['schedule'] = $scheduleFull;
  $user['scheduleOriginal'] = $scheduleOriginal;
  $user['schedule_multiple'] = $scheduleMultiple;
  
  // Log da resposta completa
  error_log("Resposta completa com dados do usuário: " . json_encode(['status' => 'sucesso', 'data' => $user]));
  
  echo json_encode(['status' => 'sucesso', 'data' => $user]);
}
?>

