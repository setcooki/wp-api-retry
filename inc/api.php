<?php

use Setcooki\Wp\Api\Retry\Retry;

if (function_exists('api_retry_push')) {
    /**
     * @param $status
     * @param $provider
     * @param $method
     * @param $service
     * @param $endpoint
     * @param $request
     * @param null $callback
     * @return int
     * @throws \Setcooki\Wp\Api\Retry\Exception
     */
    function api_retry_push($status, $provider, $method, $service, $endpoint, $request, $callback = null)
    {
        return Retry::push($status, $provider, $method, $service, $endpoint, $request);
    }
}


if (function_exists('api_retry_pull')) {
    /**
     * @param int $status
     * @param null $provider
     * @param null $method
     * @param null $service
     * @return array
     */
    function api_retry_pull($status = 0, $provider = null, $method = null, $service = null)
    {
        return Retry::pull($status, $provider, $method, $service);
    }
}