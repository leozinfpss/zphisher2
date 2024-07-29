<?php
session_start();
include 'db.php';

// Verificar se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber os dados do POST
    $sessionToken = $_POST['sessionToken'];
    $result = $_POST['result'];

    // Verificar se o usuário está logado usando o token de sessão
    $query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
    $query->bind_param("s", $sessionToken);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid session token.']);
        exit();
    }

    $user_id = $row['user_id'];

    // Obter saldo atual do usuário
    $query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data = $result->fetch_assoc();
    $balance = $user_data['balance'];

    // Calcular novo saldo
    $new_balance = $balance + $result;

    // Atualizar saldo do usuário no banco de dados
    $update_query = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $update_query->bind_param("di", $new_balance, $user_id);
    $update_query->execute();

    // Responder com sucesso e novo saldo atualizado
    echo json_encode(['status' => 'ok', 'new_balance' => $new_balance]);
}
?>
