<?php
//require_once('phputil/exceptions/FisdapException.inc');

/**
 * A base DB exception.
 */
class Fisdap_Exception_Database extends Fisdap_Exception_Exception
{
    private $sql_message;

    /**
     * Constructor.
     * @param string|null $message An indication of what caused the problem.
     * @param string|null $sql_message The SQL message text.
     * @param Exception|null $cause The underlying cause.
     */
    public function __construct($message, $sql_message = null, $cause = null)
    {
        $this->sql_message = $sql_message;
        parent::__construct($message . ': ' . $sql_message, $cause);
    }

    /**
     * Retrieve the SQL error message.
     * @return string The SQL error message.
     */
    public function get_sql_message()
    {
        return $this->sql_message;
    }

}

?>
