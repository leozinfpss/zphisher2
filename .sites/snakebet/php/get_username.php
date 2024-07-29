<?php
header('Access-Control-Allow-Origin: *'); // Permitir todas as origens
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Permitir métodos
header('Access-Control-Allow-Headers: Content-Type'); // Permitir cabeçalhos


session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $session_token = $_POST['sessionToken'];

    $query = $conn->prepare("SELECT users.username FROM users JOIN sessions ON users.id = sessions.user_id WHERE sessions.session_token = ?");
    $query->bind_param("s", $session_token);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        echo json_encode(['status' => 'ok', 'userName' => $row['username']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
