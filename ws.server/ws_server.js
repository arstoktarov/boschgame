const socketio = require('socket.io');
const Redis = require('ioredis');
const EventEmitter = require('events');
const authService = require('./AuthService');
const WebSocket = require('ws');
const queryString = require('query-string');
const url = require('url');

const redis = new Redis();
const emitter = new EventEmitter();

const wss = new WebSocket.Server({port: 8080});
console.log('WS server started at ' + 8080);



let users = new Map();

// on client connected
wss.on('connection', async (socket, request) => {
    console.log('New connection! ' + request.url);
    let queryData = queryString.parse(url.parse(request.url).search);

    if (!queryData.token) {
        socket.close();
        return;
    }

    let token = queryData.token;
    let isTokenValid = await authService.isTokenValid(token);

    if (!isTokenValid) {
        socket.close();
        return;
    }
    
    socket.user = await authService.getUserByToken(token);

    if (!socket.user) return;

    console.log('New user! ' + (socket.user ? `${socket.user.id}:${socket.user.login}` : socket.id) );

    users.set(socket.user.id.toString(), socket);
});


redis.psubscribe('*', function(error, count) {

});
// On redis message sent
redis.on('pmessage', async function(pattern, channel, message) {
    message = JSON.parse(message);

    let event = message.event;
    let dataToSend = message.data.data;

    if (message.data.to) sendToUser(event, message.data.to, dataToSend);
    else broadcast(event, dataToSend);

    //console.log(channel, message);
});

// Send message to Particular socket user
function sendToUser(event, user_id, data) {
    let socketUser = users.get(user_id.toString());

    if (socketUser) {
        console.log(`Sending message about ${event} to user: ${socketUser.user.login}`);
        sendMessage(socketUser, event, data);
    }
}

// Broadcast message to everyone
function broadcast(event, data) {
    console.log(`Broadcasting message about ${event}`);
    users.forEach(function(socket) {
        sendMessage(socket, event, data);
    });
}

function sendMessage(socket, event, data) {
    socket.send(JSON.stringify({
        'event': event,
        'data': data
    }));
}