<?php

namespace ksr\classes;


use ksr\exceptions\FTPException;
use Noodlehaus\Config;
use Psr\Log\LogLevel;

/**
 * Class FTPConnector
 * @package ksr\classes
 */
class FTPConnector
{
    /**
     * @var self $instance
     */
    private static $instance = null;
    /**
     * @var Config $opts
     */
    private $opts;
    /**
     * @var resource ftp connection descriptor
     */
    private $ftpStream;

    /**
     * FTPConnector constructor.
     */
    private function __construct()
    {
        $this->opts = Config::load(__DIR__ . '/../../security/creds.json');
        if (!$this->opts)
            throw new FTPException(LogLevel::ERROR, 'Не могу получить настройки для инициализации скрипта');
        $this->connect();
    }

    /**
     * @return bool
     * @throws FTPException
     */
    private function connect() : bool
    {
        $host = $this->opts['host'];
        $this->ftpStream = ftp_connect($host);
        if (!ftp_login($this->ftpStream, $this->opts['login'], $this->opts['password']))
            throw new FTPException(LogLevel::CRITICAL, 'Не могу соединиться с фтп ' . $host);
        ftp_pasv($this->ftpStream, true);

        return true;
    }

    /**
     * @return FTPConnector
     */
    public static function init() : self
    {
        if (is_null(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return resource ftp connect descriptor
     */
    public function getFtpStream()
    {
        return $this->ftpStream;
    }

    /**
     * @return Config object;
     */
    public function getOpts() : Config
    {
        return $this->opts;
    }


}
