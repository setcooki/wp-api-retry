<?php

namespace Setcooki\Wp\Api\Retry;

/**
 * Class Retry
 * @package Setcooki\Wp\Api\Retry
 */
abstract class Retry
{
    /**
     * @param $provider
     * @param $method
     * @param $url
     * @param $request
     * @param null $callback
     * @return int
     */
    public static function push($provider, $method, $url, $request, $callback = null)
    {
        global $wpdb;

        if(is_array($request) || is_object($request))
        {
            $request = json_encode($request);
        }

        $provider = trim($provider);
        $method = trim($method);
        $url = trim($url);
        $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
        $hash = md5(sprintf('%s-%s-%s', $method, $url, $request));

        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".API_RETRY_TABLE_NAME."` WHERE `provider` = %s AND `hash` = %s LIMIT 1", [$provider, $hash]));
        if(is_object($result))
        {
            if($result->tries >= API_RETRY_MAX_TRIES)
            {
                if(API_RETRY_EMAIL_RECIPIENTS)
                {
                    $recipients = preg_split('=\s*\,\s*=i', trim(API_RETRY_EMAIL_RECIPIENTS, ' ,'));
                    if(!empty($recipients))
                    {
                        api_retry_report($recipients, $provider, $method, $url, $request);
                    }
                }
                return (int)$wpdb->query($wpdb->prepare("DELETE FROM `".API_RETRY_TABLE_NAME."` WHERE `provider` = %s AND `hash` = %s", [$provider, $hash]));
            }else{
                return (int)$wpdb->query($wpdb->prepare("UPDATE `".API_RETRY_TABLE_NAME."` SET `tries` = `tries` + %d", [1]));
            }
        }else{
            return (int)$wpdb->query($wpdb->prepare("INSERT INTO `".API_RETRY_TABLE_NAME."` (`provider`, `method`, `url`, `request`, `hash`, `timestamp`) VALUES (%s, %s, %s, %s, %s, %s)", [
                $provider,
                $method,
                $url,
                $request,
                $hash,
                $timestamp
            ]));
        }
    }


    /**
     * @param int $status
     * @param null $provider
     * @param null $method
     * @return array|object|null
     */
    public static function pull($status = 0, $provider = null, $method = null)
    {
        global $wpdb;

        $query = [];
        $query[] = sprintf("SELECT * FROM `%s`", API_RETRY_TABLE_NAME);
        $query[] = sprintf("WHERE `status` = %d", (int)$status);

        return $wpdb->get_results(implode(" ", $query), OBJECT_K);
    }
}