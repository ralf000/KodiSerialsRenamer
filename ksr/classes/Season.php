<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 25.09.2016
 * Time: 18:09
 */

namespace ksr\classes;


class Season extends ARenaimer
{
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
}