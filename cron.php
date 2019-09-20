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
ini_set('memory_limit', '2048M');
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

$plugin = \Setcooki\Wp\Api\Retry\Plugin::getInstance();
$plugin->cron();