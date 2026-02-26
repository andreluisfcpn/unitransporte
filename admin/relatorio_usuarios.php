<?php
$pageTitle = "Relatório de Usuários"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';
?>

<div class="container-xxl mt-4 ml-4 mr-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Relatório de Viagens dos Usuários</h2>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="card-title mb-0">Pesquisar Usuário</h5>
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

<!-- Template para o relatório por mês -->
<template id="monthTemplate">
  <div class="month-section mb-4">
    <h4 class="month-title bg-light p-2 rounded"></h4>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Data/Hora</th>
            <th>Ônibus</th>
            <th>Local</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody class="month-trips">
        </tbody>
      </table>
    </div>
  </div>
</template>

<!-- Template para viagens de um dia específico -->
<template id="dayTemplate">
  <div class="day-section mb-4">
    <h4 class="day-title bg-light p-2 rounded"></h4>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>Hora</th>
            <th>Ônibus</th>
            <th>Local</th>
            <th>Status</th>
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
  const searchInput = document.getElementById('searchUser');
  const searchResults = document.getElementById('searchResults');
  const selectedUser = document.getElementById('selectedUser');
  const userName = document.getElementById('userName');
  const userCpf = document.getElementById('userCpf');
  const dateSelector = document.getElementById('dateSelector');
  const tripDate = document.getElementById('tripDate');
  const generateReport = document.getElementById('generateReport');
  const reportArea = document.getElementById('reportArea');
  const reportContent = document.getElementById('reportContent');
  
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

  // Gerar relatório
  generateReport.addEventListener('click', function() {
    if (!selectedUserId) return;
    
    // Mostrar área do relatório
    reportArea.classList.remove('d-none');
    
    // Mostrar indicador de carregamento
    reportContent.innerHTML = '<div class="text-center my-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Carregando relatório...</p></div>';
    
    // Construir a URL da API
    let apiUrl = `api/get_user_trips.php?user_id=${selectedUserId}`;
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
            reportContent.innerHTML = '<div class="alert alert-info">Nenhuma viagem encontrada para este usuário.</div>';
            return;
          }
          
          // Limpar o conteúdo anterior
          reportContent.innerHTML = '';
          
          // Se uma data específica foi selecionada
          if (tripDate.value) {
            renderDayReport(data, tripDate.value);
          } else {
            renderMonthlyReport(data);
          }
          
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

  // Função para renderizar relatório por mês
  function renderMonthlyReport(trips) {
    // Agrupar viagens por mês
    const tripsByMonth = {};
    
    trips.forEach(trip => {
      const date = new Date(trip.data_viagem);
      const monthYear = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
      
      if (!tripsByMonth[monthYear]) {
        tripsByMonth[monthYear] = [];
      }
      
      tripsByMonth[monthYear].push(trip);
    });
    
    // Ordenar meses (mais recentes primeiro)
    const sortedMonths = Object.keys(tripsByMonth).sort().reverse();
    
    // Renderizar cada mês
    sortedMonths.forEach(month => {
      const monthTemplate = document.getElementById('monthTemplate').content.cloneNode(true);
      const [year, monthNum] = month.split('-');
      const monthName = getMonthName(parseInt(monthNum) - 1);
      
      monthTemplate.querySelector('.month-title').textContent = `${monthName} de ${year}`;
      
      const tbody = monthTemplate.querySelector('.month-trips');
      
      // Adicionar cada viagem à tabela
      tripsByMonth[month].forEach(trip => {
        const row = document.createElement('tr');
        
        // Formatar data e hora
        const tripDate = new Date(trip.data_viagem);
        const formattedDate = tripDate.toLocaleDateString('pt-BR');
        const formattedTime = tripDate.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        
        // Formatar localização
        let location = 'Não foi detectado';
        if (trip.latitude && trip.longitude) {
          location = `Lat: ${trip.latitude}, Long: ${trip.longitude}`;
        }
        
        row.innerHTML = `
          <td>${formattedDate} às ${formattedTime}</td>
          <td>${trip.onibus || 'Não registrado'}</td>
          <td>${location}</td>
          <td>${trip.status || 'Registrado'}</td>
        `;
        
        tbody.appendChild(row);
      });
      
      reportContent.appendChild(monthTemplate);
    });
  }

  // Função para renderizar relatório de um dia específico
  function renderDayReport(trips, selectedDate) {
    const dayTemplate = document.getElementById('dayTemplate').content.cloneNode(true);
    
    // Formatar a data para exibição
    const date = new Date(selectedDate);
    const formattedDate = date.toLocaleDateString('pt-BR', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
    
    dayTemplate.querySelector('.day-title').textContent = `Viagens em ${formattedDate}`;
    
    const tbody = dayTemplate.querySelector('.day-trips');
    
    // Adicionar cada viagem à tabela
    trips.forEach(trip => {
      const row = document.createElement('tr');
      
      // Formatar hora
      const tripDate = new Date(trip.data_viagem);
      const formattedTime = tripDate.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
      
      // Formatar localização
      let location = 'Não foi detectado';
      if (trip.latitude && trip.longitude) {
        location = `Lat: ${trip.latitude}, Long: ${trip.longitude}`;
      }
      
      row.innerHTML = `
        <td>${formattedTime}</td>
        <td>${trip.onibus || 'Não registrado'}</td>
        <td>${location}</td>
        <td>${trip.status || 'Registrado'}</td>
      `;
      
      tbody.appendChild(row);
    });
    
    reportContent.appendChild(dayTemplate);
  }

  // Função para obter o nome do mês
  function getMonthName(monthIndex) {
    const months = [
      'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
      'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    return months[monthIndex];
  }
});
</script>

<?php include '../includes/footer.php'; ?>
