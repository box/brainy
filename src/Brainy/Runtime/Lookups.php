<?php

namespace Box\Brainy\Runtime;

use \Box\Brainy\Brainy;


class Lookups
{
    /**
     * Performs a safe lookup of a variable.
     * @param array $arr
     * @param string|int $key
     * @param int $safety
     * @return mixed
     * @throws InvalidArgumentException
     * @see \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeVarLookup($arr, $key, $safety)
    {
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Brainy::LOOKUP_SAFE_WARN) {
            trigger_error('Could not find variable "' . $key . '" in Brainy template.', E_USER_WARNING);
        }
        return $arr[$key] = new \Box\Brainy\Templates\Variable();
    }

    /**
     * Performs a safe lookup of an array member with a safety value.
     * @param array $arr
     * @param string|int $key
     * @param int $safety
     * @return mixed
     * @throws InvalidArgumentException
     * @see \Box\Brainy\Brainy::$safe_lookups
     * @internal
     */
    public static function safeArrayLookup($arr, $key, $safety)
    {
        if (is_array($arr) && isset($arr[$key])) {
            return $arr[$key];
        }
        if ($safety === Brainy::LOOKUP_SAFE_WARN) {
            trigger_error('Could not find member "' . $key . '" in Brainy template.', E_USER_WARNING);
        }
        return '';
    }
}
