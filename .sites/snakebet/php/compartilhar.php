<?php
session_start();
include '../php/db.php';
include '../php/functions.php';

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

$user_id = $row['user_id'] ?? null;
if ($user_id === null) {
    die('Erro: ID do usuário não encontrado.');
}

// Obter saldo do usuário e dados de indicação
$query = $conn->prepare("SELECT balance, referral_amount FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user_data = $result->fetch_assoc();

$balance = $user_data['balance'] ?? 0.00;
$referral_amount = $user_data['referral_amount'] ?? 0.00;

// Obter dados de usuários convidados
$query = $conn->prepare("SELECT u.username, r.status, r.referral_amount, r.final_referral_amount FROM referrals r JOIN users u ON r.referred_user_id = u.id WHERE r.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$referrals = [];
while ($row = $result->fetch_assoc()) {
    $referrals[] = $row;
}

// Obter a comissão mínima
$userCommission = getUserCommission($user_id);
$minCommission = $userCommission ?? getGlobalSetting('commission_value') ?? 0.00;

// Calcular o valor final da comissão
$finalCommission = array_sum(array_column($referrals, 'final_referral_amount')) ?? 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartilhar - SnakeBet</title>
    <link rel="stylesheet" href="../css/line.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="../css/toastify.css" rel="stylesheet">
    <link href="../css/boxicons.min.css" rel="stylesheet">
    <link href="../css/compartilhar.css" rel="stylesheet">

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
            background: rgba(0, 0, 0, 0.5);
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal.show {
            visibility: visible;
            opacity: 1;
        }
        .modalContainer {
            background: #263043;
            border-radius: 10px;
            padding: 1.5rem;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        .exit {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .buttonSaveBPT button {
            padding: 0.7rem;
            background: #ffc158;
            border-radius: 10px;
            color: #1d2634;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            border: none;
        }
    </style>
    <div id="withdrawalModal" class="modal">
        <div class="modalContainer">
            <div class="exit" id="exitModalWithdrawal"><i class="bx bx-x"></i></div>
            <div class="title">
                <h1 style="font-size: 165%;">Solicitar Saque da Comissão</h1>
            </div>
            <form id="withdrawalForm" method="POST" action="processar_saqueaf.php" onsubmit="submitWithdrawalForm(event)">
                <div style="width:100%">
                    <span>Valor Total da Comissão Disponível: R$ <?php echo number_format((float)$finalCommission, 2, ',', '.'); ?></span>
                    <label for="withdrawal_amount">Valor do Saque:</label>
                    <input id="withdrawal_amount" name="withdrawal_amount" type="number" step="0.01" min="0.01" max="<?php echo $finalCommission; ?>" required>
                </div>
                <div style="width:100%; margin-top:1rem">
                    <input style="width:100%" id="pixKey" name="pix_key" type="text" placeholder="Chave Pix" required>
                </div>
                <div class="buttonSaveBPT">
                    <button id="confirmWithdrawalButton" style="gap: 10px;/*! display: flex; */margin-top: 18px;">CONFIRMAR SAQUE</button>
                </div>
                <div style="display:flex;align-items:center;padding:0.4rem;justify-content:center;gap:0.3rem;margin-top:1rem">
                    <i class="bx bxs-lock-alt"></i><span style="font-size:0.9rem">Ambiente seguro Informações criptografadas!</span>
                </div>
            </form>
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
                <img src="../images/Total%20dinheiro.svg" alt="myprofile"> R$ <?php echo number_format((float)$balance, 2, ',', '.'); ?>
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
                    <h1>COMPARTILHAR</h1>
                </div>
                <div class="informations">
                    <div class="text">Link de Compartilhamento</div>
                    <div class="inputText">
                        <input type="text" value="https://cobrasnake.click/register.php?ref=<?php echo $user_id; ?>" id="inputField" disabled>
                        <button class="buttonCopiar" id="copybutton">COPIAR</button>
                        <script>
                            document.getElementById('copybutton').addEventListener('click', () => {
                                const inputField = document.getElementById('inputField');
                                const tempTextArea = document.createElement('textarea');
                                tempTextArea.value = inputField.value;
                                document.body.appendChild(tempTextArea);
                                tempTextArea.select();
                                tempTextArea.setSelectionRange(0, 99999);
                                document.execCommand('copy');
                                document.body.removeChild(tempTextArea);
                                document.getElementById('copybutton').innerHTML = 'COPIADO';
                                document.getElementById('copybutton').classList.add('copyBus')

                                setTimeout(() => {
                                    document.getElementById('copybutton').innerHTML = 'COPIAR';
                                    document.getElementById('copybutton').classList.remove('copyBus')
                                }, 3000);
                            });
                        </script>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 1rem;">
                    <span style="font-size: 1.1rem; text-align: center; font-weight: 800;">
                        O valor mínimo de depósito para comissões é R$
                        <?php
                        $userMinDeposit = getUserCommission($user_id);
                        $globalMinDeposit = getGlobalSetting('min_deposit');
                        echo number_format((float)($userMinDeposit ?? $globalMinDeposit), 2, ',', '.');
                        ?>.
                    </span>
                </div>
                <div class="stat">
                    <div class="convidados green">
                        <div class="Title">
                            Total de Comissão Disponível
                        </div>
                        <div class="number">
                            R$ <?php echo number_format((float)$finalCommission, 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 1rem;">
                    <span style="font-size: 1.1rem; text-align: center; font-weight: 800;">Os valores serão debitados no saldo real da sua conta do jogo.</span>
                </div>
                <?php if (empty($referrals)): ?>
                    <div class="notUsers" style="margin-top:1rem">
                        Parece que você não tem nenhum usuário convidado!
                    </div>
                <?php endif; ?>
                <div class="buttonDeposit">
                    <button onclick="openWithdrawalModal()">SOLICITAR SAQUE DA COMISSÃO</button>
                </div>
            </div>
        </main>
        <!-- End Main -->
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("openModal").addEventListener("click", function() {
                document.getElementById("modal").classList.add("show");
            });

            document.getElementById("fecharmodal").addEventListener("click", function() {
                document.getElementById("modal").classList.remove("show");
            });

            document.getElementById('exitModalWithdrawal').addEventListener('click', function() {
                document.getElementById('withdrawalModal').classList.remove('show');
            });
        });

        function openSidebar() {
            let sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('sidebar-responsive');
        }

        function openWithdrawalModal() {
            document.getElementById('withdrawalModal').classList.add('show');
        }

        function submitWithdrawalForm(event) {
            event.preventDefault();
            let confirmButton = document.getElementById('confirmWithdrawalButton');
            confirmButton.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;
            confirmButton.disabled = true;

            let formData = $('#withdrawalForm').serialize();
            $.ajax({
                type: 'POST',
                url: 'processar_saqueaf.php',
                data: formData,
                success: function(response) {
                    console.log("Resposta recebida do processar_saqueaf.php:", response);
                    confirmButton.innerHTML = 'CONFIRMAR SAQUE';
                    confirmButton.disabled = false;
                    alert('Saque solicitado com sucesso!');
                    document.getElementById('withdrawalModal').classList.remove('show');
                },
                error: function(xhr, status, error) {
                    console.error("Erro ao processar saque:", error);
                    confirmButton.innerHTML = 'CONFIRMAR SAQUE';
                    confirmButton.disabled = false;
                }
            });
        }
    </script>
    <!-- Scripts -->
    <script src="../js/apexcharts.min.js"></script>
    <script src="../js/toastify-js.js"></script>
</body>
</html>
