<?php

/**
 * @param $recipients
 * @param $provider
 * @param $method
 * @param $url
 * @param null $request
 * @return bool
 */
if(!function_exists('api_retry_report'))
{
    function api_retry_report($recipients, $provider, $method, $url, $request = null)
    {
        $subject = sprintf(__('API retry report for provider: %s'), $provider);
        $message = '';
        $message .= sprintf(__('The \'%s\' request to: %s with request body:'), $method, $url);
        if(!empty($request))
        {
            $message .= "\n\n";
            $message .= $request;
        }
        $message .= "\n\n";
        $message .= sprintf(__('failed after %d tries. Please take appropriate actions.'), API_RETRY_MAX_TRIES);

        return wp_mail($recipients, $subject, $message);
    }
}