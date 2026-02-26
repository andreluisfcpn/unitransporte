<?php 
$pageTitle = "Cadastro de Ônibus";
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="pt-BR" ng-app="busApp">
<head>
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Gerenciamento de Ônibus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS (v4.5) -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- AngularJS 1.8 -->
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
  <style>
    .table-responsive {
      max-height: 400px;
    }
    .pagination > li > a, .pagination > li > span {
      cursor: pointer;
    }
  </style>
</head>
<body ng-controller="BusController as ctrl">
  <?php include '../includes/header.php'; ?>

  <div class="container-xxl mt-4 ml-4 mr-4" ng-init="ctrl.init()">
    <h2 class="text-center mb-4">Gerenciamento de Ônibus</h2>

    <!-- Relatório de Ônibus -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Relatório de Ônibus</h4>
        <input type="text" class="form-control w-50" placeholder="Pesquisar (mínimo 3 caracteres)" 
               ng-model="ctrl.searchTerm" ng-change="ctrl.searchBuses()">
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>Identificador</th>
                <th>Ponto de Partida</th>
                <th>Horário de Ida</th>
                <th>Horário de Volta</th>
                <th>Itinerário</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr ng-repeat="bus in ctrl.filteredBuses | limitTo: ctrl.pageSize : (ctrl.currentPage-1)*ctrl.pageSize">
                <td>{{ bus.id }}</td>
                <td>{{ bus.identificador }}</td>
                <td>{{ bus.ponto_partida }}</td>
                <td>{{ bus.horario_ida }}</td>
                <td>{{ bus.horario_volta }}</td>
                <td>{{ bus.itinerario }}</td>
                <td>
                  <button class="btn btn-sm btn-warning" ng-click="ctrl.openEditModal(bus)">Editar</button>
                  <br/>
                  <button class="btn btn-sm btn-danger mt-1" ng-click="ctrl.deleteBus(bus.id)">Excluir</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Paginação -->
      <div class="card-footer d-flex justify-content-center">
        <ul class="pagination mb-0">
          <li class="page-item" ng-class="{disabled: ctrl.currentPage==1}">
            <span class="page-link" ng-click="ctrl.changePage(ctrl.currentPage-1)">&laquo;</span>
          </li>
          <li class="page-item" ng-repeat="p in [].constructor(ctrl.totalPages) track by $index" ng-class="{active: ($index+1)==ctrl.currentPage}">
            <span class="page-link" ng-click="ctrl.changePage($index+1)">{{$index+1}}</span>
          </li>
          <li class="page-item" ng-class="{disabled: ctrl.currentPage==ctrl.totalPages}">
            <span class="page-link" ng-click="ctrl.changePage(ctrl.currentPage+1)">&raquo;</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Botão para abrir modal de cadastro -->
    <div class="text-center mb-3">
      <button class="btn btn-primary" ng-click="ctrl.openCreateModal()">
        {{ ctrl.editMode ? "Ocultar Cadastro" : "Cadastrar Novo Ônibus" }}
      </button>
    </div>
  </div>

  <!-- Modal para Cadastro/Edição de Ônibus -->
  <div class="modal fade" id="busModal" tabindex="-1" role="dialog" aria-labelledby="busModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
      <div class="modal-content">
        <form name="busForm" novalidate ng-submit="ctrl.saveBus()">
          <div class="modal-header">
            <h5 class="modal-title" id="busModalLabel">{{ ctrl.editMode ? "Editar Ônibus" : "Cadastrar Novo Ônibus" }}</h5>
            <button type="button" class="close" ng-click="ctrl.closeBusModal()" aria-label="Fechar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
              <!-- Linha 1: Identificador e Ponto de Partida -->
              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="form-group">
                    <label>Identificador do Ônibus</label>
                    <input type="text" class="form-control" ng-model="ctrl.currentBus.identificador" required>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="form-group">
                    <label>Ponto de Partida</label>
                    <input type="text" class="form-control" ng-model="ctrl.currentBus.ponto_partida" required>
                  </div>
                </div>
              </div>
              <!-- Linha 2: Horário de Ida e Horário de Volta -->
              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="form-group">
                    <label>Horário de Ida</label>
                    <input type="time" class="form-control" ng-model="ctrl.currentBus.horario_ida" required>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="form-group">
                    <label>Horário de Volta</label>
                    <input type="time" class="form-control" ng-model="ctrl.currentBus.horario_volta" required>
                  </div>
                </div>
              </div>
              <!-- Linha 3: Itinerário -->
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label>Itinerário</label>
                    <textarea class="form-control" ng-model="ctrl.currentBus.itinerario" rows="3"></textarea>
                  </div>
                </div>
              </div>
            </div> <!-- container-fluid -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" ng-click="ctrl.closeBusModal()">Fechar</button>
            <button type="submit" class="btn btn-primary">{{ ctrl.editMode ? "Salvar Alterações" : "Cadastrar" }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Máscaras simples em JavaScript (vanilla) -->
  <script>
    function applyMaskEmail(value) { return value; }
  </script>

  <!-- AngularJS App -->
  <script>
    angular.module('busApp', [])
    .controller('BusController', ['$scope', '$http', function($scope, $http) {
      var vm = this;
      vm.buses = [];
      vm.filteredBuses = [];
      vm.pageSize = 20;
      vm.currentPage = 1;
      vm.searchTerm = "";
      vm.editMode = false;
      vm.currentBus = {};
      
      vm.init = function() {
        vm.loadBuses();
      };

      vm.loadBuses = function() {
        $http.get('/admin/api/get_buses.php').then(function(response) {
          vm.buses = response.data;
          vm.filteredBuses = angular.copy(vm.buses);
          vm.currentPage = 1;
          vm.updatePagination();
        }, function(error) {
          alert("Erro ao carregar ônibus.");
        });
      };

      vm.searchBuses = function() {
        if(vm.searchTerm.length < 3) {
          vm.filteredBuses = angular.copy(vm.buses);
        } else {
          var term = vm.searchTerm.toLowerCase();
          vm.filteredBuses = vm.buses.filter(function(bus) {
            return bus.identificador.toLowerCase().indexOf(term) !== -1 ||
                   bus.ponto_partida.toLowerCase().indexOf(term) !== -1;
          });
        }
        vm.currentPage = 1;
        vm.updatePagination();
      };

      vm.updatePagination = function() {
        vm.totalPages = Math.ceil(vm.filteredBuses.length / vm.pageSize) || 1;
      };

      vm.changePage = function(page) {
        if(page >= 1 && page <= vm.totalPages) {
          vm.currentPage = page;
        }
      };

      // Modal para criar/editar ônibus
      vm.openCreateModal = function() {
        vm.editMode = false;
        vm.currentBus = {
          identificador: "",
          ponto_partida: "",
          horario_ida: "",
          horario_volta: "",
          itinerario: ""
        };
        $("#busModal").modal("show");
      };

      vm.openEditModal = function(bus) {
        vm.editMode = true;
        vm.currentBus = angular.copy(bus);
        $("#busModal").modal("show");
      };

      vm.closeBusModal = function() {
        $("#busModal").modal("hide");
      };

      vm.saveBus = function() {
        var endpoint = vm.editMode ? '/admin/api/update_bus.php' : '/admin/api/save_bus.php';
        var payload = angular.copy(vm.currentBus);
        $http.post(endpoint, payload).then(function(response) {
          alert(response.data.message);
          vm.loadBuses();
          $("#busModal").modal("hide");
        }, function(error) {
          alert("Erro ao salvar ônibus: " + (error.data.message || error.statusText));
        });
      };

vm.deleteBus = function(busId) {
  // Primeira tentativa sem override
  $http.post('/admin/api/delete_bus.php', { id: busId })
    .then(function(response) {
      alert(response.data.message);
      vm.loadBuses(); // Atualize a lista de ônibus
    }, function(error) {
      if (error.data && error.data.requires_override) {
        if (confirm(error.data.message)) {
          // Se o usuário confirmar, envie novamente com override=1
          $http.post('/admin/api/delete_bus.php', { id: busId, override: 1 })
            .then(function(response) {
              alert(response.data.message);
              vm.loadBuses();
            }, function(err) {
              alert("Erro ao excluir ônibus: " + (err.data.message || err.statusText));
            });
        }
      } else {
        alert("Erro ao excluir ônibus: " + (error.data.message || error.statusText));
      }
    });
};


      vm.init();
    }]);
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>

