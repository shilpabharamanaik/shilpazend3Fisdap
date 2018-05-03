<?php namespace Fisdap\JBL\Authentication\Exceptions;

/**
 * Class AuthenticationFailedException
 *
 * Exception for when authentication fails
 *
 * @package Fisdap\JBL\Authentication\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class AuthenticationFailedException extends RequestException
{
    const HTTP_STATUS_CODE = 403;

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
