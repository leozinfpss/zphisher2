<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão inválida.']);
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$session_token = $_SESSION['session_token'];
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão inválida.']);
    exit();
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

$postData = json_decode(file_get_contents('php://input'), true);
$bet_value = $postData['bet_value'] ?? 0;

if ($bet_value > 0) {
    // Inserir a aposta na tabela bets
    $query = $conn->prepare("INSERT INTO bets (user_id, bet_value) VALUES (?, ?)");
    $query->bind_param("id", $user_id, $bet_value);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a aposta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Valor da aposta inválido.']);
}
?>
