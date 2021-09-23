<?php

global $wpdb;

define('API_RETRY_TABLE_NAME', $wpdb->prefix . 'api_retry');
define('API_RETRY_STATUS_FAILURE', -1);
define('API_RETRY_STATUS_QUEUE', 0);
define('API_RETRY_STATUS_SUCCESS', 1);
if (!defined('API_RETRY_MAX_TRIES')) {
    define('API_RETRY_MAX_TRIES', 3);
}
if (!defined('API_RETRY_EMAIL_RECIPIENTS')) {
    define('API_RETRY_EMAIL_RECIPIENTS', '');
}
if (!defined('API_RETRY_PURGE_INTERVAL')) {
    define('API_RETRY_PURGE_INTERVAL', 7); //in days
}
if (!defined('API_RETRY_REPORT')) {
    define('API_RETRY_REPORT', false);
}
if (!defined('API_RETRY_PURGE_REPORT')) {
    define('API_RETRY_PURGE_REPORT', true);
}