<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'php/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar credenciais do usuário
    $sql = "CALL login_user(?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        die("Erro ao preparar a consulta.");
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (isset($result['error_message'])) {
        echo $result['error_message'];
    } else {
        $_SESSION['session_token'] = $result['session_token'];
        $_SESSION['username'] = $username;
        header("Location: php/dashboard.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
