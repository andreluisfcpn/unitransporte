<?php  
$pageTitle = "Início"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');
include '../includes/header.php';
?>
<style>
  /* Melhorias de responsividade para o dashboard */
  .dashboard-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    border-radius: 10px;
    overflow: hidden;
    border: none;
  }
  
  .dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  
  .dashboard-card .card-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  
  .dashboard-card .card-text {
    flex-grow: 1;
    margin-bottom: 1rem;
  }
  
  .dashboard-btn {
    border-radius: 5px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
  }
  
  @media (max-width: 576px) {
    .container {
      padding-left: 10px;
      padding-right: 10px;
    }
    
    h2 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .dashboard-card {
      margin-bottom: 1rem;
    }
    
    .dashboard-card .card-body {
      padding: 1rem;
    }
    
    .dashboard-card .card-title {
      font-size: 1.25rem;
    }
    
    .dashboard-card .card-text {
      font-size: 0.9rem;
    }
  }
</style>

<div class="container mt-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Dashboard do Administrador</h2>
    </div>
  </div>
  <div class="row">
    <!-- Card para Cadastro de Usuário -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Cadastro de Usuário</h5>
          <p class="card-text">Registrar novos usuários do sistema.</p>
          <a href="cadastrar_usuario.php" class="btn btn-primary dashboard-btn">Cadastro de Usuário</a>
        </div>
      </div>
    </div>
    <!-- Card para Cadastro de Motorista -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Cadastro de Motorista</h5>
          <p class="card-text">Registrar novos motoristas do sistema.</p>
          <a href="cadastrar_motorista.php" class="btn btn-primary dashboard-btn">Cadastro de Motorista</a>
        </div>
      </div>
    </div>
    <!-- Card para Cadastro de Ônibus -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Cadastro de Ônibus</h5>
          <p class="card-text">Cadastrar novos ônibus com seus detalhes.</p>
          <a href="cadastrar_onibus.php" class="btn btn-primary dashboard-btn">Cadastro de Ônibus</a>
        </div>
      </div>
    </div>
    <!-- Card para Relatório de Usuários -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Relatório de Usuários</h5>
          <p class="card-text">Visualizar relatórios de viagens dos usuários do sistema.</p>
          <a href="relatorio_usuarios.php" class="btn btn-primary dashboard-btn">Ver Relatório</a>
        </div>
      </div>
    </div>
    <!-- Card para Imprimir Carteiras -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Imprimir Carteiras</h5>
          <p class="card-text">Imprimir carteiras universitárias individuais ou em lote.</p>
          <a href="imprimir_carteiras.php" class="btn btn-primary dashboard-btn">Imprimir Carteiras</a>
        </div>
      </div>
    </div>
    <!-- Card para Relatório de Motoristas -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Relatório de Motoristas</h5>
          <p class="card-text">Visualizar relatórios de validações realizadas pelos motoristas.</p>
          <a href="relatorio_motoristas.php" class="btn btn-primary dashboard-btn">Ver Relatório</a>
        </div>
      </div>
    </div>
    <!-- Card para Relatório de Ônibus -->
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Relatório de Ônibus</h5>
          <p class="card-text">Visualizar contagem de passageiros por ônibus e período.</p>
          <a href="relatorio_onibus.php" class="btn btn-primary dashboard-btn">Ver Relatório</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

