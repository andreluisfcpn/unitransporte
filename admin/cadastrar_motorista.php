<?php 
$pageTitle = "Cadastro de Motoristas";
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="pt-BR" ng-app="motoristaApp">
<head>
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Gerenciamento de Motoristas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS (v4.5) -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- AngularJS 1.8 -->
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
  <style>
    .img-circle {
      border-radius: 50%;
      object-fit: cover;
    }
    .table-responsive {
      max-height: 400px;
    }
    .pagination > li > a, .pagination > li > span {
      cursor: pointer;
    }
    /* Para garantir que o modal tenha scroll interno se necessário */
    .modal-body-scroll {
      max-height: 70vh;
      overflow-y: auto;
    }
  </style>
</head>
<body ng-controller="MotoristaController as ctrl">
  <?php include '../includes/header.php'; ?>

  <div class="container-xxl mt-4 ml-4 mr-4" ng-init="ctrl.init()">
    <h2 class="text-center mb-4">Gerenciamento de Motoristas</h2>
    
    <!-- Relatório de Motoristas -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Relatório de Motoristas</h4>
        <input type="text" class="form-control w-50" placeholder="Pesquisar (mínimo 3 caracteres)" 
               ng-model="ctrl.searchTerm" ng-change="ctrl.searchMotoristas()">
      </div>
<div class="card-body p-0">
  <div class="table-responsive">
    <table class="table table-striped table-hover mb-0">
      <thead class="thead-dark">
        <tr>
          <th>Foto</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Endereço</th>
          <th>CPF</th>
          <th>Telefone</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr ng-repeat="motorista in ctrl.filteredMotoristas | limitTo: ctrl.pageSize : (ctrl.currentPage-1)*ctrl.pageSize">
          <td>
            <img ng-if="motorista.foto" ng-src="{{ motorista.foto }}" class="img-circle" style="width: 50px; height: 50px;">
            <span ng-if="!motorista.foto">Sem Foto</span>
          </td>
          <td>{{ motorista.nome }}</td>
          <td>{{ motorista.email }}</td>
          <td>{{ motorista.endereco }}</td>
          <td>{{ motorista.cpf }}</td>
          <td>{{ motorista.telefone }}</td>
          <td>
            <button class="btn btn-sm btn-warning" ng-click="ctrl.openEditModal(motorista)">Editar</button>
            <button class="btn btn-sm btn-danger" ng-click="ctrl.deleteMotorista(motorista.id)">Excluir</button>
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
          <li class="page-item" ng-repeat="p in [].constructor(ctrl.totalPages) track by $index" 
              ng-class="{active: ($index+1)==ctrl.currentPage}">
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
        {{ ctrl.editMode ? "Ocultar Cadastro" : "Cadastrar Novo Motorista" }}
      </button>
    </div>
  </div>

  <!-- Modal para Cadastro/Edição de Motorista -->
  <div class="modal fade" id="motoristaModal" tabindex="-1" role="dialog" aria-labelledby="motoristaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
      <div class="modal-content">
        <form name="motoristaForm" novalidate ng-submit="ctrl.saveMotorista()">
          <div class="modal-header">
            <h5 class="modal-title" id="motoristaModalLabel">{{ ctrl.editMode ? "Editar Motorista" : "Cadastrar Novo Motorista" }}</h5>
            <button type="button" class="close" ng-click="ctrl.closeMotoristaModal()" aria-label="Fechar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body modal-body-scroll">
            <!-- Campos Básicos -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Nome</label>
                <input type="text" class="form-control" ng-model="ctrl.currentMotorista.nome" required>
              </div>
              <div class="form-group col-md-6">
                <label>Email</label>
                <input type="email" class="form-control" ng-model="ctrl.currentMotorista.email" required mask-input="email">
              </div>
            </div>
            <!-- Campo Senha -->
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Senha <small ng-if="ctrl.editMode">(deixe em branco para manter a atual)</small></label>
                <input type="password" class="form-control" ng-model="ctrl.currentMotorista.senha" ng-required="!ctrl.editMode">
              </div>
            </div>
            <!-- Endereço -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Endereço</label>
                <input type="text" class="form-control" ng-model="ctrl.currentMotorista.endereco">
              </div>
            </div>
            <!-- Campos Adicionais -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>CPF</label>
                <input type="text" class="form-control" ng-model="ctrl.currentMotorista.cpf" mask-input="cpf">
              </div>
              <div class="form-group col-md-6">
                <label>Telefone</label>
                <input type="text" class="form-control" ng-model="ctrl.currentMotorista.telefone" mask-input="telefone" placeholder="(xx) xxxxx-xxxx">
              </div>
            </div>
            <!-- Foto -->
            <div class="form-group">
              <label>Foto Atual:</label>
              <div ng-if="ctrl.currentMotorista.foto">
                <img ng-src="{{ ctrl.currentMotorista.foto }}" alt="Foto" style="height:50px; width:50px;" class="img-circle">
              </div>
              <div ng-if="!ctrl.currentMotorista.foto">Sem foto</div>
              <br>
              <label>Enviar Nova Foto:</label>
              <input type="file" file-model="ctrl.newFotoFile" class="form-control-file">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" ng-click="ctrl.closeMotoristaModal()">Fechar</button>
            <button type="submit" class="btn btn-primary">{{ ctrl.editMode ? "Salvar Alterações" : "Cadastrar" }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<!-- Máscaras e validações em JavaScript -->
<script>
  function applyMaskCPF(value) {
    value = value.replace(/\D/g, ''); // Remove tudo que não for número
    value = value.substring(0, 11); // Limita a 11 caracteres numéricos

    if (value.length > 9) {
      value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
    } else if (value.length > 6) {
      value = value.replace(/(\d{3})(\d{3})(\d{1,3})$/, '$1.$2.$3');
    } else if (value.length > 3) {
      value = value.replace(/(\d{3})(\d{1,3})$/, '$1.$2');
    }
    
    return value;
  }

  function applyMaskTelefone(value) {
    value = value.replace(/\D/g, ''); // Remove tudo que não for número
    value = value.substring(0, 11); // Limita a 11 caracteres numéricos

    if (value.length > 10) {
      value = value.replace(/(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3'); // Formato (XX) XXXXX-XXXX
    } else if (value.length > 6) {
      value = value.replace(/(\d{2})(\d{4})(\d{1,4})$/, '($1) $2-$3'); // Formato (XX) XXXX-XXXX
    } else if (value.length > 2) {
      value = value.replace(/(\d{2})(\d{1,4})$/, '($1) $2'); // Formato (XX) XXXX
    }

    return value;
  }

   document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[mask-input="cpf"]').forEach(function (input) {
      input.addEventListener('input', function () {
        this.value = applyMaskCPF(this.value);
      });
    });

    document.querySelectorAll('[mask-input="telefone"]').forEach(function (input) {
      input.addEventListener('input', function () {
        this.value = applyMaskTelefone(this.value);
      });
    });
  });
</script>


  <!-- AngularJS App -->
  <script>
    angular.module('motoristaApp', [])
    .directive('fileModel', ['$parse', function ($parse) {
      return {
        restrict: 'A',
        link: function(scope, element, attrs) {
          var model = $parse(attrs.fileModel);
          var modelSetter = model.assign;
          element.bind('change', function(){
            scope.$apply(function(){
              modelSetter(scope, element[0].files[0]);
            });
          });
        }
      };
    }])
    .directive('maskInput', function() {
      return {
        restrict: 'A',
        link: function(scope, element, attrs) {
          element.on('input', function() {
            var value = element.val();
            if(attrs.maskInput === "cpf") {
              element.val(applyMaskCPF(value));
            }
            if(attrs.maskInput === "telefone") {
              element.val(applyMaskTelefone(value));
            }
            if(attrs.maskInput === "email") {
              element.val(applyMaskEmail(value));
            }
          });
        }
      };
    })
    .controller('MotoristaController', ['$scope', '$http', function($scope, $http) {
      var vm = this;
      vm.motoristas = [];
      vm.filteredMotoristas = [];
      vm.buses = [];
      vm.pageSize = 20;
      vm.currentPage = 1;
      vm.searchTerm = "";
      vm.editMode = false;
      vm.newFotoFile = null;

      vm.init = function() {
        vm.loadMotoristas();
        vm.loadBuses();
      };

      vm.loadMotoristas = function() {
        $http.get('/admin/api/get_motoristas.php').then(function(response) {
          vm.motoristas = response.data;
          vm.filteredMotoristas = angular.copy(vm.motoristas);
          vm.currentPage = 1;
          vm.updatePagination();
        }, function(error) {
          alert("Erro ao carregar motoristas.");
        });
      };

      vm.loadBuses = function() {
        $http.get('/admin/api/get_buses.php').then(function(response) {
          vm.buses = response.data;
        }, function(error) {
          alert("Erro ao carregar ônibus.");
        });
      };

      vm.searchMotoristas = function() {
        if(vm.searchTerm.length < 3) {
          vm.filteredMotoristas = angular.copy(vm.motoristas);
        } else {
          var term = vm.searchTerm.toLowerCase();
          vm.filteredMotoristas = vm.motoristas.filter(function(motorista) {
            return motorista.nome.toLowerCase().indexOf(term) !== -1 ||
                   motorista.email.toLowerCase().indexOf(term) !== -1;
          });
        }
        vm.currentPage = 1;
        vm.updatePagination();
      };

      vm.updatePagination = function() {
        vm.totalPages = Math.ceil(vm.filteredMotoristas.length / vm.pageSize) || 1;
      };

      vm.changePage = function(page) {
        if(page >= 1 && page <= vm.totalPages) {
          vm.currentPage = page;
        }
      };

      // Modal para criar/editar motorista
      vm.openCreateModal = function() {
        vm.editMode = false;
        vm.currentMotorista = {
          nome: "",
          email: "",
          senha: "",
          endereco: "",
          cpf: "",
          telefone: "",
          role: "motorista"
        };
        vm.newFotoFile = null;
        $("#motoristaModal").modal("show");
      };

      vm.openEditModal = function(motorista) {
        vm.editMode = true;
        vm.currentMotorista = angular.copy(motorista);
        vm.newFotoFile = null;
        $("#motoristaModal").modal("show");
      };

      vm.closeMotoristaModal = function() {
        $("#motoristaModal").modal("hide");
      };

vm.saveMotorista = function() {
    // Verifica se está no modo de edição e inclui o ID
    var motoristaData = {
        user_id: vm.editMode ? vm.currentMotorista.id : null, // Garante que o ID seja enviado na edição
        nome: vm.currentMotorista.nome,
        email: vm.currentMotorista.email,
        senha: vm.currentMotorista.senha ? vm.currentMotorista.senha : "", // Se estiver vazio, não envia nada
        endereco: vm.currentMotorista.endereco,
        cpf: vm.currentMotorista.cpf,
        telefone: vm.currentMotorista.telefone
    };

    console.log("Enviando motoristaData:", motoristaData); // Debug no console do navegador

    var endpoint = vm.editMode ? '/admin/api/update_motorista.php' : '/admin/api/save_motorista.php';

    $http.post(endpoint, motoristaData, {
        headers: { 'Content-Type': 'application/json' }
    }).then(function(response) {
        alert(response.data.message);
        vm.loadMotoristas();
        $("#motoristaModal").modal("hide");
    }, function(error) {
        console.error("Erro ao salvar motorista:", error); // Mostra o erro completo no console
        alert("Erro ao salvar motorista: " + (error.data.message || error.statusText));
    });
};
      vm.deleteMotorista = function(motoristaId) {
        if(confirm("Tem certeza que deseja excluir este motorista?")) {
          $http.post('/admin/api/delete_motorista.php', { id: motoristaId }).then(function(response) {
            alert(response.data.message);
            vm.loadMotoristas();
          }, function(error) {
            alert("Erro ao excluir motorista: " + (error.data.message || error.statusText));
          });
        }
      };

      vm.init();
    }]);
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>

