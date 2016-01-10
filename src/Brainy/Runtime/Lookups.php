<?php
namespace Box\Brainy\Runtime;

use \Box\Brainy\Brainy;

class Lookups
{
    /**
     * Performs a safe lookup of a variable.
     * Used when \Box\Brainy\Brainy::$safe_lookups is set to LOOKUP_SAFE
     *
     * @param    array      $arr
     * @param    string|int $key
     * @return   mixed
     * @throws   InvalidArgumentException
     * @see      \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeVarLookup($arr, $key)
    {
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        return null;
    }

    /**
     * Performs a safe lookup of a variable and raises a warning
     * using trigger_error if the variable is not found
     *
     * Used when \Box\Brainy\Brainy::$safe_lookups is set to LOOKUP_SAFE_WARN
     *
     * @param    array      $arr
     * @param    string|int $key
     * @return   mixed
     * @throws   InvalidArgumentException
     * @see      \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeVarLookupWarn($arr, $key)
    {
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        trigger_error('Could not find variable "' . $key . '" in Brainy template.', E_USER_WARNING);
        return null;
    }

    /**
     * Performs a safe lookup of an array member with a safety value.
     * Used when \Box\Brainy\Brainy::$safe_lookups is set to LOOKUP_SAFE
     *
     * @param    array      $arr
     * @param    string|int $key
     * @return   mixed
     * @throws   InvalidArgumentException
     * @see      \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeArrayLookup($arr, $key)
    {
        if (is_array($arr) && isset($arr[$key])) {
            return $arr[$key];
        }

        return '';
    }

    /**
     * Performs a safe lookup of an array member and raises a warning
     * using trigger_error if the member is not found.
     *
     * Used when \Box\Brainy\Brainy::$safe_lookups is set to LOOKUP_SAFE_WARN
     *
     * @param    array      $arr
     * @param    string|int $key
     * @return   mixed
     * @throws   InvalidArgumentException
     * @see      \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeArrayLookupWarn($arr, $key)
    {
        if (is_array($arr) && isset($arr[$key])) {
            return $arr[$key];
        }

        trigger_error('Could not find member "' . $key . '" in Brainy template.', E_USER_WARNING);
        return '';
    }
}
