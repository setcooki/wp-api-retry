<?php

namespace Setcooki\Wp\Api\Retry;

/**
 * Class Settings
 * @package Setcooki\Wp\Api\Retry
 */
class Settings
{
    /**
     * @var Plugin|null
     */
    public $plugin = null;


    /**
     * @var null|\stdClass
     */
    public $menu = null;


    /**
     *
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->menu = new \stdClass();
    }


    /**
     *
     */
    public function init()
    {
        if (is_admin()) {
            add_filter('plugin_action_links_wp-api-retry/wp-api-retry.php', [$this, 'adminLink']);
            add_action('admin_menu', function () {
                add_options_page
                (
                    'API Retry Settings',
                    'API Retry',
                    'manage_options',
                    'api-retry',
                    [$this, 'adminMenu']
                );
            });
        }
    }


    /**
     *
     */
    public function adminMenu()
    {
        $options = json_decode(get_option('wpar_options', new \stdClass()));
        $tries = API_RETRY_MAX_TRIES;
        if (!empty($options) && isset($options->max_tries)) {
            $tries = $options->max_tries;
        }

        $this->menu->orderby = $_REQUEST['orderby'] ?: '';
        $this->menu->order = $_REQUEST['order'] ?: 'desc';

        if (strtolower($_SERVER['REQUEST_METHOD']) === 'get') {
            if (isset($_GET['try']) && !empty($_GET['try'])) {
                $this->tryItem($_GET['try']);
            }
        } else if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
            $options = new \stdClass();
            $options->enabled = (isset($_POST['enabled']) && (int)$_POST['enabled'] === 1) ? 1 : 0;
            $options->purge_interval = (int)$_POST['purge_interval'];
            $options->max_tries = (int)$_POST['max_tries'];
            update_option('wpar_options', json_encode($options));
        }

        $options = json_decode(get_option('wpar_options', new \stdClass()));
        if (empty($options) || (!empty($options) && !isset($options->enabled))) {
            $options->enabled = 0;
        }
        if (empty($options) || (!empty($options) && !isset($options->purge_interval))) {
            $options->purge_interval = API_RETRY_PURGE_INTERVAL;
        }
        if (empty($options) || (!empty($options) && !isset($options->max_tries))) {
            $options->max_tries = API_RETRY_MAX_TRIES;
        }
        $items = $this->getItems();

        ob_start();
        require_once API_RETRY_DIR . '/templates/admin/admin.php';
        echo ob_get_clean();
    }


    /**
     * @param $links
     * @return mixed
     */
    public function adminLink($links)
    {
        $url = esc_url(add_query_arg
        (
            'page',
            'api-retry',
            get_admin_url() . 'admin.php'
        ));
        array_push($links, sprintf('<a href="%s" title="%s">%s</a>', $url, __('Settings', API_RETRY_DOMAIN), __('Settings', API_RETRY_DOMAIN)));
        return $links;
    }


    /**
     * @return array
     */
    protected function getItems()
    {
        global $wpdb;

        $offset = 0;
        $orderby = $_REQUEST['orderby'] ?: 'timestamp';
        $order = $_REQUEST['order'] ?: 'desc';

        return (array)$wpdb->get_results(sprintf("SELECT * FROM " . API_RETRY_TABLE_NAME . " ORDER BY %s %s, timestamp ASC LIMIT %d, 50", $orderby, $order, $offset));
    }


    /**
     * @param $id
     * @return array|object|void|null
     */
    protected function getItem($id)
    {
        global $wpdb;

        return $wpdb->get_row(sprintf("SELECT * FROM " . API_RETRY_TABLE_NAME . " WHERE id = %d LIMIT 1", (int)$id));
    }


    /**
     * @param $id
     * @return bool
     */
    protected function tryItem($id)
    {
        $item = $this->getItem($id);
        if (!empty($item) && (int)$item->status !== 1) {
            try {
                do_action('api_retry_do', $item, $this->plugin);
            } catch (\Exception $e) {
            }
            return true;
        }
        return false;
    }
}