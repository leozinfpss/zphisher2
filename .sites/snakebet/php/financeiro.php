<?php
session_start();
include 'db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    header("Location: ../login.php");
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$session_token = $_SESSION['session_token'];
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Obter saldo do usuário
$query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();
$balance = $user_data['balance'];

// Obter depósitos do usuário
$query = $conn->prepare("SELECT created_at, 'Depósito' as transaction_type, status, value as amount FROM deposits WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$deposits = $result->fetch_all(MYSQLI_ASSOC);

// Obter saques do usuário
$query = $conn->prepare("SELECT created_at, 'Saque' as transaction_type, status, amount FROM withdrawal_requests WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$withdrawals = $result->fetch_all(MYSQLI_ASSOC);

// Combinar depósitos e saques
$transactions = array_merge($deposits, $withdrawals);

// Ordenar transações por data
usort($transactions, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Função para traduzir status
function traduzirStatus($status) {
    $traducoes = [
        'pending' => 'Pendente',
        'confirmed' => 'Finalizado',
        'failed' => 'Falhou',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado'
    ];
    return $traducoes[$status] ?? $status;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - SnakeBet</title>
    <link rel="stylesheet" href="../css/line.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/details.css">
    <link href="../css/toastify.css" rel="stylesheet">
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="icon" href="../images/snakebet-mascote2.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <script src="../js/jquery-3.6.0.min.js"></script>
    <link href="../css/css" rel="stylesheet">
    <link rel="stylesheet" href="../css/app.css">
    <link rel="stylesheet" href="../css/app-D_f84DVn.css">
    <script type="module" src="../js/app-mqEmiGqA.js"></script>
</head>
<body class="font-sans antialiased">
    <style>
        @font-face {
            font-family: 'SuperPositive';
            src: url('../fonts/super_positive/Super Positive Personal Use.ttf') format('truetype');
        }
        .modal {
            position: fixed;
            width: 100%;
            height: 100vh;
            background: #1d1b1b5e;
            visibility: hidden;
        }
        .modal.show {
            display: block;
            opacity: 1;
            visibility: visible;
            transition-delay: 0s;
        }
        .modalContainer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 10px;
            border-bottom: 2px solid #4aeba1;
            border-right: 2px solid #4aeba1;
            background: #263043;
            padding: 0.8rem;
            width: 50%;
        }
        @media screen and (max-width:1028px) {
            .modalContainer {
                width: 75%;
            }
        }
        @media screen and (max-width:726px) {
            .modalContainer {
                width: 95%;
            }
        }
        .notification_cont .ler button {
            padding: 0.4rem;
            background: #263043;
            color: #4aeba1;
            font-weight: bold;
            border-radius: 5px;
        }
        .notification_cont.white {
            color: #fff;
        }
        .notification_cont.red {
            color: #c40707;
        }
        .notification_cont.orange {
            color: #c78201;
        }
        .notification_cont.green {
            color: #01c711;
        }
        .notification_cont {
            padding: 0.6rem;
            display: flex;
            border-radius: 10px;
            justify-content: space-between;
            align-items: center;
            background: #181c25;
            margin-bottom: 0.4rem;
        }
        .modalContainer .title i {
            font-size: 1.8rem;
            cursor: pointer;
        }
        .modalContainer .title h2 {
            font-size: 1.3rem;
        }
        .modalContainer .title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            color: #fff;
        }
    </style>
    <div id="modal" class="modal">
        <div class="modalContainer">
            <div class="title">
                <h2>Notificações</h2>
                <i id="fecharmodal" class="bx bx-x"></i>
            </div>
            <script>
                $(document).ready(function () {
                    $('.ler button').click(function () {
                        var button = $(this);
                        var notificationId = button.data('notification-id');
                        $.ajax({
                            type: 'POST',
                            url: '../php/mark_as_read.php',
                            data: { notification_id: notificationId },
                            success: function (response) {
                                var counterValue = parseInt(document.getElementById('counterNotify').innerText);
                                if (counterValue > 0) {
                                    counterValue--;
                                    document.getElementById('counterNotify').innerText = counterValue;
                                }
                                button.closest('.notification_cont').remove();
                            },
                            error: function (xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>

    <div class="grid-container">
        <!-- Header -->
        <div id="openModal" class="div_notification">
            <div class="marginNotification">
                <div><i class="bx bxs-bell"></i></div>
                <div class="value_notify" id="counterNotify">0</div>
            </div>
        </div>
        <header class="header">
            <div class="menu-icon" onclick="openSidebar()">
                <span class="material-icons-outlined">menu</span>
            </div>
            <div class="imagemTopHeard">
                <a style="display:flex;justify-content:center;" href="/dashboard">
                    <img src="../images/Logotipo_SnakeBet.svg" alt="">
                </a>
            </div>
        </header>
        <!-- End Header -->

        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    <div><img src="../images/Logotipo_SnakeBet.svg" alt=""></div>
                    <div class="welcospa"><span>Seja muito bem vindo(a) ao SnakeBet.io</span></div>
                </div>
                <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
            </div>
            <div class="saldomesg">
                <img src="../images/Total%20dinheiro.svg" alt="myprofile"> R$ <?php echo number_format($balance, 2, ',', '.'); ?>
            </div>
            <a href="profile.php">
                <div class="sidemenuTitle">
                    <img src="../images/Perfil.svg" alt="myprofile"> Meu Perfil
                </div>
            </a>
            <a href="depositar.php">
                <div class="sidemenuTitle">
                    <img src="../images/Depositar.svg" alt="deposito"> Depositar
                </div>
            </a>
            <a href="saque.php">
                <div class="sidemenuTitle">
                    <img src="../images/Sacar.svg" alt="myprofile"> Sacar
                </div>
            </a>
            <a href="game/jogadas.php">
                <div class="sidemenuTitle">
                    <img src="../images/Histórico.svg" alt="myprofile"> Jogadas
                </div>
            </a>
            <a href="financeiro.php">
                <div class="sidemenuTitle">
                    <img src="../images/Financeiro.svg" alt="myprofile"> Financeiro
                </div>
            </a>
            <a href="compartilhar.php">
                <div class="sidemenuTitle">
                    <img src="../images/compartilhar.png" alt="myprofile"> Compartilhar
                </div>
            </a>
            <a href="dashboard.php">
                <div class="sidemenuTitle">
                    <img src="../images/Jogar.svg" alt="myprofile"> Jogar
                </div>
            </a>
            <a href="logout.php">
                <div class="sidemenuTitle">
                    <img src="../images/Sair.svg" alt="myprofile"> Sair
                </div>
            </a>
        </aside>
        <!-- End Sidebar -->

        <!-- Main -->
        <main class="main-container">
            <div class="divCentral">
                <div class="title">
                    <h1>FINANCEIRO</h1>
                </div>
                <div class="tableFinance">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Movimentação</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                                    <td><?php echo $transaction['transaction_type']; ?></td>
                                    <td><span><?php echo traduzirStatus($transaction['status']); ?></span></td>                                    <td>R$ <?php echo number_format($transaction['amount'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <!-- End Main -->
    </div>

    <script>
        document.getElementById('openModal').addEventListener('click', function() {
            document.getElementById('modal').classList.add('show');
        });

        document.getElementById('fecharmodal').addEventListener('click', function() {
            document.getElementById('modal').classList.remove('show');
        });

        let sidebarOpen = false;
        const sidebar = document.getElementById('sidebar');

        function openSidebar() {
            if (!sidebarOpen) {
                sidebar.classList.add('sidebar-responsive');
                sidebarOpen = true;
            }
        }

        function closeSidebar() {
            if (sidebarOpen) {
                sidebar.classList.remove('sidebar-responsive');
                sidebarOpen = false;
            }
        }
    </script>
    <!-- Scripts -->
    <script src="../js/apexcharts.min.js"></script>
    <script src="../js/toastify-js.js"></script>
</body>
</html>
