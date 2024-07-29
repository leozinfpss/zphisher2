<?php
// Inclua o arquivo de conexão com o banco de dados
include 'db.php';

// Verifique se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receba o token de sessão do corpo da requisição
    $sessionToken = $_POST['sessionToken'];

    // Prepare a consulta para verificar o token de sessão no banco de dados
    $query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
    $query->bind_param("s", $sessionToken);
    $query->execute();
    $result = $query->get_result();

    // Verifique se encontrou alguma linha (se o token de sessão é válido)
    if ($result->num_rows > 0) {
        // Obtém o ID do usuário associado ao token de sessão
        $row = $result->fetch_assoc();
        $userId = $row['user_id'];

        // Consulta para obter informações adicionais do usuário (opcional)
        $queryUser = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $queryUser->bind_param("i", $userId);
        $queryUser->execute();
        $resultUser = $queryUser->get_result();
        $userData = $resultUser->fetch_assoc();

        // Se desejar, você pode retornar mais informações do usuário além do status 'ok'
        echo json_encode(['status' => 'ok', 'userId' => $userId, 'userData' => $userData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid session token.']);
    }
} else {
    // Se o método de requisição não for POST, retorne um erro
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
