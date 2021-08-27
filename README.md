# php-socket-logger
PSR3 LoggerInterface implementation with websocket support for real-time log monitoring.

[![Latest Stable Version](http://poser.pugx.org/pablosanches/php-websocket-logger/v)](https://packagist.org/packages/pablosanches/php-websocket-logger)
[![Total Downloads](http://poser.pugx.org/pablosanches/php-websocket-logger/downloads)](https://packagist.org/packages/pablosanches/php-websocket-logger)
[![License](http://poser.pugx.org/pablosanches/php-websocket-logger/license)](https://packagist.org/packages/pablosanches/php-websocket-logger)

## Using

```sh
composer require pablosanches/php-websocket-logger
```

### PHP
```php
<?php

require __DIR__ . '/vendor/autoload.php';
use PabloSanches\Logger;

Logger::info('Message info here - {variable}', array('variable' => 'changed'));
Logger::critical('Message critical here - {variable}', array('variable' => 'changed'));
```

### NODEJS
```js
const server = require('http').createServer();
const io = require('socket.io')(server);
const logger = require('winston');
const port = 1337;
const token = 'PABLO-TOKEN';

// Logger config
logger.remove(logger.transports.Console);
logger.add(logger.transports.Console, { colorize: true, timestamp: true });
logger.info('SocketIO > listening on port ' + port);

// Stored tokens
var tokens = {};

// Stored users
var users = {};

// set up initialization and authorization method
io.use(function (socket, next) {
    let auth = socket.request.headers.authorization;

    if(auth) {
        const token = auth.replace("Bearer ", "");
        logger.info("auth token", token);

        return next();
    } else {
        return next(new Error("no authorization header"));
    }
});

io.on('connection', function (socket){

    logger.info('SocketIO > Connected socket ' + socket.id);
    logger.info("X-My-Header", socket.handshake.headers['x-my-header']);

    socket.on('logger_emmiter', function (params) {
        if (params['token'] !== token) {
            logger.error('Invalid token!');
        } else {
            logger.info('Received a log event. Log type:' + params['level']);
            logger.info('LOG Message: ' + params['message']);
            logger.info('LOG Token: ' + params['token']);
        }
    });

    socket.on('disconnect', function () {
        logger.info('SocketIO > Disconnected socket ' + socket.id);
    });
});

server.listen(port);
```