# php-socket-logger
PSR3 LoggerInterface implementation with websocket support for real-time log monitoring.


## Using

```sh
composer require pablosanches/php-websocket-logger
```

```php
<?php

use PabloSanches\Logger;

$log = new Logger();
$log->debug('This is a custom debug. Variable test will be changed', ['test' => 'Changed!']);
```