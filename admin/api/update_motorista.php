<?php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

// Lê o JSON enviado e converte para um array PHP
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['message' => 'Erro ao processar os dados. Verifique o formato da requisição.']);
    exit;
}

// Log para debug (verifique se o JSON está correto nos logs do servidor)
error_log("Dados recebidos no update_motorista: " . print_r($data, true));

// Validação do ID
if (!isset($data['user_id']) || empty($data['user_id']) || !is_numeric($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'ID do motorista inválido ou ausente.']);
    exit;
}

// Captura os dados
$user_id = intval($data['user_id']);
$nome = isset($data['nome']) ? trim($data['nome']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$endereco = isset($data['endereco']) ? trim($data['endereco']) : '';
$cpf = isset($data['cpf']) ? trim($data['cpf']) : '';
$telefone = isset($data['telefone']) ? trim($data['telefone']) : '';
$senha = isset($data['senha']) ? trim($data['senha']) : '';

try {
    $params = [
        'nome' => $nome,
        'email' => $email,
        'endereco' => $endereco,
        'cpf' => $cpf,
        'telefone' => $telefone,
        'user_id' => $user_id
    ];

    $query = "UPDATE users SET nome = :nome, email = :email, endereco = :endereco, cpf = :cpf, telefone = :telefone";

    if (!empty($senha)) {
        $query .= ", senha = :senha";
        $params['senha'] = md5($senha);
    }

    $query .= " WHERE id = :user_id AND role = 'motorista'";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['message' => 'Motorista atualizado com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao atualizar motorista: ' . $e->getMessage()]);
}
?>

