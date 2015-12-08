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
    public function to_inline_data()
    {
        throw new \Box\Brainy\Exceptions\SmartyException('PHP Expression cast to inline template data');
    }

    /**
     * Return buffer content in parentheses
     *
     * @return string content
     */
    public function to_smarty_php()
    {
        return sprintf("(%s)", $this->data);
    }

    /**
     * @return bool
     */
    public function can_combine_inline_data()
    {
        return false;
    }
}
