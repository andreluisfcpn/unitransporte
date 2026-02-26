<?php
// admin/api/search_drivers.php
require_once '../../config.php';
require_once '../../includes/auth.php';

// Verificar se o usuário tem permissão de admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

// Obter o termo de pesquisa
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

// Verificar se o termo de pesquisa tem pelo menos 2 caracteres
if (strlen($searchTerm) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Pesquisar motoristas pelo nome ou CPF
    $stmt = $pdo->prepare("SELECT id, nome, cpf FROM users 
                          WHERE (nome LIKE ? OR cpf LIKE ?) 
                          AND role = 'motorista' 
                          ORDER BY nome 
                          LIMIT 20");
    $searchPattern = "%{$searchTerm}%";
    $stmt->execute([$searchPattern, $searchPattern]);
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($drivers);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro na API search_drivers: " . $e->getMessage());
    echo json_encode(['message' => 'Erro ao pesquisar motoristas: ' . $e->getMessage()]);
}
?>
