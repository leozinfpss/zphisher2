<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../php/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo 'okooko';
    $username = $_POST['username'];
    $password = $_POST['password'];

    var_dump($_POST);

    // Verificar credenciais do administrador
    $sql = "SELECT id, password FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        die("Erro ao preparar a consulta.");
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    var_dump($result);

    if ($result) {
        if (password_verify($password, $result['password'])) {
            $_SESSION['session_token'] = session_id();
            $_SESSION['user_id'] = $result['id'];
            header("Location: admin.php");
            exit();
        } else {
            $error = "Credenciais inválidas ou você não tem permissão para acessar.";
        }
    } else {
        $error = "Credenciais inválidas ou você não tem permissão para acessar.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px;
            width: 100%;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], button {
            margin-bottom: 20px;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
<?php
// echo password_hash("22062024", PASSWORD_BCRYPT);
?>
