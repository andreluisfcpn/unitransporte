<?php
// admin/api/get_user_trips.php
require_once '../../config.php';
require_once '../../includes/auth.php';

// Verificar se o usuário tem permissão de admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

// Obter o ID do usuário
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Verificar se o ID do usuário é válido
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'ID de usuário inválido']);
    exit;
}

try {
    // Verificar se uma data específica foi fornecida
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $date = $_GET['date'];
        
        // Consultar viagens do usuário na data específica
        $stmt = $pdo->prepare("
            SELECT t.*, b.identificador as onibus
            FROM trips t
            LEFT JOIN buses b ON t.bus_id = b.id
            WHERE t.user_id = :user_id 
            AND DATE(t.data_viagem) = :date
            ORDER BY t.data_viagem DESC
        ");
        $stmt->execute([
            'user_id' => $userId,
            'date' => $date
        ]);
    } else {
        // Consultar todas as viagens do usuário
        $stmt = $pdo->prepare("
            SELECT t.*, b.identificador as onibus
            FROM trips t
            LEFT JOIN buses b ON t.bus_id = b.id
            WHERE t.user_id = :user_id
            ORDER BY t.data_viagem DESC
        ");
        $stmt->execute(['user_id' => $userId]);
    }
    
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Converter datas para formato ISO para facilitar o processamento no JavaScript
    foreach ($trips as &$trip) {
        if (isset($trip['data_viagem'])) {
            $date = new DateTime($trip['data_viagem']);
            $trip['data_viagem'] = $date->format('c'); // Formato ISO 8601
        }
    }
    
    echo json_encode($trips);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro na API get_user_trips: " . $e->getMessage());
    echo json_encode(['message' => 'Erro ao buscar viagens: ' . $e->getMessage()]);
}
?>
