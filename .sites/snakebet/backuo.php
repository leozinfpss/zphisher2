<html lang="en"><head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" type="text/css" href="https://snakebet.io/game/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&amp;display=swap" rel="stylesheet">
        <meta name="csrf-token" content="xD45WODdZ96KZqK8cLWavJjl9gtfKvsv0F6Bum9m">
        <script type="text/javascript" src="https://snakebet.io/game/js/joy.js"></script>
    <title>SnakeBet</title>
</head>
<body>
    <style>
         #joyDiv
        {
            width: 200px;
            height: 200px;
            margin: 50px;

        }
        #joystick
        {
            border-radius: 15px;
            background: #1f242591
        }
        .jow{
            width: 100%;
            display: flex;
            justify-content: center
        }
        .all{
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>
    <div class="all">
    <script>
        var intervalId = setInterval(function() {
            var start = performance.now();
            debugger;
            var end = performance.now();
            
            if (end - start > 100) {
                clearInterval(intervalId);
                window.location.href = '/aviso';
            }
        }, 1000); 
    </script>
        <div class="container">
            <div class="gamestite">
                <img src="https://snakebet.io/assets/images/snakebet-mascote.svg" alt="">
            </div>
            <div id="game">
                <canvas class="cnv" id="canvasSnake" height="400" width="600"></canvas>
                <canvas class="cnv" id="canvasFood" height="400" width="600"></canvas>
                <canvas class="cnv" id="canvasHex" height="400" width="600"></canvas>
            </div>
            <div class="gamestatus">
                <div class="points" id="pontos">0</div>
                <div class="dinheiro" id="dinheiro">R$0,00</div>
            </div>
            <style>
                .windraw{
                    width: 100%;
                    margin-top: 1rem;
                }
                .windraw button{
                    border: none;
                    border-radius: 10px;
                    font-weight: bold;
                    padding: 0.7rem;
                    background: #189100;
                    color: #fff;
                    width: 100%;
                    animation: anime 0.5s infinite;
                }
                @keyframes anime{
                    0%{
                        box-shadow: rgba(53, 168, 0, 0.2) 0px 8px 24px;
                    }
                    50%{
                        box-shadow: rgba(53, 168, 0, 0) 0px 8px 24px;

                    }
                    100%{
                        box-shadow: rgba(53, 168, 0, 0.2) 0px 8px 24px;

                    }
                }
            </style>
            <div class="windraw" id="windraw" style="display: none;">
                <button id="retirar">RETIRAR</button>
            </div>
            <div class="jow">
                <div id="joyDiv" style="touch-action: none;"><canvas id="joystick" width="200" height="200"></canvas></div>
            </div>

        </div>
    </div>

    <script type="text/javascript">
        const token = "WI7LO3AaSEERsHOjJeyKBOLkOhJ7sscI";
        const vix = "40"
        const meta_game = "1.00" * 2;
        const bet_value = "1.00"
        var Joy = new JoyStick('joyDiv');
        let nomeCompleto = "Felipe";
        let partesNome = nomeCompleto.split(" ");
        let primeiroNome = partesNome[0];
        console.log(primeiroNome);

    </script>
<script type="text/javascript" src="https://snakebet.io/game/js/util.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/point.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/hexagon.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/snake.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/snakeai.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/Food.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/Game.js"></script>
<script type="text/javascript" src="https://snakebet.io/game/js/script.js"></script>





</body></html>