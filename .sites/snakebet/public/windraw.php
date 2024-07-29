<?php
// windraw.php

session_start();
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['sessionToken']) || !isset($data['value_v']) || !isset($data['score'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
    exit();
}

$session_token = $data['sessionToken'];
$value_v = $data['value_v'];
$score = $data['score'];

// Verificar se o token é válido
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

// Atualizar saldo do usuário
$query = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
$query->bind_param("di", $value_v, $user_id);
$query->execute();

echo json_encode(['status' => 'ok', 'message' => 'Saldo atualizado']);

?>
