<?php

/*
 * Selections from the laravel framework helpers and some Fisdap/bgetsug originals
 *
 * https://github.com/laravel/framework/blob/5.1/src/Illuminate/Foundation/helpers.php
 */

use Illuminate\Support\Str;

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        return APPLICATION_PATH . '/configs/' . $path ? DIRECTORY_SEPARATOR.$path : $path;
    }
}


if (!function_exists('base_path')) {
    /**
     * Get the base path.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return realpath(APPLICATION_PATH . '/..' . ($path ? DIRECTORY_SEPARATOR.$path : $path));
    }
}


if (!function_exists('storage_path')) {
    /**
     * Get the storage path.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return realpath(APPLICATION_PATH . '/../data/' . ($path ? DIRECTORY_SEPARATOR.$path : $path));
    }
}


if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}


if (! function_exists('bitwiseConstants')) {
    function bitwiseConstants($value)
    {
        if (preg_match('/\$|;/', $value)) {
            throw new \Exception(
                "The string '$value' is does not contain a valid set of constants and/or bitwise operators'"
            );
        }

        /*
         * Yeah, eval is EVIL, but at least we're validating that the input values don't contain any $ or ;
         * So any malicious code will have a real hard time executing. ~bgetsug
         */
        return eval("return {$value};");
    }
}
