<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['telefone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $ref = isset($_POST['ref']) ? intval($_POST['ref']) : null;

    // Verificar se o usuário já existe
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        die("Erro ao preparar a consulta.");
    }
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Usuário já existe";
    } else {
        // Inserir novo usuário
        $sql = "INSERT INTO users (username, email, phone, password, affiliate_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Erro ao preparar a consulta: " . $conn->error);
            die("Erro ao preparar a consulta.");
        }
        $stmt->bind_param("ssssi", $username, $email, $phone, $password, $ref);
        if ($stmt->execute()) {
            $new_user_id = $conn->insert_id;
            
            // Registrar a indicação, se houver
            if ($ref) {
                $query = $conn->prepare("INSERT INTO referrals (user_id, referred_user_id, status, referral_amount) VALUES (?, ?, 'registered', 0.00)");
                if ($query === false) {
                    error_log("Erro ao preparar a consulta: " . $conn->error);
                    die("Erro ao preparar a consulta.");
                }
                $query->bind_param("ii", $ref, $new_user_id);
                $query->execute();
            }

            // Gerar um token de sessão
            $session_token = bin2hex(random_bytes(32));
            
            // Armazenar o token de sessão no banco de dados
            $sql = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                error_log("Erro ao preparar a consulta: " . $conn->error);
                die("Erro ao preparar a consulta.");
            }
            $stmt->bind_param("is", $new_user_id, $session_token);
            $stmt->execute();
            
            $_SESSION['session_token'] = $session_token;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Erro: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
