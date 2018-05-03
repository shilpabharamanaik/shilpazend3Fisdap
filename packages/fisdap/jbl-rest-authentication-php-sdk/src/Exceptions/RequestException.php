<?php namespace Fisdap\JBL\Authentication\Exceptions;

/**
 * Class RequestException
 *
 * Exception for when anything besides a 403 or 500 happens when connecting to server
 *
 * @package Fisdap\JBL\Authentication\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class RequestException extends \Exception
{
    /**
     * RequestException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
