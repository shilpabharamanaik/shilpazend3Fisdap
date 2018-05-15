<?php
// require_once('Assert.inc');

/**
 * Data conversions.
 */
class Util_Convert
{
    /**
     * Convert a variable to an array.
     * @param mixed $var The variable to convert.
     * @return array The array, no elements if null if passed.
     */
    public static function to_array($var)
    {
        if (is_array($var)) {
            return $var;
        }
        if (is_null($var)) {
            return array();
        }

        return array($var);
    }

    /**
     * Convert a variable to a TRUE/FALSE value.
     * @param mixed $var The variable to convert.
     * @return boolean TRUE or FALSE.
     */
    public static function to_boolean($var)
    {
        if ($var) {
            return true;
        }
        return false;
    }

    /**
     * Convert a variable to an integer.
     * A non numeric arguments will cause the program to die.
     * Empty strings and nulls are allowed
     * @param mixed $var The variable to convert.
     * @return int The integer.
     * @todo this isn't very strict right now, we may want to make it more so
     */
    public static function to_int($var)
    {
        Util_Assert::is_true(is_numeric($var) || $var==='');
        //Util_Assert::is_true((int)$var == (float)$var);

        return (int)$var;
    }

    /**
     * Convert a variable to a float.
     * A non numeric arguments will cause the program to die.
     * @param mixed $var The variable to convert.
     * @return float The float.
     */
    public static function to_float($var)
    {
        Util_Assert::is_true(is_numeric($var) || $var==='');

        return (float)$var;
    }

    /**
     * Convert a variable to a string.
     * Passing an array will cause the program to die.
     * @param mixed $var The variable to convert.
     * @return string The string, or the empty string if null is passed.
     */
    public static function to_string($var)
    {
        Util_Assert::is_true(!Util_Test::is_array($var));

        if (is_object($var)) {
            return $var->__toString();
        }

        return (string)$var;
    }

    /**
     * Convert a variable to a string or null.
     * Passing an array will cause the program to die.
     * @param mixed $var The variable to convert.
     * @return string|null The string, or null if null is passed.
     */
    public static function to_string_or_null($var)
    {
        Util_Assert::is_true(!Util_Test::is_array($var));

        if (is_object($var)) {
            return $var->__toString();
        }
        if (is_null($var)) {
            return null;
        }

        return (string)$var;
    }

    /**
     * Convert a variable to an object.
     * If the variable already is an instance of the class, it is simply
     * returned.
     * @param mixed $var The date.
     * @param string $class_name The class name the var must be an instance of.
     * @return object The object.
     */
    public static function to_a($var, $class_name)
    {
        Util_Assert::is_not_empty_string($class_name);

        if ($var instanceof $class_name) {
            return $var;
        }
        return new $class_name($var);
    }
}
