<?php

namespace Setcooki\Wp\Api\Retry;

use Setcooki\Wp\Api\Retry\Traits\Singleton;

/**
 * Class Plugin
 * @package Setcooki\Wp\Api\Retry
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
        if (is_array($options)) {
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
        if (API_RETRY_DB_VERSION !== get_option('api_retry_db_version', '')) {
            $this->activate();
        }

        if (is_admin()) {
            (new Settings($this))->init();
        }
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
        if (!empty($wpdb->charset)) {
            $charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            if (stripos($wpdb->charset, 'utf8mb4') !== false) {
                $charset_collate .= " COLLATE utf8mb4_unicode_ci";
                $collation = 'utf8mb4_unicode_ci';
            } else {
                $charset_collate .= " COLLATE utf8_unicode_ci";
            }
        }

        $sql = "
        CREATE TABLE " . API_RETRY_TABLE_NAME . " (
          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          provider VARCHAR(50) COLLATE {$collation} NOT NULL,
          method VARCHAR(25) COLLATE {$collation} NOT NULL,
          service TEXT COLLATE {$collation} NOT NULL,
          endpoint TEXT COLLATE {$collation} NOT NULL,
          request TEXT COLLATE {$collation} NOT NULL,
          hash CHAR(32) COLLATE {$collation} NOT NULL,
          status TINYINT(1) NOT NULL DEFAULT '0',
          tries TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
          failure TEXT COLLATE {$collation} NOT NULL,
          success TEXT COLLATE {$collation} NOT NULL,
          decode TINYINT(1) NOT NULL DEFAULT '0',
          custom TEXT COLLATE {$collation} NOT NULL,
          timestamp datetime NOT NULL,
          created datetime NOT NULL,
          PRIMARY KEY  (id),
          KEY provider (provider),
          KEY hash  (hash)
        ) ENGINE=MyISAM {$charset_collate};
        ";

        dbDelta($sql);

        do_action('api_retry_activate', $this);

        update_option('api_retry_db_version', API_RETRY_DB_VERSION);
        update_option('api_retry_last_purge', time());
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

        if (!defined('WP_UNINSTALL_PLUGIN')) {
            exit();
        }
        $wpdb->query("DROP TABLE IF EXISTS " . API_RETRY_TABLE_NAME . "");
        delete_option("api_retry_db_version");
        delete_option("api_retry_last_purge");
    }


    /**
     * @param null $provider
     * @return $this
     */
    public function cron($provider = null)
    {
        $options = json_decode(get_option('wpar_options', new \stdClass()));

        if (!empty($options) && isset($options->enabled) && (int)$options->enabled === 0) {
            return $this;
        }

        $data = Retry::pull(API_RETRY_STATUS_QUEUE, $provider);
        foreach ($data as $d) {
            try {
                do_action('api_retry_do', $d, $this);
            } catch (\Exception $e) {
                Retry::failure($d->provider, $d->method, $d->service, $d->endpoint, $d->request, $e->getMessage());
            }
        }

        return $this;
    }


    /**
     * @param null $provider
     * @return $this
     */
    public function purge($provider = null)
    {
        $options = json_decode(get_option('wpar_options', new \stdClass()));

        if (!empty($options) && isset($options->enabled) && (int)$options->enabled === 0) {
            return $this;
        }

        Retry::purge($provider);

        return $this;
    }
}