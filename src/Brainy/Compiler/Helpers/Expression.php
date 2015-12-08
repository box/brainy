<?php

namespace Box\Brainy\Compiler\Helpers;

class Expression extends ParseTree
{
    /**
     * Create parse tree buffer for code fragment
     *
     * @param string $data content
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function toInlineData()
    {
        throw new \Box\Brainy\Exceptions\SmartyException('PHP Expression cast to inline template data');
    }

    /**
     * Return buffer content in parentheses
     *
     * @return string content
     */
    public function toSmartyPHP()
    {
        return sprintf("(%s)", $this->data);
    }

    /**
     * @return bool
     */
    public function canCombineInlineData()
    {
        return false;
    }
}
