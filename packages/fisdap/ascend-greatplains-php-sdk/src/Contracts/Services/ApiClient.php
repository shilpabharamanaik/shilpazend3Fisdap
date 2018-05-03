<?php namespace Fisdap\Ascend\Greatplains\Contracts\Services;

/**
 * Interface ApiClient
 *
 * Api client interaction
 *
 * @package Fisdap\Ascend\Greatplains\Contracts\Services
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface ApiClient
{
    /**
     * Make a GET request and return array from json
     *
     * @param string $endpoint
     * @return array
     */
    public function get($endpoint);

    /**
     * Make a POST request and return array from json
     *
     * @param string $endpoint
     * @param $data
     * @return array
     */
    public function post($endpoint, $data = null);

    /**
     * Make a PATCH request and return array from json
     *
     * @param string $endpoint
     * @param $data
     * @return array
     */
    public function patch($endpoint, $data = null);
}
