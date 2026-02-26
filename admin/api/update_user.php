<?php
// admin/api/update_user.php
require_once '../../config.php';
require_once '../../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update' || !isset($_POST['user_id'])) {
  http_response_code(400);
  echo json_encode(['message' => 'Dados insuficientes']);
  exit;
}

$user_id = intval($_POST['user_id']);
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$turno = $_POST['turno'] ?? '';
$bairro = trim($_POST['bairro'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$faculdade = trim($_POST['faculdade'] ?? '');

$foto = "";
if (isset($_FILES['fotoFile']) && $_FILES['fotoFile']['error'] === UPLOAD_ERR_OK) {
  // Verificar tipo de arquivo permitido
  $fileInfo = pathinfo($_FILES['fotoFile']['name']);
  $extension = strtolower($fileInfo['extension']);
  $allowedTypes = ['jpg', 'jpeg', 'png'];
  
  // Verificar o tipo MIME real do arquivo
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $fileMimeType = $finfo->file($_FILES['fotoFile']['tmp_name']);
  $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  
  // Verificação de segurança adicional - busca por conteúdo PHP suspeito
  $fileContent = file_get_contents($_FILES['fotoFile']['tmp_name']);
  $phpSignatures = ['<?php', '<?=', '<script', 'eval(', 'base64_decode(', 'system(', 'exec(', 'shell_exec(', 'passthru('];
  $containsPhpCode = false;
  
  foreach ($phpSignatures as $signature) {
      if (stripos($fileContent, $signature) !== false) {
          $containsPhpCode = true;
          break;
      }
  }
  
  if (!in_array($extension, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes) || $containsPhpCode) {
    http_response_code(400);
    echo json_encode(['message' => 'Tipo de arquivo não permitido ou conteúdo suspeito detectado.']);
    exit;
  }
  
  // Verificar se é uma imagem válida
  if (!getimagesize($_FILES['fotoFile']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['message' => 'O arquivo não é uma imagem válida.']);
    exit;
  }
  
  $uploadDir = '../../assets/uploads/';
  $foto = time() . '_' . basename($_FILES['fotoFile']['name']);
  move_uploaded_file($_FILES['fotoFile']['tmp_name'], $uploadDir . $foto);
}

try {
  $fields = "nome = :nome, email = :email, endereco = :endereco, turno = :turno, bairro = :bairro, cpf = :cpf, telefone = :telefone, faculdade = :faculdade";
  $params = [
    'nome' => $nome,
    'email' => $email,
    'endereco' => $endereco,
    'turno' => $turno,
    'bairro' => $bairro,
    'cpf' => $cpf,
    'telefone' => $telefone,
    'faculdade' => $faculdade,
    'id' => $user_id
  ];
  if (!empty($senha)) {
    $fields .= ", senha = :senha";
    $params['senha'] = md5($senha);
  }
  if (!empty($foto)) {
    $fields .= ", foto = :foto";
    $params['foto'] = $foto;
  }
  $stmt = $pdo->prepare("UPDATE users SET $fields WHERE id = :id");
  $stmt->execute($params);
  echo json_encode(['message' => 'Usuário atualizado com sucesso']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['message' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
}
?>


