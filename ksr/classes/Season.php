<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 25.09.2016
 * Time: 18:09
 */

namespace ksr\classes;


/**
 * Class Season
 * @package ksr\classes
 */
class Season extends ASerial
{

    /**
     * @var string ткущий номер сезона
     */
    protected static $seasonNum = '01';
    /**
     * @var string текущее название сезона
     */
    protected static $season = '';

    /**
     * Season constructor.
     * @param string $seasonName
     */
    public function __construct(string $seasonName)
    {
        parent::__construct();
        self::$season = $seasonName;
    }

    /**
     * Проверяет с сезонами или эпизодами имеем дело
     * @param array $seasons
     * @return bool
     */
    public static function isSeasons(array $seasons)
    {
        return (is_file(self::getFullPath(Serial::getSerial() . '/' . current($seasons)))) ? false : true;
    }

    /**
     * @return string преобразованное название сезона
     */
    public function seasonNameHandler()
    {
        preg_match('/\d{1,2}/', self::$season, $seasonNum);
        if (mb_strlen($seasonNum[0]) == 1)
            $seasonNum[0] = '0' . $seasonNum[0];
        self::$seasonNum = $seasonNum[0];
        return 'Season ' . trim($seasonNum[0]);
    }

    /**
     * @param string $seasonNum
     */
    public static function setSeasonNum($seasonNum = '')
    {
        if (!empty($seasonNum)) {
            self::$seasonNum = $seasonNum;
            return;
        }
        if (preg_match('/[^\d](\d{1,2})[^\d]/', Serial::getSerial(), $seasonNum))
            self::$seasonNum = $seasonNum[1];
        if (strlen(self::$seasonNum) == 1)
            self::$seasonNum = '0' . self::$seasonNum;
    }

    /**
     * Получить список файлов сериала
     * @return array|bool
     */
    public static function getSeasons()
    {
        //заходим в папку сериала
        self::$fileHandler->open(Serial::getSerial());
        //получаем список сезонов или серий
        return (self::$fileHandler->list()) ?: false;
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename(string $newName) : bool
    {
        return (self::$fileHandler->rename(self::$season, $newName)) ? true : false;
    }

    /**
     * @return string
     */
    public static function getSeason()
    {
        return self::$season;
    }

    /**
     * @return string
     */
    public static function getSeasonNum()
    {
        return self::$seasonNum;
    }


}