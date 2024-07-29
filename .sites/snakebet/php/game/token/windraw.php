<?php
// Conexão com o banco de dados
include 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'];
$value_v = $input['value_v'];
$score = $input['score'];

// Verificar se o token é válido e obter o ID do usuário
$query = $db->prepare("SELECT id FROM users WHERE session_token = :token");
$query->bindParam(':token', $token);
$query->execute();

$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $userId = $user['id'];

    // Adicionar o valor ganho ao saldo do usuário
    $updateQuery = $db->prepare("UPDATE users SET balance = balance + :value_v WHERE id = :userId");
    $updateQuery->bindParam(':value_v', $value_v);
    $updateQuery->bindParam(':userId', $userId);
    $updateQuery->execute();

    if ($updateQuery->rowCount() > 0) {
        $response = ['success' => true, 'message' => 'Saldo atualizado com sucesso'];
    } else {
        $response = ['success' => false, 'message' => 'Falha ao atualizar saldo'];
    }
} else {
    $response = ['success' => false, 'message' => 'Token inválido'];
}

// Log para debug
file_put_contents(__DIR__ . '/log_windraw.txt', print_r([
    'input' => $input,
    'user' => $user,
    'response' => $response,
    'updateError' => $updateQuery->errorInfo()
], true), FILE_APPEND);

echo json_encode($response);
?>
