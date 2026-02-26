<?php 
$pageTitle = "Cadastro de Usuário";
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="pt-BR" ng-app="userApp">
<head>
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Gerenciamento de Usuários</title>
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
  </style>
</head>
<body ng-controller="UserController as ctrl">
  <?php include '../includes/header.php'; ?>

  <div class="container-xxl mt-4 ml-4 mr-4" ng-init="ctrl.init()">
    <h2 class="text-center mb-4">Gerenciamento de Usuários</h2>

    <!-- Relatório de Usuários -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Relatório de Usuários</h4>
        <input type="text" class="form-control w-50" placeholder="Pesquisar (mínimo 3 caracteres)" 
               ng-model="ctrl.searchTerm" ng-change="ctrl.searchUsers()">
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Turno</th>
                <th>Endereço</th>
                <th>Bairro</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Faculdade</th>
                <th>Viagens Permitidas</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr ng-repeat="user in ctrl.filteredUsers | limitTo: ctrl.pageSize : (ctrl.currentPage-1)*ctrl.pageSize">
                <td>{{ user.id }}</td>
                <td>
                  <img ng-if="user.foto" ng-src="{{ user.foto }}" alt="Foto" style="height:50px; width:50px;" class="img-circle">
                  <span ng-if="!user.foto">Sem foto</span>
                </td>
                <td>{{ user.nome }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.turno }}</td>
                <td>{{ user.endereco }}</td>
                <td>{{ user.bairro }}</td>
                <td>{{ user.cpf }}</td>
                <td>{{ user.telefone }}</td>
                <td>{{ user.faculdade }}</td>
                <td>
                  <div ng-if="user.schedule.length > 0">
                    <div ng-repeat="schedule in user.schedule | orderByDiaSemana">
                      {{ schedule.dia_semana | uppercase }} - {{ schedule.busIdentificador || 'N/D' }}
                    </div>
                  </div>
                  <div ng-if="user.schedule.length === 0">
                    Nenhuma viagem permitida
                  </div>
                </td>
                <td>
                  <button class="btn btn-sm btn-warning" ng-click="ctrl.openEditModal(user)">Editar</button>
                  <br/>
                  <button class="btn btn-sm btn-info mt-1" ng-click="ctrl.openPermissionModal(user)">Permissão</button>
                  <br/>
                  <button class="btn btn-sm btn-danger mt-1" ng-click="ctrl.deleteUser(user.id)">Excluir</button>
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
        {{ ctrl.editMode ? "Ocultar Cadastro" : "Cadastrar Novo Usuário" }}
      </button>
    </div>
  </div>

<!-- Modal para Cadastro/Edição de Usuário -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content">
      <form name="userForm" novalidate ng-submit="ctrl.saveUser()">
        <div class="modal-header">
          <h5 class="modal-title" id="userModalLabel">{{ ctrl.editMode ? "Editar Usuário" : "Cadastrar Novo Usuário" }}</h5>
          <button type="button" class="close" ng-click="ctrl.closeUserModal()" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Injeção de estilo para scroll se necessário -->
          <div class="container-fluid">
            <!-- Linha 1: Nome e Email -->
            <div class="row">
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label>Nome</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.nome" required>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" class="form-control" ng-model="ctrl.currentUser.email" required mask-input="email">
                </div>
              </div>
            </div>
            <!-- Linha 2: Senha -->
            <div class="row">
              <div class="col-12 col-md-4">
                <div class="form-group">
                  <label>Senha <small ng-if="ctrl.editMode">(deixe em branco para manter a atual)</small></label>
                  <input type="password" class="form-control" ng-model="ctrl.currentUser.senha" ng-required="!ctrl.editMode">
                </div>
              </div>
            </div>
            <!-- Linha 3: Turno e Endereço -->
            <div class="row">
              <div class="col-12 col-md-4">
                <div class="form-group">
                  <label>Turno</label>
                  <select class="form-control" ng-model="ctrl.currentUser.turno" required>
                    <option value="manha">Manhã</option>
                    <option value="tarde">Tarde</option>
                    <option value="noite">Noite</option>
                  </select>
                </div>
              </div>
              <div class="col-12 col-md-8">
                <div class="form-group">
                  <label>Endereço</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.endereco">
                </div>
              </div>
            </div>
            <!-- Linha 4: Campos adicionais -->
            <div class="row">
              <div class="col-12 col-md-3">
                <div class="form-group">
                  <label>Bairro</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.bairro">
                </div>
              </div>
              <div class="col-12 col-md-3">
                <div class="form-group">
                  <label>CPF</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.cpf" mask-input="cpf">
                </div>
              </div>
              <div class="col-12 col-md-3">
                <div class="form-group">
                  <label>Telefone</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.telefone" mask-input="telefone" placeholder="(xx) xxxxx-xxxx">
                </div>
              </div>
              <div class="col-12 col-md-3">
                <div class="form-group">
                  <label>Faculdade</label>
                  <input type="text" class="form-control" ng-model="ctrl.currentUser.faculdade">
                </div>
              </div>
            </div>
            <!-- Linha 5: Foto -->
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label>Foto Atual:</label>
                  <div ng-if="ctrl.currentUser.foto">
                    <img ng-src="{{ ctrl.currentUser.foto }}" alt="Foto" style="height:50px; width:50px;" class="img-circle">
                  </div>
                  <div ng-if="!ctrl.currentUser.foto">Sem foto</div>
                  <br>
                  <label>Enviar Nova Foto:</label>
                  <input type="file" file-model="ctrl.newFotoFile" class="form-control-file">
                </div>
              </div>
            </div>
            <!-- Linha 6: Viagens Permitidas (apenas em cadastro) -->
            <div class="row" ng-if="!ctrl.editMode">
              <div class="col-12">
                <div class="form-group">
                  <label>Viagens permitidas</label>
                  <div class="form-row mb-2" ng-repeat="(diaKey, diaName) in ctrl.simpleDias">
                    <div class="col-4">
          <div class="form-check">
            <input 
              type="checkbox" 
              class="form-check-input" 
              ng-model="ctrl.newUser.schedule[diaKey].selected"
            >
            <label class="form-check-label">{{ diaName }}</label>
          </div>
        </div>
        <div class="col-8">
          <select 
            class="form-control" 
            ng-model="ctrl.newUser.schedule[diaKey].busId" 
            ng-disabled="!ctrl.newUser.schedule[diaKey].selected"
          >
            <option value="">Selecione o Ônibus</option>
            <option ng-repeat="bus in ctrl.buses" value="{{ bus.id }}">
              {{ bus.identificador }}
            </option>
          </select>
        </div>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- container-fluid -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" ng-click="ctrl.closeUserModal()">Fechar</button>
          <button type="submit" class="btn btn-primary">{{ ctrl.editMode ? "Salvar Alterações" : "Cadastrar" }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <!-- Modal para Gerenciar Permissões -->
  <div class="modal fade" id="permModal" tabindex="-1" role="dialog" aria-labelledby="permModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="permModalLabel">Gerenciar Permissões do Usuário</h5>
          <button type="button" class="close" ng-click="ctrl.closePermModal()" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Lista de permissões atuais -->
          <div>
            <h6>Permissões Atuais:</h6>
            <!--<ul class="list-group">
              <li class="list-group-item" ng-repeat="(dia, perm) in ctrl.currentUser.schedule" ng-if="perm.selected">
                {{ dia | uppercase }} - {{ perm.busId ? ( (ctrl.buses | filter:{id:perm.busId})[0].identificador ) : 'N/D' }}
                <button class="btn btn-sm btn-danger float-right" ng-click="ctrl.deletePermission(dia)">X</button>
              </li>
            </ul>-->
            <ul class="list-group">
              <li class="list-group-item" ng-repeat="perm in ctrl.currentUser.schedule | orderByDiaSemana track by perm.id">
                {{ perm.dia_semana | uppercase }} - {{ perm.bus_id ? perm.busIdentificador : 'N/D' }}
                <button class="btn btn-sm btn-danger float-right" ng-click="ctrl.deletePermission(perm)">X</button>
              </li>
            </ul>
          </div>
          <hr>
          <!-- Adicionar nova permissão (caso o usuário não tenha permissão para um dia) -->
          <div>
            <h6>Adicionar Nova Permissão</h6>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Dia da Semana</label>
                <select class="form-control" ng-model="ctrl.newPerm.dia" required>
                  <option value="">Selecione o Dia</option>
                  <option ng-repeat="(key, value) in ctrl.simpleDias" value="{{ key }}">{{ value }}</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label>Ônibus</label>
                <select class="form-control" ng-model="ctrl.newPerm.busId" required>
                  <option value="">Selecione o Ônibus</option>
                  <option ng-repeat="bus in ctrl.buses" value="{{ bus.id }}">{{ bus.identificador }}</option>
                </select>
              </div>
            </div>
            <button class="btn btn-primary btn-block" ng-click="ctrl.addPermission()">Adicionar Permissão</button>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" ng-click="ctrl.closePermModal()">Fechar</button>
        </div>
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
    angular.module('userApp', [])
    .filter('orderByDiaSemana', function() {
      return function(input) {
        if (!input) return input;
        
        // Mapear dias para números (segunda = 1, ..., sexta = 5)
        var ordemDias = { 
          segunda: 1,
          terca: 2,
          quarta: 3,
          quinta: 4,
          sexta: 5
        };
        
        // Ordenar cópia do array para não alterar o original
        var sorted = input.slice().sort(function(a, b) {
          return ordemDias[a.dia_semana] - ordemDias[b.dia_semana];
        });
        
        return sorted;
      };
    })
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
    .controller('UserController', ['$scope', '$http', function($scope, $http) {
      var vm = this;
      vm.users = [];
      vm.filteredUsers = [];
      vm.buses = [];
      vm.diasSemana = {
        segunda: "Segunda",
        terca: "Terça",
        quarta: "Quarta",
        quinta: "Quinta",
        sexta: "Sexta",
        sabado: "Sábado" // se desejar incluir sábado, senão remova
      };
      // Para o painel simplificado de permissão, use somente segunda a sexta
      vm.simpleDias = {
        segunda: "Segunda",
        terca: "Terça",
        quarta: "Quarta",
        quinta: "Quinta",
        sexta: "Sexta"
      };
      vm.pageSize = 20;
      vm.currentPage = 1;
      vm.searchTerm = "";
      vm.editMode = false;
      vm.newPerm = { dia: "", busId: "" };

      vm.init = function() {
        vm.loadUsers();
        vm.loadBuses();
      };

      vm.loadUsers = function() {
        $http.get('/admin/api/get_users.php').then(function(response) {
          vm.users = response.data || [];

          vm.users.forEach(function(user) {
              // Certifica-se de que a foto esteja correta
              if (user.foto) {
                  user.foto = user.foto; 
              }

              // Certifica-se de que `schedule` seja um array
              if (!angular.isArray(user.schedule)) {
                  user.schedule = []; // Se não for um array, define como vazio para evitar erros
              }
          });

          vm.filteredUsers = angular.copy(vm.users);
          vm.currentPage = 1;
          vm.updatePagination();
        }, function(error) {
          alert("Erro ao carregar usuários.");
        });
      };

      vm.loadBuses = function() {
        $http.get('/admin/api/get_buses.php').then(function(response) {
          vm.buses = response.data;
        }, function(error) {
          alert("Erro ao carregar ônibus.");
        });
      };

      vm.searchUsers = function() {
        if(vm.searchTerm.length < 3) {
          vm.filteredUsers = angular.copy(vm.users);
        } else {
          var term = vm.searchTerm.toLowerCase();
          vm.filteredUsers = vm.users.filter(function(user) {
            return user.nome.toLowerCase().indexOf(term) !== -1 ||
                   user.email.toLowerCase().indexOf(term) !== -1;
          });
        }
        vm.currentPage = 1;
        vm.updatePagination();
      };

      vm.updatePagination = function() {
        vm.totalPages = Math.ceil(vm.filteredUsers.length / vm.pageSize) || 1;
      };

      vm.changePage = function(page) {
        if(page >= 1 && page <= vm.totalPages) {
          vm.currentPage = page;
        }
      };

      // Modal para criar/editar usuário
      vm.openCreateModal = function() {
        vm.editMode = false;
        vm.currentUser = {
          nome: "",
          email: "",
          senha: "",
          endereco: "",
          turno: "manha",
          bairro: "",
          cpf: "",
          telefone: "",
          faculdade: "",
          foto: "",
          schedule: {}
        };
        angular.forEach(vm.simpleDias, function(value, key) {
          vm.currentUser.schedule[key] = { selected: false, busId: "", busIdentificador: "" };
        });
        vm.newFotoFile = null;
        $("#userModal").modal("show");
      };

      vm.openEditModal = function(user) {
        vm.editMode = true;
        vm.currentUser = angular.copy(user);
        if(!vm.currentUser.schedule) {
          vm.currentUser.schedule = {};
          angular.forEach(vm.simpleDias, function(value, key) {
            vm.currentUser.schedule[key] = { selected: false, busId: "", busIdentificador: "" };
          });
        }
        vm.newFotoFile = null;
        $("#userModal").modal("show");
      };

      vm.closeUserModal = function() {
        $("#userModal").modal("hide");
      };

      vm.saveUser = function() {
  var formData = new FormData();

  // Adicionar campos do usuário (exceto schedule)
  angular.forEach(vm.currentUser, function(value, key) {
    if (key !== 'schedule') {
      formData.append(key, value);
    }
  });

  // Adicionar foto (se houver)
  if (vm.newFotoFile) {
    formData.append("fotoFile", vm.newFotoFile);
  }

  // Processar schedule apenas no modo de criação
  if (!vm.editMode) {
    angular.forEach(vm.newUser.schedule, function(dayConfig, diaKey) {
      if (dayConfig.selected && dayConfig.busId) {
        formData.append('dias[]', diaKey); // Ex: "segunda"
        formData.append('onibus_dia[' + diaKey + ']', dayConfig.busId); // Ex: "onibus_dia[segunda]=5"
      }
    });
  }

  // Adicionar ação e ID (se edição)
  formData.append("action", vm.editMode ? "update" : "create");
  if (vm.editMode) {
    formData.append("user_id", vm.currentUser.id);
  }

  // Enviar requisição
  var endpoint = vm.editMode ? '/admin/api/update_user.php' : '/admin/api/save_user.php';
  $http.post(endpoint, formData, {
    transformRequest: angular.identity,
    headers: { 'Content-Type': undefined }
  }).then(function(response) {
    alert(response.data.message);
    vm.loadUsers();
    $("#userModal").modal("hide");
  }, function(error) {
    alert("Erro ao salvar usuário: " + (error.data.message || 'Verifique o console para detalhes.'));
  });
};

// Inicialização do newUser.schedule no controller
vm.simpleDias = { segunda: 'Segunda', terca: 'Terça', quarta: 'Quarta', quinta: 'Quinta', sexta: 'Sexta' };
vm.newUser = {
  schedule: {}
};

// Inicialização correta do schedule:
angular.forEach(vm.simpleDias, function(name, diaKey) {
  vm.newUser.schedule[diaKey] = { 
    selected: false, 
    busId: '' // Usar string vazia (não null)
  };
});

      vm.deleteUser = function(userId) {
        if(confirm("ATENÇÃO: Todos os dados relacionados ao usuário serão apagados. \nDeseja realmente excluir este usuário?")) {
          $http.post('/admin/api/delete_user.php', { id: userId }).then(function(response) {
            alert(response.data.message);
            vm.loadUsers();
          }, function(error) {
            alert("Erro ao excluir usuário: " + (error.data.message || error.statusText));
          });
        }
      };

      // Modal para Gerenciar Permissões
      vm.openPermissionModal = function(user) {
        vm.currentUser = angular.copy(user);
        if(!vm.currentUser.schedule) {
          vm.currentUser.schedule = {};
          angular.forEach(vm.simpleDias, function(value, key) {
            vm.currentUser.schedule[key] = { selected: false, busId: "", busIdentificador: "" };
          });
        }
        vm.newPerm = { dia: "", busId: "" };
        // Carrega permissões atualizadas do servidor
        $http.get('/admin/api/get_permissions.php?user_id=' + vm.currentUser.id)
          .then(function(resp) {
            var permissions = resp.data;
            // Converta a lista para um objeto indexado por dia
            vm.currentUser.schedule = {};
            angular.forEach(vm.simpleDias, function(value, key) {
              vm.currentUser.schedule[key] = { selected: false, busId: "", busIdentificador: "" };
            });
            permissions.forEach(function(perm) {
              vm.currentUser.schedule[perm.dia_semana] = {
                selected: true,
                busId: perm.bus_id,
                busIdentificador: perm.bus_identificador,
                id: perm.id
              };
            });
            $("#permModal").modal("show");
          }, function(err) {
            alert("Erro ao obter permissões.");
          });
      };

      vm.closePermModal = function() {
        $("#permModal").modal("hide");
      };

      vm.addPermission = function() {
        if(vm.newPerm.dia && vm.newPerm.busId) {
          $http.post('/admin/api/add_permission.php', {
            user_id: vm.currentUser.id,
            dia: vm.newPerm.dia,
            bus_id: vm.newPerm.busId
          }).then(function(response) {
            alert(response.data.message);
            // Atualiza a permissão localmente
            var selectedBus = vm.buses.find(function(b) { return b.id == vm.newPerm.busId; });
            vm.currentUser.schedule[vm.newPerm.dia] = {
              selected: true,
              busId: vm.newPerm.busId,
              busIdentificador: selectedBus ? selectedBus.identificador : "",
              id: response.data.permission_id // Certifique-se que o endpoint retorne isso
            };
            vm.newPerm = { dia: "", busId: "" };
            // Opcional: recarregar os usuários para atualizar a tabela principal
            vm.loadUsers();
            vm.closePermModal();
          }, function(error) {
            var msg = (error.data && error.data.message) ? error.data.message : "Erro ao adicionar permissão.";
            alert("Erro ao adicionar permissão: " + msg);
          });
        } else {
          alert("Selecione o dia e o ônibus.");
        }
      };

      vm.deletePermission = function(permission) {
  if (permission && permission.id) {
    if (confirm("Tem certeza que deseja excluir esta permissão?")) {
      $http.post('/admin/api/delete_permission.php', { id: permission.id })
        .then(function(response) {
          alert(response.data.message);

          // Verifica se schedule é um objeto
          if (typeof vm.currentUser.schedule === 'object' && !Array.isArray(vm.currentUser.schedule)) {
            // Percorre as chaves do objeto
            Object.keys(vm.currentUser.schedule).forEach(function(dia) {
              if (vm.currentUser.schedule[dia].id === permission.id) {
                delete vm.currentUser.schedule[dia];
              }
            });
          }

          // Opcional: recarregar os usuários
          vm.loadUsers();

          // Fechar o modal (substitua '#meuModal' pelo seletor correto do seu modal)
          vm.closePermModal();
        }, function(error) {
          var msg = (error.data && error.data.message) ? error.data.message : "Erro ao excluir permissão.";
          alert("Erro ao excluir permissão: " + msg);
        });
    }
  } else {
    alert("Permissão inválida.");
  }
};

      vm.init();
    }]);
  </script>

  <?php include '../includes/footer.php'; ?>
</body>
</html>

