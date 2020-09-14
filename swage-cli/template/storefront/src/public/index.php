<?php

use Doctrine\DBAL\DBALException;
use PackageVersions\Versions;
use Shopware\Core\Framework\Adapter\Cache\CacheIdLoader;
use Shopware\Core\Framework\Event\BeforeSendResponseEvent;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\DbalKernelPluginLoader;
use Shopware\Core\Framework\Routing\RequestTransformerInterface;
use Shopware\Production\HttpKernel;
use Shopware\Production\Kernel;
use Shopware\Storefront\Framework\Cache\CacheStore;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Doctrine\DBAL\Connection;

function dirToArray($dir) {
  
    $result = array();
 
    $cdir = scandir($dir);
    foreach ($cdir as $key => $value)
    {
       if (!in_array($value,array(".","..")))
       {
          if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
          {
             $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
          }
          else
          {
             $result[] = $value;
          }
       }
    }
   
    return $result;
}

if (getenv('DIR')) {
    print_r(dirToArray(getenv('DIR')));
}

$taskRoot = getenv('LAMBDA_TASK_ROOT');
$appRoot = isset($_SERVER['PROJECT_ROOT']) ? $_SERVER['PROJECT_ROOT'] : '/opt/src';
if (! isset($_SERVER['PROJECT_ROOT'])) { $_SERVER['PROJECT_ROOT'] = $appRoot; }

require $appRoot.'/config/bootstrap.php';

if (PHP_VERSION_ID < 70200) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    
    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 6 requires at least PHP 7.2.0';
    exit();
}

if (getenv('SWAGE_AUTOLOAD_PATH')) {
    /** @noinspection PhpIncludeInspection */
    $classLoader = require getenv('SWAGE_AUTOLOAD_PATH');
} else {
    /** @noinspection PhpIncludeInspection */
    $classLoader = require $appRoot . '/vendor/autoload.php';
}

if (is_file('/tmp/files/update/update.json') || is_dir('/tmp/update-assets')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 1200');
    if (file_exists(__DIR__ . '/maintenance.html')) {
        readfile(__DIR__ . '/maintenance.html');
    } else {
        readfile(__DIR__ . '/recovery/update/maintenance.html');
    }

    return;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}


$kernel = new HttpKernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG'], $classLoader);
$request = Request::createFromGlobals();
$result = $kernel->handle($request);
$result->getResponse()->send();
$kernel->terminate($request, $result->getResponse());
