<?php
//require_once('phputil/exceptions/FisdapDatabaseException.inc');

/**
 * A DB exception that occurred while running a SQL statement.
 *
 * The text of the message should be generic so that the general
 * problem is displayed, not the actual specifics.
 */
class Fisdap_Exception_DatabaseStatement extends Fisdap_Exception_Database
{
    private $statement;

    /**
     * Constructor.
     * @param string|null $message An indication of what caused the problem.
     * @param string $sql_message The SQL message text.
     * @param string $statement The SQL statement that caused the error.
     * @param Exception|null $cause The underlying cause.
     */
    public function __construct($message, $sql_message, $statement, $cause = null)
    {
        $this->statement = $statement;

        parent::__construct($message, $sql_message, $cause);
    }

    /**
     * Retrieve the statement that was executing at the time of the error.
     * @return string The SQL statement.
     */
    public function get_statement()
    {
        return $this->statement;
    }

    public function get_additional_info()
    {
        return array_merge(
            parent::get_additional_info(),
            array('Statement: ' . $this->get_statement())
        );
    }
}
