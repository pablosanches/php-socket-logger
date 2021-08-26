<?php

namespace PabloSanches\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;

/**
 * Implements PSR3 emitting logs per socket
 */
class Logger implements LoggerInterface
{
    const LOGGER_TOKEN = 'PABLO-TOKEN';

    private $socketClient;

    public function __construct()
    {
        $this->socketClient = new Client(new Version2X('http://localhost:1337', [
            'headers' => [
                'X-My-Header: SocketLogger',
                'Authorization: Bearer ' . self::LOGGER_TOKEN,
                'User: peter',
            ]
        ]));
    }

    public function __destruct()
    {
        $this->socketClient->close();
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        return $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = array())
    {
        return $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = array())
    {
        return $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = array())
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = array())
    {
        return $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = array())
    {
        return $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = array())
    {
        return $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = array())
    {
        return $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        ];

        if (!in_array($level, $levels)) {
            throw new InvalidArgumentException('Invalid log level');
        }

        $this->emit($level . ' ' . $this->interpolate($message, $context));
    }

    /**
     * Interpolates context values into the message placeholders
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = array();
        foreach ($context as $key => $value) {
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{'. $value .'}'] = $value;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Emit the log to socket
     *
     * @param string $msg
     * @return void
     */
    protected function emit($msg)
    {
        $this->socketClient->initialize();
        
        $this->socketClient->emit('logger_emmiter', array(
            'message' => $msg,
            'token' => self::LOGGER_TOKEN
        ));

        $this->socketClient->close();
    }
}