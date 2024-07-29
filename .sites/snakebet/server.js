const fs = require('fs');
const express = require('express');
const https = require('https');
const socketIo = require('socket.io');
const mysql = require('mysql');
const cors = require('cors');

const app = express();

const options = {
    key: fs.readFileSync('/www/wwwroot/cobrasnake.click/certificados/privkey.pem'),
    cert: fs.readFileSync('/www/wwwroot/cobrasnake.click/certificados/fullchain.pem')
};

const server = https.createServer(options, app);

const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

app.use(cors());

const db = mysql.createConnection({
    host: 'localhost',
    user: 'cobrasnake',
    password: '6NA8nCRaRYRdePaB',
    database: 'cobrasnake'
});

db.connect(err => {
    if (err) throw err;
    console.log('Conectado ao banco de dados');
});

let games = {};

io.on('connection', socket => {
    console.log('Novo cliente conectado', socket.id);

    socket.on('startGame', data => {
        console.log('Evento startGame recebido:', data);
        const { bet, sessionToken } = data;

        const query = "SELECT users.id, users.balance, users.username FROM users INNER JOIN sessions ON users.id = sessions.user_id WHERE sessions.session_token = ?";
        db.query(query, [sessionToken], (err, results) => {
            if (err) {
                socket.emit('gameUpdate', { status: 'error', message: 'Erro ao verificar o saldo' });
                console.error('Erro ao verificar o saldo:', err);
                return;
            }

            if (results.length === 0) {
                socket.emit('gameUpdate', { status: 'error', message: 'Usuário não encontrado' });
                return;
            }

            const user = results[0];
            if (user.balance < bet) {
                socket.emit('gameUpdate', { status: 'error', message: 'Saldo insuficiente' });
                return;
            }

            const newBalance = user.balance - bet;
            const updateQuery = "UPDATE users SET balance = ? WHERE id = ?";
            db.query(updateQuery, [newBalance, user.id], err => {
                if (err) {
                    socket.emit('gameUpdate', { status: 'error', message: 'Erro ao atualizar o saldo' });
                    console.error('Erro ao atualizar o saldo:', err);
                    return;
                }

                // Registrar a transação de aposta
                const insertTransactionQuery = "INSERT INTO game_transactions (user_id, bet_value) VALUES (?, ?)";
                db.query(insertTransactionQuery, [user.id, bet], err => {
                    if (err) {
                        socket.emit('gameUpdate', { status: 'error', message: 'Erro ao registrar a aposta' });
                        console.error('Erro ao registrar a aposta:', err);
                        return;
                    }

                    games[socket.id] = {
                        userId: user.id,
                        username: user.username,
                        balance: newBalance,
                        bet,
                        points: 0,
                        money: 0,
                        canWithdraw: false
                    };

                    socket.emit('gameStarted', {
                        status: 'ok',
                        userName: user.username,
                        points: 0,
                        money: 0,
                        canWithdraw: false
                    });

                    // Notificar outros jogadores sobre o novo jogador
                    socket.broadcast.emit('playerJoined', { username: user.username });
                });
            });
        });
    });

    socket.on('joinGame', data => {
        console.log('Evento joinGame recebido:', data);
        const game = games[socket.id];
        if (game) {
            socket.emit('gameUpdate', {
                points: game.points,
                money: game.money,
                canWithdraw: game.canWithdraw
            });
        }
    });

    socket.on('withdraw', () => {
        const game = games[socket.id];
        if (game) {
            const { userId, money, bet } = game;
            const query = "UPDATE users SET balance = balance + ? WHERE id = ?";
            db.query(query, [money, userId], err => {
                if (err) {
                    socket.emit('gameUpdate', { status: 'error', message: 'Erro ao atualizar o saldo' });
                    console.error('Erro ao atualizar o saldo:', err);
                    return;
                }

                // Registrar a transação de ganho
                const insertWinQuery = "UPDATE game_transactions SET win_value = ? WHERE user_id = ? AND bet_value = ?";
                db.query(insertWinQuery, [money, userId, bet], err => {
                    if (err) {
                        socket.emit('gameUpdate', { status: 'error', message: 'Erro ao registrar o ganho' });
                        console.error('Erro ao registrar o ganho:', err);
                        return;
                    }

                    socket.emit('gameUpdate', { status: 'ok', message: 'Retirada realizada com sucesso' });
                    delete games[socket.id];
                });
            });
        }
    });

    socket.on('disconnect', () => {
        console.log('Cliente desconectado', socket.id);
        delete games[socket.id];
    });
});

server.listen(5020, () => {
    console.log('Servidor rodando na porta 5020');
});
