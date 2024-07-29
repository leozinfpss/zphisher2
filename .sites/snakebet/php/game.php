<?php
// game.php

session_start();
include 'db.php';

if (!isset($_SESSION['session_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token de sessão inválido']);
    exit();
}

$session_token = $_SESSION['session_token'];

// Obter o ID do usuário a partir do token de sessão
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Token de sessão inválido']);
    exit();
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Obter saldo do usuário
$query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();
$balance = $user_data['balance'];

echo json_encode(['status' => 'ok', 'user' => $user_data]);

?>
