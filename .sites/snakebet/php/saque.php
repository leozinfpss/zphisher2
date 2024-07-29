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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacar - SnakeBet</title>
    <link rel="stylesheet" href="../css/line.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="../css/toastify.css" rel="stylesheet">
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link href="../css/saque.css" rel="stylesheet">

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
    <style>

.modalDeposit .content .title h1{
        font-size: 1.7rem;
    }
    .modalDeposit .content .title{
        padding: 0.5rem;
    }
    .modalDeposit .content .exit{
        position: absolute;
        right: 1rem;
        font-size: 1.9rem;
        cursor: pointer;
    }
    .modalDeposit .content .corpoQr img{
        width: 10rem;
        background: #fff;
        border-radius: 10px;
    }
    .modalDeposit .content .corpoQr{
        display: flex;
        justify-content: center;
    }
    .modalDeposit .content .ButtonCopy button:hover{
        background: #e9a631;
    }
    .modalDeposit .content .ButtonCopy button{
        padding: 0.3rem;
        transition: all 0.2s;
        background: #ffc158;
        border-radius: 5px;
        color: #263043;
        font-weight: bold;
        font-size: 120%;
        width: 100%;
    }
    .modalDeposit .content .ButtonCopy{
        width: 100%;
    }
    .modalDeposit .content .qrCopia .code{
        word-wrap: break-word;
    }
    .modalDeposit .content .qrCopia .code{
        padding: 0.35rem;
        background: #263043;
        border-left: 4px solid #ffffff5b;
        border-radius: 6px;
        font-weight: 200;
        flex-wrap: wrap;
    }
    .modalDeposit .content .qrCopia{
        width: 100%;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .modalDeposit .content{
        background: #1d2634;
        top: 50%;
        position: absolute;
        padding: 0.4rem;
        width: 85%;
        border-radius: 10px;
        left: 50%;
        transform: translate(-50%,-50%);
        opacity: 0;
        transition: opacity 0.6s ease;
    }
    .modalDeposit{
        position: fixed;
        top: 0;
        left: 0 ;
        width: 100%;
        height: 100%;
        background: rgba(30, 30, 31, 0.61);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.1s ease, visibility 0.1s ease;
    }

    .modalDeposit-show {
        opacity: 1;
        visibility: visible;
    }

    .modalDeposit-show .content {
        opacity: 1;
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
                    <h1>SACAR</h1>
                </div>
                <div class="formDeposit">
                    <form id="withdrawForm" action="../php/process_withdrawal.php" method="POST">
                    <input type="hidden" name="_token" value="xD45WODdZ96KZqK8cLWavJjl9gtfKvsv0F6Bum9m" autocomplete="off">            <label for="telefone">
                Digite o valor do seu Saque:
                <input id="valor_saque" name="valor_saque" value="30" step="0.1" type="number">
            </label>
            <div style="margin-top: 1rem; display:flex; flex-direction:column">
                <span class="styleDp">Saque mínimo : <span style="color: #ffc158">R$30,00</span></span>
                <span class="styleDp">Saque máximo : <span style="color: #ffc158">R$1000,00</span></span>
            </div>
            <div>
                <label for="">
                    Escolha a sua chave Pix
                    <div class="methodsPayments">
                        <div>
                            <input type="radio" name="pix" id="" value="phoneNumber">
                            <span>Celular</span>
                        </div>
                        <div>
                            <input type="radio" name="pix" id="" value="document">
                            <span>CPF/CNPJ</span>
                        </div>
                        <div>
                            <input type="radio" name="pix" id="" value="email">
                            <span>E-mail</span>
                        </div>
                        <div>
                            <input type="radio" name="pix" id="" value="randomKey">
                            <span>Aleatória</span>
                        </div>
                    </div>
                </label>
            </div>
            <label for="telefone">
                Digite a sua Chave Pix:
                <input id="telefone" name="pix_chave" value="" step="1" type="number">
            </label>
            <div class="buttonDeposit">
                <button>SACAR</button>
            </div>
        </form>
        <div class="imagePix">
            <img src="../images/pixmetho.webp" alt="">
        </div>
        <div class="bg-InfomationsDeposit">
            <p>As solicitações de saque são atendidas o momento em que o saque é solicitado, porém podem levar de 1 segundo até 12 horas para serem enviados para a sua conta.</p>
        </div>
        </div>
        </main>
        <!-- End Main -->
    </div>

    <script>
  document.getElementById('withdrawForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var valorSaque = parseFloat(document.getElementById('valor_saque').value);
    if (isNaN(valorSaque) || valorSaque < 100.00) {
        alert('O valor mínimo para saque é R$100,00.');
        return;
    }

    var formData = $(this).serialize();
    $.ajax({
        type: 'POST',
        url: '../php/process_withdrawal.php',
        data: formData,
        success: function(response) {
            var responseData = JSON.parse(response);
            if (responseData.status === 'success') {
                alert(responseData.message);
                location.reload();
            } else {
                alert(responseData.message);
            }
        },
        error: function(xhr, status, error) {
            console.log(error);
            alert('Erro ao processar a solicitação de saque. Tente novamente.');
        }
    });
});


        function openSidebar() {
            document.getElementById('sidebar').classList.add('sidebar-responsive');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('sidebar-responsive');
        }

        document.getElementById('openModal').addEventListener('click', function() {
            document.getElementById('modal').classList.add('show');
        });

        document.getElementById('fecharmodal').addEventListener('click', function() {
            document.getElementById('modal').classList.remove('show');
        });
    </script>
    <!-- Scripts -->
    <script src="../js/apexcharts.min.js"></script>
    <script src="../js/toastify-js.js"></script>
</body>
</html>
