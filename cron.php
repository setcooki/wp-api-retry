#!/usr/local/bin/php
<?php

global $argv;

if(strtolower(trim(php_sapi_name())) !== 'cli')
{
    echo "script can only be called from command line";
    exit(0);
}

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');
if(preg_match('=\-\-debug=i', implode(' ', $argv)))
{
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
}else{
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

define('SHORTINIT', false);
define('WP_USE_THEMES', false);
if(!defined('DIRECTORY_SEPARATOR'))
{
    define('DIRECTORY_SEPARATOR', ((isset($_ENV['OS']) && strpos('win', $_ENV['OS']) !== false) ? '\\' : '/'));
}
if(!defined('ABSPATH'))
{
    if(isset($_SERVER['PWD']) && !empty($_SERVER['PWD']) && preg_match('=wp-content.*=i', $_SERVER['PWD'])){
        $path = rtrim(preg_replace('=wp-content.*=i', '', $_SERVER['PWD']), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }else if(isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME']) && preg_match('=wp-content.*=i', $_SERVER['SCRIPT_NAME'])){
        $path = rtrim(preg_replace('=wp-content.*=i', '', $_SERVER['SCRIPT_NAME']), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }else if(isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']) && preg_match('=wp-content.*=i', $_SERVER['SCRIPT_FILENAME'])){
        $path = rtrim(preg_replace('=wp-content.*=i', '', $_SERVER['SCRIPT_FILENAME']), ' ' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }else{
        $path = dirname(__FILE__) . '/../../../../';
    }
    define('ABSPATH', $path);
}

require_once ABSPATH . 'wp-load.php';
if(!defined('WPINC'))
{
    define('WPINC', 'wp-includes');
}
require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'l10n.php';
require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'plugin.php';
require_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'functions.php';
require_once dirname(__FILE__) . '/lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/inc/globals.php';
require_once dirname(__FILE__) . '/inc/functions.php';


$args = [];
if($argv)
{
    foreach((array)$argv as $arg)
    {
        $arg = trim($arg);
        if(substr($arg, 0, 2) === '--')
        {
            if(stripos($arg, '=') !== false){
                $arg = explode('=', $arg);
                $args[substr($arg[0], 2)] = $arg[1];
            }else{
                $args[substr($arg, 2)] = null;
            }
        }
    }
}

$plugin = \Setcooki\Wp\Api\Retry\Plugin::getInstance();
$provider = (array_key_exists('provider', $args)) ? trim($args['provider']) : null;

try
{
    $plugin->cron($provider)->purge($provider);
}
catch(\Setcooki\Wp\Api\Retry\Exception $e)
{
    $plugin->purge($provider);
    echo $e->getMessage();
    exit(0);
}
catch(\Exception $e)
{
    $plugin->purge($provider);
}