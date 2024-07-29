<?php
// Conexão com o banco de dados
include 'db.php';

// Log para debug
$logFile = '/tmp/log_update_balance.txt';
$log = "Requisição recebida:\n";
file_put_contents($logFile, $log, FILE_APPEND);

// Captura do conteúdo da requisição JSON
$input = json_decode(file_get_contents('php://input'), true);

// Verificação de dados recebidos
if (is_null($input)) {
    $log = "Erro: Nenhum dado JSON recebido\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido']);
    exit();
}

// Captura das variáveis do JSON
$sessionToken = isset($input['sessionToken']) ? $input['sessionToken'] : null;
$value_v = isset($input['value_v']) ? $input['value_v'] : null;
$score = isset($input['score']) ? $input['score'] : null;

$log = "Dados recebidos: " . print_r($input, true) . "\n";
file_put_contents($logFile, $log, FILE_APPEND);

// Verificação se todos os dados necessários foram recebidos
if (is_null($sessionToken) || is_null($value_v) || is_null($score)) {
    $log = "Erro: Dados incompletos recebidos. Token: $sessionToken, Value: $value_v, Score: $score\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos recebidos']);
    exit();
}

$log = "Token: $sessionToken\nValue: $value_v\nScore: $score\n";
file_put_contents($logFile, $log, FILE_APPEND);

// Verificar se o token é válido e obter o ID do usuário
$query = $db->prepare("SELECT users.id FROM users INNER JOIN sessions ON users.id = sessions.user_id WHERE sessions.session_token = :token ORDER BY sessions.id DESC LIMIT 1");
$query->bindParam(':token', $sessionToken);
$query->execute();

$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $userId = $user['id'];
    $log = "Usuário encontrado: ID $userId\n";
    file_put_contents($logFile, $log, FILE_APPEND);

    // Iniciar transação
    $db->beginTransaction();

    try {
        // Adicionar o valor ganho ao saldo do usuário
        $updateQuery = $db->prepare("UPDATE users SET balance = balance + :value_v WHERE id = :userId");
        $updateQuery->bindParam(':value_v', $value_v);
        $updateQuery->bindParam(':userId', $userId);
        $updateQuery->execute();

        $log = "Saldo atualizado para o usuário $userId com valor $value_v\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        // Registrar a transação
        $transactionQuery = $db->prepare("INSERT INTO game_transactions (user_id, bet_value, win_value) VALUES (:userId, :bet_value, :win_value)");
        $transactionQuery->bindParam(':userId', $userId);
        $transactionQuery->bindParam(':bet_value', $value_v);
        $transactionQuery->bindParam(':win_value', $value_v);
        $transactionQuery->execute();

        $log = "Transação registrada para o usuário $userId com valor $value_v\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        // Commit da transação
        $db->commit();

        $log = "Respondendo com sucesso\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Saldo atualizado e transação registrada com sucesso']);
        exit();
    } catch (Exception $e) {
        // Rollback da transação em caso de erro
        $db->rollBack();
        $log = "Erro ao atualizar saldo e registrar transação: " . $e->getMessage() . "\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar saldo e registrar transação']);
        exit();
    }
} else {
    $log = "Token inválido\n";
    file_put_contents($logFile, $log, FILE_APPEND);

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Token inválido']);
    exit();
}
?>
