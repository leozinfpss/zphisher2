<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_or_email = $_POST['username']; // Pode ser username ou email
    $password = $_POST['password'];

    // Buscar usuário pelo username ou email
    $sql = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        die("Erro ao preparar a consulta.");
    }
    $stmt->bind_param("ss", $user_or_email, $user_or_email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password, $result['password'])) {
        // Gerar um token de sessão
        $session_token = bin2hex(random_bytes(32));
        
        // Armazenar o token de sessão no banco de dados
        $sql = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $conn->error);
            die("Erro ao preparar a consulta.");
        }
        $stmt->bind_param("is", $result['id'], $session_token);
        $stmt->execute();
        
        $_SESSION['session_token'] = $session_token;
        $_SESSION['username'] = $result['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Credenciais inválidas.";
    }

    $stmt->close();
    $conn->close();
}
?>
