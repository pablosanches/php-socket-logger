<?php

require __DIR__ . '/vendor/autoload.php';
use PabloSanches\Logger;

Logger::info('Message info here - {variable}', array('variable' => 'changed'));
Logger::critical('Message info here - {variable}', array('variable' => 'changed'));