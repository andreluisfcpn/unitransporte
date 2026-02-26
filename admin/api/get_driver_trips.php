<?php
// admin/api/get_driver_trips.php
require_once '../../config.php';
require_once '../../includes/auth.php';

// Verificar se o usuário tem permissão de admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

// Obter o ID do motorista
$driverId = isset($_GET['driver_id']) ? intval($_GET['driver_id']) : 0;

// Verificar se o ID do motorista é válido
if ($driverId <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'ID de motorista inválido']);
    exit;
}

try {
    // Verificar se uma data específica foi fornecida
    $date_condition = "";
    $params = ['driver_id' => $driverId];
    
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $date = $_GET['date'];
        $date_condition = " AND DATE(t.data_viagem) = :date";
        $params['date'] = $date;
    }
    
    // Consultar viagens do motorista
    $sql = "
        SELECT 
            DATE(t.data_viagem) as data,
            b.identificador as onibus,
            b.id as bus_id,
            b.horario_ida,
            b.horario_volta
        FROM trips t
        INNER JOIN buses b ON t.bus_id = b.id
        WHERE t.motorista_id = :driver_id 
        $date_condition
        GROUP BY DATE(t.data_viagem), b.id
        ORDER BY t.data_viagem DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    
    // Para cada viagem/ônibus, buscar os usuários
    foreach ($trips as $trip) {
        $busId = $trip['bus_id'];
        $date = $trip['data'];
        $horarioIda = $trip['horario_ida'];
        $horarioVolta = $trip['horario_volta'];
        
        // Calcular os períodos de ida e volta (3 horas após o horário)
        $dataInicioIda = $date . ' ' . $horarioIda;
        $dataFimIda = date('Y-m-d H:i:s', strtotime($dataInicioIda . ' + 3 hours'));
        
        $dataInicioVolta = $date . ' ' . $horarioVolta;
        $dataFimVolta = date('Y-m-d H:i:s', strtotime($dataInicioVolta . ' + 3 hours'));
        
        // Registrar informações para depuração
        error_log("MOTORISTA ID: $driverId, BUS ID: $busId, DATA: $date, ONIBUS: {$trip['onibus']}");
        error_log("Período IDA: $dataInicioIda até $dataFimIda");
        error_log("Período VOLTA: $dataInicioVolta até $dataFimVolta");
        
        // Consultar usuários AUTORIZADOS na ida
        $stmtIda = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_ida
            AND data_viagem <= :fim_ida
            AND status = 'autorizado'
            AND motorista_id = :driver_id
        ");
        
        $stmtIda->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_ida' => $dataInicioIda,
            'fim_ida' => $dataFimIda,
            'driver_id' => $driverId
        ]);
        
        $usuariosIda = $stmtIda->fetchColumn();
        error_log("Usuários autorizados na ida: $usuariosIda");
        
        // Consultar usuários recusados na ida
        $stmtIdaRecusados = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_ida
            AND data_viagem <= :fim_ida
            AND status = 'recusado'
            AND motorista_id = :driver_id
        ");
        
        $stmtIdaRecusados->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_ida' => $dataInicioIda,
            'fim_ida' => $dataFimIda,
            'driver_id' => $driverId
        ]);
        
        $recusadosIda = $stmtIdaRecusados->fetchColumn();
        error_log("Usuários recusados na ida: $recusadosIda");
        
        // Consultar usuários AUTORIZADOS na volta
        $stmtVolta = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_volta
            AND data_viagem <= :fim_volta
            AND status = 'autorizado'
            AND motorista_id = :driver_id
        ");
        
        $stmtVolta->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_volta' => $dataInicioVolta,
            'fim_volta' => $dataFimVolta,
            'driver_id' => $driverId
        ]);
        
        $usuariosVolta = $stmtVolta->fetchColumn();
        error_log("Usuários autorizados na volta: $usuariosVolta");
        
        // Consultar usuários recusados na volta
        $stmtVoltaRecusados = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_volta
            AND data_viagem <= :fim_volta
            AND status = 'recusado'
            AND motorista_id = :driver_id
        ");
        
        $stmtVoltaRecusados->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_volta' => $dataInicioVolta,
            'fim_volta' => $dataFimVolta,
            'driver_id' => $driverId
        ]);
        
        $recusadosVolta = $stmtVoltaRecusados->fetchColumn();
        error_log("Usuários recusados na volta: $recusadosVolta");
        
        // Adicionar informações ao resultado
        $tripInfo = [
            'data' => $date,
            'onibus' => $trip['onibus'],
            'horario_ida' => $horarioIda,
            'horario_volta' => $horarioVolta,
            'usuarios_ida' => $usuariosIda,
            'usuarios_volta' => $usuariosVolta,
            'recusados_ida' => $recusadosIda,
            'recusados_volta' => $recusadosVolta
        ];
        
        // Formatar a data para exibição
        $dateObj = new DateTime($date);
        $tripInfo['data_formatada'] = $dateObj->format('d/m/Y');
        $tripInfo['dia_semana'] = $dateObj->format('l'); // Nome do dia da semana em inglês
        
        // Traduzir o dia da semana para português
        $diasSemana = [
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        
        $tripInfo['dia_semana'] = $diasSemana[$tripInfo['dia_semana']];
        
        // Formatar horários
        $tripInfo['horario_ida_formatado'] = substr($horarioIda, 0, 5); // HH:MM
        $tripInfo['horario_volta_formatado'] = substr($horarioVolta, 0, 5); // HH:MM
        
        $result[] = $tripInfo;
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro na API get_driver_trips: " . $e->getMessage());
    echo json_encode(['message' => 'Erro ao buscar viagens: ' . $e->getMessage()]);
}
?>
