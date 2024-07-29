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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SnakeBet</title>
    <link rel="stylesheet" href="../css/line.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="../css/toastify.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="../css/icon" rel="stylesheet">
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="icon" href="../images/snakebet-mascote2.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <script src="../js/jquery-3.6.0.min.js"></script>
    <link href="../css/css" rel="stylesheet">
    <link rel="stylesheet" href="../css/app.css">
    <!-- Scripts -->
    <link rel="preload" as="style" href="../css/app-D_f84DVn.css">
    <link rel="modulepreload" href="../js/app-mqEmiGqA.js">
    <link rel="stylesheet" href="../css/app-D_f84DVn.css">
    <script type="module" src="../js/app-mqEmiGqA.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #111;
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
        @font-face {
            font-family: 'SuperPositive';
            src: url('../fonts/super_positive/Super Positive Personal Use.ttf') format('truetype');
        }
    </style>
</head>
<body class="font-sans antialiased">
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
                        url: '/notifications/mark-as-read',
                        data: {
                            notification_id: notificationId,
                            _token: '0MVo7EnhoY0qKrX6kLyg6PIGxkp35AVFicfJZfaE'
                        },
                        success: function (response) {
                            var counterValue = parseInt(document.getElementById('counterNotify').innerText);
                            if (counterValue > 0) {
                                counterValue--;
                                document.getElementById('counterNotify').innerText = counterValue;
                            }
                            button.closest('.notification_cont').remove();
                        },
                        error: function (xhr, status, error) {
                        }
                    })
                })
            })
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
        <link rel="stylesheet" href="../css/jogaview.css">
        <div id="modalDp" class="modalDp">
            <div class="contentModalDp">
                <div class="slif">
                    <h1>Saldo insuficiente!</h1>
                    <i id="closeModalDp" class="bx bx-x"></i>
                </div>
                <div>
                    <span>Atualmente seu saldo é de R$ <span style="color: red; font-weight:800"><?php echo number_format($balance, 2, ',', '.'); ?></span>, para continuar você precisa realizar um depósito!<br style="margin-top: 0.5rem">Clique no botão abaixo para ir até a pagina de depósitos.</span>
                </div>
                <div class="bntdep">
                    <a href="depositar.php">
                        <button>DEPOSITAR</button>
                    </a>
                </div>
            </div>
        </div>
        <div id="modalDp2" class="modalDp">
            <div class="contentModalDp">
                <div class="slif">
                    <h1>Jogada Ganha!</h1>
                    <i id="closeModalDp2" class="bx bx-x"></i>
                </div>
                <div>
                    <span>Parabéns <span style="color: green; font-weight:800">VOCÊ GANHOUUU</span> Continue assim!!.</span>
                </div>
                <div class="GANHO">
                    <span id="ganhodinheiroff">R$0.00</span>
                </div>
                <div class="bntdep">
                    <button style="color: #fff;background-color: #6b7280" id="fechatmd2">FECHAR</button>
                </div>
            </div>
        </div>
        <div id="modalDp1" class="modalDp">
            <div class="contentModalDp">
                <div class="slif">
                    <h1>Jogada Perdida!</h1>
                    <i id="closeModalDp1" class="bx bx-x"></i>
                </div>
                <div>
                    <span>Infelizmente essa <span style="color: red; font-weight:800">VOCÊ PERDEU</span> Tente novamente!!.</span>
                </div>
                <div class="bntdep">
                    <button id="fechatmd1">FECHAR</button>
                </div>
            </div>
        </div>
        <div class="divCentral">
            <div id="databet" data-minbet="1.00" data-maxbet="500.00">
            </div>
            <div class="logotipo">
                <img src="../images/Logotipo_SnakeBet.svg" alt="">
            </div>
            <div class="gamesaldo">
                <span class="sp1"><i class="bx bxs-wallet"></i> Carteira: </span><span class="sp2">R$<?php echo number_format($balance, 2, ',', '.'); ?></span>
            </div>
            <form id="betForm" action="javascript:void(0);" class="formGaming">
                <div class="inptvalue">
                    <input id="betvalue" min="1.00" max="500.00" name="bet" type="number" placeholder="Valor da Aposta" required="">
                    <button type="button" id="splitvalue">½</button>
                    <button type="button" id="doublevalue">2X</button>
                </div>
                <div class="jogarContain">
                    <button id="jogaragora" type="submit">JOGAR AGORA</button>
                </div>
            </form>
            <div class="spanObs">
                <p>Clique no botão acima pra começar o jogo!</p>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script>
        <script>
            const socket = io('http://localhost:3000');
            
            document.getElementById('betForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const betValue = document.getElementById('betvalue').value;

                socket.emit('startGame', { bet: betValue });

                socket.on('gameStarted', (data) => {
                    if (data.status === 'ok') {
                        // Redirecionar para a página do jogo Node.js
                        window.location.href = `http://localhost:3000/game.html?bet=${betValue}`;
                    } else {
                        // Tratar erro
                        alert('Erro ao iniciar o jogo.');
                    }
                });
            });

            socket.on('connect', () => {
                console.log('Connected to the server');
            });

            socket.on('disconnect', () => {
                console.log('Disconnected from the server');
            });

            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById('fechatmd2').addEventListener('click', () => {
                document.getElementById('modalDp2').classList.add('hide');
                window.location.href = '/dashboard';
            });
            document.getElementById('closeModalDp2').addEventListener('click', () => {
                document.getElementById('modalDp2').classList.add('hide');
                window.location.href = '/dashboard';
            });
            if (urlParams.has('winGame')) {
                const winGameValue = urlParams.get('winGame');
                document.getElementById('ganhodinheiroff').innerHTML = 'R$' + winGameValue;
                document.getElementById('modalDp2').classList.add('show');
            }

            document.getElementById('fechatmd1').addEventListener('click', () => {
                document.getElementById('modalDp1').classList.add('hide');
                window.location.href = '/dashboard';
            });
            document.getElementById('closeModalDp1').addEventListener('click', () => {
                document.getElementById('modalDp1').classList.add('hide');
                window.location.href = '/dashboard';
            });
            if (urlParams.has('loseGame')) {
                document.getElementById('modalDp1').classList.add('show');
            }
            document.addEventListener('DOMContentLoaded', function () {
                var doubleButton = document.getElementById('doublevalue');
                var splitButton = document.getElementById('splitvalue');

                var betInput = document.getElementById('betvalue');

                var informationsBet = document.getElementById('databet');

                var min_bet = parseFloat(informationsBet.dataset.minbet);
                var max_bet = parseFloat(informationsBet.dataset.maxbet);

                doubleButton.addEventListener('click', function () {
                    var currentBet = parseFloat(betInput.value);
                    if (!isNaN(currentBet)) {

                        var doubledBet = currentBet * 2;
                        if (doubledBet < max_bet) {
                            betInput.value = doubledBet.toFixed(2);
                        } else {
                            betInput.value = max_bet;
                        }

                    }
                });

                splitButton.addEventListener('click', function () {
                    var currentBet = parseFloat(betInput.value);


                    if (!isNaN(currentBet) && currentBet > 0) {
                        var halfBet = currentBet / 2;
                        if (halfBet > min_bet) {
                            betInput.value = halfBet.toFixed(2);
                        } else {
                            betInput.value = min_bet;
                        }


                    }
                });
            });
            document.getElementById('closeModalDp').addEventListener('click', () => {
                document.getElementById('modalDp').classList.add('hide')
            });
            function subFormGame() {
                document.getElementById('jogaragora').innerHTML = `<i class='bx bx-loader-alt bx-spin' style='color:#1b2029'  ></i>`
            }
        </script>
    </main>
    <!-- End Main -->
</div>

<script>
    let sidebarOpen = false;
    const sidebar = document.getElementById('sidebar');

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("openModal").addEventListener("click", function () {
            document.getElementById("modal").classList.add("show");
        });

        document.getElementById("fecharmodal").addEventListener("click", function () {
            document.getElementById("modal").classList.remove("show");
        });
    });

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

<svg id="SvgjsSvg1001" width="2" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev" style="overflow: hidden; top: -100%; left: -100%; position: absolute; opacity: 0;">
    <defs id="SvgjsDefs1002"></defs>
    <polyline id="SvgjsPolyline1003" points="0,0"></polyline>
    <path id="SvgjsPath1004" d="M0 0 "></path>
</svg>
</body>
</html>
