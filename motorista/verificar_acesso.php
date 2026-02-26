<?php
// motorista/verificar_acesso.php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'motorista') {
    http_response_code(403);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
    exit;
}

if (!isset($_POST['userId']) || !isset($_POST['busId'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados insuficientes.']);
    exit;
}

$userId = intval($_POST['userId']);
$busId  = intval($_POST['busId']);

// Obter o dia da semana atual em português (segunda, terca, ...)
$diaSemanaIngles = date('l'); // ex: Monday, Tuesday, ...
$mapaDias = [
    'Monday'    => 'segunda',
    'Tuesday'   => 'terca',
    'Wednesday' => 'quarta',
    'Thursday'  => 'quinta',
    'Friday'    => 'sexta',
    'Saturday'  => 'sabado',
    'Sunday'    => 'domingo'
];
$diaAtual = isset($mapaDias[$diaSemanaIngles]) ? $mapaDias[$diaSemanaIngles] : strtolower($diaSemanaIngles);

// Registrar a verificação para fins de diagnóstico
error_log("Verificando acesso para usuário: $userId, ônibus: $busId, dia: $diaAtual");

// Consulta principal - verificar permissão para este ônibus específico
$stmt = $pdo->prepare("SELECT us.*, b.identificador as nome_onibus 
                      FROM user_schedule us
                      JOIN buses b ON us.bus_id = b.id
                      WHERE us.user_id = :user_id 
                      AND us.dia_semana = :dia 
                      AND us.bus_id = :bus_id");

$stmt->execute([
    'user_id' => $userId,
    'dia'     => $diaAtual,
    'bus_id'  => $busId
]);

$permissao = $stmt->fetch(PDO::FETCH_ASSOC);

// Se encontrou permissão para este ônibus, autoriza
if ($permissao) {
    error_log("Permissão encontrada: " . json_encode($permissao));
    echo json_encode([
        'status' => 'autorizado', 
        'permissao' => $permissao,
        'mensagem' => 'Usuário possui permissão para este ônibus neste dia.'
    ]);
} 
else {
    // Verificar todas as permissões do usuário para este dia
    $stmt = $pdo->prepare("SELECT us.*, b.identificador as nome_onibus 
                          FROM user_schedule us
                          JOIN buses b ON us.bus_id = b.id
                          WHERE us.user_id = :user_id 
                          AND us.dia_semana = :dia");
    
    $stmt->execute([
        'user_id' => $userId,
        'dia'     => $diaAtual
    ]);
    
    $todasPermissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Se o usuário possui outras permissões para este dia, informar
    if (count($todasPermissoes) > 0) {
        error_log("Usuário tem " . count($todasPermissoes) . " permissões para este dia, mas nenhuma para o ônibus $busId");
        error_log("Permissões disponíveis: " . json_encode($todasPermissoes));
        
        // Listar os ônibus permitidos para a mensagem de erro
        $onibusPermitidos = [];
        foreach ($todasPermissoes as $perm) {
            $onibusPermitidos[] = $perm['nome_onibus'];
        }
        
        echo json_encode([
            'status' => 'nao_autorizado',
            'mensagem' => 'Usuário não tem permissão para este ônibus neste dia.',
            'detalhes' => 'Permissões existentes para: ' . implode(', ', $onibusPermitidos),
            'permissoes_existentes' => $todasPermissoes
        ]);
    } 
    else {
        error_log("Usuário não tem permissões para este dia da semana");
        echo json_encode([
            'status' => 'nao_autorizado',
            'mensagem' => 'Usuário não tem permissão para este dia da semana.'
        ]);
    }
}
?>

