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
     * @param $service
     * @param $endpoint
     * @param $request
     * @param null $message
     * @param null $callback
     * @return int
     * @throws Exception
     */
    public static function push($status, $provider, $method, $service, $endpoint, $request, $message = null, $callback = null)
    {
        global $wpdb;

        $decode = false;

        //manual retry from monitor
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'api-retry' && isset($_GET['try']) && !empty($_GET['try'])) {
            $id = (int)$_GET['try'];
        } else {
            $id = 0;
        }

        if (is_array($request) || is_object($request)) {
            $request = json_encode($request);
            $decode = true;
        }
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }

        $options = json_decode(get_option('wpar_options', new \stdClass()));
        $tries = API_RETRY_MAX_TRIES;
        if (!empty($options) && isset($options->max_tries)) {
            $tries = $options->max_tries;
        }

        $provider = trim((string)$provider);
        $method = strtoupper(trim($method));
        $service = trim((string)$service);
        $endpoint = trim((string)$endpoint);
        $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
        //TODO: the hash may need to include the date or timestamp
        $hash = md5(sprintf('%s%s%s%s%s', $provider, $method, $service, $endpoint, $request));

        if ($id > 0) {
            return (int)$wpdb->query($wpdb->prepare("UPDATE `" . API_RETRY_TABLE_NAME . "` SET `status` = %d, `tries` = `tries` + %d, `timestamp` = %s  WHERE `id` = %d LIMIT 1", [$status, 1, $timestamp, $id]));
        }

        if ((int)$status === API_RETRY_STATUS_SUCCESS) {
            return (int)$wpdb->query($wpdb->prepare("UPDATE `" . API_RETRY_TABLE_NAME . "` SET `status` = %d, `message` = %s, `timestamp` = %s WHERE `provider` = %s AND `hash` = %s LIMIT 1", [API_RETRY_STATUS_SUCCESS, $message, $timestamp, $provider, $hash]));
        } else if ((int)$status === API_RETRY_STATUS_FAILURE) {
            //TODO: we limit the select query to 1 record. What if we have multiple? should they not be deleted?
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . API_RETRY_TABLE_NAME . "` WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [$provider, $hash, API_RETRY_STATUS_QUEUE]));
            if ($result && is_object($result)) {
                if ($result->tries >= $tries) {
                    if ((bool)API_RETRY_REPORT && API_RETRY_EMAIL_RECIPIENTS) {
                        $recipients = preg_split('=\s*\,\s*=i', trim(API_RETRY_EMAIL_RECIPIENTS, ' ,'));
                        if (!empty($recipients)) {
                            api_retry_report($recipients, $provider, $method, $endpoint, $request);
                        }
                    }
                    return (int)$wpdb->query($wpdb->prepare("UPDATE `" . API_RETRY_TABLE_NAME . "` SET `status` = %d, `message` = %s, `timestamp` = %s  WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [API_RETRY_STATUS_FAILURE, $message, $timestamp, $provider, $hash, API_RETRY_STATUS_QUEUE]));
                } else {
                    return (int)$wpdb->query($wpdb->prepare("UPDATE `" . API_RETRY_TABLE_NAME . "` SET `tries` = `tries` + %d, `message` = %s, `timestamp` = %s WHERE `provider` = %s AND `hash` = %s AND `status` = %d LIMIT 1", [1, $message, $timestamp, $provider, $hash, API_RETRY_STATUS_QUEUE]));
                }
            } else {
                return (int)$wpdb->query($wpdb->prepare("INSERT INTO `" . API_RETRY_TABLE_NAME . "` (`provider`, `method`, `service`, `endpoint`, `request`, `hash`, `message`, `decode`, `timestamp`, `created`) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %s, %s)", [
                    $provider,
                    $method,
                    $service,
                    $endpoint,
                    $request,
                    $hash,
                    $message,
                    (int)$decode,
                    $timestamp,
                    $timestamp
                ]));
            }
        } else {
            throw new Exception(sprintf(__('Status: %d not implemented'), (int)$status));
        }
    }


    /**
     * @param $provider
     * @param $method
     * @param $service
     * @param $endpoint
     * @param $request
     * @param null $failure
     * @param null $callback
     * @return false|mixed
     */
    public static function failure($provider, $method, $service, $endpoint, $request, $failure = null, $callback = null)
    {
        return call_user_func_array([__CLASS__, 'push'], array_merge([API_RETRY_STATUS_FAILURE], func_get_args()));
    }


    /**
     * @param $provider
     * @param $method
     * @param $service
     * @param $endpoint
     * @param $request
     * @param null $success
     * @param null $callback
     * @return false|mixed
     */
    public static function success($provider, $method, $service, $endpoint, $request, $success = null, $callback = null)
    {
        return call_user_func_array([__CLASS__, 'push'], array_merge([API_RETRY_STATUS_SUCCESS], func_get_args()));
    }


    /**
     * @param int $status
     * @param null $provider
     * @param null $method
     * @param null $service
     * @return array
     */
    public static function pull($status = API_RETRY_STATUS_QUEUE, $provider = null, $method = null, $service = null)
    {
        global $wpdb;

        $q = [];
        $query = [];
        $query[] = sprintf("SELECT * FROM `%s`", API_RETRY_TABLE_NAME);
        $query[] = sprintf("WHERE `status` = %d", (int)$status);
        if (!empty($provider)) {
            $q[] = sprintf("`provider` = '%s'", $provider);
        }
        if (!empty($method)) {
            $q[] = sprintf("`method` = '%s'", $method);
        }
        if (!empty($service)) {
            $q[] = sprintf("`service` = '%s'", $service);
        }
        if (!empty($q)) {
            $query[] = sprintf('AND %s ', implode(' AND ', $q));
        }

        $results = (array)$wpdb->get_results(implode(" ", $query), OBJECT_K);
        foreach ($results as &$result) {
            if (isset($result->decode) && (bool)$result->decode) {
                $result->request = json_decode($result->request);
            }
        }
        return $results;
    }


    /**
     * @param null $provider
     * @return bool
     */
    public static function purge($provider = null)
    {
        global $wpdb;

        $ts = time();
        $options = json_decode(get_option('wpar_options', new \stdClass()));
        $last = get_option('api_retry_last_purge', false);
        if (is_numeric($last)) {
            $last = (int)$last;
        } else {
            $last = PHP_INT_MAX;
        }
        $interval = (int)API_RETRY_PURGE_INTERVAL;
        if (!empty($options) && isset($options->purge_interval)) {
            $interval = (int)$options->purge_interval;
        }
        $tries = (int)API_RETRY_MAX_TRIES;
        if (!empty($options) && isset($options->max_tries)) {
            $tries = (int)$options->max_tries;
        }

        if ($interval > 0 && $last < strtotime(sprintf('-%d day', $interval), $ts)) {
            $sql[] = sprintf("SELECT * FROM `" . API_RETRY_TABLE_NAME . "` WHERE `status` != %d AND UNIX_TIMESTAMP(`timestamp`) < %d", API_RETRY_STATUS_QUEUE, $last);
            if (!empty($provider)) {
                $sql[] = sprintf("AND `provider` = '%s'", $provider);
            }
            $sql[] = 'ORDER BY `timestamp` DESC';
            $result = $wpdb->get_results(implode(' ', $sql));
            if (!empty($result) && (bool)API_RETRY_PURGE_REPORT && API_RETRY_EMAIL_RECIPIENTS) {
                $logs = [];
                $recipients = preg_split('=\s*\,\s*=i', trim(API_RETRY_EMAIL_RECIPIENTS, ' ,'));
                if (!empty($recipients)) {
                    foreach ($result as $res) {
                        $log = '';
                        $args =
                            [
                                date('Y-m-d H:i:s', strtotime($res->timestamp)),
                                $res->provider,
                                $res->method,
                                $res->service,
                                $res->endpoint,
                                (!empty($res->request)) ? trim($res->request) : '',
                                (!empty($res->message)) ? trim($res->message) : '',
                                $res->tries
                            ];
                        if ((int)$res->status === API_RETRY_STATUS_FAILURE) {
                            $log = apply_filters('api_retry_purge_failure_log', '[%s] %s:%s/%s to: %s with request [%s] failed ("%s") after %d tries', $args);
                        } else if ((int)$res->status === API_RETRY_STATUS_SUCCESS) {
                            $log = apply_filters('api_retry_purge_success_log', '[%s] %s:%s/%s to: %s with request [%s] succeeded ("%s") after %d tries', $args);
                        }
                        if (!empty($log)) {
                            $logs[] = vsprintf($log, $args);
                        }
                    }
                    if (sizeof($logs) > 0) {
                        $message = "";
                        $message .= sprintf(__("The following retries will be purged from the queue:"), $tries);
                        $message .= "\n\n";
                        foreach ($logs as $log) {
                            $message .= $log . PHP_EOL;
                        }
                        wp_mail($recipients, __('API retry purge report'), $message);
                    }
                }
            }
            if (!empty($provider)) {
                $wpdb->query($wpdb->prepare("DELETE FROM `" . API_RETRY_TABLE_NAME . "` WHERE `provider` = %s AND `status` != %d", [$provider, API_RETRY_STATUS_QUEUE]));
            } else {
                $wpdb->query($wpdb->prepare("DELETE FROM `" . API_RETRY_TABLE_NAME . "` WHERE `status` != %d", [API_RETRY_STATUS_QUEUE]));
            }
            update_option('api_retry_last_purge', $ts);
        }

        return true;
    }
}