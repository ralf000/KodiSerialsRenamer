<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;

abstract class ASerial extends ARenamer
{

    protected static $path = '';

    /**
     * ASerial constructor.
     */
    public function __construct()
    {
        if (is_null(KodiSerialRenamer::$fileHandler))
            throw new FileHandlerException('Класс "KodiSerialRenamer" не инициализирован');
        parent::__construct(KodiSerialRenamer::$fileHandler);
    }

    protected function isNew(string $fileName)
    {
        return !strpos($fileName, self::RENAME_TAG);
    }

    /**
     * @param string $path
     */
    protected static function setPath($path)
    {
        self::$path = $path;
    }


    public static function getFullPath(string $dir) : string
    {
        return self::$path . '/' . static::$fileHandler->getOpts()['dir'] . '/' . $dir;
    }
}