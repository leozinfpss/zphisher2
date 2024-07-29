<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    echo json_encode([]);
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$session_token = $_SESSION['session_token'];
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode([]);
    exit();
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Obter jogadas do usuário
$query = $conn->prepare("
    SELECT gt.id, gt.bet_value, gt.win_value, gt.created_at AS data, 
           CASE 
               WHEN gt.win_value > gt.bet_value THEN 'GANHO'
               WHEN gt.win_value < gt.bet_value THEN 'PERCA'
               ELSE 'PENDENTE'
           END AS resultado,
           0 AS score  -- você pode adicionar lógica para o cálculo de score se necessário
    FROM game_transactions gt
    WHERE gt.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$jogadas = [];
while ($row = $result->fetch_assoc()) {
    $jogadas[] = $row;
}

echo json_encode($jogadas);
?>
