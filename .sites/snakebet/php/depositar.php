<?php
include 'db.php';
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    header("Location: ../login.php");
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $_SESSION['session_token']);
$query->execute();
$result = $query->get_result();
$session_data = $result->fetch_assoc();

if ($session_data) {
    $user_id = $session_data['user_id'];

    // Obter informações do usuário
    $query = $conn->prepare("SELECT name, cpf, email, balance FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user_data = $result->fetch_assoc();

    if ($user_data) {
        $nome = $user_data['name'];
        $cpf = $user_data['cpf'];
        $email = $user_data['email'];
        $balance = $user_data['balance'];
    } else {
        die("Erro: Usuário não encontrado.");
    }
} else {
    die("Erro: Sessão inválida.");
}
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
                <a style="display:flex;justify-content:center;" href="dashboard.php">
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
                <img src="../images/Total%20dinheiro.svg" alt="myprofile">  R$ <?php echo number_format($balance, 2, ',', '.'); ?>
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
            <link rel="stylesheet" href="../css/deposito.css">
            <script src="../js/jquery.min.js"></script>
            <script src="../js/jquery-3.6.0.min.js"></script>
            <script src="../js/jquery.mask.min.js"></script>
            <style>
                .modalDeposit25 .content .title h1 {
                    font-size: 1.7rem;
                }
                .modalDeposit25 .content .title {
                    padding: 0.5rem;
                }
                .modalDeposit25 .content .exit {
                    position: absolute;
                    right: 1rem;
                    font-size: 1.9rem;
                    cursor: pointer;
                }
                .modalDeposit25 .content .corpoQr img {
                    width: 10rem;
                    background: #fff;
                    border-radius: 10px;
                }
                .modalDeposit25 .content .corpoQr {
                    display: flex;
                    justify-content: center;
                }
                .modalDeposit25 .content .ButtonCopy button:hover {
                    background: #e9a631;
                }
                .modalDeposit25 .content .ButtonCopy button {
                    padding: 0.3rem;
                    transition: all 0.2s;
                    background: #ffc158;
                    border-radius: 5px;
                    color: #263043;
                    font-weight: bold;
                    font-size: 120%;
                    width: 100%;
                }
                .modalDeposit25 .content .ButtonCopy {
                    width: 100%;
                }
                .modalDeposit25 .content .qrCopia .code {
                    word-wrap: break-word;
                }
                .modalDeposit25 .content .qrCopia .code {
                    padding: 0.35rem;
                    background: #263043;
                    border-left: 4px solid #ffffff5b;
                    border-radius: 6px;
                    font-weight: 200;
                    flex-wrap: wrap;
                }
                .modalDeposit25 .content .qrCopia {
                    width: 100%;
                    margin-top: 1rem;
                    margin-bottom: 1rem;
                }
                .modalDeposit25 .content {
                    background: #1d2634;
                    top: 50%;
                    position: absolute;
                    padding: 0.4rem;
                    width: 85%;
                    border-radius: 10px;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    opacity: 0;
                    transition: opacity 0.6s ease;
                }
                .modalDeposit25 {
                    position: fixed;
                    top: 0;
                    left: 0;
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
                .modalDeposit25 form {
                    width: 100%;
                }
                .modalDeposit25 input {
                    border-radius: 0.4rem;
                    background: #263043;
                }
                [type=text]:focus, input:where(:not([type])):focus, [type=email]:focus, [type=url]:focus, [type=password]:focus, [type=number]:focus, [type=date]:focus, [type=datetime-local]:focus, [type=month]:focus, [type=search]:focus, [type=tel]:focus, [type=time]:focus, [type=week]:focus, [multiple]:focus, textarea:focus, select:focus {
                    outline: 2px solid transparent;
                    outline-offset: 2px;
                    --tw-ring-inset: var(--tw-empty);
                    --tw-ring-offset-width: 0px;
                    --tw-ring-offset-color: #fff;
                    --tw-ring-color: #ffc158;
                    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
                    border-color: #ffc158;
                }
                input[type="password"]::-webkit-input-placeholder {
                    color: white !important;
                    width: 100%;
                }
                .buttonSaveBPT button {
                    padding: 0.7rem;
                    background: #ffc158;
                    border-radius: 10px;
                    color: #1d2634;
                    font-weight: bold;
                    width: 100%;
                }
                .buttonSaveBPT {
                    width: 100%;
                    margin-top: 1rem;
                }
            </style>
            <div class="divCentral">
                <div class="title">
                    <h1>DEPOSITAR</h1>
                </div>
                <div class="formDeposit">
                    <form id="mainFormU" method="POST" action="process_deposit.php" onsubmit="alterStyleButton(event)">
                        <input type="hidden" name="name" id="hiddenName">
                        <input type="hidden" name="cpf" id="hiddenCpf">
                        <label for="valor">
                            Digite o valor do seu Depósito:
                            <input id="valor" name="valor" value="30.00" step="1" type="number" min="30">
                        </label>
                        <div style="background:#ffc158;margin-top:1rem;padding:0.7rem;border-radius:5px;text-align:center">
                            <span style="color:#263043;font-weight: 800;">Ao depositar um valor maior que R$50,00 você receberá <b>5</b> Rodadas Grátis com multiplicação em dobro.</span>
                        </div>
                        <div style="margin-top: 1rem; display:flex; flex-direction:column">
                            <span class="styleDp">Depósito mínimo : <span style="color: #ffc158">R$20.00</span></span>
                            <span class="styleDp">Depósito máximo : <span style="color: #ffc158">R$10000.00</span></span>
                        </div>
                        <div class="buttonDeposit">
                            <button id="depositButton">DEPOSITAR</button>
                        </div>
                    </form>

                    <!-- Modal for personal information -->
                    <div class="modalDeposit25">
                        <div class="content">
                            <div class="exit" id="exitModal22"><i class="bx bx-x"></i></div>
                            <div class="title"><h1>Informações pessoais...</h1></div>
                            <form id="formularioTesteComplit" method="POST" action="process_deposit.php" onsubmit="alterStyleButton(event)">
                                <div style="width:100%">
                                    <input id="hiddenValor" name="valor" value="20.00" step="1" type="hidden">
                                    <input style="width:100%" id="nome" name="name" type="text" placeholder="Nome Completo" required>
                                </div>
                                <div style="width:100%; margin-top:1rem">
                                    <input style="width:100%" id="cpf" name="cpf" type="text" placeholder="CPF" required maxlength="14">
                                </div>
                                <div class="buttonSaveBPT">
                                    <button id="inportumeCOntinuar">CONTINUAR</button>
                                </div>
                                <div style="display:flex;align-items:center;padding:0.4rem;justify-content:center;gap:0.3rem;margin-top:1rem">
                                    <i class="bx bxs-lock-alt"></i><span style="font-size:0.9rem">Ambiente seguro Informações criptografadas!</span>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('#cpf').mask('000.000.000-00');
                                    });
                                    document.getElementById('exitModal22').addEventListener('click', () => {
                                        document.querySelector('.modalDeposit25').classList.remove('modalDeposit-show');
                                    });

                                    $('#formularioTesteComplit').submit(function(e) {
                                        e.preventDefault();
                                        // Capture name, cpf, and valor and set them to hidden fields in the main form
                                        $('#hiddenName').val($('#nome').val());
                                        $('#hiddenCpf').val($('#cpf').val());
                                        $('#hiddenValor').val($('#valor').val());
                                        // Submit the main form
                                        $('#mainFormU').submit();
                                    });
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- End Main -->
    </div>

    <!-- Modal for QR Code -->
    <div class="modalDeposit25" id="qrCodeModal">
        <div class="content">
            <div class="exit" id="exitModalQrCode"><i class="bx bx-x"></i></div>
            <div class="title"><h1>QR Code para Pagamento</h1></div>
            <div class="corpoQr">
                <img id="qrCodeImage" src="" alt="QR Code">
            </div>
            <div class="qrCopia">
                <div class="code" id="paymentCodeText"></div>
            </div>
            <div class="ButtonCopy">
                <button onclick="copyQrCode()">Copiar Código de Pagamento</button>
            </div>
        </div>
    </div>
    <script src="check_payment.js"></script>

    <script>
        document.getElementById('exitModalQrCode').addEventListener('click', () => {
            document.getElementById('qrCodeModal').classList.remove('modalDeposit-show');
        });

        function copyQrCode() {
            var paymentCode = document.getElementById("paymentCodeText").textContent;
            var input = document.createElement("input");
            document.body.appendChild(input);
            input.value = paymentCode;
            input.select();
            document.execCommand("copy");
            document.body.removeChild(input);
            alert("Código de pagamento copiado para a área de transferência!");
        }
    </script>

    <script>
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

    <script>
        var paymentCheckInterval;

        function alterStyleButton(event) {
            event.preventDefault();
            var openModal = true;

            if (openModal) {
                var mainForm = document.getElementById('mainFormU');
                var modaVp = document.querySelector('.modalDeposit25');
                modaVp.classList.add('modalDeposit-show');
                $(document).ready(function() {
                    $('#formularioTesteComplit').submit(function(e) {
                        e.preventDefault();
                        document.getElementById('inportumeCOntinuar').innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;
                        document.getElementById('inportumeCOntinuar').disabled = true;

                        var formData = $(this).serialize();
                        $.ajax({
                            type: 'POST',
                            url: 'process_deposit.php',
                            data: formData,
                            success: function(response) {
                                console.log("Resposta recebida do process_deposit.php:", response); // Log da resposta recebida

                                try {
                                    var data = JSON.parse(response);
                                } catch (e) {
                                    console.error("Erro ao analisar a resposta JSON:", e);
                                    alert('Erro no servidor. Tente novamente.');
                                    document.getElementById('inportumeCOntinuar').innerHTML = 'CONTINUAR';
                                    document.getElementById('inportumeCOntinuar').disabled = false;
                                    return;
                                }

                                if (data.status === 'success') {
                                    modaVp.classList.remove('modalDeposit-show');
                                    document.getElementById('qrCodeImage').src = 'data:image/png;base64,' + data.qr_code;
                                    document.getElementById('paymentCodeText').textContent = data.paymentCode; // Define o paymentCode
                                    document.getElementById('qrCodeModal').classList.add('modalDeposit-show');

                                    // Inicia a checagem do status do pagamento a cada 5 segundos
                                    paymentCheckInterval = setInterval(function() {
                                        checkPaymentStatus(data.transaction_id);
                                    }, 5000);
                                } else {
                                    alert('Erro ao gerar QR Code: ' + data.message);
                                    document.getElementById('inportumeCOntinuar').innerHTML = 'CONTINUAR';
                                    document.getElementById('inportumeCOntinuar').disabled = false;
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Erro ao processar depósito:", error); // Log de erro
                                document.getElementById('inportumeCOntinuar').innerHTML = 'CONTINUAR';
                                document.getElementById('inportumeCOntinuar').disabled = false;
                            }
                        });
                    });
                });
            } else {
                event.currentTarget.submit();
                document.getElementById('depositButton').innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;
                document.getElementById('depositButton').disabled = true;
            }
        }
    </script>

    <!-- Scripts -->
    <script src="../js/apexcharts.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>
