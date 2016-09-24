<?php

namespace ksr\classes;


use Noodlehaus\Config;

interface IFileHandler
{
    public function open(string $dir) : bool;

    public function list(string $dir = '');

    public function rename(string $oldName, string $newName) : bool;

    public function parent() : bool;

    public function getOpts() : Config;

}