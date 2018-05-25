<?php namespace Fisdap\Exception;

class MethodNotImplementedException extends \BadMethodCallException
{
    public function __construct($method, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = "$method is not yet implemented";
    }
}