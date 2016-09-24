<?php

namespace ksr\classes;


interface IFileHandler
{
    public function open(string $dir) : bool;

    public function list(string $dir = '') : array;

    public function rename(string $oldName, string $newName) : bool;

    public function parent() : bool;

    public function getOpts() : bool;

}