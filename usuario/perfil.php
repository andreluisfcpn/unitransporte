<?php 
// usuario/perfil.php
$pageTitle = "Perfil"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('usuario');

// Atualiza apenas os campos editáveis: email, endereço, telefone e foto
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $endereco = trim($_POST['endereco']);
    $telefone = trim($_POST['telefone']);

    if(empty($email) || empty($endereco) || empty($telefone)) {
        $message = "Email, Endereço e Telefone são obrigatórios.";
    } else {
        // Processa o upload da foto se houver
        $foto = $user['foto']; // Mantém a foto atual por padrão
        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = '../assets/uploads/';
            
            // Verifica o tipo MIME real do arquivo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileMimeType = $finfo->file($_FILES['foto']['tmp_name']);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            
            // Verifica a extensão do arquivo
            $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            
            // Verificação de segurança adicional - busca por conteúdo PHP suspeito
            $fileContent = file_get_contents($_FILES['foto']['tmp_name']);
            $phpSignatures = ['<?php', '<?=', '<script', 'eval(', 'base64_decode(', 'system(', 'exec(', 'shell_exec(', 'passthru('];
            $containsPhpCode = false;
            
            foreach ($phpSignatures as $signature) {
                if (stripos($fileContent, $signature) !== false) {
                    $containsPhpCode = true;
                    break;
                }
            }
            
            if (!in_array($fileExtension, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes) || $containsPhpCode) {
                $message = "Erro: Arquivo inválido ou não permitido. Apenas imagens JPG, JPEG e PNG são permitidas.";
            } elseif ($_FILES['foto']['size'] > 5000000) { // 5MB limit
                $message = "Erro: O tamanho máximo permitido é 5MB.";
            } else {
                // Verifica se o arquivo é realmente uma imagem válida
                if (!getimagesize($_FILES['foto']['tmp_name'])) {
                    $message = "Erro: O arquivo não é uma imagem válida.";
                } else {
                    // Gera um nome único para o arquivo
                    $newFileName = uniqid('user_' . $_SESSION['user']['id'] . '_') . '.' . $fileExtension;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $newFileName)) {
                        // Se houver uma foto antiga, exclui
                        if (!empty($user['foto']) && file_exists($uploadDir . $user['foto'])) {
                            unlink($uploadDir . $user['foto']);
                        }
                        $foto = $newFileName;
                    } else {
                        $message = "Erro ao fazer upload da foto.";
                    }
                }
            }
        }

        if (empty($message)) {
            $stmt = $pdo->prepare("UPDATE users SET email = :email, endereco = :endereco, telefone = :telefone, foto = :foto WHERE id = :id");
            if ($stmt->execute([
                'email'    => $email,
                'endereco' => $endereco,
                'telefone' => $telefone,
                'foto'     => $foto,
                'id'       => $_SESSION['user']['id']
            ])) {
                $message = "Perfil atualizado com sucesso!";
                $_SESSION['user']['email']    = $email;
                $_SESSION['user']['endereco'] = $endereco;
                $_SESSION['user']['telefone'] = $telefone;
                $_SESSION['user']['foto']     = $foto;
            } else {
                $message = "Erro ao atualizar perfil.";
            }
        }
    }
}

// Recupera os dados do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user']['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Recupera o agendamento do usuário (dias de atendimento e ônibus selecionado)
$stmtSchedule = $pdo->prepare("SELECT us.dia_semana, b.identificador 
                               FROM user_schedule us 
                               INNER JOIN buses b ON us.bus_id = b.id 
                               WHERE us.user_id = :user_id");
$stmtSchedule->execute(['user_id' => $_SESSION['user']['id']]);
$schedule = $stmtSchedule->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h2>Meu Perfil</h2>
      <?php if($message): ?>
        <div class="alert <?php echo strpos($message, 'Erro') !== false ? 'alert-danger' : 'alert-success'; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
      <form method="post" action="perfil.php" enctype="multipart/form-data">
        <div class="form-group">
          <label for="nome">Nome:</label>
          <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($user['nome']); ?>" disabled>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="form-group">
          <label for="endereco">Endereço:</label>
          <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($user['endereco']); ?>">
        </div>
        <div class="form-group">
          <label for="telefone">Telefone:</label>
          <input type="text" name="telefone" id="telefone" class="form-control" value="<?php echo htmlspecialchars($user['telefone']); ?>">
        </div>
        <div class="form-group">
          <label for="bairro">Bairro:</label>
          <input type="text" name="bairro" class="form-control" value="<?php echo htmlspecialchars($user['bairro']); ?>" disabled>
        </div>
        <div class="form-group">
          <label for="cpf">CPF:</label>
          <input type="text" name="cpf" class="form-control" value="<?php echo htmlspecialchars($user['cpf']); ?>" disabled>
        </div>
        <div class="form-group">
          <label for="faculdade">Faculdade:</label>
          <input type="text" name="faculdade" class="form-control" value="<?php echo htmlspecialchars($user['faculdade']); ?>" disabled>
        </div>
        <div class="form-group">
          <label for="foto">Foto:</label>
          <div class="mb-2">
            <?php if(!empty($user['foto'])): ?>
              <img src="/assets/uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto" class="img-thumbnail" style="height:150px;">
              <p class="text-muted small">Foto atual</p>
            <?php else: ?>
              <p class="text-muted">Sem foto</p>
            <?php endif; ?>
          </div>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/jpeg,image/png">
            <label class="custom-file-label" for="foto">Escolher arquivo</label>
          </div>
          <small class="form-text text-muted">
            Formatos aceitos: JPG, JPEG, PNG. Tamanho máximo: 5MB.
          </small>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-4">Atualizar Perfil</button>
      </form>

      <!-- Seção de Viagens Permitidas -->
      <div class="mt-5">
        <h3>Viagens permitidas</h3>
        <?php if($schedule): ?>
          <ul class="list-group">
            <?php foreach($schedule as $sch): ?>
              <li class="list-group-item">
                <strong><?php echo ucfirst($sch['dia_semana']); ?>:</strong> Ônibus <?php echo htmlspecialchars($sch['identificador']); ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Nenhuma viagem foi permitida.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
// Máscara para o campo de telefone
function formatPhone(value) {
    value = value.replace(/\D/g, '');
    if (value.length > 11) {
        value = value.substring(0,11);
    }
    if (value.length <= 2) {
        return '(' + value;
    } else if (value.length <= 7) {
        return '(' + value.substring(0,2) + ') ' + value.substring(2);
    } else {
        return '(' + value.substring(0,2) + ') ' + value.substring(2,7) + '-' + value.substring(7);
    }
}

document.getElementById('telefone').addEventListener('input', function(e) {
    e.target.value = formatPhone(e.target.value);
});

// Atualizar nome do arquivo selecionado
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Escolher arquivo';
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});
</script>
<?php include '../includes/footer.php'; ?>

