var canvas = document.getElementById("canvasSnake");
var ctxSnake = canvas.getContext("2d");
var ctxFood = document.getElementById("canvasFood").getContext("2d");
var ctxHex = document.getElementById("canvasHex").getContext("2d");
var ut = new Util();
var mouseDown = false,
    cursor = new Point(0, 0);
var game = new Game(ctxSnake, ctxFood, ctxHex);

canvas.onmousemove = function(e){
    if(mouseDown){
        cursor = ut.getMousePos(canvas, e);
        var ang = ut.getAngle(game.snakes[0].arr[0], cursor);
        game.snakes[0].changeAngle(ang);
    }
}

function handleJoy(input){
    if(input.y !== '0' && input.x !== '0'){
        var angle = Math.atan2(-input.y, input.x);
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
    const token = await getToken();
    game.init();
    update();
}

async function getToken() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('sessionToken');
    const response = await fetch('/game/token/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ token: token })
    });

    return response.json();
}

var updateId,
    previousDelta = 0,
    fpsLimit = 20;

document.getElementById('windraw').addEventListener('click', () => {
    document.getElementById('retirar').disabled = true;
    game.pause_inGame = true;
    if(game.snakes[0].money >= meta_game){
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const value_v = parseFloat(game.snakes[0].money.toFixed(2));
        return fetch('/game/token/windraw', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ token: token, value_v:value_v, score:game.snakes[0].score})
        })
        .then(response => {
            window.location.href = '/dashboard?winGame=' + value_v;
            return response.json();
        })
        .then(data => {
            return data;
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    }
});

function update(currentDelta){
    updateId = requestAnimationFrame(update);
    var delta = currentDelta - previousDelta;
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
