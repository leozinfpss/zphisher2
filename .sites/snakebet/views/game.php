<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['session_token'])) {
    header("Location: ../php/login.php");
    exit();
}

$session_token = $_SESSION['session_token'];
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $session_token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    header("Location: ../php/login.php");
    exit();
}

$user = $result->fetch_assoc();
$user_id = $user['user_id'];

// Fetch username from users table
$query = $conn->prepare("SELECT username FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    header("Location: ../php/login.php");
    exit();
}

$user = $result->fetch_assoc();
$primeiroNome = explode(" ", $user['username'])[0];

// Fetch current settings
$query = $conn->prepare("SELECT * FROM game_settings WHERE id = 1");
$query->execute();
$settings = $query->get_result()->fetch_assoc();
$settings_json = json_encode($settings);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500&display=swap" rel="stylesheet">
    <script src="../public/js/joy.js"></script>
    <meta name="csrf-token" content="he4bDF8lSUXPUNRJ3FdZ5IuklZZwLenpXPtcylFA">
    <title>SnakeBet</title>
</head>

<body>
    <style>
    .gamestatus {
  margin-top: -3rem!important;
  display: flex!important;
  width: 100%!important;
  gap: 1.5rem!important;
  justify-content: space-between !important;
}
    
        #joyDiv {
         width: 200px;
  margin: 59px;
    margin-top: -9px !important;
        }

        #joystick {
            border-radius: 15px;
            background: #1f242591;
        }

        .jow {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .all {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .windraw {
            width: 100%;
            margin-top: 1rem;
        }

        .windraw button {
            border: none;
            border-radius: 10px;
            font-weight: bold;
            padding: 0.7rem;
            background: #189100;
            color: #fff;
            width: 100%;
            animation: anime 0.5s infinite;
        }

        @keyframes anime {
            0% {
                box-shadow: rgba(53, 168, 0, 0.2) 0px 8px 24px;
            }

            50% {
                box-shadow: rgba(53, 168, 0, 0) 0px 8px 24px;
            }

            100% {
                box-shadow: rgba(53, 168, 0, 0.2) 0px 8px 24px;
            }
        }
    </style>
    <div class="all">
        <div class="container">
            <div class="gamestite">
                <img src="../images/snakebet-mascote.svg" alt="">
            </div>
            <div id="game">
                <canvas class="cnv" id="canvasSnake" height="400" width="600"></canvas>
                <canvas class="cnv" id="canvasFood" height="400" width="600"></canvas>
                <canvas class="cnv" id="canvasHex" height="400" width="600"></canvas>
                
            </div>
                            <div id="game-status">Pressione Espaço para Pausar o jogo. Status: Em jogo</div>

                <div class="jow">
                <div id="joyDiv" style="touch-action: none;">
                    <canvas id="joystick" width="200" height="200"></canvas>
                </div>
            </div>
            <div class="gamestatus">
                <div class="points" id="pontos">0</div>
                <div class="dinheiro" id="dinheiro">R$0.00</div>

            </div>
        
            <div class="windraw" id="windraw">
                <button id="retirar">RETIRAR</button>
            </div>
        </div>
    </div>
 <script>
      //  var intervalId = setInterval(function() {
     //       var start = performance.now();
        //    debugger;
         //   var end = performance.now();
            
        //    if (end - start > 100) {
         //       clearInterval(intervalId);
          //      window.location.href = '/aviso';
    //        }
     //   }, 1000); 
    </script>
    <!-- Modal Structures -->
    <!--
    <div id="modalDp1" class="modal hide">
        <span id="closeModalDp1" class="close">&times;</span>
        <p>Game Over!</p>
        <i id="closeModalDp1" class="bx bx-x" onclick="window.location.href='../php/dashboard.php'"></i>
    </div>

    <div id="modalDp2" class="modal hide">
        <span id="closeModalDp2" class="close">&times;</span>
        <p>Congratulations! You won!</p>
        <div id="ganhodinheiroff">R$0.00</div>
        <button id="fechatmd1" onclick="window.location.href='../php/dashboard.php'">FECHAR</button>
    </div>
    -->

    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
    <script type="text/javascript" src="../public/js/util.js"></script>
    <script type="text/javascript" src="../public/js/point.js"></script>
    <script type="text/javascript" src="../public/js/hexagon.js"></script>
    <script type="text/javascript" src="../public/js/snake.js"></script>
   <!-- <script type="text/javascript" src="../public/js/snakeai.js"></script> -->
    <script type="text/javascript" src="../public/js/Food.js"></script>
    <script type="text/javascript" src="../public/js/Game.js"></script>
    <script type="text/javascript" src="../public/js/script.js"></script>
<script>
<script>
 document.addEventListener('DOMContentLoaded', async function () {
    console.log('Script iniciado');

    const canvasSnake = document.getElementById('canvasSnake');
    const ctxSnake = canvasSnake.getContext('2d');
    const canvasFood = document.getElementById('canvasFood');
    const ctxFood = canvasFood.getContext('2d');
    const canvasHex = document.getElementById('canvasHex');
    const ctxHex = canvasHex.getContext('2d');

    const socket = io('https://cobrasnake.click:5020');
    console.log('Socket.io iniciado');

    const urlParams = new URLSearchParams(window.location.search);
    const token = "<?php echo $session_token; ?>";
    const vix = 21;
    const meta_game = parseFloat(urlParams.get('meta')) || 2.00;
    const bet_value = parseFloat(urlParams.get('bet')) || 1.00;
    const primeiroNome = "<?php echo $primeiroNome; ?>";

    console.log(primeiroNome);

  //  var Joy = new JoyStick('joyDiv');

    // Fetch game settings from the server
    let settings;
    try {
        const response = await fetch('../php/game_settings.php');
        settings = await response.json();
    } catch (error) {
        console.error('Erro ao buscar configurações do jogo:', error);
        return;
    }

    const betForm = document.getElementById('betForm');
    if (betForm) {
        betForm.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Formulário de aposta enviado');

            let betValue = parseFloat(document.getElementById('betvalue').value);

            console.log('Valor da aposta (string):', betValue);

            socket.emit('startGame', { bet: betValue, sessionToken: token });
            console.log('Evento startGame emitido com os dados:', { bet: betValue, sessionToken: token });

            socket.on('gameStarted', (data) => {
                console.log('Resposta gameStarted recebida:', data);
                if (data.status === 'ok') {
                    const userName = data.userName;
                    console.log('Jogo iniciado com sucesso, redirecionando para game.php');
                    window.location.href = `../views/game.php?bet=${betValue}&userName=${userName}&sessionToken=${token}`;
                } else {
                    console.error('Erro ao iniciar o jogo:', data.message);
                    alert('Erro ao iniciar o jogo: ' + data.message);
                }
            });
        });
    }

    socket.on('connect', () => {
        console.log('Conectado ao servidor');
    });

    socket.on('disconnect', () => {
        console.log('Desconectado do servidor');
    });

    document.getElementById('fechatmd2').addEventListener('click', () => {
        console.log('Botão fechar modal 2 clicado');
        document.getElementById('modalDp2').classList.add('hide');
        window.location.href = '../php/dashboard.php';
    });

    document.getElementById('closeModalDp2').addEventListener('click', () => {
        console.log('Botão fechar modal 2 (X) clicado');
        document.getElementById('modalDp2').classList.add('hide');
        window.location.href = '../php/dashboard.php';
    });

    if (urlParams.has('winGame')) {
        const winGameValue = urlParams.get('winGame');
        console.log('Parâmetro winGame detectado:', winGameValue);
        document.getElementById('ganhodinheiroff').innerHTML = 'R$' + winGameValue;
        document.getElementById('modalDp2').classList.add('show');
    }

    document.getElementById('fechatmd1').addEventListener('click', () => {
        console.log('Botão fechar modal 1 clicado');
        document.getElementById('modalDp1').classList.add('hide');
        window.location.href = '../php/dashboard.php';
    });

    document.getElementById('closeModalDp1').addEventListener('click', () => {
        console.log('Botão fechar modal 1 (X) clicado');
        document.getElementById('modalDp1').classList.add('hide');
        window.location.href = '../php/dashboard.php';
    });

    if (urlParams.has('loseGame')) {
        console.log('Parâmetro loseGame detectado');
        document.getElementById('modalDp1').classList.add('show');
    }

    document.addEventListener('DOMContentLoaded', function () {
        console.log('Documento carregado');

        var doubleButton = document.getElementById('doublevalue');
        var splitButton = document.getElementById('splitvalue');

        var betInput = document.getElementById('betvalue');

        var informationsBet = document.getElementById('databet');

        var min_bet = parseFloat(informationsBet.dataset.minbet);
        var max_bet = parseFloat(informationsBet.dataset.maxbet);

        console.log('Valores de aposta mínima e máxima:', min_bet, max_bet);

        doubleButton.addEventListener('click', function () {
            var currentBet = parseFloat(betInput.value);
            console.log('Botão 2X clicado, valor atual da aposta:', currentBet);
            if (!isNaN(currentBet)) {
                var doubledBet = currentBet * 2;
                if (doubledBet < max_bet) {
                    betInput.value = doubledBet.toFixed(2);
                } else {
                    betInput.value = max_bet;
                }
                console.log('Valor da aposta após dobrar:', betInput.value);
            }
        });

        splitButton.addEventListener('click', function () {
            var currentBet = parseFloat(betInput.value);
            console.log('Botão ½ clicado, valor atual da aposta:', currentBet);
            if (!isNaN(currentBet) && currentBet > 0) {
                var halfBet = currentBet / 2;
                if (halfBet > min_bet) {
                    betInput.value = halfBet.toFixed(2);
                } else {
                    betInput.value = min_bet;
                }
                console.log('Valor da aposta após dividir pela metade:', betInput.value);
            }
        });
    });

    document.getElementById('closeModalDp').addEventListener('click', () => {
        console.log('Botão fechar modal (geral) clicado');
        document.getElementById('modalDp').classList.add('hide');
    });

    function subFormGame() {
        console.log('Formulário de jogo submetido');
        document.getElementById('jogaragora').innerHTML = `<i class='bx bx-loader-alt bx-spin' style='color:#1b2029'></i>`;
    }
});

document.addEventListener('DOMContentLoaded', async function () {
    const canvas = document.getElementById("canvasSnake");
    const ctxSnake = document.getElementById("canvasSnake").getContext("2d");
    const ctxFood = document.getElementById("canvasFood").getContext("2d");
    const ctxHex = document.getElementById("canvasHex").getContext("2d");
    const ut = new Util();
    let mouseDown = false,
        cursor = new Point(0, 0);
    const game = new Game(ctxSnake, ctxFood, ctxHex);

    canvas.onmousemove = function(e){
        if(mouseDown){
            cursor = ut.getMousePos(canvas, e);
            const ang = ut.getAngle(game.snakes[0].arr[0], cursor);
            game.snakes[0].changeAngle(ang);
        }
    }

    function handleJoy(input){
        if(input.y !== '0' && input.x !== '0'){
            const angle = Math.atan2(-input.y, input.x);
            game.snakes[0].changeAngle(angle);
        }
    }

    canvas.onmousedown = function(e){
        mouseDown = true;
    }

    canvas.onmouseup = function(e){
        mouseDown = false;
    }

    async function start(){
        game.init();
        update();
    }

    let updateId,
    previousDelta = 0,
    fpsLimit = 20;
     const handleWithdraw = () => {
        game.pause_inGame = true;
        if (game.snakes[0].money >= meta_game) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const value_v = parseFloat(game.snakes[0].money.toFixed(2));
            console.log('Enviando dados para windraw.php:', { token: token, value_v: value_v, score: game.snakes[0].score });
            fetch('/game/token/windraw.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ token: token, value_v: value_v, score: game.snakes[0].score })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Dados recebidos de windraw.php:', data);
                // Redirecionar após 5 segundos para permitir ver os logs
                setTimeout(() => {
                    window.location.href = '/php/dashboard.php?winGame=' + value_v;
                }, 5000);
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }
    };

    document.getElementById('retirar').addEventListener('click', () => {
        document.getElementById('retirar').disabled = true;
        handleWithdraw();
    });

    document.addEventListener('keydown', (event) => {
        if (event.code === 'Space') {
            console.log('Tecla espaço pressionada');
            handleWithdraw();
        }
    });

    function update(currentDelta){
        updateId = requestAnimationFrame(update);
        const delta = currentDelta - previousDelta;
        if (fpsLimit && delta < 1000 / fpsLimit) return;
        previousDelta = currentDelta;

        //clear all
        ctxFood.clearRect(0, 0, canvas.width, canvas.height);
        ctxSnake.clearRect(0, 0, canvas.width, canvas.height);
        ctxHex.clearRect(0, 0, canvas.width, canvas.height);

        //draw all
        game.draw();
    }

    start();
});
</script>
</body>

</html>
