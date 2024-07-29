<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    header("Location: ../login.php");
    exit();
}

// Obter os dados do formulário
$user_id = $_POST['user_id'];
$email = $_POST['email'];
$phone = $_POST['telefone'];
$name = $_POST['name'];

// Atualizar os dados do usuário no banco de dados
$query = $conn->prepare("UPDATE users SET email = ?, phone = ?, name = ? WHERE id = ?");
$query->bind_param("sssi", $email, $phone, $name, $user_id);

if ($query->execute()) {
    // Redirecionar de volta para o perfil com uma mensagem de sucesso
    $_SESSION['message'] = "Perfil atualizado com sucesso!";
    header("Location: profile.php");
} else {
    // Redirecionar de volta para o perfil com uma mensagem de erro
    $_SESSION['error'] = "Erro ao atualizar perfil. Tente novamente.";
    header("Location: profile.php");
}
?>
