<?php namespace Fisdap\Ascend\Greatplains\Exceptions;

/**
 * Class InvalidArgumentException
 *
 * Customer exception for when an invalid argument is used
 *
 * @package Fisdap\Ascend\Greatplains\Exceptions
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InvalidArgumentException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
