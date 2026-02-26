<?php
// usuario/gerar_qr.php
$pageTitle = "Gerar QR Code"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('usuario');

// Dados fixos do usuário para o QR Code
$data = [
    'id'        => $_SESSION['user']['id'],
    'turno'     => $_SESSION['user']['turno'],
    'timestamp' => time()
];
// Converte para JSON e URL-encodes (garantindo que os caracteres especiais sejam tratados)
$jsonData = rawurlencode(json_encode($data));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Meu QR Code</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .modal-fullscreen {
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
    }
    .modal-content {
      height: 100%;
      border: 0;
      border-radius: 0;
    }
    .qr-container {
      display: flex;
      align-items: center;
      justify-content: center;
      height: calc(100% - 56px);
    }
  </style>
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <div class="container text-center mt-5">
    <h2>Meu QR Code</h2>
    <p class="mt-3">O QR Code deve ser mostrado ao responsável pelo embarque.</p>
    <button class="btn btn-primary" data-toggle="modal" data-target="#qrModal">Gerar QR Code</button>
  </div>

  <!-- Modal para exibir o QR Code em tela cheia -->
  <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qrModalLabel">QR Code do Usuário</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" onclick="clearQRCode();">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body qr-container">
            <div id="qrcode"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts: QRCode.js, jQuery e Bootstrap -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function generateQRCode() {
      document.getElementById("qrcode").innerHTML = "";
      new QRCode(document.getElementById("qrcode"), {
          text: <?php echo json_encode($jsonData); ?>,
          width: 300,
          height: 300,
          colorDark : "#000000",
          colorLight : "#ffffff",
          correctLevel : QRCode.CorrectLevel.H
      });
    }
    function clearQRCode() {
      document.getElementById("qrcode").innerHTML = "";
    }
    $('#qrModal').on('shown.bs.modal', function () {
      generateQRCode();
    });
    $('#qrModal').on('hidden.bs.modal', function () {
      clearQRCode();
    });
  </script>
  <?php include '../includes/footer.php'; ?>
</body>
</html>
