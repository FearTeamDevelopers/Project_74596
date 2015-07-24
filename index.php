<?php

//define environment
if(preg_match('#^.*\.dev$#i',$_SERVER['SERVER_NAME'])){
    defined('ENV')? null : define('ENV', 'dev');
}elseif(preg_match('#^.*\.fear-team\.cz$#i', $_SERVER['SERVER_NAME'])){
    defined('ENV')? null : define('ENV', 'qa');
}elseif(preg_match('#^dev\..*\.cz$#i', $_SERVER['SERVER_NAME'])){
    defined('ENV')? null : define('ENV', 'qa');
}else{
    defined('ENV')? null : define('ENV', 'live');
}

defined('APP_PATH')? null : define('APP_PATH', realpath(dirname(__FILE__)));
defined('MODULES_PATH')? null : define('MODULES_PATH', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'modules'));

if (ENV == 'dev') {
    error_reporting(E_ALL);
    ini_set('opcache.enable', '0');
} else {
    error_reporting(0);
}

//check PHP version
if (version_compare(phpversion(), '5.4', '<')) {
    header('Content-type: text/html');
    include(APP_PATH . '/phpversion.phtml');
    exit();
}

//xdebug profiler
//setcookie('XDEBUG_PROFILE', 1, time()+1800);
//setcookie('XDEBUG_PROFILE', '', time()-1800);

//register modules
$modules = array('App', 'Admin', 'Search', 'Cron');

//core
require(APP_PATH.'/vendors/thcframe/core/core.php');
THCFrame\Core\Core::initialize($modules);

//plugins
$path = APP_PATH . '/application/plugins';
$iterator = new \DirectoryIterator($path);

foreach ($iterator as $item) {
    if (!$item->isDot() && $item->isDir()) {
        include($path . '/' . $item->getFilename() . '/initialize.php');
    }
}

//internal profiler
$profiler = THCFrame\Profiler\Profiler::getInstance();
$profiler->start();

// load services and run dispatcher
THCFrame\Core\Core::run();