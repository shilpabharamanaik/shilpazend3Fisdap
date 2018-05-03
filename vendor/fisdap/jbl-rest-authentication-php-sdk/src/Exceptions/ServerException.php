<?php namespace Fisdap\JBL\Authentication\Exceptions;

/**
 * Class ServerException
 *
 * Exceptions when the server returns back 500 error status
 *
 * @package Fisdap\JBL\Authentication\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class ServerException extends RequestException
{
    const HTTP_STATUS_CODE = 500;

    /**
     * Get the http status code for exception
     *
     * @return int
     */
    public function getHttpStatus()
    {
        return self::HTTP_STATUS_CODE;
    }
}
