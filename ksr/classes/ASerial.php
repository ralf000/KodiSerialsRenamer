<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;
use Psr\Log\LogLevel;

/**
 * Class ASerial
 * @package ksr\classes
 */
abstract class ASerial extends ARenamer
{

    /**
     * @var string
     */
    protected static $path = '';

    /**
     * ASerial constructor.
     */
    public function __construct()
    {
        if (is_null(KodiSerialRenamer::$fileHandler))
            throw new FileHandlerException(LogLevel::CRITICAL, 'Класс "KodiSerialRenamer" не инициализирован');
        parent::__construct(KodiSerialRenamer::$fileHandler);
    }

    /**
     * @param string $fileName
     * @return bool
     */
    protected function isNew(string $fileName)
    {
        return !strpos($fileName, self::RENAME_TAG);
    }

    /**
     * @param $path
     */
    protected static function setPath($path)
    {
        self::$path = $path;
    }


    /**
     * @param string $dir
     * @return string
     */
    public static function getFullPath(string $dir) : string
    {
        return self::$path . '/' . static::$fileHandler->getOpts()['dir'] . '/' . $dir;
    }
}