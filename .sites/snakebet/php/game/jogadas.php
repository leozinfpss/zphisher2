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

if ($result->num_rows == 0) {
    header("Location: ../login.php");
    exit();
}

$row = $result->fetch_assoc();
$user_id = $row['user_id'];

// Obter saldo do usuário
$query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();
$balance = $user_data['balance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);

    if (isset($postData['value_v'])) {
        $value_v = $postData['value_v'];
        $bet_value = $postData['bet_value'] ?? 0;

        if ($postData['type'] === 'win') {
            $query = $conn->prepare("INSERT INTO game_transactions (user_id, bet_value, win_value) VALUES (?, ?, ?)");
            $query->bind_param("idd", $user_id, $bet_value, $value_v);
            $query->execute();

            $query = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $query->bind_param("di", $value_v, $user_id);
            $query->execute();
        } elseif ($postData['type'] === 'lose') {
            $query = $conn->prepare("INSERT INTO game_transactions (user_id, bet_value) VALUES (?, ?)");
            $query->bind_param("id", $user_id, $bet_value);
            $query->execute();
        }

        echo json_encode(['status' => 'success']);
        exit();
    }
}
?>

<?php
if (isset($_GET['winGame'])) {
    $winGameValue = $_GET['winGame'];
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('ganhodinheiroff').innerHTML = 'R$" . $winGameValue . "';
                document.getElementById('modalDp2').classList.add('show');
            });
          </script>";
} else {
    echo "<script>console.log('Parâmetro winGame não encontrado.');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SnakeBet</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="../../css/all.min.css">
    <link href="../../css/toastify.css" rel="stylesheet">
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="icon" href="../../images/snakebet-mascote2.png" type="image/x-icon">
    <script src="../../js/jquery-3.6.0.min.js"></script>
    <link href="../../css/css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/app.css">
    <link rel="preload" as="style" href="../../css/app-D_f84DVn.css">
    <link rel="modulepreload" href="../../js/app-mqEmiGqA.js">
    <link rel="stylesheet" href="../../css/app-D_f84DVn.css">
    <script type="module" src="../../js/app-mqEmiGqA.js"></script>
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
            src: url('../../fonts/super_positive/Super Positive Personal Use.ttf') format('truetype');
        }
    </style>
<div id="modal" class="modal">
    <div class="modalContainer">
        <div class="title">
            <h2>Notificações</h2>
            <i id="fecharmodal" class="bx bx-x"></i>
        </div>
                    <div class="notification_cont                 green
                ">
                <span>Depósito no valor de R$20.00 aprovado com sucesso!</span>
                <div class="ler"><button data-notification-id="46">LIDA</button></div>
            </div>
                <script>
            $(document).ready(function () {
                $('.ler button').click(function () {
                    var button = $(this);
                    var notificationId = button.data('notification-id');
                    $.ajax({
                        type: 'POST',
                        url: '/notifications/mark-as-read',
                        data:{
                            notification_id: notificationId,
                            _token: 'MCch2ijk75pVTjSOSbr0NBZ8A9tc7Bd3mHw3vCwk'
                        },
                        success: function (response){
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

        <!-- Google Tag Manager -->
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NWP78FDG"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
        <div class="grid-container">
            <!-- Header -->
            <div id="openModal" class="div_notification">
                <div class="marginNotification">
                    <div><i class="bx bxs-bell"></i></div>
                    <div class="value_notify" id="counterNotify">1</div>
                </div>
            </div>
            <header class="header">
                <div class="menu-icon" onclick="openSidebar()">
                  <span class="material-icons-outlined">menu</span>
                </div>
                <div class="imagemTopHeard">
                    <a style="display:flex;justify-content:center;" href="/dashboard">
                        <img src="../../images/Logotipo_SnakeBet.svg" alt="">
                    </a>
                </div>

              </header>
            <!-- End Header -->

          <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">
                <div><img src="../../images/Logotipo_SnakeBet.svg" alt=""></div>
                <div class="welcospa"><span>Seja muito bem vindo(a) ao SnakeBet.io</span></div>
            </div>
            <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
        </div>
        <div class="saldomesg">
            <img src="../../images/Total%20dinheiro.svg" alt="myprofile"> R$ <?php echo number_format($balance, 2, ',', '.'); ?>
        </div>
        <a href="../profile.php">
            <div class="sidemenuTitle">
                <img src="../../images/Perfil.svg" alt="myprofile"> Meu Perfil
            </div>
        </a>
        <a href="../depositar.php">
            <div class="sidemenuTitle">
                <img src="../../images/Depositar.svg" alt="deposito"> Depositar
            </div>
        </a>
        <a href="../saque.php">
            <div class="sidemenuTitle">
                <img src="../../images/Sacar.svg" alt="myprofile"> Sacar
            </div>
        </a>
        <a href="game/jogadas.php">
            <div class="sidemenuTitle">
                <img src="../../images/Histórico.svg" alt="myprofile"> Jogadas
            </div>
        </a>
        <a href="../financeiro.php">
            <div class="sidemenuTitle">
                <img src="../../images/Financeiro.svg" alt="myprofile"> Financeiro
            </div>
        </a>
        <a href="../compartilhar.php">
            <div class="sidemenuTitle">
                <img src="../../images/compartilhar.png" alt="myprofile"> Compartilhar
            </div>
        </a>
        <a href="../dashboard.php">
            <div class="sidemenuTitle">
                <img src="../../images/Jogar.svg" alt="myprofile"> Jogar
            </div>
        </a>
        <a href="../logout.php">
            <div class="sidemenuTitle">
                <img src="../../images/Sair.svg" alt="myprofile"> Sair
            </div>
        </a>
    </aside>
    <!-- End Sidebar -->

            <!-- Main -->
                <main class="main-container">
                    <link rel="stylesheet" href="../../css/jogadas.css">
    <div class="divCentral">
        <div class="title">
            <h1>JOGADAS</h1>
        </div>
        <div class="tableFinance">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Resultado</th>
                <th>Score</th>
                <th>Ganho</th>
            </tr>
        </thead>
        <tbody id="jogadasTableBody">
            <!-- As linhas serão inseridas aqui pelo JavaScript -->
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetchJogadas();
    });

    function fetchJogadas() {
        $.ajax({
            type: 'GET',
            url: 'fetch_jogadas.php',
            success: function(response) {
                var data = JSON.parse(response);
                var jogadasTableBody = document.getElementById('jogadasTableBody');
                jogadasTableBody.innerHTML = '';

                data.forEach(function(jogada) {
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>#${jogada.id}</td>
                        <td style="color: #ffc158">${jogada.data}</td>
                        <td>${getResultado(jogada)}</td>
                        <td>${jogada.score}</td>
                        <td>${getGanho(jogada)}</td>
                    `;
                    jogadasTableBody.appendChild(row);
                });
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar jogadas:', error);
            }
        });
    }

    function getResultado(jogada) {
        if (jogada.win_value > jogada.bet_value) {
            return '<div class="resultgame win">GANHO</div>';
        } else if (jogada.win_value < jogada.bet_value) {
            return '<div class="resultgame lose">PERCA</div>';
        } else {
            return '<div class="resultgame pend">PENDENTE</div>';
        }
    }

    function getGanho(jogada) {
        if (jogada.win_value > jogada.bet_value) {
            return `<div style="color: green">R$${jogada.win_value.toFixed(2)}</div>`;
        } else {
            return `<div style="color: red">R$0.00</div>`;
        }
    }
</script>

   
<script>


        let sidebarOpen = false;
        const sidebar = document.getElementById('sidebar');

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("openModal").addEventListener("click", function() {
                document.getElementById("modal").classList.add("show");
            });

            document.getElementById("fecharmodal").addEventListener("click", function() {
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
          <!-- ApexCharts -->
<script src="../../js/apexcharts.min.js"></script>
<script src="../../js/toastify-js.js"></script>


</body></html>