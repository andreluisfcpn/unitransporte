<?php
// admin/api/save_user.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

// Verifica se a requisição é POST e ação é "create"
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'create') {
  http_response_code(400);
  echo json_encode(['message' => 'Ação inválida']);
  exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$turno = $_POST['turno'] ?? '';
$bairro = trim($_POST['bairro'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$faculdade = trim($_POST['faculdade'] ?? '');

// Campos obrigatórios
if (empty($nome) || empty($email) || empty($senha)) {
  http_response_code(400);
  echo json_encode(['message' => 'Campos obrigatórios não preenchidos']);
  exit;
}

// Upload da foto
$foto = "";
if (isset($_FILES['fotoFile']) && $_FILES['fotoFile']['error'] === UPLOAD_ERR_OK) {
  // Verificar tipo de arquivo permitido
  $fileInfo = pathinfo($_FILES['fotoFile']['name']);
  $extension = strtolower($fileInfo['extension']);
  $allowedTypes = ['jpg', 'jpeg', 'png'];
  
  if (!in_array($extension, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['message' => 'Tipo de arquivo não permitido. Use apenas jpg, jpeg ou png.']);
    exit;
  }
  
  $uploadDir = '../../assets/uploads/';
  $foto = time() . '_' . basename($_FILES['fotoFile']['name']);
  move_uploaded_file($_FILES['fotoFile']['tmp_name'], $uploadDir . $foto);
}

$dias = $_POST['dias'] ?? [];
$onibus_dia = $_POST['onibus_dia'] ?? [];

try {
  $pdo->beginTransaction();
  $senhaMD5 = md5($senha);
  $stmt = $pdo->prepare("INSERT INTO users (nome, email, senha, endereco, role, turno, data_criacao, bairro, cpf, telefone, faculdade, foto)
                         VALUES (:nome, :email, :senha, :endereco, 'usuario', :turno, NOW(), :bairro, :cpf, :telefone, :faculdade, :foto)");
  $stmt->execute([
    'nome' => $nome,
    'email' => $email,
    'senha' => $senhaMD5,
    'endereco' => $endereco,
    'turno' => $turno,
    'bairro' => $bairro,
    'cpf' => $cpf,
    'telefone' => $telefone,
    'faculdade' => $faculdade,
    'foto' => $foto
  ]);
  $lastUserId = $pdo->lastInsertId();
  foreach ($dias as $dia) {
      $dia = trim($dia);
      if (isset($onibus_dia[$dia]) && !empty($onibus_dia[$dia])) {
        $stmtSched = $pdo->prepare("INSERT INTO user_schedule (user_id, dia_semana, bus_id) VALUES (:user_id, :dia, :bus_id)");
        $stmtSched->execute([
          'user_id' => $lastUserId,
          'dia' => $dia,
          'bus_id' => $onibus_dia[$dia]
        ]);
      }
  }
  $pdo->commit();
  echo json_encode(['message' => 'Usuário cadastrado com sucesso']);
} catch (Exception $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao cadastrar usuário: ' . $e->getMessage()]);
}
?>


