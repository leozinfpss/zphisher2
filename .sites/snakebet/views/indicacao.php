<?php
session_start();
include '../php/db.php';
include '../php/functions.php';

if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT admin FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0 || $result->fetch_assoc()['admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Configurar comissão global
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_global_commission'])) {
    $globalMinDeposit = $_POST['global_min_deposit'];
    $globalCommission = $_POST['global_commission'];

    $query = $conn->prepare("REPLACE INTO referral_settings (setting_key, setting_value) VALUES ('min_deposit', ?), ('commission_value', ?)");
    $query->bind_param("dd", $globalMinDeposit, $globalCommission);
    $query->execute();
}

// Configurar comissão personalizada por usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_commission'])) {
    $userId = $_POST['user_id'];
    $commissionValue = $_POST['commission_value'];

    if (userExists($userId)) {
        $query = $conn->prepare("REPLACE INTO user_commissions (user_id, commission_value) VALUES (?, ?)");
        $query->bind_param("id", $userId, $commissionValue);
        $query->execute();
    } else {
        echo "Usuário não encontrado.";
    }
}

// Definir porcentagem de comissão final
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_percentage'])) {
    $percentage = $_POST['percentage'];
    $userId = $_POST['user_id'];

    if (userExists($userId)) {
        $query = $conn->prepare("UPDATE referrals SET final_referral_amount = referral_amount * ? / 100 WHERE user_id = ? AND status = 'deposited'");
        $query->bind_param("di", $percentage, $userId);
        $query->execute();
    } else {
        echo "Usuário não encontrado.";
    }
}

// Obter os dados dos usuários e suas comissões
$query = $conn->prepare("SELECT u.id, u.username, u.email, uc.commission_value FROM users u LEFT JOIN user_commissions uc ON u.id = uc.user_id");
$query->execute();
$result = $query->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$globalCommission = getGlobalSetting('commission_value') ?? 0;
$globalMinDeposit = getGlobalSetting('min_deposit') ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Configuração de Indicações</title>
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
        input[type="number"], input[type="text"], button {
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin.php">Configurações de Jogo</a>
        <a href="manage_influencers.php">Incluir Influencer</a>
        <a href="suitpay.php">Credenciais suitpay</a>
        <a href="indicacao.php">Administração Afiliados</a>
        <a href="dashboard_afiliados.php">Dashboard Afiliados</a>
        <a href="withdrawal_requests.php">Solicitações de Saque</a>
        <a href="admin_deposits.php">Depósitos</a>
    </div>

    <div class="container">
        <h1>Configuração de Indicações</h1>
        <h2>Comissão Global</h2>
        <form method="POST">
            <label for="global_min_deposit">Valor Mínimo de Depósito Global:</label>
            <input type="number" id="global_min_deposit" name="global_min_deposit" step="0.01" value="<?php echo $globalMinDeposit; ?>" required>
            <label for="global_commission">Comissão Global por Indicação:</label>
            <input type="number" id="global_commission" name="global_commission" step="0.01" value="<?php echo $globalCommission; ?>" required>
            <button type="submit" name="set_global_commission">Salvar Configuração Global</button>
        </form>

        <h2>Configurar Comissão por Usuário</h2>
        <form method="POST">
            <label for="user_id">Selecionar Usuário:</label>
            <select id="user_id" name="user_id" required>
                <option value="">Selecione um usuário</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")"; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="commission_value">Comissão por Indicação:</label>
            <input type="number" id="commission_value" name="commission_value" step="0.01" required>
            <button type="submit" name="set_commission">Salvar Configurações</button>
        </form>

        <h2>Definir Porcentagem de Comissão Final</h2>
        <form method="POST">
            <label for="user_id">Selecionar Usuário:</label>
            <select id="user_id" name="user_id" required>
                <option value="">Selecione um usuário</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")"; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="percentage">Porcentagem de Comissão Final (%):</label>
            <input type="number" id="percentage" name="percentage" step="0.01" required>
            <button type="submit" name="set_percentage">Definir Porcentagem</button>
        </form>

        <h2>Usuários e Suas Comissões</h2>
        <table>
            <tr>
                <th>Usuário</th>
                <th>Email</th>
                <th>Comissão por Indicação</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>R$ <?php echo isset($user['commission_value']) ? number_format($user['commission_value'], 2, ',', '.') : number_format($globalCommission, 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
