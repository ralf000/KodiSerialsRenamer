<?php

namespace ksr\classes;


use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger implements \ArrayAccess
{
    private $log = [];

    public function log($level, $message, array $context = array())
    {
        $data = $this->logHandler($level, $message, $context);
        if (!file_put_contents(__DIR__ . '/../../logs/logs.txt', $data . PHP_EOL, FILE_APPEND))
            throw new \Exception('Запись в лог не удалась');
    }

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
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->log[] = $value;
        } else {
            $this->log[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->log[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->log[$offset]);
    }

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