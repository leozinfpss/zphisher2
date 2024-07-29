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
$current_password = $_POST['current_password'];
$new_password = $_POST['password'];
$password_confirmation = $_POST['password_confirmation'];

// Verificar se a nova senha e a confirmação são iguais
if ($new_password !== $password_confirmation) {
    $_SESSION['error'] = "A nova senha e a confirmação não correspondem.";
    header("Location: profile.php");
    exit();
}

// Obter a senha atual do banco de dados
$query = $conn->prepare("SELECT password FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();
$hashed_current_password = $row['password'];

// Verificar se a senha atual fornecida está correta
if (!password_verify($current_password, $hashed_current_password)) {
    $_SESSION['error'] = "A senha atual está incorreta.";
    header("Location: profile.php");
    exit();
}

// Hash a nova senha
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Atualizar a senha no banco de dados
$query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$query->bind_param("si", $new_hashed_password, $user_id);

if ($query->execute()) {
    $_SESSION['message'] = "Senha atualizada com sucesso!";
    header("Location: profile.php");
} else {
    $_SESSION['error'] = "Erro ao atualizar senha. Tente novamente.";
    header("Location: profile.php");
}
?>
