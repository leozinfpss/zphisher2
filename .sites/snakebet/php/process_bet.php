<?php
include 'db.php';

$bet = $_POST['bet'];
$sessionToken = $_POST['sessionToken'];

if (empty($bet) || empty($sessionToken)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters.']);
    exit();
}

// Verificar se o token de sessão é válido
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $sessionToken);
$query->execute();
$result = $query->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session token.']);
    exit();
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Verificar se o saldo é suficiente
$query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();
$balance = $user_data['balance'];

if ($balance < $bet) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance.']);
    exit();
}

// Deduzir a aposta do saldo
$new_balance = $balance - $bet;
$query = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
$query->bind_param("di", $new_balance, $user_id);
$query->execute();

echo json_encode(['status' => 'ok', 'gameData' => ['bet' => $bet]]);
?>
