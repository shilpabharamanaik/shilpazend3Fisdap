<?php namespace Fisdap\Api\Client\Gateway;

/**
 * General contract for a Gateway
 *
 * @package Fisdap\Api\Client\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface Gateway
{
    const RESPONSE_TYPE_OBJECT = 'object';
    const RESPONSE_TYPE_ARRAY = 'array';


    /**
     * @param string $responseType
     *
     * @return static
     */
    public function setResponseType($responseType);


    /**
     * @return string
     */
    public function getResponseType();
}
