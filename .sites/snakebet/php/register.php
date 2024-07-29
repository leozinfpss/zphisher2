<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['telefone'];
    $password = $_POST['password'];
    $aff_id = $_POST['aff_id'];

    // Verificar se o usuário já existe
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "User already exists";
    } else {
        // Inserir novo usuário
        $sql = "INSERT INTO users (username, email, phone, password, affiliate_id) VALUES (?, ?, ?, SHA2(?, 256), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $phone, $password, $aff_id);
        if ($stmt->execute()) {
            echo "User registered successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
