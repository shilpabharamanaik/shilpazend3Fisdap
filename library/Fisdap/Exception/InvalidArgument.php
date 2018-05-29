<?php
//require_once('phputil/exceptions/FisdapException.inc');

/**
 * An invalid argument has occurred.
 */
final class Fisdap_Exception_InvalidArgument extends Fisdap_Exception_Exception
{
    /**
     * Constructor.
     * @param string|null $message An indication of what caused the problem.
     * @param Exception|null $cause The underlying cause.
     */
    public function __construct($message = null, $cause = null)
    {
        if (is_null($message)) {
            $message = 'invalid argument';
        }

        parent::__construct($message, $cause);
    }
}

?>
