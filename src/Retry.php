<?php

namespace Setcooki\Wp\Api\Retry;

/**
 * Class Retry
 * @package Setcooki\Wp\Api\Retry
 */
abstract class Retry
{
    /**
     * @param $status
     * @param $provider
     * @param $method
     * @param $url
     * @param $request
     * @param null $message
     * @param null $callback
     * @return int
     * @throws Exception
     */
    public static function push($status, $provider, $method, $url, $request, $message = null, $callback = null)
    {
        global $wpdb;

        if(is_array($request) || is_object($request))
        {
            $request = json_encode($request);
        }

        $provider = trim($provider);
        $method = trim($method);
        $url = trim($url);
        $message = trim((string)$message);
        $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
        $hash = md5(sprintf('%s%s%s%s', $provider, $method, $url, $request));

        if((int)$status === API_RETRY_STATUS_SUCCESS)
        {
            return (int)$wpdb->query($wpdb->prepare("UPDATE `".API_RETRY_TABLE_NAME."` SET `timestamp` = %d, `timestamp` = %s WHERE `provider` = %s AND `hash` = %s LIMIT 1", [API_RETRY_STATUS_SUCCESS, $timestamp, $provider, $hash]));
        }else if((int)$status === API_RETRY_STATUS_FAILURE){
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".API_RETRY_TABLE_NAME."` WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [$provider, $hash, API_RETRY_STATUS_QUEUE]));
            if(is_object($result))
            {
                if($result->tries >= API_RETRY_MAX_TRIES)
                {
                    if((bool)API_RETRY_REPORT && API_RETRY_EMAIL_RECIPIENTS)
                    {
                        $recipients = preg_split('=\s*\,\s*=i', trim(API_RETRY_EMAIL_RECIPIENTS, ' ,'));
                        if(!empty($recipients))
                        {
                            api_retry_report($recipients, $provider, $method, $url, $request);
                        }
                    }
                    return (int)$wpdb->query($wpdb->prepare("UPDATE `".API_RETRY_TABLE_NAME."` SET `status` = %d, `timestamp` = %s  WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [API_RETRY_STATUS_FAILURE, $timestamp, $provider, $hash, API_RETRY_STATUS_QUEUE]));
                }else{
                    return (int)$wpdb->query($wpdb->prepare("UPDATE `".API_RETRY_TABLE_NAME."` SET `tries` = `tries` + %d, `message` = %s, `timestamp` = %s WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [1, $message, $timestamp, $provider, $hash, API_RETRY_STATUS_QUEUE]));
                }
            }else{
                return (int)$wpdb->query($wpdb->prepare("INSERT INTO `".API_RETRY_TABLE_NAME."` (`provider`, `method`, `url`, `request`, `hash`, `message`, `timestamp`, `created`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", [
                    $provider,
                    $method,
                    $url,
                    $request,
                    $hash,
                    $message,
                    $timestamp,
                    $timestamp
                ]));
            }
        }else{
            throw new Exception(sprintf(__('Status: %d not implemented'), (int)$status));
        }
    }


    /**
     * @param $provider
     * @param $method
     * @param $url
     * @param $request
     * @param null $failure
     * @param null $callback
     * @return mixed
     */
    public static function failure($provider, $method, $url, $request, $failure = null, $callback = null)
    {
        return call_user_func_array([__CLASS__, 'push'], array_merge([API_RETRY_STATUS_FAILURE], func_get_args()));
    }


    /**
     * @param $provider
     * @param $method
     * @param $url
     * @param $request
     * @param null $success
     * @param null $callback
     * @return mixed
     */
    public static function success($provider, $method, $url, $request, $success = null, $callback = null)
    {
        return call_user_func_array([__CLASS__, 'push'], array_merge([API_RETRY_STATUS_SUCCESS], func_get_args()));
    }


    /**
     * @param int $status
     * @param null $provider
     * @param null $method
     * @return array|object|null
     */
    public static function pull($status = API_RETRY_STATUS_QUEUE, $provider = null, $method = null)
    {
        global $wpdb;

        $q = [];
        $query = [];
        $query[] = sprintf("SELECT * FROM `%s`", API_RETRY_TABLE_NAME);
        $query[] = sprintf("WHERE `status` = %d", (int)$status);
        if(!empty($provider))
        {
            $q[] = sprintf("`provider` = '%s'", $provider);
        }
        if(!empty($method))
        {
            $q[] = sprintf("`method` = '%s'", $method);
        }
        if(!empty($q))
        {
           $query[] = sprintf('AND %s ', implode(' AND ', $q));
        }

        return $wpdb->get_results(implode(" ", $query), OBJECT_K);
    }


    /**
     * @param null $provider
     * @return bool
     */
    public static function purge($provider = null)
    {
        global $wpdb;

        $ts = time();
        $last = get_option('api_retry_last_purge', false);
        if(is_numeric($last))
        {
            $last = (int)$last;
        }else{
            $last = PHP_INT_MAX;
        }

        if($last < strtotime(sprintf('-%d day', (int)API_RETRY_PURGE_INTERVAL), $ts))
        {
            $sql[] = sprintf("SELECT * FROM `".API_RETRY_TABLE_NAME."` WHERE `status` != %d AND UNIX_TIMESTAMP(`timestamp`) < %d", API_RETRY_STATUS_QUEUE, $last);
            if(!empty($provider))
            {
                $sql[] = sprintf("AND `provider` = '%s'", $provider);
            }
            $sql[] = 'ORDER BY `timestamp` DESC';
            $result = $wpdb->get_results(implode(' ', $sql));
            if(!empty($result) && (bool)API_RETRY_PURGE_REPORT && API_RETRY_EMAIL_RECIPIENTS)
            {
                $logs = [];
                $recipients = preg_split('=\s*\,\s*=i', trim(API_RETRY_EMAIL_RECIPIENTS, ' ,'));
                if(!empty($recipients))
                {
                    foreach($result as $res)
                    {
                        $log = '';
                        $args =
                        [
                            date('Y-m-d H:i:s', strtotime($res->timestamp)),
                            $res->provider,
                            $res->method,
                            $res->url,
                            (!empty($res->request)) ? trim($res->request) : '',
                            (!empty($res->message)) ? trim($res->message) : '',
                            $res->tries
                        ];
                        if((int)$res->status === API_RETRY_STATUS_FAILURE)
                        {
                            $log = apply_filters('api_retry_purge_failure_log', '[%s] %s:%s to: %s with request [%s] failed ("%s") after %d tries', $args);
                        }else if((int)$res->status === API_RETRY_STATUS_SUCCESS) {
                            $log = apply_filters('api_retry_purge_success_log', '[%s] %s:%s to: %s with request [%s] succeeded ("%s") after %d tries', $args);
                        }
                        if(!empty($log))
                        {
                            $logs[] = vsprintf($log, $args);
                        }
                    }
                    if(sizeof($logs) > 0)
                    {
                        $message = "";
                        $message .= sprintf(__("The following retries will be purged from the queue:"), API_RETRY_MAX_TRIES);
                        $message .= "\n\n";
                        foreach($logs as $log)
                        {
                            $message .= $log . PHP_EOL;
                        }
                        wp_mail($recipients, __('API retry purge report'), $message);
                    }
                }
            }
            if(!empty($provider))
            {
                $wpdb->query($wpdb->prepare("DELETE FROM `".API_RETRY_TABLE_NAME."` WHERE `provider` = %s AND `status` != %d", [$provider, API_RETRY_STATUS_QUEUE]));
            }else{
                $wpdb->query($wpdb->prepare("DELETE FROM `".API_RETRY_TABLE_NAME."` WHERE `status` != %d", [API_RETRY_STATUS_QUEUE]));
            }
            update_option('api_retry_last_purge', $ts);
        }

        return true;
    }
}