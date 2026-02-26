<?php
// usuario/carteira_universitaria.php
$pageTitle = "Carteira Universitária"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('usuario');

// Recupera os dados do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user']['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Dados fixos do usuário para o QR Code
$data = [
    'id'        => $_SESSION['user']['id'],
    'turno'     => $_SESSION['user']['turno'],
    'timestamp' => time()
];
// Converte para JSON e URL-encodes (garantindo que os caracteres especiais sejam tratados)
$jsonData = rawurlencode(json_encode($data));

// Recupera os dias de atendimento
$stmtDias = $pdo->prepare("SELECT dia_semana FROM user_schedule WHERE user_id = :user_id");
$stmtDias->execute(['user_id' => $_SESSION['user']['id']]);
$dias = $stmtDias->fetchAll(PDO::FETCH_COLUMN);
$diasFormatados = "";
if ($dias) {
  $diasFormatados = implode(', ', array_map('ucfirst', $dias));
} else {
  $diasFormatados = "Não definido";
}

include '../includes/header.php';
?>
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h2 class="text-center mb-4">Carteira Universitária</h2>
      
      <div class="card-container">
        <div class="card-flip">
          <!-- Frente da Carteira -->
          <div class="card-front">
            <!-- Cabeçalho da Carteira -->
            <div class="card-header">
              <div class="card-title">
                <h2>ÔNIBUS</h2>
                <h3>UNIVERSITÁRIO</h3>
              </div>
              <div class="card-logo">
                <img src="/assets/img/buzios_logo.png" alt="Prefeitura de Búzios">
              </div>
            </div>
            
            <!-- Corpo da Carteira -->
            <div class="card-body-content">
              <!-- Foto do Estudante -->
              <div class="student-photo">
                <?php if(!empty($user['foto'])): ?>
                  <img src="/assets/uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto">
                <?php else: ?>
                  <div class="no-photo">
                    <span>Sem foto</span>
                  </div>
                <?php endif; ?>
              </div>
              
              <!-- Dados do Estudante -->
              <div class="student-info">
                <div class="info-item">
                  <div class="info-label">Nome:</div>
                  <div class="info-value"><?php echo htmlspecialchars($user['nome']); ?></div>
                </div>
                
                <div class="info-row">
                  <div class="info-item half">
                    <div class="info-label">Turno:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['turno']); ?></div>
                  </div>
                  
                  <div class="info-item half bairro-label">
                    <span class="info-label">Bairro:</span><span class="info-value"><?php echo htmlspecialchars($user['bairro']);?></span>
                  </div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Dias:</div>
                  <div class="info-value"><?php echo htmlspecialchars($diasFormatados); ?></div>
                </div>
                
                <div class="info-item">
                  <div class="info-label">Faculdade:</div>
                  <div class="info-value"><?php echo htmlspecialchars($user['faculdade']); ?></div>
                </div>
              </div>
            </div>
            
            <div class="card-footer">
              <p>Clique na carteira para ver o QR Code</p>
            </div>
          </div>
          
          <!-- Verso da Carteira -->
          <div class="card-back">
            <div class="card-back-header">
              <h3>QR CODE DE ACESSO</h3>
            </div>
            
            <div class="qr-container">
              <div id="qrcode"></div>
            </div>
            
            <div class="card-back-footer">
              <p>Apresente este QR Code ao motorista para validação</p>
              <p>Clique na carteira para ver seus dados</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-4 mb-4 action-buttons">
        <button id="printBtn" class="btn btn-primary">Imprimir Carteira</button>
        <a href="dashboard.php" class="btn btn-secondary ml-2">Voltar ao Dashboard</a>
      </div>
    </div>
  </div>
</div>

<!-- Versão para impressão -->
<div class="print-only">
  <div class="print-instructions">
    <h3>Instruções:</h3>
    <ol>
      <li>Recorte nas linhas pontilhadas</li>
      <li>Dobre ao meio</li>
      <li>Cole as duas faces (opcional)</li>
    </ol>
  </div>
  
  <div class="print-card-container">
    <!-- Frente da Carteira para Impressão -->
    <div class="print-card-front">
      <!-- Cabeçalho da Carteira -->
      <div class="card-header">
        <div class="card-title">
          <h2>ÔNIBUS</h2>
          <h3>UNIVERSITÁRIO</h3>
        </div>
        <div class="card-logo">
          <img src="/assets/img/buzios_logo.png" alt="Prefeitura de Búzios">
        </div>
      </div>
      
      <!-- Corpo da Carteira -->
      <div class="card-body-content">
        <!-- Foto do Estudante -->
        <div class="student-photo">
          <?php if(!empty($user['foto'])): ?>
            <img src="/assets/uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto">
          <?php else: ?>
            <div class="no-photo">
              <span>Sem foto</span>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- Dados do Estudante -->
        <div class="student-info">
          <div class="info-item">
            <div class="info-label">Nome:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['nome']); ?></div>
          </div>
          
          <div class="info-row">
            <div class="info-item half">
              <div class="info-label">Turno:</div>
              <div class="info-value"><?php echo htmlspecialchars($user['turno']); ?></div>
            </div>
            
            <div class="info-item half bairro-label">
              <span class="info-label">Bairro:</span><span class="info-value"><?php echo htmlspecialchars($user['bairro']);?></span>
            </div>
          </div>
          
          <div class="info-item">
            <div class="info-label">Dias:</div>
            <div class="info-value"><?php echo htmlspecialchars($diasFormatados); ?></div>
          </div>
          
          <div class="info-item">
            <div class="info-label">Faculdade:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['faculdade']); ?></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Verso da Carteira para Impressão -->
    <div class="print-card-back">
      <div class="card-back-header">
        <h3>QR CODE DE ACESSO</h3>
      </div>
      
      <div class="qr-container">
        <div id="print-qrcode"></div>
      </div>
      
      <div class="card-back-footer">
        <p>Apresente este QR Code ao motorista para validação</p>
      </div>
    </div>
  </div>
</div>

<!-- Scripts: QRCode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Gerar QR Code para exibição
    new QRCode(document.getElementById("qrcode"), {
      text: <?php echo json_encode($jsonData); ?>,
      width: 150,
      height: 150,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
    
    // Gerar QR Code para impressão
    new QRCode(document.getElementById("print-qrcode"), {
      text: <?php echo json_encode($jsonData); ?>,
      width: 150,
      height: 150,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
    
    // Configurar efeito de flip na carteira
    const cardFlip = document.querySelector('.card-flip');
    if (cardFlip) {
      cardFlip.addEventListener('click', function() {
        this.classList.toggle('flipped');
      });
    }
    
    // Configurar botão de impressão
    document.getElementById('printBtn').addEventListener('click', function() {
      window.print();
    });
  });
</script>

<style>
  /* Estilos para a carteira universitária */
  .card-container {
    perspective: 1000px;
    margin: 0 auto;
    max-width: 400px;
  }
  
  .card-flip {
    position: relative;
    width: 100%;
    height: 250px;
    transition: transform 0.8s;
    transform-style: preserve-3d;
    cursor: pointer;
    margin-bottom: 20px;
  }
  
  .card-flip.flipped {
    transform: rotateY(180deg);
  }
  
  .card-front, .card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }
  
  .card-front {
    background-color: white;
  }
  
  .card-back {
    background-color: white;
    transform: rotateY(180deg);
    display: flex;
    flex-direction: column;
  }
  
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #0052cc;
    padding: 10px 15px;
  }
  
  .card-title h2, .card-title h3 {
    color: #ffde00;
    font-weight: bold;
    margin: 0;
    text-transform: uppercase;
  }
  
  .card-title h2 {
    font-size: 20px;
  }
  
  .card-title h3 {
    font-size: 24px;
  }
  
  .card-logo img {
    height: 40px;
  }
  
  .card-body-content {
    display: flex;
    padding: 10px;
  }
  
  .student-photo {
    width: 80px;
    height: 110px;
    border: 1px solid #0052cc;
    overflow: hidden;
    margin-right: 10px;
    flex-shrink: 0;
  }
  
  .student-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .no-photo {
    width: 100%;
    height: 100%;
    background-color: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
  }
  
  .student-info {
    flex-grow: 1;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  
  .info-item {
    margin-bottom: 4px;
    display: flex;
    align-items: baseline;
  }
  
  .info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
  }
  
  .info-item.half {
    width: 48%;
  }
  
  .info-label {
    font-weight: bold;
    color: #0052cc;
    width: 65px;
    flex-shrink: 0;
  }
  
  .info-value {
    flex-grow: 1;
    border-bottom: 1px solid #ccc;
    padding-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 16px;
  }
  
  .bairro-label {
    display: flex;
  }
  
  .bairro-label .info-label {
    margin-right: 0;
    padding-right: 0;
    width: auto;
  }
  
  .bairro-label .info-value {
    margin-left: 0;
    padding-left: 0;
    display: inline-block;
    width: auto;
    flex: 1;
  }
  
  .card-footer, .card-back-footer {
    text-align: center;
    font-size: 10px;
    color: #666;
    padding: 5px;
    background-color: #f8f9fa;
  }
  
  .card-back-header {
    background-color: #0052cc;
    color: white;
    text-align: center;
    padding: 10px;
  }
  
  .card-back-header h3 {
    margin: 0;
    font-size: 16px;
  }
  
  .qr-container {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
  }
  
  .card-back-footer p {
    margin: 2px 0;
  }
  
  /* Estilos para impressão */
  .print-only {
    display: none;
  }
  
  .print-card-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 150mm;
    margin: 0 auto;
  }
  
  .print-card-front, .print-card-back {
    width: 125mm;
    height: 78.6mm;
    border: 1px solid #ccc;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
    box-sizing: border-box;
    page-break-inside: avoid;
  }
  
  .print-card-front .card-header,
  .print-card-back .card-back-header {
    font-size: 1.2em;
  }
  
  .print-card-front .student-photo {
    width: 120px;
    height: 160px;
    margin-right: 20px;
  }
  
  .print-card-front .student-info {
    font-size: 16px;
  }
  
  .print-card-front .info-label {
    width: 85px;
  }
  
  .print-card-back .qr-container #print-qrcode img {
    width: 200px;
    height: 200px;
  }
  
  .print-cut-line {
    text-align: center;
    margin: 10px 0;
    font-size: 14px;
    color: #999;
    width: 100%;
  }
  
  @media print {
    body {
      margin: 0;
      padding: 0;
      background-color: white;
    }
    
    header, footer, .action-buttons, .container > .row > .col-md-8 > h2, .card-container {
      display: none !important;
    }
    
    .print-only {
      display: block;
      padding: 20px;
    }
    
    .print-instructions {
      margin-bottom: 20px;
    }
  }
  
  /* Responsividade para celulares */
  @media (max-width: 576px) {
    .card-container {
      max-width: 100%;
    }
    
    .card-flip {
      height: 230px;
    }
    
    .card-title h2 {
      font-size: 18px;
    }
    
    .card-title h3 {
      font-size: 20px;
    }
    
    .card-logo img {
      height: 35px;
    }
    
    .student-photo {
      width: 70px;
      height: 100px;
    }
    
    .student-info {
      font-size: 11px;
    }
    
    .info-item {
      margin-bottom: 3px;
    }
    
    .info-label {
      width: 55px;
    }
  }
</style>

<?php include '../includes/footer.php'; ?>
