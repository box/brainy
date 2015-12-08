<?php

namespace Box\Brainy\Compiler\Helpers;

class Tag extends ParseTree
{

    /**
     * Create parse tree buffer for Smarty tag
     *
     * @param object $parser parser object
     * @param string $data   content
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
        throw new \Box\Brainy\Exceptions\SmartyException('Brainy tag cast to inline template data');
    }

    /**
     * Return buffer content
     *
     * @return string content
     */
    public function toSmartyPHP()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function canCombineInlineData()
    {
        return false;
    }
}
