<?php

namespace ksr\classes;


use Noodlehaus\Config;

abstract class ARenamer
{
    const ONE_SEASON = TRUE;
    const RENAME_TAG = '(R)';
    const STATUS = [
        'SKIP' => -1,
        'IS_FILE' => 0,
        'RENAMED' => 1,
        'SKIP_ALL' => 2
    ];

    /**
     * @var $fileHandler IFileHandler
     */
    protected static $fileHandler = null;
    /**
     * @var $params Config
     */
    protected static $params;
    /**
     * @var $status int
     */
    protected static $status;

    /**
     * SerialRenaimer constructor.
     * @param IFileHandler $fileHandler
     */
    public function __construct(IFileHandler $fileHandler)
    {
        if (is_null(static::$fileHandler))
            self::$fileHandler = $fileHandler;
    }

    /**
     * @return Config
     */
    public static function getParams() : Config
    {
        return self::$params;
    }

}