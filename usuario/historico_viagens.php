<?php 
$pageTitle = "Histórico de Viagens"; // Título desta página
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('usuario');

$stmt = $pdo->prepare("SELECT * FROM trips WHERE user_id = :user_id ORDER BY data_viagem DESC");
$stmt->execute(['user_id' => $_SESSION['user']['id']]);
$viagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<div class="container-xxl mr-4 ml-4 mt-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header text-center">
          <h2>Histórico de Viagens</h2>
        </div>
        <div class="card-body">
          <?php if(empty($viagens)): ?>
            <p class="text-center">Nenhuma viagem registrada.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="thead-dark">
                  <tr>
                    <th>Data da Viagem</th>
                    <th>Status</th>
                    <th>Mensagem</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($viagens as $v): ?>
                    <tr>
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

