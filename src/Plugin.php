<?php

namespace Setcooki\Wp\Api\Retry;

use Setcooki\Wp\Api\Retry\Traits\Singleton;

/**
 *
 */
class Plugin
{
    use Singleton;

    /**
     * @var null
     */
    public static $options = [];


    /**
     * Plugin constructor.
     * @param null $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        if(is_array($options))
        {
           static::$options = array_merge(static::$options, $options);
        }
        $this->setup();
    }


    /**
     * @throws \Exception
     */
    protected function setup()
    {
    }


    /**
     * @throws \Exception
     */
    public function init()
    {
        if(API_RETRY_DB_VERSION !== get_option('api_retry_db_version', ''))
        {
            $this->activate();
        }
        add_action('api_retry_push', ['\Setcooki\Wp\Api\Retry\Retry', 'push'], 10, 4);
    }


    /**
     * @throws \Exception
     */
    public function activate()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $collation = 'utf8_unicode_ci';
        $charset_collate = '';
        if(!empty($wpdb->charset))
        {
            $charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if(!empty($wpdb->collate))
        {
            if(stripos($wpdb->charset, 'utf8mb4') !== false)
            {
                $charset_collate .= " COLLATE utf8mb4_unicode_ci";
                $collation = 'utf8mb4_unicode_ci';
            }else{
                $charset_collate .= " COLLATE utf8_unicode_ci";
            }
        }

        $sql = "
        CREATE TABLE ".API_RETRY_TABLE_NAME." (
          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          provider VARCHAR(50) COLLATE {$collation} NOT NULL,
          method VARCHAR(25) COLLATE {$collation} NOT NULL,
          url TEXT COLLATE {$collation} NOT NULL,
          request TEXT COLLATE {$collation} NOT NULL,
          hash CHAR(32) {$collation} NOT NULL,
          status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
          tries TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
          timestamp datetime NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=MyISAM {$charset_collate};
        ";

        dbDelta($sql);

        do_action('api_retry_activate', $this);

        update_option('api_retry_db_version', API_RETRY_DB_VERSION);
    }


    /**
     *
     */
    public function deactivate()
    {
    }


    /**
     *
     */
    public static function uninstall()
    {
        global $wpdb;

        if(!defined('WP_UNINSTALL_PLUGIN'))
        {
            exit();
        }
        $wpdb->query( "DROP TABLE IF EXISTS ".API_RETRY_TABLE_NAME."");
        delete_option("api_retry_db_version");
    }


    /**
     *
     */
    public function cron()
    {
        $data = Retry::pull();
        foreach($data as $d)
        {
            do_action('api_retry_do', $d, $this);
        }
    }
}