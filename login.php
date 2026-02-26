<?php
// login.php
session_start();
require_once 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicializar variáveis
$error = "";
$successMessage = "";
$showForgotPassword = false;
$showRecoveryInfo = false;
$debug = ""; // Para debug

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $error = "Preencha todos os campos.";
    } else {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $senhaMD5 = md5($senha);
            if ($senhaMD5 === $user['senha']) {
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'nome'  => $user['nome'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                    'turno' => $user['turno']
                ];
                
                switch ($user['role']) {
                    case 'admin':
                        header("Location: /admin/dashboard.php");
                        break;
                    case 'motorista':
                        header("Location: /motorista/dashboard.php");
                        break;
                    case 'usuario':
                        header("Location: /usuario/dashboard.php");
                        break;
                    default:
                        header("Location: /login.php");
                        break;
                }
                exit;
            } else {
                $error = "Email ou senha incorretos.";
            }
        } else {
            $error = "Email ou senha incorretos.";
        }
    }
}

// Processar solicitação de recuperação de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'recover_password') {
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);
    
    if (empty($telefone) || empty($cpf)) {
        $error = "Preencha todos os campos.";
        $showForgotPassword = true;
    } else {
        try {
            // Verificar no banco de dados (CPF COM formatação)
            $query = "SELECT * FROM users WHERE cpf = :cpf";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->execute();
            
            // Debug da consulta
            $debug = "CPF procurado: " . $cpf;
            $debug .= " | Telefone enviado: " . $telefone;
            
            $userFound = false;
            
            // Limpar formatação dos telefones para comparação
            $telefoneClean = preg_replace('/[^0-9]/', '', $telefone);
            
            // Verificar resultados
            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $telefoneDB = preg_replace('/[^0-9]/', '', $user['telefone']);
                $debug .= " | Telefone no banco: " . $user['telefone'] . " (limpo: " . $telefoneDB . ")";
                
                // Comparamos os telefones sem formatação
                if ($telefoneClean === $telefoneDB) {
                    // Gerar nova senha com base no CPF
                    $cpfDigits = preg_replace('/[^0-9]/', '', $user['cpf']);
                    
                    // Se o CPF não tiver pelo menos 4 dígitos ou for vazio, gera senha aleatória
                    if (empty($cpfDigits) || strlen($cpfDigits) < 4) {
                        // Gerar senha aleatória de 6 caracteres
                        $novaSenha = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
                    } else {
                        // Usar os 4 primeiros dígitos do CPF
                        $novaSenha = substr($cpfDigits, 0, 4);
                    }
                    
                    // Gerar hash MD5 da nova senha
                    $novaSenhaMD5 = md5($novaSenha);
                    
                    // Atualizar senha no banco de dados
                    $updateQuery = "UPDATE users SET senha = :senha WHERE id = :id";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->bindParam(':senha', $novaSenhaMD5);
                    $updateStmt->bindParam(':id', $user['id']);
                    $updateStmt->execute();
                    
                    // Salvar email do usuário para mostrar no modal
                    $recoveredEmail = $user['email'];
                    $recoveredSenha = $novaSenha;
                    
                    // Modal será ativado com JS
                    $successMessage = "Sua senha foi redefinida com sucesso!";
                    $userFound = true;
                    
                    // Preparar dados para o modal (serão usados via JavaScript)
                    echo '<script>
                        var recoveredData = {
                            email: "' . htmlspecialchars($recoveredEmail) . '",
                            senha: "' . htmlspecialchars($recoveredSenha) . '"
                        };
                        window.onload = function() {
                            $("#recoverySuccessModal").modal("show");
                            $("#recoveredEmailInput").val(recoveredData.email);
                            $("#recoveredSenhaInput").val(recoveredData.senha);
                        }
                    </script>';
                    break;
                }
            }
            
            if (!$userFound) {
                $error = "CPF ou telefone não encontrados. Verifique os dados e tente novamente.";
                $showForgotPassword = true;
            }
        } catch (PDOException $e) {
            $error = "Erro ao verificar os dados. Por favor, tente novamente.";
            $debug = "Erro PDO: " . $e->getMessage();
        }
    }
}

// Alternar visualização do formulário de senha esquecida
if (isset($_GET['forgot']) && $_GET['forgot'] == '1') {
    $showForgotPassword = true;
}

$pageTitle = "Login";
?>
<?php include 'includes/header.php'; ?>

<!-- Estilização adicional para melhorar a UX -->
<style>
.login-container {
    margin-top: 50px;
    margin-bottom: 50px;
}
.login-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.login-header {
    background: linear-gradient(135deg, #003049 0%, #005b96 100%);
    padding: 25px;
}
.login-header h2 {
    font-weight: 300;
    letter-spacing: 0.5px;
    margin: 0;
}
.login-form {
    padding: 30px;
}
.login-form label {
    font-weight: 500;
    color: #495057;
}
.login-input {
    padding: 12px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}
.login-input:focus {
    border-color: #003049;
    box-shadow: 0 0 0 0.2rem rgba(0, 48, 73, 0.25);
}
.login-btn {
    padding: 12px;
    border-radius: 5px;
    font-weight: 500;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, #003049 0%, #005b96 100%);
    border: none;
    transition: all 0.3s;
}
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 48, 73, 0.3);
}
.forgot-link {
    color: #005b96;
    font-weight: 500;
    transition: all 0.3s;
}
.forgot-link:hover {
    color: #003049;
    text-decoration: none;
}
.login-footer {
    color: #6c757d;
}
.recovery-icon {
    font-size: 3rem;
    color: #005b96;
    margin-bottom: 15px;
}
.copy-btn {
    cursor: pointer;
}
.copy-success {
    background-color: #d4edda !important;
    transition: background-color 0.5s;
}
.icon-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-20px);}
    60% {transform: translateY(-10px);}
}
.shake {
    animation: shake 0.5s;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-5px); }
    40%, 80% { transform: translateX(5px); }
}
.pulse {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
.modal-custom {
    border-radius: 15px;
    overflow: hidden;
}
.modal-header-custom {
    border-radius: 15px 15px 0 0;
}
.debug-info {
    font-size: 0.8rem;
    color: #dc3545;
    margin-top: 5px;
}

/* Melhorias de responsividade */
@media (max-width: 576px) {
    .login-container {
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .login-form {
        padding: 20px 15px;
    }
    .login-header {
        padding: 20px 15px;
    }
    .login-header h2 {
        font-size: 1.5rem;
    }
    .login-btn {
        padding: 10px;
    }
    .input-group-append button {
        padding: .375rem .5rem;
    }
}
</style>

<div class="container login-container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card login-card">
        <div class="login-header text-white text-center">
          <h2>
            <i class="fas fa-user-circle mr-2"></i> Acesso ao Sistema
          </h2>
        </div>
        <div class="card-body login-form">
          <?php if($error): ?>
            <div class="alert alert-danger shake" role="alert">
              <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
              <?php if(!empty($debug) && isset($_GET['debug'])): ?>
                <div class="debug-info"><?php echo htmlspecialchars($debug); ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <?php if($successMessage): ?>
            <div class="alert alert-success pulse" role="alert">
              <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($successMessage); ?>
            </div>
          <?php endif; ?>
          
          <?php if($showForgotPassword): ?>
            <!-- Formulário de recuperação de senha -->
            <form method="post" action="login.php" id="forgotPasswordForm">
              <input type="hidden" name="action" value="recover_password">
              <div class="text-center mb-4">
                <i class="fas fa-unlock-alt recovery-icon"></i>
                <h4>Recuperação de Acesso</h4>
                <p class="text-muted">Informe os dados abaixo para recuperar seu acesso</p>
              </div>
              
              <div class="form-group">
                <label for="cpf">
                  <i class="fas fa-id-card text-primary mr-2"></i> CPF:
                </label>
                <input type="text" name="cpf" id="cpf" class="form-control login-input" 
                      placeholder="000.000.000-00" required>
                <small class="form-text text-muted">Digite o CPF com pontos e traço</small>
              </div>
              
              <div class="form-group">
                <label for="telefone">
                  <i class="fas fa-phone text-primary mr-2"></i> Telefone:
                </label>
                <input type="text" name="telefone" id="telefone" class="form-control login-input" 
                      placeholder="(00) 00000-0000" required>
                <small class="form-text text-muted">Digite o telefone cadastrado no sistema</small>
              </div>
              
              <button type="submit" class="btn btn-primary btn-block login-btn mt-4">
                <i class="fas fa-search mr-2"></i> Verificar Dados
              </button>
              
              <div class="mt-3 text-center">
                <a href="login.php" class="forgot-link">
                  <i class="fas fa-arrow-left mr-1"></i> Voltar para o login
                </a>
              </div>
            </form>
          <?php else: ?>
            <!-- Formulário de login principal -->
            <form method="post" action="login.php" id="loginForm">
              <input type="hidden" name="action" value="login">
              <div class="form-group">
                <label for="email">
                  <i class="fas fa-envelope text-primary mr-2"></i> Email:
                </label>
                <input type="email" name="email" id="email" class="form-control form-control-lg login-input" 
                      placeholder="Seu email" required>
              </div>
              
              <div class="form-group">
                <label for="senha">
                  <i class="fas fa-lock text-primary mr-2"></i> Senha:
                </label>
                <div class="input-group">
                  <input type="password" name="senha" id="senha" class="form-control form-control-lg login-input" 
                        placeholder="Sua senha" required>
                  <div class="input-group-append">
                    <span class="btn btn-outline-secondary" id="togglePassword" style="cursor: pointer;">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>
              
              <button type="submit" class="btn btn-primary btn-block btn-lg login-btn mt-4">
                <i class="fas fa-sign-in-alt mr-2"></i> Entrar
              </button>
              
              <div class="text-center mt-3">
                <a href="?forgot=1" class="forgot-link">
                  <i class="fas fa-question-circle mr-1"></i> Esqueci minha senha
                </a>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-center mt-3 text-muted small login-footer">
        <p>Secretaria de Administração &copy; <?php echo date('Y'); ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Modal para mostrar os dados recuperados -->
<div class="modal fade" id="recoverySuccessModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-custom">
      <div class="modal-header bg-success text-white modal-header-custom">
        <h5 class="modal-title" id="recoveryModalLabel">
          <i class="fas fa-check-circle mr-2"></i> Dados Recuperados
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <i class="fas fa-user-check fa-4x text-success mb-3 icon-bounce"></i>
          <h4>Seus dados de acesso</h4>
        </div>
        
        <div class="card bg-light mb-3">
          <div class="card-body">
            <div class="form-group">
              <label class="font-weight-bold text-primary">Seu email para login:</label>
              <div class="input-group">
                <input type="text" class="form-control" id="recoveredEmailInput" readonly>
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary copy-btn" type="button" onclick="copyToClipboard('recoveredEmailInput')">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
              </div>
              <small class="form-text text-muted">Este é o seu nome de usuário para acessar o sistema.</small>
            </div>
            
            <div class="form-group mb-0">
              <label class="font-weight-bold text-primary">Sua nova senha:</label>
              <div class="input-group">
                <input type="text" class="form-control" id="recoveredSenhaInput" readonly>
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary copy-btn" type="button" onclick="copyToClipboard('recoveredSenhaInput')">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>
              </div>
              <small class="form-text text-muted">Sua senha foi atualizada para os dígitos mostrados acima.</small>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="login.php" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Voltar para Login
        </a>
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="autoFillLoginForm()">
          <i class="fas fa-sign-in-alt mr-2"></i> Usar estes dados agora
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
// Função isolada para o toggle de senha
function setupPasswordToggle() {
  var toggleBtn = document.getElementById('togglePassword');
  var senhaInput = document.getElementById('senha');
  
  if (toggleBtn && senhaInput) {
    toggleBtn.addEventListener('click', function() {
      if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        toggleBtn.querySelector('i').className = 'fas fa-eye-slash';
      } else {
        senhaInput.type = 'password';
        toggleBtn.querySelector('i').className = 'fas fa-eye';
      }
    });
  }
}

// Executar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
  setupPasswordToggle();
});

// Também executar quando o jQuery estiver pronto (abordagem de fallback)
$(document).ready(function() {
  // Máscaras para os campos
  $('#cpf').mask('000.000.000-00');
  $('#telefone').mask('(00) 00000-0000');
  
  // Garantir que o toggle de senha esteja configurado
  setupPasswordToggle();
  
  // Inicializar dados recuperados quando o modal for aberto
  $('#recoverySuccessModal').on('shown.bs.modal', function() {
    if (typeof recoveredData !== 'undefined') {
      $('#recoveredEmailInput').val(recoveredData.email);
      $('#recoveredSenhaInput').val(recoveredData.senha);
    }
  });
});

// Função para copiar para área de transferência
function copyToClipboard(elementId) {
  const element = document.getElementById(elementId);
  element.select();
  document.execCommand('copy');
  
  // Feedback visual
  element.classList.add('copy-success');
  
  setTimeout(function() {
    element.classList.remove('copy-success');
  }, 500);
}

// Preencher formulário de login automaticamente
function autoFillLoginForm() {
  if (typeof recoveredData !== 'undefined') {
    // Primeiro esconder o modal
    $('#recoverySuccessModal').modal('hide');
    
    // Pequeno atraso para permitir a transição do modal
    setTimeout(function() {
      // Preencher o formulário de login
      $('#email').val(recoveredData.email);
      $('#senha').val(recoveredData.senha);
      
      // Destacar os campos preenchidos
      $('#email, #senha').css('background-color', '#d4edda').delay(500).queue(function(next) {
        $(this).css('background-color', '');
        next();
      });
    }, 500);
  }
}
</script>

<?php include 'includes/footer.php'; ?>

