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

class Serial extends ASerial
{

    private static $serials = [];
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
            throw new FTPException('Недоступная директория: ' . $dir);

        self::$serials = self::$fileHandler->list();
        if (empty(self::$serials) || !is_array(self::$serials))
            throw new FileHandlerException('Не удалось получить список сериалов');

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