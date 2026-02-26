<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'motorista') {
        http_response_code(403);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
        exit;
    }

    if (!isset($_POST['userId'], $_POST['busId'], $_POST['action'])) {
        http_response_code(400);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados insuficientes.']);
        exit;
    }

    $userId = intval($_POST['userId']);
    $busId = intval($_POST['busId']);
    $action = $_POST['action'];
    $latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? trim($_POST['latitude']) : null;
    $longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? trim($_POST['longitude']) : null;

    error_log("Recebendo ação: $action para userId: $userId e busId: $busId");

    if (!in_array($action, ['confirmar', 'excecao', 'recusar'])) {
        http_response_code(400);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Ação inválida.']);
        exit;
    }

    $status = ($action === 'recusar') ? 'recusado' : 'autorizado';
    
    if ($action === 'recusar') {
      $mensagem = 'Viagem recusada.';
    } else if ($action === 'excecao') {
      $mensagem = 'Viagem autorizada com ressalvas.';
    } else if ($action === 'confirmar') {
      $mensagem = 'Viagem autorizada.';
    }

    $sql = "INSERT INTO trips (user_id, bus_id, motorista_id, data_viagem, status, mensagem, latitude, longitude)
            VALUES (:user_id, :bus_id, :motorista_id, NOW(), :status, :mensagem, :latitude, :longitude)";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        'user_id' => $userId,
        'bus_id' => $busId,
        'motorista_id' => $_SESSION['user']['id'],
        'status' => $status,
        'mensagem' => $mensagem,
        'latitude' => $latitude,
        'longitude' => $longitude
    ]);

    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Ação registrada.']);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro no banco de dados: " . $e->getMessage());
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro interno no servidor.']);
}

?>

