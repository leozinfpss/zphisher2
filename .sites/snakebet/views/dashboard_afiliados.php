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

// Verificar depósitos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $filteredUserId = $_POST['filtered_user_id'];
    validateDeposits($filteredUserId);
}

// Aprovar comissão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $referralId = $_POST['referral_id'];
    $referralQuery = $conn->prepare("SELECT * FROM referrals WHERE id = ?");
    $referralQuery->bind_param("i", $referralId);
    $referralQuery->execute();
    $referral = $referralQuery->get_result()->fetch_assoc();

    if ($referral && !$referral['approved']) {
        $referrerId = $referral['user_id'];
        $commissionAmount = $referral['final_referral_amount'];

        $updateReferralQuery = $conn->prepare("UPDATE referrals SET approved = 1 WHERE id = ?");
        $updateReferralQuery->bind_param("i", $referralId);
        $updateReferralQuery->execute();

        $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $updateBalanceQuery->bind_param("di", $commissionAmount, $referrerId);
        $updateBalanceQuery->execute();
    }
}

// Obter os dados dos usuários que alcançaram a meta
$query = $conn->prepare("SELECT r.id, u.username AS referrer_username, u2.username AS referred_username, r.final_referral_amount, r.approved 
                         FROM referrals r 
                         JOIN users u ON r.user_id = u.id 
                         JOIN users u2 ON r.referred_user_id = u2.id 
                         WHERE r.status = 'deposited'");
$query->execute();
$result = $query->get_result();

$referrals = [];
while ($row = $result->fetch_assoc()) {
    $referrals[] = $row;
}

// Obter todos os usuários
$usersQuery = $conn->prepare("SELECT id, username FROM users");
$usersQuery->execute();
$usersResult = $usersQuery->get_result();
$users = [];
while ($user = $usersResult->fetch_assoc()) {
    $users[] = $user;
}

// Obter totais
$totalCommission = 0;
$approvedCommission = 0;
$pendingCommission = 0;
foreach ($referrals as $referral) {
    $totalCommission += $referral['final_referral_amount'];
    if ($referral['approved']) {
        $approvedCommission += $referral['final_referral_amount'];
    } else {
        $pendingCommission += $referral['final_referral_amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard de Afiliados</title>
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
            margin-bottom: 20px;
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
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .totals {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .total-box {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            width: 30%;
            text-align: center;
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
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
        }
        .approve-button, .verify-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .approve-button:disabled, .verify-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .verify-form {
            text-align: center;
            margin-bottom: 20px;
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
        <h1>Dashboard de Afiliados</h1>
        <div class="verify-form">
            <form method="POST">
                <label for="filtered_user_id">Filtrar por Usuário:</label>
                <select id="filtered_user_id" name="filtered_user_id" required>
                    <option value="">Selecione um usuário</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="verify" class="verify-button">Verificar</button>
            </form>
        </div>
        <div class="totals">
            <div class="total-box">
                <h2>Total de Comissões</h2>
                <p>R$ <?php echo number_format($totalCommission, 2, ',', '.'); ?></p>
            </div>
            <div class="total-box">
                <h2>Comissões Aprovadas</h2>
                <p>R$ <?php echo number_format($approvedCommission, 2, ',', '.'); ?></p>
            </div>
            <div class="total-box">
                <h2>Comissões Pendentes</h2>
                <p>R$ <?php echo number_format($pendingCommission, 2, ',', '.'); ?></p>
            </div>
        </div>
        <h2>Comissões Pendentes</h2>
        <table>
            <tr>
                <th>Usuário Indicador</th>
                <th>Usuário Indicado</th>
                <th>Valor Final da Comissão</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
            <?php if (count($referrals) > 0): ?>
                <?php foreach ($referrals as $referral): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($referral['referrer_username']); ?></td>
                        <td><?php echo htmlspecialchars($referral['referred_username']); ?></td>
                        <td>R$ <?php echo number_format($referral['final_referral_amount'], 2, ',', '.'); ?></td>
                        <td><?php echo $referral['approved'] ? 'Aprovado' : 'Pendente'; ?></td>
                        <td>
                            <?php if (!$referral['approved']): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                                    <button type="submit" name="approve" class="approve-button">Aprovar</button>
                                </form>
                            <?php else: ?>
                                <button class="approve-button" disabled>Aprovado</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhum usuário alcançou a meta ainda.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="footer">
        <p>Dados atualizados em tempo real.</p>
    </div>
</body>
</html>
