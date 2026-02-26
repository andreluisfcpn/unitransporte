<?php 
$pageTitle = "Relatório de Motoristas"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';
?>

<div class="container-xxl mt-4 ml-4 mr-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Relatório de Viagens dos Motoristas</h2>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Pesquisar Motorista</h5>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label for="searchDriver">Pesquisar por Nome ou CPF:</label>
        <input type="text" id="searchDriver" class="form-control" placeholder="Digite pelo menos 2 caracteres" autocomplete="off">
      </div>
      <div id="searchResults" class="list-group mt-2 mb-3" style="max-height: 200px; overflow-y: auto;"></div>
      <div id="selectedDriver" class="alert alert-info d-none">
        <p class="mb-0"><strong>Motorista selecionado:</strong> <span id="driverName"></span></p>
        <p class="mb-0"><strong>CPF:</strong> <span id="driverCpf"></span></p>
      </div>
      
      <div id="dateSelector" class="form-group mt-3 d-none">
        <label for="tripDate">Selecione uma data específica (opcional):</label>
        <input type="date" id="tripDate" class="form-control">
        <small class="form-text text-muted">Deixe em branco para ver todas as viagens</small>
      </div>
      
      <button id="generateReport" class="btn btn-primary mt-3 d-none">
        <i class="fas fa-search"></i> Gerar Relatório
      </button>
    </div>
  </div>

  <!-- Área do Relatório -->
  <div id="reportArea" class="d-none">
    <div class="card shadow-sm">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">Relatório de Viagens</h5>
      </div>
      <div class="card-body">
        <div id="reportContent"></div>
      </div>
    </div>
  </div>
</div>

<!-- Template para o relatório por dia -->
<template id="dayTemplate">
  <div class="day-section mb-4">
    <h4 class="day-title bg-light p-2 rounded"></h4>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Ônibus</th>
            <th>Horário de Ida</th>
            <th>Usuários na Ida</th>
            <th>Recusados na Ida</th>
            <th>Horário de Volta</th>
            <th>Usuários na Volta</th>
            <th>Recusados na Volta</th>
          </tr>
        </thead>
        <tbody class="day-trips">
        </tbody>
      </table>
    </div>
  </div>
</template>

<!-- JavaScript para funcionalidades da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchDriver');
  const searchResults = document.getElementById('searchResults');
  const selectedDriver = document.getElementById('selectedDriver');
  const driverName = document.getElementById('driverName');
  const driverCpf = document.getElementById('driverCpf');
  const dateSelector = document.getElementById('dateSelector');
  const tripDate = document.getElementById('tripDate');
  const generateReport = document.getElementById('generateReport');
  const reportArea = document.getElementById('reportArea');
  const reportContent = document.getElementById('reportContent');
  
  let selectedDriverId = null;
  let searchTimeout = null;

  // Pesquisa de motoristas em tempo real
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
      xhr.open('GET', `api/search_drivers.php?term=${encodeURIComponent(term)}`, true);
      
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            const data = JSON.parse(xhr.responseText);
            
            searchResults.innerHTML = '';
            
            if (data.length === 0) {
              const noResults = document.createElement('div');
              noResults.className = 'list-group-item';
              noResults.textContent = 'Nenhum motorista encontrado';
              searchResults.appendChild(noResults);
              return;
            }
            
            data.forEach(driver => {
              const item = document.createElement('a');
              item.href = '#';
              item.className = 'list-group-item list-group-item-action';
              item.innerHTML = `<strong>${driver.nome}</strong> - CPF: ${driver.cpf}`;
              
              item.addEventListener('click', function(e) {
                e.preventDefault();
                selectedDriverId = driver.id;
                driverName.textContent = driver.nome;
                driverCpf.textContent = driver.cpf;
                selectedDriver.classList.remove('d-none');
                dateSelector.classList.remove('d-none');
                generateReport.classList.remove('d-none');
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
          searchResults.innerHTML = '<div class="list-group-item text-danger">Erro ao buscar motoristas: ' + xhr.status + ' ' + xhr.statusText + '</div>';
        }
      };
      
      xhr.onerror = function() {
        console.error('Erro de rede na requisição');
        searchResults.innerHTML = '<div class="list-group-item text-danger">Erro de conexão com o servidor</div>';
      };
      
      xhr.send();
    }, 300);
  });

  // Gerar relatório
  generateReport.addEventListener('click', function() {
    if (!selectedDriverId) return;
    
    // Mostrar área do relatório
    reportArea.classList.remove('d-none');
    
    // Mostrar indicador de carregamento
    reportContent.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Carregando relatório...</p></div>';
    
    // Construir a URL da API
    let apiUrl = `api/get_driver_trips.php?driver_id=${selectedDriverId}`;
    if (tripDate.value) {
      apiUrl += `&date=${tripDate.value}`;
    }
    
    // Fazer a requisição para a API
    const xhr = new XMLHttpRequest();
    xhr.open('GET', apiUrl, true);
    
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          const data = JSON.parse(xhr.responseText);
          
          if (data.length === 0) {
            reportContent.innerHTML = '<div class="alert alert-info">Nenhuma viagem encontrada para este motorista.</div>';
            return;
          }
          
          // Limpar o conteúdo anterior
          reportContent.innerHTML = '';
          
          // Agrupar viagens por data
          const tripsByDate = {};
          
          data.forEach(trip => {
            if (!tripsByDate[trip.data]) {
              tripsByDate[trip.data] = [];
            }
            
            tripsByDate[trip.data].push(trip);
          });
          
          // Ordenar datas (mais recentes primeiro)
          const sortedDates = Object.keys(tripsByDate).sort().reverse();
          
          // Renderizar cada dia
          sortedDates.forEach(date => {
            const dayTemplate = document.getElementById('dayTemplate').content.cloneNode(true);
            const trips = tripsByDate[date];
            const firstTrip = trips[0]; // Usamos o primeiro registro para obter informações da data
            
            dayTemplate.querySelector('.day-title').textContent = `${firstTrip.dia_semana}, ${firstTrip.data_formatada}`;
            
            const tbody = dayTemplate.querySelector('.day-trips');
            
            // Adicionar cada viagem à tabela
            trips.forEach(trip => {
              const row = document.createElement('tr');
              
              row.innerHTML = `
                <td>${trip.onibus || 'Não registrado'}</td>
                <td>${trip.horario_ida_formatado || 'N/A'}</td>
                <td>${trip.usuarios_ida || '0'}</td>
                <td>${trip.recusados_ida || '0'}</td>
                <td>${trip.horario_volta_formatado || 'N/A'}</td>
                <td>${trip.usuarios_volta || '0'}</td>
                <td>${trip.recusados_volta || '0'}</td>
              `;
              
              tbody.appendChild(row);
            });
            
            reportContent.appendChild(dayTemplate);
          });
          
        } catch (error) {
          console.error('Erro ao processar resposta:', error);
          reportContent.innerHTML = '<div class="alert alert-danger">Erro ao processar dados do relatório.</div>';
        }
      } else {
        console.error('Erro na requisição:', xhr.status, xhr.statusText);
        reportContent.innerHTML = '<div class="alert alert-danger">Erro ao buscar dados do relatório: ' + xhr.status + ' ' + xhr.statusText + '</div>';
      }
    };
    
    xhr.onerror = function() {
      console.error('Erro de rede na requisição');
      reportContent.innerHTML = '<div class="alert alert-danger">Erro de conexão com o servidor</div>';
    };
    
    xhr.send();
  });
});
</script>

<?php include '../includes/footer.php'; ?>
