<?php

namespace ksr\classes;


abstract class ARenaimer
{
    const RENAME_TAG = '(R)';
    protected $fileHandler = null;
    private $path = '';

    /**
     * SerialRenaimer constructor.
     * @param IFileHandler $fileHandler
     */
    public function __construct(IFileHandler $fileHandler)
    {
        if (is_null($this->fileHandler))
            $this->fileHandler = $fileHandler;
    }

    protected function isNew(string $fileName)
    {
        return !strpos($fileName, self::RENAME_TAG);
    }

    /**
     * @param string $path
     */
    protected function setPath($path)
    {
        $this->path = $path;
    }


    protected function getFullPath(string $dir) : string
    {
        return $this->path . '/' . $dir . '/';
    }

}