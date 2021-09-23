<?php

if (!function_exists('api_retry_report')) {
    /**
     * @param $recipients
     * @param $provider
     * @param $method
     * @param $service
     * @param $endpoint
     * @param null $request
     * @return bool
     */
    function api_retry_report($recipients, $provider, $method, $service, $endpoint, $request = null)
    {
        $options = json_decode(get_option('wpar_options', new \stdClass()));
        $tries = API_RETRY_MAX_TRIES;
        if (!empty($options) && isset($options->max_tries)) {
            $tries = $options->max_tries;
        }

        $subject = sprintf(__('API retry report for provider: %s'), $provider);
        $message = '';
        $message .= sprintf(__('The %s/%s request to: %s with request body:'), $method, $service, $endpoint);
        if (!empty($request)) {
            $message .= "\n\n";
            $message .= $request;
        }
        $message .= "\n\n";
        $message .= sprintf(__('failed after %d tries. Please take appropriate actions.'), $tries);

        return wp_mail($recipients, $subject, $message);
    }
}