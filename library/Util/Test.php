<?php
/**
 * Test conditions.
 * This class is most useful when coupled with assertions.
 */
class Util_Test {
    /**
     * Test if a condition is a specific object type.
     * @param mixed $condition The condition to test.
     * @param string $class The class name.
     */
    public static function is_a($condition, $class) {
        return $condition instanceof $class;
    }

    /**
     * Test if a condition is an array.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_array($condition) {
        return is_array($condition);
    }

    /**
     * Test if a value is in an array.
     * @param mixed $value The value to test.
	 * @param array $list The list to test against.
	 * @param boolean $strict TRUE if types should be checked also.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_in_array($value, $list, $strict=false) {
        return in_array($value, $list, $strict);
    }

    /**
     * Test if a condition is a boolean.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_boolean($condition) {
        return self::is_int($condition) ||
            ($condition === true) ||
            ($condition === false);
    }

    /**
     * Test if a condition is false.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_false($condition) {
        return !$condition;
    }

    /**
     * Test if a condition is an int.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_int($condition) {
        if (is_int($condition)) return true;
        if (!is_numeric($condition)) return false;

        return (int) $condition == (float) $condition;
    }

    /**
     * Test if a condition is a float.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_float($condition) {
        if (is_float($condition)) return true;
        if (is_numeric($condition)) return true;

        return false;
    }

    /**
     * Test if a condition is NOT null.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_not_null($condition) {
        return !is_null($condition);
    }

    /**
     * Test if a condition is null.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_null($condition) {
        return is_null($condition);
    }

    /**
     * Test if a condition is an object.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_object($condition) {
        return is_object($condition);
    }

    /**
     * Test if a condition is a string.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_string($condition) {
        return is_string($condition);
    }

    /**
     * Test if a condition is true.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_true($condition) {
        return !!$condition;
    }

    /**
     * Test if a condition is a not empty string.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_not_empty_string($condition) {
        return is_string($condition) && strlen($condition);
    }

    /**
     * Test if a condition is a not empty trimmed string.
     * @param mixed $condition The condition to test.
     * @return boolean TRUE if the condition is met.
     */
    public static function is_not_empty_trimmed_string($condition) {
        return is_string($condition) && strlen(trim($condition));
    }
}
?>
