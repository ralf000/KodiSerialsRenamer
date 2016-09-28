<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 25.09.2016
 * Time: 18:09
 */

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;
use ksr\exceptions\FTPException;
use Psr\Log\LogLevel;

/**
 * Class Serial
 * @package ksr\classes
 */
class Serial extends ASerial
{

    /**
     * @var array
     */
    private static $serials = [];
    /**
     * @var string
     */
    private static $serial = '';

    /**
     * Serial constructor.
     * @param string $serial
     */
    public function __construct(string $serial)
    {
        parent::__construct();
        self::$serial = $serial;
    }

    /**
     * Проверяет, что сериал это папка, не отмеченная тегом (R)
     * @return bool
     */
    public function validate()
    {
        return (is_dir(self::getFullPath(self::$serial)) && $this->isNew(self::$serial)) ? true : false;
    }


    /**
     * @return array Массив из папок сериалов (string)
     * @throws FTPException
     * @throws FileHandlerException
     */
    public static function getAll() : array
    {
        $opts = self::$fileHandler->getOpts();
        $path = "ftp://{$opts['login']}:{$opts['password']}@{$opts['host']}/{$opts['path']}";
        self::setPath($path);

        $dir = $opts['path'] . '/' . $opts['dir'];
        if (!self::$fileHandler->open($dir))
            throw new FTPException(LogLevel::ERROR, 'Недоступная директория: ' . $dir);

        self::$serials = self::$fileHandler->list();
        if (empty(self::$serials) || !is_array(self::$serials))
            throw new FileHandlerException(LogLevel::WARNING, 'Не удалось получить список сериалов');

        return self::$serials;
    }

    /**
     * @return string
     */
    public static function getSerial() : string
    {
        return self::$serial;
    }

}