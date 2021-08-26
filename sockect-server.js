const server = require('http').createServer();
const io = require('socket.io')(server);
const port = 1337;

let tokens = {};
let users = {};

io.use((socket, next) => {
    let auth = socket.request.headers.authorization;
    let user = socket.request.headers.user;

    if (!auth || !user) {
        return next(new Error('No authorization header'));
    } else {
        const token = auth.replace("Bearer ", "");

        if (!tokens[token] && !users[token]) {
            tokens[token] = socket.id;
            users[token] = user;
        }

        return next();
    }
});

io.on('connection', socket => {
    let nb = 0;

    socket.on('logger_emmiter', (message) => {
        ++nb;

        let user = users[message['token']];
        console.log('Dispatch event');
    });
    
    socket.on('disconnect', () => {
        console.log('Disconnecting socket...');
    });
});

server.listen(port);
console.log('Server running on port: ' + port);