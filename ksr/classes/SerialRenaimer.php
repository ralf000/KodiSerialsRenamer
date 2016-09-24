<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;

class SerialRenaimer extends ARenaimer
{

    const STATUS = [
        'SKIP' => -1,
        'IS_FILE' => 0,
        'RENAMED' => 1
    ];
    const ONE_SEASON = TRUE;

    private $extensions = [];
    private $currentPath = '';
    private $serials = [];
    private $serial = [];
    private $seasons = [];
    private $season = [];
    private $seasonNum = '01';
    private $extension = '';

    /**
     * SerialRenaimer constructor.
     * @param IFileHandler $fileHandler
     */
    public function __construct(IFileHandler $fileHandler)
    {
        parent::__construct($fileHandler);
        // TODO get extensions from config
        //$this->extensions = $extensions;
    }


    public function run()
    {
        $this->serial();
    }

    private function serial()
    {
        $opts = $this->fileHandler->getOpts();
        $path = "ftp://{$opts['login']}:{$opts['password']}@{$opts['host']}/{$opts['path']}";
        $this->setPath($path);
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

    private function season() : int
    {
        $this->currentPath = $this->getFullPath("{$this->serial}/{$this->seasons[1]}");
        // проверяем файлы перед нами или папки
        if (is_file($this->currentPath)) {
            if (preg_match('/[^\d](\d{1,2})[^\d]/', $this->serial, $seasonNum))
                $this->seasonNum = $seasonNum[1];
            if (strlen($this->seasonNum) == 1)
                $this->seasonNum = '0' . $this->seasonNum;
            $this->series(self::ONE_SEASON);
            return self::STATUS['IS_FILE'];
        }
        foreach ($this->seasons as $season) {
            $this->season = $season;
            $this->currentPath = $this->getFullPath($this->serial) . $season;
            //если это сезон
            if (is_dir($this->currentPath)) {
                preg_match('/\d{1,2}/', $season, $seasonNum);
                if (mb_strlen($seasonNum[0]) == 1)
                    $seasonNum = '0' . $seasonNum[0];
                $this->seasonNum = $seasonNum;
                $newName = 'Season ' . trim($seasonNum[0]);
                $result = $this->series();
                if ($result) {
                    if ($season !== $newName)
                        $this->fileHandler->rename($season, $newName);
                } else {
                    return self::STATUS['SKIP'];
                }
            }
        }
        return self::STATUS['RENAMED'];
    }

    private function series($oneSeasonSerial = FALSE) : bool
    {
        if (!$oneSeasonSerial)
            $this->fileHandler->open($this->serial);
        if (is_dir($this->fullPath)) {
            $series = $this->fileHandler->list();
        }
        sort($series);
        foreach ($series as $ep) {
            if (is_file($oldEpFullPath = $this->currentPath . $ep)) {
                //пропускаем недокачанные серии
                if (($this->extension = pathinfo($ep)['extension']) == 'part') {
                    $this->fileHandler->parent();
                    return FALSE;
                }
                if (preg_match('/\d{1,2}[-|_]\d{1,2}/', $ep, $match)) {
                    $epNewName = $this->episode(str_replace('_', '-', $match[0]));
                } else if (preg_match('/s(\d{1,2})e(\d{1,2})/i', $ep, $match)) {
                    $epNewName = $this->episode($match[2]);
                } else if (preg_match('/(\d{1,2})[^\d]/', $ep, $match)) {
                    $epNewName = $this->episode($match[1]);
                } else {
                    continue;
                }
                if ($epNewName === self::STATUS['SKIP'])
                    continue;
                if ($ep !== $epNewName) {
                    $this->fileHandler->rename($ep, $epNewName);
                }
            }
        }
        return $this->fileHandler->parent() ? true : false;
    }

    private function episode($epNum) : string
    {
        if (!in_array($this->extension, $this->extensions)) {
            return self::STATUS['SKIP'];
        }
        if (strlen($epNum) == 1)
            $epNum = '0' . $epNum;
        return "s{$this->seasonNum}e{$epNum}.{$this->extension}";
    }

}