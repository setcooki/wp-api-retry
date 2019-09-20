<?php

global $wpdb;

define('API_RETRY_TABLE_NAME', $wpdb->prefix . 'api_retry');
if(!defined('API_RETRY_MAX_TRIES'))
{
    define('API_RETRY_MAX_TRIES', 3);
}
if(!defined('API_RETRY_EMAIL_RECIPIENTS'))
{
    define('API_RETRY_EMAIL_RECIPIENTS', 'set@cooki.me');
}