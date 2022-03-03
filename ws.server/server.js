const socketio = require('socket.io');
const Redis = require('ioredis');
const EventEmitter = require('events');
const authService = require('./AuthService');


const io = socketio(6001);
const redis = new Redis();
const emitter = new EventEmitter();


let users = new Map();


redis.psubscribe('*', function(error, count) {
    console.log('Succesfully subscribed to redis!');
});


// middleware
io.use(async function(socket, next) {
    let token = socket.handshake.query.token;

    if (await authService.isTokenValid(token)) {
        socket.user = await authService.getUserByToken(token);
        return next();
    }

    return next(new Error('authentication error'));
});

// on client connected
io.on('connection', (socket) => {
    console.log('New connection! ' + (socket.user ? `${socket.user.id}:${socket.user.login}` : socket.id) );

    users.set(socket.user.id.toString(), socket);
});

redis.on('pmessage', function(pattern, channel, message) {
    message = JSON.parse(message);

    let event = message.event;
    let dataToSend = message.data.data;

    if (message.data.to) sendToUser(event, message.data.to, dataToSend);
    else broadcast(event, dataToSend);

    console.log(channel, message);
});

function sendToUser(event, user_id, data) {
    let socketUser = users.get(user_id.toString());

    if (socketUser) socketUser.emit(event, data);
}

function broadcast(event, data) {
    io.emit(event, data);
}