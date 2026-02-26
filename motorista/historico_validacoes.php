<?php 
// motorista/historico_validacoes.php
$pageTitle = "Histórico de Validações"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('motorista');

$stmt = $pdo->prepare("SELECT t.*, u.nome AS usuario 
                       FROM trips t 
                       INNER JOIN users u ON t.user_id = u.id 
                       WHERE t.motorista_id = :motorista_id 
                       ORDER BY t.data_viagem DESC");
$stmt->execute(['motorista_id' => $_SESSION['user']['id']]);
$validacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<div class="container-xxl mr-4 ml-4 mt-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow-sm">
        <div class="card-header text-center">
          <h2>Histórico de Validações</h2>
        </div>
        <div class="card-body">
          <?php if(empty($validacoes)): ?>
            <p class="text-center">Nenhuma validação registrada.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="thead-dark">
                  <tr>
                    <th>Usuário</th>
                    <th>Data da Viagem</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($validacoes as $v): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($v['usuario']); ?></td>
                      <td><?php echo htmlspecialchars($v['data_viagem']); ?></td>
                      <td><?php echo htmlspecialchars($v['status']); ?></td>
                      <td><?php echo htmlspecialchars($v['mensagem']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>

