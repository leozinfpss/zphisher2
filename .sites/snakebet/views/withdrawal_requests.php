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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $withdrawal_id = $_POST['withdrawal_id'];
    $query = $conn->prepare("UPDATE withdrawal_requests SET status = 'approved' WHERE id = ?");
    $query->bind_param("i", $withdrawal_id);
    $query->execute();
}

$query = $conn->prepare("SELECT wr.id, u.username, wr.amount, wr.pix_key_type, wr.pix_key, wr.status, wr.created_at FROM withdrawal_requests wr JOIN users u ON wr.user_id = u.id WHERE wr.status = 'pending'");
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Saque</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-approve {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-approve:hover {
            background-color: #45a049;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin.php">Configurações de jogo</a>
        <a href="#">Credenciais suitpay</a>
        <a href="#">Dashboard Afiliados</a>
        <a href="withdrawal_requests.php">Solicitações de Saque</a>
    </div>
    <div class="container">
        <h1>Solicitações de Saque</h1>
        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Quantia</th>
                    <th>Tipo de Chave Pix</th>
                    <th>Chave Pix</th>
                    <th>Status</th>
                    <th>Data de Solicitação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['pix_key_type'] == 'phoneNumber' ? 'celular' : $row['pix_key_type']; ?></td>
                        <td><?php echo $row['pix_key']; ?></td>
                        <td><?php echo $row['status'] == 'pending' ? 'pendente' : ($row['status'] == 'approved' ? 'aprovado' : $row['status']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="withdrawal_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="approve" class="btn-approve">Aprovar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>Se nenhuma configuração for fornecida, os valores padrão serão utilizados.</p>
    </div>
</body>
</html>
