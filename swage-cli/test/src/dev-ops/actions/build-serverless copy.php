#!/usr/bin/php

<?php 

require __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Filesystem\Filesystem;
$fileSystem = new Filesystem();

$cwd = dirname(__FILE__);
echo $cwd . PHP_EOL;

$project = realpath($cwd . "/../..");
echo $project. PHP_EOL;

$artifacts = $project."/bartifacts";
echo $artifacts . PHP_EOL;

if (is_dir($artifacts)) $fileSystem->remove([$artifacts]);

$fileSystem->mkdir($artifacts, 0777, true);

$flags = [];

class CustomFilterIterator extends \RecursiveFilterIterator {

    public static $folder_names = [
        'bartifacts',
        '.serverless',
        '.gitlab-ci',
        '.git',
        'node_modules',
        'Tests',
        'tests',
        'Test',
        'test',
    ];

    public static $line_endings = [
        'pdf',
        'md',
        'scss',
        'sass',
        'ts',
        'vue',
        'zip'
    ];

    public static $paths = [
        'shopware\administration\resources\app\administration\build',
        'shopware\administration\resources\app\administration\config',
        'shopware\administration\resources\app\administration\src',
        'shopware\recovery',
        'shopware\storefront\resources\app\storefront\build',
        'shopware\storefront\resources\app\storefront\src',
        'shopware\storefront\resources\app\storefront\test',
        'shopware\storefront\resources\app\storefront\vendor',
    ];


    public function accept() {

        $fileInfo = $this->current();
        
        // Filter extension
        $extension = $fileInfo->getExtension();
        if (in_array($extension, static::$line_endings, true)) {
            echo "Extension exclusion: ". $extension . PHP_EOL;
            return false;
        }

        // Filter path
        $currentPath = strtolower($fileInfo->getRealPath());
        foreach (static::$paths as $path) {
            if (strpos($currentPath, $path) > 0) {
                echo "Path exclusion: ". $currentPath . PHP_EOL;
                return false;
            }
        }


        if ($fileInfo->isDir()) {
            // Filter folders
            if (in_array($fileInfo->getBasename(), static::$folder_names)) {
                echo "Folder name exclusion: ". $fileInfo->getPathname() . PHP_EOL;
                return false;
            }
        }


        return true;
    }

}

$directoryIterator =  new \RecursiveDirectoryIterator($project);
$filterIterator = new CustomFilterIterator($directoryIterator);

$iterator = new \RecursiveIteratorIterator(
               $filterIterator,
                \RecursiveIteratorIterator::SELF_FIRST
            );

$fileSystem->mirror($project, $artifacts, $iterator);
