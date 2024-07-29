<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não está logado.']);
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$session_token = $_SESSION['session_token'];
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão inválida.']);
    exit();
}

$user_id = $row['user_id'];

// Capturar valores do formulário
$valor_saque = $_POST['valor_saque'];
$pix_key_type = $_POST['pix'];
$pix_key = $_POST['pix_chave'];

// Validar se o valor do saque é maior que o saldo disponível
$query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();

if ($valor_saque > $user_data['balance']) {
    echo json_encode(['status' => 'error', 'message' => 'Saldo insuficiente.']);
    exit();
}

// Inserir a solicitação de saque no banco de dados
$query = $conn->prepare("INSERT INTO withdrawal_requests (user_id, amount, pix_key_type, pix_key, status) VALUES (?, ?, ?, ?, 'pending')");
$query->bind_param("idss", $user_id, $valor_saque, $pix_key_type, $pix_key);
if ($query->execute()) {
    // Atualizar o saldo do usuário
    $new_balance = $user_data['balance'] - $valor_saque;
    $query = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $query->bind_param("di", $new_balance, $user_id);
    $query->execute();

    echo json_encode(['status' => 'success', 'message' => 'Saque solicitado com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar a solicitação de saque.']);
}
?>
