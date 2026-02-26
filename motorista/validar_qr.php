<?php 
$pageTitle = "Validação de QR Code"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('motorista');

// Obtenha a lista de ônibus para o dropdown
$stmtBuses = $pdo->query("SELECT id, identificador FROM buses ORDER BY identificador ASC");
$buses = $stmtBuses->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Validação de QR Code</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    #reader {
      width: 100%;
      max-width: 500px;
      margin: 20px auto;
      background-color: #000;
      border: 1px solid #ccc;
      display: none;
    }
    .permissao-ok { color: green; }
    .permissao-neg { color: red; }
    
    /* Melhorias de responsividade */
    @media (max-width: 576px) {
      .container {
        padding-left: 10px;
        padding-right: 10px;
      }
      
      .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1.1rem;
      }
      
      .modal-dialog {
        margin: 0.5rem;
      }
      
      .modal-body {
        padding: 1rem;
      }
      
      h2 {
        font-size: 1.5rem;
      }
      
      #reader {
        max-width: 100%;
        height: auto;
      }
    }
  </style>
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <div class="container mt-4">
    <div class="row text-center">
      <div class="col">
        <h2 class="mt-4">Scanner de QR Code</h2>
        <p>Aponte a câmera para o QR Code fixo do usuário.</p>
        <button id="startValidationBtn" class="btn btn-primary btn-lg mb-4">Validar QR Code</button>
        <div id="reader"></div>
      </div>
    </div>
  </div>
  
  <!-- Campo oculto para armazenar o ID do ônibus selecionado -->
  <input type="hidden" id="busSelect" value="">
  
  <!-- Modal para seleção do ônibus -->
  <div class="modal fade" id="busSelectionModal" tabindex="-1" role="dialog" aria-labelledby="busSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="busSelectionModalLabel" class="modal-title">Selecione o Ônibus</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="modalBusSelect">Selecione o Ônibus em que está:</label>
            <select id="modalBusSelect" class="form-control">
              <option value="">Selecione...</option>
              <?php foreach($buses as $bus): ?>
                <option value="<?php echo $bus['id']; ?>"><?php echo htmlspecialchars($bus['identificador']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" id="confirmBusBtn" class="btn btn-primary">Iniciar Scanner</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Modal para exibir os dados do usuário -->
  <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 id="userModalLabel" class="modal-title">Dados do Usuário</h5>
            <div id="selectedBusInfo" class="text-primary font-weight-bold"></div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" id="closeModalBtn">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="modalUserInfo">
          <!-- Os dados do usuário serão exibidos aqui -->
        </div>
        <div class="modal-footer" id="modalFooter">
          <!-- Os botões serão ajustados conforme a permissão -->
          <button type="button" id="recuseBtn" class="btn btn-danger">Recusar</button>
          <button type="button" id="confirmBtn" class="btn btn-success">Confirmar</button>
        </div>
      </div>
    </div>
  </div>
  
  <?php include '../includes/footer.php'; ?>
  
  <!-- Scripts: jQuery, Bootstrap, html5-qrcode -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <script>
    let currentUserId = null;
    let html5QrCode;
    let currentLatitude = "";
    let currentLongitude = "";
    let scannerActive = false;
    let selectedBusId = ""; // Variável para armazenar o ID do ônibus selecionado
    
    // Atualiza a posição ao carregar a página (para minimizar atrasos na leitura)
    function atualizarPosicao() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          currentLatitude = position.coords.latitude;
          currentLongitude = position.coords.longitude;
        }, function(error) {
          console.error("Erro ao obter localização:", error);
          currentLatitude = "";
          currentLongitude = "";
        });
      } else {
        console.error("Geolocalização não suportada.");
      }
    }
    atualizarPosicao();
    
    // Função para mapear getDay() para nomes de dias em português (minúsculas)
    function getDiaSemana() {
      const dias = ["domingo", "segunda", "terca", "quarta", "quinta", "sexta", "sabado"];
      return dias[new Date().getDay()];
    }
    
    function onScanSuccess(decodedText, decodedResult) {
      console.log("QR Code detectado:", decodedText);
      html5QrCode.stop().then(() => {
        // Atualiza a posição novamente antes de processar o QR Code
        atualizarPosicao();
        processQRCode(decodedText);
        $("#reader").hide();
        scannerActive = false;
      }).catch(err => {
        console.error("Erro ao parar scanner:", err);
      });
    }
    
    function onScanFailure(error) {
      console.warn("Falha na leitura do QR Code: " + error);
    }
    
    function startScanner() {
      $("#userModal").modal('hide');
      currentUserId = null;
      
      // Usar o valor armazenado na variável global
      if (!selectedBusId) {
        alert("Por favor, selecione o ônibus em que está.");
        $("#busSelectionModal").modal('show');
        return;
      }
      
      // Garantir que o valor está no campo hidden para compatibilidade
      $("#busSelect").val(selectedBusId);
      
      $("#reader").show();
      scannerActive = true;
      
      const config = { fps: 10, qrbox: 250 };
      html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
      .catch(err => {
        console.error("Erro ao iniciar scanner:", err);
        alert("Erro ao iniciar scanner: " + err);
        $("#reader").hide();
        scannerActive = false;
      });
    }
    
    function processQRCode(decodedText) {
      $.ajax({
        url: 'buscar_usuario.php',
        method: 'POST',
        dataType: 'json',
        data: { qrData: decodedText },
        success: function(response) {
          if(response.status === 'sucesso') {
            const user = response.data;
            currentUserId = user.id;
            
            // Log para debugar os dados do usuário
            console.log("Dados do usuário recebidos:", user);
            
            // Atualizar informação do ônibus selecionado no modal
            let busName = getBusNameById(selectedBusId);
            $("#userModalLabel").text("Validação Ônibus");
            $("#selectedBusInfo").text("Ônibus escolhido: " + busName);
            
            // Construir HTML com a foto - abordagem simplificada e robusta
            let html = "<div class='d-flex align-items-center'>";
            
            // Adicionar a foto do usuário com fallback para imagem padrão
            const fotoUrl = user.foto ? `/assets/uploads/${user.foto}` : `/assets/img/default_user.png`;
            html += `<div style="width:80px; height:80px; margin-right:15px; border-radius:50%; overflow:hidden; background-color:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                <img 
                    src="${fotoUrl}" 
                    alt="Foto do usuário" 
                    style="width:100%; height:100%; object-fit:cover;"
                    onerror="this.onerror=null; this.src='/assets/img/default_user.png'; console.log('Erro ao carregar imagem, usando padrão');">
            </div>`;
            
            html += "<div>";
            html += "<p><strong>Nome:</strong> " + user.nome + "</p>";
            html += "<p><strong>Email:</strong> " + user.email + "</p>";
            html += "<p><strong>CPF:</strong> " + (user.cpf ? user.cpf : "Não informado") + "</p>";
            html += "</div></div>";
            
            // Verifica se o dia atual está agendado para o usuário e se o ônibus selecionado bate
            let diaAtual = getDiaSemana();
            let permitido = false;
            let mensagemPermissao = "";
            
            // Verificando permissão diretamente no front-end (verificação preliminar)
            const schedule = user.schedule; // Formato original para compatibilidade
            const scheduleMultiple = user.schedule_multiple; // Novo formato com múltiplos ônibus
            const busSelecionado = selectedBusId; 
            
            console.log("Dia atual:", diaAtual);
            console.log("Schedule:", schedule);
            console.log("Schedule múltiplo:", scheduleMultiple);
            console.log("Ônibus selecionado:", busSelecionado);
            
            // Primeiro verificar o formato padrão para compatibilidade
            if(schedule && schedule[diaAtual] && busSelecionado) {
                if(parseInt(busSelecionado) === parseInt(schedule[diaAtual])) {
                    permitido = true;
                    console.log("Permissão encontrada no formato padrão");
                }
            }
            
            // Se não for permitido pelo formato padrão, verificar o formato múltiplo
            if(!permitido && scheduleMultiple && scheduleMultiple[diaAtual] && busSelecionado) {
                // Procurar o ônibus selecionado na lista de ônibus permitidos para este dia
                for(let i = 0; i < scheduleMultiple[diaAtual].length; i++) {
                    const onibus = scheduleMultiple[diaAtual][i];
                    console.log("Verificando ônibus:", onibus);
                    if(parseInt(onibus.bus_id) === parseInt(busSelecionado)) {
                        permitido = true;
                        console.log("Permissão encontrada no formato múltiplo");
                        break;
                    }
                }
            }
            
            // Verificar a permissão com o servidor (verificação final)
            $.ajax({
                url: 'verificar_acesso.php',
                method: 'POST',
                dataType: 'json',
                data: { 
                    userId: currentUserId, 
                    busId: busSelecionado
                },
                success: function(response) {
                    console.log("Resposta de verificação do servidor:", response);
                    
                    // O servidor tem a palavra final sobre a permissão
                    if(response.status === 'autorizado') {
                        permitido = true;
                        mensagemPermissao = "<p class='permissao-ok'><strong>Acesso permitido.</strong></p>";
                    } else {
                        permitido = false;
                        
                        // Se houver mensagem detalhada do servidor, exibir
                        if(response.detalhes) {
                            mensagemPermissao = "<p class='permissao-neg'><strong>Não autorizado para este ônibus.</strong></p>";
                            mensagemPermissao += "<p class='permissao-neg'>" + response.detalhes + "</p>";
                        } else {
                            mensagemPermissao = "<p class='permissao-neg'><strong>Fora dos dias permitidos.</strong></p>";
                        }
                    }
                    
                    // Controlar exibição do botão de recusa baseado na permissão
                    if(permitido) {
                        $("#recuseBtn").hide();
                    } else {
                        $("#recuseBtn").show();
                    }
                    
                    // Atualizar a mensagem no modal
                    $("#modalUserInfo").html(mensagemPermissao + html);
                    
                    // Exibir o modal do usuário
                    $("#userModal").modal('show');
                },
                error: function(xhr, status, error) {
                    console.error("Erro na verificação de acesso:", error);
                    permitido = false;
                    mensagemPermissao = "<p class='permissao-neg'><strong>Erro na verificação. Tente novamente.</strong></p>";
                    $("#recuseBtn").show();
                    $("#modalUserInfo").html(mensagemPermissao + html);
                    $("#userModal").modal('show');
                }
            });
          } else {
            alert("Usuário não encontrado ou QR Code inválido.");
            startScanner();
          }
        },
        error: function(xhr, status, error) {
          console.error("Erro ao buscar usuário:", error);
          alert("Erro na comunicação com o servidor.");
          startScanner();
        }
      });
    }
    
    // Função para obter o nome do ônibus a partir do ID
    function getBusNameById(busId) {
      let busName = "Desconhecido";
      $("#modalBusSelect option").each(function() {
        if ($(this).val() == busId) {
          busName = $(this).text();
          return false; // break the loop
        }
      });
      return busName;
    }
    
    $("#recuseBtn").click(function() {
      let busId = selectedBusId; // Usar a variável global
      if (!busId) {
        alert("Por favor, selecione o ônibus em que está.");
        return;
      }

      $.ajax({
        url: 'confirmar_viagem.php',
        method: 'POST',
        dataType: 'json',
        data: { 
          userId: currentUserId, 
          busId: busId, 
          action: 'recusar',
          latitude: currentLatitude,
          longitude: currentLongitude
        },
        success: function(response) {
          console.log("Resposta do servidor:", response); // Verifica a resposta
          if (response.status === 'sucesso') {
            alert("Viagem registrada como recusada.");
          } else {
            alert("Erro: " + response.mensagem);
          }
          currentUserId = null;
          $("#userModal").modal('hide');
          setTimeout(startScanner, 1000);
        },
        error: function(xhr, status, error) {
          console.error("Erro AJAX:", xhr.responseText); // Exibe a resposta do erro
          alert("Erro na comunicação com o servidor.");
          setTimeout(startScanner, 1000);
        }
      });
    });

    $("#confirmBtn").click(function(){
      let busId = selectedBusId; // Usar a variável global
      if(!busId) {
        alert("Por favor, selecione o ônibus em que está.");
        return;
      }
      // Se o botão Recusar estiver visível, a ação será 'excecao', caso contrário 'confirmar'
      let actionEnvio = ($("#recuseBtn").is(":visible")) ? 'excecao' : 'confirmar';
      $.ajax({
        url: 'confirmar_viagem.php',
        method: 'POST',
        dataType: 'json',
        data: { 
          userId: currentUserId, 
          busId: busId, 
          action: actionEnvio,
          latitude: currentLatitude,
          longitude: currentLongitude
        },
        success: function(response) {
          if(response.status === 'sucesso'){
            alert("Viagem registrada: " + response.mensagem);
          } else {
            alert("Erro: " + response.mensagem);
          }
          currentUserId = null;
          $("#userModal").modal('hide');
          setTimeout(startScanner, 1000);
        },
        error: function(xhr, status, error) {
          console.error("Erro ao confirmar viagem:", error);
          alert("Erro na comunicação com o servidor.");
          setTimeout(startScanner, 1000);
        }
      });
    });
    
    $("#closeModalBtn").click(function(){
      currentUserId = null;
      if (scannerActive) {
        html5QrCode.stop().then(() => {
          $("#reader").hide();
          scannerActive = false;
        }).catch(err => {
          console.error("Erro ao parar scanner:", err);
        });
      }
      setTimeout(startScanner, 1000);
    });
    
    // Manipulador para quando o modal de usuário é fechado (clicando fora ou no X)
    $('#userModal').on('hidden.bs.modal', function () {
      currentUserId = null;
      setTimeout(startScanner, 1000);
    });
    
    // Novo botão para iniciar o processo de validação
    $("#startValidationBtn").click(function() {
      $("#busSelectionModal").modal('show');
    });
    
    // Botão de confirmação no modal de seleção de ônibus
    $("#confirmBusBtn").click(function() {
      // Armazenar o valor selecionado na variável global
      selectedBusId = $("#modalBusSelect").val();
      
      if (!selectedBusId) {
        alert("Por favor, selecione o ônibus em que está.");
        return;
      }
      
      // Atualizar também o campo hidden para compatibilidade
      $("#busSelect").val(selectedBusId);
      
      $("#busSelectionModal").modal('hide');
      setTimeout(startScanner, 500); // Pequeno delay para garantir que o modal fechou
    });
    
    $(document).ready(function(){
      html5QrCode = new Html5Qrcode("reader");
      Html5Qrcode.getCameras().then(cameras => {
        if(cameras && cameras.length) {
          // Não iniciar o scanner automaticamente, aguardar clique no botão
          console.log("Câmeras disponíveis:", cameras.length);
        } else {
          alert("Nenhuma câmera encontrada.");
        }
      }).catch(err => {
        console.error("Erro ao obter câmeras:", err);
        alert("Erro ao obter câmeras: " + err);
      });
    });
  </script>
</body>
</html>

