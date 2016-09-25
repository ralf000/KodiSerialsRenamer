<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;
use ksr\exceptions\FTPException;
use Noodlehaus\Config;

class KodiSerialRenamer extends ARenamer
{

    /**
     * KodiSerialRenamer constructor.
     * @param IFileHandler $fileHandler
     */
    public function __construct(IFileHandler $fileHandler)
    {
        parent::__construct($fileHandler);
        $this->initParams();
    }

    public static function g($var)
    {
        echo '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css">
                <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
                <script>hljs.initHighlightingOnLoad();</script>';
        echo '<pre><code class="html" style="border: 1px solid black;">';
        if (is_array($var) || is_object($var)) {
            print_r($var);
            if (is_object($var)) {
                $class = get_class($var);
                \Reflection::export(new \ReflectionClass($class));
            }
        } else {
            echo htmlspecialchars($var);
        }
        echo '</code>';
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

    private function season() : int
    {
        $this->currentPath = $this->getFullPath($this->serial);
        // проверяем файлы перед нами или папки
        if (is_file($this->getFullPath("{$this->serial}/{$this->seasons[1]}"))) {
            if (preg_match('/[^\d](\d{1,2})[^\d]/', $this->serial, $seasonNum))
                $this->seasonNum = $seasonNum[1];
            if (strlen($this->seasonNum) == 1)
                $this->seasonNum = '0' . $this->seasonNum;

            $this->series(self::ONE_SEASON);
            return self::STATUS['IS_FILE'];
        }
        foreach ($this->seasons as $season) {
            $this->season = $season;
            $this->currentPath = $this->getFullPath($this->serial) . '/' . $season;
            //если это сезон
            if (is_dir($this->currentPath)) {
                preg_match('/\d{1,2}/', $season, $seasonNum);
                if (mb_strlen($seasonNum[0]) == 1)
                    $seasonNum = '0' . $seasonNum[0];
                $this->seasonNum = $seasonNum;
                $newName = 'Season ' . trim($seasonNum);
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
            $this->fileHandler->open($this->season);
        if (!is_dir($this->currentPath))
            return false;

        $series = $this->fileHandler->list();
        sort($series);

        foreach ($series as $ep) {
            if (is_file($oldEpFullPath = $this->currentPath . '/' . $ep)) {
                //пропускаем недокачанные серии
                if (($this->extension = pathinfo($ep)['extension']) == 'part') {
                    $this->fileHandler->parent();
                    return FALSE;
                }
//                HouseMD.Seson1-episod-2 - копия.avi
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

    private function initParams()
    {
        $params = Config::load(__DIR__ . '/../../security/params.php');
        $this->extensions = $params['serialExtensions'];
    }

}