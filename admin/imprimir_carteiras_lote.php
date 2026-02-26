<?php
$pageTitle = "Imprimir Carteiras em Lote"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

// Consultar todos os usuários com role 'usuario'
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'usuario'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para obter os dias de atendimento formatados
function getDiasFormatados($pdo, $userId) {
    $stmtDias = $pdo->prepare("SELECT dia_semana FROM user_schedule WHERE user_id = :user_id");
    $stmtDias->execute(['user_id' => $userId]);
    $dias = $stmtDias->fetchAll(PDO::FETCH_COLUMN);
    
    if ($dias) {
        return implode(', ', array_map('ucfirst', $dias));
    } else {
        return "Não definido";
    }
}

// Não incluir o header padrão para impressão
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impressão de Carteiras em Lote</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <style>
    /* Estilos gerais */
    body {
      background-color: #f8f9fa;
      padding: 20px;
    }
    
    /* Estilos para a carteira universitária */
    .card-container {
      width: 100%;
      max-width: 400px;
      margin-bottom: 20px;
    }
    
    .card-front, .card-back {
      width: 100%;
      height: 250px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      margin-bottom: 10px;
      background-color: white;
    }
    
    .card-back {
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
    .print-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-gap: 10px;
      margin-bottom: 20px;
    }
    
    .print-card-set {
      display: flex;
      flex-direction: column;
    }
    
    .instructions {
      margin: 20px 0;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
    
    @media print {
      body {
        margin: 0;
        padding: 0;
        background-color: white;
      }
      
      .no-print {
        display: none !important;
      }
      
      .print-grid {
        page-break-after: always;
      }
      
      .print-grid:last-child {
        page-break-after: avoid;
      }
      
      .print-card-set {
        page-break-inside: avoid;
      }
      
      .card-front, .card-back {
        box-shadow: none;
        border: 1px solid #ccc;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row no-print">
      <div class="col-12">
        <h2 class="text-center mb-4">Impressão de Carteiras em Lote</h2>
        
        <div class="alert alert-info">
          <p class="mb-0">Total de carteiras: <strong><?php echo count($users); ?></strong></p>
          <p class="mb-0">Clique no botão abaixo para imprimir todas as carteiras.</p>
        </div>
        
        <div class="instructions">
          <h5>Instruções para impressão:</h5>
          <ol>
            <li>Verifique se a impressora está configurada para imprimir em papel A4.</li>
            <li>Configure a impressão sem margens para melhor resultado.</li>
            <li>Após a impressão, recorte as carteiras nas linhas pontilhadas.</li>
            <li>Dobre cada carteira ao meio para ter frente e verso.</li>
          </ol>
        </div>
        
        <div class="text-center mb-4">
          <button onclick="window.print();" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir Carteiras
          </button>
          <a href="imprimir_carteiras.php" class="btn btn-secondary ml-2">
            <i class="fas fa-arrow-left"></i> Voltar
          </a>
        </div>
      </div>
    </div>
    
    <?php 
    // Dividir usuários em grupos de 4 para impressão
    $chunks = array_chunk($users, 4);
    
    foreach ($chunks as $index => $chunk): 
    ?>
    <div class="print-grid">
      <?php foreach ($chunk as $user): 
        // Obter dias formatados
        $diasFormatados = getDiasFormatados($pdo, $user['id']);
        
        // Dados para o QR Code
        $data = [
            'id'        => $user['id'],
            'turno'     => $user['turno'],
            'timestamp' => time()
        ];
        // Converte para JSON
        $jsonData = json_encode($data);
        $qrId = 'qrcode-' . $user['id'];
      ?>
      <div class="print-card-set">
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
            <p>Secretaria de Educação, Ciência e Tecnologia - Prefeitura de Armação dos Búzios</p>
          </div>
        </div>
        
        <!-- Verso da Carteira -->
        <div class="card-back">
          <div class="card-back-header">
            <h3>QR CODE DE ACESSO</h3>
          </div>
          
          <div class="qr-container">
            <div id="<?php echo $qrId; ?>"></div>
          </div>
          
          <div class="card-back-footer">
            <p>Apresente este QR Code ao motorista para validação</p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <?php 
      // Preencher com carteiras vazias se não tiver 4 na última página
      $emptyCount = 4 - count($chunk);
      for ($i = 0; $i < $emptyCount; $i++): 
      ?>
      <div class="print-card-set"></div>
      <?php endfor; ?>
    </div>
    <?php endforeach; ?>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Gerar QR Codes para cada usuário
      <?php foreach ($users as $user): 
        $data = [
            'id'        => $user['id'],
            'turno'     => $user['turno'],
            'timestamp' => time()
        ];
        $jsonData = json_encode($data);
        $qrId = 'qrcode-' . $user['id'];
      ?>
      new QRCode(document.getElementById("<?php echo $qrId; ?>"), {
        text: <?php echo json_encode($jsonData); ?>,
        width: 150,
        height: 150,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
      });
      <?php endforeach; ?>
    });
  </script>
</body>
</html>
