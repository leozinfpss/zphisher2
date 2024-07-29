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
    const token = urlParams.get('sessionToken');
    const meta_game = parseFloat(urlParams.get('meta')) || 2.00;
    const bet_value = parseFloat(urlParams.get('bet')) || 1.00;
    let nomeCompleto = urlParams.get('userName') || 'Felipe';
    let partesNome = nomeCompleto.split(" ");
    let primeiroNome = partesNome[0];

    console.log(primeiroNome);

    var Joy = new JoyStick('joyDiv', {}, handleJoy);

    // Fetch game settings from the server
    let settings;
    try {
        const response = await fetch('../php/game_settings.php');
        settings = await response.json();
    } catch (error) {
        console.error('Erro ao buscar configurações do jogo:', error);
        return;
    }

    // Fetch user details to check for influencer status
    let isInfluencer = false;
    try {
        const response = await fetch('../php/check_influencer.php?token=' + token);
        const userDetails = await response.json();
        isInfluencer = userDetails.isInfluencer;
    } catch (error) {
        console.error('Erro ao buscar detalhes do usuário:', error);
    }

    const adjustedSettings = {
        ...settings,
        foodInitialCount: isInfluencer ? settings.influencerFoodInitialCount : settings.foodInitialCount,
        enemyInitialCount: isInfluencer ? settings.influencerEnemyInitialCount : settings.enemyInitialCount,
        foodValueMultiplier: isInfluencer ? settings.influencerFoodValueMultiplier : settings.foodValueMultiplier,
    };

    class Util {
        static getMousePos(canvas, evt) {
            var rect = canvas.getBoundingClientRect();
            var x = evt.clientX - rect.left;
            var y = evt.clientY - rect.top;
            return new Point(x, y);
        }

        static random(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        static randomColor() {
            var colors = ["#C0392B", "#E74C3C", "#9B59B6", "#8E44AD", "#2980B9", "#3498DB", "#17A589", "#138D75", "#229954", "#28B463", "#D4AC0D", "#D68910", "#CA6F1E", "#BA4A00"];
            return colors[this.random(0, colors.length - 1)];
        }

        static randomName() {
            var names = ['Pablo', 'Henrique', 'Ana', 'Gabriel', 'Manu', 'Jessica', 'Cadu', 'Claudio', 'Joao', 'Carlos', 'Rosiane'];
            return names[this.random(0, names.length - 1)];
        }

        static getDistance(i, f) {
            return Math.abs(Math.sqrt(Math.pow((f.x - i.x), 2) + Math.pow((f.y - i.y), 2)));
        }

        static getAngle(p1, p2) {
            return (Math.atan2(p2.y - p1.y, p2.x - p1.x));
        }

        static cirCollission(x1, y1, r1, x2, y2, r2) {
            return (this.getDistance(new Point(x1, y1), new Point(x2, y2)) < (r1 + r2));
        }

        static drawHexagon(ctx, size, x, y) {
            var angle = 60;
            ctx.beginPath();
            ctx.moveTo(x + size * Math.cos(0), y + size * Math.sin(0));
            for (var i = 1; i <= 6; i++) {
                ctx.lineTo(x + size * Math.cos(i * 2 * Math.PI / 6), y + size * Math.sin(i * 2 * Math.PI / 6));
            }
            ctx.fillStyle = "black";
            ctx.fill();
        }

        static color(hex, lum) {
            hex = String(hex).replace(/[^0-9a-f]/gi, '');
            if (hex.length < 6) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            lum = lum || 0;

            var rgb = "#", c, i;
            for (i = 0; i < 3; i++) {
                c = parseInt(hex.substr(i * 2, 2), 16);
                c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
                rgb += ("00" + c).substr(c.length);
            }

            return rgb;
        }

        static rotate(p, c, angle) {
            var s = Math.sin(angle);
            var cos = Math.cos(angle);

            // translate point back to origin
            p.x -= c.x;
            p.y -= c.y;

            // rotate point
            var xnew = p.x * cos - p.y * s;
            var ynew = p.x * s + p.y * cos;

            // translate point back
            p.x = xnew + c.x;
            p.y = ynew + c.y;
            return p;
        }
    }

    class Point {
        constructor(x, y) {
            this.x = x;
            this.y = y;
        }
    }

    class Snake {
        constructor(ctx, name, id) {
            this.ctx = ctx;
            this.name = name;
            this.id = id;
            this.score = 0;
            this.force = settings.force || 5;
            if (settings.superApelao) {
                this.force = 7; // Aumentar a velocidade da cobra principal no modo apelão
            }
            this.state = 0;
            this.money = 0.0;
            this.headType = Util.random(0, 2);
            this.pos = new Point(game.SCREEN_SIZE.x / 2, game.SCREEN_SIZE.y / 2);
            this.velocity = new Point(0, 0);
            this.angle = Util.random(0, Math.PI);
            this.length = settings.initialLength || 10;
            this.MAXSIZE = 12;
            this.size = settings.initialSize || 10;
            this.game_round = 0;
            this.exit_game = false;

            this.mainColor = Util.randomColor();
            this.midColor = Util.color(this.mainColor, 0.33);
            this.supportColor = Util.color(this.midColor, 0.33);

            this.arr = [];
            this.arr.push(new Point(game.SCREEN_SIZE.x / 2, game.SCREEN_SIZE.y / 2));
            for (var i = 1; i < this.length; i++) {
                this.arr.push(new Point(this.arr[i - 1].x, this.arr[i - 1].y));
            }
        }

        drawHeadTwoEyeBranch() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;

            var d = this.size * 1.9;
            var p1 = new Point(x + d * Math.cos(this.angle), y + d * Math.sin(this.angle));
            p1 = Util.rotate(p1, this.arr[0], Math.PI / 6);
            var p2 = Util.rotate(new Point(p1.x, p1.y), this.arr[0], -Math.PI / 3);

            this.ctx.fillStyle = this.mainColor;
            this.ctx.beginPath();
            this.ctx.arc(p1.x, p1.y, this.size / 2 + 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.beginPath();
            this.ctx.arc(p1.x, p1.y, this.size / 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "black";
            this.ctx.beginPath();
            this.ctx.arc(p1.x + Math.cos(this.angle), p1.y + Math.sin(this.angle), this.size / 4, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = this.mainColor;
            this.ctx.beginPath();
            this.ctx.arc(p2.x, p2.y, this.size / 2 + 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.beginPath();
            this.ctx.arc(p2.x, p2.y, this.size / 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "black";
            this.ctx.beginPath();
            this.ctx.arc(p2.x + Math.cos(this.angle), p2.y + Math.sin(this.angle), this.size / 4, 0, 2 * Math.PI);
            this.ctx.fill();

            var grd = this.ctx.createRadialGradient(x, y, 2, x + 4, y + 4, 10);
            grd.addColorStop(0, this.supportColor);
            grd.addColorStop(1, this.midColor);
            this.ctx.fillStyle = grd;
            this.ctx.beginPath();
            this.ctx.arc(x, y, this.size + 1, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.font = "10px Arial";
            this.ctx.fillText(this.name, x - 10, y - 10);
        }

        drawHeadTwoEye() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;

            this.ctx.fillStyle = this.color;
            this.ctx.beginPath();
            this.ctx.arc(x, y, this.size + 1, 0, 2 * Math.PI);
            this.ctx.fill();

            var d = this.size / 2;
            var p1 = new Point(x + d * Math.cos(this.angle), y + d * Math.sin(this.angle));
            p1 = Util.rotate(p1, this.arr[0], -20);

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.beginPath();
            this.ctx.arc(p1.x, p1.y, this.size / 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "black";
            this.ctx.beginPath();
            this.ctx.arc(p1.x + Math.cos(this.angle), p1.y + Math.sin(this.angle), this.size / 4, 0, 2 * Math.PI);
            this.ctx.fill();

            var p2 = Util.rotate(p1, this.arr[0], 40);
            this.ctx.fillStyle = "whitesmoke";
            this.ctx.beginPath();
            this.ctx.arc(p2.x, p2.y, this.size / 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "black";
            this.ctx.beginPath();
            this.ctx.arc(p2.x + Math.cos(this.angle), p2.y + Math.sin(this.angle), this.size / 4, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.font = "10px Arial";
            this.ctx.fillText(this.name, x - 5, y - 10);
        }

        drawHeadOneEye() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;

            this.ctx.fillStyle = this.color;
            this.ctx.beginPath();
            this.ctx.arc(x, y, this.size + 2, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.beginPath();
            this.ctx.arc(x, y, this.size, 0, 2 * Math.PI);
            this.ctx.fill();

            var d = 2;
            this.ctx.fillStyle = "black";
            this.ctx.beginPath();
            this.ctx.arc(x + d * Math.cos(this.angle), y + d * Math.sin(this.angle), this.size / 1.5, 0, 2 * Math.PI);
            this.ctx.fill();

            var d = 3;
            this.ctx.fillStyle = "white";
            this.ctx.beginPath();
            this.ctx.arc(x + d * Math.cos(this.angle), y + d * Math.sin(this.angle), this.size / 4, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = "whitesmoke";
            this.ctx.font = "10px Arial";
            this.ctx.fillText(this.name, x - 5, y - 10);
        }

        drawBody(x, y, i) {
            var grd = this.ctx.createRadialGradient(x, y, 2, x + 4, y + 4, 10);
            grd.addColorStop(0, this.supportColor);
            grd.addColorStop(1, this.midColor);

            var radius = this.size - (i * 0.01);
            if (radius < 0) radius = 1;

            this.ctx.beginPath();
            this.ctx.fillStyle = this.mainColor;
            this.ctx.arc(x, y, radius + 1, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.fillStyle = grd;
            this.ctx.beginPath();
            this.ctx.arc(x, y, radius, 0, 2 * Math.PI);
            this.ctx.fill();
        }

        move() {
            this.velocity.x = this.force * Math.cos(this.angle);
            this.velocity.y = this.force * Math.sin(this.angle);

            var d = this.size / 2;
            for (var i = this.length - 1; i >= 1; i--) {
                this.arr[i].x = this.arr[i - 1].x - d * Math.cos(this.angle);
                this.arr[i].y = this.arr[i - 1].y - d * Math.sin(this.angle);
                this.drawBody(this.arr[i].x, this.arr[i].y, i);
            }

            this.pos.x += this.velocity.x;
            this.pos.y += this.velocity.y;

            if (this.headType == 0) this.drawHeadOneEye();
            else if (this.headType == 1) this.drawHeadTwoEye();
            else if (this.headType == 2) this.drawHeadTwoEyeBranch();

            this.checkCollissionFood();
            this.checkCollissionSnake();
            this.checkBoundary();
            this.checkPaused();
        }

        checkPaused() {
            if (game.pause_inGame) {
                this.win();
            }
        }

        checkBoundary() {
            if (this.arr[0].x < game.world.x) {
                this.pos.x = game.world.x + this.size * 2;
                this.velocity.x *= -1;
                this.angle = Math.PI - this.angle;
            } else if (this.arr[0].x > game.world.x + game.WORLD_SIZE.x) {
                this.pos.x = game.world.x + game.WORLD_SIZE.x - this.size * 2;
                this.velocity.x *= -1;
                this.angle = Math.PI - this.angle;
            } else if (this.arr[0].y < game.world.y) {
                this.pos.y = game.world.y + this.size * 2;
                this.velocity.y *= -1;
                this.angle = Math.PI - this.angle;
            } else if (this.arr[0].y > game.world.y + game.WORLD_SIZE.y) {
                this.pos.y = game.world.y + game.WORLD_SIZE.y - this.size * 2;
                this.velocity.y *= -1;
                this.angle = Math.PI - this.angle;
            }
        }

        checkCollissionFood() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;
            for (var i = 0; i < game.foods.length; i++) {
                if (Util.cirCollission(x, y, this.size + 3, game.foods[i].pos.x, game.foods[i].pos.y, game.foods[i].size)) {
                    game.foods[i].die();
                    this.addScore();
                    this.incSize();
                }
            }
        }

        checkCollissionSnake() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;
            for (var i = 0; i < game.snakes.length; i++) {
                var s = game.snakes[i];
                if (s !== this) {
                    for (var j = 0; j < game.snakes[i].arr.length; j += 2) {
                        if (Util.cirCollission(x, y, this.size, s.arr[j].x, s.arr[j].y, s.size)) {
                            if (settings.aggressiveEnemyPercentage && Math.random() < settings.aggressiveEnemyPercentage) {
                                this.die();
                            }
                        }
                    }
                }
            }
        }

        addScore() {
            this.length++;
            this.score++;

            if (this.id === 0) {
                var randomDecimal = (Math.random() * (0.50 - 0.10)) + 0.10;
                randomDecimal = Number(randomDecimal.toFixed(2));
                var valorPago = bet_value * randomDecimal;
                if (isInfluencer) {
                    valorPago *= adjustedSettings.influencerFoodValueMultiplier;
                }
                this.money += valorPago;

                this.game_round += 1;

                var dinheiroFormatado = this.money.toFixed(2);
                if (this.money > meta_game) {
                    document.getElementById('windraw').style.display = 'flex';
                } else {
                    document.getElementById('windraw').style.display = 'none';
                }

                document.getElementById('dinheiro').innerHTML = "R$" + dinheiroFormatado;
                document.getElementById('pontos').innerHTML = this.score;

                if (this.money < 0) {
                    console.log('Red');
                    this.die();
                }
            }
            this.arr.push(new Point(-100, -100));
        }

        die() {
            this.state = 1;
            var index = game.snakes.indexOf(this);
            if (index !== -1) {
                game.snakes.splice(index, 1);
                window.location.href = '/php/dashboard.php?loseGame';
            }
        }

        incSize() {
            if (this.length % 30 == 0) this.size++;
            if (this.size > this.MAXSIZE) this.size = this.MAXSIZE;
        }

        changeAngle(angle) {
            this.angle = angle;
        }

        win() {
            this.state = 1;
            for (var i = 0; i < this.arr.length; i += 3) {
                game.foods.push(new Food(game.ctxFood, this.arr[i].x, this.arr[i].y));
            }

            var index = game.snakes.indexOf(this);
            game.snakes.splice(index, 1);

            const value_v = parseFloat(this.money.toFixed(2));
            const sessionToken = new URLSearchParams(window.location.search).get('sessionToken');

            console.log('Enviando dados para update_balance.php:', {
                sessionToken: sessionToken,
                value_v: value_v,
                score: this.score
            });

            fetch('/update_balance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ sessionToken: sessionToken, value_v: value_v, score: this.score })
            })
            .then(response => {
                console.log('Resposta recebida do servidor:', response);
                if (response.ok) {
                    console.log('Redirecionando para /php/dashboard.php?winGame=' + value_v);
                    window.location.href = '/php/dashboard.php?winGame=' + value_v;
                } else {
                    throw new Error('Erro na resposta do servidor: ' + response.statusText);
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar saldo no banco de dados:', error);
            });
        }

        exitGame() {
            if (!this.exit_game) {
                var index = game.snakes.indexOf(this);
                if (index !== -1) {
                    game.snakes.splice(index, 1);
                    window.location.href = '/php/dashboard.php?loseGame';
                }
                this.exit_game = true;
            }
        }
    }

    class SnakeAi extends Snake {
        constructor(ctx, name, id) {
            super(ctx, name, id);

            this.force = 2;
            this.pos = new Point(Util.random(-6000, 1800), Util.random(-300, 900));
            this.length = Util.random(10, 50);

            this.arr = [];
            this.arr.push(this.pos);
            for (var i = 1; i < this.length; i++) this.arr.push(new Point(this.arr[i - 1].x, this.arr[i - 1].y));

            this.initAiMovement();
        }

        initAiMovement() {
            var self = this;
            var count = 0;
            var units = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1];
            var unit = 0.5;
            this.timer = setInterval(function () {
                if (count > 20) {
                    self.angle += 0;
                    unit = units[Util.random(0, units.length - 1)];
                } else if (count > 10) self.angle += unit;
                else if (count > 0) self.angle -= unit;

                count++;
                count %= 30;
            }, 100);
        }

        move(player) {
            this.velocity.x = this.force * Math.cos(this.angle);
            this.velocity.y = this.force * Math.sin(this.angle);
            for (var i = this.length - 1; i >= 1; i--) {
                this.arr[i].x = this.arr[i - 1].x;
                this.arr[i].y = this.arr[i - 1].y;

                this.arr[i].x -= player.velocity.x;
                this.arr[i].y -= player.velocity.y;

                this.drawBody(this.arr[i].x, this.arr[i].y, i);
            }

            this.arr[0].x += this.velocity.x;
            this.arr[0].y += this.velocity.y;

            this.pos.x += this.velocity.x;
            this.pos.y += this.velocity.y;

            this.arr[0].x -= player.velocity.x;
            this.arr[0].y -= player.velocity.y;

            if (this.headType == 0) this.drawHeadOneEye();
            else if (this.headType == 1) this.drawHeadTwoEye();
            else if (this.headType == 2) this.drawHeadTwoEyeBranch();

            this.ctx.beginPath();
            this.ctx.globalAlpha = 0.5;
            this.ctx.fillStyle = "white";
            if (this.inDanger) this.ctx.fillStyle = "red";
            this.ctx.arc(this.pos.x, this.pos.y, this.shield, 0, 2 * Math.PI);
            this.ctx.fill();
            this.ctx.globalAlpha = 1;

            super.checkCollissionFood();
            this.checkCollissionSnake();
            this.checkBoundary();

            if (settings.superApelao) {
                this.angle = Util.getAngle(this.pos, player.pos); // Adjust AI snakes to follow the player
            }
        }

        checkBoundary() {
            if (this.arr[0].x < game.world.x) this.arr[0].x = game.world.x + game.WORLD_SIZE.x;
            else if (this.arr[0].x > game.world.x + game.WORLD_SIZE.x) this.arr[0].x = game.world.x;
            if (this.arr[0].y < game.world.y) this.arr[0].y = game.world.y + game.WORLD_SIZE.y;
            else if (this.arr[0].y > game.world.y + game.WORLD_SIZE.y) this.arr[0].y = game.world.y;
        }

        die() {
            // Não morrem
        }

        checkCollissionSnake() {
            var x = this.arr[0].x;
            var y = this.arr[0].y;
            for (var i = 0; i < game.snakes.length; i++) {
                var s = game.snakes[i];
                if (s !== this) {
                    for (var j = 0; j < s.arr.length; j++) {
                        if (Util.cirCollission(x, y, this.size, s.arr[j].x, s.arr[j].y, s.size)) {
                            if (settings.aggressiveEnemyPercentage && Math.random() < settings.aggressiveEnemyPercentage) {
                                s.die();
                            }
                        }
                    }
                }
            }
        }
    }

    class Food {
        constructor(ctx, x, y) {
            this.ctx = ctx;
            this.pos = new Point(x, y);
            this.sizeMin = 4;
            this.sizeMax = 8;
            this.mainColor = Util.randomColor();
            this.supportColor = Util.color(this.mainColor, 0.5);

            this.size = Util.random(this.sizeMin, this.sizeMax);
        }

        draw(player) {
            this.pos.x -= player.velocity.x;
            this.pos.y -= player.velocity.y;

            this.ctx.globalAlpha = 0.5;
            this.ctx.fillStyle = this.mainColor;
            this.ctx.beginPath();
            this.ctx.arc(parseInt(this.pos.x), parseInt(this.pos.y), this.size, 0, 2 * Math.PI);
            this.ctx.fill();

            this.ctx.globalAlpha = 1;
            this.ctx.fillStyle = this.supportColor;
            this.ctx.beginPath();
            this.ctx.arc(parseInt(this.pos.x), parseInt(this.pos.y), this.size / 2, 0, 2 * Math.PI);
            this.ctx.fill();
        }

        die() {
            this.state = 1;
            var index = game.foods.indexOf(this);
            game.foods.splice(index, 1);
        }
    }

    class Game {
        constructor(ctxSnake, ctxFood, ctxHex) {
            this.ctxSnake = ctxSnake;
            this.ctxFood = ctxFood;
            this.ctxHex = ctxHex;
            this.WORLD_SIZE = new Point(adjustedSettings.worldSize || 4000, 2000);
            this.SCREEN_SIZE = new Point(800, 400);
            this.world = new Point(-1200, -600);
            this.snakes = [];
            this.foods = [];
            this.bricks = [];
            this.meta = 0;
            this.modalVisible = true;
            this.pause_inGame = false;
        }

        init() {
            this.snakes[0] = new Snake(this.ctxSnake, primeiroNome, 0);
            for (var i = 0; i < (adjustedSettings.enemyInitialCount || 10); i++) this.addSnake(Util.randomName(), 100);
            this.generateFoods(adjustedSettings.foodInitialCount || 750);
        }

        draw() {
            this.drawWorld();
            this.drawBricks();
            if (this.snakes[0].state === 0) this.snakes[0].move();
            for (var i = 1; i < this.snakes.length; i++)
                if (this.snakes[i].state === 0) this.snakes[i].move(this.snakes[0]);
            for (var i = 0; i < this.foods.length; i++) this.foods[i].draw(this.snakes[0]);

            this.drawScoreMoney();
            this.drawScore();
            this.drawMap();
        }

        drawWorld() {
            this.ctxHex.fillStyle = "white";
            this.ctxHex.fillRect(this.world.x - 2, this.world.y - 2, this.WORLD_SIZE.x + 4, this.WORLD_SIZE.y + 4);

            this.ctxHex.fillStyle = "#17202A";
            this.ctxHex.fillRect(this.world.x, this.world.y, this.WORLD_SIZE.x, this.WORLD_SIZE.y);

            this.world.x -= this.snakes[0].velocity.x;
            this.world.y -= this.snakes[0].velocity.y;
        }

        drawScoreMoney() {
            var start = new Point(canvasSnake.width - 180, 30);
            this.ctxSnake.fillStyle = "white";
            this.ctxSnake.font = "bold 24px Arial";
            this.ctxSnake.fillText("META: R$" + meta_game, start.x, start.y);
        }

        drawScore() {
            var start = new Point(20, 20);
            for (var i = 0; i < this.snakes.length; i++) {
                this.ctxSnake.fillStyle = this.snakes[i].mainColor;
                this.ctxSnake.font = "bold 10px Arial";
                this.ctxSnake.fillText(this.snakes[i].name + ":" + this.snakes[i].score,
                    start.x - 5, start.y + i * 15);
            }
        }

        drawMap() {
            this.ctxSnake.globalAlpha = 0.5;

            var mapSize = new Point(100, 50);
            var start = new Point(20, this.SCREEN_SIZE.y - mapSize.y - 10);
            this.ctxSnake.fillStyle = "white";
            this.ctxSnake.fillRect(start.x, start.y, mapSize.x, mapSize.y);
            this.ctxSnake.fill();

            this.ctxSnake.globalAlpha = 1;

            for (var i = 0; i < this.snakes.length; i++) {
                var playerInMap = new Point(start.x + (mapSize.x / this.WORLD_SIZE.x) * this.snakes[i].pos.x,
                    start.y + (mapSize.y / this.WORLD_SIZE.y) * this.snakes[i].pos.y);

                this.ctxSnake.fillStyle = this.snakes[i].mainColor;
                this.ctxSnake.beginPath();
                this.ctxSnake.arc(start.x + playerInMap.x, playerInMap.y + 10, 2, 0, 2 * Math.PI);
                this.ctxSnake.fill();
            }
        }

        drawBricks() {
            var size = 50;
            for (var i = 0; i < this.bricks.length; i++) {
                Util.drawHexagon(this.ctxHex, 22, this.bricks[i].x + size / 2, this.bricks[i].y + size / 2);
                this.bricks[i].x -= this.snakes[0].velocity.x;
                this.bricks[i].y -= this.snakes[0].velocity.y;

                if (this.bricks[i].x + size < 0) this.bricks[i].x = this.SCREEN_SIZE.x;
                else if (this.bricks[i].x > this.SCREEN_SIZE.x) this.bricks[i].x = -size;
                if (this.bricks[i].y + size < 0) this.bricks[i].y = this.SCREEN_SIZE.y;
                else if (this.bricks[i].y > this.SCREEN_SIZE.y) this.bricks[i].y = -size;
            }
        }

        addSnake(name, id) {
            this.snakes.push(new SnakeAi(this.ctxSnake, name, id));
        }

        generateFoods(n) {
            for (var i = 0; i < n; i++) {
                this.foods.push(new Food(this.ctxFood, Util.random(-1200 + 50, 2800 - 50),
                    Util.random(-600 + 50, 1400 - 50)));
            }
        }

        generateBricks() {
            var size = 50;
            var inRows = this.SCREEN_SIZE.x / size + 2;
            var inCols = this.SCREEN_SIZE.y / size + 2;
            var start = new Point(-size, -size);
            for (var i = 0; i < inRows; i++) {
                for (var j = 0; j < inCols; j++) {
                    var point = new Point(start.x + i * size, start.y + j * size);
                    this.bricks.push(point);
                }
            }
        }
    }

    const game = new Game(ctxSnake, ctxFood, ctxHex);

    function update(currentDelta) {
        requestAnimationFrame(update);

        ctxFood.clearRect(0, 0, canvasSnake.width, canvasSnake.height);
        ctxSnake.clearRect(0, 0, canvasSnake.width, canvasSnake.height);
        ctxHex.clearRect(0, 0, canvasSnake.width, canvasSnake.height);

        game.draw();
    }

    function handleJoy(input) {
        if (input.y !== '0' && input.x !== '0') {
            const angle = Math.atan2(-input.y, input.x);
            game.snakes[0].changeAngle(angle);
        }
    }

    canvasSnake.onmousemove = function (e) {
        if (mouseDown) {
            var cursor = Util.getMousePos(canvasSnake, e);
            var ang = Util.getAngle(game.snakes[0].arr[0], cursor);
            game.snakes[0].changeAngle(ang);
        }
    }

    canvasSnake.onmousedown = function (e) {
        mouseDown = true;
    }

    canvasSnake.onmouseup = function (e) {
        mouseDown = false;
    }

    function handleWithdraw() {
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
                setTimeout(() => {
                    window.location.href = '/php/dashboard.php?winGame=' + value_v;
                }, 5000);
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }
    }

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

    game.init();
    requestAnimationFrame(update);

    document.getElementById('fechatmd1').addEventListener('click', () => {
        console.log('Botão fechar modal 1 clicado');
        document.getElementById('modalDp1').classList.add('hide');
        window.location.href = '/php/dashboard.php';
    });

    document.getElementById('closeModalDp1').addEventListener('click', () => {
        console.log('Botão fechar modal 1 (X) clicado');
        document.getElementById('modalDp1').classList.add('hide');
        window.location.href = '/php/dashboard.php';
    });

    document.getElementById('fechatmd2').addEventListener('click', () => {
        console.log('Botão fechar modal 2 clicado');
        document.getElementById('modalDp2').classList.add('hide');
        window.location.href = '/php/dashboard.php';
    });

    document.getElementById('closeModalDp2').addEventListener('click', () => {
        console.log('Botão fechar modal 2 (X) clicado');
        document.getElementById('modalDp2').classList.add('hide');
        window.location.href = '/php/dashboard.php';
    });

    if (urlParams.has('winGame')) {
        const winGameValue = urlParams.get('winGame');
        console.log('Parâmetro winGame detectado:', winGameValue);
        document.getElementById('ganhodinheiroff').innerHTML = 'R$' + winGameValue;
        document.getElementById('modalDp2').classList.add('show');
    }

    if (urlParams.has('loseGame')) {
        console.log('Parâmetro loseGame detectado');
        document.getElementById('modalDp1').classList.add('show');
    }
});
