<?php
include 'php/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['telefone'];
    $password = $_POST['password'];
    $aff_id = $_POST['aff_id'];

    // Verificar se o usuário já existe
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "User already exists";
    } else {
        // Inserir novo usuário
        $sql = "INSERT INTO users (username, email, phone, password, affiliate_id) VALUES (?, ?, ?, SHA2(?, 256), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $email, $phone, $password, $aff_id);
        if ($stmt->execute()) {
            echo "User registered successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="/images/snakebet-mascote2.png" type="image/x-icon">
    <link href="css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/all.min.css">
    <title>Register</title>
</head>
<body>
    <style>
        *{
            padding: 0;
            margin: 0;
        }
        :root {
            --verde: #4aeba1;
            --amarelo: #ffc158;
            --branco: #ffffff;
            --azul: #1d3f4b;
            --rodapebg: #9a6634;
            --background-imagem-p1: url('/images/background-p1.jpg');
            --background-imagem-p2: url('/images/bg-logincd.jpg');
        }
        body{
            background-image: var(--background-imagem-p2);
        }
        @font-face {
            font-family: 'SuperPositive';
            src: url('/fonts/super_positive/Super Positive Personal Use.ttf') format('truetype');
        }
        .loginContainer{
            position: fixed;
            right: 0;
            font-family: 'SuperPositive', sans-serif;
            top: 20%;
            text-align: center;
            transform: translate(-50%)
        }
        .LgCotainer{
            background: #112830;
            color: #fff;
            border: 3px solid #4aeba1;
            border-radius: 10px;
            padding: 1.4rem;
        }
        .LgCotainer form{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .LgCotainer form button{
            background: #4aeba1;
            color: #112830;
            width: 100%;
            cursor: pointer;
            margin-top: 1rem;
            height: 2.5rem;
            font-family: 'SuperPositive', sans-serif;
            font-size: 140%;
            border: none;
            border-radius: 10px;
        }
        .LgCotainer form input{
            height: 2.5rem;
            border: none;
            margin-top: 1rem;
            color: #fff;
            font-weight: 800;
            border-radius: 8px;
            background: #1d3f4b;
            padding-left: 1rem;
            width: 100%;
        }
        .LgCotainer form input:focus{
            outline: none;
            border: 1px solid #4aeba1;
        }
        .LgCotainer form input::placeholder{
            color: #ffffff;
            font-weight: 400;
            font-family: 'SuperPositive', sans-serif;
        }
        .optionalts a{
            color: #4aeba1;
            text-decoration: none;
        }
        .optionalts{
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            color: #4aeba1;
        }
        @media screen and (max-width:826px){
            .loginContainer{
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
                width: 90%
            }
            .logo img{
                width: 50%;
            }
        }
    </style>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.mask.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

        .sidemenu{
            position: fixed;
            background: var(--verde);
            left: 50%;
            top: 2rem;
            padding: 0.2rem;
            z-index: 999;
            align-items: center;
            display: flex;
            justify-content: center;
            gap: 2rem;
            border-radius: 10px;
            transform: translate(-50%)
        }
        .sidemenu .communA{
            font-family: 'SuperPositive', sans-serif;
            color: #000;
            text-decoration: none;
        }
        .sidemenu .communB:hover{
            background: #f57f34;
            color: #fff;
        }
        .sidemenu .communB{
            text-decoration: none;
            border-radius: 5px;
            padding: 0.5rem;
            border-radius: 10px;
            background-color: var(--amarelo);
            transition: all 0.3s;
            font-family: 'SuperPositive', sans-serif;
            color: #000
        }
        .sidemenu .communB.rige{
            margin-right: 2rem;
        }
        .sidemenu .communA.rapm{
            margin-left: 2rem;
        }
        @media screen and (max-width:726px){
            .sidemenu{
                width: 85%;
            }
            .sidemenu .communA:nth-child(2){
                display: none
            }
            .sidemenu .communA:nth-child(1){
                display: none
            }
        }
    </style>
      <div class="sidemenu">
        <a class="communA rapm" href="">HOME</a>
        <a class="communA" href="">COMO JOGAR?</a>
        <a class="communA" href="/register_handler.php">CADASTRAR</a>
        <a class="communB rige" href="https://cobrasnake.click/">LOGIN</a>
    </div>
    <div class="loginContainer">
        <div class="logo"><img src="images/Logotipo_SnakeBet.svg" alt=""></div>
        <div class="LgCotainer">
            <h1>CADASTRE-SE</h1>
            <form action="php/register_handler.php" method="POST" onsubmit="onloading()">
                  <input type="hidden" name="ref" value="<?php echo isset($_GET['ref']) ? $_GET['ref'] : ''; ?>">
             
                <input type="text" name="username" id="" placeholder="NOME DE USUARIO" required="">
                <input type="text" name="email" id="" placeholder="E-MAIL" required="">
                <input type="text" id="phone" name="telefone" placeholder="TELEFONE" required="" maxlength="16">
                <input type="password" name="password" id="password" placeholder="SENHA" required="">
                <input type="hidden" name="aff_id" id="aff_id">
                <button id="submitButton">CADASTRAR</button>
            </form>
            <script>
                window.addEventListener('DOMContentLoaded', function() {
                    var urlParams = new URLSearchParams(window.location.search);
                    var aff_id = urlParams.get('ref');
                    if (aff_id) {
                        document.getElementById('aff_id').value = aff_id;
                    }
                });

                function onloading(){
                    var button = document.getElementById("submitButton");
                    button.disabled = true; // desativa o botão
                    button.innerHTML = `<i id="loaderIcon" class='bx bx-loader-alt bx-spin' style='color:#112830;'></i>`;
                }
            </script>
            <script>
                $(document).ready(function() {
                    $('#phone').mask('(00) 0 0000-0000');
                });
            </script>
        </div>
    </div>
</body>
</html>
