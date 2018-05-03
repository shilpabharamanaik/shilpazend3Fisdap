<?php
//require_once('Convert.inc');
//require_once('Test.inc');
//require_once('phputil/exceptions/Fisdap_Exception_InvalidArgumentException.inc');

/**
 * This class provides ways to validate expressions for type and/or value.
 * If an assertion fails, the program will be stopped.
 */
class Util_Assert {
    const REASON = 'assertion failed';

    /**
     * Assert that a condition is a specific object type.
     * @param mixed $condition The condition to test.
     * @param string $class The class name.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_a($condition, $class) {
        if (!Util_Test::is_a($condition, $class)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is an array.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_array($condition) {
        if (!Util_Test::is_array($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a value is in an array.
     * @param mixed $value The value to test.
	 * @param array $list The list to test against.
	 * @param boolean $strict TRUE if types should be checked also.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_in_array($value, $list, $strict=false) {
		Util_Assert::is_array($list);

        if (!Util_Test::is_in_array($value, $list, $strict)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is a boolean.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_boolean($condition) {
        if (!Util_Test::is_boolean($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is false.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_false($condition) {
        if (!Util_Test::is_false($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is NOT null.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_not_null($condition) {
        if (!Util_Test::is_not_null($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is null.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_null($condition) {
        if (!Util_Test::is_null($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is an object.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_object($condition) {
        if (!Util_Test::is_object($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is an int.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_int($condition) {
        if (!Util_Test::is_int($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is a flat.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_float($condition) {
        if (!Util_Test::is_float($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is a string.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_string($condition) {
        if (!Util_Test::is_string($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Assert that a condition is true.
     * @param mixed $condition The condition to test.
     * @throws Fisdap_Exception_InvalidArgumentException
     */
    public static function is_true($condition) {
        if (!Util_Test::is_true($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Test if a condition is a not empty string.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_not_empty_string($condition) {
        if (!Util_Test::is_not_empty_string($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }

    /**
     * Test if a condition is a not empty trimmed string.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_not_empty_trimmed_string($condition) {
        if (!Util_Test::is_not_empty_trimmed_string($condition)) {
            throw new Fisdap_Exception_InvalidArgument(self::REASON);
        }
    }
}
?>
