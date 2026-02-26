<?php
$pageTitle = "Imprimir Carteiras Universitárias"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';
?>

<div class="container-xxl mt-4 ml-4 mr-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Impressão de Carteiras Universitárias</h2>
    </div>
  </div>

  <div class="row">
    <!-- Card para Pesquisa Individual -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0">Imprimir Carteira Individual</h5>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="searchUser">Pesquisar por Nome ou CPF:</label>
            <input type="text" id="searchUser" class="form-control" placeholder="Digite pelo menos 2 caracteres" autocomplete="off">
          </div>
          <div id="searchResults" class="list-group mt-2 mb-3" style="max-height: 200px; overflow-y: auto;"></div>
          <div id="selectedUser" class="alert alert-info d-none">
            <p class="mb-0"><strong>Usuário selecionado:</strong> <span id="userName"></span></p>
            <p class="mb-0"><strong>CPF:</strong> <span id="userCpf"></span></p>
          </div>
          <button id="printIndividual" class="btn btn-primary btn-block mt-3 d-none">
            <i class="fas fa-print"></i> Imprimir Carteira
          </button>
        </div>
      </div>
    </div>

    <!-- Card para Impressão em Lote -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="card-title mb-0">Imprimir Carteiras em Lote</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Imprima todas as carteiras universitárias em lote, organizadas em 4 carteiras por página.</p>
          <button id="printBatch" class="btn btn-success btn-block mt-3">
            <i class="fas fa-print"></i> Imprimir Carteiras em Lote
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Visualização de Impressão (oculta) -->
  <div id="printPreview" class="d-none">
    <div id="printContent"></div>
  </div>
</div>

<!-- Modal de Carregamento -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="sr-only">Carregando...</span>
        </div>
        <h5>Gerando carteiras, por favor aguarde...</h5>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript para funcionalidades da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchUser');
  const searchResults = document.getElementById('searchResults');
  const selectedUser = document.getElementById('selectedUser');
  const userName = document.getElementById('userName');
  const userCpf = document.getElementById('userCpf');
  const printIndividual = document.getElementById('printIndividual');
  const printBatch = document.getElementById('printBatch');
  
  let selectedUserId = null;
  let searchTimeout = null;

  // Pesquisa de usuários em tempo real
  searchInput.addEventListener('input', function() {
    const term = this.value.trim();
    
    // Limpar o timeout anterior
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    
    // Limpar resultados se o campo estiver vazio
    if (term.length < 2) {
      searchResults.innerHTML = '';
      return;
    }
    
    // Definir um novo timeout para evitar muitas requisições
    searchTimeout = setTimeout(function() {
      // Mostrar indicador de carregamento
      searchResults.innerHTML = '<div class="list-group-item text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Pesquisando...</div>';
      
      // Usar XMLHttpRequest em vez de fetch para melhor compatibilidade
      const xhr = new XMLHttpRequest();
      xhr.open('GET', `api/search_users.php?term=${encodeURIComponent(term)}`, true);
      
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const data = JSON.parse(xhr.responseText);
            
            searchResults.innerHTML = '';
            
            if (data.length === 0) {
              const noResults = document.createElement('div');
              noResults.className = 'list-group-item';
              noResults.textContent = 'Nenhum usuário encontrado';
              searchResults.appendChild(noResults);
              return;
            }
            
            data.forEach(user => {
              const item = document.createElement('a');
              item.href = '#';
              item.className = 'list-group-item list-group-item-action';
              item.innerHTML = `<strong>${user.nome}</strong> - CPF: ${user.cpf}`;
              
              item.addEventListener('click', function(e) {
                e.preventDefault();
                selectedUserId = user.id;
                userName.textContent = user.nome;
                userCpf.textContent = user.cpf;
                selectedUser.classList.remove('d-none');
                printIndividual.classList.remove('d-none');
                searchResults.innerHTML = '';
                searchInput.value = '';
              });
              
              searchResults.appendChild(item);
            });
          } catch (error) {
            console.error('Erro ao processar resposta:', error);
            searchResults.innerHTML = '<div class="list-group-item text-danger">Erro ao processar resposta do servidor</div>';
          }
        } else {
          console.error('Erro na requisição:', xhr.status, xhr.statusText);
          searchResults.innerHTML = '<div class="list-group-item text-danger">Erro ao buscar usuários: ' + xhr.status + ' ' + xhr.statusText + '</div>';
        }
      };
      
      xhr.onerror = function() {
        console.error('Erro de rede na requisição');
        searchResults.innerHTML = '<div class="list-group-item text-danger">Erro de conexão com o servidor</div>';
      };
      
      xhr.send();
    }, 300);
  });

  // Impressão de carteira individual
  printIndividual.addEventListener('click', function() {
    if (!selectedUserId) return;
    
    // Redirecionar para a página de impressão individual
    window.location.href = `imprimir_carteira.php?id=${selectedUserId}`;
  });

  // Impressão em lote
  printBatch.addEventListener('click', function() {
    // Mostrar modal de carregamento
    $('#loadingModal').modal('show');
    
    // Abrir a página de impressão em lote em uma nova aba
    const printWindow = window.open('imprimir_carteiras_lote.php', '_blank');
    
    // Fechar o modal após um tempo
    setTimeout(() => {
      $('#loadingModal').modal('hide');
    }, 2000);
  });
});
</script>

<?php include '../includes/footer.php'; ?>
