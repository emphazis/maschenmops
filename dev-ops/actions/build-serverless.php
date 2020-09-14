#!/usr/bin/php

<?php 

require __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Filesystem\Filesystem;
$fileSystem = new Filesystem();

class CustomFilterIterator extends \RecursiveFilterIterator {
    public static $folder_names = [
        '.serverless',
        '.gitlab-ci',
        '.git',
        '.dockerignore',
        '.editorconfig',
        '.env',
        '.env.dist',
        'vendor',
        'cartifacts',
        'composer.lock',
        'docker-compose.yml',
        'Dockerfile',
    ];

    public static $line_endings = ['md']; public static $paths = [];


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

        // Filter folders
        if (in_array($fileInfo->getBasename(), static::$folder_names)) {
            echo "File name exclusion: ". $fileInfo->getPathname() . PHP_EOL;
            return false;
        }


        return true;
    }

}

$persist = (isset($argv[1]) && $argv[1] === "force") ? false : true;

$cwd = dirname(__FILE__);
$project = realpath($cwd . "/../..");
echo $project. PHP_EOL;

echo "Clean any cartifacts..." . PHP_EOL;
if (! $persist && is_dir("$project/cartifacts")) $fileSystem->remove(["$project/cartifacts"]);

// Register copy function
$copy = function ($src, $target) use ($fileSystem) {
        
    $iterator = new \RecursiveIteratorIterator(
                    new CustomFilterIterator(new \RecursiveDirectoryIterator($src)),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
    
    $fileSystem->mirror($src, $target, $iterator);

};

// /cartifacts/storefront/composer.json
$storefront = '
{
    "name": "storefront/artifact",
    "type": "project",
    "license": "MIT",
    "config": {
        "optimize-autoloader": true
    },
    "prefer-stable": true,
    "minimum-stability": "stable",
    "autoload": {
        "psr-4": {
            "Shopware\\\\Production\\\\": "src\/"
        }
    },
    "repositories": [
        {
            "type": "path",
            "url": "custom\/static-plugins\/*",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "php": "~7.2",
        "shopware\/core": "~v6.2",
        "shopware\/storefront": "~v6.2",
        "bref\/bref": "^0.5.26",
        "ocramius\/package-versions": "1.4.0"
    },
    "replace": {
        "google\/apiclient": "*",
        "google\/cloud-storage": "*"
    }
}';

// /cartifacts/administration/composer.json
$administration = '
{
    "name": "administration/artifact",
    "type": "project",
    "license": "MIT",
    "config": {
        "optimize-autoloader": true
    },
    "prefer-stable": true,
    "minimum-stability": "stable",
    "autoload": {
        "psr-4": {
            "Shopware\\\\Production\\\\": "src\/"
        }
    },
    "repositories": [
        {
            "type": "path",
            "url": "custom\/static-plugins\/*",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "php": "~7.2",
        "shopware\/core": "~v6.2",
        "shopware\/administration": "~v6.2",
        "bref\/bref": "^0.5.26",
        "ocramius\/package-versions": "1.4.0"
    },
    "replace": {
        "google\/apiclient": "*",
        "google\/cloud-storage": "*"
    }
}';

$paths = [
    'storefront' => ['composerContent' => $storefront],
    'administration' => ['composerContent' => $administration]
];

foreach ($paths as $path => $options) {

    $path = $project. "/cartifacts/" . $path;
    echo "Install: " . $path . PHP_EOL;

    if (! $persist) {
        // Create directory if not exists
        if (! is_dir($path)) $fileSystem->mkdir($path);
        
        // Copy cwd to artifect directory 
        $copy($project, $path);
    }

    $method = (file_exists("$path/composer.json")) ? "update" : "install";

    // Save composer.json in artifact directory
    file_put_contents("$path/composer.json", $options['composerContent']);

    // Run composer install
    $output = shell_exec("composer $method -d $path --prefer-dist --no-dev -o");

}