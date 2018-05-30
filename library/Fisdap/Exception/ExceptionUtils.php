<?php

/**
 * An exception helper class that does all the bookkeeping.
 * This class is not meant for usage outside of this package.
 */
class Fisdap_Exception_ExceptionUtils
{
    const DELIMITER = "\n";

    /**
     * Retrieve the end of line delimiter.
     * @return string The delimiter.
     */
    public static function get_delimiter()
    {
        return self::DELIMITER;
    }

    /**
     * Create a unique ID.
     * @return string The ID.
     */
    public static function generate_id()
    {
        static $last_addition;
        static $last_time = null;

        // The time part is guaranteed unique.
        if (function_exists('microtime')) {
            $id = (string)microtime(true);
        } else {
            $id = (string)time();
        }

        // If the time matches the last time, add on a fudge factor so
        // our ID appears unique.
        if ($id == $last_time) {
            $id .= '.' . ++$last_addition;
        } else {
            $last_time = $id;
            $last_addition = 0;
        }

        return $id;
    }

    /**
     * Retrieve the string representation of an exception.
     * @param Exception $exception The exception to use.
     * @return string The text with no trailing newline.
     */
    public static function get_as_string($exception, $showtrace = false)
    {
        if (($exception instanceof Fisdap_Exception_Exception) ||
            ($exception instanceof Fisdap_Exception_Runtime)
        ) {
            $id = ' (ID: ' . $exception->get_id() . ')';
        } else {
            $id = '';
        }

        $s = "exception '" . get_class($exception) . "' with message '" .
            $exception->getMessage() . "' in " . $exception->getFile() .
            ':' . $exception->getLine() . $id;
        if ($showtrace) {
            $s .= self::DELIMITER . 'Stack trace:' . self::DELIMITER .
                $exception->getTraceAsString();
        }
        return $s;
    }
}
