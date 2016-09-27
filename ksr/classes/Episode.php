<?php


namespace ksr\classes;


use ksr\exceptions\ConfigException;

class Episode extends ASerial
{

    private static $episodes = [];
    private static $episode = '';

    private static $extension = '';

    /**
     * Episode constructor.
     * @param string $episode
     */
    public function __construct(string $episode)
    {
        parent::__construct();
        self::$episode = $episode;
    }

    private function rename($epNum)
    {
        $extension = self::$extension;
        $extensions = self::getParams()['serialExtensions'];
        if (!$extensions)
            throw new ConfigException('Не удалось получить список разрешенных разрешений файлов');
        if (!in_array($extension, $extensions)) {
            self::$status = self::STATUS['SKIP'];
            return self::STATUS['SKIP'];
        }
        if (strlen($epNum) == 1)
            $epNum = '0' . $epNum;
        $seasonNum = Season::getSeasonNum();
        $epNewName = "s{$seasonNum}e{$epNum}.{$extension}";

        if (self::$episode !== $epNewName)
            self::$fileHandler->rename(self::$episode, $epNewName);
        self::$status = self::STATUS['RENAMED'];
        return self::STATUS['RENAMED'];
    }

    public static function getEpisodes() : array
    {
        $episodes = self::$fileHandler->list();
        if (!$episodes) return false;
        sort($episodes);
        self::$episodes = $episodes;
        return $episodes;
    }

    public function renameEpisode()
    {
        if (preg_match('/\d{1,2}[-|_]\d{1,2}/', self::$episode, $match))
            $this->rename(str_replace('_', '-', $match[0]));
        else if (preg_match('/s(\d{1,2})e(\d{1,2})/i', self::$episode, $match))
            $this->rename($match[2]);
        else if (preg_match('/(\d{1,2})[^\d]/', self::$episode, $match))
            $this->rename($match[1]);
    }

    public static function isEpisode($currentPath) : bool
    {
        return (is_file($currentPath . '/' . self::$episode)) ? true : false;
    }

    /**
     * пропускаем недокачанные серии
     * @return bool
     */
    public static function skipPart() : bool
    {
        if (!self::$episodes || !is_array(self::$episodes))
            return false;
        foreach (self::$episodes as $ep) {
            if ((self::$extension = pathinfo($ep)['extension']) == 'part') {
                self::$fileHandler->parent();
                self::$status = self::STATUS['SKIP'];
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public static function getExtension() : string
    {
        return self::$extension;
    }
}