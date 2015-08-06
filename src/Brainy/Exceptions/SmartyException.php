<?php

namespace Box\Brainy\Exceptions;


/**
 * Smarty exception class
 * @package Brainy
 */
class SmartyException extends \Exception
{
    /**
     * Whether to HTML escape the contents of the exception.
     * @var boolean
     */
    public static $escape = false;

    /**
     * @internal
     */
    public function __toString() {
        return ' --> Smarty: ' . (self::$escape ? htmlentities($this->message) : $this->message)  . ' <-- ';
    }
}
