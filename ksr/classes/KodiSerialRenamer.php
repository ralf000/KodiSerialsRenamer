<?php

namespace ksr\classes;


use ksr\exceptions\FileHandlerException;
use ksr\exceptions\FTPException;
use Noodlehaus\Config;
use Psr\Log\LogLevel;

/**
 * Class KodiSerialRenamer
 * @package ksr\classes
 */
class KodiSerialRenamer extends ARenamer
{

    /**
     * @var string путь до текущей рабочей директории
     */
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

    /**
     * получаем параметры приложения
     */
    private function initParams()
    {
        self::$params = self::$fileHandler->getParams();
    }

    /**
     * Запуск приложения
     * @throws FTPException
     * @throws FileHandlerException
     * @throws \Exception
     */
    public function run()
    {
        foreach (Serial::getAll() as $serialName) {
            self::$currentPath = Season::getFullPath($serialName);

            $serial = new Serial($serialName);
            if (!$serial->validate())
                continue;

            $seasons = Season::getSeasons();
            if (!$seasons) continue;

            if (!Season::isSeasons($seasons)) {
                Season::setSeasonNum();
                $this->episodesHandler(self::ONE_SEASON);
                self::$status = self::STATUS['IS_FILE'];
            } else {
                $this->seasonsHandler($seasons);
                if (self::$status === self::STATUS['SKIP']) continue;
                self::$status = self::STATUS['RENAMED'];
            }
            $this->statusHandler();
        }
        self::$logger->log(LogLevel::INFO, self::$logger->getLog(), [static::class]);
    }

    /**
     * Обработчик сезонов сериала
     * @param $seasons
     */
    private function seasonsHandler($seasons){
        foreach ($seasons as $seasonName) {
            $season = new Season($seasonName);
            self::$currentPath = Season::getFullPath(Serial::getSerial()) . '/' . $seasonName;
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
    }

    /**
     * Обработчик эпизодов сезона сериала
     * @param bool $oneSeasonSerial
     * @return bool
     */
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
                if (Episode::skipPart()) {
                    self::$logger[] = Serial::getSerial() . ' ещё не докачен и был пропущен';
                    return false;
                }
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

    /**
     * Обработчик статусов приложения
     */
    private function statusHandler(){
        if (self::$status !== self::STATUS['IS_FILE']) {
            self::$fileHandler->parent();
        }
        if (self::$status !== self::STATUS['SKIP']) {
            self::$fileHandler->rename(Serial::getSerial(), Serial::getSerial() . ' ' . self::RENAME_TAG);
            self::$logger[] = '""' . Serial::getSerial() . '" успешно обработан';
        }
        self::$status = '';
    }

}