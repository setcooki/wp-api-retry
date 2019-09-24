<?php

use Setcooki\Wp\Api\Retry\Retry;

if(function_exists('api_retry_push'))
{
    /**
     * @param $status
     * @param $provider
     * @param $method
     * @param $url
     * @param $request
     * @param null $callback
     * @return int
     * @throws Exception
     */
    function api_retry_push($status, $provider, $method, $url, $request, $callback = null)
    {
        return Retry::push($status, $provider, $method, $url, $request);
    }
}


if(function_exists('api_retry_pull'))
{
    /**
     * @param int $status
     * @param null $provider
     * @param null $method
     * @return mixed
     */
    function api_retry_pull($status = 0, $provider = null, $method = null)
    {
        return Retry::pull($status, $provider, $method);
    }
}