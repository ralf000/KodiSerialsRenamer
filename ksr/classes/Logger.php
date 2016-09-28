<?php

namespace ksr\classes;


use Psr\Log\AbstractLogger;

/**
 * Class Logger
 * @package ksr\classes
 */
class Logger extends AbstractLogger implements \ArrayAccess
{
    /**
     * @var array список сообщений приложения (string)
     */
    private $log = [];

    public function log($level, $message, array $context = array())
    {
        $data = $this->logHandler($level, $message, $context);
        if (!file_put_contents(__DIR__ . '/../../logs/logs.txt', $data . PHP_EOL, FILE_APPEND))
            throw new \Exception('Запись в лог не удалась');
    }

    /**
     * @param $level
     * @param $message
     * @param $context
     * @return string
     */
    private function logHandler($level, $message, $context)
    {
        $output = date('H:i:s d-m-Y') . PHP_EOL;
        $output .= 'Тип: ' . $level . PHP_EOL;
        if ($message)
            $output .= 'Сообщение: ' . PHP_EOL . $message . PHP_EOL;
        if ($context)
            $output .= 'Контекст выполнения: ' . PHP_EOL . implode(PHP_EOL, $context) . PHP_EOL;
        $output .= '==============================';
        return $output;
    }

//    array access interface
    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->log[] = $value;
        } else {
            $this->log[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->log[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->log[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->log[$offset]) ? $this->log[$offset] : null;
    }
//    /array access interface

    /**
     * @return string
     */
    public function getLog() : string
    {
        return implode(PHP_EOL, $this->log);
    }


}