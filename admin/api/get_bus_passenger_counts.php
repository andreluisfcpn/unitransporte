<?php
// admin/api/get_bus_passenger_counts.php
require_once '../../config.php';
require_once '../../includes/auth.php';

// Verificar se o usuário tem permissão de admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

try {
    // Verificar se uma data específica foi fornecida
    $date_condition = "";
    $params = [];
    
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $date = $_GET['date'];
        $date_condition = " AND DATE(t.data_viagem) = :date";
        $params['date'] = $date;
    }
    
    // Consultar todos os ônibus com viagens
    $sql = "
        SELECT DISTINCT 
            b.id as bus_id,
            b.identificador as onibus,
            b.horario_ida,
            b.horario_volta,
            DATE(t.data_viagem) as data
        FROM trips t
        INNER JOIN buses b ON t.bus_id = b.id
        WHERE 1=1 $date_condition
        ORDER BY b.identificador, DATE(t.data_viagem)
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $busTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    
    // Para cada ônibus/data, buscar a contagem de passageiros
    foreach ($busTrips as $busTrip) {
        $busId = $busTrip['bus_id'];
        $date = $busTrip['data'];
        $horarioIda = $busTrip['horario_ida'];
        $horarioVolta = $busTrip['horario_volta'];
        
        // Calcular os períodos de ida e volta (3 horas após o horário)
        $dataInicioIda = $date . ' ' . $horarioIda;
        $dataFimIda = date('Y-m-d H:i:s', strtotime($dataInicioIda . ' + 3 hours'));
        
        $dataInicioVolta = $date . ' ' . $horarioVolta;
        $dataFimVolta = date('Y-m-d H:i:s', strtotime($dataInicioVolta . ' + 3 hours'));
        
        // Registrar informações para depuração
        error_log("BUS ID: $busId, DATA: $date, ONIBUS: {$busTrip['onibus']}");
        error_log("Período IDA: $dataInicioIda até $dataFimIda");
        error_log("Período VOLTA: $dataInicioVolta até $dataFimVolta");
        
        // Consultar passageiros AUTORIZADOS na ida
        $stmtIda = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_ida
            AND data_viagem <= :fim_ida
            AND status = 'autorizado'
        ");
        
        $stmtIda->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_ida' => $dataInicioIda,
            'fim_ida' => $dataFimIda
        ]);
        
        $passageirosIda = $stmtIda->fetchColumn();
        error_log("Passageiros autorizados na ida: $passageirosIda");
        
        // Consultar passageiros RECUSADOS na ida
        $stmtIdaRecusados = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_ida
            AND data_viagem <= :fim_ida
            AND status = 'recusado'
        ");
        
        $stmtIdaRecusados->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_ida' => $dataInicioIda,
            'fim_ida' => $dataFimIda
        ]);
        
        $recusadosIda = $stmtIdaRecusados->fetchColumn();
        error_log("Passageiros recusados na ida: $recusadosIda");
        
        // Consultar passageiros AUTORIZADOS na volta
        $stmtVolta = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_volta
            AND data_viagem <= :fim_volta
            AND status = 'autorizado'
        ");
        
        $stmtVolta->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_volta' => $dataInicioVolta,
            'fim_volta' => $dataFimVolta
        ]);
        
        $passageirosVolta = $stmtVolta->fetchColumn();
        error_log("Passageiros autorizados na volta: $passageirosVolta");
        
        // Consultar passageiros RECUSADOS na volta
        $stmtVoltaRecusados = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM trips
            WHERE bus_id = :bus_id
            AND DATE(data_viagem) = :date
            AND data_viagem >= :inicio_volta
            AND data_viagem <= :fim_volta
            AND status = 'recusado'
        ");
        
        $stmtVoltaRecusados->execute([
            'bus_id' => $busId,
            'date' => $date,
            'inicio_volta' => $dataInicioVolta,
            'fim_volta' => $dataFimVolta
        ]);
        
        $recusadosVolta = $stmtVoltaRecusados->fetchColumn();
        error_log("Passageiros recusados na volta: $recusadosVolta");
        
        // Adicionar informações ao resultado
        $tripInfo = [
            'data' => $date,
            'onibus' => $busTrip['onibus'],
            'horario_ida' => $horarioIda,
            'horario_volta' => $horarioVolta,
            'passageiros_ida' => $passageirosIda,
            'passageiros_volta' => $passageirosVolta,
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
    error_log("Erro na API get_bus_passenger_counts: " . $e->getMessage());
    echo json_encode(['message' => 'Erro ao buscar contagem de passageiros: ' . $e->getMessage()]);
}
?>
