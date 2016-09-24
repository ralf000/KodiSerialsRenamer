<?php

namespace ksr\classes;


use ksr\exceptions\FTPException;

class FTPConnector
{
    private static $instance = null;
    private $opts = [];
    private $ftpStream;

    /**
     * FTPConnector constructor.
     */
    private function __construct()
    {
        $this->opts = json_decode(file_get_contents('../../security/creds.json'), TRUE);
        if (!$this->opts)
            throw new FTPException('Не могу получить настройки для инициализации скрипта');
        $this->ftpStreamPreparer();
    }

    private function ftpStreamPreparer()
    {
        $host = $this->opts['host'];
        $this->ftpStream = ftp_connect($host);
        if (!ftp_login($this->ftpStream, $this->opts['login'], $this->opts['password']))
            throw new FTPException('Не могу соединиться с фтп ' . $host);
        if (!ftp_chdir($this->ftpStream, $this->opts['path']))
            throw new FTPException('Недоступная директория: ' . $this->opts['path']);
        return true;
    }

    public static function init(){
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
     * @return array;
     */
    public function getOpts()
    {
        return $this->opts;
    }


}
