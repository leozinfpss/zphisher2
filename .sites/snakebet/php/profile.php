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

// Obter os dados do usuário
$query_user_data = $conn->prepare("SELECT username, email, phone, cpf, name FROM users WHERE id = ?");
$query_user_data->bind_param("i", $user_id);
$query_user_data->execute();
$result_user_data = $query_user_data->get_result();
$user_data = $result_user_data->fetch_assoc();

// Obter saldo do usuário
$query_balance = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$query_balance->bind_param("i", $user_id);
$query_balance->execute();
$result_balance = $query_balance->get_result();
$user_balance = $result_balance->fetch_assoc();
$balance = $user_balance['balance'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnakeBet</title>
    <link rel="stylesheet" href="../css/line.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">    
    <link rel="stylesheet" href="../css/boxicons.min.css">
    <link rel="stylesheet" href="../css/app.css">
    <link rel="stylesheet" href="../css/app-D_f84DVn.css">
    <link rel="icon" href="../images/snakebet-mascote2.png" type="image/x-icon">
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script src="../js/jquery.mask.min.js"></script>
</head>
<body class="font-sans antialiased">
    <style>
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
                            url: '/notifications/mark-as-read',
                            data: {
                                notification_id: notificationId
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
            <style>

@font-face {
    font-family: 'SuperPositive';
    src: url('../fonts/super_positive/Super Positive Personal Use.ttf') format('truetype');
}

                .divCentral {
                    padding: 2.5rem;
                }
                .divCentral .title h1 {
                    font-size: 500%;
                    color: #ffc158;
                    font-family: 'SuperPositive', sans-serif;
                }
                .alterSenhaTitle {
                    font-size: 200%;
                    color: #ffc158;
                    font-family: 'SuperPositive', sans-serif;
                }
                .divCentral .completarDado {
                    background: #ffc158;
                    padding: 10px;
                    border-radius: 0.2rem;
                    color: #1d3f4b;
                    font-size: 110%;
                    font-weight: 900;
                }
                .formUser {
                    display: flex;
                    justify-content: center;
                    margin-top: 0.4rem;
                    gap: 6rem;
                    flex-wrap: wrap;
                }
                .formUser label {
                    display: flex;
                    margin-top: 0.9rem;
                    flex-direction: column;
                }
                .formUser form {
                    flex-grow: 1;
                }
                .formUser input:disabled {
                    color: #9d9d9d
                }
                .formUser input {
                    border-radius: 0.4rem;
                    background: #263043;
                }
                @media screen and (max-width:726px) {
                    .divCentral .title h1 {
                        font-size: 300%;
                    }
                }
                .saveButton {
                    margin-top: 1rem;
                }
                .saveButton button:hover {
                    background: #e9a631;
                }
                .saveButton button {
                    padding: 0.5rem;
                    font-size: 120%;
                    transition: all 0.2s;
                    font-family: 'SuperPositive', sans-serif;
                    background: #ffc158;
                    color: #263043;
                    border-radius: 0.5rem;
                    width: 100%;
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
                }
            </style>
            <div class="divCentral">
                <div class="title">
                    <h1>MEU PERFIL</h1>
                </div>
                <div class="completarDado">
                    <p>Complete os dados do seu perfil e ganhe quatro rodadas grátis!</p>
                </div>
                <div class="formUser">
                    <form action="update_profile.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <label for="username">
                            *Username:
                            <input name="username" id="username" value="<?php echo $user_data['username']; ?>" type="text" disabled>
                        </label>
                        <label for="email">
                            *E-mail:
                            <input id="email" name="email" value="<?php echo $user_data['email']; ?>" type="text">
                        </label>
                        <label for="telefone">
                            *Telefone:
                            <input id="telefone" name="telefone" value="<?php echo $user_data['phone']; ?>" type="text">
                        </label>
                        <label for="cpf">
                            *CPF:
                            <input id="cpf" name="cpf" value="<?php echo $user_data['cpf']; ?>" type="text" disabled>
                        </label>
                        <label for="name">
                            *Nome:
                            <input id="name" name="name" value="<?php echo $user_data['name']; ?>" type="text">
                        </label>
                        <script>
                            $(document).ready(function() {
                                $('#telefone').mask('(00) 0 0000-0000');
                            });
                        </script>
                        <div class="saveButton">
                            <button type="submit">SALVAR</button>
                        </div>
                    </form>
                    <form action="update_password.php" onsubmit="onsubMupdated()" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <h1 class="alterSenhaTitle">ALTERAR SENHA</h1>
                        <label for="senha_att">
                            Senha Atual:
                            <input id="senha_att" name="current_password" type="password" placeholder="********" required>
                        </label>
                        <label for="new_senha">
                            Nova Senha:
                            <input id="new_senha" name="password" type="password" required>
                        </label>
                        <label for="repita_senha">
                            Repita a Senha:
                            <input id="repita_senha" type="password" name="password_confirmation" required>
                        </label>
                        <div class="saveButton">
                            <button id="loaderUpadtedpass">ALTERAR SENHA</button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                function onsubMupdated(){
                    var button = document.getElementById("loaderUpadtedpass");
                    button.disabled = true;
                    button.innerHTML = `
                    <i id="loaderIcon" class='bx bx-loader-alt bx-spin' style='color:#112830;'></i>
                    `
                }
            </script>
        </main>
        <!-- End Main -->

    </div>
    <script>
        var intervalId = setInterval(function() {
            var start = performance.now();
            var end = performance.now();
            
            if (end - start > 100) {
                clearInterval(intervalId);
                window.location.href = '/aviso';
            }
        }, 1000); 
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
    <script src="../js/apexcharts.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>
</html>
