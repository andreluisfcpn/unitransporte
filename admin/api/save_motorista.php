<?php
require_once '../../config.php';
require_once '../../includes/auth.php';
header('Content-Type: application/json');

// Decodifica o JSON recebido
$data = json_decode(file_get_contents("php://input"), true);

// Validação dos campos obrigatórios
if (!isset($data['nome']) || !isset($data['email']) || !isset($data['senha'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Campos obrigatórios não foram preenchidos.']);
    exit;
}

// Captura os dados do JSON
$nome = trim($data['nome']);
$email = trim($data['email']);
$senha = trim($data['senha']);
$endereco = isset($data['endereco']) ? trim($data['endereco']) : '';
$cpf = isset($data['cpf']) ? trim($data['cpf']) : '';
$telefone = isset($data['telefone']) ? trim($data['telefone']) : '';

// Hash seguro para a senha
$senhaHash = md5($senha);

try {
    // Verifica se o e-mail já existe
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmtCheck->execute(['email' => $email]);
    $emailExists = $stmtCheck->fetchColumn();

    if ($emailExists > 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Erro: Este e-mail já está cadastrado. Escolha outro e-mail.']);
        exit;
    }

    // Insere o novo motorista no banco de dados
    $stmt = $pdo->prepare("INSERT INTO users (nome, email, senha, endereco, cpf, telefone, role, data_criacao) 
                           VALUES (:nome, :email, :senha, :endereco, :cpf, :telefone, 'motorista', NOW())");
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash,
        'endereco' => $endereco,
        'cpf' => $cpf,
        'telefone' => $telefone
    ]);

    echo json_encode(['message' => 'Motorista cadastrado com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao cadastrar motorista: ' . $e->getMessage()]);
}
?>

