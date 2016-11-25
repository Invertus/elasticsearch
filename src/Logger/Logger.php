<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Invertus\Brad\Logger;

use Monolog\Handler\StreamHandler;

/**
 * Class Logger
 *
 * @package Invertus\Brad\Logger
 */
class Logger implements LoggerInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $monolog;

    /**
     * Logger constructor.
     *
     * @param string $logDir
     */
    public function __construct($logDir)
    {
        $logFile = $logDir.'logs.log';

        $streamhandler = new StreamHandler($logFile);

        $monolog = new \Monolog\Logger('brad_logger');
        $monolog->pushHandler($streamhandler);

        $this->monolog = $monolog;
    }

    /**
     * Log message
     *
     * @param string $message
     * @param array $context
     * @param int $level
     *
     * @return bool
     */
    public function log($message, array $context = [], $level = Logger::ERROR)
    {
        switch ($level) {
            default:
            case Logger::DEBUG:
                $monologLevel = \Monolog\Logger::DEBUG;
                break;
            case Logger::INFO:
                $monologLevel = \Monolog\Logger::INFO;
                break;
            case Logger::WARNING:
                $monologLevel = \Monolog\Logger::WARNING;
                break;
            case Logger::ERROR:
                $monologLevel = \Monolog\Logger::ERROR;
                break;
        }

        return $this->monolog->log($monologLevel, $message, $context);
    }
}
