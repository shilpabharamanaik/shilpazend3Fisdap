<?php namespace Fisdap\JBL\Authentication\Contracts;

/**
 * Interface HttpClient
 *
 * Http client to make requests against REST API
 *
 * @package Fisdap\JBL\Authentication\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface HttpClient
{
    /**
     * POST a request
     *
     * @param string $endpoint
     * @param null|array $data
     * @return mixed
     */
    public function post($endpoint, $data = null);

    /**
     * Make a GET request
     *
     * @param string $endpoint
     * @param null|array $data
     * @return mixed
     */
    public function get($endpoint, $data = null);

    /**
     * Set headers
     *
     * @param array $headers
     * @return HttpClient
     */
    public function setHeaders($headers = []);
}
