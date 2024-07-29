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

$where = [];
$params = [];
$types = '';

// Filtros por data
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $where[] = "d.created_at >= ?";
    $params[] = $_GET['start_date'];
    $types .= 's';
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $where[] = "d.created_at <= ?";
    $params[] = $_GET['end_date'] . ' 23:59:59';
    $types .= 's';
}

$where_clause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// Fetch all deposits with filters
$query_string = "SELECT d.id, u.username, d.value, d.status, d.transaction_id, d.created_at, d.updated_at 
                 FROM deposits d 
                 JOIN users u ON d.user_id = u.id
                 $where_clause";

$query = $conn->prepare($query_string);

if (!empty($params)) {
    $query->bind_param($types, ...$params);
}

$query->execute();
$deposits = $query->get_result();

// Calcular somas gerais
$soma_query_string = "SELECT 
                        SUM(CASE WHEN DATE(d.created_at) = CURDATE() THEN d.value ELSE 0 END) AS soma_dia,
                        SUM(CASE WHEN WEEK(d.created_at) = WEEK(CURDATE()) THEN d.value ELSE 0 END) AS soma_semana,
                        SUM(CASE WHEN MONTH(d.created_at) = MONTH(CURDATE()) THEN d.value ELSE 0 END) AS soma_mes
                      FROM deposits d
                      $where_clause";

$soma_query = $conn->prepare($soma_query_string);

if (!empty($params)) {
    $soma_query->bind_param($types, ...$params);
}

$soma_query->execute();
$somas = $soma_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Depósitos</title>
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
            max-width: 1200px;
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
            justify-content: center;
            margin-bottom: 20px;
        }
        label {
            margin: 0 10px;
            font-weight: bold;
        }
        input[type="date"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
        }
        .somas {
            text-align: center;
            margin-bottom: 20px;
        }
        .somas p {
            font-size: 18px;
            color: #333;
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
        <h1>Admin Panel - Depósitos</h1>
        
        <form method="GET">
            <label for="start_date">Data Inicial:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
            <label for="end_date">Data Final:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            <button type="submit">Filtrar</button>
        </form>
        
        <div class="somas">
            <p>Total do Dia: R$<?php echo number_format($somas['soma_dia'], 2, ',', '.'); ?></p>
            <p>Total da Semana: R$<?php echo number_format($somas['soma_semana'], 2, ',', '.'); ?></p>
            <p>Total do Mês: R$<?php echo number_format($somas['soma_mes'], 2, ',', '.'); ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>ID da Transação</th>
                    <th>Criado em</th>
                    <th>Atualizado em</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($deposits->num_rows > 0): ?>
                    <?php while($deposit = $deposits->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $deposit['id']; ?></td>
                            <td><?php echo $deposit['username']; ?></td>
                            <td><?php echo 'R$' . number_format($deposit['value'], 2, ',', '.'); ?></td>
                            <td><?php echo ucfirst($deposit['status']); ?></td>
                            <td><?php echo $deposit['transaction_id']; ?></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($deposit['created_at'])); ?></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($deposit['updated_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhum depósito encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>Se nenhum depósito estiver listado, isso significa que não há depósitos no momento.</p>
    </div>
</body>
</html>
