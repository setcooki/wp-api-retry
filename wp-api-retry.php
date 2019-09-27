<?php
/*
Plugin Name: WP Api Retry
Plugin URI: https://github.com/setcooki/wp-api-retry
Description: Wordpress Plugin for queueing and retrying failed external api calls
Author: Frank Mueller <set@cooki.me>
Author URI: https://github.com/setcooki/
Issues: https://github.com/setcooki/wp-api-retry/issues
Text Domain: wp-minio-sync
Version: 0.0.5
*/

if(!defined('API_RETRY_DOMAIN'))
{
    define('API_RETRY_DOMAIN', 'wp-api-retry');
}
define('API_RETRY_DIR', dirname(__FILE__));
define('API_RETRY_NAME', basename(__FILE__, '.php'));
define('API_RETRY_FILE', __FILE__);
define('API_RETRY_URL', plugin_dir_url(API_RETRY_FILE));
define('API_RETRY_DB_VERSION', '0.0.1');

if(!function_exists('api_retry'))
{
    function api_retry()
    {
        try
        {
            require dirname(__FILE__) . '/lib/vendor/autoload.php';
            require_once API_RETRY_DIR . '/inc/globals.php';
            if(is_file(API_RETRY_DIR . '/inc/functions.php'))
            {
                require_once API_RETRY_DIR . '/inc/functions.php';
            }
            if(is_file(API_RETRY_DIR . '/inc/api.php'))
            {
                require_once API_RETRY_DIR . '/inc/api.php';
            }
            $plugin = \Setcooki\Wp\Api\Retry\Plugin::getInstance();
            register_activation_hook(__FILE__, array($plugin, 'activate'));
            register_deactivation_hook(__FILE__, array($plugin, 'deactivate'));
            register_uninstall_hook(__FILE__, array(get_class($plugin), 'uninstall'));
            add_action('api_retry_failure', ['\Setcooki\Wp\Api\Retry\Retry', 'failure'], 10, 6);
            add_action('api_retry_success', ['\Setcooki\Wp\Api\Retry\Retry', 'success'], 10, 6);
            add_action('init', function() use ($plugin)
            {
                $plugin->init();
            });
        }
        catch(Exception $e)
        {
            @file_put_contents(ABSPATH . 'wp-content/logs/debug.log', $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
api_retry();
