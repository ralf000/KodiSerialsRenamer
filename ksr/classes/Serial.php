<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 25.09.2016
 * Time: 18:09
 */

namespace ksr\classes;


class Serial extends ARenamer
{
    private function serial()
    {
        $opts = $this->fileHandler->getOpts();
        $path = "ftp://{$opts['login']}:{$opts['password']}@{$opts['host']}/{$opts['path']}";
        $this->setPath($path);

        $dir = $opts['path'] . '/' . $opts['dir'];
        if (!$this->fileHandler->open($dir))
            throw new FTPException('Недоступная директория: ' . $dir);

        $this->serials = $this->fileHandler->list();
        if (empty($this->serials) || !is_array($this->serials))
            throw new FileHandlerException;

        foreach ($this->serials as $serial) {
            $this->serial = $serial;
            if (is_dir($this->getFullPath($serial)) && $this->isNew($serial)) {
                //заходим в папку сериала
                $this->fileHandler->open($serial);
                //получаем список сезонов или серий
                $this->seasons = $this->fileHandler->list();
                $status = $this->season();
                if ($status !== self::STATUS['IS_FILE'])
                    $this->fileHandler->parent();
                if ($status !== self::STATUS['SKIP'])
                    $this->fileHandler->rename($serial, $serial
                        . ' ' . self::RENAME_TAG);
            }
        }
    }
}