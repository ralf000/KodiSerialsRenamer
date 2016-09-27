<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;
use ksr\exceptions\FTPException;
use Noodlehaus\Config;

class KodiSerialRenamer extends ARenamer
{

    private static $currentPath = '';

    /**
     * KodiSerialRenamer constructor.
     * @param IFileHandler $fileHandler
     */
    public function __construct(IFileHandler $fileHandler)
    {
        parent::__construct($fileHandler);
        $this->initParams();
    }

    private function initParams()
    {
        self::$params = self::$fileHandler->getParams();
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
        foreach (Serial::getAll() as $serialName) {
            $serial = new Serial($serialName);
            if (!$serial->validate())
                continue;

            $seasons = Season::getSeasons();
            if (!$seasons) continue;

            self::$currentPath = Season::getFullPath($serialName);

            if (!Season::isSeasons($seasons)) {
                Season::setSeasonNum();
                $this->episodesHandler(self::ONE_SEASON);
                self::$status = self::STATUS['IS_FILE'];
            } else {
                foreach ($seasons as $seasonName) {
                    $season = new Season($seasonName);
                    self::$currentPath = Season::getFullPath($serialName) . '/' . $seasonName;
                    //если это сезон
                    if (is_dir(self::$currentPath)) {
                        $newSeasonName = $season->seasonNameHandler();
                        $status = $this->episodesHandler();
                        if (!$status)
                            continue;
                        if ($seasonName !== $newSeasonName)
                            $season->rename($newSeasonName);

                    }
                }
                if (self::$status === self::STATUS['SKIP']) continue;
                
                self::$status = self::STATUS['RENAMED'];
            }

            if (self::$status !== self::STATUS['IS_FILE']) {
                self::$fileHandler->parent();
            }
            if (self::$status !== self::STATUS['SKIP']) {
                self::$fileHandler->rename($serialName, $serialName . ' ' . self::RENAME_TAG);
            }
            self::$status = '';
        }
    }

    private function episodesHandler($oneSeasonSerial = FALSE) : bool
    {
        if (!$oneSeasonSerial)
            self::$fileHandler->open(Season::getSeason());
        if (!is_dir(self::$currentPath))
            return false;

        $episodes = Episode::getEpisodes();
        foreach ($episodes as $ep) {
            $episode = new Episode($ep);

            if (Episode::isEpisode(self::$currentPath)) {
                if (Episode::skipPart())
                    return false;
                $episode->renameEpisode();
                if (self::$status === self::STATUS['SKIP']) {
                    self::$status = '';
                    continue;
                }
            }
        }
        self::$fileHandler->parent();
        return true;
    }

}