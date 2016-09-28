<?php

namespace ksr\exceptions;


use Exception;
use ksr\classes\Logger;

class BaseException extends Exception
{
    public function __construct($level, $message, $code = null, Exception $previous = null)
    {
        $logger = new Logger();
        $context = ['Класс ошибки: ' . static::class, 'Файл: ' . $this->getFile(), 'Строка: ' . $this->getLine()];
        $logger->log($level, $message, $context);
        parent::__construct($message, $code, $previous);
    }

}