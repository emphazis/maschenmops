<?php 

namespace Swage\Cli\Helper;

class CustomFilterIterator extends \RecursiveFilterIterator {


    protected $files = [
        '.serverless',
        '.gitlab-ci',
        '.git',
        '.dockerignore',
        '.editorconfig',
        'vendor',
        'cartifacts',
        'composer.lock',
        'docker-compose.yml',
        'Dockerfile',
    ];

    protected $extensions = ['md']; 
    
    protected $paths = [];

    public function setOptions(array $options): void 
    {
        $this->files = (isset($options['files'])) ? $options['files'] : [];

        $this->paths = (isset($options['paths'])) ? $options['paths'] : [];

        $this->extensions = (isset($options['extensions'])) ? $options['extensions'] : [];
    }


    public function accept() {

        $fileInfo = $this->current();
        
        // Filter extension
        $extension = $fileInfo->getExtension();
        if (in_array($extension, $this->extensions, true)) {
            return false;
        }

        // Filter path
        $currentPath = strtolower($fileInfo->getRealPath());
        foreach ($this->paths as $path) {
            if (strpos($currentPath, $path) > 0) {
                echo "Path exclusion: ". $currentPath . PHP_EOL;
                return false;
            }
        }

        // Filter folders
        if (in_array($fileInfo->getBasename(), $this->files)) {
            echo "File name exclusion: ". $fileInfo->getPathname() . PHP_EOL;
            return false;
        }


        return true;
    }

}