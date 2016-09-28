<?php

namespace ksr\classes;


use Noodlehaus\Config;

/**
 * Class FTPHandler
 * @package ksr\classes
 */
class FTPHandler implements IFileHandler
{

    /**
     * @var FTPConnector $ftp
     */
    private $ftp = null;

    /**
     * FTPHandler constructor.
     */
    public function __construct()
    {
        if (is_null($this->ftp)) {
            $this->ftp = FTPConnector::init();
        }
    }

    /**
     * Сменить директорию
     * @param string $dir
     * @return bool
     */
    public function open(string $dir) : bool
    {
        return ftp_chdir($this->ftp->getFtpStream(), $dir) ? true : false;
    }

    /**
     * Показать содержимое директории
     * @param string $dir
     * @return mixed
     */
    public function list(string $dir = '')
    {
        return ftp_nlist($this->ftp->getFtpStream(), $dir);
    }

    /**
     * Переименовать файл
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function rename(string $oldName, string $newName) : bool
    {
        return ftp_rename($this->ftp->getFtpStream(), $oldName, $newName)
            ? true : false;
    }

    /**
     * Перейти в родительскую директорию
     * @return bool
     */
    public function parent() : bool
    {
        return ftp_cdup($this->ftp->getFtpStream()) ? true : false;
    }


    /**
     * @return Config
     */
    public function getOpts() : Config
    {
        return $this->ftp->getOpts();
    }

    /**
     * @return Config
     */
    public function getParams() : Config
    {
        return Config::load(__DIR__ . '/../../security/params.php') ?: false;
    }


}