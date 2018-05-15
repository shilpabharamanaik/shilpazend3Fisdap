<?php
require_once('Assert.inc');

/**
 * Determine if a string ends with another.
 * If the ending is the empty string, this method returns TRUE.
 * @param string $str1 The string to test.
 * @param string $str2 The ending.
 * @return boolean TRUE if $str1 ends with $str2.
 */
function str_ends_with($str1, $str2)
{
    Assert::is_string($str1);
    Assert::is_string($str2);

    $offset = strlen($str1) - strlen($str2);
    if ($offset < 0) {
        return false;
    }

    return (substr($str1, $offset) == $str2);
}

/**
 * Pluralize a word.
 * If the singular form ends in a 'y', the plural will end in 'ies'.
 * @param string $singular The word or phrase in singular form (no trailing 's').
 * @param int $n The number the word pertains to.
 * @return The singular (n=1) or plural form (n != 1, an 's' is added).
 */
function pluralize($singular, $n)
{
    Assert::is_string($singular);
    Assert::is_int($n);

    if ($n != 1) {
        if (str_ends_with($singular, 'y')) {
            $singular = substr($singular, 0, strlen($singular)-1) . 'ies';
        } elseif (str_ends_with($singular, 'Y')) {
            $singular = substr($singular, 0, strlen($singular)-1) . 'IES';
        } else {
            $singular .= 's';
        }
    }

    return $singular;
}
