<?php 
$pageTitle = "Início"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('motorista');
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
  }
</style>

<div class="container mt-4">
  <div class="row mb-3">
    <div class="col">
      <h2 class="text-center">Dashboard do Motorista</h2>
    </div>
  </div>
  <div class="row">
    <!-- Card para "Validar QR Code" -->
    <div class="col-md-6 col-sm-12 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Validar QR Code</h5>
          <p class="card-text">Utilize o scanner para validar a passagem dos usuários.</p>
          <a href="validar_qr.php" class="btn btn-primary dashboard-btn">Validar QR Code</a>
        </div>
      </div>
    </div>
    <!-- Card para "Histórico de Validações" -->
    <div class="col-md-6 col-sm-12 mb-3">
      <div class="card shadow-sm dashboard-card">
        <div class="card-body text-center">
          <h5 class="card-title">Histórico de Validações</h5>
          <p class="card-text">Consulte as validações realizadas.</p>
          <a href="historico_validacoes.php" class="btn btn-primary dashboard-btn">Ver Histórico</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

