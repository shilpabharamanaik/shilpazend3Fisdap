<?php
//require_once('phputil/exceptions/FisdapExceptionUtils.inc');

/**
 * A base exception in Fisdap.
 */
abstract class Fisdap_Exception_Base extends Exception
{
    private $cause;
    private $id;

    /**
     * Constructor.
     * @param string $message An indication of what caused the problem.
     * @param Exception|null $cause The underlying cause.
     */
    public function __construct($message, $cause = null)
    {
        // Silently ignore error if cause is not an exception.
        if ($cause instanceof Exception) {
            $this->cause = $cause;
        }

        $this->id = Fisdap_Exception_ExceptionUtils::generate_id();

        parent::__construct($message, 0);
    }

    /**
     * Retrieve the cause.
     * @return Exception | null The cause.
     */
    public function get_cause()
    {
        return $this->cause;
    }

    /**
     * Retrieve the unique ID.
     * @return string The exception ID.
     */
    public function get_id()
    {
        return $this->id;
    }

    public function __toString()
    {
        $s = Fisdap_Exception_ExceptionUtils::get_as_string($this);
        if (!is_null($this->cause)) {
            $s .= Fisdap_Exception_ExceptionUtils::get_delimiter() . $this->cause;
        }

        return $s;
    }

    /**
     * Retrieve any additional information about the exception.
     * This method should be overridden by subclasses that have additional
     * information.
     * @return array Additional messages.
     */
    public function get_additional_info()
    {
        return array();
    }
}

?>
