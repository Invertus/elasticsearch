<?php

namespace Invertus\Brad\Logger;

/**
 * Interface LoggerInterface
 *
 * @package Invertus\Brad\Logger
 */
interface LoggerInterface
{
    const DEBUG = 1;
    const INFO = 2;
    const WARNING = 3;
    const ERROR = 4;

    /**
     * Log message
     *
     * @param string $message
     * @param array $params
     * @param int $level
     *
     * @return bool
     */
    public function log($message, array $params = [], $level = Logger::DEBUG);
}
