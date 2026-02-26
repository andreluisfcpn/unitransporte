<?php 
$pageTitle = "Relatório de Ônibus"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';
?>

<div class="container-xxl mt-4 ml-4 mr-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Relatório de Passageiros por Ônibus</h2>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Filtros</h5>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label for="tripDate">Selecione uma data específica (opcional):</label>
        <input type="date" id="tripDate" class="form-control mb-3">
        <small class="form-text text-muted">Deixe em branco para ver todos os dias</small>
      </div>
      
      <button id="generateReport" class="btn btn-primary mt-2">
        <i class="fas fa-search"></i> Gerar Relatório
      </button>
    </div>
  </div>

  <!-- Área do Relatório -->
  <div id="reportArea" class="d-none">
    <!-- Filtros de ônibus -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">Filtrar por Ônibus</h5>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <button id="selectAllBuses" class="btn btn-sm btn-outline-primary me-2">Selecionar Todos</button>
          <button id="deselectAllBuses" class="btn btn-sm btn-outline-secondary">Desmarcar Todos</button>
        </div>
        <div id="busFilterButtons" class="d-flex flex-wrap gap-2">
          <!-- Botões de ônibus serão inseridos aqui dinamicamente -->
        </div>
      </div>
    </div>
    
    <!-- Resultados do relatório -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Relatório de Passageiros</h5>
        <div class="bus-filter-info text-white small">
          <span id="filteredBusCount">0</span> ônibus exibidos
        </div>
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
            <th>Passageiros na Ida</th>
            <th>Recusados na Ida</th>
            <th>Horário de Volta</th>
            <th>Passageiros na Volta</th>
            <th>Recusados na Volta</th>
          </tr>
        </thead>
        <tbody class="day-trips">
        </tbody>
      </table>
    </div>
  </div>
</template>

<!-- Template para botão de filtro de ônibus -->
<template id="busButtonTemplate">
  <button class="btn btn-sm btn-outline-primary bus-filter-btn" data-bus-id="">
    <!-- Nome do ônibus será inserido aqui -->
  </button>
</template>

<!-- JavaScript para funcionalidades da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tripDate = document.getElementById('tripDate');
  const generateReport = document.getElementById('generateReport');
  const reportArea = document.getElementById('reportArea');
  const reportContent = document.getElementById('reportContent');
  const filteredBusCount = document.getElementById('filteredBusCount');
  const busFilterButtons = document.getElementById('busFilterButtons');
  const selectAllBuses = document.getElementById('selectAllBuses');
  const deselectAllBuses = document.getElementById('deselectAllBuses');
  
  let allTripsData = []; // Armazenar todos os dados dos ônibus
  let busesInfo = []; // Lista de ônibus únicos
  
  // Selecionar todos os ônibus
  selectAllBuses.addEventListener('click', function() {
    document.querySelectorAll('.bus-filter-btn').forEach(btn => {
      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-primary');
      btn.setAttribute('data-active', 'true');
    });
    
    filterBusesByButtons();
  });
  
  // Desmarcar todos os ônibus
  deselectAllBuses.addEventListener('click', function() {
    document.querySelectorAll('.bus-filter-btn').forEach(btn => {
      btn.classList.remove('btn-primary');
      btn.classList.add('btn-outline-primary');
      btn.setAttribute('data-active', 'false');
    });
    
    filterBusesByButtons();
  });
  
  // Função para criar os botões de filtro de ônibus
  function createBusFilterButtons(data) {
    // Limpar área de botões
    busFilterButtons.innerHTML = '';
    
    // Extrair ônibus únicos dos dados
    busesInfo = Array.from(new Set(data.map(trip => trip.onibus || 'Não registrado')))
                      .sort();
    
    // Criar botão para cada ônibus
    busesInfo.forEach(busName => {
      const buttonTemplate = document.getElementById('busButtonTemplate').content.cloneNode(true);
      const button = buttonTemplate.querySelector('.bus-filter-btn');
      
      button.textContent = busName;
      button.setAttribute('data-bus-id', busName);
      button.setAttribute('data-active', 'true');
      button.classList.remove('btn-outline-primary');
      button.classList.add('btn-primary');
      
      // Adicionar evento de clique
      button.addEventListener('click', function() {
        const isActive = this.getAttribute('data-active') === 'true';
        
        if (isActive) {
          // Desativar
          this.setAttribute('data-active', 'false');
          this.classList.remove('btn-primary');
          this.classList.add('btn-outline-primary');
        } else {
          // Ativar
          this.setAttribute('data-active', 'true');
          this.classList.remove('btn-outline-primary');
          this.classList.add('btn-primary');
        }
        
        filterBusesByButtons();
      });
      
      busFilterButtons.appendChild(button);
    });
  }
  
  // Função para filtrar ônibus baseado nos botões selecionados
  function filterBusesByButtons() {
    // Coletar ônibus ativos
    const activeBuses = Array.from(document.querySelectorAll('.bus-filter-btn[data-active="true"]'))
                            .map(btn => btn.getAttribute('data-bus-id'));
    
    let visibleBusCount = 0;
    
    // Percorrer todas as linhas das tabelas
    document.querySelectorAll('.day-trips tr').forEach(row => {
      const busName = row.getAttribute('data-bus-id');
      
      // Determinar se a linha deve ser visível
      const isVisible = activeBuses.includes(busName);
      
      // Aplicar visibilidade
      row.style.display = isVisible ? '' : 'none';
      
      if (isVisible) visibleBusCount++;
    });
    
    // Atualizar contador de ônibus visíveis
    filteredBusCount.textContent = visibleBusCount;
    
    // Verificar se todas as linhas em uma seção de dia estão ocultas
    document.querySelectorAll('.day-section').forEach(daySection => {
      const allRowsHidden = Array.from(daySection.querySelectorAll('.day-trips tr'))
                                 .every(row => row.style.display === 'none');
      
      daySection.style.display = allRowsHidden ? 'none' : '';
    });
  }

  // Gerar relatório
  generateReport.addEventListener('click', function() {
    // Mostrar área do relatório
    reportArea.classList.remove('d-none');
    
    // Mostrar indicador de carregamento
    reportContent.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Carregando relatório...</p></div>';
    
    // Construir a URL da API
    let apiUrl = 'api/get_bus_passenger_counts.php';
    if (tripDate.value) {
      apiUrl += `?date=${tripDate.value}`;
    }
    
    // Fazer a requisição para a API
    fetch(apiUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error(`Erro HTTP: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        // Armazenar dados para uso na filtragem
        allTripsData = data;
        
        if (data.length === 0) {
          reportContent.innerHTML = '<div class="alert alert-info">Nenhuma viagem encontrada para o período selecionado.</div>';
          busFilterButtons.innerHTML = '';
          return;
        }
        
        // Criar botões de filtro para ônibus
        createBusFilterButtons(data);
        
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
            // Guardar o ID do ônibus como atributo para facilitar a filtragem
            const busId = trip.onibus || 'Não registrado';
            row.setAttribute('data-bus-id', busId);
            
            row.innerHTML = `
              <td>${busId}</td>
              <td>${trip.horario_ida_formatado || 'N/A'}</td>
              <td>${trip.passageiros_ida || '0'}</td>
              <td>${trip.recusados_ida || '0'}</td>
              <td>${trip.horario_volta_formatado || 'N/A'}</td>
              <td>${trip.passageiros_volta || '0'}</td>
              <td>${trip.recusados_volta || '0'}</td>
            `;
            
            tbody.appendChild(row);
          });
          
          reportContent.appendChild(dayTemplate);
        });
        
        // Atualizar contador de ônibus
        filteredBusCount.textContent = data.length;
      })
      .catch(error => {
        console.error('Erro na requisição:', error);
        reportContent.innerHTML = '<div class="alert alert-danger">Erro ao buscar dados do relatório: ' + error.message + '</div>';
      });
  });
});
</script>

<?php include '../includes/footer.php'; ?>
