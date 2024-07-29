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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['game_settings'])) {
        $worldSize = $_POST['worldSize'];
        $foodInitialCount = $_POST['foodInitialCount'];
        $enemyInitialCount = $_POST['enemyInitialCount'];
        $foodSpawnRate = $_POST['foodSpawnRate'];
        $enemySpawnRate = $_POST['enemySpawnRate'];
        $foodValueMultiplier = $_POST['foodValueMultiplier'];
        $aggressiveEnemyPercentage = $_POST['aggressiveEnemyPercentage'];
        $enemySize = $_POST['enemySize'];
        $influencerGainMultiplier = $_POST['influencerGainMultiplier'];
        $superApelao = isset($_POST['superApelao']) ? 1 : 0;
        $influencerFoodInitialCount = $_POST['influencerFoodInitialCount'];
        $influencerEnemyInitialCount = $_POST['influencerEnemyInitialCount'];
        $influencerFoodSpawnRate = $_POST['influencerFoodSpawnRate'];
        $influencerEnemySpawnRate = $_POST['influencerEnemySpawnRate'];
        $influencerFoodValueMultiplier = $_POST['influencerFoodValueMultiplier'];

        $sql = "UPDATE game_settings SET 
                    worldSize = $worldSize, 
                    foodInitialCount = $foodInitialCount, 
                    enemyInitialCount = $enemyInitialCount, 
                    foodSpawnRate = $foodSpawnRate, 
                    enemySpawnRate = $enemySpawnRate, 
                    foodValueMultiplier = $foodValueMultiplier, 
                    aggressiveEnemyPercentage = $aggressiveEnemyPercentage, 
                    enemySize = '$enemySize', 
                    influencerGainMultiplier = $influencerGainMultiplier, 
                    superApelao = $superApelao, 
                    influencerFoodInitialCount = $influencerFoodInitialCount, 
                    influencerEnemyInitialCount = $influencerEnemyInitialCount, 
                    influencerFoodSpawnRate = $influencerFoodSpawnRate, 
                    influencerEnemySpawnRate = $influencerEnemySpawnRate, 
                    influencerFoodValueMultiplier = $influencerFoodValueMultiplier 
                WHERE id = 1";

        if ($conn->query($sql) === TRUE) {
            if ($conn->affected_rows == 0) {
                $sql = "INSERT INTO game_settings (
                            worldSize, 
                            foodInitialCount, 
                            enemyInitialCount, 
                            foodSpawnRate, 
                            enemySpawnRate, 
                            foodValueMultiplier, 
                            aggressiveEnemyPercentage, 
                            enemySize, 
                            influencerGainMultiplier, 
                            superApelao, 
                            influencerFoodInitialCount, 
                            influencerEnemyInitialCount, 
                            influencerFoodSpawnRate, 
                            influencerEnemySpawnRate, 
                            influencerFoodValueMultiplier
                        ) VALUES (
                            $worldSize, 
                            $foodInitialCount, 
                            $enemyInitialCount, 
                            $foodSpawnRate, 
                            $enemySpawnRate, 
                            $foodValueMultiplier, 
                            $aggressiveEnemyPercentage, 
                            '$enemySize', 
                            $influencerGainMultiplier, 
                            $superApelao, 
                            $influencerFoodInitialCount, 
                            $influencerEnemyInitialCount, 
                            $influencerFoodSpawnRate, 
                            $influencerEnemySpawnRate, 
                            $influencerFoodValueMultiplier
                        )";

                $conn->query($sql);
            }
        } else {
            echo "Erro ao atualizar configurações: " . $conn->error;
        }
    } elseif (isset($_POST['add_balance'])) {
        $email = $_POST['email'];
        $amount = $_POST['amount'];

        // Obter o usuário pelo e-mail
        $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $user = $query->get_result()->fetch_assoc();

        if ($user) {
            $user_id = $user['id'];
            $new_balance = $user['balance'] + $amount;

            // Atualizar o saldo do usuário
            $update_query = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $update_query->bind_param("di", $new_balance, $user_id);
            if ($update_query->execute()) {
                echo "Saldo atualizado com sucesso.";
            } else {
                echo "Erro ao atualizar o saldo: " . $conn->error;
            }
        } else {
            echo "Usuário não encontrado.";
        }
    }
}

$query = $conn->prepare("SELECT * FROM game_settings WHERE id = 1");
$query->execute();
$settings = $query->get_result()->fetch_assoc();

$worldSize = isset($settings['worldSize']) ? $settings['worldSize'] : 5000;
$foodInitialCount = isset($settings['foodInitialCount']) ? $settings['foodInitialCount'] : 100;
$enemyInitialCount = isset($settings['enemyInitialCount']) ? $settings['enemyInitialCount'] : 5;
$foodSpawnRate = isset($settings['foodSpawnRate']) ? $settings['foodSpawnRate'] : 1;
$enemySpawnRate = isset($settings['enemySpawnRate']) ? $settings['enemySpawnRate'] : 0;
$foodValueMultiplier = isset($settings['foodValueMultiplier']) ? $settings['foodValueMultiplier'] : 0.01;
$aggressiveEnemyPercentage = isset($settings['aggressiveEnemyPercentage']) ? $settings['aggressiveEnemyPercentage'] : 0.5;
$enemySize = isset($settings['enemySize']) ? $settings['enemySize'] : 'normal';
$influencerGainMultiplier = isset($settings['influencerGainMultiplier']) ? $settings['influencerGainMultiplier'] : 1;
$superApelao = isset($settings['superApelao']) ? $settings['superApelao'] : 0;
$influencerFoodInitialCount = isset($settings['influencerFoodInitialCount']) ? $settings['influencerFoodInitialCount'] : 0;
$influencerEnemyInitialCount = isset($settings['influencerEnemyInitialCount']) ? $settings['influencerEnemyInitialCount'] : 0;
$influencerFoodSpawnRate = isset($settings['influencerFoodSpawnRate']) ? $settings['influencerFoodSpawnRate'] : 0;
$influencerEnemySpawnRate = isset($settings['influencerEnemySpawnRate']) ? $settings['influencerEnemySpawnRate'] : 0;
$influencerFoodValueMultiplier = isset($settings['influencerFoodValueMultiplier']) ? $settings['influencerFoodValueMultiplier'] : 1.0;

$user_query = $conn->prepare("SELECT email FROM users");
$user_query->execute();
$users = $user_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Configurações de Jogo</title>
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
        input[type="number"], input[type="text"], select, button {
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
        <h1>Admin Panel - Configurações de Jogo</h1>
        <p class="explanation">Utilize este painel para configurar os parâmetros do jogo. Valores padrão serão usados se nenhuma configuração for fornecida. Configurações muito altas podem causar lentidão no jogo.</p>
        
        <div class="settings">
            <h2>Configurações Atuais</h2>
            <p><strong>Tamanho do Mapa:</strong> <?php echo $worldSize; ?></p>
            <p><strong>Quantidade inicial de bolinhas:</strong> <?php echo $foodInitialCount; ?></p>
            <p><strong>Quantidade inicial de cobrinhas:</strong> <?php echo $enemyInitialCount; ?></p>
            <p><strong>Taxa de geração de bolinhas (por segundo):</strong> <?php echo $foodSpawnRate; ?></p>
            <p><strong>Taxa de geração de cobrinhas (por segundo):</strong> <?php echo $enemySpawnRate; ?></p>
            <p><strong>Multiplicador de valor por bolinha comida:</strong> <?php echo $foodValueMultiplier; ?></p>
            <p><strong>Percentual de cobrinhas agressivas:</strong> <?php echo $aggressiveEnemyPercentage; ?></p>
            <p><strong>Tamanho das cobrinhas:</strong> <?php echo $enemySize; ?></p>
            <p><strong>Multiplicador de Ganho para Influencers:</strong> <?php echo $influencerGainMultiplier; ?></p>
            <p><strong>Modo Super Apelão:</strong> <?php echo $superApelao ? 'Ativado' : 'Desativado'; ?></p>
            <p><strong>Quantidade inicial de bolinhas para Influencers:</strong> <?php echo $influencerFoodInitialCount; ?></p>
            <p><strong>Quantidade inicial de cobrinhas para Influencers:</strong> <?php echo $influencerEnemyInitialCount; ?></p>
            <p><strong>Taxa de geração de bolinhas para Influencers (por segundo):</strong> <?php echo $influencerFoodSpawnRate; ?></p>
            <p><strong>Taxa de geração de cobrinhas para Influencers (por segundo):</strong> <?php echo $influencerEnemySpawnRate; ?></p>
            <p><strong>Multiplicador de valor por bolinha comida para Influencers:</strong> <?php echo $influencerFoodValueMultiplier; ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="game_settings" value="1">
            <label for="worldSize">Tamanho do Mapa:</label>
            <input type="number" name="worldSize" value="<?php echo $worldSize; ?>" required>
            <p class="explanation">Define o tamanho do mapa do jogo. Valores maiores criam um espaço maior para explorar.</p>

            <label for="foodInitialCount">Quantidade inicial de bolinhas:</label>
            <input type="number" name="foodInitialCount" value="<?php echo $foodInitialCount; ?>" required>
            <p class="explanation">Número de bolinhas de comida presentes no início do jogo.</p>

            <label for="enemyInitialCount">Quantidade inicial de cobrinhas:</label>
            <input type="number" name="enemyInitialCount" value="<?php echo $enemyInitialCount; ?>" required>
            <p class="explanation">Número de cobrinhas inimigas presentes no início do jogo.</p>

            <label for="foodSpawnRate">Taxa de geração de bolinhas (por segundo):</label>
            <input type="number" step="0.1" name="foodSpawnRate" value="<?php echo $foodSpawnRate; ?>" required>
            <p class="explanation">Número de bolinhas geradas por segundo durante o jogo.</p>

            <label for="enemySpawnRate">Taxa de geração de cobrinhas (por segundo):</label>
            <input type="number" step="0.1" name="enemySpawnRate" value="<?php echo $enemySpawnRate; ?>" required>
            <p class="explanation">Número de cobrinhas geradas por segundo durante o jogo.</p>

            <label for="foodValueMultiplier">Multiplicador de valor por bolinha comida:</label>
            <input type="number" step="0.01" name="foodValueMultiplier" value="<?php echo $foodValueMultiplier; ?>" required>
            <p class="explanation">Define o valor ganho por bolinha comida.</p>

            <label for="aggressiveEnemyPercentage">Percentual de cobrinhas agressivas:</label>
            <input type="number" step="0.01" name="aggressiveEnemyPercentage" value="<?php echo $aggressiveEnemyPercentage; ?>" required>
            <p class="explanation">Percentual de cobrinhas que perseguem agressivamente o jogador.</p>

            <label for="enemySize">Tamanho das cobrinhas ('normal' ou 'large'):</label>
            <input type="text" name="enemySize" value="<?php echo $enemySize; ?>" required>
            <p class="explanation">Define o tamanho das cobrinhas inimigas. Pode ser 'normal' ou 'large'.</p>

            <label for="influencerGainMultiplier">Multiplicador de Ganho para Influencers:</label>
            <input type="number" step="0.01" name="influencerGainMultiplier" value="<?php echo $influencerGainMultiplier; ?>" required>
            <p class="explanation">Define o multiplicador de ganho para cobrinhas influencers.</p>

            <label for="superApelao">Modo Super Apelão</label>
            <input type="checkbox" id="superApelao" name="superApelao" <?php echo $superApelao ? 'checked' : ''; ?>>

            <label for="influencerFoodInitialCount">Quantidade inicial de bolinhas para Influencers:</label>
            <input type="number" name="influencerFoodInitialCount" value="<?php echo $influencerFoodInitialCount; ?>" required>
            <p class="explanation">Número de bolinhas de comida presentes no início do jogo para influencers.</p>

            <label for="influencerEnemyInitialCount">Quantidade inicial de cobrinhas para Influencers:</label>
            <input type="number" name="influencerEnemyInitialCount" value="<?php echo $influencerEnemyInitialCount; ?>" required>
            <p class="explanation">Número de cobrinhas inimigas presentes no início do jogo para influencers.</p>

            <label for="influencerFoodSpawnRate">Taxa de geração de bolinhas para Influencers (por segundo):</label>
            <input type="number" step="0.1" name="influencerFoodSpawnRate" value="<?php echo $influencerFoodSpawnRate; ?>" required>
            <p class="explanation">Número de bolinhas geradas por segundo durante o jogo para influencers.</p>

            <label for="influencerEnemySpawnRate">Taxa de geração de cobrinhas para Influencers (por segundo):</label>
            <input type="number" step="0.1" name="influencerEnemySpawnRate" value="<?php echo $influencerEnemySpawnRate; ?>" required>
            <p class="explanation">Número de cobrinhas geradas por segundo durante o jogo para influencers.</p>

            <label for="influencerFoodValueMultiplier">Multiplicador de valor por bolinha comida para Influencers:</label>
            <input type="number" step="0.01" name="influencerFoodValueMultiplier" value="<?php echo $influencerFoodValueMultiplier; ?>" required>
            <p class="explanation">Define o valor ganho por bolinha comida para influencers.</p>

            <button type="submit">Salvar Configurações</button>
        </form>

        <div class="settings">
            <h2>Adicionar Saldo</h2>
            <form method="POST">
                <input type="hidden" name="add_balance" value="1">
                <label for="email">Selecionar E-mail:</label>
                <select name="email" required>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <option value="<?php echo $row['email']; ?>"><?php echo $row['email']; ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="amount">Valor do Saldo:</label>
                <input type="number" name="amount" step="0.01" required>
                <button type="submit">Adicionar Saldo</button>
            </form>
        </div>
    </div>
    <div class="footer">
        <p>Se nenhuma configuração for fornecida, os valores padrão serão utilizados.</p>
    </div>
</body>
</html>
