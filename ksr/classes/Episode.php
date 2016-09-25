<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 25.09.2016
 * Time: 18:10
 */

namespace ksr\classes;


class Episode extends ARenamer
{

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