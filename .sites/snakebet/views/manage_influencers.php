<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
  
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0 || $result->fetch_assoc()['id'] != 1) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_influencer'])) {
    $username = $_POST['username'];
    $query = $conn->prepare("UPDATE users SET is_influencer = 1 WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_influencer'])) {
    $username = $_POST['username'];
    $query = $conn->prepare("UPDATE users SET is_influencer = 0 WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
}

$influencers_query = $conn->prepare("SELECT username FROM users WHERE is_influencer = 1");
$influencers_query->execute();
$influencers = $influencers_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Gerenciar Influencers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
        }
        .navbar a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
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
        input[type="number"], input[type="text"], button, select {
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
        .explanation {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
        }
        .settings {
            background-color: #e9e9e9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .settings h2 {
            color: #333;
        }
        .settings p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
        <div class="navbar">
        <a href="admin.php">Configurações de Jogo</a>
        <a href="manage_influencers.php">Incluir Influencer</a>
        <a href="#">Credenciais suitpay</a>
        <a href="#">Dashboard Afiliados</a>
        <a href="withdrawal_requests.php">Solicitações de Saque</a>
        <a href="admin_deposits.php">Depósitos</a>
    </div>
    <div class="container">
        <h1>Admin Panel - Gerenciar Influencers</h1>
        <p class="explanation">Utilize este painel para adicionar ou remover influencers.</p>
        
        <div class="settings">
            <h2>Influencers Atuais</h2>
            <ul>
                <?php foreach ($influencers as $influencer): ?>
                    <li><?php echo $influencer['username']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="POST">
            <input type="hidden" name="set_influencer" value="1">
            <label for="username">Adicionar Influencer (por username):</label>
            <select name="username" required>
                <?php
                $users_query = $conn->prepare("SELECT username FROM users WHERE is_influencer = 0");
                $users_query->execute();
                $users = $users_query->get_result()->fetch_all(MYSQLI_ASSOC);
                foreach ($users as $user) {
                    echo "<option value='" . $user['username'] . "'>" . $user['username'] . "</option>";
                }
                ?>
            </select>
            <button type="submit">Adicionar Influencer</button>
        </form>

        <form method="POST">
            <input type="hidden" name="remove_influencer" value="1">
            <label for="username">Remover Influencer (por username):</label>
            <select name="username" required>
                <?php
                foreach ($influencers as $influencer) {
                    echo "<option value='" . $influencer['username'] . "'>" . $influencer['username'] . "</option>";
                }
                ?>
            </select>
            <button type="submit">Remover Influencer</button>
        </form>
    </div>
    <div class="footer">
        <p>Gerencie os influencers da plataforma.</p>
    </div>
</body>
</html>
