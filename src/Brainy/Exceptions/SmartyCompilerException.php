<?php

namespace Box\Brainy\Exceptions;

class SmartyCompilerException extends SmartyException
{
    /**
     * @internal
     */
    public function __toString()
    {
        return ' --> Smarty Compiler: ' . $this->message . ' <-- ';
    }
    public $line;
    /**
     * The template source snippet relating to the error
     * @var string|null
     */
    public $source = null;
    /**
     * The raw text of the error message
     * @var string|null
     */
    public $desc = null;
    /**
     * The resource identifier or template name
     * @var string|null
     */
    public $template = null;
}
