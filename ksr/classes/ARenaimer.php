<?php

namespace ksr\classes;


abstract class ARenamer
{
    const ONE_SEASON = TRUE;
    const RENAME_TAG = '(R)';
    const STATUS = [
        'SKIP' => -1,
        'IS_FILE' => 0,
        'RENAMED' => 1
    ];

    protected $extensions = [];
    protected $currentPath = '';
    protected $serials = [];
    protected $serial = [];
    protected $seasons = [];
    protected $season = [];
    protected $seasonNum = '01';
    protected $extension = '';

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
        return $this->path . '/' . $this->fileHandler->getOpts()['dir'] . '/' . $dir;
    }

}