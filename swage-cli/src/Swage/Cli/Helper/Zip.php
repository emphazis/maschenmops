<?php 

namespace Swage\Cli\Helper;

use ZipArchive;

class Zip 
{
    protected $archive;

    protected $pathToArchive;

    public function __construct($pathToArchive)
    {
        $this->archive = new ZipArchive();
        $this->pathToArchive = $pathToArchive;

        $this->open();
    }
    
    
    public function open() 
    {
        $this->archive->open($this->pathToArchive, ZipArchive::CREATE);
    }

    public function close() 
    {
        $this->archive->close();
    }

    /**
     * @var string|array $files
     * @var string $localName
     *
     * @return \Swage\Cli\Helper\Zip
     */
    public function add($file, string $baseFolder = ""): void
    {
        $localName = str_replace($baseFolder, "", $file->getRealPath());

        if ($file->isDir()) {
            $this->archive->addEmptyDir($localName);
        }

        if ($file->isFile()) {
            $this->archive->addFile($file->getRealPath(), $localName);
        }

    }
}